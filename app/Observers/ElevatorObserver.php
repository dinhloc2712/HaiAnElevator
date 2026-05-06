<?php

namespace App\Observers;

use App\Models\Elevator;
use App\Notifications\MaintenanceReminderNotification;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class ElevatorObserver
{
    /**
     * Handle the Elevator "updated" event.
     */
    public function updated(Elevator $elevator): void
    {
        // Kiểm tra xem trường maintenance_deadline có thực sự bị thay đổi hay không
        $oldDeadline = $elevator->getOriginal('maintenance_deadline');
        $newDeadline = $elevator->maintenance_deadline;

        // Chỉ xử lý nếu ngày mới khác ngày cũ và ngày mới không rỗng
        if ($newDeadline && $newDeadline != $oldDeadline) {
            
            $deadline = Carbon::parse($newDeadline)->startOfDay();
            $today = Carbon::today();
            
            // Kiểm tra nếu ngày hạn mới cách hôm nay đúng 3 ngày
            if ($deadline->diffInDays($today) === 3) {
                if ($elevator->customer_phone) {
                    Notification::route('zalo', $elevator->customer_phone)
                        ->notify(new MaintenanceReminderNotification($elevator));
                }
            }
        }
    }

    /**
     * Handle the Elevator "created" event.
     */
    public function created(Elevator $elevator): void
    {
        // Kiểm tra ngay khi tạo mới, nếu ngày hạn cách hôm nay 3 ngày thì gửi luôn
        if ($elevator->maintenance_deadline) {
            $deadline = Carbon::parse($elevator->maintenance_deadline)->startOfDay();
            $today = Carbon::today();
            
            if ($deadline->diffInDays($today) === 3) {
                if ($elevator->customer_phone) {
                    Notification::route('zalo', $elevator->customer_phone)
                        ->notify(new MaintenanceReminderNotification($elevator));
                }
            }
        }
    }
}
