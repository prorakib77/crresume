<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MailchimpSetting;

class MailchimpSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if Mailchimp settings already exist
        $existingSettings = MailchimpSetting::where('is_active', true)->first();

        if ($existingSettings) {
            $this->command->info('Mailchimp settings already exist. Skipping...');
            return;
        }

        // Create Mailchimp settings with current configuration
        MailchimpSetting::create([
            'api_key' => config('services.mailchimp.api_key'),
            'server_prefix' => config('services.mailchimp.server_prefix', 'us11'),
            'list_id' => config('services.mailchimp.list_id', '41abeb1653'),
            'from_name' => 'W-Automation',
            'from_email' => config('mail.from.address', 'noreply@w-automation.com'),
            'reply_to' => config('mail.from.address', 'noreply@w-automation.com'),
            'is_active' => true,
            'auto_subscribe' => true,
            'send_welcome_email' => true,
            'welcome_email_template' => '<h2>Welcome to W-Automation!</h2><p>Thank you for joining our system. You will receive regular work updates and meeting notifications.</p>',
            'work_update_template' => '<h2>Daily Work Update</h2><p>Here is your daily work summary:</p>',
            'merge_fields' => [
                'FNAME' => 'First Name',
                'LNAME' => 'Last Name',
                'COMPANY' => 'Company'
            ],
            'tags' => ['client', 'work-updates', 'automation'],
            'last_sync_at' => null,
            'sync_status' => 'Ready for configuration'
        ]);

        $this->command->info('Mailchimp settings created successfully!');
    }
}
