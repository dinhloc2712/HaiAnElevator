<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MaintenanceCheck;
use App\Models\Elevator;
use App\Models\User;
use Carbon\Carbon;

class FakeFaultSeeder extends Seeder
{
    public function run()
    {
        $elevators = Elevator::all();
        $user = User::first(); // Just get a user for user_id

        if ($elevators->isEmpty() || !$user) {
            echo "Vui lòng tạo trước Elevator và User.\n";
            return;
        }

        $categories = ['Cơ khí', 'Hệ điều khiển', 'Điện', 'Khác'];
        
        // Let's create about 30 repair tickets scattered over the last few months
        for ($i = 0; $i < 30; $i++) {
            $monthOffset = rand(0, 5); // 0 to 5 months ago
            $date = Carbon::now()->subMonths($monthOffset)->startOfMonth()->addDays(rand(1, 28));
            
            // Randomly select elevator
            $elevator = $elevators->random();
            
            // Randomly select category based on a distribution
            // Cơ khí 40%, Hệ điều khiển 30%, Cửa tầng 20%, Khác 10%
            $rand = rand(1, 100);
            if ($rand <= 40) {
                $categoriesArr = ['Cơ khí'];
                if (rand(1,10) > 8) $categoriesArr[] = 'Khác';
            } elseif ($rand <= 70) {
                $categoriesArr = ['Hệ điều khiển'];
                if (rand(1,10) > 7) $categoriesArr[] = 'Cơ khí';
            } elseif ($rand <= 90) {
                $categoriesArr = ['Điện'];
                if (rand(1,10) > 6) $categoriesArr[] = 'Khác';
            } else {
                $categoriesArr = ['Khác'];
            }

            MaintenanceCheck::create([
                'elevator_id' => $elevator->id,
                'user_id' => $user->id,
                'status' => 'completed',
                'task_type' => 'repair',
                'fault_category' => $categoriesArr,
                'check_date' => $date,
                'evaluation' => 'Đã khắc phục sự cố ' . implode(', ', $categoriesArr),
                'staff_names' => 'KTV Hỗ trợ',
                'performer_count' => rand(1, 2),
            ]);
        }
        
        echo "Đã fake xong 30 bản ghi sự cố thang máy (task_type=repair).\n";
    }
}
