<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
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
     * Mark all notifications (including news) as read
     */
    public function markAllAsRead()
    {
        // Mark database notifications as read
        auth()->user()->unreadNotifications->markAsRead();

        // Mark news as read
        $userId = auth()->user()->id;
        $unreadNews = auth()->user()->unreadNews(null); // Get all unread news
        
        foreach ($unreadNews as $news) {
            \App\Models\NewsRead::updateOrCreate([
                'user_id' => $userId,
                'news_id' => $news->id
            ]);
        }

        return redirect()->back()->with('success', 'Đã đánh dấu tất cả thông báo là đã xem.');
    }
}
