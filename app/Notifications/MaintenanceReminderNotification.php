<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\ZaloChannel;

class MaintenanceReminderNotification extends Notification
{
    use Queueable;

    protected $elevator;
    protected ?string $templateId;
    protected string $type;

    /**
     * Create a new notification instance.
     *
     * @param  mixed        $elevator
     * @param  string|null  $templateId  Template ID tùy chỉnh; null = dùng template mặc định từ config
     * @param  string       $type        Loại thông báo: 'maintenance' (bảo trì) hoặc 'inspection' (kiểm định)
     */
    public function __construct($elevator, ?string $templateId = null, string $type = 'maintenance')
    {
        $this->elevator   = $elevator;
        $this->type       = $type;
        
        if ($templateId) {
            $this->templateId = $templateId;
        } else {
            $this->templateId = $type === 'inspection' 
                ? config('services.zalo.inspection_template_id') 
                : config('services.zalo.maintenance_template_id');
        }
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

        $templateData = [
            'customer_name'   => $elevator->customer_name ?? 'Quý khách',
            'building_name'   => $buildingName,
            'elevator_code'   => $elevator->code,
            'address'         => $fullAddress ?: 'N/A',
            'phone'           => $elevator->customer_phone ?? 'N/A',
        ];

        if ($this->type === 'inspection') {
            $templateData['inspection_date'] = optional($elevator->inspection_date)->format('d/m/Y') ?? 'N/A';
        } else {
            $templateData['maintenance_day'] = optional($elevator->maintenance_deadline)->format('d/m/Y') ?? 'N/A';
        }

        return [
            'phone'         => $elevator->customer_phone ?? '84966471929',
            'template_id'   => $this->templateId,
            'template_data' => $templateData,
        ];
    }

    /**
     * Get the array representation of the notification for database logs.
     */
    public function toArray(object $notifiable): array
    {
        if ($this->type === 'inspection') {
            return [
                'title' => 'Nhắc lịch kiểm định: ' . $this->elevator->code,
                'body' => "Thang máy tại {$this->elevator->building->name} sẽ đến hạn kiểm định vào ngày " . optional($this->elevator->inspection_date)->format('d/m/Y'),
                'type' => 'inspection_reminder',
                'elevator_id' => $this->elevator->id
            ];
        }

        return [
            'title' => 'Nhắc lịch bảo trì: ' . $this->elevator->code,
            'body' => "Thang máy tại {$this->elevator->building->name} sẽ đến hạn bảo trì vào ngày " . optional($this->elevator->maintenance_deadline)->format('d/m/Y'),
            'type' => 'maintenance_reminder',
            'elevator_id' => $this->elevator->id
        ];
    }
}
