<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\InspectionProcess;
use App\Models\InspectionDetail;
use App\Models\Proposal;
use App\Models\Ship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InspectionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_inspections');

        $query = Inspection::with(['ship', 'process', 'inspector'])
            ->withExists(['details as has_failed_step' => function($q) {
                $q->where('status', 'fail');
            }]);

        $sortColumn = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 20);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('code', 'like', "%{$search}%")
                  ->orWhereHas('ship', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%");
                  });
        }
        
        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by Result
        if ($request->filled('result')) {
            $resultFilter = $request->result;
            if ($resultFilter === 'fail') {
                $query->where(function($q) {
                    $q->where('result', 'fail')
                      ->orWhereHas('details', function($q2) {
                          $q2->where('status', 'fail');
                      });
                });
            } else if ($resultFilter === 'pass') {
                $query->where('result', 'pass')
                      ->whereDoesntHave('details', function($q2) {
                          $q2->where('status', 'fail');
                      });
            } else {
                $query->where('result', $resultFilter);
            }
        }

        // Sorting
        $validSortColumns = ['code', 'inspection_date', 'status', 'result', 'created_at'];
        if (in_array($sortColumn, $validSortColumns)) {
            $query->orderBy($sortColumn, $sortOrder);
        } else {
            $query->latest();
        }

        $inspections = $query->paginate($perPage)->withQueryString();
        
        // Data for Create Modal
        $ships = Ship::all(); 
        $processes = InspectionProcess::with('steps.items')->get();

        return view('admin.inspections.index', compact('inspections', 'ships', 'processes', 'sortColumn', 'sortOrder'));
    }

    public function create()
    {
        // Deprecated: Moving to Modal
        return redirect()->route('admin.inspections.index');
    }
    
    public function destroy(Inspection $inspection)
    {
        $this->authorize('delete_inspections');
        
        // Optional: details/files cleanup
        $inspection->details()->delete();
        $inspection->delete();
        
        return back()->with('success', 'Đã xóa đợt đăng kiểm thành công.');
    }

    public function store(Request $request)
    {
        $this->authorize('create_inspections');

        $validated = $request->validate([
            'ship_id' => 'required|exists:ships,id',
            'inspection_process_id' => 'required|exists:inspection_processes,id',
            'inspection_date' => 'required|date',
            'fee_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $count = Inspection::whereYear('created_at', date('Y'))->count() + 1;
            $code = 'DK-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $inspection = Inspection::create([
                'code' => $code,
                'ship_id' => $validated['ship_id'],
                'inspection_process_id' => $validated['inspection_process_id'],
                'inspector_id' => auth()->id(),
                'inspection_date' => $validated['inspection_date'],
                'fee_amount' => $validated['fee_amount'] ?? null,
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Initialize details based on process items
            $process = InspectionProcess::with('steps.items')->find($validated['inspection_process_id']);
            foreach ($process->steps as $step) {
                foreach ($step->items as $item) {
                    InspectionDetail::create([
                        'inspection_id' => $inspection->id,
                        'inspection_step_item_id' => $item->id,
                        'status' => 'pending', // or null
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.inspections.show', $inspection)->with('success', 'Đã tạo đợt đăng kiểm mới.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi khi tạo đợt đăng kiểm: ' . $e->getMessage());
        }
    }

    public function show(Inspection $inspection)
    {
        $this->authorize('view_inspections');
        $inspection->load(['ship', 'process.steps.items', 'details.proposal', 'inspector']);

        // Organize details by item ID for easy access in view
        $detailsMap = $inspection->details->keyBy('inspection_step_item_id')->map(function ($detail) {
            if (!empty($detail->evidence_files)) {
                  $filename = basename($detail->evidence_files[0]);
                  $detail->evidence_url = route('admin.media.serve', ['filename' => $filename]);
            } else {
                $detail->evidence_url = null;
            }
            // Include proposal info for approval-required items
            $detail->proposal_status = $detail->proposal ? $detail->proposal->status : null;
            $detail->proposal_id_val = $detail->proposal_id;
            return $detail;
        });

        // Ensure it's an object for JS
        if ($detailsMap->isEmpty()) {
            $detailsMap = (object)[];
        }

        return view('admin.inspections.show', compact('inspection', 'detailsMap'));
    }

    /**
     * Request a proposal approval for an item requiring sign-off.
     */
    public function requestApproval(Request $request, Inspection $inspection)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:inspection_step_items,id',
        ]);

        $detail = InspectionDetail::firstOrCreate([
            'inspection_id' => $inspection->id,
            'inspection_step_item_id' => $validated['item_id'],
        ], ['status' => 'pending']);

        if ($detail->proposal_id) {
            return response()->json(['success' => false, 'message' => 'Đề xuất đã tồn tại.', 'proposal_status' => $detail->proposal->status]);
        }

        // Find the item title for proposal name
        $item = $detail->item;
        $inspection->load('ship');

        $content = 'Đề xuất được tự động tạo bởi hệ thống trong quá trình đăng kiểm.' .
                     "\n- Đợt đăng kiểm: {$inspection->code}" .
                     "\n- Hạng mục: " . ($item->content ?? 'N/A');

        if (!empty($detail->note)) {
            $content .= "\n- Ghi chú: " . $detail->note;
        }

        // Copy existing evidence files to proposal attachments to prevent accidental deletion crossover
        $copiedFiles = [];
        if (!empty($detail->evidence_files)) {
            foreach ($detail->evidence_files as $idx => $filename) {
                if (\Illuminate\Support\Facades\Storage::disk('private')->exists($filename)) {
                    $newFilename = 'proposal_auto_' . time() . '_' . $idx . '_' . substr($filename, 0, 150);
                    \Illuminate\Support\Facades\Storage::disk('private')->copy($filename, $newFilename);
                    $copiedFiles[] = $newFilename;
                }
            }
        }

        $proposal = Proposal::create([
            'title'            => 'Xin phê duyệt: ' . ($item->content ?? 'Hạng mục kiểm tra'),
            'category'         => 'Đăng kiểm tàu: ' . ($inspection->ship->name ?? $inspection->code),
            'content'          => $content,
            'user_id'          => Auth::id(),
            'ship_id'          => $inspection->ship_id,
            'status'           => 'pending',
            'attachment_files' => !empty($copiedFiles) ? $copiedFiles : null,
        ]);

        $approvers = $item->approvers ?? [];
        if (empty($approvers)) {
            // Fallback if no approvers assigned in Builder
            $approvers = \App\Models\User::pluck('id')->toArray();
        }

        $step = $proposal->steps()->create([
            'step_level'    => 1,
            'approval_type' => $item->require_all_approvers ? 'and' : 'or', // Use item's AND/OR setting
            'status'        => 'pending',
        ]);

        foreach ($approvers as $userId) {
            $step->approvals()->create([
                'user_id' => $userId,
                'status'  => 'pending',
            ]);
        }

        $detail->proposal_id = $proposal->id;
        $detail->save();

        return response()->json([
            'success' => true,
            'message' => 'Đề xuất đã được gửi, chờ Giám đốc phê duyệt.',
            'proposal_status' => 'pending',
            'proposal_id' => $proposal->id,
        ]);
    }

    public function updateStatus(Request $request, Inspection $inspection)
    {
        $this->authorize('update_inspections');
        // AJAX update for individual items
        $validated = $request->validate([
            'item_id' => 'required|exists:inspection_step_items,id',
            'status' => 'nullable|in:pass,fail,skipped',
            'note' => 'nullable|string',
            'evidence_files' => 'nullable|array',
            'evidence_files.*' => 'file|max:10240', // 10MB each
        ]);

        $detail = InspectionDetail::firstOrNew([
            'inspection_id' => $inspection->id,
            'inspection_step_item_id' => $validated['item_id'],
        ]);

        if ($request->hasFile('evidence_files')) {
            $existing = $detail->evidence_files ?? [];
            $newFiles = [];
            foreach ($request->file('evidence_files') as $idx => $file) {
                $filename = 'inspection_' . \Str::slug($inspection->code) . '_' . $validated['item_id'] . '_' . time() . '_' . $idx . '.' . $file->getClientOriginalExtension();
                $file->storeAs('/', $filename, 'private');
                $newFiles[] = $filename;
            }
            $detail->evidence_files = array_merge($existing, $newFiles);
        }

        if ($request->filled('status')) {
            $detail->status = $validated['status'];
        }

        if ($request->has('note')) {
             $detail->note = $request->input('note');
        }

        $detail->save();

        // Build array of all evidence URLs
        $evidenceUrls = collect($detail->evidence_files ?? [])->map(fn($f) => [
            'filename' => $f,
            'url' => route('admin.media.serve', ['filename' => $f]),
        ])->values()->toArray();

        return response()->json([
            'success' => true, 
            'detail' => $detail,
            'evidence_urls' => $evidenceUrls,
            // Keep backwards compat for old evidence_url single key
            'evidence_url' => count($evidenceUrls) > 0 ? $evidenceUrls[0]['url'] : null,
        ]);
    }
    
    public function update(Request $request, Inspection $inspection) {
         $this->authorize('update_inspections');
         // Update metadata
         $validated = $request->validate([
            'inspection_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'status' => 'sometimes|in:draft,in_progress,completed,rejected', // Optional updates if passed
            'result' => 'sometimes|nullable|in:pass,fail',
            'fee_amount' => 'nullable|numeric|min:0',
            'new_expiration_date' => 'nullable|date',
            'technical_safety_number' => 'nullable|string',
            'technical_safety_date' => 'nullable|date',
            'record_number' => 'nullable|string',
            'record_date' => 'nullable|date',
         ]);
         
         $inspection->update([
             'inspection_date' => $validated['inspection_date'] ?? $inspection->inspection_date,
             'notes' => $validated['notes'] ?? $inspection->notes,
             'status' => $validated['status'] ?? $inspection->status,
             'result' => $validated['result'] ?? $inspection->result,
             'fee_amount' => $validated['fee_amount'] ?? $inspection->fee_amount,
         ]);
         
         // Cập nhật thông tin cho Tàu khi hoàn thành đăng kiểm
         if (($validated['status'] ?? '') === 'completed') {
             $shipUpdates = [];
             
             if (array_key_exists('new_expiration_date', $validated) && !empty($validated['new_expiration_date'])) {
                 $shipUpdates['expiration_date'] = $validated['new_expiration_date'];
             }
             if (array_key_exists('technical_safety_number', $validated)) {
                 $shipUpdates['technical_safety_number'] = $validated['technical_safety_number'];
             }
             if (array_key_exists('technical_safety_date', $validated)) {
                 $shipUpdates['technical_safety_date'] = $validated['technical_safety_date'];
             }
             if (array_key_exists('record_number', $validated)) {
                 $shipUpdates['record_number'] = $validated['record_number'];
             }
             if (array_key_exists('record_date', $validated)) {
                 $shipUpdates['record_date'] = $validated['record_date'];
             }

             if (!empty($shipUpdates)) {
                 $inspection->ship->update($shipUpdates);
             }
         }
         
         // Nếu đăng kiểm hoàn thành (Bất kể Đạt/Không đạt), tạo/cập nhật Phiếu Thu để xác nhận doanh thu cho Đăng kiểm viên
         if (($validated['status'] ?? '') === 'completed') {
             $fee = $validated['fee_amount'] ?? $inspection->fee_amount;
             if ($fee > 0) {
                 $existingTrans = \App\Models\Transaction::where('reference_type', \App\Models\Inspection::class)
                     ->where('reference_id', $inspection->id)
                     ->first();
                     
                 if (!$existingTrans) {
                     $count = \App\Models\Transaction::whereYear('created_at', date('Y'))->count() + 1;
                     $code = 'PT-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
                     
                     \App\Models\Transaction::create([
                         'code' => $code,
                         'customer_name' => $inspection->ship->owner_name ?? 'Khách hàng',
                         'type' => 'income',
                         'amount' => $fee,
                         'payment_method' => 'cash',
                         'status' => 'approved',
                         'description' => 'Thu phí đăng kiểm tàu ' . ($inspection->ship->registration_number ?? '') . ' (' . $inspection->code . ')',
                         'user_id' => $inspection->inspector_id,
                         'reference_type' => \App\Models\Inspection::class,
                         'reference_id' => $inspection->id,
                         'transaction_date' => now(),
                     ]);
                 } else {
                     if ($existingTrans->amount != $fee) {
                         $existingTrans->update(['amount' => $fee]);
                     }
                 }
             }
         }
         
         return back()->with('success', 'Đợt đăng kiểm đã được hoàn tất thành công.');
    }
}
