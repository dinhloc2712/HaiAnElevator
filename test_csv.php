<?php

$lines = [
"SĐK	Họ tên chủ tàu	phường/xã mới	Tỉnh/TP	Nghề cụ thể	Tổng dung tích	Trọng tải (tấn)	Ltk	Btk	Lmax	Bmax	Dmax	d 	Vật liệu vỏ	Thuyền viên	Năm đóng	Nơi đóng	Ký hiệu máy	Số máy 	MÁY1	MÁY2	MÁY3	MÁY1 (KW)	MÁY 2 (KW)	MÁY 3 (KW)	Số ATKT	Số Biên bản	Ngày cấp ATKT	Ngày cấp BB	Ngày hết hạn đăng kiểm	Vùng Hoạt Động	Số điện thoại	Số CCCD",
"ST-90098-TS	Tô Thị Thu	TT Trần Đề	Sóc Trăng	Lưới kéo	57.00		17.00	4.80	19.60	4.92	2.35	1.70	Gỗ	4	1996		Hino V26C	A 10385	450			331.2			90098/ST	18/25T	03/01/2025	01/01/2025	01/01/2026	Hạn chế II	0965773434	"
];

foreach ($lines as $line) {
    $delimiter = ',';
    if (strpos($line, "\t") !== false) {
        $delimiter = "\t";
    } elseif (strpos($line, ";") !== false) {
        $delimiter = ";";
    }
    
    $row = str_getcsv($line, $delimiter);
    $row = array_pad($row, 33, '');
    
    echo "Row SĐK: " . $row[0] . "\n";
    echo "Owner: " . $row[1] . "\n";
    echo "Ltk: " . $row[7] . "\n";
    echo "ATKT: " . $row[25] . "\n";
    echo "Date: " . $row[27] . " -> " . date('Y-m-d', strtotime(str_replace('/', '-', trim($row[27])))) . "\n";
    echo "Expiration: " . trim($row[29]) . " -> " . date('Y-m-d', strtotime(str_replace('/', '-', trim($row[29])))) . "\n";
    echo "-------------------\n";
}
