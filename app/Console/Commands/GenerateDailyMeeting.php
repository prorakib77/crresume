<?php

namespace App\Console\Commands;

use App\Models\EmailTemplate;
use App\Models\Meeting;
use App\Models\User;
use App\Services\EmailTemplateService;
use App\Services\GoogleMeetService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateDailyMeeting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meet:generate-daily {--force : Force generation even if meeting exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily Google Meet link for agent tracking';

    protected GoogleMeetService $googleMeetService;
    protected EmailTemplateService $emailTemplateService;

    /**
     * Create a new command instance.
     */
    public function __construct(GoogleMeetService $googleMeetService, EmailTemplateService $emailTemplateService)
    {
        parent::__construct();
        $this->googleMeetService = $googleMeetService;
        $this->emailTemplateService = $emailTemplateService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting daily meeting generation...');

        try {
            $date = Carbon::now();
            $this->info("Generating meeting for {$date->format('M d, Y')}.");

            $existingMeeting = Meeting::query()->whereDate('date', $date->format('Y-m-d'))->first();

            if ($existingMeeting && !$this->option('force')) {
                $this->warn("Meeting already exists for {$date->format('M d, Y')}.");
                $this->info("Meeting link: {$existingMeeting->meet_link}");
                $this->info('Use --force to regenerate.');

                return self::SUCCESS;
            }

            if ($existingMeeting && $this->option('force')) {
                $this->info("Deleting existing meeting for {$date->format('M d, Y')}...");
                $existingMeeting->delete();
            }

            $this->info('Creating new meeting...');
            $meeting = $this->googleMeetService->createDailyMeet($date);

            $this->info('Meeting created successfully.');
            $this->info("Date: {$meeting->date}");
            $this->info("Duration: {$meeting->getDurationInHours()} Hours ({$meeting->start_time->format('H:i')} - {$meeting->end_time->format('H:i')})");
            $this->info("Meet Link: {$meeting->meet_link}");

            $this->info('Sending notifications to agents...');
            $this->sendNotificationsToAgents($meeting);

            $this->info('Daily meeting generation completed successfully.');

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error('Failed to generate daily meeting: ' . $exception->getMessage());
            $this->error($exception->getTraceAsString());

            return self::FAILURE;
        }
    }

    /**
     * Send notifications to all agents.
     */
    protected function sendNotificationsToAgents(Meeting $meeting): void
    {
        try {
            $agents = User::query()
                ->whereHas('role', function ($query) {
                    $query->where('name', 'agent');
                })
                ->get();

            if ($agents->isEmpty()) {
                $this->warn('No agents found to notify.');

                return;
            }

            $this->info("Found {$agents->count()} agents to notify.");

            foreach ($agents as $agent) {
                try {
                    $fallbackBody = view('emails.daily-meeting-notification', [
                        'agent' => $agent,
                        'meeting' => $meeting,
                    ])->render();

                    $this->emailTemplateService->sendTemplate(
                        EmailTemplate::KEY_DAILY_MEETING_NOTIFICATION,
                        (string) $agent->email,
                        (string) $agent->name,
                        [
                            'agent_name' => $agent->name,
                            'meeting_date' => optional($meeting->date)->format('l, F j, Y'),
                            'meeting_date_short' => optional($meeting->date)->format('M d, Y'),
                            'meeting_time' => optional($meeting->start_time)->format('g:i A') . ' - ' . optional($meeting->end_time)->format('g:i A'),
                            'meeting_duration' => $meeting->getDurationInHours() . ' Hours',
                            'meeting_title' => $meeting->title,
                            'meeting_link' => $meeting->meet_link,
                        ],
                        [
                            'subject_fallback' => "Daily Agent Meeting - {$meeting->date->format('M d, Y')}",
                            'body_fallback' => $fallbackBody,
                        ]
                    );

                    $this->info("Notification sent to {$agent->name} ({$agent->email}).");
                } catch (\Throwable $exception) {
                    $this->error("Failed to send notification to {$agent->name}: {$exception->getMessage()}");
                }
            }

            $this->info('All notifications processed.');
        } catch (\Throwable $exception) {
            $this->error('Failed to send notifications: ' . $exception->getMessage());
        }
    }
}

