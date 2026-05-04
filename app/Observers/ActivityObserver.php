<?php

namespace App\Observers;

use Spatie\Activitylog\Models\Activity;
use App\Models\User;
use App\Notifications\MaintenanceNotification;
use Illuminate\Support\Facades\Notification;

class ActivityObserver
{
    /**
     * Handle the Activity "created" event.
     */
    public function created(Activity $activity): void
    {
        // Don't notify if there's no causer (e.g. system tasks)
        if (!$activity->causer || !($activity->causer instanceof User)) {
            return;
        }

        $causer = $activity->causer;
        
        // Optional: Don't notify if the causer is already an admin? 
        // Or keep it to notify other admins.
        
        $title = "Hoạt động mới: " . $causer->name;
        $body = $this->getActivityDescription($activity);
        $url = route('admin.activity-logs.index'); // Link to activity logs
        
        // Find all admins
        $admins = User::whereHas('role', function($q) {
            $q->where('name', 'admin');
        })->where('id', '!=', $causer->id)->get(); // Don't notify the causer themselves

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new MaintenanceNotification($title, $body, $url, 'fas fa-history', 'activity'));
        }
    }

    /**
     * Format a readable description for the activity.
     */
    protected function getActivityDescription(Activity $activity): string
    {
        $subject = $activity->subject_type;
        $subjectName = class_basename($subject);
        
        // Vietnamese translation for common actions
        $description = $activity->description;
        $action = match($description) {
            'created' => 'đã tạo mới',
            'updated' => 'đã cập nhật',
            'deleted' => 'đã xóa',
            'restored' => 'đã khôi phục',
            default => $description
        };

        $subjectDisplay = "";
        if ($activity->subject) {
            // Try to get a name or code from the subject
            $subjectDisplay = $activity->subject->name ?? $activity->subject->code ?? $activity->subject->title ?? "đối tượng #{$activity->subject_id}";
        } else {
            $subjectDisplay = "đối tượng #{$activity->subject_id}";
        }

        $userName = $activity->causer->name ?? 'Người dùng';
        return "{$userName} {$action} {$subjectName}: {$subjectDisplay}";
    }
}
