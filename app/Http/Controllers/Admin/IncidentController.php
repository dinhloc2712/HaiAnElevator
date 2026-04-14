<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\Elevator;
use Illuminate\Http\Request;

class IncidentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Incident::with(['elevator.building', 'elevator.branch']);

        // DASHBOARD STATISTICS
        $emergencyCount = Incident::where('priority', 'emergency')->count();
        $processingCount = Incident::where('status', 'processing')->count();
        
        // Prepare trend data for last 7 days
        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $count = Incident::whereDate('reported_at', $date)->count();
            
            $days = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
            $chartLabels[] = $days[$date->dayOfWeek];
            $chartData[] = $count;
        }

        // Search by Code or Elevator Code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhereHas('elevator', function($qe) use ($search) {
                      $qe->where('code', 'like', "%{$search}%");
                  });
            });
        }

        // Search by Building or Customer
        if ($request->filled('building')) {
            $building = $request->building;
            $query->whereHas('elevator.building', function($q) use ($building) {
                $q->where('name', 'like', "%{$building}%");
            });
        }

        // Filter by Priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $incidents = $query->latest('reported_at')->paginate(15);

        return view('admin.incidents.index', compact('incidents', 'emergencyCount', 'processingCount', 'chartLabels', 'chartData'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $elevators = Elevator::with('building')->get();
        return view('admin.incidents.create', compact('elevators'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'elevator_id' => 'required|exists:elevators,id',
            'reporter_name' => 'nullable|string|max:255',
            'reporter_phone' => 'nullable|string|max:20',
            'description' => 'required|string',
            'priority' => 'required|in:emergency,high,medium,low',
            'status' => 'required|in:new,processing,resolved,canceled',
            'reported_date' => 'required|date',
            'reported_time' => 'required',
        ]);

        // Auto-generate code: INC-YYYY-XXX
        $year = date('Y');
        $prefix = "INC-{$year}-";
        $latest = Incident::where('code', 'like', "{$prefix}%")->latest('id')->first();
        $sequence = $latest ? intval(substr($latest->code, -3)) + 1 : 1;
        $code = $prefix . str_pad($sequence, 3, '0', STR_PAD_LEFT);

        Incident::create([
            'code' => $code,
            'elevator_id' => $request->elevator_id,
            'reporter_name' => $request->reporter_name,
            'reporter_phone' => $request->reporter_phone,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => $request->status,
            'reported_at' => $request->reported_date . ' ' . $request->reported_time,
        ]);

        return redirect()->route('admin.incidents.index')->with('success', 'Đã báo cáo sự cố mới thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Incident $incident)
    {
        return view('admin.incidents.show', compact('incident'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Incident $incident)
    {
        $elevators = Elevator::with('building')->get();
        return view('admin.incidents.edit', compact('incident', 'elevators'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Incident $incident)
    {
        $request->validate([
            'elevator_id' => 'required|exists:elevators,id',
            'reporter_name' => 'nullable|string|max:255',
            'reporter_phone' => 'nullable|string|max:20',
            'description' => 'required|string',
            'priority' => 'required|in:emergency,high,medium,low',
            'status' => 'required|in:new,processing,resolved,canceled',
            'reported_date' => 'required|date',
            'reported_time' => 'required',
        ]);

        $incident->update([
            'elevator_id' => $request->elevator_id,
            'reporter_name' => $request->reporter_name,
            'reporter_phone' => $request->reporter_phone,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => $request->status,
            'reported_at' => $request->reported_date . ' ' . $request->reported_time,
        ]);

        return redirect()->route('admin.incidents.index')->with('success', 'Cập nhật thông tin sự cố thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Incident $incident)
    {
        $incident->delete();
        return redirect()->route('admin.incidents.index')->with('success', 'Đã xóa hồ sơ sự cố.');
    }
}
