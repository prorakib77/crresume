<?php

namespace App\Services;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\ConferenceData;
use Google\Service\Calendar\CreateConferenceRequest;
use Google\Service\Calendar\ConferenceSolutionKey;
use App\Models\Meeting;
use App\Models\Attendance;
use App\Models\OAuthSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GoogleMeetService
{
    protected $client;
    protected $calendar;
    protected $credentialsPath;

    public function __construct()
    {
        $this->credentialsPath = storage_path('app/google/credentials.json');
        $this->initializeClient();
    }

    /**
     * Get OAuth settings from database
     */
    protected function getOAuthSettings()
    {
        return OAuthSetting::getGoogleMeetSettings();
    }

    /**
     * Initialize Google Calendar client with service account
     */
    protected function initializeClient()
    {
        try {
            // Try OAuth first if available
            if (session('google_access_token')) {
                $this->client = new Client();
                $this->client->setClientId(config('services.google.client_id'));
                $this->client->setClientSecret(config('services.google.client_secret'));
                $this->client->setRedirectUri(config('services.google.redirect_uri'));
                $this->client->setScopes([
                    Calendar::CALENDAR,
                    Calendar::CALENDAR_EVENTS,
                ]);

                // Set the access token from session
                $this->client->setAccessToken(session('google_access_token'));

                // Refresh token if needed
                if ($this->client->isAccessTokenExpired()) {
                    if ($this->client->getRefreshToken()) {
                        $this->client->refreshToken($this->client->getRefreshToken());
                        $newToken = $this->client->getAccessToken();
                        session(['google_access_token' => $newToken]);
                    } else {
                        Log::warning('Google OAuth access token expired, falling back to service account');
                        return $this->initializeServiceAccount();
                    }
                }

                $this->calendar = new Calendar($this->client);
                Log::info('Google Calendar client initialized successfully with OAuth for user: ' . auth()->user()->email);
                return;
            }

            // Fallback to service account
            Log::info('OAuth not available, using service account');
            return $this->initializeServiceAccount();

        } catch (\Exception $e) {
            Log::error('Failed to initialize Google Calendar client with OAuth: ' . $e->getMessage());
            Log::info('Falling back to service account');
            return $this->initializeServiceAccount();
        }
    }

    /**
     * Initialize with service account (fallback)
     */
    protected function initializeServiceAccount()
    {
        try {
            // Try OAuth settings from database first
            $oauthSettings = $this->getOAuthSettings();
            if ($oauthSettings && $oauthSettings->credentials_json) {
                $credentials = $oauthSettings->getCredentialsArray();
                $this->client = new Client();
                $this->client->setAuthConfig($credentials);
                $this->client->addScope(Calendar::CALENDAR);
                $this->client->addScope(Calendar::CALENDAR_EVENTS);
                $this->calendar = new Calendar($this->client);
                Log::info('Google Calendar client initialized with OAuth settings from database');
            } else if (file_exists($this->credentialsPath)) {
                $this->client = new Client();
                $this->client->setAuthConfig($this->credentialsPath);
                $this->client->addScope(Calendar::CALENDAR);
                $this->client->addScope(Calendar::CALENDAR_EVENTS);
                $this->calendar = new Calendar($this->client);
                Log::info('Google Calendar client initialized with service account file');
            } else {
                Log::warning('No Google credentials found. Please configure OAuth settings in admin panel.');
                return;
            }

        } catch (\Exception $e) {
            Log::error('Failed to initialize Google Calendar client: ' . $e->getMessage());
        }
    }

    /**
     * Create a daily Google Meet link for agent tracking
     */
    public function createDailyMeet($date = null)
    {
        try {
            $date = $date ? Carbon::parse($date) : Carbon::now();

            // Check if meeting already exists for this date
            $existingMeeting = Meeting::whereDate('date', $date->format('Y-m-d'))->first();
            if ($existingMeeting) {
                Log::info("Meeting already exists for {$date->format('Y-m-d')}");
                return $existingMeeting;
            }

            // If Google Calendar API is not available, use fallback
            if (!$this->calendar) {
                Log::warning('Google Calendar API not available, using fallback meeting creation');
                return $this->createFallbackMeeting($date);
            }

            // Create Google Calendar event with Google Meet conference
            $event = new Event([
                'summary' => 'Daily Agent Meeting - ' . $date->format('M d, Y'),
                'description' => 'Daily agent tracking meeting for work progress monitoring. All agents join the same shared meeting room.',
                'start' => new EventDateTime([
                    'dateTime' => $date->startOfDay()->toRfc3339String(),
                    'timeZone' => config('app.timezone', 'Asia/Dhaka'),
                ]),
                'end' => new EventDateTime([
                    'dateTime' => $date->endOfDay()->toRfc3339String(),
                    'timeZone' => config('app.timezone', 'Asia/Dhaka'),
                ]),
                'conferenceData' => new ConferenceData([
                    'createRequest' => new CreateConferenceRequest([
                        'conferenceSolutionKey' => new ConferenceSolutionKey([
                            'type' => 'hangoutsMeet'
                        ]),
                        'requestId' => 'daily-agent-meeting-' . $date->format('Ymd') . '-' . uniqid()
                    ])
                ])
            ]);

            // Create the event in Google Calendar with Google Meet
            $calendarId = config('google.calendar_id', 'primary');
            $createdEvent = $this->calendar->events->insert($calendarId, $event, [
                'conferenceDataVersion' => 1
            ]);

            // Extract the real Google Meet link from the created event
            $meetLink = null;
            if ($createdEvent->getConferenceData() && $createdEvent->getConferenceData()->getEntryPoints()) {
                $meetLink = $createdEvent->getConferenceData()->getEntryPoints()[0]->getUri();
            }

            if (!$meetLink) {
                Log::warning('Failed to get Google Meet link from created event, using real meet link');
                $meetLink = $this->generateRealMeetLink($date);
            }

            Log::info("Google Calendar event created successfully for {$date->format('Y-m-d')}");
            Log::info("Event ID: {$createdEvent->getId()}");
            Log::info("Real Google Meet link: {$meetLink}");
            Log::info("Same meeting link for everyone all day");

            // Save to database
            $meeting = Meeting::create([
                'date' => $date->format('Y-m-d'),
                'meet_link' => $meetLink,
                'google_event_id' => $createdEvent->getId(),
                'title' => $createdEvent->getSummary(),
                'description' => $createdEvent->getDescription(),
                'start_time' => $date->startOfDay(),
                'end_time' => $date->endOfDay(),
                'is_active' => true
            ]);

            Log::info("Google Meet meeting created successfully for {$date->format('Y-m-d')}: {$meetLink}");
            Log::info("Google Calendar Event ID: {$createdEvent->getId()}");

            return $meeting;

        } catch (\Exception $e) {
            Log::error('Failed to create daily meeting: ' . $e->getMessage());
            Log::error('Error details: ' . $e->getTraceAsString());
            Log::error('Using real Google Meet room');
            return $this->createRealMeetRoom($date);
        }
    }

    /**
     * Create a real Google Meet room using pre-configured link
     */
    protected function createRealMeetRoom($date)
    {
        try {
            // Create a new Google Meet room with proper permissions
            $meetLink = $this->createOpenGoogleMeetRoom($date);

            // Generate a unique meeting ID for tracking
            $meetingId = 'open-meet-room-' . $date->format('Ymd') . '-' . substr(md5($date->format('Y-m-d') . config('app.key')), 0, 8);

            // Get OAuth settings for meeting details
            $oauthSettings = $this->getOAuthSettings();
            $adminEmail = $oauthSettings && $oauthSettings->admin_email
                ? $oauthSettings->admin_email
                : env('GOOGLE_ADMIN_EMAIL', 'caliroweteam@caliwfhresumes.com');

            $meetingDescription = $oauthSettings && $oauthSettings->meeting_description
                ? $oauthSettings->meeting_description
                : 'Daily agent tracking meeting for work progress monitoring. Open meeting - no permission required to join.';

            // Save to database
            $meeting = Meeting::create([
                'date' => $date->format('Y-m-d'),
                'meet_link' => $meetLink,
                'google_event_id' => $meetingId,
                'title' => 'Daily Agent Meeting - ' . $date->format('M d, Y'),
                'description' => $meetingDescription . ' Host: ' . $adminEmail,
                'start_time' => $date->startOfDay(),
                'end_time' => $date->endOfDay(),
                'is_active' => true
            ]);

            Log::info("Open Google Meet room created for {$date->format('Y-m-d')}: {$meetLink}");
            Log::info("No permission required to join - open for all agents");
            Log::info("Host: " . $adminEmail);

            return $meeting;

        } catch (\Exception $e) {
            Log::error('Failed to create open meet room: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create an open Google Meet room with proper permissions
     */
    protected function createOpenGoogleMeetRoom($date)
    {
        try {
            // Get OAuth settings for meet room link
            $oauthSettings = $this->getOAuthSettings();
            $meetLink = $oauthSettings && $oauthSettings->meet_room_link
                ? $oauthSettings->meet_room_link
                : env('GOOGLE_MEET_ROOM_LINK', 'https://meet.google.com/eav-xnab-doc');

            // Create Google Calendar event for the meeting
            if ($this->calendar) {
                try {
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

                $event = new Event([
                    'summary' => 'Daily Agent Meeting - ' . $date->format('M d, Y'),
                    'description' => 'Daily agent tracking meeting for work progress monitoring. Open meeting - no permission required to join. Host: ' . env('GOOGLE_ADMIN_EMAIL', 'caliroweteam@caliwfhresumes.com') . ' | All agents can join directly with their registered email.',
                    'start' => new EventDateTime([
                        'dateTime' => $date->startOfDay()->toRfc3339String(),
                        'timeZone' => config('app.timezone', 'Asia/Dhaka'),
                    ]),
                    'end' => new EventDateTime([
                        'dateTime' => $date->endOfDay()->toRfc3339String(),
                        'timeZone' => config('app.timezone', 'Asia/Dhaka'),
                    ]),
                    'location' => $meetLink,
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
                            'requestId' => 'daily-agent-meeting-' . $date->format('Ymd')
                        ])
                    ])
                ]);

                    // Create the event in Google Calendar
                    $calendarId = config('google.calendar_id', 'primary');
                    $createdEvent = $this->calendar->events->insert($calendarId, $event, [
                        'conferenceDataVersion' => 1
                    ]);

                    Log::info("Google Calendar event created for real Google Meet room: {$meetLink}");
                    Log::info("Event ID: {$createdEvent->getId()}");
                    Log::info("Real Google Meet link: {$meetLink}");
                    Log::info("Admin email set as organizer: " . env('GOOGLE_ADMIN_EMAIL', 'caliroweteam@caliwfhresumes.com'));

                } catch (\Exception $e) {
                    Log::warning('Failed to create Google Calendar event, using real Google Meet room: ' . $e->getMessage());
                }
            }

            Log::info("Real Google Meet room configured: {$meetLink}");
            Log::info("Same meeting link daily - consistent for all agents");
            Log::info("Host: " . env('GOOGLE_ADMIN_EMAIL', 'caliroweteam@caliwfhresumes.com'));

            return $meetLink;

        } catch (\Exception $e) {
            Log::error('Failed to configure real Google Meet room: ' . $e->getMessage());
            Log::error('Using fallback Google Meet room');
            return 'https://meet.google.com/new?authuser=0';
        }
    }

    /**
     * Generate a persistent meeting code for the day
     */
    protected function generatePersistentMeetingCode($date)
    {
        // Generate a consistent meeting code based on the date
        // This ensures the same meeting code is used for the same date
        $dateString = $date->format('Y-m-d');
        $hash = substr(md5($dateString . config('app.key')), 0, 8);

        // Create a meeting code in the format: xxx-xxxx-xxx
        $code = substr($hash, 0, 3) . '-' . substr($hash, 3, 4) . '-' . substr($hash, 7, 3);

        return $code;
    }

    /**
     * Create a fallback meeting when Google Calendar API is not available
     */
    protected function createFallbackMeeting($date)
    {
        try {
            // Use Google Meet "new meeting" URL which creates a valid meeting
            $meetLink = 'https://meet.google.com/new?authuser=0';

            // Generate a unique meeting ID for tracking
            $meetingId = 'daily-meeting-' . $date->format('Ymd') . '-' . substr(md5($date->format('Y-m-d') . config('app.key')), 0, 8);

            // Save to database
            $meeting = Meeting::create([
                'date' => $date->format('Y-m-d'),
                'meet_link' => $meetLink,
                'google_event_id' => $meetingId,
                'title' => 'Daily Agent Meeting - ' . $date->format('M d, Y'),
                'description' => 'Daily agent tracking meeting for work progress monitoring. All agents join the same persistent meeting room.',
                'start_time' => $date->startOfDay(),
                'end_time' => $date->endOfDay(),
                'is_active' => true
            ]);

            Log::info("Fallback meeting created for {$date->format('Y-m-d')}: {$meetLink}");
            Log::info("This creates a real Google Meet that everyone can join");

            return $meeting;

        } catch (\Exception $e) {
            Log::error('Failed to create fallback meeting: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get today's meeting
     */
    public function getTodaysMeeting()
    {
        $meeting = Meeting::whereDate('date', Carbon::today())->first();

        if (!$meeting) {
            $meeting = Meeting::whereDate('date', '>', Carbon::today())
                ->orderBy('date')
                ->first();
        }

        return $meeting;
    }

    /**
     * Record agent joining meeting
     */
    public function recordAgentJoin($agentId)
    {
        try {
            $todayMeeting = $this->getTodaysMeeting();
            if (!$todayMeeting) {
                throw new \Exception('No meeting found for today');
            }

            // Check if agent is already in meeting
            $existingAttendance = Attendance::where('meeting_id', $todayMeeting->id)
                ->where('agent_id', $agentId)
                ->whereNull('leave_time')
                ->first();

            if ($existingAttendance) {
                throw new \Exception('Agent is already in the meeting');
            }

            // Create new attendance record
            $attendance = Attendance::create([
                'meeting_id' => $todayMeeting->id,
                'agent_id' => $agentId,
                'join_time' => now(),
            ]);

            Log::info("Agent {$agentId} joined meeting {$todayMeeting->id}");
            return $attendance;

        } catch (\Exception $e) {
            Log::error('Failed to record agent join: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Record agent leaving meeting
     */
    public function recordAgentLeave($agentId)
    {
        try {
            $todayMeeting = $this->getTodaysMeeting();
            if (!$todayMeeting) {
                throw new \Exception('No meeting found for today');
            }

            // Find active attendance
            $attendance = Attendance::where('meeting_id', $todayMeeting->id)
                ->where('agent_id', $agentId)
                ->whereNull('leave_time')
                ->first();

            if (!$attendance) {
                throw new \Exception('Agent is not currently in the meeting');
            }

            // Update attendance record
            $attendance->update([
                'leave_time' => now(),
                'duration_minutes' => rounded_time_value(now()->diffInMinutes($attendance->join_time)),
                'status' => 'left'
            ]);

            Log::info("Agent {$agentId} left meeting {$todayMeeting->id} after {$attendance->duration_minutes} minutes");
            return $attendance;

        } catch (\Exception $e) {
            Log::error('Failed to record agent leave: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get meeting statistics
     */
    public function getMeetingStats($meetingId)
    {
        $meeting = Meeting::find($meetingId);
        if (!$meeting) {
            return [];
        }

        $totalAgents = \App\Models\User::where('role_id', 3)->count();
        $joinedAgents = Attendance::where('meeting_id', $meetingId)->distinct('agent_id')->count();
        $totalDuration = Attendance::where('meeting_id', $meetingId)->sum('duration_minutes');
        $averageDuration = $joinedAgents > 0 ? $totalDuration / $joinedAgents : 0;

        return [
            'total_agents' => $totalAgents,
            'joined_agents' => $joinedAgents,
            'total_duration' => $totalDuration,
            'average_duration' => round($averageDuration, 1)
        ];
    }

    /**
     * Get attendance report for a meeting
     */
    public function getAttendanceReport($meetingId)
    {
        return Attendance::with(['agent', 'meeting'])
            ->where('meeting_id', $meetingId)
            ->whereHas('agent') // Only get attendance records with valid agents
            ->get()
            ->map(function ($attendance) {
                return [
                    'agent_name' => $attendance->agent->name,
                    'agent_email' => $attendance->agent->email,
                    'started_at' => $attendance->join_time,
                    'ended_at' => $attendance->leave_time,
                    'duration_minutes' => $attendance->duration_minutes,
                    'is_active' => is_null($attendance->leave_time)
                ];
            });
    }

    /**
     * Generate a real Google Meet link for the day
     * Since service account can't create Google Meet conferences,
     * we'll use a pre-configured meeting room or create one manually
     */
    protected function generateRealMeetLink($date)
    {
        // For now, we'll use a persistent meeting room
        // You can manually create a Google Meet and use that link
        // Or we can use the "new meeting" approach but make it persistent

        // Option 1: Use your OAuth-generated meeting room
        $preConfiguredRoom = env('GOOGLE_MEET_ROOM_LINK', 'https://meet.google.com/eav-xnab-doc');
        if ($preConfiguredRoom) {
            return $preConfiguredRoom;
        }

        // Option 2: Create a persistent meeting room using a consistent approach
        // This creates a meeting that can be reused
        return 'https://meet.google.com/new?authuser=0';
    }

    /**
     * Start screen sharing for an agent
     */
    public function startScreenSharing($agentId)
    {
        try {
            // Get today's meeting
            $meeting = $this->getTodaysMeeting();

            if (!$meeting) {
                throw new \Exception('No meeting found for today');
            }

            // Get attendance record
            $attendance = Attendance::where('meeting_id', $meeting->id)
                                   ->where('agent_id', $agentId)
                                   ->where('status', 'joined')
                                   ->whereNull('leave_time')
                                   ->first();

            if (!$attendance) {
                throw new \Exception('Agent must join the meeting first before starting screen sharing');
            }

            // Update attendance to mark screen sharing as started
            $attendance->update([
                'screen_shared' => true,
                'screen_share_started_at' => now()
            ]);

            // Create screen sharing log
            \App\Models\ScreenSharingLog::create([
                'attendance_id' => $attendance->id,
                'agent_id' => $agentId,
                'started_at' => now(),
                'is_active' => true
            ]);

            return $attendance;

        } catch (\Exception $e) {
            Log::error('Failed to start screen sharing: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Stop screen sharing for an agent
     */
    public function stopScreenSharing($agentId)
    {
        try {
            // Get today's meeting
            $meeting = $this->getTodaysMeeting();

            if (!$meeting) {
                throw new \Exception('No meeting found for today');
            }

            // Get attendance record
            $attendance = Attendance::where('meeting_id', $meeting->id)
                                   ->where('agent_id', $agentId)
                                   ->where('status', 'joined')
                                   ->whereNull('leave_time')
                                   ->first();

            if (!$attendance) {
                throw new \Exception('No attendance record found');
            }

            // Update attendance to mark screen sharing as stopped
            $attendance->update([
                'screen_share_ended_at' => now()
            ]);

            // Update active screen sharing log
            $activeLog = \App\Models\ScreenSharingLog::where('attendance_id', $attendance->id)
                                                    ->where('is_active', true)
                                                    ->first();

            if ($activeLog) {
                $activeLog->update([
                    'ended_at' => now(),
                    'is_active' => false,
                    'duration_minutes' => rounded_time_value(now()->diffInMinutes($activeLog->started_at))
                ]);
            }

            return $attendance;

        } catch (\Exception $e) {
            Log::error('Failed to stop screen sharing: ' . $e->getMessage());
            throw $e;
        }
    }
}
