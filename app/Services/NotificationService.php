<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\WorkUpdate;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Create and send notification to user
     */
    public function notify(
        User $user,
        string $title,
        string $message,
        string $type = Notification::TYPE_INFO,
        array $data = [],
        string $priority = Notification::PRIORITY_NORMAL,
        ?Model $notifiable = null,
        ?string $actionUrl = null,
        ?\DateTime $expiresAt = null
    ): Notification {
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'priority' => $priority,
            'data' => $data,
            'notifiable_type' => $notifiable ? get_class($notifiable) : null,
            'notifiable_id' => $notifiable?->id,
            'action_url' => $actionUrl,
            'expires_at' => $expiresAt
        ]);

        // Trigger real-time notification
        $this->triggerRealTimeNotification($notification);

        return $notification;
    }

    /**
     * Notify multiple users
     */
    public function notifyMany(
        Collection $users,
        string $title,
        string $message,
        string $type = Notification::TYPE_INFO,
        array $data = [],
        string $priority = Notification::PRIORITY_NORMAL,
        ?Model $notifiable = null,
        ?string $actionUrl = null,
        ?\DateTime $expiresAt = null
    ): Collection {
        $notifications = collect();

        foreach ($users as $user) {
            $notifications->push(
                $this->notify($user, $title, $message, $type, $data, $priority, $notifiable, $actionUrl, $expiresAt)
            );
        }

        return $notifications;
    }

    /**
     * Handle work update submission notifications
     */
    public function notifyWorkUpdateSubmitted(WorkUpdate $workUpdate): Collection
    {
        $notifications = collect();

        // Notify managers/admins about submission
        $managers = User::whereHas('role', function ($query) {
            $query->whereIn('name', ['admin', 'manager']);
        })->get();

        foreach ($managers as $manager) {
            $notifications->push(
                Notification::createWorkUpdateNotification(
                    $manager,
                    $workUpdate,
                    'submitted',
                    ['submitted_by' => $workUpdate->user->name]
                )
            );
        }

        // Log activity
        ActivityLog::logWorkUpdateActivity('submitted', $workUpdate, $workUpdate->user);

        return $notifications;
    }

    /**
     * Handle work update approval notifications
     */
    public function notifyWorkUpdateApproved(WorkUpdate $workUpdate, User $approver, string $notes = ''): Notification
    {
        $notification = Notification::createWorkUpdateNotification(
            $workUpdate->user,
            $workUpdate,
            'approved',
            [
                'approved_by' => $approver->name,
                'notes' => $notes
            ]
        );

        // Log activity
        ActivityLog::logWorkUpdateActivity('approved', $workUpdate, $approver, [
            'notes' => $notes
        ]);

        return $notification;
    }

    /**
     * Handle work update rejection notifications
     */
    public function notifyWorkUpdateRejected(WorkUpdate $workUpdate, User $rejector, string $notes): Notification
    {
        $notification = Notification::createWorkUpdateNotification(
            $workUpdate->user,
            $workUpdate,
            'rejected',
            [
                'rejected_by' => $rejector->name,
                'notes' => $notes
            ]
        );

        // Log activity
        ActivityLog::logWorkUpdateActivity('rejected', $workUpdate, $rejector, [
            'notes' => $notes
        ]);

        return $notification;
    }

    /**
     * Send system-wide notification
     */
    public function sendSystemNotification(
        string $title,
        string $message,
        array $userIds = [],
        array $roleNames = [],
        string $priority = Notification::PRIORITY_NORMAL,
        ?\DateTime $expiresAt = null
    ): Collection {
        $users = collect();

        // Get users by IDs
        if (!empty($userIds)) {
            $users = $users->merge(User::whereIn('id', $userIds)->get());
        }

        // Get users by roles
        if (!empty($roleNames)) {
            $roleUsers = User::whereHas('role', function ($query) use ($roleNames) {
                $query->whereIn('name', $roleNames);
            })->get();
            $users = $users->merge($roleUsers);
        }

        // If no specific users/roles, send to all users
        if ($users->isEmpty()) {
            $users = User::all();
        }

        // Remove duplicates
        $users = $users->unique('id');

        return $this->notifyMany(
            $users,
            $title,
            $message,
            Notification::TYPE_SYSTEM,
            [],
            $priority,
            null,
            null,
            $expiresAt
        );
    }

    /**
     * Get user's unread notifications count
     */
    public function getUnreadCount(User $user): int
    {
        return Notification::where('user_id', $user->id)
                          ->unread()
                          ->active()
                          ->count();
    }

    /**
     * Get user's recent notifications
     */
    public function getRecentNotifications(User $user, int $limit = 20): Collection
    {
        return Notification::where('user_id', $user->id)
                          ->active()
                          ->latest()
                          ->limit($limit)
                          ->get();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): bool
    {
        if ($notification->markAsRead()) {
            $this->triggerNotificationUpdate($notification);
            return true;
        }
        return false;
    }

    /**
     * Mark all user notifications as read
     */
    public function markAllAsRead(User $user): int
    {
        $count = Notification::markAllAsRead($user);

        // Trigger real-time update
        $this->triggerUserNotificationUpdate($user);

        return $count;
    }

    /**
     * Delete old notifications
     */
    public function deleteOldNotifications(int $days = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($days))
                          ->where('read_at', 'IS NOT NULL')
                          ->delete();
    }

    /**
     * Clean up expired notifications
     */
    public function cleanupExpiredNotifications(): int
    {
        return Notification::cleanupExpired();
    }

    /**
     * Get notification statistics
     */
    public function getStatistics(array $filters = []): array
    {
        $query = Notification::query();

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return [
            'total_notifications' => $query->count(),
            'unread_notifications' => $query->clone()->unread()->count(),
            'notifications_by_type' => $query->clone()
                                           ->groupBy('type')
                                           ->selectRaw('type, COUNT(*) as count')
                                           ->pluck('count', 'type')
                                           ->toArray(),
            'notifications_by_priority' => $query->clone()
                                               ->groupBy('priority')
                                               ->selectRaw('priority, COUNT(*) as count')
                                               ->pluck('count', 'priority')
                                               ->toArray()
        ];
    }

    /**
     * Trigger real-time notification (can be implemented with WebSockets, Server-Sent Events, etc.)
     */
    protected function triggerRealTimeNotification(Notification $notification): void
    {
        // This is where you would integrate with your real-time system
        // For example, using Laravel Echo, Pusher, Socket.io, etc.

        // Example implementation:
        // broadcast(new NotificationCreated($notification))->toOthers();

        // For now, we'll just dispatch an event
        event('notification.created', $notification);
    }

    /**
     * Trigger notification update event
     */
    protected function triggerNotificationUpdate(Notification $notification): void
    {
        event('notification.updated', $notification);
    }

    /**
     * Trigger user notification update event
     */
    protected function triggerUserNotificationUpdate(User $user): void
    {
        event('user.notifications.updated', $user);
    }

    /**
     * Send daily summary notifications
     */
    public function sendDailySummary(): Collection
    {
        $notifications = collect();

        // Get users with pending work updates
        $usersWithPendingUpdates = User::whereHas('workUpdates', function ($query) {
            $query->where('status', 'draft')
                  ->where('created_at', '<', now()->subHours(24));
        })->get();

        foreach ($usersWithPendingUpdates as $user) {
            $pendingCount = $user->workUpdates()
                                ->where('status', 'draft')
                                ->count();

            $actionUrl = route('dashboard');
            if ($user->isAdmin()) {
                $actionUrl = route('admin.work-updates');
            } elseif ($user->isAgent()) {
                $actionUrl = route('agent.work-updates.index');
            } elseif ($user->isClient()) {
                $actionUrl = route('client.work-updates.index');
            }

            $notifications->push(
                $this->notify(
                    $user,
                    'Daily Reminder: Pending Work Updates',
                    "You have {$pendingCount} draft work update(s) that need to be completed.",
                    Notification::TYPE_WARNING,
                    ['pending_count' => $pendingCount],
                    Notification::PRIORITY_NORMAL,
                    null,
                    $actionUrl,
                    now()->addDay()
                )
            );
        }

        return $notifications;
    }
}
