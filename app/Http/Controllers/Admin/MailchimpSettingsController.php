<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MailchimpSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MailchimpSettingsController extends Controller
{
    /**
     * Display Mailchimp settings management page
     */
    public function index()
    {
        $mailchimpSettings = MailchimpSetting::getActive();

        return view('admin.mailchimp.index', compact('mailchimpSettings'));
    }

    /**
     * Show the form for creating/editing Mailchimp settings
     */
    public function create()
    {
        $mailchimpSettings = MailchimpSetting::getActive();
        return view('admin.mailchimp.create', compact('mailchimpSettings'));
    }

    /**
     * Store or update Mailchimp settings
     */
    public function store(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string',
            'server_prefix' => 'required|string|in:us1,us2,us3,us4,us5,us6,us7,us8,us9,us10,us11,us12,us13,us14,us15,us16,us17,us18,us19,us20,us21',
            'list_id' => 'required|string',
            'from_name' => 'required|string|max:255',
            'from_email' => 'required|email',
            'reply_to' => 'nullable|email',
            'is_active' => 'boolean',
            'auto_subscribe' => 'boolean',
            'send_welcome_email' => 'boolean',
            'welcome_email_template' => 'nullable|string',
            'work_update_template' => 'nullable|string',
            'merge_fields' => 'nullable|string',
            'tags' => 'nullable|string',
        ]);

        try {
            // Get or create Mailchimp settings
            $mailchimpSettings = MailchimpSetting::where('is_active', true)->first();

            if (!$mailchimpSettings) {
                $mailchimpSettings = new MailchimpSetting();
            }

            // Parse JSON fields
            $mergeFields = $request->merge_fields ? json_decode($request->merge_fields, true) : null;
            $tags = $request->tags ? json_decode($request->tags, true) : null;

            // Update settings
            $mailchimpSettings->updateSettings([
                'api_key' => $request->api_key,
                'server_prefix' => $request->server_prefix,
                'list_id' => $request->list_id,
                'from_name' => $request->from_name,
                'from_email' => $request->from_email,
                'reply_to' => $request->reply_to,
                'is_active' => $request->boolean('is_active', true),
                'auto_subscribe' => $request->boolean('auto_subscribe', true),
                'send_welcome_email' => $request->boolean('send_welcome_email', true),
                'welcome_email_template' => $request->welcome_email_template,
                'work_update_template' => $request->work_update_template,
                'merge_fields' => $mergeFields,
                'tags' => $tags,
            ]);

            return redirect()->route('admin.mailchimp.index')
                ->with('success', 'Mailchimp settings updated successfully!');

        } catch (\Exception $e) {
            Log::error('Mailchimp settings update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update Mailchimp settings: ' . $e->getMessage());
        }
    }

    /**
     * Test Mailchimp connection
     */
    public function testConnection(Request $request)
    {
        try {
            $mailchimpSettings = MailchimpSetting::getActive();

            if (!$mailchimpSettings) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Mailchimp settings found'
                ]);
            }

            $result = $mailchimpSettings->testConnection();

            if ($result) {
                $accountInfo = $mailchimpSettings->getAccountInfo();
                $listInfo = $mailchimpSettings->getListInfo();

                return response()->json([
                    'success' => true,
                    'message' => 'Mailchimp connection successful!',
                    'account_info' => $accountInfo,
                    'list_info' => $listInfo
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Mailchimp connection failed. Please check your API key and server prefix.'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Mailchimp connection test failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get Mailchimp lists
     */
    public function getLists(Request $request)
    {
        try {
            $mailchimpSettings = MailchimpSetting::getActive();

            if (!$mailchimpSettings || !$mailchimpSettings->api_key || !$mailchimpSettings->server_prefix) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mailchimp settings not configured'
                ]);
            }

            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://{$mailchimpSettings->server_prefix}.api.mailchimp.com/3.0/lists", [
                'auth' => ['anystring', $mailchimpSettings->api_key],
                'query' => [
                    'count' => 100,
                    'fields' => 'lists.id,lists.name,lists.stats'
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            $lists = $data['lists'] ?? [];

            return response()->json([
                'success' => true,
                'lists' => $lists
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get Mailchimp lists: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get lists: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Send test email
     */
    public function sendTestEmail(Request $request)
    {
        try {
            $request->validate([
                'test_email' => 'required|email'
            ]);

            $mailchimpSettings = MailchimpSetting::getActive();

            if (!$mailchimpSettings) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Mailchimp settings found'
                ]);
            }

            // Use the existing MailchimpService to send test email
            $mailchimpService = app(\App\Services\MailchimpService::class);

            // Create a test work update
            $testWorkUpdate = [
                'client_name' => 'Test Client',
                'date' => now()->format('Y-m-d'),
                'tasks_completed' => ['Test task 1', 'Test task 2'],
                'hours_worked' => 8,
                'notes' => 'This is a test email from W-Automation system.'
            ];

            $result = $mailchimpService->sendDailyWorkUpdate(
                (object)['name' => 'Test Client', 'email' => $request->test_email],
                [$testWorkUpdate],
                now()
            );

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test email sent successfully to ' . $request->test_email
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send test email'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Test email sending failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Reset Mailchimp settings to defaults
     */
    public function reset(Request $request)
    {
        try {
            $mailchimpSettings = MailchimpSetting::where('is_active', true)->first();

            if ($mailchimpSettings) {
                $mailchimpSettings->updateSettings([
                    'api_key' => null,
                    'server_prefix' => 'us18',
                    'list_id' => null,
                    'from_name' => 'W-Automation',
                    'from_email' => null,
                    'reply_to' => null,
                    'is_active' => false,
                    'auto_subscribe' => true,
                    'send_welcome_email' => true,
                    'welcome_email_template' => null,
                    'work_update_template' => null,
                    'merge_fields' => null,
                    'tags' => null,
                ]);
            }

            return redirect()->route('admin.mailchimp.index')
                ->with('success', 'Mailchimp settings reset to defaults!');

        } catch (\Exception $e) {
            Log::error('Mailchimp settings reset failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to reset Mailchimp settings: ' . $e->getMessage());
        }
    }

    /**
     * Get current Mailchimp status
     */
    public function status()
    {
        try {
            $mailchimpSettings = MailchimpSetting::getActive();

            if (!$mailchimpSettings) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Mailchimp settings configured',
                    'status' => 'not_configured'
                ]);
            }

            return response()->json([
                'success' => true,
                'status' => $mailchimpSettings->is_active ? 'active' : 'inactive',
                'last_sync' => $mailchimpSettings->last_sync_at,
                'sync_status' => $mailchimpSettings->sync_status,
                'account_info' => $mailchimpSettings->getAccountInfo(),
                'list_info' => $mailchimpSettings->getListInfo()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get Mailchimp status: ' . $e->getMessage()
            ]);
        }
    }
}
