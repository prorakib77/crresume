<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\Searchable;

class Permission extends Model
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
        'category',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Permission categories
     */
    const CATEGORY_USER_MANAGEMENT = 'user-management';
    const CATEGORY_CLIENT_MANAGEMENT = 'client-management';
    const CATEGORY_WORK_UPDATES = 'work-updates';
    const CATEGORY_REPORTS = 'reports';
    const CATEGORY_SYSTEM = 'system';
    const CATEGORY_ASSIGNMENTS = 'assignments';

    /**
     * Core permission names
     */
    const MANAGE_USERS = 'manage-users';
    const MANAGE_ROLES = 'manage-roles';
    const MANAGE_CLIENTS = 'manage-clients';
    const ASSIGN_AGENTS = 'assign-agents';
    const APPROVE_WORK_UPDATES = 'approve-work-updates';
    const SUBMIT_WORK_UPDATES = 'submit-work-updates';
    const VIEW_OWN_UPDATES = 'view-own-updates';
    const MANAGE_SETTINGS = 'manage-settings';
    const VIEW_REPORTS = 'view-reports';
    const BULK_OPERATIONS = 'bulk-operations';
    const SYSTEM_ADMIN = 'system-admin';

    /**
     * Get all roles that have this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    /**
     * Get all available permission categories.
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_USER_MANAGEMENT => 'User Management',
            self::CATEGORY_CLIENT_MANAGEMENT => 'Client Management',
            self::CATEGORY_WORK_UPDATES => 'Work Updates',
            self::CATEGORY_REPORTS => 'Reports & Analytics',
            self::CATEGORY_SYSTEM => 'System Administration',
            self::CATEGORY_ASSIGNMENTS => 'Agent Assignments',
        ];
    }

    /**
     * Get core permissions with descriptions.
     */
    public static function getCorePermissions(): array
    {
        return [
            self::MANAGE_USERS => [
                'display_name' => 'Manage Users',
                'description' => 'Create, edit, delete, and manage user accounts',
                'category' => self::CATEGORY_USER_MANAGEMENT,
            ],
            self::MANAGE_ROLES => [
                'display_name' => 'Manage Roles',
                'description' => 'Create, edit, delete, and assign roles and permissions',
                'category' => self::CATEGORY_USER_MANAGEMENT,
            ],
            self::MANAGE_CLIENTS => [
                'display_name' => 'Manage Clients',
                'description' => 'Create, edit, delete, and manage client profiles',
                'category' => self::CATEGORY_CLIENT_MANAGEMENT,
            ],
            self::ASSIGN_AGENTS => [
                'display_name' => 'Assign Agents',
                'description' => 'Assign and reassign agents to clients',
                'category' => self::CATEGORY_ASSIGNMENTS,
            ],
            self::APPROVE_WORK_UPDATES => [
                'display_name' => 'Approve Work Updates',
                'description' => 'Review, approve, reject work updates submitted by agents',
                'category' => self::CATEGORY_WORK_UPDATES,
            ],
            self::SUBMIT_WORK_UPDATES => [
                'display_name' => 'Submit Work Updates',
                'description' => 'Create and submit work updates for clients',
                'category' => self::CATEGORY_WORK_UPDATES,
            ],
            self::VIEW_OWN_UPDATES => [
                'display_name' => 'View Own Updates',
                'description' => 'View own work updates and status',
                'category' => self::CATEGORY_WORK_UPDATES,
            ],
            self::MANAGE_SETTINGS => [
                'display_name' => 'Manage Settings',
                'description' => 'Configure system settings and preferences',
                'category' => self::CATEGORY_SYSTEM,
            ],
            self::VIEW_REPORTS => [
                'display_name' => 'View Reports',
                'description' => 'Access reports and analytics dashboards',
                'category' => self::CATEGORY_REPORTS,
            ],
            self::BULK_OPERATIONS => [
                'display_name' => 'Bulk Operations',
                'description' => 'Perform bulk operations on users, clients, and updates',
                'category' => self::CATEGORY_SYSTEM,
            ],
            self::SYSTEM_ADMIN => [
                'display_name' => 'System Administration',
                'description' => 'Full system administration access',
                'category' => self::CATEGORY_SYSTEM,
            ],
        ];
    }

    /**
     * Scope to filter active permissions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to search permissions.
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
     * Get display name or fallback to name.
     */
    public function getDisplayNameAttribute($value): string
    {
        return $value ?: ucwords(str_replace(['-', '_'], ' ', $this->name));
    }

    /**
     * Get category display name.
     */
    public function getCategoryDisplayNameAttribute(): string
    {
        $categories = self::getCategories();
        return $categories[$this->category] ?? ucwords(str_replace(['-', '_'], ' ', $this->category));
    }

    /**
     * Get role count for this permission.
     */
    public function getRoleCountAttribute(): int
    {
        return $this->roles()->count();
    }

    /**
     * Create or update core permissions.
     */
    public static function createCorePermissions(): void
    {
        foreach (self::getCorePermissions() as $name => $data) {
            self::updateOrCreate(
                ['name' => $name],
                array_merge($data, ['is_active' => true])
            );
        }
    }

    /**
     * Check if permission exists.
     */
    public static function exists(string $name): bool
    {
        return self::where('name', $name)->exists();
    }

    /**
     * Get permissions grouped by category.
     */
    public static function getGroupedByCategory(): array
    {
        return self::active()
                  ->orderBy('category')
                  ->orderBy('display_name')
                  ->get()
                  ->groupBy('category')
                  ->toArray();
    }
}
