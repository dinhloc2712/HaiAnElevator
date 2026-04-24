<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaintenanceCheck;
use App\Models\User;
use App\Models\Elevator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
class ReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_report');
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

    public function exportMaintenance(Request $request)
    {
        $this->authorize('view_report');
        $now = Carbon::now();
        $month = $request->input('month', $now->month);
        $year = $request->input('year', $now->year);
        
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $allElevators = Elevator::with(['building', 'branch'])
            ->get()
            ->map(function($elevator) {
                $lastCompleted = MaintenanceCheck::where('elevator_id', $elevator->id)
                    ->where('status', 'completed')
                    ->whereNotNull('check_date')
                    ->orderBy('check_date', 'desc')
                    ->first();

                $elevator->last_check_date = $lastCompleted ? Carbon::parse($lastCompleted->check_date) : null;

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

        $dueElevators = $allElevators->filter(function($elevator) use ($startOfMonth, $endOfMonth) {
            if (!$elevator->next_check_date) return false;
            if (!$elevator->next_check_date->between($startOfMonth, $endOfMonth)) return false;

            $hasCompleted = MaintenanceCheck::where('elevator_id', $elevator->id)
                ->where('status', 'completed')
                ->whereBetween('check_date', [$startOfMonth, $endOfMonth])
                ->exists();

            return !$hasCompleted;
        })->sortBy('next_check_date');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', "DANH SÁCH THANG MÁY CẦN BẢO TRÌ THÁNG {$month} NĂM {$year}");
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 14],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1e3a8a']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $headers = ['Mã thang máy', 'Tòa nhà/Khách hàng', 'Chi nhánh', 'Lịch bảo trì'];
        $sheet->fromArray($headers, null, 'A2');
        $sheet->getStyle('A2:D2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '3b82f6']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $row = 3;
        foreach ($dueElevators as $elevator) {
            $sheet->setCellValue('A' . $row, $elevator->code);
            $sheet->setCellValue('B' . $row, $elevator->building ? $elevator->building->name : 'N/A');
            $sheet->setCellValue('C' . $row, $elevator->branch ? $elevator->branch->name : 'N/A');
            $sheet->setCellValue('D' . $row, $elevator->next_check_date ? $elevator->next_check_date->format('d/m/Y') : 'N/A');
            $row++;
        }

        $lastRow = $row - 1;
        if ($lastRow >= 2) {
            $sheet->getStyle('A1:D' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
        }

        foreach(range('A','D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "danh_sach_bao_tri_{$month}_{$year}.xlsx";

        return response()->streamDownload(function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function exportStaff(Request $request)
    {
        $this->authorize('view_report');
        $now = Carbon::now();
        $month = $request->input('month', $now->month);
        $year = $request->input('year', $now->year);
        
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();

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
                        'total' => 0
                    ];
                }
                
                $staffPerformance[$sid]['total']++;
                if ($task->status == 'completed') $staffPerformance[$sid]['completed']++;
                elseif ($task->status == 'in_progress') $staffPerformance[$sid]['in_progress']++;
                else $staffPerformance[$sid]['pending']++;
            }
        }
        
        usort($staffPerformance, function($a, $b) {
            return $b['completed'] <=> $a['completed'];
        });

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', "TỔNG HỢP CÔNG VIỆC NHÂN VIÊN THÁNG {$month} NĂM {$year}");
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 14],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1e3a8a']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $headers = ['Nhân viên', 'Tổng nhiệm vụ', 'Hoàn thành', 'Đang thực hiện', 'Chờ xử lý'];
        $sheet->fromArray($headers, null, 'A2');
        $sheet->getStyle('A2:E2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '3b82f6']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $row = 3;
        foreach ($staffPerformance as $staff) {
            $sheet->setCellValue('A' . $row, $staff['name']);
            $sheet->setCellValue('B' . $row, $staff['total']);
            $sheet->setCellValue('C' . $row, $staff['completed']);
            $sheet->setCellValue('D' . $row, $staff['in_progress']);
            $sheet->setCellValue('E' . $row, $staff['pending']);
            $row++;
        }

        $lastRow = $row - 1;
        if ($lastRow >= 2) {
            $sheet->getStyle('A1:E' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
        }

        foreach(range('A','E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "cong_viec_nhan_vien_{$month}_{$year}.xlsx";

        return response()->streamDownload(function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
