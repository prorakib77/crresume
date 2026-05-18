<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OAuthSetting;

class OAuthSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if OAuth settings already exist
        $existingSettings = OAuthSetting::where('service_name', 'google_meet')->first();

        if ($existingSettings) {
            $this->command->info('OAuth settings already exist. Skipping...');
            return;
        }

        // Get current Google Meet settings from environment/config
        $currentCredentials = null;
        $credentialsPath = storage_path('app/google-service-account.json');

        if (file_exists($credentialsPath)) {
            $currentCredentials = file_get_contents($credentialsPath);
        } elseif (
            filled(env('GOOGLE_PROJECT_ID'))
            && filled(env('GOOGLE_SERVICE_EMAIL'))
            && filled(env('GOOGLE_PRIVATE_KEY'))
        ) {
            $currentCredentials = json_encode([
                'type' => 'service_account',
                'project_id' => env('GOOGLE_PROJECT_ID'),
                'private_key_id' => env('GOOGLE_PRIVATE_KEY_ID'),
                'private_key' => env('GOOGLE_PRIVATE_KEY'),
                'client_email' => env('GOOGLE_SERVICE_EMAIL'),
                'client_id' => env('GOOGLE_PROJECT_NUMBER'),
                'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
                'token_uri' => 'https://oauth2.googleapis.com/token',
                'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
                'client_x509_cert_url' => null,
                'universe_domain' => 'googleapis.com',
            ]);
        }

        // Create OAuth settings with current values
        OAuthSetting::create([
            'service_name' => 'google_meet',
            'credentials_json' => $currentCredentials,
            'admin_email' => env('GOOGLE_ADMIN_EMAIL', 'caliroweteam@caliwfhresumes.com'),
            'calendar_id' => env('GOOGLE_CALENDAR_ID', 'primary'),
            'timezone' => env('GOOGLE_TIMEZONE', 'Asia/Dhaka'),
            'meet_room_link' => env('GOOGLE_MEET_ROOM_LINK', 'https://meet.google.com/eav-xnab-doc'),
            'is_active' => true,
            'auto_generate_meetings' => true,
            'meeting_start_time' => '09:00:00',
            'meeting_end_time' => '17:00:00',
            'meeting_duration_minutes' => 60,
            'meeting_description' => 'Daily team meeting for work updates and coordination',
            'send_notifications' => true,
            'create_calendar_events' => true,
            'auto_join_enabled' => false,
            'meeting_settings' => [
                'conference_solution' => 'google_meet',
                'send_updates' => 'all',
                'reminders' => [
                    'use_default' => true,
                    'overrides' => [
                        ['method' => 'email', 'minutes' => 10],
                        ['method' => 'popup', 'minutes' => 5]
                    ]
                ]
            ]
        ]);

        $this->command->info('OAuth settings seeded successfully!');
        $this->command->info('Current Google Meet settings have been preserved.');
    }
}
