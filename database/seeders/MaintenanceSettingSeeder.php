<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MaintenanceCategory;
use App\Models\MaintenanceItem;
use App\Models\MaintenanceStatus;

class MaintenanceSettingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Data for Checklist Items
        $checklist = [
            '1. PHÒNG MÁY' => [
                'Môi trường phòng máy', 'Máy kéo', 'Phanh từ',
                'Puly dẫn hướng', 'Tủ điều khiển', 'Encoder',
                'Bộ hạn chế tốc độ', 'INTERCOM', 'Bộ cứu hộ ARD',
                'Bộ ắc quy cứu hộ', 'Dầu máy'
            ],
            '2. CABIN' => [
                'Nóc cabin', 'Puly nóc cabin', 'Shoes dẫn hướng cabin',
                'Shoes dẫn hướng đối trọng', 'Dầu ray cabin, đối trọng',
                'Hộp dầu cabin, đối trọng', 'Mô tơ cửa',
                'Phanh hãm sự cố vượt tốc', 'Công tắc an toàn', 'Cửa cabin'
            ],
            '3. CỬA TẦNG' => [
                'Nút gọi tầng', 'Hiển thị', 'Khóa liên động',
                'Cánh cửa', 'Cáp mềm', 'Công tắc giới hạn', 'Guốc cửa tầng'
            ],
            '4. GIẾNG THANG' => [
                'Cáp tải', 'Cáp GOV'
            ],
            '5. BUỒNG THANG' => [
                'Môi trường trong cabin', 'Hiển thị số', 'Đèn, quạt',
                'Cảm biến hồng ngoại', 'Độ bằng tầng', 'Nút bấm trong cabin'
            ],
            '6. HỐ THANG' => [
                'Môi trường hố thang', 'Công tắc an toàn',
                'K/cách đối trọng đến giảm chấn'
            ],
        ];

        $catOrder = 1;
        foreach ($checklist as $catName => $items) {
            $category = MaintenanceCategory::create([
                'name' => $catName,
                'sort_order' => $catOrder++
            ]);

            $itemOrder = 1;
            foreach ($items as $itemName) {
                MaintenanceItem::create([
                    'category_id' => $category->id,
                    'name' => $itemName,
                    'sort_order' => $itemOrder++
                ]);
            }
        }

        // 2. Data for Statuses (without symbols)
        $statuses = [
            'Bình thường',
            'Đã kiểm tra, bảo trì và hiệu chỉnh',
            'Đã thay thế',
            'Đã sửa chữa, đại tu',
            'Đang chờ thay thế, bổ sung',
            'Không sử dụng',
            'Không có thiết bị',
        ];

        $statusOrder = 1;
        foreach ($statuses as $statusName) {
            MaintenanceStatus::create([
                'name' => $statusName,
                'sort_order' => $statusOrder++
            ]);
        }
    }
}
