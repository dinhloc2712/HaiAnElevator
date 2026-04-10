<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ship;
use Carbon\Carbon;

class ShipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ships = [
            [
                'registration_number' => 'QNg-90123-TS',
                'registration_date' => Carbon::now()->subYears(2),
                'status' => 'active',
                'name' => 'Tàu Hoàng Sa 01',
                'hull_number' => 'HS-01',
                'usage' => 'Khai thác thủy sản',
                'operation_area' => 'Vùng khơi',
                'crew_size' => 12,
                'main_occupation' => 'Lưới kéo',
                'secondary_occupation' => 'Câu mực',
                'owner_name' => 'Nguyễn Văn A',
                'owner_id_card' => '123456789012',
                'owner_phone' => '0912345678',
                'address' => 'Thôn Đông, Xã An Hải, Huyện Lý Sơn',
                'province_id' => '51', // Example ID for Quang Ngai
                'ward_id' => '001', // Example
            ],
            [
                'registration_number' => 'QNg-95678-TS',
                'registration_date' => Carbon::now()->subYears(1),
                'status' => 'active',
                'name' => 'Tàu Trường Sa 02',
                'hull_number' => 'TS-02',
                'usage' => 'Khai thác thủy sản',
                'operation_area' => 'Vùng lộng',
                'crew_size' => 8,
                'main_occupation' => 'Lưới vây',
                'secondary_occupation' => null,
                'owner_name' => 'Trần Văn B',
                'owner_id_card' => '987654321012',
                'owner_phone' => '0987123456',
                'address' => 'Xã Bình Châu, Huyện Bình Sơn',
                'province_id' => '51',
                'ward_id' => '002',
            ],
            [
                'registration_number' => 'QNg-11223-TS',
                'registration_date' => Carbon::now()->subMonths(6),
                'status' => 'suspended',
                'name' => 'Tàu Biển Đông 03',
                'hull_number' => 'BD-03',
                'usage' => 'Hậu cần nghề cá',
                'operation_area' => 'Vùng khơi',
                'crew_size' => 5,
                'main_occupation' => 'Thu mua',
                'secondary_occupation' => null,
                'owner_name' => 'Lê Thị C',
                'owner_id_card' => '555555555555',
                'owner_phone' => '0905123456',
                'address' => 'Phường Phổ Thạnh, Thị xã Đức Phổ',
                'province_id' => '51',
                'ward_id' => '003',
            ],
        ];

        foreach ($ships as $ship) {
            Ship::firstOrCreate(
                ['registration_number' => $ship['registration_number']],
                $ship
            );
        }
    }
}
