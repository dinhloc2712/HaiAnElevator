<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Elevator;
use App\Models\Building;
use App\Models\Branch;
use Illuminate\Http\Request;

class ElevatorController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_elevator');

        $query = Elevator::with(['building', 'branch']);

        // Quick Search (keep existing)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('province', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%");
            });
        }

        // Advanced Filters
        if ($request->filled('code')) {
            $query->where('code', 'like', "%{$request->code}%");
        }

        if ($request->filled('customer')) {
            $customer = $request->customer;
            $query->where(function ($q) use ($customer) {
                $q->where('customer_name', 'like', "%{$customer}%")
                  ->orWhere('customer_phone', 'like', "%{$customer}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('province')) {
            $query->where('province', $request->province);
        }

        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }

        if ($request->filled('manufacturer')) {
            $query->where('manufacturer', $request->manufacturer);
        }

        if ($request->filled('model')) {
            $query->where('model', $request->model);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Capacity Range
        if ($request->filled('capacity_from')) {
            $query->where('capacity', '>=', $request->capacity_from);
        }
        if ($request->filled('capacity_to')) {
            $query->where('capacity', '<=', $request->capacity_to);
        }

        // Created At Range
        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }
        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }

        // Maintenance Deadline Logic
        if ($request->filled('deadline_status')) {
            $today = now()->startOfDay();
            if ($request->deadline_status === 'upcoming') {
                $query->whereBetween('maintenance_deadline', [$today, $today->copy()->addDays(7)]);
            } elseif ($request->deadline_status === 'overdue') {
                $query->where('maintenance_deadline', '<', $today);
            }
        }

        // Sorting logic
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        $allowedSorts = ['maintenance_deadline', 'created_at', 'code'];
        if (!in_array($sort, $allowedSorts)) { $sort = 'created_at'; }

        $elevators = $query->orderBy($sort, $direction)->get();
        $groupedElevators = $elevators->groupBy(['province', 'district']);

        // Dashboard Statistics
        $totalElevators = Elevator::count();
        $thisMonthCount = Elevator::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $lastMonthCount = Elevator::whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->count();
        
        $growth = 0;
        if ($lastMonthCount > 0) {
            $growth = (($totalElevators - $lastMonthCount) / $lastMonthCount) * 100;
        } elseif ($totalElevators > 0) {
            $growth = 100;
        }

        $statusStats = Elevator::selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status')->toArray();
        $manufacturerStats = Elevator::selectRaw('manufacturer, count(*) as count')
            ->whereNotNull('manufacturer')
            ->groupBy('manufacturer')
            ->orderBy('count', 'desc')
            ->take(5)
            ->get();

        // Data for dropdowns
        $provinces = Elevator::distinct()->pluck('province')->sort();
        $manufacturers = Elevator::distinct()->whereNotNull('manufacturer')->pluck('manufacturer')->sort();
        $models = Elevator::distinct()->whereNotNull('model')->pluck('model')->sort();
        $types = Elevator::distinct()->whereNotNull('type')->pluck('type')->sort();

        return view('admin.elevators.index', compact(
            'groupedElevators', 
            'provinces', 
            'manufacturers', 
            'models', 
            'types',
            'totalElevators',
            'growth',
            'statusStats',
            'manufacturerStats'
        ));
    }

    public function create()
    {
        $this->authorize('create_elevator');
        $buildings = Building::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();
        return view('admin.elevators.create', compact('buildings', 'branches'));
    }

    public function store(Request $request)
    {
        $this->authorize('create_elevator');

        $validated = $request->validate([
            'code'                 => 'required|string|unique:elevators,code',
            'building_id'          => 'nullable|exists:buildings,id',
            'branch_id'            => 'nullable|exists:branches,id',
            'customer_name'        => 'nullable|string|max:255',
            'customer_phone'       => 'nullable|string|max:20',
            'province'             => 'required|string|max:255',
            'district'             => 'required|string|max:255',
            'manufacturer'         => 'nullable|string|max:255',
            'model'                => 'nullable|string|max:255',
            'type'                 => 'nullable|string|max:255',
            'capacity'             => 'nullable|string|max:255',
            'cycle_days'           => 'required|integer|min:1',
            'status'               => 'required|string|in:active,error,maintenance',
            'maintenance_deadline' => 'nullable|date',
        ]);

        Elevator::create($validated);

        return redirect()->route('admin.elevators.index')->with('success', 'Thang máy đã được thêm thành công.');
    }

    public function show(Elevator $elevator)
    {
        return redirect()->route('admin.elevators.edit', $elevator);
    }

    public function edit(Elevator $elevator)
    {
        $this->authorize('update_elevator');
        $buildings = Building::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();
        return view('admin.elevators.edit', compact('elevator', 'buildings', 'branches'));
    }

    public function update(Request $request, Elevator $elevator)
    {
        $this->authorize('update_elevator');

        $validated = $request->validate([
            'code'                 => 'required|string|unique:elevators,code,' . $elevator->id,
            'building_id'          => 'nullable|exists:buildings,id',
            'branch_id'            => 'nullable|exists:branches,id',
            'customer_name'        => 'nullable|string|max:255',
            'customer_phone'       => 'nullable|string|max:20',
            'province'             => 'required|string|max:255',
            'district'             => 'required|string|max:255',
            'manufacturer'         => 'nullable|string|max:255',
            'model'                => 'nullable|string|max:255',
            'type'                 => 'nullable|string|max:255',
            'capacity'             => 'nullable|string|max:255',
            'cycle_days'           => 'required|integer|min:1',
            'status'               => 'required|string|in:active,error,maintenance',
            'maintenance_deadline' => 'nullable|date',
        ]);

        $elevator->update($validated);

        return redirect()->route('admin.elevators.index')->with('success', 'Cập nhật thang máy thành công.');
    }

    public function destroy(Elevator $elevator)
    {
        $this->authorize('delete_elevator');
        $elevator->delete();
        return redirect()->route('admin.elevators.index')->with('success', 'Đã xóa thang máy.');
    }
}
