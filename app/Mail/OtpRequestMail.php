<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\OtpVerification;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public OtpVerification $otpVerification;

    /**
     * Create a new message instance.
     */
    public function __construct(OtpVerification $otpVerification)
    {
        $this->otpVerification = $otpVerification;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $otpVerification = $this->otpVerification->loadMissing('agent', 'client');
        $expiresInMinutes = 10;
        if ($otpVerification->created_at && $otpVerification->expires_at) {
            $calculatedMinutes = (int) ceil(
                $otpVerification->created_at->diffInSeconds($otpVerification->expires_at, false) / 60
            );
            if ($calculatedMinutes > 0) {
                $expiresInMinutes = (int) $calculatedMinutes;
            }
        }

        $fallbackBody = view('emails.otp-request', [
            'otpVerification' => $otpVerification,
            'expiresInMinutes' => $expiresInMinutes,
        ])->render();

        $rendered = app(EmailTemplateService::class)->render(
            EmailTemplate::KEY_OTP_REQUEST,
            [
                'client_name' => $otpVerification->client?->name ?? 'Client',
                'agent_name' => 'Team',
                'requested_at' => optional($otpVerification->created_at)->format('M d, Y h:i A'),
                'expires_at' => optional($otpVerification->expires_at)->format('M d, Y h:i A'),
                'expires_in_minutes' => (string) $expiresInMinutes,
                'request_message' => trim((string) ($otpVerification->message ?? '')) !== ''
                    ? (string) $otpVerification->message
                    : 'Please submit the verification code with the company name by clicking the link below.',
                'submit_url' => route('otp.submit.public', $otpVerification),
            ],
            'Verification Code Request - Action Required',
            $fallbackBody
        );

        return $this->from(config('mail.from.address'), 'Atswfhresumes')
            ->subject($rendered['subject'])
            ->html($rendered['body']);
    }
}
