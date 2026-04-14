<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\User;
use App\Notifications\MaintenanceNotification;
use Illuminate\Support\Facades\Notification;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        $currentUserId = auth()->id();

        $admins = User::whereHas('role', function($q) {
            $q->where('name', 'admin');
        })->where('id', '!=', $currentUserId)->get(); // Exclude current user

        if ($admins->isEmpty()) return;

        $title = "🛒 Đơn hàng mới: " . $order->code;
        $buildingName = $order->building->name ?? 'N/A';
        $amount = number_format($order->total_amount, 0, ',', '.') . ' ₫';
        $body = "Đơn hàng mới tại {$buildingName} với tổng giá trị {$amount}.";
        $url = route('admin.dashboard');

        Notification::send($admins, new MaintenanceNotification($title, $body, $url, 'fas fa-shopping-cart', 'order'));
    }
}
