<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\OtpSubmission;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpSubmissionNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public OtpSubmission $otpSubmission;

    /**
     * Create a new message instance.
     */
    public function __construct(OtpSubmission $otpSubmission)
    {
        $this->otpSubmission = $otpSubmission;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $otpSubmission = $this->otpSubmission->loadMissing('agent', 'client');

        $fallbackBody = view('emails.otp-submission-notification', [
            'otpSubmission' => $otpSubmission,
        ])->render();

        $rendered = app(EmailTemplateService::class)->render(
            EmailTemplate::KEY_OTP_SUBMISSION_NOTIFICATION,
            [
                'agent_name' => $otpSubmission->agent?->name ?? 'Agent',
                'client_name' => $otpSubmission->client?->name ?? 'Client',
                'client_email' => $otpSubmission->client?->email ?? '',
                'company_name' => $otpSubmission->company_name,
                'otp_code' => $otpSubmission->otp_code,
                'status' => $otpSubmission->getStatusLabel(),
                'submitted_at' => optional($otpSubmission->submitted_at)->format('M d, Y h:i A'),
                'submissions_url' => route('agent.submissions.index'),
            ],
            'Your client submitted a verification code - ' . $otpSubmission->company_name,
            $fallbackBody
        );

        return $this->from(config('mail.from.address'), 'Atswfhresumes')
            ->subject($rendered['subject'])
            ->html($rendered['body']);
    }
}
