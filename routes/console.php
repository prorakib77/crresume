<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule daily meeting generation
Schedule::command('meet:generate-daily')
    ->dailyAt('08:00')
    ->timezone('Asia/Dhaka')
    ->description('Generate daily meeting link at 8:00 AM')
    ->withoutOverlapping();

// Schedule weekly meeting generation for the next 7 days
Schedule::command('meet:schedule-daily')
    ->weeklyOn(1, '08:30') // Every Monday at 8:30 AM
    ->timezone('Asia/Dhaka')
    ->description('Generate meetings for the next 7 days')
    ->withoutOverlapping();
