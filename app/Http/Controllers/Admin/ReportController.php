<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaintenanceCheck;
use App\Models\User;
use App\Models\Elevator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();
        $month = $request->input('month', $now->month);
        $year = $request->input('year', $now->year);
        
        $date = Carbon::createFromDate($year, $month, 1);
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        // -----------------------------------------------------
        // TAB 1: TỔNG QUAN
        // -----------------------------------------------------
        
        // 1. Phân loại lỗi thường gặp (Pie Chart) -> Mọi thời đại hoặc theo tháng (ở đây tính trong tháng)
        $faultRecords = MaintenanceCheck::where('task_type', 'repair')
            ->whereNotNull('fault_category')
            ->whereBetween('check_date', [$startOfMonth, $endOfMonth])
            ->pluck('fault_category')
            ->toArray();

        $flatFaults = [];
        foreach ($faultRecords as $faultList) {
            if (is_array($faultList)) {
                $flatFaults = array_merge($flatFaults, $faultList);
            } elseif (is_string($faultList)) {
                $flatFaults[] = $faultList;
            }
        }
        
        $faultDistribution = array_count_values($flatFaults);
        $faultStats = [
            'labels' => array_keys($faultDistribution),
            'data' => array_values($faultDistribution),
        ];

        // 2. Hiệu suất Kỹ thuật viên (Horizontal Bar Chart)
        $staffStats = User::whereHas('role', function($q) {
                // Fetch staff/tech users (Assuming 'staff' or similar role, or users who have executed tasks)
            })->orWhereIn('id', MaintenanceCheck::pluck('user_id')->toArray())->get();
        
        // We will just fetch all maintenance checks in this month and group by staff
        $tasksInMonth = MaintenanceCheck::whereBetween('check_date', [$startOfMonth, $endOfMonth])->get();

        $staffPerformance = [];
        foreach ($tasksInMonth as $task) {
            $staffIds = is_array($task->staff_ids) ? $task->staff_ids : [$task->user_id];
            
            foreach ($staffIds as $sid) {
                if (!$sid) continue;
                if (!isset($staffPerformance[$sid])) {
                    $staffObj = User::find($sid);
                    $staffPerformance[$sid] = [
                        'name' => $staffObj ? $staffObj->name : 'Unknown',
                        'completed' => 0,
                        'in_progress' => 0,
                        'pending' => 0,
                        'total' => 0,
                        'rating' => rand(45, 50) / 10 // Mock rating 4.5 -> 5.0
                    ];
                }
                
                $staffPerformance[$sid]['total']++;
                if ($task->status == 'completed') $staffPerformance[$sid]['completed']++;
                elseif ($task->status == 'in_progress') $staffPerformance[$sid]['in_progress']++;
                else $staffPerformance[$sid]['pending']++;
            }
        }
        
        // Sort performance by completed descending
        usort($staffPerformance, function($a, $b) {
            return $b['completed'] <=> $a['completed'];
        });

        $topStaffLabels = array_column(array_slice($staffPerformance, 0, 10), 'name');
        $topStaffData = array_column(array_slice($staffPerformance, 0, 10), 'completed');

        // -----------------------------------------------------
        // TAB 2: DANH SÁCH BẢO TRÌ (Thang máy cần bảo trì)
        // -----------------------------------------------------
        
        // Load all elevators with building + latest periodic check
        $allElevators = Elevator::with(['building', 'branch'])
            ->get()
            ->map(function($elevator) {
                // Lần bảo trì cuối: bản ghi completed mới nhất
                $lastCompleted = MaintenanceCheck::where('elevator_id', $elevator->id)
                    ->where('status', 'completed')
                    ->whereNotNull('check_date')
                    ->orderBy('check_date', 'desc')
                    ->first();

                $elevator->last_check_date = $lastCompleted
                    ? Carbon::parse($lastCompleted->check_date)
                    : null;

                // Lịch bảo trì kế tiếp: ưu tiên lịch chưa hoàn thành (pending/in_progress)
                // Nếu không có → dùng maintenance_deadline từ bảng thang máy
                $pendingCheck = MaintenanceCheck::where('elevator_id', $elevator->id)
                    ->where('task_type', 'periodic')
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->whereNotNull('check_date')
                    ->orderBy('check_date', 'asc')
                    ->first();

                if ($pendingCheck) {
                    $elevator->next_check_date = Carbon::parse($pendingCheck->check_date);
                    $elevator->source = 'check';
                } elseif ($elevator->maintenance_deadline) {
                    $elevator->next_check_date = Carbon::parse($elevator->maintenance_deadline);
                    $elevator->source = 'deadline';
                } else {
                    $elevator->next_check_date = null;
                    $elevator->source = null;
                }

                return $elevator;
            });

        // Filter: chỉ thang máy đến hạn trong tháng VÀ chưa có bảo trì hoàn thành trong tháng đó
        $dueElevators = $allElevators->filter(function($elevator) use ($startOfMonth, $endOfMonth) {
            if (!$elevator->next_check_date) return false;
            if (!$elevator->next_check_date->between($startOfMonth, $endOfMonth)) return false;

            // Loại bỏ nếu đã có bảo trì hoàn thành trong tháng này
            $hasCompleted = MaintenanceCheck::where('elevator_id', $elevator->id)
                ->where('status', 'completed')
                ->whereBetween('check_date', [$startOfMonth, $endOfMonth])
                ->exists();

            return !$hasCompleted;
        })->sortBy('next_check_date');

        return view('admin.reports.index', compact(
            'date',
            'month',
            'year',
            'faultStats',
            'staffPerformance',
            'topStaffLabels',
            'topStaffData',
            'dueElevators'
        ));
    }
}
