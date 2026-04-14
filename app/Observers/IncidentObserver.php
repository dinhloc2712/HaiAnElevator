<?php

namespace App\Observers;

use App\Models\Incident;
use App\Models\User;
use App\Notifications\MaintenanceNotification;
use Illuminate\Support\Facades\Notification;

class IncidentObserver
{
    /**
     * Handle the Incident "created" event.
     */
    public function created(Incident $incident): void
    {
        $this->notifyTechnicians($incident, '🚨 Sự cố mới');
    }

    /**
     * Handle the Incident "updated" event.
     */
    public function updated(Incident $incident): void
    {
        if ($incident->isDirty('status') || $incident->isDirty('staff_ids') || $incident->isDirty('priority')) {
            $this->notifyTechnicians($incident, 'Cập nhật sự cố');
        }
    }

    /**
     * Notify all assigned technicians
     */
    protected function notifyTechnicians(Incident $incident, $titlePrefix)
    {
        $elevatorCode = $incident->elevator->code ?? 'N/A';
        $title = $titlePrefix . ': thang máy ' . $elevatorCode;
        $body = "Bạn được gán xử lý sự cố. Mức độ: " . trans('messages.' . $incident->priority ?? $incident->priority);
        $url = route('admin.incidents.show', $incident->id);

        $staffIds = $incident->staff_ids ?? [];

        if (empty($staffIds)) return;

        // Exclude the person who made the change
        $currentUserId = auth()->id();
        $recipientIds = array_filter($staffIds, fn($id) => $id != $currentUserId);

        if (empty($recipientIds)) return;

        $technicians = User::whereIn('id', $recipientIds)->get();

        Notification::send($technicians, new MaintenanceNotification($title, $body, $url, 'fas fa-exclamation-triangle', 'incident'));
    }

    /**
     * Handle the Incident "deleted" event.
     */
    public function deleted(Incident $incident): void
    {
        //
    }

    /**
     * Handle the Incident "restored" event.
     */
    public function restored(Incident $incident): void
    {
        //
    }

    /**
     * Handle the Incident "force deleted" event.
     */
    public function forceDeleted(Incident $incident): void
    {
        //
    }
}
