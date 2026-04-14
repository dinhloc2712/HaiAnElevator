<?php

namespace App\Observers;

use App\Models\MaintenanceCheck;
use App\Models\User;
use App\Notifications\MaintenanceNotification;
use Illuminate\Support\Facades\Notification;

class MaintenanceCheckObserver
{
    /**
     * Handle the MaintenanceCheck "created" event.
     */
    public function created(MaintenanceCheck $maintenanceCheck): void
    {
        $this->notifyTechnicians($maintenanceCheck, 'Lịch bảo trì mới');
    }

    /**
     * Handle the MaintenanceCheck "updated" event.
     */
    public function updated(MaintenanceCheck $maintenanceCheck): void
    {
        // Only notify if status changed or someone was added
        if ($maintenanceCheck->isDirty('status') || $maintenanceCheck->isDirty('staff_ids')) {
            $this->notifyTechnicians($maintenanceCheck, 'Cập nhật lịch bảo trì');
        }
    }

    /**
     * Notify all involved technicians
     */
    protected function notifyTechnicians(MaintenanceCheck $check, $titlePrefix)
    {
        $elevatorCode = $check->elevator->code ?? 'N/A';
        $title = $titlePrefix . ': ' . $elevatorCode;
        $body = "Bạn được gán vào lịch bảo trì của thang máy " . $elevatorCode;
        $url = route('admin.maintenance.show', $check->id);

        // Get unique staff IDs
        $staffIds = $check->staff_ids ?? [];
        if ($check->user_id && !in_array($check->user_id, $staffIds)) {
            $staffIds[] = $check->user_id;
        }

        if (empty($staffIds)) return;

        // Exclude the person who made the change - they don't need a notification
        $currentUserId = auth()->id();
        $recipientIds = array_filter($staffIds, fn($id) => $id != $currentUserId);

        if (empty($recipientIds)) return;

        $technicians = User::whereIn('id', $recipientIds)->get();

        Notification::send($technicians, new MaintenanceNotification($title, $body, $url, 'fas fa-tools', 'maintenance'));
    }
}
