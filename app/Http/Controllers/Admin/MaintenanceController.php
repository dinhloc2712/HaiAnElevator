<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceCheck;
use App\Models\Elevator;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    /**
     * Define the sections and items as seen in the paper form.
     */
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

    public function index()
    {
        // Automatically mark pending tasks as overdue if their scheduled date (or check date) has passed
        MaintenanceCheck::where('status', 'pending')
            ->where('check_date', '<', now()->startOfDay())
            ->whereNotNull('check_date')
            ->update(['status' => 'overdue']);

        // DASHBOARD STATISTICS
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $monthTasks = MaintenanceCheck::whereBetween('check_date', [$startOfMonth, $endOfMonth])->get();

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
        
        $lastMonthTotal = MaintenanceCheck::whereBetween('check_date', [$startOfLastMonth, $endOfLastMonth])->count();
            
        $lastMonthCompleted = MaintenanceCheck::where('status', 'completed')
            ->whereBetween('check_date', [$startOfLastMonth, $endOfLastMonth])
            ->count();

        $lastMonthRate = $lastMonthTotal > 0 ? round(($lastMonthCompleted / $lastMonthTotal) * 100) : 0;
        $trend = $stats['completion_rate'] - $lastMonthRate;

        $elevators = Elevator::all();
        
        $upcomingTasks = MaintenanceCheck::with('elevator.building', 'staff')
            ->orderBy('check_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        return view('admin.maintenance.index', compact('upcomingTasks', 'elevators', 'stats', 'trend'));
    }

    public function orders()
    {
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
        $order->load('items');
        $buildings = \App\Models\Building::all();
        $elevators = Elevator::all();
        return view('admin.maintenance.orders_edit', compact('order', 'buildings', 'elevators'));
    }

    public function updateOrder(Request $request, Order $order)
    {
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

    public function export(MaintenanceCheck $maintenance)
    {
        $sections = $this->getChecklistItems();
        $symbols = $this->getSymbols();
        return view('admin.maintenance.export', compact('maintenance', 'sections', 'symbols'));
    }

    public function destroy(MaintenanceCheck $maintenance)
    {
        $maintenance->delete();
        return redirect()->route('admin.maintenance.index')->with('success', 'Đã xóa công việc / lịch bảo trì.');
    }
}
