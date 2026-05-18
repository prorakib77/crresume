<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserCommunicationService
{
    public function __construct(
        protected EmailTemplateService $emailTemplateService,
        protected NotificationService $notificationService,
    ) {
    }

    public function notify(
        User $user,
        string $title,
        string $message,
        string $type = Notification::TYPE_INFO,
        array $data = [],
        string $priority = Notification::PRIORITY_NORMAL,
        ?Model $notifiable = null,
        ?string $actionUrl = null
    ): Notification {
        return $this->notificationService->notify(
            $user,
            $title,
            $message,
            $type,
            $data,
            $priority,
            $notifiable,
            $actionUrl
        );
    }

    public function sendStructuredEmail(
        User $user,
        string $subject,
        string $headline,
        array $paragraphs = [],
        ?string $actionUrl = null,
        ?string $actionLabel = null,
        ?string $replyToEmail = null,
        ?string $replyToName = null
    ): bool {
        if (blank($user->email)) {
            return false;
        }

        $body = [];
        $body[] = '<p>Hello ' . e($user->name ?: 'there') . ',</p>';
        $body[] = '<p><strong>' . e($headline) . '</strong></p>';

        foreach ($paragraphs as $paragraph) {
            $text = trim((string) $paragraph);

            if ($text === '') {
                continue;
            }

            $body[] = '<p>' . nl2br(e($text)) . '</p>';
        }

        if (filled($actionUrl) && filled($actionLabel)) {
            $body[] = '<p><a href="' . e((string) $actionUrl) . '">' . e((string) $actionLabel) . '</a></p>';
        }

        return $this->dispatchSystemUpdateEmail(
            $user,
            $subject,
            implode('', $body),
            $replyToEmail,
            $replyToName
        );
    }

    public function sendCustomEmail(
        User $user,
        string $subject,
        string $body,
        bool $isHtml = false,
        ?string $replyToEmail = null,
        ?string $replyToName = null
    ): bool {
        if (blank($user->email)) {
            return false;
        }

        $normalizedBody = trim($body);

        if (!$isHtml) {
            $normalizedBody = '<p>' . nl2br(e($normalizedBody)) . '</p>';
        } elseif (!Str::contains($normalizedBody, '<')) {
            $normalizedBody = '<p>' . nl2br(e($normalizedBody)) . '</p>';
        }

        return $this->dispatchSystemUpdateEmail(
            $user,
            $subject,
            $normalizedBody,
            $replyToEmail,
            $replyToName
        );
    }

    public function sendClientWelcomeEmail(User $user): bool
    {
        if (blank($user->email)) {
            return false;
        }

        try {
            $this->emailTemplateService->sendTemplate(
                EmailTemplate::KEY_CLIENT_WELCOME,
                (string) $user->email,
                (string) $user->name,
                [
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'dashboard_url' => route('client.dashboard'),
                    'onboarding_url' => route('client.onboarding.create'),
                ]
            );

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Failed to send client welcome email.', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'error' => $exception->getMessage(),
            ]);

            report($exception);

            return false;
        }
    }

    public function sendOnboardingConfirmationEmail(User $user): bool
    {
        if (blank($user->email)) {
            return false;
        }

        try {
            $this->emailTemplateService->sendTemplate(
                EmailTemplate::KEY_ONBOARDING_SUBMISSION_CONFIRMATION,
                (string) $user->email,
                (string) $user->name,
                [
                    'user_name' => $user->name,
                    'dashboard_url' => route('client.dashboard'),
                    'support_tickets_url' => route('client.support-tickets.create'),
                ]
            );

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Failed to send onboarding confirmation email.', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'error' => $exception->getMessage(),
            ]);

            report($exception);

            return false;
        }
    }

    private function dispatchSystemUpdateEmail(
        User $user,
        string $subject,
        string $body,
        ?string $replyToEmail = null,
        ?string $replyToName = null
    ): bool {
        try {
            $this->emailTemplateService->sendTemplate(
                'system_update',
                (string) $user->email,
                (string) $user->name,
                [],
                [
                    'subject_fallback' => $subject,
                    'body_fallback' => $body,
                    'reply_to_email' => $replyToEmail,
                    'reply_to_name' => $replyToName,
                ]
            );

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Failed to send user communication email.', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'subject' => $subject,
                'error' => $exception->getMessage(),
            ]);

            report($exception);

            return false;
        }
    }
}
