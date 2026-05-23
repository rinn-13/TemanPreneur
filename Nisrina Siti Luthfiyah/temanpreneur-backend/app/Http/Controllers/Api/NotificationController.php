<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = auth()->user()->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return response()->json($notifications);
    }

    public function unreadCount()
    {
        $count = auth()->user()->notifications()
            ->where('is_read', false)
            ->count();
        return response()->json(['count' => $count]);
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->update(['is_read' => true]);
        return response()->json($notification);
    }

    public function markAllAsRead()
    {
        auth()->user()->notifications()
            ->where('is_read', false)
            ->update(['is_read' => true]);
        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function destroy($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->delete();
        return response()->json(['message' => 'Notification deleted']);
    }
}