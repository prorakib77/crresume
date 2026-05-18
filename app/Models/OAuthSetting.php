<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class OAuthSetting extends Model
{
    protected $table = 'oauth_settings';

    protected $fillable = [
        'service_name',
        'credentials_json',
        'admin_email',
        'calendar_id',
        'timezone',
        'meet_room_link',
        'is_active',
        'auto_generate_meetings',
        'meeting_start_time',
        'meeting_end_time',
        'meeting_duration_minutes',
        'meeting_attendees',
        'send_notifications',
        'create_calendar_events',
        'auto_join_enabled',
        'meeting_settings',
        'last_sync_at',
        'sync_status'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_generate_meetings' => 'boolean',
        'send_notifications' => 'boolean',
        'create_calendar_events' => 'boolean',
        'auto_join_enabled' => 'boolean',
        'meeting_attendees' => 'array',
        'meeting_settings' => 'array',
        'last_sync_at' => 'datetime'
    ];

    /**
     * Get the active OAuth settings
     */
    public static function getActive()
    {
        return Cache::remember('oauth_settings_active', 3600, function () {
            return static::where('is_active', true)->first();
        });
    }

    /**
     * Get Google Meet settings
     */
    public static function getGoogleMeetSettings()
    {
        return Cache::remember('oauth_google_meet_settings', 3600, function () {
            return static::where('service_name', 'google_meet')
                ->where('is_active', true)
                ->first();
        });
    }

    /**
     * Update settings and clear cache
     */
    public function updateSettings(array $data)
    {
        $this->update($data);
        Cache::forget('oauth_settings_active');
        Cache::forget('oauth_google_meet_settings');
        return $this;
    }

    /**
     * Test OAuth connection
     */
    public function testConnection()
    {
        try {
            if (!$this->credentials_json) {
                throw new \Exception('No credentials provided');
            }

            $credentials = json_decode($this->credentials_json, true);
            if (!$credentials) {
                throw new \Exception('Invalid credentials JSON');
            }

            // Test Google API connection
            $client = new \Google_Client();
            $client->setAuthConfig($credentials);
            $client->setScopes([
                \Google_Service_Calendar::CALENDAR,
                \Google_Service_Calendar::CALENDAR_EVENTS
            ]);

            $service = new \Google_Service_Calendar($client);
            $calendar = $service->calendarList->get($this->calendar_id);

            $this->update([
                'last_sync_at' => now(),
                'sync_status' => 'Connected successfully'
            ]);

            return [
                'success' => true,
                'message' => 'OAuth connection successful',
                'calendar_title' => $calendar->getSummary()
            ];

        } catch (\Exception $e) {
            $this->update([
                'sync_status' => 'Connection failed: ' . $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'OAuth connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get credentials as array
     */
    public function getCredentialsArray()
    {
        return json_decode($this->credentials_json, true) ?? [];
    }

    /**
     * Get meeting settings with defaults
     */
    public function getMeetingSettings()
    {
        $defaults = [
            'duration' => $this->meeting_duration_minutes,
            'start_time' => $this->meeting_start_time,
            'end_time' => $this->meeting_end_time,
            'attendees' => $this->meeting_attendees ?? [],
            'description' => $this->meeting_description,
            'send_notifications' => $this->send_notifications,
            'create_calendar_events' => $this->create_calendar_events,
            'auto_join_enabled' => $this->auto_join_enabled
        ];

        return array_merge($defaults, $this->meeting_settings ?? []);
    }
}
