<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users to create notifications for
        $users = User::all();

        if ($users->count() < 2) {
            $this->command->info('Not enough users to create notifications. Please run other seeders first.');
            return;
        }

        // Create sample notifications for each user
        foreach ($users as $user) {
            // Welcome notification
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Welcome to W-Automation!',
                'message' => 'Welcome to our work-from-home job placement system. We\'re here to help you succeed!',
                'type' => Notification::TYPE_SUCCESS,
                'priority' => Notification::PRIORITY_NORMAL,
                'data' => ['welcome' => true],
                'action_url' => route('dashboard'),
            ]);

            // System update notification
            Notification::create([
                'user_id' => $user->id,
                'title' => 'System Update Available',
                'message' => 'A new system update is available with improved features and bug fixes.',
                'type' => Notification::TYPE_INFO,
                'priority' => Notification::PRIORITY_NORMAL,
                'data' => ['update_version' => '1.2.0'],
                'action_url' => route('admin.settings'),
            ]);

            // Work update notification (for agents and clients)
            if ($user->isAgent() || $user->isClient()) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Daily Work Update Reminder',
                    'message' => 'Don\'t forget to submit your daily work updates to keep your clients informed.',
                    'type' => Notification::TYPE_WORK_UPDATE,
                    'priority' => Notification::PRIORITY_HIGH,
                    'data' => ['reminder_type' => 'daily'],
                    'action_url' => $user->isAgent() ? route('agent.work-updates.create') : route('client.dashboard'),
                ]);
            }

            // Assignment notification (for agents)
            if ($user->isAgent()) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'New Client Assignment',
                    'message' => 'You have been assigned to a new client. Check your dashboard for details.',
                    'type' => Notification::TYPE_INFO,
                    'priority' => Notification::PRIORITY_NORMAL,
                    'data' => ['assignment_type' => 'new_client'],
                    'action_url' => route('agent.dashboard'),
                ]);
            }

            // Work update received notification (for clients)
            if ($user->isClient()) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'New Work Updates Received',
                    'message' => 'Your agent has submitted new work updates for today. Check them out!',
                    'type' => Notification::TYPE_WORK_UPDATE,
                    'priority' => Notification::PRIORITY_NORMAL,
                    'data' => ['updates_count' => 4],
                    'action_url' => route('client.dashboard'),
                ]);
            }

            // Admin notification (for admins)
            if ($user->isAdmin() || $user->isSuperAdmin()) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'System Maintenance Scheduled',
                    'message' => 'System maintenance is scheduled for tonight at 2 AM. Please plan accordingly.',
                    'type' => Notification::TYPE_WARNING,
                    'priority' => Notification::PRIORITY_HIGH,
                    'data' => ['maintenance_time' => '2024-01-15 02:00:00'],
                    'action_url' => route('admin.settings'),
                ]);
            }
        }

        $this->command->info('Sample notifications created successfully!');
    }
}
