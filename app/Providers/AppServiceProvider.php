<?php

namespace App\Providers;

use App\Models\EmailTemplate;
use App\Services\EmailTemplateService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Force correct base URL for route generation (avoids localhost redirects in prod)
        $appUrl = config('app.url');

        // If APP_URL is not set correctly, fall back to the current host (web only)
        if (
            (!$appUrl || str_contains($appUrl, 'localhost') || str_contains($appUrl, '127.0.0.1')) &&
            !app()->runningInConsole()
        ) {
            $currentHost = request()->getSchemeAndHttpHost();
            if ($currentHost) {
                $appUrl = $currentHost;
            }
        }

        if ($appUrl) {
            URL::forceRootUrl($appUrl);

            // Force HTTPS when the configured URL uses https
            if (str_starts_with($appUrl, 'https://')) {
                URL::forceScheme('https');
            }
        }

        VerifyEmail::toMailUsing(function (object $notifiable, string $verificationUrl) {
            $service = app(EmailTemplateService::class);
            $expiresInMinutes = (int) config('auth.verification.expire', 60);

            $fallbackBody = view('emails.email-verification', [
                'user' => $notifiable,
                'verificationUrl' => $verificationUrl,
            ])->render();

            $rendered = $service->render(
                EmailTemplate::KEY_EMAIL_VERIFICATION,
                [
                    'user_name' => data_get($notifiable, 'name', 'User'),
                    'user_email' => data_get($notifiable, 'email', ''),
                    'verification_url' => $verificationUrl,
                    'verification_expires_at' => now()->addMinutes($expiresInMinutes)->format('M d, Y h:i A'),
                ],
                'Verify Your Email Address',
                $fallbackBody
            );

            return (new MailMessage())
                ->subject($rendered['subject'])
                ->view('emails.dynamic-template', ['html' => $rendered['body']]);
        });

        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $email = method_exists($notifiable, 'getEmailForPasswordReset')
                ? $notifiable->getEmailForPasswordReset()
                : data_get($notifiable, 'email', '');

            $resetUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $email,
            ], false));

            $passwordsKey = (string) Config::get('auth.defaults.passwords', 'users');
            $expiresInMinutes = (int) Config::get("auth.passwords.{$passwordsKey}.expire", 60);
            $service = app(EmailTemplateService::class);

            $fallbackBody = view('emails.password-reset', [
                'user' => $notifiable,
                'resetUrl' => $resetUrl,
                'request' => request(),
            ])->render();

            $rendered = $service->render(
                EmailTemplate::KEY_PASSWORD_RESET,
                [
                    'user_name' => data_get($notifiable, 'name', 'User'),
                    'user_email' => $email,
                    'reset_url' => $resetUrl,
                    'reset_expires_minutes' => $expiresInMinutes,
                ],
                'Reset Your Password',
                $fallbackBody
            );

            return (new MailMessage())
                ->subject($rendered['subject'])
                ->view('emails.dynamic-template', ['html' => $rendered['body']]);
        });
    }
}
