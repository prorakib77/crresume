<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\MailManager;

class MailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Override the default mail configuration to always use Atswfhresumes as sender name
        Mail::alwaysFrom(config('mail.from.address'), 'Atswfhresumes');
    }
}
