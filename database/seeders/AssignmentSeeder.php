<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AgentClientAssignment;
use App\Models\User;

class AssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get agents and clients
        $agents = User::where('role_id', 3)->get();
        $clients = User::where('role_id', 4)->get();

        if ($agents->count() == 0 || $clients->count() == 0) {
            $this->command->info('No agents or clients found. Please run other seeders first.');
            return;
        }

        // Create some sample assignments
        $assignments = [
            [
                'agent_id' => $agents->first()->id,
                'client_id' => $clients->first()->id,
                'assigned_date' => now()->subDays(5),
                'service_end_date' => now()->addDays(25),
                'is_active' => true,
            ],
        ];

        // If we have multiple agents and clients, create more assignments
        if ($agents->count() > 1 && $clients->count() > 1) {
            $assignments[] = [
                'agent_id' => $agents->skip(1)->first()->id,
                'client_id' => $clients->skip(1)->first()->id,
                'assigned_date' => now()->subDays(3),
                'service_end_date' => now()->addDays(30),
                'is_active' => true,
            ];
        }

        // Create assignments
        foreach ($assignments as $assignmentData) {
            AgentClientAssignment::firstOrCreate(
                [
                    'agent_id' => $assignmentData['agent_id'],
                    'client_id' => $assignmentData['client_id'],
                ],
                $assignmentData
            );
        }

        $this->command->info('Sample assignments created successfully!');
    }
}
