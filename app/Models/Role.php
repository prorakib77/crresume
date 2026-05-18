<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\Searchable;

class Role extends Model
{
    use HasFactory, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_active',
        'hierarchy_level',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'hierarchy_level' => 'integer',
    ];

    /**
     * Role hierarchy levels
     */
    const LEVEL_SUPER_ADMIN = 1;
    const LEVEL_ADMIN = 2;
    const LEVEL_MANAGER = 3;
    const LEVEL_AGENT = 4;
    const LEVEL_CLIENT = 5;

    /**
     * Default role names
     */
    const SUPER_ADMIN = 'super-admin';
    const ADMIN = 'admin';
    const AGENT_MANAGER = 'agent-manager';
    const AGENT = 'agent';
    const CLIENT_MANAGER = 'client-manager';
    const CLIENT = 'client';

    /**
     * Get all permissions associated with this role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    /**
     * Get all users with this role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if role has a specific permission.
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()->where('name', $permissionName)->exists();
    }

    /**
     * Check if role has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->permissions()->whereIn('name', $permissions)->exists();
    }

    /**
     * Check if role has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        $rolePermissions = $this->permissions()->pluck('name')->toArray();
        return empty(array_diff($permissions, $rolePermissions));
    }

    /**
     * Give permission(s) to the role.
     */
    public function givePermissionTo(...$permissions): self
    {
        $permissions = collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                if (empty($permission)) {
                    return false;
                }
                return $this->getStoredPermission($permission);
            })
            ->filter(function ($permission) {
                return $permission instanceof Permission;
            })
            ->map->id
            ->all();

        if (count($permissions)) {
            $this->permissions()->syncWithoutDetaching($permissions);
        }

        return $this;
    }

    /**
     * Revoke permission(s) from the role.
     */
    public function revokePermissionTo(...$permissions): self
    {
        $permissions = collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                if (empty($permission)) {
                    return false;
                }
                return $this->getStoredPermission($permission);
            })
            ->filter(function ($permission) {
                return $permission instanceof Permission;
            })
            ->map->id
            ->all();

        $this->permissions()->detach($permissions);

        return $this;
    }

    /**
     * Sync permissions for the role.
     */
    public function syncPermissions(...$permissions): self
    {
        $permissions = collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                if (empty($permission)) {
                    return false;
                }
                return $this->getStoredPermission($permission);
            })
            ->filter(function ($permission) {
                return $permission instanceof Permission;
            })
            ->map->id
            ->all();

        $this->permissions()->sync($permissions);

        return $this;
    }

    /**
     * Get stored permission from name or Permission instance.
     */
    protected function getStoredPermission($permission): ?Permission
    {
        if (is_string($permission)) {
            return Permission::where('name', $permission)->first();
        }

        if ($permission instanceof Permission) {
            return $permission;
        }

        return null;
    }

    /**
     * Scope to filter active roles.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to search roles.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('display_name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to order by hierarchy.
     */
    public function scopeOrderByHierarchy(Builder $query): Builder
    {
        return $query->orderBy('hierarchy_level', 'asc');
    }

    /**
     * Check if this role is higher in hierarchy than another role.
     */
    public function isHigherThan(Role $role): bool
    {
        return $this->hierarchy_level < $role->hierarchy_level;
    }

    /**
     * Check if this role is lower in hierarchy than another role.
     */
    public function isLowerThan(Role $role): bool
    {
        return $this->hierarchy_level > $role->hierarchy_level;
    }

    /**
     * Check if this role can manage another role.
     */
    public function canManage(Role $role): bool
    {
        return $this->isHigherThan($role);
    }

    /**
     * Get roles that this role can manage.
     */
    public function managableRoles(): Builder
    {
        return static::where('hierarchy_level', '>', $this->hierarchy_level)
                    ->where('is_active', true);
    }

    /**
     * Get default permissions for role based on hierarchy.
     */
    public function getDefaultPermissions(): array
    {
        return match($this->name) {
            self::SUPER_ADMIN => [
                'manage-users', 'manage-roles', 'manage-clients', 'assign-agents',
                'approve-work-updates', 'view-reports', 'manage-settings',
                'bulk-operations', 'system-admin'
            ],
            self::ADMIN => [
                'manage-users', 'manage-clients', 'assign-agents',
                'approve-work-updates', 'view-reports', 'bulk-operations'
            ],
            self::AGENT_MANAGER => [
                'assign-agents', 'approve-work-updates', 'view-reports'
            ],
            self::AGENT => [
                'submit-work-updates', 'view-own-updates'
            ],
            self::CLIENT_MANAGER => [
                'manage-clients', 'view-reports'
            ],
            self::CLIENT => [
                'view-own-updates'
            ],
            default => []
        };
    }

    /**
     * Get display name or fallback to name.
     */
    public function getDisplayNameAttribute($value): string
    {
        return $value ?: ucwords(str_replace(['-', '_'], ' ', $this->name));
    }

    /**
     * Get user count for this role.
     */
    public function getUserCountAttribute(): int
    {
        return $this->users()->count();
    }

    /**
     * Get permission count for this role.
     */
    public function getPermissionCountAttribute(): int
    {
        return $this->permissions()->count();
    }
}
