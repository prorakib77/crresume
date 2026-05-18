<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, ensure roles and permissions exist
        $this->ensureRolesAndPermissions();

        // Create Super Admin user
        $superAdminRole = Role::where('name', 'super-admin')->first();

        if ($superAdminRole) {
            // Check if super admin already exists
            $existingSuperAdmin = User::where('email', 'superadmin@wautomation.com')->first();

            if (!$existingSuperAdmin) {
                $superAdmin = User::create([
                    'name' => 'Super Administrator',
                    'email' => 'superadmin@wautomation.com',
                    'password' => Hash::make('password'),
                    'role_id' => $superAdminRole->id,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]);

                echo "Super Admin created successfully!\n";
                echo "Email: superadmin@wautomation.com\n";
                echo "Password: password\n";
            } else {
                echo "Super Admin already exists!\n";
            }
        } else {
            echo "Super Admin role not found. Please run FinalRolePermissionSeeder first.\n";
        }
    }

    private function ensureRolesAndPermissions(): void
    {
        // Check if roles exist, if not create them
        if (Role::count() === 0) {
            echo "No roles found. Creating basic roles...\n";

            $roles = [
                [
                    'name' => 'super-admin',
                    'display_name' => 'Super Administrator',
                    'description' => 'Full system access with all permissions',
                    'hierarchy_level' => 1,
                    'is_active' => true
                ],
                [
                    'name' => 'admin',
                    'display_name' => 'Administrator',
                    'description' => 'Full administrative access to all system functions',
                    'hierarchy_level' => 2,
                    'is_active' => true
                ],
                [
                    'name' => 'agent',
                    'display_name' => 'Agent',
                    'description' => 'Submit work updates, see assigned clients, view own work updates',
                    'hierarchy_level' => 3,
                    'is_active' => true
                ],
                [
                    'name' => 'client',
                    'display_name' => 'Client',
                    'description' => 'View their work updates as daily work table',
                    'hierarchy_level' => 4,
                    'is_active' => true
                ],
            ];

            foreach ($roles as $role) {
                Role::create($role);
            }
        }

        // Check if permissions exist, if not create them
        if (Permission::count() === 0) {
            echo "No permissions found. Creating basic permissions...\n";

            $permissions = [
                ['name' => 'manage-users', 'display_name' => 'Manage Users', 'description' => 'Create, edit, delete users', 'category' => 'user-management'],
                ['name' => 'manage-roles', 'display_name' => 'Manage Roles', 'description' => 'Create, edit, delete roles', 'category' => 'user-management'],
                ['name' => 'manage-clients', 'display_name' => 'Manage Clients', 'description' => 'Manage client profiles', 'category' => 'client-management'],
                ['name' => 'assign-agents', 'display_name' => 'Assign Agents', 'description' => 'Assign agents to clients', 'category' => 'assignments'],
                ['name' => 'submit-work-updates', 'display_name' => 'Submit Work Updates', 'description' => 'Submit work updates', 'category' => 'work-updates'],
                ['name' => 'view-own-updates', 'display_name' => 'View Own Updates', 'description' => 'View own updates', 'category' => 'work-updates'],
                ['name' => 'view-all-updates', 'display_name' => 'View All Updates', 'description' => 'View all updates', 'category' => 'work-updates'],
                ['name' => 'manage-settings', 'display_name' => 'Manage Settings', 'description' => 'Manage system settings', 'category' => 'system'],
                ['name' => 'view-reports', 'display_name' => 'View Reports', 'description' => 'View reports', 'category' => 'reports'],
                ['name' => 'bulk-operations', 'display_name' => 'Bulk Operations', 'description' => 'Perform bulk operations', 'category' => 'system'],
                ['name' => 'system-admin', 'display_name' => 'System Administration', 'description' => 'Full system administration access', 'category' => 'system'],
            ];

            foreach ($permissions as $permission) {
                Permission::create(array_merge($permission, ['is_active' => true]));
            }
        }

        // Assign all permissions to super-admin role
        $superAdminRole = Role::where('name', 'super-admin')->first();
        $allPermissions = Permission::pluck('id');

        if ($superAdminRole && $allPermissions->count() > 0) {
            $superAdminRole->permissions()->sync($allPermissions);
            echo "All permissions assigned to Super Admin role.\n";
        }
    }
}
