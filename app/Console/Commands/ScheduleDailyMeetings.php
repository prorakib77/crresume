<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleMeetService;
use App\Models\Meeting;
use Carbon\Carbon;

class ScheduleDailyMeetings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meet:schedule-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule daily meetings for the next 7 days';

    protected $googleMeetService;

    public function __construct(GoogleMeetService $googleMeetService)
    {
        parent::__construct();
        $this->googleMeetService = $googleMeetService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Scheduling daily meetings for the next 7 days...");

        $createdCount = 0;
        $skippedCount = 0;

        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::today()->addDays($i);

            try {
                // Check if meeting already exists for this date
                $existingMeeting = Meeting::where('date', $date->format('Y-m-d'))->first();

                if ($existingMeeting) {
                    $this->line("⏭️  Meeting already exists for {$date->format('Y-m-d')}");
                    $skippedCount++;
                    continue;
                }

                // Create new meeting
                $meeting = $this->googleMeetService->createDailyMeet($date);

                $this->line("✅ Created meeting for {$date->format('Y-m-d')}: {$meeting->meet_link}");
                $createdCount++;

            } catch (\Exception $e) {
                $this->error("❌ Failed to create meeting for {$date->format('Y-m-d')}: " . $e->getMessage());
            }
        }

        $this->info("📊 Summary:");
        $this->info("   Created: {$createdCount} meetings");
        $this->info("   Skipped: {$skippedCount} meetings (already exist)");

        return 0;
    }
}
