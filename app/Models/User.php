<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\HasSlugRouteKey;
use App\Models\Role;
use App\Models\ClientProfile;
use App\Models\SupportTicket;
use App\Models\WorkUpdate;
use App\Models\ClientSubmission;
use App\Models\WorkUpdateBatch;
use App\Models\AgentClientAssignment;
use App\Models\AgentActivity;
use App\Models\Attendance;
use App\Traits\Searchable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Searchable, HasSlugRouteKey;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'status',
        'last_login_at',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * User status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Role constants for easier reference
     */
    const ROLE_SUPER_ADMIN = 1;
    const ROLE_ADMIN = 2;
    const ROLE_AGENT = 3;
    const ROLE_CLIENT = 4;

    /**
     * Get the user's role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permissionName): bool
    {
        if (!$this->role) {
            return false;
        }

        return $this->role->permissions()->where('name', $permissionName)->exists();
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if (!$this->role) {
            return false;
        }

        return $this->role->permissions()->whereIn('name', $permissions)->exists();
    }

    /**
     * Check if user has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if (!$this->role) {
            return false;
        }

        $userPermissions = $this->role->permissions()->pluck('name')->toArray();
        return empty(array_diff($permissions, $userPermissions));
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role_id === self::ROLE_SUPER_ADMIN
            || ($this->role && strcasecmp($this->role->name, 'super-admin') === 0);
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role_id, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN])
            || ($this->role && in_array(strtolower($this->role->name), ['admin', 'super-admin']));
    }

    /**
     * Check if user is agent.
     */
    public function isAgent(): bool
    {
        return $this->role_id === self::ROLE_AGENT
            || ($this->role && strtolower($this->role->name) === 'agent');
    }

    /**
     * Check if user is client.
     */
    public function isClient(): bool
    {
        return $this->role_id === self::ROLE_CLIENT
            || ($this->role && strtolower($this->role->name) === 'client');
    }

    /**
     * Check if user is agent manager.
     */
    public function isAgentManager(): bool
    {
        return false; // Role removed in simplified system
    }

    protected function routeKeyPrefix(): string
    {
        return 'u';
    }

    protected function routeKeySourceColumn(): ?string
    {
        return 'name';
    }

    /**
     * Get user's client profile (for clients).
     */
    public function clientProfile(): HasOne
    {
        return $this->hasOne(ClientProfile::class, 'user_id');
    }

    /**
     * Get work updates created by this user (for agents).
     */
    public function workUpdates(): HasMany
    {
        return $this->hasMany(WorkUpdate::class, 'agent_id');
    }

    /**
     * Get work updates for this client.
     */
    public function clientWorkUpdates(): HasMany
    {
        return $this->hasMany(WorkUpdate::class, 'client_id');
    }

    /**
     * Get work update batches created by this agent.
     */
    public function workUpdateBatches(): HasMany
    {
        return $this->hasMany(WorkUpdateBatch::class, 'agent_id');
    }

    /**
     * Get support tickets opened by this client.
     */
    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'client_id');
    }

    /**
     * Get support tickets assigned to this agent.
     */
    public function assignedSupportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'agent_id');
    }

    /**
     * Get work updates approved by this user.
     */
    public function approvedWorkUpdates(): HasMany
    {
        return $this->hasMany(WorkUpdate::class, 'approved_by');
    }

    /**
     * Get clients assigned to this agent.
     */
    public function clients(): BelongsToMany
    {
        if ($this->isAgent()) {
            return $this->belongsToMany(
                User::class,
                'agent_client',
                'agent_id',
                'client_id'
            )->join('client_profiles', 'client_profiles.id', '=', 'agent_client.client_id')
             ->join('users as client_users', 'client_users.id', '=', 'client_profiles.user_id')
             ->select('client_users.*')
             ->withTimestamps()
             ->where('client_users.role_id', self::ROLE_CLIENT);
        }

        return $this->belongsToMany(User::class, 'agent_client', 'agent_id', 'client_id')
                    ->whereRaw('1 = 0');
    }

    /**
     * Get agents assigned to this client.
     */
    public function agents(): BelongsToMany
    {
        if ($this->isClient() && $this->clientProfile) {
            return $this->belongsToMany(
                User::class,
                'agent_client',
                'client_id',
                'agent_id'
            )->using(\Illuminate\Database\Eloquent\Relations\Pivot::class)
             ->withPivot('id')
             ->withTimestamps()
             ->where('users.role_id', self::ROLE_AGENT);
        }

        return $this->belongsToMany(User::class, 'agent_client', 'client_id', 'agent_id')
                    ->whereRaw('1 = 0');
    }

    /**
     * Get assigned agents for a client through client_profile.
     */
    public function getAssignedAgents()
    {
        if ($this->isClient() && $this->clientProfile) {
            return User::whereHas('assignedClientProfiles', function($query) {
                $query->where('client_profiles.id', $this->clientProfile->id);
            })->where('role_id', self::ROLE_AGENT)->get();
        }
        return collect();
    }

    /**
     * Get assigned client profiles for an agent.
     */
    public function assignedClientProfiles(): BelongsToMany
    {
        return $this->belongsToMany(
            ClientProfile::class,
            'agent_client',
            'agent_id',
            'client_id'
        )->withTimestamps();
    }

    /**
     * Get agent-client assignments where this user is the agent
     */
    public function agentAssignments(): HasMany
    {
        return $this->hasMany(AgentClientAssignment::class, 'agent_id');
    }

    /**
     * Get agent-client assignments where this user is the client
     */
    public function clientAssignments(): HasMany
    {
        return $this->hasMany(AgentClientAssignment::class, 'client_id');
    }

    /**
     * Get active clients assigned to this agent through assignments
     */
    public function activeClients()
    {
        return $this->belongsToMany(
            User::class,
            'agent_client',
            'agent_id',
            'client_id'
        )->wherePivot('is_active', true)
         ->withPivot('service_end_date', 'assigned_date', 'is_active')
         ->withTimestamps();
    }

    /**
     * Get active clients assigned to this agent through assignments (as collection)
     */
    public function getActiveClientsAttribute()
    {
        if (!$this->isAgent()) {
            return collect();
        }

        return $this->mapAssignmentsToClients($this->orderedActiveAssignments());
    }

    /**
     * Get active agents assigned to this client
     */
    public function activeAgents()
    {
        return $this->belongsToMany(
            User::class,
            'agent_client',
            'client_id',
            'agent_id'
        )->wherePivot('is_active', true)
         ->withPivot('service_end_date', 'assigned_date', 'is_active')
         ->withTimestamps();
    }

    /**
     * Get active agents assigned to this client (as collection)
     */
    public function getActiveAgentsAttribute()
    {
        if (!$this->isClient()) {
            return collect();
        }

        $assignments = AgentClientAssignment::where('client_id', $this->id)
                                           ->where('is_active', true)
                                           ->with('agent')
                                           ->get();

        return $assignments->map(function($assignment) {
            return $assignment->agent;
        })->filter();
    }

    /**
     * Scope to filter users by role.
     */
    public function scopeWithRole(Builder $query, string $roleName): Builder
    {
        return $query->whereHas('role', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    /**
     * Scope to filter users by status.
     */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to search users.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhereHas('role', function ($roleQuery) use ($search) {
                  $roleQuery->where('name', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Get the user's agent activities.
     */
    public function agentActivities()
    {
        return $this->hasMany(AgentActivity::class, 'agent_id');
    }

    /**
     * Get user's performance metrics (for agents).
     */
    public function getPerformanceMetrics(): array
    {
        if (!$this->isAgent()) {
            return [];
        }

        $totalUpdates = $this->workUpdates()->count();
        $approvedUpdates = $this->workUpdates()->where('status', WorkUpdate::STATUS_APPROVED)->count();
        $rejectedUpdates = 0; // No longer using rejection workflow
        $pendingUpdates = $this->workUpdates()->whereIn('status', [
            WorkUpdate::STATUS_DRAFT,
            WorkUpdate::STATUS_SUBMITTED
        ])->count();

        return [
            'total_updates' => $totalUpdates,
            'approved_updates' => $approvedUpdates,
            'rejected_updates' => $rejectedUpdates,
            'pending_updates' => $pendingUpdates,
            'approval_rate' => $totalUpdates > 0 ? round(($approvedUpdates / $totalUpdates) * 100, 1) : 0,
            'active_clients' => $this->assignedClientProfiles()->count(),
        ];
    }

    /**
     * Get today's work updates count for agent.
     */
    public function getTodaysUpdatesCount(): int
    {
        if (!$this->isAgent()) {
            return 0;
        }

        return $this->workUpdates()
                    ->whereDate('created_at', today())
                    ->count();
    }

    /**
     * Get this month's work updates count for agent.
     */
    public function getMonthlyUpdatesCount(): int
    {
        if (!$this->isAgent()) {
            return 0;
        }

        return $this->workUpdates()
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count();
    }

    /**
     * Get user's full name with role.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ' (' . ($this->role->name ?? 'No Role') . ')';
    }

    /**
     * Get status badge class for UI.
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status ?? 'active') {
            self::STATUS_ACTIVE => 'badge-success',
            self::STATUS_INACTIVE => 'badge-secondary',
            self::STATUS_SUSPENDED => 'badge-error',
            default => 'badge-secondary',
        };
    }

    /**
     * Get assigned clients for this agent (alias for getActiveClientsAttribute)
     */
    public function assignedClients()
    {
        if (!$this->isAgent()) {
            return collect();
        }

        return $this->mapAssignmentsToClients($this->orderedAssignments());
    }

    protected function orderedActiveAssignments()
    {
        return AgentClientAssignment::query()
            ->where('agent_id', $this->id)
            ->with('client')
            ->newestFirst()
            ->get()
            ->filter(fn (AgentClientAssignment $assignment) => $assignment->isActive())
            ->unique('client_id')
            ->values();
    }

    protected function orderedAssignments()
    {
        return AgentClientAssignment::query()
            ->where('agent_id', $this->id)
            ->with('client')
            ->newestFirst()
            ->get()
            ->unique('client_id')
            ->values();
    }

    protected function mapAssignmentsToClients($assignments)
    {
        return $assignments->map(function (AgentClientAssignment $assignment) {
            $client = $assignment->client;

            if (!$client) {
                return null;
            }

            $client->pivot = (object) [
                'service_end_date' => $assignment->service_end_date,
                'assigned_date' => $assignment->assigned_date,
                'is_active' => $assignment->is_active,
            ];

            return $client;
        })->filter()->values();
    }

    /**
     * Get the client's submissions
     */
    public function clientSubmissions()
    {
        return $this->hasMany(ClientSubmission::class, 'client_id');
    }

    /**
     * Get the user's attendances
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'agent_id');
    }
}
