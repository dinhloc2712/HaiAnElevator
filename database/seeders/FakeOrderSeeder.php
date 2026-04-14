<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Building;
use App\Models\Elevator;
use Carbon\Carbon;

class FakeOrderSeeder extends Seeder
{
    public function run()
    {
        $buildings = Building::all();
        $elevators = Elevator::all();

        if ($buildings->isEmpty() || $elevators->isEmpty()) {
            echo "Vui lòng tạo trước Building và Elevator.\n";
            return;
        }

        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Order::truncate();
        OrderItem::truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Target: Create orders for the last 6 months
        for ($i = 5; $i >= 0; $i--) {
            // Generate 3 to 7 orders per month
            $ordersCount = rand(3, 7);
            
            for ($o = 0; $o < $ordersCount; $o++) {
                $monthDate = Carbon::now()->subMonths($i)->startOfMonth()->addDays(rand(1, 28));
                
                // Randomly select building and its elevator
                $building = $buildings->random();
                $buildingElevators = $elevators->where('building_id', $building->id);
                $elevator = $buildingElevators->count() > 0 ? $buildingElevators->random() : $elevators->random();

                // Order amount and status
                // To make the chart look nice, let's make most of them 'paid'
                $status = rand(1, 10) > 2 ? 'paid' : 'pending'; 
                
                // Generate items
                $itemsCount = rand(1, 3);
                $totalAmount = 0;
                $items = [];

                for ($j = 0; $j < $itemsCount; $j++) {
                    $price = rand(10, 50) * 100000; // 1M to 5M
                    $qty = rand(1, 2);
                    $subtotal = $price * $qty;
                    $totalAmount += $subtotal;

                    $items[] = [
                        'service_name' => 'Dịch vụ bảo trì / linh kiện phụ trợ #' . rand(100, 999),
                        'price' => $price,
                        'quantity' => $qty,
                        'subtotal' => $subtotal,
                        'created_at' => $monthDate,
                        'updated_at' => $monthDate,
                    ];
                }

                // Create Order
                $orderCode = 'HD-' . $monthDate->format('Ymd') . '-' . str_pad($o + 1, 3, '0', STR_PAD_LEFT);
                $order = Order::create([
                    'code' => $orderCode,
                    'building_id' => $building->id,
                    'elevator_id' => $elevator->id,
                    'total_amount' => $totalAmount,
                    'status' => $status,
                    'created_at' => $monthDate,
                    'updated_at' => $monthDate,
                ]);

                // Create Items
                foreach ($items as $itemData) {
                    $itemData['order_id'] = $order->id;
                    OrderItem::create($itemData);
                }
            }
        }
        
        echo "Đã fake xong dữ liệu Order 6 tháng gần nhất.\n";
    }
}
