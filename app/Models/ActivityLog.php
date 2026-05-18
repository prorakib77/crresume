<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'properties',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'ip_address',
        'user_agent',
        'context'
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Common action types
     */
    const ACTION_CREATED = 'created';
    const ACTION_UPDATED = 'updated';
    const ACTION_DELETED = 'deleted';
    const ACTION_SUBMITTED = 'submitted';
    const ACTION_APPROVED = 'approved';
    const ACTION_REJECTED = 'rejected';
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_VIEW = 'viewed';
    const ACTION_EXPORT = 'exported';

    /**
     * Context types
     */
    const CONTEXT_WEB = 'web';
    const CONTEXT_API = 'api';
    const CONTEXT_SYSTEM = 'system';
    const CONTEXT_CLI = 'cli';

    /**
     * Get the user that performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subject of the activity
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the causer of the activity
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for specific action
     */
    public function scopeForAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for specific subject type
     */
    public function scopeForSubjectType($query, $type)
    {
        return $query->where('subject_type', $type);
    }

    /**
     * Scope for recent activities
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope for today's activities
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Get activity description with proper formatting
     */
    public function getFormattedDescriptionAttribute(): string
    {
        $userName = $this->user ? $this->user->name : 'System';
        
        if ($this->description) {
            return str_replace('{user}', $userName, $this->description);
        }

        // Generate description based on action
        $subjectName = $this->getSubjectName();
        
        return match ($this->action) {
            self::ACTION_CREATED => "{$userName} created {$subjectName}",
            self::ACTION_UPDATED => "{$userName} updated {$subjectName}",
            self::ACTION_DELETED => "{$userName} deleted {$subjectName}",
            self::ACTION_SUBMITTED => "{$userName} submitted {$subjectName}",
            self::ACTION_APPROVED => "{$userName} approved {$subjectName}",
            self::ACTION_REJECTED => "{$userName} rejected {$subjectName}",
            self::ACTION_LOGIN => "{$userName} logged in",
            self::ACTION_LOGOUT => "{$userName} logged out",
            self::ACTION_VIEW => "{$userName} viewed {$subjectName}",
            self::ACTION_EXPORT => "{$userName} exported {$subjectName}",
            default => "{$userName} performed {$this->action} on {$subjectName}"
        };
    }

    /**
     * Get subject name for description
     */
    protected function getSubjectName(): string
    {
        if (!$this->subject) {
            return class_basename($this->subject_type ?? 'unknown');
        }

        return match (true) {
            $this->subject instanceof WorkUpdate => "work update '{$this->subject->title}'",
            $this->subject instanceof User => "user '{$this->subject->name}'",
            $this->subject instanceof WorkUpdateBatch => "batch #{$this->subject->id}",
            default => class_basename($this->subject_type) . " #{$this->subject_id}"
        };
    }

    /**
     * Get icon for activity action
     */
    public function getActionIconAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_CREATED => 'plus-circle',
            self::ACTION_UPDATED => 'pencil',
            self::ACTION_DELETED => 'trash',
            self::ACTION_SUBMITTED => 'paper-airplane',
            self::ACTION_APPROVED => 'check-circle',
            self::ACTION_REJECTED => 'x-circle',
            self::ACTION_LOGIN => 'login',
            self::ACTION_LOGOUT => 'logout',
            self::ACTION_VIEW => 'eye',
            self::ACTION_EXPORT => 'download',
            default => 'information-circle'
        };
    }

    /**
     * Get color class for activity action
     */
    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_CREATED => 'text-green-600',
            self::ACTION_UPDATED => 'text-blue-600',
            self::ACTION_DELETED => 'text-red-600',
            self::ACTION_SUBMITTED => 'text-purple-600',
            self::ACTION_APPROVED => 'text-green-600',
            self::ACTION_REJECTED => 'text-red-600',
            self::ACTION_LOGIN => 'text-blue-600',
            self::ACTION_LOGOUT => 'text-gray-600',
            self::ACTION_VIEW => 'text-gray-600',
            self::ACTION_EXPORT => 'text-indigo-600',
            default => 'text-gray-600'
        };
    }

    /**
     * Log activity
     */
    public static function logActivity(
        string $action,
        Model $subject = null,
        User $causer = null,
        array $properties = [],
        string $description = null
    ): self {
        $causer = $causer ?? auth()->user();
        
        return self::create([
            'user_id' => $causer?->id,
            'action' => $action,
            'description' => $description,
            'properties' => $properties,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'causer_type' => $causer ? get_class($causer) : null,
            'causer_id' => $causer?->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'context' => app()->runningInConsole() ? self::CONTEXT_CLI : 
                        (request()->is('api/*') ? self::CONTEXT_API : self::CONTEXT_WEB)
        ]);
    }

    /**
     * Log work update activity
     */
    public static function logWorkUpdateActivity(
        string $action,
        WorkUpdate $workUpdate,
        User $user = null,
        array $properties = []
    ): self {
        return self::logActivity($action, $workUpdate, $user, $properties);
    }

    /**
     * Log user activity
     */
    public static function logUserActivity(
        string $action,
        User $user = null,
        array $properties = [],
        string $description = null
    ): self {
        return self::logActivity($action, $user, $user, $properties, $description);
    }

    /**
     * Get recent activities for dashboard
     */
    public static function getRecentActivities(int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return self::with(['user', 'subject'])
                   ->latest()
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get user's recent activities
     */
    public static function getUserActivities(User $user, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return self::with(['subject'])
                   ->where('user_id', $user->id)
                   ->latest()
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get activities for specific model
     */
    public static function getModelActivities(Model $model, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return self::with(['user'])
                   ->where('subject_type', get_class($model))
                   ->where('subject_id', $model->id)
                   ->latest()
                   ->limit($limit)
                   ->get();
    }

    /**
     * Clean up old activities
     */
    public static function cleanupOld(int $days = 90): int
    {
        return self::where('created_at', '<', now()->subDays($days))->delete();
    }

    /**
     * Get activity statistics
     */
    public static function getStatistics(array $filters = []): array
    {
        $query = self::query();

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return [
            'total_activities' => $query->count(),
            'activities_by_action' => $query->groupBy('action')
                                           ->selectRaw('action, COUNT(*) as count')
                                           ->pluck('count', 'action')
                                           ->toArray(),
            'activities_by_user' => $query->join('users', 'activity_logs.user_id', '=', 'users.id')
                                         ->groupBy('users.name')
                                         ->selectRaw('users.name, COUNT(*) as count')
                                         ->pluck('count', 'name')
                                         ->toArray(),
            'daily_activities' => $query->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                                       ->groupBy('date')
                                       ->orderBy('date')
                                       ->pluck('count', 'date')
                                       ->toArray()
        ];
    }
}