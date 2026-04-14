<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class MaintenanceNotification extends Notification
{
    use Queueable;

    protected $title;
    protected $body;
    protected $url;
    protected $icon;
    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct($title, $body, $url = '/', $icon = 'fas fa-bell', $type = 'system')
    {
        $this->title = $title;
        $this->body = $body;
        $this->url = $url;
        $this->icon = $icon;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class, 'database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            'icon' => $this->icon,
            'type' => $this->type
        ];
    }

    /**
     * Get the web push representation of the notification.
     */
    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title($this->title)
            ->icon(url('/logo.png')) // Use absolute URL for icons
            ->body($this->body)
            ->action('Xem chi tiết', 'view_action')
            ->data(['url' => $this->url]); // Use data() instead of options()
    }
}
