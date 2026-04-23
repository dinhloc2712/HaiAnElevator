<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Installation;
use App\Models\Building;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;

class InstallationController extends Controller
{
    /**
     * Display a listing of the installation orders.
     */
    public function index()
    {
        $this->authorize('view_installation');
        $installations = Installation::with(['building', 'staff', 'branch'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'in_progress' => Installation::where('status', 'in_progress')->count(),
            'pending' => Installation::where('status', 'pending')->count(),
            'completed' => Installation::where('status', 'completed')->count(),
        ];

        return view('admin.installations.index', compact('installations', 'stats'));
    }

    /**
     * Show the form for creating a new installation order.
     */
    public function create()
    {
        $this->authorize('create_installation');
        $buildings = Building::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();
        $staffs = User::all(); // You might want to filter by role 'technical' or similar

        return view('admin.installations.create', compact('buildings', 'branches', 'staffs'));
    }

    /**
     * Store a newly created installation order in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create_installation');
        $request->validate([
            'code' => 'required|unique:installations,code',
            'branch_id' => 'required|exists:branches,id',
            'building_id' => 'required', // Can be ID or String (for new building)
            'user_id' => 'required|exists:users,id',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $buildingId = $request->building_id;

        // Auto-creation logic for Buildings
        if (!is_numeric($buildingId)) {
            $building = Building::create([
                'name' => $buildingId,
                'is_active' => true,
                // Add other defaults or placeholders
            ]);
            $buildingId = $building->id;
        }

        Installation::create([
            'code' => $request->code,
            'branch_id' => $request->branch_id,
            'building_id' => $buildingId,
            'user_id' => $request->user_id,
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.installations.index')
            ->with('success', 'Tạo đơn lắp đặt thành công.');
    }

    /**
     * Show the form for editing the specified installation order.
     */
    public function edit(Installation $installation)
    {
        $this->authorize('update_installation');
        $buildings = Building::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();
        $staffs = User::all();

        return view('admin.installations.edit', compact('installation', 'buildings', 'branches', 'staffs'));
    }

    /**
     * Update the specified installation order in storage.
     */
    public function update(Request $request, Installation $installation)
    {
        $this->authorize('update_installation');
        $rules = [
            'code' => 'sometimes|required|unique:installations,code,' . $installation->id,
            'branch_id' => 'sometimes|required|exists:branches,id',
            'building_id' => 'sometimes|required',
            'user_id' => 'sometimes|required|exists:users,id',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'status' => 'sometimes|required|in:pending,in_progress,completed',
        ];

        $request->validate($rules);

        $data = $request->only(['code', 'branch_id', 'building_id', 'user_id', 'start_date', 'due_date', 'status', 'notes']);

        // Auto-creation logic for Buildings (only if building_id is provided and not numeric)
        if ($request->has('building_id') && !is_numeric($request->building_id)) {
            $building = Building::create([
                'name' => $request->building_id,
                'is_active' => true,
            ]);
            $data['building_id'] = $building->id;
        }

        $installation->update($data);

        return redirect()->route('admin.installations.index')
            ->with('success', 'Cập nhật đơn lắp đặt thành công.');
    }

    /**
     * Start the installation process.
     */
    public function start(Installation $installation)
    {
        $this->authorize('update_installation');
        $installation->update(['status' => 'in_progress']);

        return redirect()->route('admin.installations.index')
            ->with('success', 'Đã bắt đầu quá trình lắp đặt.');
    }

    /**
     * Complete the installation by registering an elevator.
     */
    public function complete(Request $request, Installation $installation)
    {
        $this->authorize('update_installation');
        $request->validate([
            'elevator_code' => 'required|unique:elevators,code',
            'manufacturer'   => 'nullable|string|max:255',
            'model'          => 'nullable|string|max:255',
            'type'           => 'nullable|string|max:255',
            'capacity'       => 'nullable|string|max:255',
            'province'       => 'required|string|max:255',
            'district'       => 'required|string|max:255',
            'cycle_days'     => 'required|integer|min:1',
        ]);

        \DB::transaction(function () use ($request, $installation) {
            // Create the Elevator
            \App\Models\Elevator::create([
                'code'                 => $request->elevator_code,
                'installation_id'      => $installation->id,
                'building_id'          => $installation->building_id,
                'branch_id'            => $installation->branch_id,
                'customer_name'        => $installation->building->customer_name ?? $installation->building->name,
                'customer_phone'       => $installation->building->contact_phone,
                'province'             => $request->province,
                'district'             => $request->district,
                'manufacturer'         => $request->manufacturer,
                'model'                => $request->model,
                'type'                 => $request->type,
                'capacity'             => $request->capacity,
                'cycle_days'           => $request->cycle_days,
                'status'               => 'active',
                'maintenance_deadline' => now()->addDays($request->cycle_days),
            ]);

            // Update Installation Status
            $installation->update(['status' => 'completed']);
        });

        return redirect()->route('admin.installations.index')
            ->with('success', 'Đã hoàn thành lắp đặt và đăng ký thang máy thành công.');
    }

    /**
     * Remove the specified installation order from storage.
     */
    public function destroy(Installation $installation)
    {
        $this->authorize('delete_installation');
        $installation->delete();
        return redirect()->route('admin.installations.index')
            ->with('success', 'Xóa đơn lắp đặt thành công.');
    }
}
