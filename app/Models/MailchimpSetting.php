<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MailchimpSetting extends Model
{
    protected $fillable = [
        'api_key',
        'server_prefix',
        'list_id',
        'from_name',
        'from_email',
        'reply_to',
        'is_active',
        'auto_subscribe',
        'send_welcome_email',
        'welcome_email_template',
        'work_update_template',
        'merge_fields',
        'tags',
        'last_sync_at',
        'sync_status'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_subscribe' => 'boolean',
        'send_welcome_email' => 'boolean',
        'merge_fields' => 'array',
        'tags' => 'array',
        'last_sync_at' => 'datetime'
    ];

    /**
     * Get the active Mailchimp settings
     */
    public static function getActive()
    {
        return Cache::remember('mailchimp_settings_active', 3600, function () {
            return static::where('is_active', true)->first();
        });
    }

    /**
     * Update settings and clear cache
     */
    public function updateSettings(array $data)
    {
        $this->update($data);
        Cache::forget('mailchimp_settings_active');
        return $this;
    }

    /**
     * Test Mailchimp connection
     */
    public function testConnection()
    {
        try {
            if (!$this->api_key || !$this->server_prefix) {
                throw new \Exception('API key or server prefix not configured');
            }

            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://{$this->server_prefix}.api.mailchimp.com/3.0/ping", [
                'auth' => ['anystring', $this->api_key]
            ]);

            $this->update([
                'last_sync_at' => now(),
                'sync_status' => 'Connected successfully'
            ]);

            return true;

        } catch (\Exception $e) {
            $this->update([
                'sync_status' => 'Connection failed: ' . $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Get account information
     */
    public function getAccountInfo()
    {
        try {
            if (!$this->api_key || !$this->server_prefix) {
                return null;
            }

            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://{$this->server_prefix}.api.mailchimp.com/3.0/", [
                'auth' => ['anystring', $this->api_key]
            ]);

            $data = json_decode($response->getBody(), true);

            return [
                'account_name' => $data['account_name'] ?? 'N/A',
                'email' => $data['email'] ?? 'N/A',
                'server' => $this->server_prefix
            ];

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get list information
     */
    public function getListInfo()
    {
        try {
            if (!$this->api_key || !$this->server_prefix || !$this->list_id) {
                return null;
            }

            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://{$this->server_prefix}.api.mailchimp.com/3.0/lists/{$this->list_id}", [
                'auth' => ['anystring', $this->api_key]
            ]);

            $data = json_decode($response->getBody(), true);

            return [
                'name' => $data['name'] ?? 'N/A',
                'member_count' => $data['stats']['member_count'] ?? 0,
                'unsubscribe_count' => $data['stats']['unsubscribe_count'] ?? 0
            ];

        } catch (\Exception $e) {
            return null;
        }
    }
}
