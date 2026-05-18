<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactFormSubmissionMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function build()
    {
        $subject = trim((string) ($this->payload['subject'] ?? 'New Contact Message'));
        $submittedAt = now();
        $fallbackBody = view('emails.contact-form-submission', [
            'payload' => $this->payload,
            'submittedAt' => $submittedAt,
        ])->render();

        $rendered = app(EmailTemplateService::class)->render(
            EmailTemplate::KEY_CONTACT_FORM_SUBMISSION,
            [
                'name' => $this->payload['name'] ?? '',
                'email' => $this->payload['email'] ?? '',
                'phone' => $this->payload['phone'] ?? 'N/A',
                'subject' => $subject,
                'message' => $this->payload['message'] ?? '',
                'submitted_at' => $submittedAt->format('M d, Y h:i A'),
            ],
            'Contact Form: ' . $subject,
            $fallbackBody
        );

        return $this->subject($rendered['subject'])
            ->replyTo((string) $this->payload['email'], (string) $this->payload['name'])
            ->html($rendered['body']);
    }
}
