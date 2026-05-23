<?php

namespace App\Models;

use App\Models\Concerns\ResolvesInternalActionUrls;
use App\Models\Concerns\HasSlugRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes, HasSlugRouteKey, ResolvesInternalActionUrls;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'priority',
        'data',
        'notifiable_type',
        'notifiable_id',
        'read_at',
        'action_url',
        'expires_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $appends = [
        'resolved_action_url',
    ];

    /**
     * Notification types
     */
    const TYPE_INFO = 'info';
    const TYPE_SUCCESS = 'success';
    const TYPE_WARNING = 'warning';
    const TYPE_ERROR = 'error';
    const TYPE_WORK_UPDATE = 'work_update';
    const TYPE_APPROVAL = 'approval';
    const TYPE_REJECTION = 'rejection';
    const TYPE_SYSTEM = 'system';
    const TYPE_OTP_REQUEST = 'otp_request';
    const TYPE_OTP_SUBMISSION = 'otp_submission';

    /**
     * Priority levels
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Get the user that owns the notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the notifiable entity
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope for specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for specific priority
     */
    public function scopeWithPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for non-expired notifications
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): bool
    {
        if ($this->read_at === null) {
            return $this->update(['read_at' => now()]);
        }
        return true;
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(): bool
    {
        return $this->update(['read_at' => null]);
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Check if notification is unread
     */
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Check if notification is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get CSS class for notification type
     */
    public function getTypeClassAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_SUCCESS, self::TYPE_APPROVAL => 'bg-green-100 text-green-800',
            self::TYPE_WARNING => 'bg-yellow-100 text-yellow-800',
            self::TYPE_ERROR, self::TYPE_REJECTION => 'bg-red-100 text-red-800',
            self::TYPE_WORK_UPDATE => 'bg-blue-100 text-blue-800',
            self::TYPE_SYSTEM => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get icon for notification type
     */
    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_SUCCESS, self::TYPE_APPROVAL => 'check-circle',
            self::TYPE_WARNING => 'exclamation-triangle',
            self::TYPE_ERROR, self::TYPE_REJECTION => 'x-circle',
            self::TYPE_WORK_UPDATE => 'document-text',
            self::TYPE_SYSTEM => 'cog',
            default => 'information-circle'
        };
    }

    /**
     * Get priority CSS class
     */
    public function getPriorityClassAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_LOW => 'border-l-gray-400',
            self::PRIORITY_NORMAL => 'border-l-blue-400',
            self::PRIORITY_HIGH => 'border-l-yellow-400',
            self::PRIORITY_URGENT => 'border-l-red-400',
            default => 'border-l-gray-400'
        };
    }

    /**
     * Create notification for work update events
     */
    public static function createWorkUpdateNotification(
        User $user,
        WorkUpdate $workUpdate,
        string $action,
        array $data = []
    ): self {
        $titles = [
            'submitted' => 'Work Update Submitted',
            'approved' => 'Work Update Approved',
            'rejected' => 'Work Update Rejected',
            'updated' => 'Work Update Modified'
        ];

        $messages = [
            'submitted' => "Work update '{$workUpdate->title}' has been submitted for approval",
            'approved' => "Your work update '{$workUpdate->title}' has been approved",
            'rejected' => "Your work update '{$workUpdate->title}' has been rejected",
            'updated' => "Work update '{$workUpdate->title}' has been updated"
        ];

        $types = [
            'submitted' => self::TYPE_WORK_UPDATE,
            'approved' => self::TYPE_APPROVAL,
            'rejected' => self::TYPE_REJECTION,
            'updated' => self::TYPE_WORK_UPDATE
        ];

        $actionUrl = route('dashboard');

        if ($user->isAdmin()) {
            $actionUrl = route('admin.work-updates', ['submission' => $workUpdate->getRouteKey()]);
        } elseif ($user->isAgent()) {
            $actionUrl = route('agent.work-updates.index');
        } elseif ($user->isClient()) {
            $actionUrl = route('client.work-updates.index');
        }

        return self::create([
            'user_id' => $user->id,
            'title' => $titles[$action] ?? 'Work Update Notification',
            'message' => $messages[$action] ?? 'Work update notification',
            'type' => $types[$action] ?? self::TYPE_INFO,
            'priority' => self::PRIORITY_NORMAL,
            'data' => array_merge($data, [
                'work_update_id' => $workUpdate->id,
                'action' => $action
            ]),
            'notifiable_type' => WorkUpdate::class,
            'notifiable_id' => $workUpdate->id,
            'action_url' => $actionUrl
        ]);
    }

    /**
     * Create system notification
     */
    public static function createSystemNotification(
        User $user,
        string $title,
        string $message,
        array $data = [],
        string $priority = self::PRIORITY_NORMAL
    ): self {
        return self::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => self::TYPE_SYSTEM,
            'priority' => $priority,
            'data' => $data
        ]);
    }

    /**
     * Bulk mark as read
     */
    public static function markAllAsRead(User $user): int
    {
        return self::where('user_id', $user->id)
                   ->whereNull('read_at')
                   ->update(['read_at' => now()]);
    }

    /**
     * Clean up expired notifications
     */
    public static function cleanupExpired(): int
    {
        return self::where('expires_at', '<', now())->delete();
    }

    public function getResolvedActionUrlAttribute(): ?string
    {
        $user = $this->relationLoaded('user') ? $this->user : null;

        if (!$user && auth()->check() && (int) auth()->id() === (int) $this->user_id) {
            $user = auth()->user();
        }

        return $this->resolveActionUrl($user);
    }

    public function resolveActionUrl(?User $user = null): ?string
    {
        $user ??= $this->user;

        $rawActionUrl = trim((string) ($this->action_url ?? ''));
        if ($rawActionUrl === '') {
            return $this->fallbackActionUrl($user);
        }

        $normalizedPathWithQuery = $this->normalizeInternalActionPath($rawActionUrl);
        if ($normalizedPathWithQuery === null) {
            return $rawActionUrl;
        }

        [$normalizedPath, $queryString] = array_pad(explode('?', $normalizedPathWithQuery, 2), 2, null);

        if (
            preg_match('#^/work-updates(?:/.*)?$#', $normalizedPath)
            || preg_match('#^/(?:admin|agent|client)/work-updates(?:/.*)?$#', $normalizedPath)
        ) {
            return $this->appendQueryString($this->roleWorkUpdatesUrl($user), $queryString);
        }

        if (
            preg_match('~^/support-tickets(?:/([^/?]+))?$~', $normalizedPath, $matches)
            || preg_match('~^/(?:admin|agent|client)/support-tickets(?:/([^/?]+))?$~', $normalizedPath, $matches)
        ) {
            $ticketKey = $matches[1] ?? null;

            if (filled($ticketKey)) {
                return $this->appendQueryString($this->roleSupportTicketUrl($ticketKey, $user), $queryString);
            }

            return $this->appendQueryString($this->roleSupportTicketsUrl($user), $queryString);
        }

        if (
            preg_match('#^/otp-requests(?:/.*)?$#', $normalizedPath)
            || preg_match('#^/(?:admin|agent|client)/otp-requests(?:/.*)?$#', $normalizedPath)
        ) {
            return $this->appendQueryString(route('client.otp-requests.index'), $queryString);
        }

        if (
            preg_match('~^/submissions(?:/([^/?]+))?$~', $normalizedPath, $matches)
            || preg_match('~^/(?:admin|agent|client)/submissions(?:/([^/?]+))?$~', $normalizedPath, $matches)
        ) {
            $submissionKey = $matches[1] ?? null;

            if (filled($submissionKey)) {
                return $this->appendQueryString($this->roleSubmissionUrl($submissionKey, $user), $queryString);
            }

            return $this->appendQueryString($this->roleSubmissionsUrl($user), $queryString);
        }

        if (
            preg_match('#^/dashboard$#', $normalizedPath)
            || preg_match('#^/(?:admin|agent|client)/dashboard$#', $normalizedPath)
        ) {
            return $this->appendQueryString($this->roleDashboardUrl($user), $queryString);
        }

        return url($normalizedPathWithQuery);
    }

    private function fallbackActionUrl(?User $user = null): string
    {
        if ($this->type === self::TYPE_OTP_REQUEST) {
            return route('client.otp-requests.index');
        }

        if ($this->type === self::TYPE_OTP_SUBMISSION) {
            return route('agent.submissions.index');
        }

        if (
            in_array($this->type, [self::TYPE_WORK_UPDATE, self::TYPE_APPROVAL, self::TYPE_REJECTION], true)
            || (($this->data['category'] ?? null) === 'work_update')
        ) {
            return $this->roleWorkUpdatesUrl($user);
        }

        if (($this->data['category'] ?? null) === 'payment_request') {
            if ($user && $user->isAdmin()) {
                return route('admin.payment-requests.index');
            }

            if ($user && $user->isClient()) {
                return route('client.notices.index');
            }
        }

        return $this->roleDashboardUrl($user);
    }

    private function roleDashboardUrl(?User $user = null): string
    {
        if ($user && $user->isAdmin()) {
            return route('admin.dashboard');
        }

        if ($user && $user->isAgent()) {
            return route('agent.dashboard');
        }

        if ($user && $user->isClient()) {
            return route('client.dashboard');
        }

        return route('dashboard');
    }

    private function roleWorkUpdatesUrl(?User $user = null): string
    {
        if ($user && $user->isAdmin()) {
            return route('admin.work-updates');
        }

        if ($user && $user->isAgent()) {
            return route('agent.work-updates.index');
        }

        if ($user && $user->isClient()) {
            return route('client.work-updates.index');
        }

        return route('dashboard');
    }

    private function roleSupportTicketsUrl(?User $user = null): string
    {
        if ($user && $user->isAdmin()) {
            return route('admin.support-tickets.index');
        }

        if ($user && $user->isAgent()) {
            return route('agent.support-tickets.index');
        }

        if ($user && $user->isClient()) {
            return route('client.support-tickets.index');
        }

        return route('dashboard');
    }

    private function roleSupportTicketUrl(string $ticketKey, ?User $user = null): string
    {
        if ($user && $user->isAdmin()) {
            return route('admin.support-tickets.show', ['supportTicket' => $ticketKey]);
        }

        if ($user && $user->isAgent()) {
            return route('agent.support-tickets.show', ['supportTicket' => $ticketKey]);
        }

        if ($user && $user->isClient()) {
            return route('client.support-tickets.show', ['supportTicket' => $ticketKey]);
        }

        return route('dashboard');
    }

    private function roleSubmissionsUrl(?User $user = null): string
    {
        if ($user && $user->isAgent()) {
            return route('agent.submissions.index');
        }

        if ($user && $user->isClient()) {
            return route('client.submissions.index');
        }

        return $this->roleDashboardUrl($user);
    }

    private function roleSubmissionUrl(string $submissionKey, ?User $user = null): string
    {
        if ($user && $user->isAgent()) {
            return route('agent.submissions.show', ['submission' => $submissionKey]);
        }

        if ($user && $user->isClient()) {
            return route('client.submissions.show', ['submission' => $submissionKey]);
        }

        return $this->roleDashboardUrl($user);
    }

    protected function routeKeyPrefix(): string
    {
        return 'ntf';
    }

    protected function routeKeySourceColumn(): ?string
    {
        return 'title';
    }
}
