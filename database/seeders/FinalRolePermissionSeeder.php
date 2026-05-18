<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class FinalRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('role_permission')->truncate();
        DB::table('users')->truncate();
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create permissions
        $this->createPermissions();

        // Create roles (only 4 roles as requested)
        $this->createRoles();

        // Assign permissions
        $this->assignPermissions();

        // Create test users
        $this->createUsers();

        echo "Final role system seeded successfully!\n";
        echo "Test accounts:\n";
        echo "superadmin@wautomation.com / password (Super Admin - Full Access)\n";
        echo "admin@wautomation.com / password (Admin - Full Access)\n";
        echo "agent@wautomation.com / password (Agent - Submit Work Updates)\n";
        echo "client@wautomation.com / password (Client - View Work Updates)\n";
    }

    private function createPermissions(): void
    {
        $permissions = [
            // Core permissions only
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

    private function createRoles(): void
    {
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

    private function assignPermissions(): void
    {
        $allPermissions = Permission::pluck('name')->toArray();

        $rolePermissions = [
            'super-admin' => $allPermissions, // Full access
            'admin' => $allPermissions, // Full access
            'agent' => [
                'submit-work-updates',
                'view-own-updates',
                'view-all-updates' // To see assigned clients
            ],
            'client' => [
                'view-own-updates'
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');
                $role->permissions()->sync($permissionIds);
            }
        }
    }

    private function createUsers(): void
    {
        $users = [
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@wautomation.com',
                'password' => Hash::make('password'),
                'role' => 'super-admin',
                'status' => 'active',
            ],
            [
                'name' => 'System Administrator',
                'email' => 'admin@wautomation.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
            ],
            [
                'name' => 'Test Agent',
                'email' => 'agent@wautomation.com',
                'password' => Hash::make('password'),
                'role' => 'agent',
                'status' => 'active',
            ],
            [
                'name' => 'Test Client',
                'email' => 'client@wautomation.com',
                'password' => Hash::make('password'),
                'role' => 'client',
                'status' => 'active',
            ],
        ];

        foreach ($users as $userData) {
            $role = Role::where('name', $userData['role'])->first();
            if ($role) {
                User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => $userData['password'],
                    'role_id' => $role->id,
                    'status' => $userData['status'],
                    'email_verified_at' => now(),
                ]);
            }
        }
    }
}
