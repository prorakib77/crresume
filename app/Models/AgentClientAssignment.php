<?php

namespace App\Models;

use App\Models\Concerns\HasSlugRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class AgentClientAssignment extends Model
{
    use HasFactory, HasSlugRouteKey;

    public const DEFAULT_MINIMUM_WORK_UPDATES = 4;

    protected $fillable = [
        'agent_id',
        'client_id',
        'assigned_date',
        'service_end_date',
        'minimum_work_updates',
        'service_completed_at',
        'service_completed_by',
        'is_active',
        'notes',
        'apply_to',
        'resume_file',
        'onboarding_form_file',
        'cover_letters',
        'note_for_agent',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'service_end_date' => 'date',
        'minimum_work_updates' => 'integer',
        'service_completed_at' => 'datetime',
        'is_active' => 'boolean',
        'cover_letters' => 'array',
    ];

    /**
     * Get the agent assigned to the client
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get the client assigned to the agent
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Scope to get active assignments
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
                    ->whereNull('service_completed_at')
                    ->where(function($q) {
                        $q->whereNull('service_end_date')
                          ->orWhere('service_end_date', '>=', now()->toDateString());
                    });
    }

    public function scopeNewestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('assigned_date')
            ->orderByDesc('id');
    }

    /**
     * Scope to get assignments for a specific agent
     */
    public function scopeForAgent(Builder $query, int $agentId): Builder
    {
        return $query->where('agent_id', $agentId);
    }

    /**
     * Scope to get assignments for a specific client
     */
    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Check if the assignment is still active
     */
    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->isServiceCompleted()) {
            return false;
        }

        if ($this->service_end_date && $this->service_end_date->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if this service was manually completed by admin.
     */
    public function isServiceCompleted(): bool
    {
        return $this->service_completed_at !== null;
    }

    /**
     * Minimum draft updates required before submit.
     */
    public function minimumWorkUpdatesRequired(): int
    {
        $minimum = (int) ($this->minimum_work_updates ?? self::DEFAULT_MINIMUM_WORK_UPDATES);

        return max(1, $minimum);
    }

    /**
     * Get days remaining in service
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

    protected function routeKeyPrefix(): string
    {
        return 'a';
    }
}
