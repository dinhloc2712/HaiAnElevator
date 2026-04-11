<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceCheck;
use App\Models\Elevator;
use App\Models\User;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    /**
     * Define the sections and items as seen in the paper form.
     */
    protected function getChecklistItems()
    {
        return [
            '1. PHÒNG MÁY' => [
                1 => 'Môi trường phòng máy', 2 => 'Máy kéo', 3 => 'Phanh từ',
                4 => 'Puly dẫn hướng', 5 => 'Tủ điều khiển', 6 => 'Encoder',
                7 => 'Bộ hạn chế tốc độ', 8 => 'INTERCOM', 9 => 'Bộ cứu hộ ARD',
                10 => 'Bộ ắc quy cứu hộ', 11 => 'Dầu máy'
            ],
            '2. CABIN' => [
                12 => 'Nóc cabin', 13 => 'Puly nóc cabin', 14 => 'Shoes dẫn hướng cabin',
                15 => 'Shoes dẫn hướng đối trọng', 16 => 'Dầu ray cabin, đối trọng',
                17 => 'Hộp dầu cabin, đối trọng', 18 => 'Mô tơ cửa',
                19 => 'Phanh hãm sự cố vượt tốc', 20 => 'Công tắc an toàn', 21 => 'Cửa cabin'
            ],
            '3. CỬA TẦNG' => [
                22 => 'Nút gọi tầng', 23 => 'Hiển thị', 24 => 'Khóa liên động',
                25 => 'Cánh cửa', 26 => 'Cáp mềm', 27 => 'Công tắc giới hạn', 28 => 'Guốc cửa tầng'
            ],
            '4. GIẾNG THANG' => [
                29 => 'Cáp tải', 30 => 'Cáp GOV'
            ],
            '5. BUỒNG THANG' => [
                31 => 'Môi trường trong cabin', 32 => 'Hiển thị số', 33 => 'Đèn, quạt',
                34 => 'Cảm biến hồng ngoại', 35 => 'Độ bằng tầng', 36 => 'Nút bấm trong cabin'
            ],
            '6. HỐ THANG' => [
                38 => 'Môi trường hố thang', 39 => 'Công tắc an toàn',
                40 => 'K/cách đối trọng đến giảm chấn'
            ],
        ];
    }

    protected function getSymbols()
    {
        return [
            'Δ' => 'Bình thường',
            '√' => 'Đã kiểm tra, bảo trì và hiệu chỉnh',
            '#' => 'Đã thay thế',
            'X' => 'Đã sửa chữa, đại tu',
            'A' => 'Đang chờ thay thế, bổ sung',
            '/' => 'Không sử dụng',
            'K' => 'Không có thiết bị',
        ];
    }

    public function index()
    {
        // Automatically mark pending tasks as overdue if their scheduled date (or check date) has passed
        MaintenanceCheck::where('status', 'pending')
            ->where(function($query) {
                $query->where(function($q) {
                    $q->whereNotNull('scheduled_date')
                      ->where('scheduled_date', '<', now()->startOfDay());
                })->orWhere(function($q) {
                    $q->whereNull('scheduled_date')
                      ->whereNotNull('check_date')
                      ->where('check_date', '<', now()->startOfDay());
                });
            })
            ->update(['status' => 'overdue']);

        // DASHBOARD STATISTICS
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $monthTasks = MaintenanceCheck::where(function($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('scheduled_date', [$startOfMonth, $endOfMonth])
                  ->orWhereBetween('check_date', [$startOfMonth, $endOfMonth]);
            })->get();

        $stats = [
            'completed' => $monthTasks->where('status', 'completed')->count(),
            'in_progress' => $monthTasks->where('status', 'in_progress')->count(),
            'pending' => $monthTasks->where('status', 'pending')->count(),
            'overdue' => $monthTasks->where('status', 'overdue')->count(),
            'total' => $monthTasks->count(),
        ];

        $stats['completion_rate'] = $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0;

        // Previous month for trend
        $startOfLastMonth = now()->subMonth()->startOfMonth();
        $endOfLastMonth = now()->subMonth()->endOfMonth();
        
        $lastMonthTotal = MaintenanceCheck::where(function($q) use ($startOfLastMonth, $endOfLastMonth) {
                $q->whereBetween('scheduled_date', [$startOfLastMonth, $endOfLastMonth])
                  ->orWhereBetween('check_date', [$startOfLastMonth, $endOfLastMonth]);
            })->count();
            
        $lastMonthCompleted = MaintenanceCheck::where('status', 'completed')
            ->where(function($q) use ($startOfLastMonth, $endOfLastMonth) {
                $q->whereBetween('scheduled_date', [$startOfLastMonth, $endOfLastMonth])
                  ->orWhereBetween('check_date', [$startOfLastMonth, $endOfLastMonth]);
            })->count();

        $lastMonthRate = $lastMonthTotal > 0 ? round(($lastMonthCompleted / $lastMonthTotal) * 100) : 0;
        $trend = $stats['completion_rate'] - $lastMonthRate;

        $elevators = Elevator::all();
        
        $upcomingTasks = MaintenanceCheck::with('elevator.building', 'staff')
            ->orderBy('scheduled_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
            
        return view('admin.maintenance.index', compact('upcomingTasks', 'elevators', 'stats', 'trend'));
    }

    public function schedule(Request $request)
    {
        $request->validate([
            'elevator_id' => 'required|exists:elevators,id',
            'task_type' => 'required|in:periodic,repair',
            'scheduled_date' => 'required|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'staff_ids' => 'nullable|array'
        ]);

        $staffNamesStr = null;
        if ($request->staff_ids) {
            $staffNamesStr = User::whereIn('id', $request->staff_ids)->pluck('name')->implode(', ');
        }

        MaintenanceCheck::create([
            'elevator_id' => $request->elevator_id,
            'user_id' => auth()->id(),
            'status' => 'pending',
            'task_type' => $request->task_type,
            'scheduled_date' => $request->scheduled_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'staff_ids' => $request->staff_ids,
            'staff_names' => $staffNamesStr
        ]);

        return redirect()->route('admin.maintenance.index')->with('success', 'Đã tạo lịch bảo trì mới.');
    }

    public function create(Request $request)
    {
        $elevators = Elevator::all();
        $selectedElevator = null;
        if ($request->has('elevator_id')) {
            $selectedElevator = Elevator::with('building')->find($request->elevator_id);
        }
        
        $sections = $this->getChecklistItems();
        $symbols = $this->getSymbols();
        $staffs = User::all();
        
        return view('admin.maintenance.create', compact('elevators', 'selectedElevator', 'sections', 'symbols', 'staffs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'elevator_id' => 'required|exists:elevators,id',
            'check_date'  => 'required|date',
            'results'     => 'required|array',
            'staff_ids'   => 'nullable|array',
        ]);

        $staffNamesStr = null;
        if ($request->staff_ids) {
            $staffNamesStr = User::whereIn('id', $request->staff_ids)->pluck('name')->implode(', ');
        }

        MaintenanceCheck::create([
            'elevator_id'     => $request->elevator_id,
            'user_id'         => auth()->id(),
            'status'          => 'completed',
            'task_type'       => $request->task_type ?? 'periodic',
            'check_date'      => $request->check_date,
            'results'         => $request->results,
            'evaluation'      => $request->evaluation,
            'staff_ids'       => $request->staff_ids,
            'staff_names'     => $staffNamesStr,
            'performer_count' => $request->performer_count ?? 1,
            'start_time'      => $request->start_time,
            'end_time'        => $request->end_time,
            'notes'           => $request->notes,
        ]);

        // Update elevator maintenance deadline & status
        if (($request->task_type ?? 'periodic') == 'periodic') {
            $elevator = Elevator::find($request->elevator_id);
            $elevator->maintenance_deadline = now()->addDays($elevator->cycle_days ?? 30);
            $elevator->status = 'active';
            $elevator->save();
        } else {
            // Even if it's repair, if it's completed, set back to active
            $elevator = Elevator::find($request->elevator_id);
            $elevator->status = 'active';
            $elevator->save();
        }

        return redirect()->route('admin.maintenance.index')
            ->with('success', 'Phiếu bảo bảo dưỡng đã được lưu thành công.');
    }

    public function edit(MaintenanceCheck $maintenance)
    {
        $elevators = Elevator::all();
        $selectedElevator = $maintenance->elevator;
        $sections = $this->getChecklistItems();
        $symbols = $this->getSymbols();
        $staffs = User::all(); // Assuming all users can be technical staff for now

        return view('admin.maintenance.edit', compact('maintenance', 'elevators', 'selectedElevator', 'sections', 'symbols', 'staffs'));
    }

    public function update(Request $request, MaintenanceCheck $maintenance)
    {
        $request->validate([
            'status'      => 'required|in:pending,overdue,in_progress,completed',
            'task_type'   => 'required|in:periodic,repair',
            'check_date'  => 'required|date',
            'results'     => 'nullable|array',
            'staff_ids'   => 'nullable|array',
        ]);

        $staffNamesStr = null;
        if ($request->staff_ids) {
            $staffNamesStr = User::whereIn('id', $request->staff_ids)->pluck('name')->implode(', ');
        }

        $maintenance->update([
            'status'          => $request->status,
            'task_type'       => $request->task_type,
            'check_date'      => $request->check_date,
            'results'         => $request->results,
            'evaluation'      => $request->evaluation,
            'staff_ids'       => $request->staff_ids,
            'staff_names'     => $staffNamesStr,
            'performer_count' => $request->performer_count ?? 1,
            'start_time'      => $request->start_time,
            'end_time'        => $request->end_time,
            'notes'           => $request->notes,
        ]);

        // If marked as completed, update elevator status to active
        if ($request->status == 'completed') {
            $elevator = $maintenance->elevator;
            $elevator->status = 'active';
            
            // Only update maintenance deadline if it is a periodic maintenance
            if ($request->task_type == 'periodic') {
                $elevator->maintenance_deadline = now()->addDays($elevator->cycle_days ?? 30);
            }
            
            $elevator->save();
        } elseif ($request->status == 'in_progress') {
            // If marked as in progress, update elevator status to maintenance
            $elevator = $maintenance->elevator;
            $elevator->status = 'maintenance';
            $elevator->save();
        }

        return redirect()->route('admin.maintenance.index')
            ->with('success', 'Đã hoàn thành công việc bảo trì.');
    }

    public function show(MaintenanceCheck $maintenance)
    {
        $sections = $this->getChecklistItems();
        $symbols = $this->getSymbols();
        return view('admin.maintenance.show', compact('maintenance', 'sections', 'symbols'));
    }

    public function destroy(MaintenanceCheck $maintenance)
    {
        $maintenance->delete();
        return redirect()->route('admin.maintenance.index')->with('success', 'Đã xóa công việc / lịch bảo trì.');
    }
}
