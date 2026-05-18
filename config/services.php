<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_REDIRECT_URI', rtrim((string) env('APP_URL', 'http://localhost'), '/').'/google/oauth/callback'),
        'service_account_path' => env('GOOGLE_SERVICE_ACCOUNT_PATH'),
        'admin_email' => env('GOOGLE_ADMIN_EMAIL', 'caliroweteam@caliwfhresumes.com'),
        'meet_api_key' => env('GOOGLE_MEET_API_KEY'),
        'calendar_id' => env('GOOGLE_CALENDAR_ID', 'primary'),
        'meeting_time' => env('GOOGLE_MEETING_TIME', '09:00'),
        'meeting_duration' => env('GOOGLE_MEETING_DURATION', 60),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'mailchimp' => [
        'api_key' => env('MAILCHIMP_API_KEY'),
        'server_prefix' => env('MAILCHIMP_SERVER_PREFIX', ''),
        'list_id' => env('MAILCHIMP_LIST_ID', ''),
    ],

];
