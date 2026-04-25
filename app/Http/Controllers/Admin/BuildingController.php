<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Building;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_building');
        $query = Building::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%");
            });
        }

        $buildings = $query->latest()->paginate(20)->withQueryString();

        return view('admin.buildings.index', compact('buildings'));
    }

    public function create()
    {
        $this->authorize('create_building');
        return view('admin.buildings.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create_building');
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'customer_name'  => 'nullable|string|max:255',
            'address'        => 'nullable|string|max:500',
            'contact_name'   => 'nullable|string|max:255',
            'contact_phone'  => 'nullable|string|max:20',
            'elevator_count' => 'nullable|integer|min:0',
            'notes'          => 'nullable|string',
            'is_active'      => 'boolean',
        ]);

        $validated['is_active']      = $request->boolean('is_active', true);
        if (!isset($validated['elevator_count'])) {
            $validated['elevator_count'] = 0;
        }

        Building::create($validated);

        return redirect()->route('admin.buildings.index')->with('success', 'Tòa nhà đã được thêm thành công.');
    }

    public function edit(Building $building)
    {
        $this->authorize('update_building');
        return view('admin.buildings.edit', compact('building'));
    }

    public function update(Request $request, Building $building)
    {
        $this->authorize('update_building');
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'customer_name'  => 'nullable|string|max:255',
            'address'        => 'nullable|string|max:500',
            'contact_name'   => 'nullable|string|max:255',
            'contact_phone'  => 'nullable|string|max:20',
            'elevator_count' => 'nullable|integer|min:0',
            'notes'          => 'nullable|string',
            'is_active'      => 'boolean',
        ]);

        $validated['is_active']      = $request->boolean('is_active', true);
        // Do not update elevator_count here as it is managed automatically
        unset($validated['elevator_count']);

        $building->update($validated);

        return redirect()->route('admin.buildings.index')->with('success', 'Cập nhật tòa nhà thành công.');
    }

    public function destroy(Building $building)
    {
        $this->authorize('delete_building');
        $building->delete();
        return redirect()->route('admin.buildings.index')->with('success', 'Đã xóa tòa nhà.');
    }

    public function import(Request $request)
    {
        $this->authorize('create_building');
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Bỏ qua dòng đầu tiên nếu là tiêu đề
            array_shift($rows);

            $count = 0;
            foreach ($rows as $row) {
                if (empty($row[0])) continue;

                Building::create([
                    'name'           => $row[0],
                    'customer_name'  => $row[1] ?? null,
                    'address'        => $row[2] ?? null,
                    'contact_name'   => $row[3] ?? null,
                    'contact_phone'  => $row[4] ?? null,
                    'notes'          => $row[5] ?? null,
                    'is_active'      => true,
                ]);
                $count++;
            }

            return back()->with('success', "Đã import thành công $count tòa nhà.");
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi import: ' . $e->getMessage());
        }
    }
}

