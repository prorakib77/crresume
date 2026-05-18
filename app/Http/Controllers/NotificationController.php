<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get user's notifications
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $limit = $request->get('limit', 50);
        $notifications = $this->notificationService->getRecentNotifications($user, $limit);
        $unreadCount = $this->notificationService->getUnreadCount($user);

        if ($request->expectsJson() || $request->has('limit')) {
            $notificationData = $notifications->map(function (Notification $notification) use ($user) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'priority' => $notification->priority,
                    'read_at' => optional($notification->read_at)?->toIso8601String(),
                    'created_at' => optional($notification->created_at)?->toIso8601String(),
                    'action_url' => $notification->action_url,
                    'resolved_action_url' => $notification->resolveActionUrl($user),
                ];
            })->values();

            return response()->json([
                'notifications' => $notificationData,
                'unread_count' => $unreadCount
            ]);
        }

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $this->notificationService->markAsRead($notification);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        $count = $this->notificationService->markAllAsRead(Auth::user());

        return response()->json([
            'success' => true,
            'marked_count' => $count
        ]);
    }

    /**
     * Get unread count
     */
    public function unreadCount(): JsonResponse
    {
        $count = $this->notificationService->getUnreadCount(Auth::user());

        return response()->json(['count' => $count]);
    }

    /**
     * Delete notification
     */
    public function destroy(Notification $notification): JsonResponse
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Get notification statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $filters = $request->only(['date_from', 'date_to']);
        $filters['user_id'] = Auth::id();

        $stats = $this->notificationService->getStatistics($filters);

        return response()->json($stats);
    }
}
