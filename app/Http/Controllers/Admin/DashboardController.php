<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Elevator;
use App\Models\Incident;
use App\Models\MaintenanceCheck;
use App\Models\Building;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        $this->authorize('view_dashboard');

        // 1. Thống kê vận hành (Dashboard Tổng Quan)
        $totalElevators = Elevator::count();
        $activeIncidentsCount = Elevator::where('status', 'error')->count();
        $ongoingMaintenanceCount = Elevator::where('status', 'maintenance')->count();
        $maintenanceDueCount = Elevator::whereBetween('maintenance_deadline', [
            now()->startOfDay(), 
            now()->addDays(30)->endOfDay()
        ])->count();
        $totalBuildings = Building::count();

        // 2. Dữ liệu biểu đồ Xu hướng bảo trì (6 tháng gần nhất)
        $maintenanceTrend = [];
        $maintenanceLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $maintenanceLabels[] = 'Tháng ' . $date->month;
            
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();
            
            $maintenanceTrend[] = MaintenanceCheck::whereBetween('check_date', [$startOfMonth, $endOfMonth])->count();
        }

        // 3. Dữ liệu biểu đồ Trạng thái thiết bị
        $faultCount = Elevator::where('status', 'error')->count();
        $maintenanceCount = Elevator::where('status', 'maintenance')->count();
        $activeCount = Elevator::where('status', 'active')->count();

        // 4. Dữ liệu biểu đồ Thống kê sự cố (6 tháng gần nhất)
        $incidentTrends = [];
        $incidentLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $incidentLabels[] = 'Tháng ' . $date->month;
            
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            // Thống kê dựa trên ngày báo cáo (reported_at) để khớp với thực tế
            $incidentTrends[] = Incident::whereBetween('reported_at', [$startOfMonth, $endOfMonth])->count();
        }

        // 5. Lịch nhắc bảo trì chu kỳ (Top 10 sắp đến hạn)
        $maintenanceReminders = Elevator::with('building')
            ->orderBy('maintenance_deadline', 'asc')
            ->whereNotNull('maintenance_deadline')
            ->take(10)
            ->get()
            ->map(function($item) {
                $isUpcoming = $item->maintenance_deadline->isFuture();
                return [
                    'code' => $item->code,
                    'contact' => $item->building->contact_name ?? $item->customer_name ?? 'Chưa xác định',
                    'deadline' => $item->maintenance_deadline->format('d/m/Y'),
                    'cycle' => $item->cycle_days ?? 30,
                    'label' => $isUpcoming ? 'Sắp đến hạn:' : 'Đến hạn:',
                    'color' => $isUpcoming ? '#d69e2e' : '#e53e3e' // Sử dụng vàng sậm hơn để dễ đọc trên nền trắng
                ];
            });

        // 6. Khách hàng sắp đến hạn bảo trì (Top 10 - Phân loại ưu tiên)
        $dueCustomers = Elevator::with('building')
            ->orderBy('maintenance_deadline', 'asc')
            ->whereNotNull('maintenance_deadline')
            ->take(10)
            ->get()
            ->map(function($item) {
                $daysDiff = now()->startOfDay()->diffInDays($item->maintenance_deadline->startOfDay(), false);
                
                $priority = 'Low';
                $badgeClass = 'bg-secondary';
                if ($daysDiff <= 0) {
                    $priority = 'Urgent';
                    $badgeClass = 'bg-danger text-white';
                } elseif ($daysDiff <= 5) {
                    $priority = 'High';
                    $badgeClass = 'bg-warning text-dark';
                } elseif ($daysDiff <= 15) {
                    $priority = 'Medium';
                    $badgeClass = 'bg-primary text-white';
                }

                return [
                    'building_name' => $item->building->name ?? 'N/A',
                    'customer_name' => $item->building->customer_name ?? $item->customer_name ?? 'N/A',
                    'deadline' => $item->maintenance_deadline->format('d/m/Y'),
                    'days_left' => $daysDiff,
                    'priority' => $priority,
                    'badge_class' => $badgeClass
                ];
            });

        // 7. Dữ liệu biểu đồ Doanh thu (15 ngày gần nhất)
        $orderTrendData = [];
        $orderLabels = [];
        for ($i = 14; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $orderLabels[] = $date->format('d/m');
            
            $orderTrendData[] = Order::whereDate('created_at', $dateStr)->sum('total_amount');
        }

        // 8. Đơn hàng gần đây (Top 10)
        $recentOrders = Order::with('building')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($item) {
                return [
                    'code' => $item->code,
                    'building_name' => $item->building->name ?? 'N/A',
                    'total_amount' => number_format($item->total_amount, 0, ',', '.') . ' ₫',
                    'status' => $item->status,
                    'status_label' => match($item->status) {
                        'completed' => 'Hoàn thành',
                        'pending' => 'Chờ xử lý',
                        'cancelled' => 'Đã hủy',
                        default => $item->status
                    },
                    'status_class' => match($item->status) {
                        'completed' => 'bg-light-green text-green',
                        'pending' => 'bg-light-orange text-orange',
                        'cancelled' => 'bg-light-red text-red',
                        default => 'bg-light-secondary'
                    }
                ];
            });

        // 9. Banner cảnh báo cho Admin - Thang máy đến hạn/quá hạn bảo trì
        // Exclude elevators unless they have an active (pending/in_progress) maintenance check
        $overdueElevators = Elevator::with('building')
            ->whereNotNull('maintenance_deadline')
            ->whereDate('maintenance_deadline', '<=', now())
            ->whereHas('maintenanceChecks', function($q) {
                $q->whereIn('status', ['pending', 'in_progress']); // Only pending and in_progress as requested
            })
            ->orderBy('maintenance_deadline', 'asc')
            ->get();

        // 10. Banner lịch bảo trì cho Nhân viên - 7 ngày tới
        $userId = auth()->id();
        $staffUpcomingMaintenance = MaintenanceCheck::with('elevator.building')
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereJsonContains('staff_ids', (string) $userId)
                  ->orWhereJsonContains('staff_ids', $userId);
            })
            ->whereBetween('check_date', [now()->startOfDay(), now()->addDays(7)->endOfDay()])
            ->whereIn('status', ['pending', 'in_progress']) // DO NOT SHOW completed or canceled
            ->orderBy('check_date', 'asc')
            ->take(5)
            ->get();

        return view('admin.dashboard', [
            'totalElevators' => $totalElevators,
            'activeIncidents' => $activeIncidentsCount,
            'ongoingMaintenance' => $ongoingMaintenanceCount,
            'maintenanceDue' => $maintenanceDueCount,
            'totalBuildings' => $totalBuildings,
            'maintenanceLabels' => $maintenanceLabels,
            'maintenanceTrend' => $maintenanceTrend,
            'deviceStats' => [
                'active' => $activeCount,
                'maintenance' => $maintenanceCount,
                'fault' => $faultCount
            ],
            'incidentLabels' => $incidentLabels,
            'incidentTrends' => $incidentTrends,
            'maintenanceReminders' => $maintenanceReminders,
            'dueCustomers' => $dueCustomers,
            'orderLabels' => $orderLabels,
            'orderTrendData' => $orderTrendData,
            'recentOrders' => $recentOrders,
            // Banner alerts
            'overdueElevators' => $overdueElevators,
            'staffUpcomingMaintenance' => $staffUpcomingMaintenance,
        ]);
    }
}
