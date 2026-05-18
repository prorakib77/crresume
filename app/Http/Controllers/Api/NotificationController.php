<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Get user's notifications
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = min($request->get('per_page', 20), 100);
        
        $query = Notification::where('user_id', $user->id)
                            ->active()
                            ->latest();

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by read status
        if ($request->has('read')) {
            if ($request->boolean('read')) {
                $query->read();
            } else {
                $query->unread();
            }
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $notifications = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'unread_count' => $this->notificationService->getUnreadCount($user)
            ]
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(): JsonResponse
    {
        $user = Auth::user();
        $count = $this->notificationService->getUnreadCount($user);

        return response()->json([
            'success' => true,
            'data' => ['unread_count' => $count]
        ]);
    }

    /**
     * Get recent notifications for dropdown/sidebar
     */
    public function recent(Request $request): JsonResponse
    {
        $user = Auth::user();
        $limit = min($request->get('limit', 10), 50);
        
        $notifications = $this->notificationService->getRecentNotifications($user, $limit);

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'meta' => [
                'unread_count' => $this->notificationService->getUnreadCount($user)
            ]
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        $user = Auth::user();

        // Check if notification belongs to user
        if ($notification->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $this->notificationService->markAsRead($notification);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => [
                'unread_count' => $this->notificationService->getUnreadCount($user)
            ]
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        $count = $this->notificationService->markAllAsRead($user);

        return response()->json([
            'success' => true,
            'message' => "Marked {$count} notifications as read",
            'data' => [
                'marked_count' => $count,
                'unread_count' => 0
            ]
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy(Notification $notification): JsonResponse
    {
        $user = Auth::user();

        // Check if notification belongs to user
        if ($notification->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
            'data' => [
                'unread_count' => $this->notificationService->getUnreadCount($user)
            ]
        ]);
    }

    /**
     * Get notification statistics (admin only)
     */
    public function statistics(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Notification::class);

        $filters = $request->only(['user_id', 'date_from', 'date_to']);
        $statistics = $this->notificationService->getStatistics($filters);

        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }

    /**
     * Send system notification (admin only)
     */
    public function sendSystemNotification(Request $request): JsonResponse
    {
        $this->authorize('create', Notification::class);

        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
            'role_names' => 'array',
            'role_names.*' => 'string|exists:roles,name',
            'priority' => 'in:low,normal,high,urgent',
            'expires_at' => 'nullable|date|after:now'
        ]);

        $notifications = $this->notificationService->sendSystemNotification(
            $request->title,
            $request->message,
            $request->get('user_ids', []),
            $request->get('role_names', []),
            $request->get('priority', 'normal'),
            $request->expires_at ? new \DateTime($request->expires_at) : null
        );

        return response()->json([
            'success' => true,
            'message' => "System notification sent to {$notifications->count()} users",
            'data' => [
                'sent_count' => $notifications->count()
            ]
        ]);
    }

    /**
     * Clean up old notifications (admin only)
     */
    public function cleanup(Request $request): JsonResponse
    {
        $this->authorize('delete', Notification::class);

        $request->validate([
            'days' => 'integer|min:1|max:365'
        ]);

        $days = $request->get('days', 30);
        $deletedCount = $this->notificationService->deleteOldNotifications($days);
        $expiredCount = $this->notificationService->cleanupExpiredNotifications();

        return response()->json([
            'success' => true,
            'message' => 'Notification cleanup completed',
            'data' => [
                'deleted_old' => $deletedCount,
                'deleted_expired' => $expiredCount,
                'total_deleted' => $deletedCount + $expiredCount
            ]
        ]);
    }

    /**
     * Test notification (development only)
     */
    public function test(Request $request): JsonResponse
    {
        if (!app()->environment('local', 'development')) {
            return response()->json([
                'success' => false,
                'message' => 'Test notifications are only available in development'
            ], 403);
        }

        $user = Auth::user();
        
        $notification = $this->notificationService->notify(
            $user,
            'Test Notification',
            'This is a test notification to verify the system is working correctly.',
            Notification::TYPE_INFO,
            ['test' => true],
            Notification::PRIORITY_LOW,
            null,
            null,
            now()->addMinutes(5)
        );

        return response()->json([
            'success' => true,
            'message' => 'Test notification sent',
            'data' => $notification
        ]);
    }
}