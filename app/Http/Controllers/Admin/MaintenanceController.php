<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceCheck;
use App\Models\Elevator;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MaintenanceController extends Controller
{
    /**
     * Define the sections and items as seen in the paper form.
     */
    protected function getChecklistItems()
    {
        return \App\Models\MaintenanceCategory::with('items')->orderBy('sort_order')->get()
            ->mapWithKeys(function ($cat) {
                return [$cat->name => $cat->items->pluck('name', 'id')->toArray()];
            })->toArray();
    }

    protected function getSymbols()
    {
        return \App\Models\MaintenanceStatus::orderBy('sort_order')->pluck('name', 'id')->toArray();
    }

    public function index(Request $request)
    {
        $this->authorize('view_maintenance_schedule');
        
        // Automatically mark pending tasks as overdue if their scheduled date (or check date) has passed
        MaintenanceCheck::where('status', 'pending')
            ->where('check_date', '<', now()->startOfDay())
            ->whereNotNull('check_date')
            ->update(['status' => 'overdue']);

        // Base Query
        $query = MaintenanceCheck::with('elevator.building', 'staff');

        // Check permissions: if user cannot create schedule, they only see their own assigned tasks
        if (!auth()->user()->can('create_maintenance_schedule')) {
            $query->where(function($q) {
                $q->where('user_id', auth()->id())
                  ->orWhereJsonContains('staff_ids', (string)auth()->id());
            });
        }

        // Sorting
        $sort = $request->input('sort', 'asc');

        // DASHBOARD STATISTICS
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Stats Query should also be filtered by permission
        $statsQuery = MaintenanceCheck::whereBetween('check_date', [$startOfMonth, $endOfMonth]);
        if (!auth()->user()->can('create_maintenance_schedule')) {
            $statsQuery->where(function($q) {
                $q->where('user_id', auth()->id())
                  ->orWhereJsonContains('staff_ids', (string)auth()->id());
            });
        }
        $monthTasks = $statsQuery->get();

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
        
        $trendQuery = MaintenanceCheck::whereBetween('check_date', [$startOfLastMonth, $endOfLastMonth]);
        if (!auth()->user()->can('create_maintenance_schedule')) {
            $trendQuery->where(function($q) {
                $q->where('user_id', auth()->id())
                  ->orWhereJsonContains('staff_ids', (string)auth()->id());
            });
        }
        $lastMonthTotal = $trendQuery->count();
            
        $lastMonthCompleted = (clone $trendQuery)->where('status', 'completed')->count();

        $lastMonthRate = $lastMonthTotal > 0 ? round(($lastMonthCompleted / $lastMonthTotal) * 100) : 0;
        $trend = $stats['completion_rate'] - $lastMonthRate;

        $elevators = Elevator::all();
        
        $upcomingTasks = $query->orderBy('check_date', $sort)
            ->orderBy('start_time', $sort)
            ->get();

        return view('admin.maintenance.index', compact('upcomingTasks', 'elevators', 'stats', 'trend', 'sort'));
    }

    public function orders()
    {
        $this->authorize('view_maintenance_order');
        // 1. Current Month & Previous Month Stats
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        
        $startOfLastMonth = now()->subMonth()->startOfMonth();
        $endOfLastMonth = now()->subMonth()->endOfMonth();

        $currentMonthRevenue = Order::where('status', 'paid')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total_amount');

        $lastMonthRevenue = Order::where('status', 'paid')
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('total_amount');

        $revenueIncreasePercent = 0;
        if ($lastMonthRevenue > 0) {
            $revenueIncreasePercent = round((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100);
        } elseif ($currentMonthRevenue > 0) {
            $revenueIncreasePercent = 100;
        }

        // 2. Six Months Revenue Chart Data
        $chartLabels = [];
        $chartData = [];

        // Loop from 5 months ago to current month
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();
            
            $monthRevenue = Order::where('status', 'paid')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('total_amount');

            $chartLabels[] = 'T' . $monthStart->format('n'); // e.g., T10, T11
            $chartData[] = $monthRevenue;
        }

        $stats = [
            'current_month_revenue' => $currentMonthRevenue,
            'revenue_increase_percent' => $revenueIncreasePercent,
            'chart_labels' => $chartLabels,
            'chart_data' => $chartData,
        ];

        $orders = Order::with('building', 'elevator')->latest()->paginate(15);
        $buildings = \App\Models\Building::all();
        $elevators = Elevator::all();
        return view('admin.maintenance.orders', compact('orders', 'buildings', 'elevators', 'stats'));
    }

    public function storeOrder(Request $request)
    {
        $this->authorize('create_maintenance_order');
        $request->validate([
            'building_id' => 'required|exists:buildings,id',
            'elevator_id' => 'required|exists:elevators,id',
            'created_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $status = $request->has('save_draft') ? 'draft' : 'pending';

        // Auto-generate order code (e.g., HD-20260413-001)
        $datePrefix = date('Ymd');
        $latestOrder = Order::where('code', 'like', "HD-{$datePrefix}-%")->latest()->first();
        $sequence = $latestOrder ? intval(substr($latestOrder->code, -3)) + 1 : 1;
        $orderCode = "HD-{$datePrefix}-" . str_pad($sequence, 3, '0', STR_PAD_LEFT);

        // Calculate total
        $totalAmount = 0;
        foreach ($request->items as $item) {
            $totalAmount += $item['quantity'] * $item['price'];
        }

        $order = Order::create([
            'code' => $orderCode,
            'building_id' => $request->building_id,
            'elevator_id' => $request->elevator_id,
            'total_amount' => $totalAmount,
            'status' => $status,
            'created_at' => $request->created_date . ' ' . date('H:i:s'),
        ]);

        foreach ($request->items as $item) {
            $subtotal = $item['quantity'] * $item['price'];
            \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'service_name' => $item['name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'subtotal' => $subtotal,
            ]);
        }

        return redirect()->route('admin.maintenance.orders')->with('success', 'Tạo đơn bảo trì/báo giá thành công.');
    }

    public function editOrder(Order $order)
    {
        $this->authorize('update_maintenance_order');
        $order->load('items');
        $buildings = \App\Models\Building::all();
        $elevators = Elevator::all();
        return view('admin.maintenance.orders_edit', compact('order', 'buildings', 'elevators'));
    }

    public function updateOrder(Request $request, Order $order)
    {
        $this->authorize('update_maintenance_order');
        $request->validate([
            'building_id' => 'required|exists:buildings,id',
            'elevator_id' => 'required|exists:elevators,id',
            'created_date' => 'required|date',
            'status' => 'required|in:pending,paid,draft',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        // Calculate total
        $totalAmount = 0;
        foreach ($request->items as $item) {
            $totalAmount += $item['quantity'] * $item['price'];
        }

        // Keep the original time part of created_at
        $timePart = $order->created_at ? $order->created_at->format('H:i:s') : date('H:i:s');

        $order->update([
            'building_id' => $request->building_id,
            'elevator_id' => $request->elevator_id,
            'total_amount' => $totalAmount,
            'status' => $request->status,
            'created_at' => $request->created_date . ' ' . $timePart,
        ]);

        // Delete old items and insert new ones
        $order->items()->delete();

        foreach ($request->items as $item) {
            $subtotal = $item['quantity'] * $item['price'];
            \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'service_name' => $item['name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'subtotal' => $subtotal,
            ]);
        }

        return redirect()->route('admin.maintenance.orders')->with('success', 'Cập nhật đơn bảo trì/báo giá thành công.');
    }



    public function create(Request $request)
    {
        $this->authorize('create_maintenance_schedule');
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
        $this->authorize('create_maintenance_schedule');
        $request->validate([
            'elevator_id' => 'required|exists:elevators,id',
            'check_date'  => 'required|date',
            'results'     => 'nullable|array',
            'staff_ids'   => 'nullable|array',
        ]);

        $staffNamesStr = null;
        if ($request->staff_ids) {
            $staffNamesStr = User::whereIn('id', $request->staff_ids)->pluck('name')->implode(', ');
        }

        MaintenanceCheck::create([
            'elevator_id'     => $request->elevator_id,
            'user_id'         => auth()->id(),
            'status'          => 'pending',
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
        $elevator = Elevator::find($request->elevator_id);
        if (($request->task_type ?? 'periodic') == 'periodic') {
            $newDeadline = now()->addDays($elevator->cycle_days ?? 30);
            
            // Check if new deadline exceeds contract end date
            if ($elevator->maintenance_end_date && $newDeadline->gt(Carbon::parse($elevator->maintenance_end_date))) {
                // If it exceeds, CLEAR the deadline
                $elevator->maintenance_deadline = null;
                $elevator->status = 'active';
            } else {
                $elevator->maintenance_deadline = $newDeadline;
                $elevator->status = 'active';
            }
            $elevator->save();
        } else {
            // Even if it's repair, if it's completed, set back to active
            $elevator->status = 'active';
            $elevator->save();
        }

        return redirect()->route('admin.maintenance.index')
            ->with('success', 'Phiếu bảo bảo dưỡng đã được lưu thành công.');
    }

    public function edit(MaintenanceCheck $maintenance)
    {
        $this->authorize('update_maintenance_schedule');
        $elevators = Elevator::all();
        $selectedElevator = $maintenance->elevator;
        $sections = $this->getChecklistItems();
        $symbols = $this->getSymbols();
        $staffs = User::all(); // Assuming all users can be technical staff for now

        return view('admin.maintenance.edit', compact('maintenance', 'elevators', 'selectedElevator', 'sections', 'symbols', 'staffs'));
    }

    public function update(Request $request, MaintenanceCheck $maintenance)
    {
        $this->authorize('update_maintenance_schedule');
        $request->validate([
            'status'      => 'nullable|in:pending,overdue,in_progress,completed',
            'task_type'   => 'required|in:periodic,repair',
            'check_date'  => 'required|date',
            'results'     => 'nullable|array',
            'staff_ids'   => 'nullable|array',
        ]);

        $staffNamesStr = null;
        if ($request->staff_ids) {
            $staffNamesStr = User::whereIn('id', $request->staff_ids)->pluck('name')->implode(', ');
        }

        $status = $request->status ?? $maintenance->status;
        if ($request->action == 'complete') {
            $status = 'completed';
        } elseif ($request->action == 'save') {
            $status = 'in_progress';
        }

        $maintenance->update([
            'status'          => $status,
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
        if ($status == 'completed') {
            $elevator = $maintenance->elevator;
            $elevator->status = 'active';
            
            // Only update maintenance deadline if it is a periodic maintenance
            if ($request->task_type == 'periodic') {
                $newDeadline = now()->addDays($elevator->cycle_days ?? 30);
                
                // Check if new deadline exceeds contract end date
                if ($elevator->maintenance_end_date && $newDeadline->gt(Carbon::parse($elevator->maintenance_end_date))) {
                    // CLEAR the deadline if it exceeds the contract end date
                    $elevator->maintenance_deadline = null;
                } else {
                    $elevator->maintenance_deadline = $newDeadline;
                }
            }
            
            $elevator->save();
        } elseif ($status == 'in_progress') {
            // If marked as in progress, update elevator status to maintenance
            $elevator = $maintenance->elevator;
            $elevator->status = 'maintenance';
            $elevator->save();
        }

        return redirect()->route('admin.maintenance.index')
            ->with('success', 'Cập nhật phiếu bảo trì thành công.');
    }

    public function start(MaintenanceCheck $maintenance)
    {
        $this->authorize('update_maintenance_schedule');
        
        $maintenance->update([
            'status' => 'in_progress',
            'start_time' => now()->format('H:i')
        ]);

        $elevator = $maintenance->elevator;
        $elevator->update(['status' => 'maintenance']);

        return redirect()->back()->with('success', 'Bắt đầu thực hiện công việc bảo trì.');
    }

    public function show(MaintenanceCheck $maintenance)
    {
        $this->authorize('view_maintenance_schedule');
        $sections = $this->getChecklistItems();
        $symbols = $this->getSymbols();
        return view('admin.maintenance.show', compact('maintenance', 'sections', 'symbols'));
    }

    public function export(MaintenanceCheck $maintenance)
    {
        $this->authorize('view_maintenance_schedule');
        $sections = $this->getChecklistItems();
        $symbols = $this->getSymbols();
        return view('admin.maintenance.export', compact('maintenance', 'sections', 'symbols'));
    }

    public function destroy(MaintenanceCheck $maintenance)
    {
        $this->authorize('delete_maintenance_schedule');
        $maintenance->delete();
        return redirect()->route('admin.maintenance.index')->with('success', 'Đã xóa công việc / lịch bảo trì.');
    }
    public function due()
    {
        $this->authorize('create_maintenance_schedule');
        
        $dueElevators = Elevator::with('building', 'branch')
            ->whereNotNull('maintenance_deadline')
            ->whereDate('maintenance_deadline', '<=', now()->addDays(15))
            ->whereDoesntHave('maintenanceChecks', function($q) {
                $q->whereIn('status', ['pending', 'in_progress']); 
            })
            ->orderBy('maintenance_deadline', 'asc')
            ->get();

        $staffs = User::all();
        
        return view('admin.maintenance.due', compact('dueElevators', 'staffs'));
    }

    public function bulkStore(Request $request)
    {
        $this->authorize('create_maintenance_schedule');
        $request->validate([
            'elevator_ids' => 'required|array|min:1',
            'elevator_ids.*' => 'exists:elevators,id',
            'check_date' => 'required|date',
            'staff_ids' => 'nullable|array',
        ]);

        $staffNamesStr = null;
        if ($request->staff_ids) {
            $staffNamesStr = User::whereIn('id', $request->staff_ids)->pluck('name')->implode(', ');
        }

        foreach ($request->elevator_ids as $elevatorId) {
            MaintenanceCheck::create([
                'elevator_id'     => $elevatorId,
                'user_id'         => auth()->id(),
                'status'          => 'pending',
                'task_type'       => 'periodic',
                'check_date'      => $request->check_date,
                'staff_ids'       => $request->staff_ids,
                'staff_names'     => $staffNamesStr,
                'performer_count' => count($request->staff_ids ?? []) ?: 1,
            ]);
        }

        return redirect()->route('admin.maintenance.index')
            ->with('success', 'Đã tạo lịch bảo trì hàng loạt cho ' . count($request->elevator_ids) . ' thang máy.');
    }
}
