<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Display a listing of system notifications
     */
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(20);
        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Mark a system notification as read
     */
    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
            
            $url = $notification->data['url'] ?? route('admin.dashboard');
            return redirect($url);
        }

        return redirect()->back()->with('error', 'Không tìm thấy thông báo.');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        // Mark database notifications as read
        auth()->user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'Đã đánh dấu tất cả thông báo là đã xem.');
    }
}
