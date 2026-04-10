<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Document;
use App\Models\Ship;

class ProposalTemplateController extends Controller
{
    /**
     * API to search and return .doc/.docx files for template filling
     */
    public function getTemplates(Request $request)
    {
        $this->authorize('create_proposal');
        
        $search = $request->input('search');
        
        $query = Document::whereIn('mime_type', [
            'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ]);

        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        $templates = $query->orderBy('created_at', 'desc')->limit(20)->get()->map(function($doc) {
            return [
                'id' => $doc->id,
                'title' => $doc->title,
                'file_path' => $doc->file_path
            ];
        });

        return response()->json($templates);
    }

    /**
     * Generate Word document filled with Ship data
     */
    public function fillTemplate(Request $request)
    {
        $this->authorize('create_proposal');

        $request->validate([
            'template_path' => 'required|string',
            'ship_id' => 'nullable|exists:ships,id',
            // Also accept ship parameters directly if creating a new ship
            'ship_data' => 'nullable|array'
        ]);

        $templatePath = $request->input('template_path');
        
        if (!Storage::disk('private')->exists($templatePath)) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy file mẫu.'], 404);
        }

        // PhpWord TemplateProcessor only supports .docx files
        $ext = strtolower(pathinfo($templatePath, PATHINFO_EXTENSION));
        if ($ext !== 'docx') {
            return response()->json(['success' => false, 'message' => 'Hệ thống chỉ hỗ trợ điền tự động đối với các file biểu mẫu định dạng .docx (Word 2007 trở lên). Vui lòng lưu file mẫu dưới dạng .docx và tải lên lại.'], 400);
        }

        $path = Storage::disk('private')->path($templatePath);
        
        try {
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($path);
            
            // Get original variables from template
            $originalVars = $templateProcessor->getVariables();
            $originalVars = array_values(array_unique($originalVars));

            // Prepare Data to fill
            $fillData = [];

            // 1. Data from Ship Context
            if ($request->input('ship_id')) {
                $ship = Ship::find($request->input('ship_id'));
                if ($ship) {
                    $fillData = $this->extractShipData($ship);
                }
            } else if ($request->input('ship_data')) {
                // Temporary data for new ship creation
                $fillData = $request->input('ship_data');
                // Ensure proper formatting if needed
            }

            // 2. Data from Proposal Payment
            $payment = (float)$request->input('payment', 0);
            if ($payment > 0) {
                $fillData['payment'] = number_format($payment, 0, ',', '.');
                $fillData['payment_text'] = $this->numberToWordsVN($payment) . ' đồng';
            } else {
                $fillData['payment'] = '0';
                $fillData['payment_text'] = 'Không đồng';
            }

            // Tách ra 2 nhóm: biến vô hướng và biến mảng
            $scalarData = [];
            $arrayData  = [];

            foreach ($fillData as $key => $value) {
                if (is_array($value)) {
                    $arrayData[$key] = array_values($value);
                } else {
                    $scalarData[$key] = $value ?? '';
                }
            }

            // --- Bước 1: Xử lý biến MẢNG (array) bằng cách clone dòng bảng ---
            // Phát hiện xem biến nào trong template thuộc về mảng
            $processedArrayKeys = [];

            foreach ($arrayData as $baseKey => $items) {
                // Tên biến có thể xuất hiện trực tiếp trong template, ví dụ ${engine_kw}
                // Tìm xem template có chứa biến này không
                if (!in_array($baseKey, $originalVars)) {
                    continue; // Bỏ qua nếu template không có biến này
                }

                $count = count($items);
                if ($count === 0) continue;

                // Cố gắng clone dòng: PHPWord sẽ tìm dòng bảng chứa biến ${baseKey}
                try {
                    // Xây dựng mảng values cho mỗi dòng: bao gồm chính biến này
                    // và các biến liên quan cùng index (ví dụ engine_hp nếu đang xử lý engine_kw)
                    $rows = [];
                    for ($i = 0; $i < $count; $i++) {
                        $row = [];
                        // Điền chính biến đang loop
                        $row[$baseKey] = (string)($items[$i] ?? '');
                        // Điền biến STT tương ứng nếu tồn tại: engine_stt, engine_kw => engine_stt
                        $sttKey = preg_replace('/_[^_]+$/', '_stt', $baseKey);
                        if ($sttKey !== $baseKey) {
                            $row[$sttKey] = (string)($i + 1);
                        }
                        // Điền các biến mảng khác có cùng số lượng phần tử (có thể là cùng bảng)
                        foreach ($arrayData as $otherKey => $otherItems) {
                            if ($otherKey === $baseKey) continue;
                            if (count($otherItems) === $count && in_array($otherKey, $originalVars)) {
                                $row[$otherKey] = (string)($otherItems[$i] ?? '');
                                $processedArrayKeys[] = $otherKey; // Đánh dấu đã xử lý
                                // STT cho key này
                                $otherSttKey = preg_replace('/_[^_]+$/', '_stt', $otherKey);
                                if (!isset($row[$otherSttKey])) {
                                    $row[$otherSttKey] = (string)($i + 1);
                                }
                            }
                        }
                        $rows[] = $row;
                    }
                    $templateProcessor->cloneRowAndSetValues($baseKey, $rows);
                    $processedArrayKeys[] = $baseKey;
                } catch (\Exception $cloneEx) {
                    // Nếu không clone được (không nằm trong bảng), ghép thành chuỗi thay thế
                    $scalarData[$baseKey] = implode(', ', array_map('strval', $items));
                }
            }

            // Xử lý các biến mảng còn lại chưa được clone (cùng bảng với cái khác nhưng có count khác)
            foreach ($arrayData as $key => $items) {
                if (in_array($key, $processedArrayKeys)) continue;
                $scalarData[$key] = implode(', ', array_map('strval', $items));
            }

            // --- Bước 2: Xử lý biến VÔ HƯỚNG (scalar) bằng setValue thông thường ---
            foreach ($originalVars as $varName) {
                $cleanVarName = trim($varName);

                // Bỏ qua những biến đã xử lý bởi clone phía trên
                if (in_array($cleanVarName, $processedArrayKeys)) continue;

                if (array_key_exists($cleanVarName, $scalarData)) {
                    $templateProcessor->setValue($varName, $scalarData[$cleanVarName]);
                } else {
                    $mappedKey = str_replace(' ', '_', strtolower($cleanVarName));
                    if (array_key_exists($mappedKey, $scalarData)) {
                        $templateProcessor->setValue($varName, $scalarData[$mappedKey]);
                    }
                }
            }


            // Generate output file
            $cleanName = pathinfo($templatePath, PATHINFO_FILENAME);
            $outputFilename = 'Generated_Proposal_' . time() . '_' . $cleanName . '.docx';
            
            $pathInfo = pathinfo($outputFilename);
            $filename = \Str::slug($pathInfo['filename']) . '.' . $pathInfo['extension'];
            
            $tempDir = storage_path('app/temp');
            $tempPath = $tempDir . '/' . $filename;
            
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            $templateProcessor->saveAs($tempPath);

            $storageFilename = 'proposal_auto_' . time() . '_' . $filename;
            
            // Xử lý tạo the folder format /YYYY/MM cho file
            $folderName = date('Y/m');
            $fullPath = $folderName . '/' . $storageFilename;
            
            Storage::disk('private')->put($fullPath, file_get_contents($tempPath));
            
            // Cleanup temp
            @unlink($tempPath);

            return response()->json([
                'success' => true,
                'message' => 'Tạo file tự động thành công.',
                'filename' => $fullPath,
                'url' => route('admin.media.serve', ['filename' => $fullPath])
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Template Fill Error', ['msg' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Lỗi xử lý file mẫu: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Map Ship Model fields to simple array for template filling
     */
    private function extractShipData($ship)
    {
        $engineKws = [];
        $engineHps = [];
        if (is_array($ship->engine_kw)) {
            $engineKws = $ship->engine_kw;
        } else if (is_string($ship->engine_kw)) {
            $engineKws = json_decode($ship->engine_kw, true) ?: [$ship->engine_kw];
        }
        
        if (is_array($ship->engine_hp)) {
            $engineHps = $ship->engine_hp;
        } else if (is_string($ship->engine_hp)) {
            $engineHps = json_decode($ship->engine_hp, true) ?: [$ship->engine_hp];
        }

        $subEngineKws = [];
        $subEngineHps = [];
        if (is_array($ship->sub_engine_kw)) {
            $subEngineKws = $ship->sub_engine_kw;
        } else if (is_string($ship->sub_engine_kw)) {
            $subEngineKws = json_decode($ship->sub_engine_kw, true) ?: [$ship->sub_engine_kw];
        }

        if (is_array($ship->sub_engine_hp)) {
            $subEngineHps = $ship->sub_engine_hp;
        } else if (is_string($ship->sub_engine_hp)) {
            $subEngineHps = json_decode($ship->sub_engine_hp, true) ?: [$ship->sub_engine_hp];
        }

        $totalKw = array_sum(array_map('floatval', $engineKws));
        $totalHp = array_sum(array_map('floatval', $engineHps));

        $totalSubKw = array_sum(array_map('floatval', $subEngineKws));
        $totalSubHp = array_sum(array_map('floatval', $subEngineHps));

        // Hàm helper format số liệu theo yêu cầu:
        // 1. Số nguyên < 10 -> thêm 0 phía trước (ví dụ 3 -> 03)
        // 2. Số nguyên >= 10 -> giữ nguyên
        // 3. Số thập phân -> luôn có 2 số sau dấu phẩy (ví dụ 3.147 -> 3.15, 3.3 -> 3.30)
        $formatVal = function ($val) {
            if ($val === null || $val === '') return '';
            
            // Nếu không phải số (ví dụ: chuỗi ký tự thông thường), trả về nguyên gốc
            if (!is_numeric($val)) return $val;
            
            $val = (float) $val;

            // Nếu là số nguyên (không có phần thập phân)
            if (floor($val) == $val) {
                if ($val < 10 && $val >= 0) {
                    return sprintf('%02d', $val); // 3 -> 03
                }
                return (string) $val; // >= 10 giữ nguyên (vd 10 -> 10)
            }

            // Nếu là số thập phân
            return number_format($val, 2, '.', ''); // => 3.147 -> 3.15, 3.300 -> 3.30, v.v
        };

        return [
            'registration_number' => $ship->registration_number,
            'name' => $ship->name,
            'owner_name' => $ship->owner_name,
            'owner_phone' => $ship->owner_phone,
            'province_id' => $ship->province_id,
            'ward_id' => $ship->ward_id,
            'build_year' => $ship->build_year,
            'build_place' => $ship->build_place,
            'main_occupation' => $ship->main_occupation,
            'secondary_occupation' => $ship->secondary_occupation,
            'hull_material' => $ship->hull_material,
            
            // Áp dụng format số
            'gross_tonnage' => $formatVal($ship->gross_tonnage),
            'deadweight' => $formatVal($ship->deadweight),
            'operation_area' => $ship->operation_area,
            'crew_size' => $formatVal($ship->crew_size),
            'length_max' => $formatVal($ship->length_max),
            'length_design' => $formatVal($ship->length_design),
            'width_max' => $formatVal($ship->width_max),
            'width_design' => $formatVal($ship->width_design),
            'depth_max' => $formatVal($ship->depth_max),
            'draft' => $formatVal($ship->draft),
            'freeboard' => $formatVal($ship->freeboard),

            // Các biến vô hướng — tổng cộng
            'total_engine_kw' => $formatVal($totalKw),
            'total_engine_hp' => $formatVal($totalHp),
            'total_sub_engine_kw' => $formatVal($totalSubKw),
            'total_sub_engine_hp' => $formatVal($totalSubHp),
            'total_engine_number' => $formatVal(count($engineKws) > 0 ? count(array_filter($engineKws)) : 0),

            // Các biến MẢNG — map lại qua formatVal
            'engine_kw' => array_values(array_map($formatVal, array_filter($engineKws, fn($v) => $v !== null && $v !== ''))),
            'engine_hp' => array_values(array_map($formatVal, array_filter($engineHps, fn($v) => $v !== null && $v !== ''))),
            
            // Các biến MẢNG MÁY PHỤ
            'sub_engine_kw' => array_values(array_map($formatVal, array_filter($subEngineKws, fn($v) => $v !== null && $v !== ''))),
            'sub_engine_hp' => array_values(array_map($formatVal, array_filter($subEngineHps, fn($v) => $v !== null && $v !== ''))),

            'engine_mark' => array_values(array_map($formatVal, array_filter($ship->engine_mark, fn($v) => $v !== null && $v !== ''))),
            'engine_number' => array_values(array_map($formatVal, array_filter($ship->engine_number, fn($v) => $v !== null && $v !== ''))),
            'sub_engine_mark' => array_values(array_map($formatVal, array_filter($ship->sub_engine_mark, fn($v) => $v !== null && $v !== ''))),
            'sub_engine_number' => array_values(array_map($formatVal, array_filter($ship->sub_engine_number, fn($v) => $v !== null && $v !== ''))),

            'technical_safety_number' => $ship->technical_safety_number,
            'technical_safety_date' => $ship->technical_safety_date ? \Carbon\Carbon::parse($ship->technical_safety_date)->format('d/m/Y') : '',
            'record_number' => $ship->record_number,
            'record_date' => $ship->record_date ? \Carbon\Carbon::parse($ship->record_date)->format('d/m/Y') : '',
            'expiration_date' => $ship->expiration_date ? \Carbon\Carbon::parse($ship->expiration_date)->format('d/m/Y') : '',
        ];
    }

    /**
     * Convert number to Vietnamese words
     */
    private function numberToWordsVN($number)
    {
        $dictionaries = [
            0 => 'không', 1 => 'một', 2 => 'hai', 3 => 'ba', 4 => 'bốn', 5 => 'năm',
            6 => 'sáu', 7 => 'bảy', 8 => 'tám', 9 => 'chín', 10 => 'mười',
            11 => 'mười một', 12 => 'mười hai', 13 => 'mười ba', 14 => 'mười bốn',
            15 => 'mười lăm', 16 => 'mười sáu', 17 => 'mười bảy', 18 => 'mười tám',
            19 => 'mười chín', 20 => 'hai mươi', 30 => 'ba mươi', 40 => 'bốn mươi',
            50 => 'năm mươi', 60 => 'sáu mươi', 70 => 'bảy mươi', 80 => 'tám mươi',
            90 => 'chín mươi', 100 => 'trăm', 1000 => 'nghìn', 1000000 => 'triệu',
            1000000000 => 'tỷ', 1000000000000 => 'nghìn tỷ'
        ];

        if (!is_numeric($number)) {
            return false;
        }

        if ($number < 0) {
            return 'âm ' . $this->numberToWordsVN(abs($number));
        }

        $string = null;
        $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionaries[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionaries[$tens];
                if ($units) {
                    $string .= ' ' . ($units == 1 ? 'mốt' : ($units == 5 ? 'lăm' : $dictionaries[$units]));
                }
                break;
            case $number < 1000:
                $hundreds  = number_format($number / 100, 0, '.', ''); // prevent float
                $hundreds = explode('.', $hundreds)[0];
                $remainder = $number % 100;
                $string = $dictionaries[$hundreds] . ' ' . $dictionaries[100];
                if ($remainder) {
                    if ($remainder < 10) {
                        $string .= ' lẻ';
                    }
                    $string .= ' ' . $this->numberToWordsVN($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = $this->numberToWordsVN($numBaseUnits) . ' ' . $dictionaries[$baseUnit];
                if ($remainder) {
                    $mod = $remainder / pow(10, floor(log($remainder, 10)));
                    if ($remainder < 100) {
                        $string .= ' không trăm';
                        if ($remainder < 10) {
                            $string .= ' lẻ';
                        }
                    } elseif (floor($remainder / 100) == 0 && $remainder >= 100) {
                        $string .= ' không trăm';
                    }
                    $string .= ' ' . $this->numberToWordsVN($remainder);
                }
                break;
        }

        // Capitalize first letter
        $string = trim($string);
        if ($string != '') {
             $string = mb_strtoupper(mb_substr($string, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($string, 1, null, 'UTF-8');
        }

        return $string;
    }
}
