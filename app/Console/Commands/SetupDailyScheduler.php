<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleMeetService;
use App\Models\Meeting;
use Carbon\Carbon;

class SetupDailyScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meet:setup-scheduler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up daily meeting scheduler and generate initial meetings';

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
        $this->info("🚀 Setting up daily meeting scheduler...");

        // Generate today's meeting
        $this->info("📅 Generating today's meeting...");
        $this->call('meet:generate-daily');

        // Generate tomorrow's meeting
        $this->info("📅 Generating tomorrow's meeting...");
        $this->call('meet:generate-tomorrow');

        // Generate meetings for the next 7 days
        $this->info("📅 Generating meetings for the next 7 days...");
        $this->call('meet:schedule-daily');

        $this->info("✅ Daily meeting scheduler setup complete!");
        $this->info("");
        $this->info("📋 To set up automatic daily generation, add this to your crontab:");
        $this->info("   0 8 * * * cd " . base_path() . " && php artisan meet:generate-daily");
        $this->info("");
        $this->info("🕘 This will generate a new meeting every day at 8:00 AM");

        return 0;
    }
}
