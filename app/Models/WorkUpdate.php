<?php

namespace App\Models;

use App\Models\Concerns\HasSlugRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use App\Traits\Searchable;
use App\Models\ActivityLog;
use App\Models\User;

class WorkUpdate extends Model
{
    use HasFactory, Searchable, HasSlugRouteKey;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'agent_id',
        'client_id',
        'batch_id',
        'job_title',
        'company',
        'applied_date',
        'job_link',
        'job_success_link',
        'applied_method',
        'application_status',
        'note',
        'remarks',
        'applied_proof',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'service_end_date',
        'draft_data',
        'draft_saved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'applied_date' => 'date',
        'service_end_date' => 'date',
        'approved_at' => 'datetime',
        'draft_saved_at' => 'datetime',
        'draft_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Application status constants
     */
    const APPLICATION_STATUS_APPLIED = 'applied';
    const APPLICATION_STATUS_INTERVIEW = 'interview';
    const APPLICATION_STATUS_HIRED = 'hired';
    const APPLICATION_STATUS_REJECTED = 'rejected';
    const APPLICATION_STATUS_INCOMPLETE = 'incomplete';

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_APPROVED = 'approved';

    /**
     * Applied method constants
     */
    const METHOD_WEB = 'web';
    const METHOD_LINKEDIN = 'linkedin';
    const METHOD_REFERRAL = 'referral';
    const METHOD_DIRECT = 'direct';
    const METHOD_EMAIL = 'email';
    const METHOD_OTHER = 'other';

    /**
     * Get all available application statuses
     *
     * @return array
     */
    public static function getApplicationStatuses(): array
    {
        return [
            self::APPLICATION_STATUS_APPLIED => 'Applied',
            self::APPLICATION_STATUS_INTERVIEW => 'Interview',
            self::APPLICATION_STATUS_HIRED => 'Hired',
            self::APPLICATION_STATUS_REJECTED => 'Rejected',
            self::APPLICATION_STATUS_INCOMPLETE => 'Incomplete Application',
        ];
    }

    /**
     * Get the application statuses agents can set during submission.
     *
     * @return array
     */
    public static function getAgentApplicationStatuses(): array
    {
        return array_intersect_key(
            self::getApplicationStatuses(),
            array_flip([
                self::APPLICATION_STATUS_APPLIED,
                self::APPLICATION_STATUS_INCOMPLETE,
            ])
        );
    }

    /**
     * Get the application statuses clients can set after submission.
     *
     * @return array
     */
    public static function getClientEditableApplicationStatuses(): array
    {
        return array_intersect_key(
            self::getApplicationStatuses(),
            array_flip([
                self::APPLICATION_STATUS_INTERVIEW,
                self::APPLICATION_STATUS_HIRED,
                self::APPLICATION_STATUS_REJECTED,
            ])
        );
    }

    /**
     * Get all available statuses
     *
     * @return array
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_APPROVED => 'Sent to Client',
        ];
    }

    /**
     * Get all available application methods
     *
     * @return array
     */
    public static function getAppliedMethods(): array
    {
        return [
            self::METHOD_WEB => 'Company Website',
            self::METHOD_LINKEDIN => 'LinkedIn',
            self::METHOD_REFERRAL => 'Referral',
            self::METHOD_DIRECT => 'Direct Application',
            self::METHOD_EMAIL => 'Email',
            self::METHOD_OTHER => 'Other',
        ];
    }

    /**
     * Get the agent that created this work update.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get the client this work update is for.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the user who approved this work update.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the batch this work update belongs to.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(WorkUpdateBatch::class, 'batch_id');
    }

    /**
     * Scope a query to only include updates for a specific agent.
     */
    public function scopeForAgent(Builder $query, int $agentId): Builder
    {
        return $query->where('agent_id', $agentId);
    }

    /**
     * Scope a query to only include updates for a specific client.
     */
    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    protected function routeKeyPrefix(): string
    {
        return 'w';
    }

    protected function routeKeySourceColumn(): ?string
    {
        return 'job_title';
    }

    /**
     * Scope a query to only include updates with a specific status.
     */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include submitted updates.
     */
    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    /**
     * Scope a query to search work updates.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('job_title', 'like', "%{$search}%")
              ->orWhere('company', 'like', "%{$search}%")
              ->orWhere('note', 'like', "%{$search}%")
              ->orWhereHas('agent', function ($agentQuery) use ($search) {
                  $agentQuery->where('name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
              })
              ->orWhereHas('client', function ($clientQuery) use ($search) {
                  $clientQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange(Builder $query, ?string $startDate, ?string $endDate): Builder
    {
        if ($startDate) {
            $query->where('applied_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('applied_date', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Check if the work update can be edited.
     */
    public function canBeEdited(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if the work update can be submitted.
     */
    public function canBeSubmitted(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Submit the work update - automatically approved.
     */
    public function submit(): bool
    {
        if (!$this->canBeSubmitted()) {
            return false;
        }

        $result = $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        if ($result) {
            // Log activity
            ActivityLog::logWorkUpdateActivity('submitted', $this, $this->agent);

            // Send notifications
            app(\App\Services\NotificationService::class)->notifyWorkUpdateSubmitted($this);
        }

        return $result;
    }



    /**
     * Get status badge class for UI.
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'bg-secondary',
            self::STATUS_SUBMITTED => 'bg-info',
            self::STATUS_APPROVED => 'bg-success',
            default => 'bg-secondary',
        };
    }

    /**
     * Get a formatted status label.
     */
    public function getStatusLabel(): string
    {
        return self::getStatuses()[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get a formatted applied method label.
     */
    public function getAppliedMethodLabel(): string
    {
        return self::getAppliedMethods()[$this->applied_method] ?? ucfirst($this->applied_method ?? 'Unknown');
    }

    /**
     * Get a formatted application status label.
     */
    public function getApplicationStatusLabel(): string
    {
        return self::getApplicationStatuses()[$this->application_status] ?? ucfirst($this->application_status ?? 'Unknown');
    }

    /**
     * Get days remaining for service
     */
    public function getDaysRemaining(): ?int
    {
        if (!$this->service_end_date) {
            return null;
        }

        return rounded_time_value(now()->diffInDays($this->service_end_date, false));
    }

    /**
     * Check if service is ending soon (within 7 days)
     */
    public function isEndingSoon(): bool
    {
        $daysRemaining = $this->getDaysRemaining();
        return $daysRemaining !== null && $daysRemaining <= 7 && $daysRemaining >= 0;
    }

    /**
     * Check if agent can submit daily update for client today (excluding drafts)
     */
    public static function canSubmitToday(int $agentId, int $clientId): bool
    {
        return !static::where('agent_id', $agentId)
                     ->where('client_id', $clientId)
                     ->whereDate('applied_date', now()->toDateString())
                     ->where('status', '!=', self::STATUS_DRAFT)
                     ->exists();
    }

    /**
     * Get today's submission for agent and client (excluding drafts)
     */
    public static function getTodaysSubmission(int $agentId, int $clientId): ?WorkUpdate
    {
        return static::where('agent_id', $agentId)
                    ->where('client_id', $clientId)
                    ->whereDate('applied_date', now()->toDateString())
                    ->where('status', '!=', self::STATUS_DRAFT)
                    ->first();
    }

    /**
     * Check if agent has active assignment with client
     */
    public static function hasActiveAssignment(int $agentId, int $clientId): bool
    {
        return AgentClientAssignment::forAgent($agentId)
                                  ->forClient($clientId)
                                  ->active()
                                  ->exists();
    }

    /**
     * Scope a query to only include draft updates.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Check if this is a draft work update.
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Save draft data for the work update.
     */
    public function saveDraft(array $draftData): bool
    {
        return $this->update([
            'draft_data' => $draftData,
            'draft_saved_at' => now(),
        ]);
    }

    /**
     * Get draft data for the work update.
     */
    public function getDraftData(): ?array
    {
        return $this->draft_data;
    }

    /**
     * Clear draft data.
     */
    public function clearDraft(): bool
    {
        return $this->update([
            'draft_data' => null,
            'draft_saved_at' => null,
        ]);
    }

    /**
     * Get formatted draft saved time.
     */
    public function getDraftSavedTime(): ?string
    {
        return $this->draft_saved_at ? $this->draft_saved_at->format('M j, Y \a\t g:i A') : null;
    }

    /**
     * Check if draft is recent (saved within last 24 hours).
     */
    public function isDraftRecent(): bool
    {
        if (!$this->draft_saved_at) {
            return false;
        }

        return $this->draft_saved_at->isAfter(now()->subDay());
    }
}
