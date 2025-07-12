<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Events\NotificationUpdated;
use App\Http\Resources\NotificationResource;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->notifications();

        if ($request->query('filter') === 'unread') {
            $query->where('read', false);
        }

        $notifications = $query->latest()->paginate(20);

        return NotificationResource::collection($notifications);
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->update(['read' => true]);

        event(new NotificationUpdated($notification));

        return response()->json([
            'success' => true,
            'message' => 'Уведомление отмечено как прочитанное'
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $user->notifications()->where('read', false)->update(['read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Все уведомления отмечены как прочитанные'
        ]);
    }
} 