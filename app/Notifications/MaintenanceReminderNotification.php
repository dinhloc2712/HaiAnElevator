<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\ZaloChannel;

class MaintenanceReminderNotification extends Notification
{
    use Queueable;

    protected $elevator;

    /**
     * Create a new notification instance.
     */
    public function __construct($elevator)
    {
        $this->elevator = $elevator;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [ZaloChannel::class, 'database'];
    }

    /**
     * Get the Zalo representation of the notification.
     */
    public function toZalo(object $notifiable): array
    {
        $elevator = $this->elevator;
        $buildingName = optional($elevator->building)->name ?? 'N/A';
        
        $fullAddress = trim(($elevator->address ?? '') . ', ' . ($elevator->district ?? '') . ', ' . ($elevator->province ?? ''));
        $fullAddress = trim($fullAddress, ', ');

        return [
            'phone' => '84966471929',
            'template_id' => config('services.zalo.template_id'),
            'template_data' => [
                'customer_name'   => $elevator->customer_name ?? 'Quý khách',
                'building_name'   => $buildingName,
                'elevator_code'   => $elevator->code,
                'maintenance_day' => optional($elevator->maintenance_deadline)->format('d/m/Y') ?? 'N/A',
                'address'         => $fullAddress ?: 'N/A',
                'phone'           => $elevator->customer_phone ?? 'N/A',
            ]
        ];
    }

    /**
     * Get the array representation of the notification for database logs.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Nhắc lịch bảo trì: ' . $this->elevator->code,
            'body' => "Thang máy tại {$this->elevator->building->name} sẽ đến hạn bảo trì vào ngày " . optional($this->elevator->maintenance_deadline)->format('d/m/Y'),
            'type' => 'maintenance_reminder',
            'elevator_id' => $this->elevator->id
        ];
    }
}
