<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ship;
use Illuminate\Http\Request;

class ShipController extends Controller
{
    public function import(Request $request)
    {
        $this->authorize('create_ship');

        $request->validate([
            'file' => 'required|file|max:10240|mimes:xlsx,xls,csv,txt', // Limit 10MB
        ]);

        if (!$request->file('file')->isValid()) {
            return back()->withErrors(['file' => 'Lỗi tải file. File có thể vượt giới hạn dung lượng của server (upload_max_filesize).']);
        }

        $file = $request->file('file');
        
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['csv', 'txt', 'xls', 'xlsx'])) {
            return back()->withErrors(['file' => 'Vui lòng tải lên file định dạng Excel (.xlsx, .xls) hoặc CSV.']);
        }

        $imported = 0;
        $errors = [];
        
        try {
            // Load file using PhpSpreadsheet efficiently (no styling memory overhead)
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getPathname());
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Get highest row and column
            $highestRow = $worksheet->getHighestDataRow();
            $chunkSize = 500;
            $records = [];
            $now = now();
            
            // Iterate over each row, starting from row 2 (skipping header)
            for ($rowIdx = 2; $rowIdx <= $highestRow; $rowIdx++) {
                // Get all columns from column A to AG (column 33)
                $row = [];
                for ($col = 1; $col <= 33; $col++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $cellValue = $worksheet->getCell($colLetter . $rowIdx)->getFormattedValue();
                    $row[] = trim((string)$cellValue);
                }
                
                // Ignore empty rows
                if (empty(array_filter($row, fn($value) => $value !== ''))) {
                    continue;
                }

                try {
                    if (empty(trim($row[0]))) {
                        throw new \Exception("Cột Số đăng ký (SĐK) không được để trống.");
                    }

                    // Prepare Engine HP Array
                    $engineHp = [];
                    if (!empty(trim($row[19]))) $engineHp[] = trim($row[19]);
                    if (!empty(trim($row[20]))) $engineHp[] = trim($row[20]);
                    if (!empty(trim($row[21]))) $engineHp[] = trim($row[21]);

                    // Prepare Engine KW Array
                    $engineKw = [];
                    if (!empty(trim($row[22]))) $engineKw[] = trim($row[22]);
                    if (!empty(trim($row[23]))) $engineKw[] = trim($row[23]);
                    if (!empty(trim($row[24]))) $engineKw[] = trim($row[24]);

                    // Prepare Dates helper
                    $parseDate = function($dateStr) {
                        $dateStr = trim($dateStr);
                        if (empty($dateStr)) return null;
                        
                        // Xử lý riêng cho định dạng ngày tháng xuất từ Excel
                        if (is_numeric($dateStr)) {
                            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateStr)->format('Y-m-d');
                        }
                        
                        // Try parsing DD/MM/YYYY or similar if possible. For now use strtotime fallback
                        $time = strtotime(str_replace('/', '-', $dateStr));
                        return $time ? date('Y-m-d', $time) : null;
                    };

                    $formatFloat = function($val) {
                        $str = number_format((float)$val, 2, '.', '');
                        return strpos($str, '.') !== false ? rtrim(rtrim($str, '0'), '.') : $str;
                    };

                    $records[] = [
                        'registration_number' => trim($row[0]),
                        'owner_name' => trim($row[1]),
                        'province_id' => trim($row[3]),
                        'ward_id'=> trim($row[2]),
                        'main_occupation' => trim($row[4]),
                        'gross_tonnage' => is_numeric($row[5]) ? floatval($row[5]) : null,
                        'deadweight' => is_numeric($row[6]) ? floatval($row[6]) : null,
                        'length_design' => is_numeric($row[7]) ? floatval($row[7]) : null,
                        'width_design' => is_numeric($row[8]) ? floatval($row[8]) : null,
                        'length_max' => is_numeric($row[9]) ? floatval($row[9]) : null,
                        'width_max' => is_numeric($row[10]) ? floatval($row[10]) : null,
                        'depth_max' => is_numeric($row[11]) ? floatval($row[11]) : null,
                        'draft' => is_numeric($row[12]) ? floatval($row[12]) : null,
                        
                        'hull_material' => trim($row[13]),
                        'crew_size' => is_numeric($row[14]) ? intval($row[14]) : null,
                        'build_year' => is_numeric($row[15]) ? intval($row[15]) : null,
                        'build_place' => trim($row[16]),
                        
                        'engine_mark' => trim($row[17]),
                        'engine_number' => trim($row[18]),
                        'engine_hp' => json_encode(array_map($formatFloat, $engineHp)), // Cast to JSON since upsert bypasses Eloquent
                        'engine_kw' => json_encode(array_map($formatFloat, $engineKw)), // Cast to JSON since upsert bypasses Eloquent
                        
                        'technical_safety_number' => trim($row[25]),
                        'record_number' => trim($row[26]),
                        'technical_safety_date' => $parseDate($row[27]),
                        'record_date' => $parseDate($row[28]),
                        'expiration_date' => $parseDate($row[29]),
                        
                        'operation_area' => trim($row[30]),
                        'owner_phone' => trim($row[31]),
                        'owner_id_card' => trim($row[32]),
                        
                        'status' => 'active',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    // Process chunk if full
                    if (count($records) >= $chunkSize) {
                        Ship::upsert($records, ['registration_number'], [
                            'owner_name', 'province_id', 'ward_id', 'main_occupation', 'gross_tonnage', 'deadweight',
                            'length_design', 'width_design', 'length_max', 'width_max', 'depth_max', 'draft',
                            'hull_material', 'crew_size', 'build_year', 'build_place', 'engine_mark',
                            'engine_number', 'engine_hp', 'engine_kw', 'technical_safety_number', 'record_number',
                            'technical_safety_date', 'record_date', 'expiration_date', 'operation_area',
                            'owner_phone', 'owner_id_card', 'status', 'updated_at'
                        ]);
                        $imported += count($records);
                        $records = [];
                    }
                } catch (\Exception $e) {
                    $errors[] = "Lỗi dòng " . $rowIdx . ": " . $e->getMessage();
                }
            } // End for loop
            
            // Process remaining records
            if (!empty($records)) {
                Ship::upsert($records, ['registration_number'], [
                    'owner_name', 'province_id', 'ward_id', 'main_occupation', 'gross_tonnage', 'deadweight',
                    'length_design', 'width_design', 'length_max', 'width_max', 'depth_max', 'draft',
                    'hull_material', 'crew_size', 'build_year', 'build_place', 'engine_mark',
                    'engine_number', 'engine_hp', 'engine_kw', 'technical_safety_number', 'record_number',
                    'technical_safety_date', 'record_date', 'expiration_date', 'operation_area',
                    'owner_phone', 'owner_id_card', 'status', 'updated_at'
                ]);
                $imported += count($records);
            }
            
        } catch (\Exception $e) {
            return redirect()->route('admin.ships.index')->with('error', "Không thể đọc file: " . $e->getMessage());
        }

        if ($imported === 0 && count($errors) > 0) {
            return redirect()->route('admin.ships.index')->with('error', "Không có tàu nào được nhập. Các lỗi gặp phải:\n" . implode("\n", array_slice($errors, 0, 10)));
        }

        if (count($errors) > 0) {
            return redirect()->route('admin.ships.index')->with('warning', "Nhập thành công $imported tàu. Có một số lỗi:\n" . implode("\n", array_slice($errors, 0, 10)));
        }

        return redirect()->route('admin.ships.index')->with('success', "Đã nhập thành công $imported tàu từ file Excel.");
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view_ship');

        $query = Ship::query();

        $sortColumn = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 20);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('registration_number', 'like', "%{$search}%")
                  ->orWhere('owner_name', 'like', "%{$search}%")
                  ->orWhere('owner_phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'processing') {
                $query->whereHas('proposals', function($q) {
                    $q->where('status', '!=', 'approved');
                });
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('expiration')) {
            $now = now();
            if ($request->expiration === 'expired') {
                // Hết hạn = ngày < hôm nay (không bao gồm hôm nay)
                $query->whereNotNull('expiration_date')
                      ->where('expiration_date', '<', $now->startOfDay());
            } elseif ($request->expiration === 'expiring_soon') {
                // Sắp hết hạn = từ hôm nay đến 30 ngày tới (bao gồm hôm nay)
                $query->whereNotNull('expiration_date')
                      ->whereBetween('expiration_date', [$now->startOfDay(), now()->addDays(30)]);
            } elseif ($request->expiration === 'passed') {
                // Đã đạt chuẩn = không hết hạn VÀ không sắp hết hạn
                $query->where(function ($q) use ($now) {
                    $q->whereNull('expiration_date')
                      ->orWhere('expiration_date', '>', now()->addDays(30));
                });
            }
        }

        // Advanced Filters (adv_*)
        if ($request->filled('adv_registration')) {
            $query->where('registration_number', 'like', '%' . $request->adv_registration . '%');
        }
        if ($request->filled('adv_owner')) {
            $query->where(function($q) use ($request) {
                $q->where('owner_name', 'like', '%' . $request->adv_owner . '%')
                  ->orWhere('owner_phone', 'like', '%' . $request->adv_owner . '%')
                  ->orWhereHas('user', function($u) use ($request) {
                      $u->where('email', 'like', '%' . $request->adv_owner . '%');
                  });
            });
        }
        if ($request->filled('adv_status')) {
            if ($request->adv_status === 'processing') {
                $query->whereHas('proposals', fn($q) => $q->where('status', '!=', 'approved'));
            } else {
                $query->where('status', $request->adv_status);
            }
        }
        if ($request->filled('adv_main_occupation')) {
            $query->where('main_occupation', 'like', '%' . $request->adv_main_occupation . '%');
        }
        if ($request->filled('adv_secondary_occupation')) {
            $query->where('secondary_occupation', 'like', '%' . $request->adv_secondary_occupation . '%');
        }
        if ($request->filled('adv_usage')) {
            $query->where('usage', 'like', '%' . $request->adv_usage . '%');
        }
        if ($request->filled('adv_province')) {
            // Data import có thể lưu ngược cột, tìm cả 2 cột province_id và ward_id
            $query->where(function($q) use ($request) {
                $q->where('province_id', 'like', '%' . $request->adv_province . '%')
                  ->orWhere('ward_id', 'like', '%' . $request->adv_province . '%');
            });
        }
        if ($request->filled('adv_ward')) {
            $query->where(function($q) use ($request) {
                $q->where('ward_id', 'like', '%' . $request->adv_ward . '%')
                  ->orWhere('province_id', 'like', '%' . $request->adv_ward . '%');
            });
        }
        if ($request->filled('adv_expiration')) {
            $now = now();
            $expiringDays = max(1, (int) $request->input('adv_expiring_days', 30));
            if ($request->adv_expiration === 'expired') {
                $query->whereNotNull('expiration_date')->where('expiration_date', '<', $now->startOfDay());
            } elseif ($request->adv_expiration === 'expiring_soon') {
                $query->whereNotNull('expiration_date')->whereBetween('expiration_date', [$now->startOfDay(), now()->addDays($expiringDays)]);
            } elseif ($request->adv_expiration === 'valid') {
                $query->whereNotNull('expiration_date')->where('expiration_date', '>', now()->addDays(0));
            }
        }
        if ($request->filled('adv_inspection_start') || $request->filled('adv_inspection_end')) {
        $start = $request->adv_inspection_start;
        $end = $request->adv_inspection_end;
        
        if ($start && $end) {
            $query->whereRaw("(SELECT MAX(created_at) FROM proposals WHERE ship_id = ships.id) BETWEEN ? AND ?", [$start . ' 00:00:00', $end . ' 23:59:59']);
        } elseif ($start) {
            $query->whereRaw("(SELECT MAX(created_at) FROM proposals WHERE ship_id = ships.id) >= ?", [$start . ' 00:00:00']);
        } elseif ($end) {
            $query->whereRaw("(SELECT MAX(created_at) FROM proposals WHERE ship_id = ships.id) <= ?", [$end . ' 23:59:59']);
        }
    }

    if ($request->filled('adv_power_min') || $request->filled('adv_power_max')) {
            // Filter by maximum engine KW (stored as JSON array - use raw approach)
            if ($request->filled('adv_power_min')) {
                $query->whereRaw("JSON_LENGTH(engine_kw) > 0")
                      ->whereRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(engine_kw, '$[0]')) AS DECIMAL(10,2)) >= ?", [$request->adv_power_min]);
            }
            if ($request->filled('adv_power_max')) {
                $query->whereRaw("JSON_LENGTH(engine_kw) > 0")
                      ->whereRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(engine_kw, '$[0]')) AS DECIMAL(10,2)) <= ?", [$request->adv_power_max]);
            }
        }

        $validSortColumns = ['registration_number', 'name', 'owner_name', 'hull_number', 'status', 'created_at', 'expiration_date'];
        
        $sortExpirationParam = $request->input('sort_expiration') ?: $request->input('adv_sort_expiration');
        if ($sortExpirationParam) {
             $query->orderBy('expiration_date', $sortExpirationParam);
        } elseif (in_array($sortColumn, $validSortColumns)) {
            $query->orderBy($sortColumn, $sortOrder);
        } else {
            $query->latest();
        }

        $ships = $query->paginate($perPage)->withQueryString();

        // Query Thống kê
        $now = now();
        $thirtyDaysLater = now()->addDays(30);

        // Lấy tất cả tàu kèm đề xuất mới nhất
        $allShipsWithLatestProposal = Ship::with(['proposals' => function ($q) {
            $q->latest('created_at')->limit(1);
        }])->get();

        $stats = [
            'total' => $allShipsWithLatestProposal->count(),
            'passed' => 0,
            'expired' => 0,
            'expiring_soon' => 0,
            'processing' => 0, // In Progress
        ];

        foreach ($allShipsWithLatestProposal as $s) {

            $latestProposal = $s->proposals->first();

            if ($latestProposal) {
                if ($latestProposal->status !== 'approved') {
                    $stats['processing']++;
                }
            }

            // Tính hạn dùng cột expiration_date của Tàu (dùng startOfDay để đồng nhất với filter)
            if ($s->expiration_date) {
                if ($s->expiration_date->lt($now->copy()->startOfDay())) {
                    // Hết hạn = ngày < hôm nay (không bao gồm hôm nay)
                    $stats['expired']++;
                } elseif ($s->expiration_date->lte($thirtyDaysLater)) {
                    // Sắp hết hạn = hôm nay đến 30 ngày tới (bao gồm hôm nay)
                    $stats['expiring_soon']++;
                }
            }
        }

        // Đã đạt chuẩn = Tổng - Hết hạn - Sắp hết hạn
        $stats['passed'] = $stats['total'] - $stats['expired'] - $stats['expiring_soon'];

        // Top 5 popular options for dropdowns
        $topMainOccupations = Ship::select('main_occupation')
            ->whereNotNull('main_occupation')->where('main_occupation', '!=', '')
            ->groupBy('main_occupation')->orderByRaw('COUNT(*) DESC')->limit(5)
            ->pluck('main_occupation');

        $topSecondaryOccupations = Ship::select('secondary_occupation')
            ->whereNotNull('secondary_occupation')->where('secondary_occupation', '!=', '')
            ->groupBy('secondary_occupation')->orderByRaw('COUNT(*) DESC')->limit(5)
            ->pluck('secondary_occupation');

        $topUsages = Ship::select('usage')
            ->whereNotNull('usage')->where('usage', '!=', '')
            ->groupBy('usage')->orderByRaw('COUNT(*) DESC')->limit(5)
            ->pluck('usage');

        return view('admin.ships.index', compact('ships', 'sortColumn', 'sortOrder', 'stats',
            'topMainOccupations', 'topSecondaryOccupations', 'topUsages'));
    }

    /**
     * Export ships to Excel
     */
    public function export(Request $request)
    {
        $this->authorize('view_ship');

        // Ngăn chặn lỗi hết RAM và Time Out khi xuất Excel lớn
        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        $query = Ship::query();

        // 1. Áp dụng toàn bộ bộ lọc y như hàm index()
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('registration_number', 'like', "%{$search}%")
                  ->orWhere('owner_name', 'like', "%{$search}%")
                  ->orWhere('owner_phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'processing') {
                $query->whereHas('proposals', function($q) {
                    $q->where('status', '!=', 'approved');
                });
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('expiration')) {
            $now = now();
            if ($request->expiration === 'expired') {
                $query->whereNotNull('expiration_date')->where('expiration_date', '<', $now->startOfDay());
            } elseif ($request->expiration === 'expiring_soon') {
                $query->whereNotNull('expiration_date')->whereBetween('expiration_date', [$now->startOfDay(), now()->addDays(30)]);
            } elseif ($request->expiration === 'passed') {
                $query->where(function ($q) use ($now) {
                    $q->whereNull('expiration_date')->orWhere('expiration_date', '>', now()->addDays(30));
                });
            }
        }

        // Advanced Filters (adv_*)
        if ($request->filled('adv_registration')) {
            $query->where('registration_number', 'like', '%' . $request->adv_registration . '%');
        }
        if ($request->filled('adv_owner')) {
            $query->where(function($q) use ($request) {
                $q->where('owner_name', 'like', '%' . $request->adv_owner . '%')
                  ->orWhere('owner_phone', 'like', '%' . $request->adv_owner . '%')
                  ->orWhereHas('user', function($u) use ($request) {
                      $u->where('email', 'like', '%' . $request->adv_owner . '%');
                  });
            });
        }
        if ($request->filled('adv_status')) {
            if ($request->adv_status === 'processing') {
                $query->whereHas('proposals', fn($q) => $q->where('status', '!=', 'approved'));
            } else {
                $query->where('status', $request->adv_status);
            }
        }
        if ($request->filled('adv_main_occupation')) {
            $query->where('main_occupation', 'like', '%' . $request->adv_main_occupation . '%');
        }
        if ($request->filled('adv_secondary_occupation')) {
            $query->where('secondary_occupation', 'like', '%' . $request->adv_secondary_occupation . '%');
        }
        if ($request->filled('adv_usage')) {
            $query->where('usage', 'like', '%' . $request->adv_usage . '%');
        }
        if ($request->filled('adv_province')) {
            $query->where(function($q) use ($request) {
                $q->where('province_id', 'like', '%' . $request->adv_province . '%')
                  ->orWhere('ward_id', 'like', '%' . $request->adv_province . '%');
            });
        }
        if ($request->filled('adv_ward')) {
            $query->where(function($q) use ($request) {
                $q->where('ward_id', 'like', '%' . $request->adv_ward . '%')
                  ->orWhere('province_id', 'like', '%' . $request->adv_ward . '%');
            });
        }
        if ($request->filled('adv_expiration')) {
            $now = now();
            $expiringDays = max(1, (int) $request->input('adv_expiring_days', 30));
            if ($request->adv_expiration === 'expired') {
                $query->whereNotNull('expiration_date')->where('expiration_date', '<', $now->startOfDay());
            } elseif ($request->adv_expiration === 'expiring_soon') {
                $query->whereNotNull('expiration_date')->whereBetween('expiration_date', [$now->startOfDay(), now()->addDays($expiringDays)]);
            } elseif ($request->adv_expiration === 'valid') {
                $query->whereNotNull('expiration_date')->where('expiration_date', '>', now()->addDays(0));
            }
        }
        if ($request->filled('adv_inspection_start') || $request->filled('adv_inspection_end')) {
            $start = $request->adv_inspection_start;
            $end = $request->adv_inspection_end;
            if ($start && $end) {
                $query->whereRaw("(SELECT MAX(created_at) FROM proposals WHERE ship_id = ships.id) BETWEEN ? AND ?", [$start . ' 00:00:00', $end . ' 23:59:59']);
            } elseif ($start) {
                $query->whereRaw("(SELECT MAX(created_at) FROM proposals WHERE ship_id = ships.id) >= ?", [$start . ' 00:00:00']);
            } elseif ($end) {
                $query->whereRaw("(SELECT MAX(created_at) FROM proposals WHERE ship_id = ships.id) <= ?", [$end . ' 23:59:59']);
            }
        }

        if ($request->filled('adv_power_min') || $request->filled('adv_power_max')) {
            if ($request->filled('adv_power_min')) {
                $query->whereRaw("JSON_LENGTH(engine_kw) > 0")
                      ->whereRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(engine_kw, '$[0]')) AS DECIMAL(10,2)) >= ?", [$request->adv_power_min]);
            }
            if ($request->filled('adv_power_max')) {
                $query->whereRaw("JSON_LENGTH(engine_kw) > 0")
                      ->whereRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(engine_kw, '$[0]')) AS DECIMAL(10,2)) <= ?", [$request->adv_power_max]);
            }
        }

        $sortColumn = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $validSortColumns = ['registration_number', 'name', 'owner_name', 'hull_number', 'status', 'created_at', 'expiration_date'];
        
        $sortExpirationParam = $request->input('sort_expiration') ?: $request->input('adv_sort_expiration');
        if ($sortExpirationParam) {
             $query->orderBy('expiration_date', $sortExpirationParam);
        } elseif (in_array($sortColumn, $validSortColumns)) {
            $query->orderBy($sortColumn, $sortOrder);
        } else {
            $query->latest();
        }

        // Tạo file Excel bằng PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Cấu hình Headers (33 cột cấu trúc giống file import)
        $headers = [
            'Số ĐK (TT)', 'Chủ tàu (TT)', 'Xã/Phường đ/c chủ (TT)', 'Huyện/Thị xã đ/c chủ (TT)', 'Nghề chính (TT)', 
            'Tổng dung tích (TT)', 'Trọng tải (TT)', 'L thiết kế (KTTT)', 'B thiết kế (KTTT)', 'L max (KTTT)', 
            'B max (KTTT)', 'D max (KTTT)', 'd (KTTT)', 'Vật liệu vỏ (KTTT)', 'Thuyền viên (TT)', 
            'Năm đóng (TT)', 'Nơi đóng (TT)', 'Ký hiệu máy (M)', 'Số máy (M)', 'Công suất1 (M)', 
            'Công suất2 (M)', 'Công suất3 (M)', 'KW1 (M)', 'KW2 (M)', 'KW3 (M)', 
            'Số GCN ATKT (ATKT)', 'Số Sổ (ATKT)', 'Ngày cấp (ATKT)', 'Ngày cấp Sổ ĐK (ĐK)', 'Hạn Đăng Kiểm (ATKT)', 
            'Vùng hoạt động (TT)', 'Số điện thoại', 'CCCD/CMND'
        ];

        // Đổ Header vào file
        foreach ($headers as $index => $header) {
            $columnValue = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($columnValue . '1', $header);
            $sheet->getStyle($columnValue . '1')->getFont()->setBold(true);
        }

        // Lấy dữ liệu theo từng chunk để tránh hết bộ nhớ PHP
        $rowIdx = 2;
        $query->chunk(500, function ($ships) use ($sheet, &$rowIdx) {
            foreach ($ships as $ship) {
                // Ép kiểu mảng cho cấu hình máy
                $hp = is_string($ship->engine_hp) ? json_decode($ship->engine_hp, true) : $ship->engine_hp;
                if (!is_array($hp)) $hp = [];
                
                $kw = is_string($ship->engine_kw) ? json_decode($ship->engine_kw, true) : $ship->engine_kw;
                if (!is_array($kw)) $kw = [];

                // Hàm định dạng ngày
                $formatDate = function($date) {
                    if (!$date) return '';
                    if ($date instanceof \Carbon\Carbon) return $date->format('d/m/Y');
                    if (is_string($date) && strtotime($date)) return date('d/m/Y', strtotime($date));
                    return $date;
                };

                // Đổ dữ liệu từng ô
                $sheet->setCellValue('A' . $rowIdx, $ship->registration_number);
                $sheet->setCellValue('B' . $rowIdx, $ship->owner_name);
                $sheet->setCellValue('C' . $rowIdx, $ship->ward_id);
                $sheet->setCellValue('D' . $rowIdx, $ship->province_id);
                $sheet->setCellValue('E' . $rowIdx, $ship->main_occupation);
                $sheet->setCellValue('F' . $rowIdx, $ship->gross_tonnage);
                $sheet->setCellValue('G' . $rowIdx, $ship->deadweight);
                $sheet->setCellValue('H' . $rowIdx, $ship->length_design);
                $sheet->setCellValue('I' . $rowIdx, $ship->width_design);
                $sheet->setCellValue('J' . $rowIdx, $ship->length_max);
                $sheet->setCellValue('K' . $rowIdx, $ship->width_max);
                $sheet->setCellValue('L' . $rowIdx, $ship->depth_max);
                $sheet->setCellValue('M' . $rowIdx, $ship->draft);
                $sheet->setCellValue('N' . $rowIdx, $ship->hull_material);
                $sheet->setCellValue('O' . $rowIdx, $ship->crew_size);
                $sheet->setCellValue('P' . $rowIdx, $ship->build_year);
                $sheet->setCellValue('Q' . $rowIdx, $ship->build_place);
                $sheet->setCellValue('R' . $rowIdx, $ship->engine_mark);
                $sheet->setCellValue('S' . $rowIdx, $ship->engine_number);
                
                // Công suất HP
                $sheet->setCellValue('T' . $rowIdx, isset($hp[0]) ? $hp[0] : '');
                $sheet->setCellValue('U' . $rowIdx, isset($hp[1]) ? $hp[1] : '');
                $sheet->setCellValue('V' . $rowIdx, isset($hp[2]) ? $hp[2] : '');
                
                // Công suất KW
                $sheet->setCellValue('W' . $rowIdx, isset($kw[0]) ? $kw[0] : '');
                $sheet->setCellValue('X' . $rowIdx, isset($kw[1]) ? $kw[1] : '');
                $sheet->setCellValue('Y' . $rowIdx, isset($kw[2]) ? $kw[2] : '');
                
                // Thông tin Giấy phép / Sổ
                $sheet->setCellValue('Z' . $rowIdx, $ship->technical_safety_number);
                $sheet->setCellValue('AA' . $rowIdx, $ship->record_number);
                $sheet->setCellValue('AB' . $rowIdx, $formatDate($ship->technical_safety_date));
                $sheet->setCellValue('AC' . $rowIdx, $formatDate($ship->record_date));
                $sheet->setCellValue('AD' . $rowIdx, $formatDate($ship->expiration_date));
                
                // Thông tin khác
                $sheet->setCellValue('AE' . $rowIdx, $ship->operation_area);
                $sheet->setCellValue('AF' . $rowIdx, $ship->owner_phone);
                
                // Định dạng văn bản thô cho CCCD thay vì số thập phân khoa học
                $sheet->setCellValueExplicit('AG' . $rowIdx, $ship->owner_id_card, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                $rowIdx++;
            }
        });

        // Tự động điều chỉnh độ rộng các cột
        for ($col = 1; $col <= 33; $col++) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
        }

        // Tải file về dạng Excel 2007 (xlsx)
        $fileName = 'Danh_Sach_Tau_Thuyen_' . date('Ymd_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create_ship');
        $users = \App\Models\User::all(); // Or filter by role if needed
        return view('admin.ships.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create_ship');

        $validated = $request->validate([
            'registration_number' => 'required|string|unique:ships|max:50',
            'registration_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'status' => 'required|string',
            'owner_name' => 'required|string|max:255',
            'owner_id_card' => 'nullable|string|max:20',
            'owner_phone' => 'nullable|string|max:20',
            'province_id' => 'nullable|string|max:100',
            'ward_id' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'hull_number' => 'nullable|string|max:50',
            'usage' => 'nullable|string|max:255',
            'operation_area' => 'nullable|string|max:255',
            'crew_size' => 'nullable|integer',
            'main_occupation' => 'nullable|string|max:255',
            'secondary_occupation' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'gross_tonnage' => 'nullable|numeric',
            'deadweight' => 'nullable|numeric',
            'length_design' => 'nullable|numeric',
            'width_design' => 'nullable|numeric',
            'length_max' => 'nullable|numeric',
            'width_max' => 'nullable|numeric',
            'depth_max' => 'nullable|numeric',
            'draft' => 'nullable|numeric',
            'hull_material' => 'nullable|string|max:255',
            'build_year' => 'nullable|integer',
            'build_place' => 'nullable|string|max:255',

            'technical_safety_number' => 'nullable|string|max:255',
            'technical_safety_date' => 'nullable|date',
            'record_number' => 'nullable|string|max:255',
            'record_date' => 'nullable|date',
        ]);

        // Process json arrays for engines
        $engine_hp = [];
        if ($request->filled('engine_hp_inputs')) {
            $engine_hp = collect($request->input('engine_hp_inputs'))->filter()->map(fn($v) => (float)$v)->values()->toArray();
        }
        $validated['engine_hp'] = $engine_hp;

        $engine_kw = [];
        if ($request->filled('engine_kw_inputs')) {
            $engine_kw = collect($request->input('engine_kw_inputs'))->filter()->map(fn($v) => (float)$v)->values()->toArray();
        }
        $validated['engine_kw'] = $engine_kw;

        $sub_engine_hp = [];
        if ($request->filled('sub_engine_hp_inputs')) {
            $sub_engine_hp = collect($request->input('sub_engine_hp_inputs'))->filter()->map(fn($v) => (float)$v)->values()->toArray();
        }
        $validated['sub_engine_hp'] = $sub_engine_hp;

        $sub_engine_kw = [];
        if ($request->filled('sub_engine_kw_inputs')) {
            $sub_engine_kw = collect($request->input('sub_engine_kw_inputs'))->filter()->map(fn($v) => (float)$v)->values()->toArray();
        }
        $validated['sub_engine_kw'] = $sub_engine_kw;

        // Parse engine mark & number arrays
        $validated['engine_mark'] = collect($request->input('engine_mark_inputs', []))
            ->map(fn($v) => (string) $v)->values()->toArray();
        $validated['engine_number'] = collect($request->input('engine_number_inputs', []))
            ->map(fn($v) => (string) $v)->values()->toArray();
        $validated['sub_engine_mark'] = collect($request->input('sub_engine_mark_inputs', []))
            ->map(fn($v) => (string) $v)->values()->toArray();
        $validated['sub_engine_number'] = collect($request->input('sub_engine_number_inputs', []))
            ->map(fn($v) => (string) $v)->values()->toArray();

        $ship = Ship::create($validated);

        return redirect()->route('admin.ships.index')->with('success', 'Tàu thuyền đã được thêm mới thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ship $ship)
    {
        $this->authorize('view_ship');
        
        // Load proposals for this ship
        $proposals = $ship->proposals()
            ->with(['creator'])
            ->latest('created_at')
            ->get();
        
        return view('admin.ships.show', compact('ship', 'proposals'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ship $ship)
    {
        $this->authorize('update_ship');
        $users = \App\Models\User::all();
        return view('admin.ships.edit', compact('ship', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ship $ship)
    {
        $this->authorize('update_ship');

        $validated = $request->validate([
            'registration_number' => 'required|string|max:50|unique:ships,registration_number,' . $ship->id,
            'registration_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'status' => 'required|string',
            'owner_name' => 'required|string|max:255',
            'owner_id_card' => 'nullable|string|max:20',
            'owner_phone' => 'nullable|string|max:20',
            'province_id' => 'nullable|string|max:100',
            'ward_id' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'hull_number' => 'nullable|string|max:50',
            'usage' => 'nullable|string|max:255',
            'operation_area' => 'nullable|string|max:255',
            'crew_size' => 'nullable|integer',
            'main_occupation' => 'nullable|string|max:255',
            'secondary_occupation' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'gross_tonnage' => 'nullable|numeric',
            'deadweight' => 'nullable|numeric',
            'length_design' => 'nullable|numeric',
            'width_design' => 'nullable|numeric',
            'length_max' => 'nullable|numeric',
            'width_max' => 'nullable|numeric',
            'depth_max' => 'nullable|numeric',
            'draft' => 'nullable|numeric',
            'hull_material' => 'nullable|string|max:255',
            'build_year' => 'nullable|integer',
            'build_place' => 'nullable|string|max:255',

            'technical_safety_number' => 'nullable|string|max:255',
            'technical_safety_date' => 'nullable|date',
            'record_number' => 'nullable|string|max:255',
            'record_date' => 'nullable|date',
        ]);

        // Process json arrays for engines
        $engine_hp = [];
        if ($request->filled('engine_hp_inputs')) {
            $engine_hp = collect($request->input('engine_hp_inputs'))->filter()->map(fn($v) => (float)$v)->values()->toArray();
        } elseif ($request->filled('engine_hp')) {
            $engine_hp = collect($request->input('engine_hp'))->filter()->map(fn($v) => (float)$v)->values()->toArray();
        }
        $validated['engine_hp'] = $engine_hp;

        $engine_kw = [];
        if ($request->filled('engine_kw_inputs')) {
            $engine_kw = collect($request->input('engine_kw_inputs'))->filter()->map(fn($v) => (float)$v)->values()->toArray();
        } elseif ($request->filled('engine_kw')) {
            $engine_kw = collect($request->input('engine_kw'))->filter()->map(fn($v) => (float)$v)->values()->toArray();
        }
        $validated['engine_kw'] = $engine_kw;

        $sub_engine_hp = [];
        if ($request->filled('sub_engine_hp_inputs')) {
            $sub_engine_hp = collect($request->input('sub_engine_hp_inputs'))->filter()->map(fn($v) => (float)$v)->values()->toArray();
        } elseif ($request->filled('sub_engine_hp')) {
            $sub_engine_hp = collect($request->input('sub_engine_hp'))->filter()->map(fn($v) => (float)$v)->values()->toArray();
        }
        $validated['sub_engine_hp'] = $sub_engine_hp;

        $sub_engine_kw = [];
        if ($request->filled('sub_engine_kw_inputs')) {
            $sub_engine_kw = collect($request->input('sub_engine_kw_inputs'))->filter()->map(fn($v) => (float)$v)->values()->toArray();
        } elseif ($request->filled('sub_engine_kw')) {
            $sub_engine_kw = collect($request->input('sub_engine_kw'))->filter()->map(fn($v) => (float)$v)->values()->toArray();
        }
        $validated['sub_engine_kw'] = $sub_engine_kw;

        // Parse engine mark & number arrays
        $engine_mark_data = $request->input('engine_mark_inputs', $request->input('engine_mark', []));
        $validated['engine_mark'] = collect($engine_mark_data)->map(fn($v) => (string) $v)->values()->toArray();

        $engine_number_data = $request->input('engine_number_inputs', $request->input('engine_number', []));
        $validated['engine_number'] = collect($engine_number_data)->map(fn($v) => (string) $v)->values()->toArray();

        $sub_engine_mark_data = $request->input('sub_engine_mark_inputs', $request->input('sub_engine_mark', []));
        $validated['sub_engine_mark'] = collect($sub_engine_mark_data)->map(fn($v) => (string) $v)->values()->toArray();

        $sub_engine_number_data = $request->input('sub_engine_number_inputs', $request->input('sub_engine_number', []));
        $validated['sub_engine_number'] = collect($sub_engine_number_data)->map(fn($v) => (string) $v)->values()->toArray();

        $ship->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Thông tin tàu thuyền đã được cập nhật.',
                'ship'    => $ship
            ]);
        }

        return redirect()->route('admin.ships.index')->with('success', 'Thông tin tàu thuyền đã được cập nhật.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ship $ship)
    {
        $this->authorize('delete_ship');

        $ship->delete();

        return redirect()->route('admin.ships.index')->with('success', 'Tàu thuyền đã được xóa.');
    }

    /**
     * Store a newly created resource quickly from modal via AJAX.
     */
    public function quickStore(Request $request)
    {
        $this->authorize('create_ship');

        $validated = $request->validate([
            'registration_number' => 'required|string|unique:ships|max:50',
            'registration_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'status' => 'nullable|string',
            'owner_name' => 'required|string|max:255',
            'owner_id_card' => 'nullable|string|max:20',
            'owner_phone' => 'nullable|string|max:20',
            'province_id' => 'nullable|string|max:100',
            'ward_id' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'hull_number' => 'nullable|string|max:50',
            'usage' => 'nullable|string|max:255',
            'operation_area' => 'nullable|string|max:255',
            'crew_size' => 'nullable|integer',
            'main_occupation' => 'nullable|string|max:255',
            'secondary_occupation' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'gross_tonnage' => 'nullable|numeric',
            'deadweight' => 'nullable|numeric',
            'length_design' => 'nullable|numeric',
            'width_design' => 'nullable|numeric',
            'length_max' => 'nullable|numeric',
            'width_max' => 'nullable|numeric',
            'depth_max' => 'nullable|numeric',
            'draft' => 'nullable|numeric',
            'hull_material' => 'nullable|string|max:255',
            'build_year' => 'nullable|integer',
            'build_place' => 'nullable|string|max:255',
            'technical_safety_number' => 'nullable|string|max:255',
            'technical_safety_date' => 'nullable|date',
            'record_number' => 'nullable|string|max:255',
            'record_date' => 'nullable|date',
            
            // Engines arrays
            'engine_hp'     => 'nullable|array',
            'engine_kw'     => 'nullable|array',
            'engine_mark'   => 'nullable|array',
            'engine_number' => 'nullable|array',
            'sub_engine_hp'     => 'nullable|array',
            'sub_engine_kw'     => 'nullable|array',
            'sub_engine_mark'   => 'nullable|array',
            'sub_engine_number' => 'nullable|array',
        ]);

        if (empty($validated['status'])) {
            $validated['status'] = 'active'; // Default status
        }
        
        // Ensure values are parsed correctly as arrays
        $validated['engine_hp'] = $request->input('engine_hp', []);
        $validated['engine_kw'] = $request->input('engine_kw', []);
        $validated['engine_mark'] = $request->input('engine_mark', []);
        $validated['engine_number'] = $request->input('engine_number', []);
        $validated['sub_engine_hp'] = $request->input('sub_engine_hp', []);
        $validated['sub_engine_kw'] = $request->input('sub_engine_kw', []);
        $validated['sub_engine_mark'] = $request->input('sub_engine_mark', []);
        $validated['sub_engine_number'] = $request->input('sub_engine_number', []);

        $ship = Ship::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tàu thuyền đã được thêm mới thành công.',
            'data' => $ship
        ]);
    }

    /**
     * Update only specific calculation parameters of the ship via AJAX.
     */
    public function updateParameters(Request $request, Ship $ship)
    {
        $this->authorize('update_ship');

        $validated = $request->validate([
            'gross_tonnage' => 'nullable|numeric',
            'engine_hp'     => 'nullable|array',
            'engine_kw'     => 'nullable|array',
            'length_max'    => 'nullable|numeric',
            'width_max'     => 'nullable|numeric',
            'deadweight'    => 'nullable|numeric',
            'technical_safety_number' => 'nullable|string|max:255',
            'technical_safety_date'   => 'nullable|date',
            'record_number'           => 'nullable|string|max:255',
            'record_date'             => 'nullable|date',
        ]);

        $ship->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Thông số tàu đã được cập nhật.',
            'data' => $ship
        ]);
    }

    /**
     * Update just the expiration_date of a ship (inline edit from index table).
     */
    public function updateExpiration(Request $request, Ship $ship)
    {
        $this->authorize('update_ship');

        $request->validate([
            'expiration_date' => 'nullable|date',
        ]);

        $ship->update(['expiration_date' => $request->expiration_date ?: null]);

        return response()->json([
            'success'         => true,
            'expiration_date' => $ship->expiration_date ? $ship->expiration_date->format('Y-m-d') : null,
        ]);
    }
}
