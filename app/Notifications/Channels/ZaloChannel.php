<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use App\Services\ZaloService;

class ZaloChannel
{
    protected $zalo;

    public function __construct(ZaloService $zalo)
    {
        $this->zalo = $zalo;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toZalo')) {
            return;
        }

        $message = $notification->toZalo($notifiable);

        if (empty($message)) {
            return;
        }

        // Lấy số điện thoại từ object $notifiable (User hoặc Customer) hoặc từ route (AnonymousNotifiable)
        $phone = $notifiable->phone ?? $notifiable->phone_number ?? null;
        
        if (!$phone && method_exists($notifiable, 'routeNotificationFor')) {
            $phone = $notifiable->routeNotificationFor('zalo');
        }

        if (!$phone) {
            \Log::warning('ZaloChannel: No phone number found for notification');
            return;
        }

        \Log::info('ZaloChannel: Attempting to send ZNS to ' . $phone);

        // Gửi qua ZNS (nếu có template_id) hoặc tin nhắn OA thường
        if (isset($message['template_id'])) {
            $this->zalo->sendZNS(
                $phone,
                $message['template_id'],
                $message['template_data'] ?? []
            );
        } else if (isset($message['text'])) {
            // Cần có zalo_user_id để gửi tin nhắn OA trực tiếp
            $zaloUserId = $notifiable->zalo_user_id ?? null;
            if ($zaloUserId) {
                $this->zalo->sendOAMessage($zaloUserId, $message['text']);
            }
        }
    }
}
