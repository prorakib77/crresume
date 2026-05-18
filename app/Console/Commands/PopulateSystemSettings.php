<?php

namespace App\Console\Commands;

use App\Models\SystemSetting;
use Illuminate\Console\Command;

class PopulateSystemSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settings:populate {--fresh : Clear existing settings first}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate system settings with default values';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('fresh')) {
            $this->info('Clearing existing settings...');
            SystemSetting::truncate();
        }

        $this->info('Populating system settings...');

        SystemSetting::createDefaults();

        $count = SystemSetting::count();

        $this->info("Successfully populated {$count} system settings.");

        return 0;
    }
}
