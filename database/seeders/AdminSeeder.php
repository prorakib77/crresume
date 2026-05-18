<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Create an admin user
        \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'abc@gmail.com',
            'password' => bcrypt('abc@gmail.com'), // Change this to a secure password
            'role_id' => 1, // Assuming '1' is the ID for the admin role
        ]);
    }
}
