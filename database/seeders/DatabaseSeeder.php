<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

require_once __DIR__ . '/ClientWorkUpdatesSeeder.php';

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // First, seed roles & permissions
        $this->call(FinalRolePermissionSeeder::class);

        // Create Super Admin
        $this->call(SuperAdminSeeder::class);

        // Create additional test users
        $this->call(AdminSeeder::class);

        // Create sample assignments
        $this->call(AssignmentSeeder::class);

        // Seed welcome-page products
        $this->call(ProductSeeder::class);

        // Seed client-facing FAQs
        $this->call(FaqSeeder::class);

        // Create sample client work updates
        $this->call(ClientWorkUpdatesSeeder::class);

        // Create sample notifications
        $this->call(NotificationSeeder::class);
    }
}
