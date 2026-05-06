<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Elevator;
use App\Notifications\MaintenanceReminderNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendMaintenanceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gửi thông báo nhắc lịch bảo trì cho khách hàng trước 3 ngày';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetDate = Carbon::now()->addDays(3)->format('Y-m-d');
        
        $this->info("Đang kiểm tra các thang máy có lịch bảo trì vào ngày: {$targetDate}");

        $elevators = Elevator::whereDate('maintenance_deadline', $targetDate)
            ->where('status', 'active')
            ->get();

        if ($elevators->isEmpty()) {
            $this->info("Không có thang máy nào cần nhắc lịch.");
            return;
        }

        foreach ($elevators as $elevator) {
            try {
                if ($elevator->customer_phone) {
                    $elevator->notify(new MaintenanceReminderNotification($elevator));
                    $this->info("Đã gửi thông báo cho thang máy: {$elevator->code} (SĐT: {$elevator->customer_phone})");
                } else {
                    $this->warn("Thang máy {$elevator->code} không có số điện thoại khách hàng.");
                }
            } catch (\Exception $e) {
                $this->error("Lỗi khi gửi thông báo cho {$elevator->code}: " . $e->getMessage());
                Log::error("Maintenance Reminder Error [{$elevator->code}]: " . $e->getMessage());
            }
        }

        $this->info("Hoàn tất gửi thông báo.");
    }
}
