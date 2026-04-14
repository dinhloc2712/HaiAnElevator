<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phieu_Bao_Tri_{{ $maintenance->elevator->code }}_{{ $maintenance->check_date->format('Ymd') }}</title>
    <style>
        @page {
            size: A4;
            margin: 7mm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 9pt;
            line-height: 1.25;
            color: #000;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        .no-print-btn {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 5px 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-family: sans-serif;
            font-weight: bold;
            z-index: 9999;
            font-size: 8pt;
        }

        @media print {
            .no-print-btn { display: none; }
            body { background: none; }
            .page-container { border: none !important; box-shadow: none !important; margin: 0 !important; width: 100% !important; padding: 0 !important; }
        }

        .page-container {
            width: 210mm;
            margin: 0px auto;
            padding: 0;
            box-sizing: border-box;
            position: relative;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 2px 4px;
            vertical-align: middle;
        }

        .no-border table, .no-border td, .no-border th {
            border: none !important;
        }

        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .text-uppercase { text-transform: uppercase; }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2px;
        }

        .header-left { width: 55%; font-size: 8.5pt; font-weight: bold; }
        .header-right { width: 40%; text-align: right; font-size: 8.5pt; font-weight: bold; }

        .doc-title {
            font-size: 13pt;
            font-weight: bold;
            text-align: center;
            margin: 5px 0;
        }

        .info-table td {
            height: 20px;
            font-size: 8.5pt;
        }

        .checklist-container {
            display: flex;
            gap: 0;
            margin-top: 5px;
        }

        .checklist-col {
            flex: 1;
        }

        .checklist-table th {
            background: #eee;
            font-size: 8pt;
            height: 18px;
        }

        .checklist-table td {
            font-size: 8pt;
            height: 18px;
        }

        .section-row {
            background: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }

        .footer-section {
            display: flex;
            margin-top: 8px;
            gap: 10px;
        }

        .footer-left { width: 45%; }
        .footer-right { width: 55%; }

        .evaluation-text {
            border: 1px solid #000;
            min-height: 50px;
            padding: 4px;
            margin-top: 3px;
            font-style: italic;
            font-size: 8.5pt;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px;
            margin-top: 5px;
        }

        .signature-item { font-size: 8.5pt; border-bottom: 1px dotted #000; height: 16px; margin-bottom: 2px; }

        .client-confirm-box {
            border: 1px solid #000;
            padding: 5px;
            margin-top: 8px;
            font-size: 8.5pt;
        }
    </style>
</head>
<body>
    <button class="no-print-btn" onclick="window.print()">IN PHIẾU / PDF</button>

    <div class="page-container">
        <div class="header-top no-border">
            <div class="header-left">
                CÔNG TY TNHH THANG MÁY & THIẾT BỊ HẢI AN<br>
                VPDD: Nhà số 226, Đ. Hải Thượng Lãn Ông, TP Vinh, NA
            </div>
            <div class="header-right">
                PHÒNG BẢO TRÌ<br>
                Trực 24/24h: 096.330.7879, 091.121.0383
            </div>
        </div>

        <div class="doc-title">CÁC HẠNG MỤC BẢO DƯỠNG THANG MÁY</div>

        <table class="info-table">
            <tr>
                <td class="text-bold" style="width: 15%;">Tên công trình</td>
                <td style="width: 50%;">{{ $maintenance->elevator->building->name ?? 'N/A' }}</td>
                <td class="text-bold" style="width: 15%;">Loại thang</td>
                <td>{{ $maintenance->elevator->type ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="text-bold">Địa chỉ</td>
                <td>{{ $maintenance->elevator->building->address ?? 'N/A' }}</td>
                <td class="text-bold">Số tầng</td>
                <td>{{ $maintenance->elevator->floors ?? '...' }}</td>
            </tr>
            <tr>
                <td class="text-bold">Thời gian</td>
                <td>Ngày: {{ $maintenance->check_date->format('d') }} &nbsp; Tháng: {{ $maintenance->check_date->format('m') }} &nbsp; Năm: {{ $maintenance->check_date->year }}</td>
                <td class="text-bold">Tải trọng</td>
                <td>{{ $maintenance->elevator->capacity ?? '...' }} kg</td>
            </tr>
        </table>

        @php
            // Prepare flat list of items to split into 2 columns
            $allItems = [];
            foreach($sections as $sectionName => $items) {
                $allItems[] = ['type' => 'section', 'name' => $sectionName];
                foreach($items as $id => $name) {
                    $allItems[] = ['type' => 'item', 'id' => $id, 'name' => $name];
                }
            }
            
            $total = count($allItems);
            $half = ceil($total / 2);
            $leftSide = array_slice($allItems, 0, $half);
            $rightSide = array_slice($allItems, $half);
            
            $results = is_array($maintenance->results) ? $maintenance->results : [];
        @endphp

        <div class="checklist-container">
            {{-- COLUMN LEFT --}}
            <div class="checklist-col">
                <table class="checklist-table">
                    <tr>
                        <th style="width: 30px;">TT</th>
                        <th>Nội dung</th>
                        <th style="width: 80px;">Kết quả</th>
                    </tr>
                    @foreach($leftSide as $index => $row)
                        @if($row['type'] == 'section')
                            <tr class="section-row">
                                <td colspan="3">{{ $row['name'] }}</td>
                            </tr>
                        @else
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $row['name'] }}</td>
                                <td class="text-center">
                                    @php 
                                        $val = $results[$row['id']] ?? '';
                                        $statusName = $symbols[$val] ?? '';
                                        $statusName = preg_replace('/[Δ√#XA\/K]\s*/', '', $statusName);
                                    @endphp
                                    {{ $statusName }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </table>
            </div>
            
            {{-- COLUMN RIGHT --}}
            <div class="checklist-col" style="border-left: none;">
                <table class="checklist-table" style="border-left: none;">
                    <tr>
                        <th style="width: 30px; border-left: none;">TT</th>
                        <th>Nội dung</th>
                        <th style="width: 80px;">Kết quả</th>
                    </tr>
                    @foreach($rightSide as $index => $row)
                        @if($row['type'] == 'section')
                            <tr class="section-row">
                                <td colspan="3" style="border-left: none;">{{ $row['name'] }}</td>
                            </tr>
                        @else
                            <tr>
                                <td class="text-center" style="border-left: none;">{{ $half + $index + 1 }}</td>
                                <td>{{ $row['name'] }}</td>
                                <td class="text-center">
                                    @php 
                                        $val = $results[$row['id']] ?? '';
                                        $statusName = $symbols[$val] ?? '';
                                        $statusName = preg_replace('/[Δ√#XA\/K]\s*/', '', $statusName);
                                    @endphp
                                    {{ $statusName }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </table>
            </div>
        </div>

        <div class="footer-section no-border" style="border: none;">
            <div class="footer-left">
                <div class="text-bold text-center text-uppercase" style="border-bottom: 1px solid #000;">ĐÁNH GIÁ, NHẬN XÉT</div>
                <div class="evaluation-text">
                    {{ $maintenance->evaluation ?: '............................................................................................................................................................................................................' }}
                </div>
                <div style="font-size: 8pt; margin-top: 10px; font-weight: bold; text-align: center;">
                    THỜI GIAN KIỂM TRA LẦN TRƯỚC VÀ LẦN SAU TỐI ĐA KHÔNG QUÁ 60 NGÀY
                </div>
            </div>

            <div class="footer-right">
                <div class="text-bold text-center text-uppercase" style="border-bottom: 1px solid #000;">CÁN BỘ KỸ THUẬT</div>
                <div class="signature-grid">
                    @php
                        $staffArray = array_filter(explode(',', $maintenance->staff_names));
                        for ($i = 0; $i < 6; $i++) {
                            $staffName = $staffArray[$i] ?? '';
                            $display = ($i + 1) . '. ' . ($staffName ?: '........................................');
                            echo '<div class="signature-item">' . $display . '</div>';
                        }
                    @endphp
                </div>

                <div class="client-confirm-box">
                    <div class="text-bold text-center text-uppercase" style="text-decoration: underline; margin-bottom: 5px;">Đại diện khách hàng xác nhận:</div>
                    @php
                        // Extract hour and minute if times are provided
                        $startHour = ''; $startMin = '';
                        if ($maintenance->start_time) {
                            $parts = explode(':', $maintenance->start_time);
                            $startHour = $parts[0] ?? '--';
                            $startMin = $parts[1] ?? '--';
                        }
                        $endHour = ''; $endMin = '';
                        if ($maintenance->end_time) {
                            $parts = explode(':', $maintenance->end_time);
                            $endHour = $parts[0] ?? '--';
                            $endMin = $parts[1] ?? '--';
                        }
                    @endphp
                    <div>1. Số người thực hiện công việc: <strong style="border-bottom: 1px solid #000; padding: 0 10px;">{{ $maintenance->performer_count ?? '.......' }}</strong> người.</div>
                    <div>2. Thời gian thực hiện công việc:</div>
                    <div style="margin-left: 20px;">- Bắt đầu: <strong style="border-bottom: 1px solid #000; padding: 0 10px;">{{ $startHour }}</strong> giờ <strong style="border-bottom: 1px solid #000; padding: 0 10px;">{{ $startMin }}</strong> phút.</div>
                    <div style="margin-left: 20px;">- Kết thúc: <strong style="border-bottom: 1px solid #000; padding: 0 10px;">{{ $endHour }}</strong> giờ <strong style="border-bottom: 1px solid #000; padding: 0 10px;">{{ $endMin }}</strong> phút.</div>
                    <div style="margin-top: 5px;">3. Ký xác nhận</div>
                    <div style="height: 50px;"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Optional: Auto open print dialog
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
