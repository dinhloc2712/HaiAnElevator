<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Elevator;
use App\Models\User;
use App\Models\MaintenanceCheck;
use App\Notifications\MaintenanceNotification;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class NotifyNeglectedMaintenance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:neglected-maintenance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Thông báo cho Admin về các thang máy sắp đến hạn bảo trì nhưng chưa có nhật ký.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        
        // Mốc 7 ngày và 3 ngày
        $targets = [7, 3];

        foreach ($targets as $days) {
            $targetDate = $today->copy()->addDays($days);
            
            // Lấy danh sách thang máy có hạn đúng vào ngày mục tiêu
            $elevators = Elevator::with('building')
                ->whereDate('maintenance_deadline', $targetDate)
                ->get();

            foreach ($elevators as $elevator) {
                // Kiểm tra xem đã có nhật ký bảo trì nào trong vòng 30 ngày qua chưa
                // Hoặc có thể kiểm tra từ ngày chu kỳ trước: (deadline - cycle_days)
                $cycleDays = $elevator->cycle_days ?? 30;
                $startDate = $targetDate->copy()->subDays($cycleDays);

                $exists = MaintenanceCheck::where('elevator_id', $elevator->id)
                    ->whereBetween('check_date', [$startDate, $today])
                    ->exists();

                if (!$exists) {
                    $this->sendAlertToAdmins($elevator, $days);
                }
            }
        }

        $this->info('Đã hoàn thành kiểm tra và gửi thông báo.');
    }

    /**
     * Gửi thông báo cho toàn bộ Admin
     */
    protected function sendAlertToAdmins($elevator, $daysLeft)
    {
        $admins = User::whereHas('role', function($q) {
            $q->where('name', 'admin');
        })->get();

        if ($admins->isEmpty()) return;

        $title = "⚠️ Cảnh báo bảo trì: " . $elevator->code;
        $body = "Thang máy tại {$elevator->building->name} còn {$daysLeft} ngày đến hạn nhưng chưa có nhật ký bảo trì mới.";
        $url = route('admin.elevators.index'); // Hoặc dẫn đến trang quản lý tòa nhà

        Notification::send($admins, new MaintenanceNotification($title, $body, $url));
        
        $this->line("Đã gửi cảnh báo {$daysLeft} ngày cho Admin về thang máy {$elevator->code}");
    }
}
