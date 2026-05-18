<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\ConferenceData;
use Google\Service\Calendar\CreateConferenceRequest;
use Google\Service\Calendar\ConferenceSolutionKey;
use Carbon\Carbon;
use App\Models\Meeting;

class GoogleOAuthController extends Controller
{
    protected $client;
    protected $calendar;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect_uri'));
        $this->client->setScopes([
            Calendar::CALENDAR,
            Calendar::CALENDAR_EVENTS,
        ]);

        // Add access type and approval prompt for better OAuth handling
        $this->client->setAccessType('offline');
        $this->client->setApprovalPrompt('force');
        $this->client->setIncludeGrantedScopes(true);
    }

    /**
     * Redirect to Google OAuth
     */
    public function redirect()
    {
        $authUrl = $this->client->createAuthUrl();
        return redirect($authUrl);
    }

    /**
     * Handle Google OAuth callback
     */
    public function callback(Request $request)
    {
        try {
            $code = $request->get('code');

            if (!$code) {
                return redirect()->route('admin.dashboard')->with('error', 'Authorization failed. Please try again.');
            }

            // Exchange authorization code for access token
            $accessToken = $this->client->fetchAccessTokenWithAuthCode($code);

            if (isset($accessToken['error'])) {
                Log::error('OAuth error: ' . $accessToken['error']);
                return redirect()->route('admin.dashboard')->with('error', 'Authorization failed: ' . $accessToken['error']);
            }

            // Store the access token in session
            session(['google_access_token' => $accessToken]);

            // Set the access token for the client
            $this->client->setAccessToken($accessToken);

            // Initialize Calendar service
            $this->calendar = new Calendar($this->client);

            // Clear old service account sync status errors
            $oauthSettings = \App\Models\OAuthSetting::getGoogleMeetSettings();
            if ($oauthSettings && $oauthSettings->sync_status) {
                $oauthSettings->update([
                    'sync_status' => 'OAuth 2.0 Connected - Service account errors cleared'
                ]);
            }

            Log::info('Google OAuth successful for user: ' . Auth::user()->email);

            return redirect()->route('admin.dashboard')->with('success', 'Google OAuth connected successfully! You can now create Google Meet rooms.');

        } catch (\Exception $e) {
            Log::error('OAuth callback error: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'OAuth callback failed: ' . $e->getMessage());
        }
    }

    /**
     * Create a Google Meet room with OAuth
     */
    public function createMeetRoom(Request $request)
    {
        try {
            // Check if user is authenticated with Google
            if (!session('google_access_token')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please connect to Google first',
                    'redirect' => route('google.oauth.redirect')
                ]);
            }

            // Set access token
            $this->client->setAccessToken(session('google_access_token'));

            // Refresh token if needed
            if ($this->client->isAccessTokenExpired()) {
                $this->client->refreshToken($this->client->getRefreshToken());
                $newToken = $this->client->getAccessToken();
                session(['google_access_token' => $newToken]);
            }

            // Initialize Calendar service
            $this->calendar = new Calendar($this->client);

            // Create Google Meet room
            $meetRoom = $this->createGoogleMeetRoom();

            return response()->json([
                'success' => true,
                'meet_link' => $meetRoom['meet_link'],
                'meeting_id' => $meetRoom['meeting_id'],
                'message' => 'Google Meet room created successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create Google Meet room: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Google Meet room: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create Google Meet room with proper permissions
     */
    protected function createGoogleMeetRoom()
    {
        $date = Carbon::now();

        // Check if meeting already exists for today
        $existingMeeting = Meeting::whereDate('date', $date->format('Y-m-d'))->first();
        if ($existingMeeting) {
            Log::info("Meeting already exists for {$date->format('Y-m-d')}, updating with OAuth meeting");

            // Get all agents for the meeting
            $agents = \App\Models\User::where('role_id', 2)->get(); // Role ID 2 is agent
            $attendees = [];
            foreach ($agents as $agent) {
                $attendees[] = [
                    'email' => $agent->email,
                    'displayName' => $agent->name,
                    'responseStatus' => 'accepted'
                ];
            }

            // Update existing meeting with OAuth-generated link
            $event = new Event([
                'summary' => 'Daily Agent Meeting - ' . $date->format('M d, Y'),
                'description' => 'Daily agent tracking meeting for work progress monitoring. Open meeting - no permission required to join. Host: ' . Auth::user()->email . ' | All agents can join directly with their registered email.',
                'start' => new EventDateTime([
                    'dateTime' => $date->startOfDay()->toRfc3339String(),
                    'timeZone' => config('app.timezone', 'Asia/Dhaka'),
                ]),
                'end' => new EventDateTime([
                    'dateTime' => $date->endOfDay()->toRfc3339String(),
                    'timeZone' => config('app.timezone', 'Asia/Dhaka'),
                ]),
                'attendees' => $attendees,
                'visibility' => 'public',
                'guestsCanInviteOthers' => false,
                'guestsCanModify' => false,
                'guestsCanSeeOtherGuests' => true,
                'conferenceData' => new ConferenceData([
                    'createRequest' => new CreateConferenceRequest([
                        'conferenceSolutionKey' => new ConferenceSolutionKey([
                            'type' => 'hangoutsMeet'
                        ]),
                        'requestId' => 'oauth-agent-meeting-' . $date->format('Ymd') . '-' . uniqid()
                    ])
                ])
            ]);

            // Create the event in Google Calendar with specific settings
            $calendarId = 'primary';
            $createdEvent = $this->calendar->events->insert($calendarId, $event, [
                'conferenceDataVersion' => 1,
                'sendUpdates' => 'all' // Send updates to all attendees
            ]);

            // Extract the Google Meet link
            $meetLink = null;
            if ($createdEvent->getConferenceData() && $createdEvent->getConferenceData()->getEntryPoints()) {
                $meetLink = $createdEvent->getConferenceData()->getEntryPoints()[0]->getUri();
            }

            // If no Meet link from API, use a pre-configured room with proper settings
            if (!$meetLink) {
                $meetLink = env('GOOGLE_MEET_ROOM_LINK', 'https://meet.google.com/eav-xnab-doc');
                Log::info("Using pre-configured Google Meet room: {$meetLink}");
            }

            // Update existing meeting
            $existingMeeting->update([
                'meet_link' => $meetLink,
                'google_event_id' => $createdEvent->getId(),
                'title' => $createdEvent->getSummary(),
                'description' => $createdEvent->getDescription(),
                'start_time' => Carbon::parse($createdEvent->getStart()->getDateTime()),
                'end_time' => Carbon::parse($createdEvent->getEnd()->getDateTime()),
                'is_active' => true
            ]);

            Log::info("Existing meeting updated with OAuth Google Meet room: {$meetLink}");
            Log::info("Event ID: {$createdEvent->getId()}");
            Log::info("Host: " . Auth::user()->email);

            return [
                'meet_link' => $meetLink,
                'meeting_id' => $createdEvent->getId(),
                'meeting' => $existingMeeting
            ];
        }

        // Get all agents for the meeting
        $agents = \App\Models\User::where('role_id', 2)->get(); // Role ID 2 is agent
        $attendees = [];
        foreach ($agents as $agent) {
            $attendees[] = [
                'email' => $agent->email,
                'displayName' => $agent->name,
                'responseStatus' => 'accepted'
            ];
        }

        // Create new meeting if none exists
        $event = new Event([
            'summary' => 'Daily Agent Meeting - ' . $date->format('M d, Y'),
            'description' => 'Daily agent tracking meeting for work progress monitoring. Open meeting - no permission required to join. Host: ' . Auth::user()->email . ' | All agents can join directly with their registered email.',
            'start' => new EventDateTime([
                'dateTime' => $date->startOfDay()->toRfc3339String(),
                'timeZone' => config('app.timezone', 'Asia/Dhaka'),
            ]),
            'end' => new EventDateTime([
                'dateTime' => $date->endOfDay()->toRfc3339String(),
                'timeZone' => config('app.timezone', 'Asia/Dhaka'),
            ]),
            'attendees' => $attendees,
            'visibility' => 'public',
            'guestsCanInviteOthers' => false,
            'guestsCanModify' => false,
            'guestsCanSeeOtherGuests' => true,
            'conferenceData' => new ConferenceData([
                'createRequest' => new CreateConferenceRequest([
                    'conferenceSolutionKey' => new ConferenceSolutionKey([
                        'type' => 'hangoutsMeet'
                    ]),
                    'requestId' => 'oauth-agent-meeting-' . $date->format('Ymd') . '-' . uniqid()
                ])
            ])
        ]);

        // Create the event in Google Calendar with specific settings
        $calendarId = 'primary';
        $createdEvent = $this->calendar->events->insert($calendarId, $event, [
            'conferenceDataVersion' => 1,
            'sendUpdates' => 'all' // Send updates to all attendees
        ]);

        // Extract the Google Meet link
        $meetLink = null;
        if ($createdEvent->getConferenceData() && $createdEvent->getConferenceData()->getEntryPoints()) {
            $meetLink = $createdEvent->getConferenceData()->getEntryPoints()[0]->getUri();
        }

        // If no Meet link from API, use a pre-configured room with proper settings
        if (!$meetLink) {
            $meetLink = env('GOOGLE_MEET_ROOM_LINK', 'https://meet.google.com/eav-xnab-doc');
            Log::info("Using pre-configured Google Meet room: {$meetLink}");
        }

        // Save to database
        $meeting = Meeting::create([
            'date' => $date->format('Y-m-d'),
            'meet_link' => $meetLink,
            'google_event_id' => $createdEvent->getId(),
            'title' => $createdEvent->getSummary(),
            'description' => $createdEvent->getDescription(),
            'start_time' => Carbon::parse($createdEvent->getStart()->getDateTime()),
            'end_time' => Carbon::parse($createdEvent->getEnd()->getDateTime()),
            'is_active' => true
        ]);

        Log::info("Google Meet room created with OAuth: {$meetLink}");
        Log::info("Event ID: {$createdEvent->getId()}");
        Log::info("Host: " . Auth::user()->email);

        return [
            'meet_link' => $meetLink,
            'meeting_id' => $createdEvent->getId(),
            'meeting' => $meeting
        ];
    }

    /**
     * Disconnect Google OAuth
     */
    public function disconnect()
    {
        session()->forget('google_access_token');
        return redirect()->route('admin.dashboard')->with('success', 'Google OAuth disconnected successfully!');
    }
}
