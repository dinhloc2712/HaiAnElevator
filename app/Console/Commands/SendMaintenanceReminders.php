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
    protected $description = 'Gửi thông báo ZNS nhắc lịch bảo trì và kiểm định của thang máy';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $parseDays = function($days) {
            if (empty($days)) return [];
            if (is_string($days)) {
                $days = trim($days);
                if (str_starts_with($days, '[') && str_ends_with($days, ']')) {
                    return array_map('intval', json_decode($days, true) ?? []);
                }
                return array_filter(array_map('intval', explode(',', $days)));
            }
            return array_map('intval', (array)$days);
        };

        // 1. Gửi thông báo BẢO TRÌ
        $maintTemplateId = config('services.zalo.maintenance_template_id');
        $maintDays = $parseDays(config('services.zalo.maintenance_days_before'));

        $this->info("--- BẮT ĐẦU QUÉT LỊCH BẢO TRÌ ---");
        if ($maintTemplateId && !empty($maintDays)) {
            foreach ($maintDays as $daysOffset) {
                $targetDate = Carbon::now()->addDays($daysOffset)->format('Y-m-d');
                $this->info("Kiểm tra mốc {$daysOffset} ngày trước hạn bảo trì (Hạn: {$targetDate})");

                $elevators = Elevator::whereDate('maintenance_deadline', $targetDate)
                    ->where('status', 'active')
                    ->get();

                if ($elevators->isEmpty()) {
                    $this->info("  Không có thang máy nào.");
                    continue;
                }

                $this->info("  Tìm thấy {$elevators->count()} thang máy.");
                foreach ($elevators as $elevator) {
                    if (!$elevator->customer_phone) {
                        $this->warn("  Thang máy {$elevator->code} không có SĐT khách hàng, bỏ qua.");
                        continue;
                    }

                    try {
                        $elevator->notify(new MaintenanceReminderNotification($elevator, $maintTemplateId, 'maintenance'));
                        $this->info("  ✓ Đã gửi thông báo bảo trì -> {$elevator->code} ({$elevator->customer_phone})");
                    } catch (\Exception $e) {
                        $this->error("  ✗ Lỗi gửi -> {$elevator->code}: " . $e->getMessage());
                        Log::error("Zalo Maintenance Notification Error [{$elevator->code}]: " . $e->getMessage());
                    }
                }
            }
        } else {
            $this->warn("Chưa cấu hình template hoặc ngày nhắc bảo trì. Bỏ qua.");
        }

        // 2. Gửi thông báo KIỂM ĐỊNH
        $inspTemplateId = config('services.zalo.inspection_template_id');
        $inspDays = $parseDays(config('services.zalo.inspection_days_before'));

        $this->info("\n--- BẮT ĐẦU QUÉT LỊCH KIỂM ĐỊNH ---");
        if ($inspTemplateId && !empty($inspDays)) {
            foreach ($inspDays as $daysOffset) {
                $targetDate = Carbon::now()->addDays($daysOffset)->format('Y-m-d');
                $this->info("Kiểm tra mốc {$daysOffset} ngày trước hạn kiểm định (Hạn: {$targetDate})");

                $elevators = Elevator::whereDate('inspection_date', $targetDate)
                    ->where('status', 'active')
                    ->get();

                if ($elevators->isEmpty()) {
                    $this->info("  Không có thang máy nào.");
                    continue;
                }

                $this->info("  Tìm thấy {$elevators->count()} thang máy.");
                foreach ($elevators as $elevator) {
                    if (!$elevator->customer_phone) {
                        $this->warn("  Thang máy {$elevator->code} không có SĐT khách hàng, bỏ qua.");
                        continue;
                    }

                    try {
                        $elevator->notify(new MaintenanceReminderNotification($elevator, $inspTemplateId, 'inspection'));
                        $this->info("  ✓ Đã gửi thông báo kiểm định -> {$elevator->code} ({$elevator->customer_phone})");
                    } catch (\Exception $e) {
                        $this->error("  ✗ Lỗi gửi -> {$elevator->code}: " . $e->getMessage());
                        Log::error("Zalo Inspection Notification Error [{$elevator->code}]: " . $e->getMessage());
                    }
                }
            }
        } else {
            $this->warn("Chưa cấu hình template hoặc ngày nhắc kiểm định. Bỏ qua.");
        }

        $this->info("\nHoàn tất xử lý thông báo nhắc lịch.");
    }
}
