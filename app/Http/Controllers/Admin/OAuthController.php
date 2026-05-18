<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OAuthSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class OAuthController extends Controller
{
    /**
     * Display OAuth settings management page
     */
    public function index()
    {
        $oauthSettings = OAuthSetting::getGoogleMeetSettings();

        return view('admin.oauth.index', compact('oauthSettings'));
    }

    /**
     * Show the form for creating/editing OAuth settings
     */
    public function create()
    {
        $oauthSettings = OAuthSetting::getGoogleMeetSettings();
        return view('admin.oauth.create', compact('oauthSettings'));
    }

    /**
     * Store or update OAuth settings
     */
    public function store(Request $request)
    {
        $request->validate([
            'credentials_json' => 'nullable|string',
            'credentials_file' => 'nullable|file|mimes:json|max:2048',
            'admin_email' => 'required|email',
            'calendar_id' => 'required|string',
            'timezone' => 'required|string',
            'meet_room_link' => 'nullable|url',
            'is_active' => 'boolean',
            'auto_generate_meetings' => 'boolean',
            'meeting_start_time' => 'required|date_format:H:i',
            'meeting_end_time' => 'required|date_format:H:i',
            'meeting_duration_minutes' => 'required|integer|min:15|max:480',
            'meeting_description' => 'nullable|string|max:1000',
            'meeting_attendees' => 'nullable|string',
            'meeting_settings' => 'nullable|string',
            'send_notifications' => 'boolean',
            'create_calendar_events' => 'boolean',
            'auto_join_enabled' => 'boolean',
        ]);

        try {
            $credentialsJson = $request->credentials_json;

            // Handle file upload
            if ($request->hasFile('credentials_file')) {
                $file = $request->file('credentials_file');
                $filePath = $file->store('oauth', 'private');
                $credentialsJson = Storage::disk('private')->get($filePath);
            }

            // Get or create OAuth settings
            $oauthSettings = OAuthSetting::where('service_name', 'google_meet')->first();

            if (!$oauthSettings) {
                $oauthSettings = new OAuthSetting();
                $oauthSettings->service_name = 'google_meet';
            }

            // Update settings
            $oauthSettings->updateSettings([
                'credentials_json' => $credentialsJson,
                'admin_email' => $request->admin_email,
                'calendar_id' => $request->calendar_id,
                'timezone' => $request->timezone,
                'meet_room_link' => $request->meet_room_link,
                'is_active' => $request->boolean('is_active', true),
                'auto_generate_meetings' => $request->boolean('auto_generate_meetings', true),
                'meeting_start_time' => $request->meeting_start_time,
                'meeting_end_time' => $request->meeting_end_time,
                'meeting_duration_minutes' => $request->meeting_duration_minutes,
                'meeting_description' => $request->meeting_description,
                'meeting_attendees' => $request->meeting_attendees ? array_filter(array_map('trim', explode("\n", $request->meeting_attendees))) : null,
                'meeting_settings' => $request->meeting_settings ? json_decode($request->meeting_settings, true) : null,
                'send_notifications' => $request->boolean('send_notifications', true),
                'create_calendar_events' => $request->boolean('create_calendar_events', true),
                'auto_join_enabled' => $request->boolean('auto_join_enabled', false),
            ]);

            return redirect()->route('admin.oauth.index')
                ->with('success', 'OAuth settings updated successfully!');

        } catch (\Exception $e) {
            Log::error('OAuth settings update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update OAuth settings: ' . $e->getMessage());
        }
    }

    /**
     * Test OAuth connection using OAuth 2.0 (same as dashboard)
     */
    public function testConnection(Request $request)
    {
        try {
            // Check if user is authenticated with Google OAuth (same as dashboard)
            if (!session('google_access_token')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please connect to Google OAuth first',
                    'redirect' => route('google.oauth.redirect')
                ]);
            }

            // Test OAuth 2.0 connection by trying to access Google Calendar
            $googleOAuthController = app(\App\Http\Controllers\GoogleOAuthController::class);

            // Create a test client to verify OAuth connection
            $client = new \Google\Client();
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->setRedirectUri(config('services.google.redirect_uri'));
            $client->setScopes([
                \Google\Service\Calendar::CALENDAR,
                \Google\Service\Calendar::CALENDAR_EVENTS,
            ]);

            // Set the access token from session
            $client->setAccessToken(session('google_access_token'));

            // Refresh token if needed
            if ($client->isAccessTokenExpired()) {
                if ($client->getRefreshToken()) {
                    $client->refreshToken($client->getRefreshToken());
                    $newToken = $client->getAccessToken();
                    session(['google_access_token' => $newToken]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'OAuth token expired. Please reconnect.',
                        'redirect' => route('google.oauth.redirect')
                    ]);
                }
            }

            // Test OAuth 2.0 connection by checking token validity
            $tokenInfo = $client->getAccessToken();

            // Clear old service account sync status errors
            $oauthSettings = OAuthSetting::getGoogleMeetSettings();
            if ($oauthSettings && $oauthSettings->sync_status) {
                $oauthSettings->update([
                    'sync_status' => 'OAuth 2.0 Connected - Service account errors cleared'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'OAuth 2.0 connection successful',
                'token_valid' => !$client->isAccessTokenExpired(),
                'user_email' => Auth::user() ? Auth::user()->email : 'Unknown'
            ]);

        } catch (\Exception $e) {
            Log::error('OAuth 2.0 connection test failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'OAuth 2.0 connection test failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generate a test meeting using OAuth 2.0 (same as dashboard)
     */
    public function generateTestMeeting(Request $request)
    {
        try {
            // Check if user is authenticated with Google OAuth (same as dashboard)
            if (!session('google_access_token')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please connect to Google OAuth first',
                    'redirect' => route('google.oauth.redirect')
                ]);
            }

            // Use the same OAuth flow as dashboard
            $googleOAuthController = app(\App\Http\Controllers\GoogleOAuthController::class);
            $result = $googleOAuthController->createMeetRoom($request);

            if ($result->getData()->success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test meeting generated successfully using OAuth 2.0',
                    'meeting_link' => $result->getData()->meet_link
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result->getData()->message
                ]);
            }

        } catch (\Exception $e) {
            Log::error('OAuth test meeting generation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'OAuth test meeting generation failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Reset OAuth settings to defaults
     */
    public function reset(Request $request)
    {
        try {
            $oauthSettings = OAuthSetting::where('service_name', 'google_meet')->first();

            if ($oauthSettings) {
                $oauthSettings->updateSettings([
                    'credentials_json' => null,
                    'admin_email' => null,
                    'calendar_id' => 'primary',
                    'timezone' => 'Asia/Dhaka',
                    'meet_room_link' => null,
                    'is_active' => false,
                    'auto_generate_meetings' => true,
                    'meeting_start_time' => '09:00:00',
                    'meeting_end_time' => '17:00:00',
                    'meeting_duration_minutes' => 60,
                    'meeting_description' => null,
                    'send_notifications' => true,
                    'create_calendar_events' => true,
                    'auto_join_enabled' => false,
                ]);
            }

            return redirect()->route('admin.oauth.index')
                ->with('success', 'OAuth settings reset to defaults!');

        } catch (\Exception $e) {
            Log::error('OAuth settings reset failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to reset OAuth settings: ' . $e->getMessage());
        }
    }

    /**
     * Get current OAuth status
     */
    public function status()
    {
        try {
            $oauthSettings = OAuthSetting::getGoogleMeetSettings();

            if (!$oauthSettings) {
                return response()->json([
                    'success' => false,
                    'message' => 'No OAuth settings configured',
                    'status' => 'not_configured'
                ]);
            }

            return response()->json([
                'success' => true,
                'status' => $oauthSettings->is_active ? 'active' : 'inactive',
                'last_sync' => $oauthSettings->last_sync_at,
                'sync_status' => $oauthSettings->sync_status,
                'admin_email' => $oauthSettings->admin_email,
                'calendar_id' => $oauthSettings->calendar_id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get OAuth status: ' . $e->getMessage()
            ]);
        }
    }
}
