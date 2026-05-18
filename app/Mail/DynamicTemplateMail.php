<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DynamicTemplateMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly string $dynamicSubject,
        private readonly string $dynamicBody,
        private readonly ?string $fromEmail = null,
        private readonly ?string $fromName = null,
        private readonly ?string $replyToEmail = null,
        private readonly ?string $replyToName = null
    ) {
    }

    public function build()
    {
        $mail = $this->subject($this->dynamicSubject);

        if (filled($this->fromEmail)) {
            $mail->from($this->fromEmail, $this->fromName ?: config('mail.from.name'));
        }

        if (filled($this->replyToEmail)) {
            $mail->replyTo($this->replyToEmail, $this->replyToName);
        }

        return $mail->html($this->dynamicBody);
    }
}
