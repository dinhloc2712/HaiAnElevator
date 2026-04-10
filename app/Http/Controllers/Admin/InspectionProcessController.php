<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InspectionProcess;
use App\Models\InspectionStep;
use App\Models\InspectionStepItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InspectionProcessController extends Controller
{
    public function index()
    {
        $this->authorize('view_inspection_process');
        $processes = InspectionProcess::with(['steps.items'])->get();
        $users = \App\Models\User::select('id', 'name')->get();
        return view('admin.inspection_processes.index', compact('processes', 'users'));
    }

    public function store(Request $request)
    {
        $this->authorize('create_inspection_process');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $process = InspectionProcess::create($validated);

        if ($request->wantsJson()) {
            return response()->json($process);
        }

        return redirect()->route('admin.inspection-processes.index')->with('success', 'Quy trình mới đã được tạo.');
    }

    public function update(Request $request, InspectionProcess $inspection_process)
    {
        $this->authorize('update_inspection_process');
        // This method handles saving the entire structure (Steps + Items)
        // Expecting JSON payload: { steps: [ { title, items: [ { content, is_required, field_type } ] } ] }
        
        $data = $request->validate([
            'steps' => 'nullable|array',
            'steps.*.title' => 'required|string',
            'steps.*.items' => 'nullable|array',
            'steps.*.items.*.content' => 'required|string',
            'steps.*.items.*.is_required' => 'boolean',
            'steps.*.items.*.requires_approval' => 'boolean',
            'steps.*.items.*.require_all_approvers' => 'boolean',
            'steps.*.items.*.approvers' => 'nullable|array',
            'steps.*.items.*.field_type' => 'nullable|string',
            'steps.*.items.*.formula' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Remove old steps (cascade delete items)
            $inspection_process->steps()->delete();

            if (!empty($data['steps'])) {
                foreach ($data['steps'] as $stepIndex => $stepData) {
                    $step = $inspection_process->steps()->create([
                        'title' => $stepData['title'],
                        'order_index' => $stepIndex,
                    ]);

                    if (!empty($stepData['items'])) {
                        foreach ($stepData['items'] as $itemIndex => $itemData) {
                            $step->items()->create([
                                'content' => $itemData['content'],
                                'is_required' => $itemData['is_required'] ?? true,
                                'requires_approval' => $itemData['requires_approval'] ?? false,
                                'require_all_approvers' => $itemData['require_all_approvers'] ?? false,
                                'approvers' => $itemData['approvers'] ?? null,
                                'field_type' => $itemData['field_type'] ?? 'file',
                                'formula' => $itemData['formula'] ?? null,
                                'order_index' => $itemIndex,
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return response()->json(['message' => 'Cấu hình quy trình đã được lưu thành công.', 'process' => $inspection_process->load('steps.items')]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Lỗi khi lưu: ' . $e->getMessage()], 500);
        }
    }
    
    public function destroy(InspectionProcess $inspection_process)
    {
        $this->authorize('delete_inspection_process');
        $inspection_process->delete();
        return redirect()->route('admin.inspection-processes.index')->with('success', 'Quy trình đã được xóa.');
    }
}
