<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProposalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view_proposal');

        $user = Auth::user();
        
        $query = Proposal::with(['creator', 'steps.approvals.user', 'ship'])->latest();
        
        // If user cannot manage all proposals, they see their own AND proposals assigned to them
        if (!$user->hasPermission('approve_proposal') && (!isset($user->role) || !in_array(strtolower($user->role->name), ['admin']))) {
            $query->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('steps.approvals', function($sq) use ($user) {
                      $sq->where('user_id', $user->id);
                  });
            });
        }

        $proposals = $query->get()->map(function ($p) use ($user) {
            // Append serve URLs for attachments
            $p->attachment_urls = collect($p->attachment_files ?? [])->map(function ($filename) {
                return [
                    'filename' => $filename,
                    'url' => route('admin.media.serve', ['filename' => $filename]),
                ];
            })->values()->toArray();

            $activeStep = $p->steps->where('status', 'pending')->sortBy('step_level')->first();
            $p->active_step_level = $activeStep ? $activeStep->step_level : null;
            $p->can_approve = false;

            if ($activeStep) {
                $p->can_approve = $activeStep->approvals->where('user_id', $user->id)->where('status', 'pending')->isNotEmpty();
            }

            // Include ship_id for QR code
            $p->ship_id_for_qr = $p->ship_id;

            return $p;
        });

        $users = \App\Models\User::select('id', 'name')->get();
        
        $ships = \App\Models\Ship::all()->append(['total_engine_hp', 'total_engine_kw']);
        $processes = \App\Models\InspectionProcess::with('steps.items')->where('is_active', true)->get();

        return view('admin.proposals.index', compact('proposals', 'users', 'ships', 'processes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create_proposal');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'content' => 'nullable|string',
            'pre_vat_amount' => 'nullable|numeric|min:0',
            'vat'            => 'nullable|integer|min:0|max:100',
            'amount'  => 'nullable|numeric|min:0',
            'steps' => 'required|array|min:1',
            'steps.*.type'      => 'required|in:and,or',
            'steps.*.name'      => 'nullable|string|max:255',
            'steps.*.approvers' => 'required|array|min:1',
            'steps.*.approvers.*' => 'exists:users,id',
            'steps.*.files'     => 'nullable|array',
            'steps.*.files.*'   => 'file|max:20480',
            'steps.*.amount'    => 'nullable|numeric|min:0',
            'expiration_date'   => 'required_if:category,Đăng kiểm tàu|nullable|date',
            'ship_data_form'    => 'nullable|string',
        ]);

        $proposal = Proposal::create([
            'title'            => $validated['title'],
            'category'         => $validated['category'],
            'content'          => $validated['content'] ?? null,
            'pre_vat_amount'   => $validated['pre_vat_amount'] ?? null,
            'vat'              => $validated['vat'] ?? null,
            'amount'           => $validated['amount'] ?? null,
            'user_id'          => Auth::id(),
            'ship_id'          => $request->input('ship_id'),
            'expiration_date'  => $validated['expiration_date'],
            'status'           => 'pending',
        ]);

        // Ghi chú: Logic thay đổi thông số thẻ Proposal hiện đã được chuyển sang nút Cập nhật thủ công (ajax).

        foreach ($validated['steps'] as $index => $stepData) {
            $stepFilenames = [];
            $stepFiles = $request->file("steps.{$index}.files");
            if (!empty($stepFiles)) {
                foreach ($stepFiles as $file) {
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $ext = $file->getClientOriginalExtension();
                    $filename = 'proposal_' . time() . '_' . \Illuminate\Support\Str::slug($originalName) . '.' . $ext;
                    $file->storeAs('/', $filename, 'private');
                    $stepFilenames[] = $filename;
                }
            }
            // (Đã loại bỏ tự động gán file docx biểu mẫu vào bước đầu tiên)

            $step = $proposal->steps()->create([
                'step_level'       => $index + 1,
                'name'             => $stepData['name'] ?? null,
                'approval_type'    => $stepData['type'],
                'amount'           => $stepData['amount'] ?? null,
                'status'           => 'pending',
                'attachment_files' => !empty($stepFilenames) ? $stepFilenames : null,
            ]);

            foreach ($stepData['approvers'] as $userId) {
                $step->approvals()->create([
                    'user_id' => $userId,
                    'status'  => 'pending',
                ]);
            }
        }

        return redirect()->back()->with('success', 'Đã tạo đề xuất thành công.');
    }

    /**
     * Upload additional files to a proposal step (AJAX).
     */
    public function uploadFile(Request $request, \App\Models\ProposalStep $step)
    {
        $this->authorize('create_proposal');

        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:10240',
        ]);

        $existing = $step->attachment_files ?? [];
        $newFiles = [];

        foreach ($request->file('files') as $file) {
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $ext = $file->getClientOriginalExtension();
            $filename = 'proposal_step_' . $step->id . '_' . time() . '_' . Str::slug($originalName) . '.' . $ext;
            $file->storeAs('/', $filename, 'private');
            $newFiles[] = $filename;
        }

        $step->attachment_files = array_merge($existing, $newFiles);
        $step->save();

        $urls = collect($step->attachment_files)->map(fn($f) => [
            'filename' => $f,
            'url' => route('admin.media.serve', ['filename' => $f]),
        ])->values()->toArray();

        return response()->json(['success' => true, 'attachment_urls' => $urls]);
    }

    /**
     * Delete a specific file from a proposal step (AJAX).
     */
    public function deleteFile(Request $request, \App\Models\ProposalStep $step)
    {
        $this->authorize('create_proposal');

        $request->validate(['filename' => 'required|string']);
        $filename = $request->filename;

        $files = $step->attachment_files ?? [];
        if (!in_array($filename, $files)) {
            return response()->json(['success' => false, 'message' => 'File không tồn tại trên bước này.'], 404);
        }

        // Remove from storage
        if (Storage::disk('private')->exists($filename)) {
            Storage::disk('private')->delete($filename);
        }

        $step->attachment_files = array_values(array_filter($files, fn($f) => $f !== $filename));
        $step->save();

        return response()->json(['success' => true]);
    }

    /**
     * Update the status of the specified resource in storage.
     */
    public function updateStatus(Request $request, Proposal $proposal)
    {
        $this->authorize('approve_proposal');

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string',
        ]);

        $user = Auth::user();

        // 1. Locate the active step (lowest step_level with status = pending)
        $activeStep = $proposal->steps()->where('status', 'pending')->orderBy('step_level')->first();

        if (!$activeStep) {
            return back()->withErrors('Đề xuất này đã kết thúc quá trình duyệt.');
        }

        // 2. Check if current user is an approver at this step and hasn't acted yet
        $approval = $activeStep->approvals()->where('user_id', $user->id)->where('status', 'pending')->first();

        if (!$approval) {
            return back()->with('error', 'Bạn không có quyền duyệt ở bước này, hoặc đã duyệt trước đó.');
        }

        // 3. Handle Reject
        if ($validated['status'] === 'rejected') {
            $rejectStep = $request->input('reject_step', 'all');

            // Người từ chối sẽ để lại commment trong mọi trường hợp
            $approval->update([
                'status'   => 'rejected',
                'comment'  => $validated['rejection_reason'],
                'acted_at' => now(),
            ]);

            // Lưu nhiều ảnh đính kèm nếu có
            $attachmentPaths = [];
            if ($request->hasFile('rejection_images')) {
                foreach ($request->file('rejection_images') as $file) {
                    $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('news_attachments', $filename, 'public');
                    $attachmentPaths[] = $path;
                }
            }

            if ($rejectStep === 'all') {
                // If anyone rejects all, the step fails and the entire proposal fails.
                $activeStep->update(['status' => 'rejected']);
                $proposal->update(['status' => 'rejected']);

                // Lấy danh sách người nhận bổ sung (nếu có)
                $notifyUserIds = $request->input('notify_users', []);
                $recipientIds = array_unique(array_merge([(string) $proposal->user_id], $notifyUserIds));

                // Tạo thông báo (News) gửi đến người tạo đề xuất
                News::create([
                    'title'          => 'Đề xuất bị từ chối: ' . $proposal->title,
                    'content'        => 'Đề xuất ' . $proposal->title . ' đã bị từ chối toàn bộ bởi ' . $user->name . '.<br>'
                                      . 'Lý do: ' . nl2br(e($validated['rejection_reason'])),
                    'attachment'     => !empty($attachmentPaths) ? json_encode($attachmentPaths) : null,
                    'recipient_type' => 'user',
                    'recipient_ids'  => $recipientIds,
                    'created_by'     => $user->id,
                ]);

                return redirect()->back()->with('error', 'Đã từ chối toàn bộ đề xuất.');
            } else {
                // Return to a specific step
                $targetStepLevel = (int) $rejectStep;
                $targetStepName = '';

                // Tìm tất cả các bước từ targetStepLevel trở đi để reset
                $stepsToReset = $proposal->steps()->where('step_level', '>=', $targetStepLevel)->get();
                foreach ($stepsToReset as $step) {
                    if ($step->step_level === $targetStepLevel) $targetStepName = $step->name;
                    $step->update(['status' => 'pending']);
                    
                    // Reset tất cả các approvals của bước đó về pending, xoá acted_at (ngoại trừ cái vừa comment reject)
                    $step->approvals()->update([
                        'status' => 'pending',
                        'acted_at' => null
                    ]);
                }

                // Chuyển lại trạng thái đề xuất thành pending
                $proposal->update(['status' => 'pending']);

                // Giữ lại comment của người vừa thao tác (mặc dù đã bị update_all reset phía trên)
                $approval->update([
                    'status'   => 'rejected',
                    'comment'  => $validated['rejection_reason'],
                    'acted_at' => now(),
                ]);

                // Lấy danh sách người nhận bổ sung (nếu có)
                $notifyUserIds = $request->input('notify_users', []);
                $recipientIds = array_unique(array_merge([(string) $proposal->user_id], $notifyUserIds));

                // Thông báo yêu cầu làm lại một phần
                News::create([
                    'title'          => 'Yêu cầu làm lại bước trong đề xuất: ' . $proposal->title,
                    'content'        => 'Đề xuất ' . $proposal->title . ' đã bị chuyển về bước "<strong>' . $targetStepName . '</strong>" (Bước ' . $targetStepLevel . ') bởi ' . $user->name . '.<br>'
                                      . 'Lý do: ' . nl2br(e($validated['rejection_reason'])),
                    'attachment'     => !empty($attachmentPaths) ? json_encode($attachmentPaths) : null,
                    'recipient_type' => 'user',
                    'recipient_ids'  => $recipientIds,
                    'created_by'     => $user->id,
                ]);

                return redirect()->back()->with('success', 'Đã chuyển đề xuất quay lại bước ' . $targetStepLevel . '.');
            }
        }

        // 4. Handle Approve
        if ($validated['status'] === 'approved') {
            $approval->update([
                'status'   => 'approved',
                'comment'  => $request->input('comment') ?? 'Đã duyệt',
                'acted_at' => now(),
            ]);

            $stepCompleted = false;

            if ($activeStep->approval_type === 'or') {
                $stepCompleted = true; // Any single approval is enough
            } else {
                // approval_type == 'and' -> check if no pending approvals left
                $pendingCount = $activeStep->approvals()->where('status', 'pending')->count();
                if ($pendingCount === 0) {
                    $stepCompleted = true;
                }
            }

            if ($stepCompleted) {
                // Mark step as approved and optionally skip others
                if ($activeStep->approval_type === 'or') {
                    $activeStep->approvals()->where('status', 'pending')->update(['status' => 'skipped']);
                }
                $activeStep->update(['status' => 'approved']);

                // Find if there is a next step
                $nextStep = $proposal->steps()->where('step_level', '>', $activeStep->step_level)->orderBy('step_level')->first();

                if (!$nextStep) {
                    // No more steps -> proposal fully approved
                    $proposal->update(['status' => 'approved']);

                    // Extend ship expiration date to the proposal expiration date if it is a ship inspection proposal
                    if ($proposal->category === 'Đăng kiểm tàu' && $proposal->ship_id && $proposal->expiration_date) {
                        $ship = $proposal->ship;
                        if ($ship) {
                            $ship->update([
                                'expiration_date' => $proposal->expiration_date
                            ]);
                        }
                    }

                    // Flash ship info for QR popup
                    if ($proposal->ship_id) {
                        session()->flash('approved_ship_id', $proposal->ship_id);
                        session()->flash('approved_ship_name', optional($proposal->ship)->name ?? '');
                        session()->flash('approved_proposal_title', $proposal->title);
                    }

                    return redirect()->back()->with('success', 'Đã duyệt toàn bộ đề xuất thành công.');
                }

                return redirect()->back()->with('success', 'Đã duyệt tầng hiện tại. Chuyển cấp phê duyệt tiếp theo.');
            }

            return redirect()->back()->with('success', 'Đã ghi nhận phê duyệt, cần chờ các người khác trong tầng xác nhận.');
        }

        return redirect()->back();
    }

    /**
     * Uỷ quyền phê duyệt cho một người dùng khác tại bước hiện tại
     */
    public function delegateApproval(Request $request, Proposal $proposal, \App\Models\ProposalStep $step)
    {
        $this->authorize('approve_proposal');

        $validated = $request->validate([
            'delegate_user_id' => 'required|exists:users,id'
        ], [
            'delegate_user_id.required' => 'Vui lòng chọn người dùng để uỷ quyền.',
            'delegate_user_id.exists' => 'Người dùng được uỷ quyền không tồn tại.',
        ]);

        $user = Auth::user();
        $delegateUserId = $validated['delegate_user_id'];

        if ($user->id == $delegateUserId) {
            return redirect()->back()->with('error', 'Bạn không thể uỷ quyền cho chính mình.');
        }

        // Kiểm tra xem bước này đã hoàn thành hay chưa
        if ($step->status !== 'pending') {
            return redirect()->back()->with('error', 'Không thể uỷ quyền vì bước duyệt này không còn ở trạng thái chờ.');
        }

        // Tìm quyền duyệt của người dùng hiện tại tại bước này
        $approval = $step->approvals()->where('user_id', $user->id)->where('status', 'pending')->first();
        
        if (!$approval) {
            return redirect()->back()->with('error', 'Bạn không có quyền duyệt ở bước này hoặc đã xử lý xong.');
        }

        // Kiểm tra xem người được uỷ quyền đã có trong danh sách duyệt của bước này chưa
        $existsInStep = $step->approvals()->where('user_id', $delegateUserId)->exists();
        if ($existsInStep) {
            return redirect()->back()->with('error', 'Người được uỷ quyền đã có sẵn quyền duyệt trong bước này.');
        }

        // Thực hiện uỷ quyền: đổi user_id của approval sang người mới
        $approval->update([
            'user_id' => $delegateUserId,
            // (Tuỳ chọn) Đánh dấu comment hoặc lịch sử đã được uỷ quyền từ ai
            'comment' => '[Đã uỷ quyền từ: ' . $user->name . ']'
        ]);

        return redirect()->back()->with('success', 'Đã uỷ quyền duyệt đề xuất thành công!');
    }

    /**

     * Tự động phê duyệt các mức đang chờ tiếp theo của user đối với đề xuất.
     */
    public function bulkApprove(Request $request, Proposal $proposal)
    {
        $this->authorize('approve_proposal');
        $user = Auth::user();
        $processedCount = 0;

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            while (true) {
                // Lấy bước đang chờ đầu tiên chưa duyệt
                $activeStep = $proposal->steps()->where('status', 'pending')->orderBy('step_level')->first();
                if (!$activeStep) {
                    break; // Đã xong toàn bộ đề xuất
                }

                // Kiểm tra mình có phải người duyệt bước này không
                $approval = $activeStep->approvals()->where('user_id', $user->id)->where('status', 'pending')->first();
                if (!$approval) {
                    break; // Dừng lại vì nhảy tới bước không phải của mình
                }

                // Thực hiện Duyệt
                $approval->update([
                    'status'   => 'approved',
                    'comment'  => 'Duyệt nhanh tất cả cấp độ',
                    'acted_at' => now(),
                ]);
                $processedCount++;

                // Lấy lại step bị Cache Relation trong model (để đếm lại pending)
                $activeStep->refresh();

                // Kiểm tra xem bước đó đã xong hoàn toàn chưa
                $stepCompleted = false;
                if ($activeStep->approval_type === 'or') {
                    $stepCompleted = true; // Any single approval is enough
                } else {
                    // check if no pending approvals left
                    if ($activeStep->approvals()->where('status', 'pending')->count() === 0) {
                        $stepCompleted = true;
                    }
                }

                if ($stepCompleted) {
                    // Đánh dấu xong bước
                    if ($activeStep->approval_type === 'or') {
                        $activeStep->approvals()->where('status', 'pending')->update(['status' => 'skipped']);
                    }
                    $activeStep->update(['status' => 'approved']);
                } else {
                    // Bước AND còn người khác chưa duyệt -> Dừng dây chuyền
                    break;
                }
            }

            // Kiểm tra xem đề xuất có cần chuyển trạng thái không
            $remainingSteps = $proposal->steps()->where('status', 'pending')->count();
            if ($remainingSteps === 0 && $processedCount > 0) {
                // No more steps -> proposal fully approved
                $proposal->update(['status' => 'approved']);

                // Extend ship expiration date to the proposal expiration date if it is a ship inspection proposal
                if ($proposal->category === 'Đăng kiểm tàu' && $proposal->ship_id && $proposal->expiration_date) {
                    $ship = $proposal->ship;
                    if ($ship) {
                        $ship->update([
                            'expiration_date' => $proposal->expiration_date
                        ]);
                    }
                }

                // Tiện ích flash Info cho QR PDF sau duyệt xong Proposal
                if ($proposal->ship_id) {
                    session()->flash('approved_ship_id', $proposal->ship_id);
                    session()->flash('approved_ship_name', optional($proposal->ship)->name ?? '');
                    session()->flash('approved_proposal_title', $proposal->title);
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            if ($processedCount > 0) {
                return redirect()->back()->with('success', "Đã duyệt thành công chuỗi $processedCount bước liên tiếp của bạn.");
            } else {
                return redirect()->back()->with('error', 'Bạn không có bước duyệt nào ở thời điểm hiện tại.');
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Bulk Approve Error', [
                'msg' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Đã xảy ra lỗi hệ thống khi duyệt: ' . $e->getMessage());
        }
    }

    /**

     * Start MatBao CA Document Signature process (Synchronous)
     */
    public function signDocument(Request $request, Proposal $proposal)
    {
        $this->authorize('approve_proposal');

        $user = Auth::user();
        $stepId = $request->input('step_id');
        $activeStep = $stepId ? $proposal->steps()->find($stepId) : $proposal->steps()->where('status', 'pending')->orderBy('step_level')->first();
        if (!$activeStep) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy bước duyệt nào hợp lệ.']);
        }

        $approval = $activeStep->approvals()->where('user_id', $user->id)->first();
        if (!$approval && !$user->hasPermission('approve_proposal')) {
            return response()->json(['success' => false, 'message' => 'Bạn không thuộc danh sách duyệt ở bước này.']);
        }

        try {
            $matbaoService = new \App\Services\MatBaoService($user);

            // Find the first PDF attachment
            $attachments = $activeStep->attachment_files ?? [];
            $pdfAttachment = null;
            $originalPdfIndex = -1;
            
            foreach ($attachments as $index => $filename) {
                if (is_string($filename) && strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'pdf') {
                    $pdfAttachment = $filename;
                    $originalPdfIndex = $index;
                    break;
                }
            }

            if (!$pdfAttachment || !Storage::disk('private')->exists($pdfAttachment)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Không tìm thấy file PDF đính kèm. Bạn phải tải lên ít nhất 1 file PDF để thực hiện ký số văn bản.'
                ]);
            }

            // 1. Read PDF and convert to Base64
            $pdfContent = Storage::disk('private')->get($pdfAttachment);
            $base64Pdf = base64_encode($pdfContent);

            // 2. Call MatBao CA
            $signConfig = [];
            if ($request->has('page')) $signConfig['Page'] = (int)$request->input('page');
            if ($request->has('x')) $signConfig['X'] = (int)$request->input('x');
            if ($request->has('y')) $signConfig['Y'] = (int)$request->input('y');
            
            $signedBase64 = $matbaoService->signPdf($base64Pdf, $signConfig);

            // 3. Decode and save the signed PDF
            $decodedPdf = base64_decode($signedBase64);
            $pathInfo = pathinfo($pdfAttachment);
            $signedFilename = $pathInfo['filename'] . '_signed_' . time() . '.pdf';
            
            Storage::disk('private')->put($signedFilename, $decodedPdf);

            // 4. Update the proposal attachments (replace or append)
            // User requested to remove the unsigned PDF from the proposal
            // We'll replace the original PDF filename with the new signed one in the array
            if (isset($originalPdfIndex)) {
                $attachments[$originalPdfIndex] = $signedFilename;
            } else {
                $attachments[] = $signedFilename;
            }
            $activeStep->attachment_files = array_values($attachments);
            $activeStep->save();

            return response()->json([
                'success' => true, 
                'message' => 'Ký tài liệu thành công. Đã tạo bản sao có chứa chữ ký số.',
                'signed_file' => route('admin.media.serve', ['filename' => $signedFilename])
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('MatBao CA sign error', ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Viettel MySign - Bước 1: Upload file + gửi yêu cầu ký
     * Trả về transactionId + fileId để frontend polling
     */
    public function mySignDocument(Request $request, Proposal $proposal)
    {
        $this->authorize('approve_proposal');

        $user = Auth::user();

        $stepId = $request->input('step_id');
        $activeStep = $stepId ? $proposal->steps()->find($stepId) : $proposal->steps()->where('status', 'pending')->orderBy('step_level')->first();
        if (!$activeStep) {
            $activeStep = $proposal->steps()->orderBy('step_level', 'desc')->first();
        }
        if (!$activeStep) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy bước duyệt nào hợp lệ.']);
        }

        $approval = $activeStep->approvals()->where('user_id', $user->id)->first();
        if (!$approval && !$user->hasPermission('approve_proposal')) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền thao tác trên file của bước này.']);
        }

        // Tìm file PDF đầu tiên
        $attachments = $activeStep->attachment_files ?? [];
        $pdfFilename = null;
        $pdfIndex    = -1;
        foreach ($attachments as $idx => $f) {
            if (is_string($f) && strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf') {
                $pdfFilename = $f;
                $pdfIndex    = $idx;
                break;
            }
        }

        if (!$pdfFilename || !Storage::disk('private')->exists($pdfFilename)) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy file PDF đính kèm. Vui lòng tải lên ít nhất 1 file PDF để thực hiện ký số.'
            ]);
        }

        try {
            $mySign = app(\App\Services\MySignService::class)->setUser($user);

            // Copy file PDF sang temp để truyền path cho service
            $pdfContent  = Storage::disk('private')->get($pdfFilename);
            $tmpPath     = sys_get_temp_dir() . '/' . $pdfFilename;
            file_put_contents($tmpPath, $pdfContent);

            // Vị trí chữ ký từ request
            $page       = (int)   $request->input('page', 1);
            $ptX        = (int)   $request->input('x', 10);
            $ptY        = (int)   $request->input('y', 10);
            $pageHeight = (float) $request->input('pageHeight', 842); // A4 = 842pt
            $stampH     = 70; // heightRectangle

            // MySign dùng bottom-left origin (PDF chuẩn), canvas dùng top-left → cần đảo Y
            $apiY = max(0, (int)($pageHeight - $ptY - $stampH));

            $display = [[
                'page'            => $page,
                'coorX'           => $ptX,
                'coorY'           => $apiY,   // ← đã convert sang bottom-left
                'widthRectangle'  => 300,
                'heightRectangle' => $stampH,
            ]];

            // Ảnh chữ ký của user (nếu có)
            $signatureImageBase64 = null;
            if ($user->mysign_signature_image && Storage::disk('public')->exists($user->mysign_signature_image)) {
                $signatureImageBase64 = base64_encode(Storage::disk('public')->get($user->mysign_signature_image));
            }

            // Kéo renderType từ request, mặc định 2 (Text + Logo trái)
            $renderType = (int) $request->input('renderType', 2);

            // Upload file → fileId
            $fileId = $mySign->uploadFile($tmpPath, $proposal->title, $display, $signatureImageBase64, $renderType);

            // Gửi yêu cầu ký → transactionId
            $transactionId = $mySign->signFile($fileId, 'Ký duyệt: ' . $proposal->title);

            @unlink($tmpPath);

            return response()->json([
                'success'        => true,
                'file_id'        => $fileId,
                'transaction_id' => $transactionId,
                'pdf_index'      => $pdfIndex,
                'pdf_filename'   => $pdfFilename,
                'step_id'        => $activeStep->id,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('MySign upload/sign error', [
                'msg'   => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Viettel MySign - Bước 2: Poll trạng thái ký (frontend gọi mỗi 3s)
     * Khi status = "1" → download file đã ký, lưu storage, cập nhật proposal
     */
    public function mySignPoll(Request $request, Proposal $proposal)
    {
        $this->authorize('approve_proposal');

        $user          = Auth::user();
        $transactionId = $request->query('transaction_id');
        $fileId        = (int) $request->query('file_id');
        $pdfFilename   = $request->query('pdf_filename');
        $pdfIndex      = (int) $request->query('pdf_index', -1);
        $stepId        = $request->query('step_id');

        if (!$transactionId || !$fileId) {
            return response()->json(['success' => false, 'message' => 'Thiếu tham số transaction_id hoặc file_id.']);
        }

        $activeStep = $stepId ? $proposal->steps()->find($stepId) : $proposal->steps()->where('status', 'pending')->orderBy('step_level')->first();
        if (!$activeStep) {
            $activeStep = $proposal->steps()->orderBy('step_level', 'desc')->first();
        }
        if (!$activeStep) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy bước duyệt nào hợp lệ.']);
        }

        try {
            $mySign = app(\App\Services\MySignService::class)->setUser($user);

            $signResult  = $mySign->checkSignStatus($transactionId);
            $status      = (string)($signResult['status'] ?? '');
            $description = mb_strtolower($signResult['description'] ?? '');

            // ── Ký thành công ──────────────────────────────────────────────
            if ($status === '1') {
                // Kiểm tra file đã được apply chữ ký chưa
                $fileStatus = $mySign->checkFileStatus($fileId, $transactionId);
                if (($fileStatus['status'] ?? 0) !== 1) {
                    return response()->json([
                        'success' => true,
                        'status'  => 'pending',
                        'message' => 'Đang xử lý file sau ký...',
                    ]);
                }

                // Download file đã ký và ghi đè
                $pdfContent     = $mySign->downloadSignedFile($fileId);
                $signedFilename = $pdfFilename ?: 'proposal_' . time() . '.pdf';
                
                Storage::disk('private')->put($signedFilename, $pdfContent);

                // Nếu là overwrite thì tên không đổi, không cần append mảng mới
                // nhưng để cẩn thận, ta update array values:
                $attachments = $activeStep->attachment_files ?? [];
                if ($pdfIndex >= 0 && isset($attachments[$pdfIndex])) {
                    $attachments[$pdfIndex] = $signedFilename;
                } else {
                    $attachments[] = $signedFilename;
                    $activeStep->attachment_files = array_values(array_unique($attachments));
                    $activeStep->save();
                }

                return response()->json([
                    'success'     => true,
                    'status'      => 'done',
                    'message'     => 'Ký số thành công! File đã được lưu.',
                    'signed_file' => route('admin.media.serve', ['filename' => $signedFilename]),
                ]);
            }

            // ── Vẫn đang chờ người dùng xác nhận ──────────────────────────
            // status "0" hoặc description cho thấy đang chờ
            $isWaiting = $status === '0'
                || str_contains($description, 'chờ')
                || str_contains($description, 'xác nhận')
                || str_contains($description, 'waiting')
                || str_contains($description, 'pending')
                || $status === '';   // chưa có status rõ ràng

            if ($isWaiting) {
                return response()->json([
                    'success' => true,
                    'status'  => 'pending',
                    'message' => 'Đang chờ xác nhận trên điện thoại...',
                ]);
            }

            // ── Thực sự thất bại (từ chối, hết hạn, lỗi server...) ────────
            return response()->json([
                'success' => false,
                'status'  => 'failed',
                'message' => 'Ký số thất bại: ' . ($signResult['description'] ?? 'Lỗi không xác định') . ' (status=' . $status . ')',
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('MySign poll error', ['msg' => $e->getMessage()]);
            return response()->json(['success' => false, 'status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Receive a PDF (with QR already embedded client-side via pdf-lib.js),
     * save it to private storage, and update the proposal's attachment_files.
     */
    public function embedQr(Request $request, Proposal $proposal)
    {
        $this->authorize('approve_proposal');

        $request->validate([
            'pdf_base64'    => 'required|string',
            'original_filename' => 'nullable|string',
            'pdf_index'     => 'nullable|integer',
        ]);

        try {
            $stepId     = $request->input('step_id');
            $activeStep = $stepId ? $proposal->steps()->find($stepId) : $proposal->steps()->orderBy('step_level', 'desc')->first();

            if (!$activeStep) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy bước duyệt nào hợp lệ.']);
            }

            // Decode base64 PDF sent from client (pdf-lib modified bytes)
            $pdfBytes = base64_decode($request->input('pdf_base64'));

            if (!$pdfBytes || strlen($pdfBytes) < 100) {
                return response()->json(['success' => false, 'message' => 'Dữ liệu PDF không hợp lệ.'], 422);
            }

            // Ghi đè file cũ
            $originalFilename = $request->input('original_filename', 'proposal_' . time() . '.pdf');
            
            Storage::disk('private')->put($originalFilename, $pdfBytes);

            // Cập nhật mảng attachment (giữ nguyên nếu overwrite)
            $attachments = $activeStep->attachment_files ?? [];
            $pdfIndex    = $request->input('pdf_index', -1);

            if ($pdfIndex >= 0 && isset($attachments[$pdfIndex])) {
                $attachments[$pdfIndex] = $originalFilename;
            } else {
                $attachments[] = $originalFilename;
                $activeStep->attachment_files = array_values(array_unique($attachments));
                $activeStep->save();
            }

            return response()->json([
                'success'      => true,
                'message'      => 'Đã dán QR vào PDF thành công!',
                'new_filename' => $originalFilename,
                'step_id'      => $activeStep->id,
                'new_url'      => route('admin.media.serve', ['filename' => $originalFilename]),
                'attachment_urls' => collect($activeStep->attachment_files)->map(fn($f) => [
                    'filename' => $f,
                    'url'      => route('admin.media.serve', ['filename' => $f]),
                ])->values()->toArray(),
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('embedQr error', ['msg' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Proposal $proposal)
    {
        $this->authorize('delete_proposal');

        // Delete attached files from storage
        foreach ($proposal->steps as $step) {
            foreach ($step->attachment_files ?? [] as $filename) {
                if (Storage::disk('private')->exists($filename)) {
                    Storage::disk('private')->delete($filename);
                }
            }
        }

        $proposal->delete();

        return redirect()->back()->with('success', 'Đã xóa đề xuất thành công.');
    }
}
