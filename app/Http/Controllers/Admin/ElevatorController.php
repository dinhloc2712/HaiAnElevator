<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Elevator;
use App\Models\Building;
use App\Models\Branch;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ElevatorController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_elevator');

        $query = Elevator::with(['building', 'branch']);

        // Quick Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('province', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhereHas('building', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
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

        // Maintenance Deadline Range
        if ($request->filled('deadline_from')) {
            $query->whereDate('maintenance_deadline', '>=', $request->deadline_from);
        }
        if ($request->filled('deadline_to')) {
            $query->whereDate('maintenance_deadline', '<=', $request->deadline_to);
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
            'floors'               => 'nullable|integer',
            'cycle_days'           => 'required|integer|min:1',
            'status'               => 'required|string|in:active,error,maintenance',
            'address'              => 'nullable|string|max:500',
            'note'                 => 'nullable|string',
            'map'                  => 'nullable|string',
            'maintenance_deadline' => 'nullable|date',
            'maintenance_end_date' => 'nullable|date',
        ]);

        // Custom Validation Logic
        if ($request->filled('maintenance_deadline') && $request->filled('maintenance_end_date')) {
            $deadline = Carbon::parse($request->maintenance_deadline);
            $endDate = Carbon::parse($request->maintenance_end_date);

            if ($deadline->gt($endDate)) {
                return back()->withErrors(['maintenance_deadline' => 'Hạn bảo trì tiếp theo không được vượt quá ngày kết thúc thời hạn bảo trì.'])->withInput();
            }

            if ($endDate->isPast() && $deadline->gt(now())) {
                 return back()->withErrors(['maintenance_deadline' => 'Hợp đồng bảo trì đã hết hạn. Không thể gia hạn bảo trì mới.'])->withInput();
            }
        }

        $elevator = Elevator::create($validated);

        if ($elevator->building_id) {
            Building::find($elevator->building_id)?->increment('elevator_count');
        }

        return redirect()->route('admin.elevators.index')->with('success', 'Thang máy đã được thêm thành công.');
    }

    public function show(Elevator $elevator)
    {
        $this->authorize('view_elevator');
        $elevator->load(['building', 'branch']);
        return view('admin.elevators.show', compact('elevator'));
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
            'floors'               => 'nullable|integer',
            'cycle_days'           => 'required|integer|min:1',
            'status'               => 'required|string|in:active,error,maintenance',
            'address'              => 'nullable|string|max:500',
            'note'                 => 'nullable|string',
            'map'                  => 'nullable|string',
            'maintenance_deadline' => 'nullable|date',
            'maintenance_end_date' => 'nullable|date',
        ]);

        // Custom Validation Logic
        if ($request->filled('maintenance_deadline') && $request->filled('maintenance_end_date')) {
            $deadline = Carbon::parse($request->maintenance_deadline);
            $endDate = Carbon::parse($request->maintenance_end_date);

            // 1. Hạn bảo trì tiếp theo không được quá ngày kết thúc
            if ($deadline->gt($endDate)) {
                return back()->withErrors(['maintenance_deadline' => 'Hạn bảo trì tiếp theo không được vượt quá ngày kết thúc thời hạn bảo trì.'])->withInput();
            }

            // 2. Nếu đã quá ngày kết thúc hợp đồng, không cho cập nhật hạn bảo trì mới trong tương lai
            if ($endDate->isPast() && $deadline->gt($elevator->maintenance_deadline)) {
                 return back()->withErrors(['maintenance_deadline' => 'Hợp đồng bảo trì đã hết hạn. Không thể cập nhật thêm hạn bảo trì mới.'])->withInput();
            }
        }

        $elevator->update($validated);

        return redirect()->route('admin.elevators.index')->with('success', 'Cập nhật thang máy thành công.');
    }

    public function destroy(Elevator $elevator)
    {
        $this->authorize('delete_elevator');
        $elevator->delete();
        return redirect()->route('admin.elevators.index')->with('success', 'Đã xóa thang máy.');
    }

    public function export(Request $request)
    {
        $this->authorize('view_elevator');
        
        $query = Elevator::with(['building']);
        
        $type = $request->get('type', 'location');
        
        if ($type === 'deadline') {
            $query->orderBy('maintenance_deadline', 'asc');
            $filename = "danh_sach_thang_may_theo_han_bao_tri.xlsx";
        } else {
            $query->orderBy('province', 'asc')->orderBy('district', 'asc');
            $filename = "danh_sach_thang_may_theo_khu_vuc.xlsx";
        }
        
        $elevators = $query->get();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = ['Mã thang máy', 'Tòa nhà/Khách hàng', 'Số điện thoại', 'Địa chỉ', 'Hạn bảo trì'];
        $sheet->fromArray($headers, null, 'A1');
        
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4e73df']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        
        $row = 2;
        foreach ($elevators as $elevator) {
            $customer = $elevator->customer_name ?? ($elevator->building->name ?? 'N/A');
            $phone = $elevator->customer_phone ?? ($elevator->building->contact_phone ?? 'N/A');
            $fullAddress = ($elevator->address ? $elevator->address . ', ' : '') . $elevator->district . ', ' . $elevator->province;
            
            $sheet->setCellValue('A' . $row, $elevator->code);
            $sheet->setCellValue('B' . $row, $customer);
            $sheet->setCellValue('C' . $row, $phone);
            $sheet->setCellValue('D' . $row, $fullAddress);
            $sheet->setCellValue('E' . $row, $elevator->maintenance_deadline ? $elevator->maintenance_deadline->format('d/m/Y') : 'N/A');
            $row++;
        }
        
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $sheet->getStyle('A1:E' . ($row - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);
        
        return response()->streamDownload(function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename);
    }
}
