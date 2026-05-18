<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Google Calendar and Meet API integration
    |
    */

    'credentials_path' => env('GOOGLE_CREDENTIALS_PATH', storage_path('app/google/credentials.json')),
    'admin_email' => env('GOOGLE_ADMIN_EMAIL', 'caliroweteam@caliwfhresumes.com'),
    'calendar_id' => env('GOOGLE_CALENDAR_ID', 'primary'),
    'timezone' => env('GOOGLE_TIMEZONE', env('APP_TIMEZONE', 'America/Los_Angeles')),

    /*
    |--------------------------------------------------------------------------
    | Meeting Configuration
    |--------------------------------------------------------------------------
    */

    'meeting' => [
        'duration_hours' => env('GOOGLE_MEETING_DURATION', 24),
        'auto_generate' => env('GOOGLE_AUTO_GENERATE', true),
        'notification_enabled' => env('GOOGLE_NOTIFICATION_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Account Configuration
    |--------------------------------------------------------------------------
    */

    'service_account' => [
        'project_id' => env('GOOGLE_PROJECT_ID', 'wfh-resume'),
        'client_email' => env('GOOGLE_SERVICE_EMAIL', 'wfh-resume@wfh-resume.iam.gserviceaccount.com'),
        'private_key' => env('GOOGLE_PRIVATE_KEY'),
        'private_key_id' => env('GOOGLE_PRIVATE_KEY_ID'),
    ],
];
