<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MailchimpService;
use App\Models\User;
use App\Models\WorkUpdate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendDailyWorkUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workupdates:send-daily {--date= : Specific date to send updates for (Y-m-d format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily work updates to clients via email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::today();
        $mailchimpService = new MailchimpService();

        $this->info("Sending daily work updates for {$date->format('Y-m-d')}...");

        // Get all clients who have approved work updates for the specified date
        $clientsWithUpdates = User::whereHas('role', function($query) {
            $query->where('name', 'client');
        })->whereHas('clientWorkUpdates', function($query) use ($date) {
            $query->whereDate('applied_date', $date)
                  ->where('status', WorkUpdate::STATUS_APPROVED);
        })->with(['clientWorkUpdates' => function($query) use ($date) {
            $query->whereDate('applied_date', $date)
                  ->where('status', WorkUpdate::STATUS_APPROVED)
                  ->with('agent');
        }])->get();

        if ($clientsWithUpdates->isEmpty()) {
            $this->info('No clients with work updates found for this date.');
            return 0;
        }

        $successCount = 0;
        $failureCount = 0;

        foreach ($clientsWithUpdates as $client) {
            $workUpdates = $client->clientWorkUpdates->toArray();

            $this->info("Sending update to {$client->name} ({$client->email}) with " . count($workUpdates) . " work updates...");

            $result = $mailchimpService->sendDailyWorkUpdate($client, $workUpdates, $date);

            if ($result) {
                $successCount++;
                $this->info("✓ Successfully sent to {$client->email}");
            } else {
                $failureCount++;
                $this->error("✗ Failed to send to {$client->email}");
            }
        }

        $this->info("\nSummary:");
        $this->info("- Successfully sent: {$successCount}");
        $this->info("- Failed: {$failureCount}");
        $this->info("- Total clients: " . $clientsWithUpdates->count());

        Log::info('Daily work updates sending completed', [
            'date' => $date->toDateString(),
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'total_clients' => $clientsWithUpdates->count()
        ]);

        return 0;
    }
}
