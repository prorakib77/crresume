<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use App\Traits\Searchable;

class WorkUpdateBatch extends Model
{
    use HasFactory, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'agent_id',
        'submission_date',
        'total_updates',
        'approved_updates',
        'rejected_updates',
        'pending_updates',
        'status',
        'notes',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
    ];

    /**\n     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'submission_date' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Batch status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_PARTIALLY_APPROVED = 'partially_approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Get all available batch statuses
     *
     * @return array
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_PARTIALLY_APPROVED => 'Partially Approved',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }

    /**
     * Get the agent that owns this batch.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get the user who reviewed this batch.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the work updates in this batch.
     */
    public function workUpdates(): HasMany
    {
        return $this->hasMany(WorkUpdate::class, 'batch_id');
    }

    /**
     * Scope a query to only include batches for a specific agent.
     */
    public function scopeForAgent(Builder $query, int $agentId): Builder
    {
        return $query->where('agent_id', $agentId);
    }

    /**
     * Scope a query to only include batches with a specific status.
     */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include batches pending review.
     */
    public function scopePendingReview(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_SUBMITTED, self::STATUS_UNDER_REVIEW]);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange(Builder $query, ?string $startDate, ?string $endDate): Builder
    {
        if ($startDate) {
            $query->where('submission_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('submission_date', '<=', $endDate);
        }
        
        return $query;
    }

    /**
     * Submit the batch for review.
     */
    public function submit(): bool
    {
        if ($this->status !== self::STATUS_DRAFT) {
            return false;
        }

        // Submit all draft work updates in this batch
        $this->workUpdates()
             ->where('status', WorkUpdate::STATUS_DRAFT)
             ->update(['status' => WorkUpdate::STATUS_SUBMITTED]);

        return $this->update([
            'status' => self::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);
    }

    /**
     * Update batch statistics based on work updates.
     */
    public function updateStatistics(): void
    {
        $workUpdates = $this->workUpdates;
        
        $this->update([
            'total_updates' => $workUpdates->count(),
            'approved_updates' => $workUpdates->where('status', WorkUpdate::STATUS_APPROVED)->count(),
            'rejected_updates' => $workUpdates->where('status', WorkUpdate::STATUS_REJECTED)->count(),
            'pending_updates' => $workUpdates->whereIn('status', [
                WorkUpdate::STATUS_SUBMITTED,
                WorkUpdate::STATUS_UNDER_REVIEW,
                WorkUpdate::STATUS_REQUIRES_REVISION
            ])->count(),
        ]);

        // Update batch status based on work update statuses
        $this->updateBatchStatus();
    }

    /**
     * Update batch status based on work update statuses.
     */
    private function updateBatchStatus(): void
    {
        $workUpdates = $this->workUpdates;
        $totalUpdates = $workUpdates->count();
        
        if ($totalUpdates === 0) {
            return;
        }

        $approvedCount = $workUpdates->where('status', WorkUpdate::STATUS_APPROVED)->count();
        $rejectedCount = $workUpdates->where('status', WorkUpdate::STATUS_REJECTED)->count();
        $pendingCount = $totalUpdates - $approvedCount - $rejectedCount;

        if ($pendingCount === 0) {
            // All updates have been reviewed
            if ($approvedCount === $totalUpdates) {
                $status = self::STATUS_APPROVED;
            } elseif ($rejectedCount === $totalUpdates) {
                $status = self::STATUS_REJECTED;
            } else {
                $status = self::STATUS_PARTIALLY_APPROVED;
            }
            
            $this->update([
                'status' => $status,
                'reviewed_at' => now(),
            ]);
        }
    }

    /**
     * Get approval percentage.
     */
    public function getApprovalPercentage(): float
    {
        if ($this->total_updates === 0) {
            return 0;
        }

        return round(($this->approved_updates / $this->total_updates) * 100, 1);
    }

    /**
     * Get completion percentage.
     */
    public function getCompletionPercentage(): float
    {
        if ($this->total_updates === 0) {
            return 0;
        }

        $reviewedUpdates = $this->approved_updates + $this->rejected_updates;
        return round(($reviewedUpdates / $this->total_updates) * 100, 1);
    }

    /**
     * Check if the batch can be submitted.
     */
    public function canBeSubmitted(): bool
    {
        return $this->status === self::STATUS_DRAFT && $this->total_updates > 0;
    }

    /**
     * Check if the batch can be reviewed.
     */
    public function canBeReviewed(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_UNDER_REVIEW]);
    }

    /**
     * Get status badge class for UI.
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'badge-secondary',
            self::STATUS_SUBMITTED => 'badge-info',
            self::STATUS_UNDER_REVIEW => 'badge-warning',
            self::STATUS_APPROVED => 'badge-success',
            self::STATUS_PARTIALLY_APPROVED => 'badge-warning',
            self::STATUS_REJECTED => 'badge-error',
            default => 'badge-secondary',
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
     * Create a new batch for today if it doesn't exist.
     */
    public static function getOrCreateTodaysBatch(int $agentId): self
    {
        $today = Carbon::today();
        
        return static::firstOrCreate([
            'agent_id' => $agentId,
            'submission_date' => $today,
        ], [
            'status' => self::STATUS_DRAFT,
            'total_updates' => 0,
            'approved_updates' => 0,
            'rejected_updates' => 0,
            'pending_updates' => 0,
        ]);
    }
}