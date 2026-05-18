<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\PaymentRequest;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public PaymentRequest $paymentRequest;

    /**
    * Create a new message instance.
    */
    public function __construct(PaymentRequest $paymentRequest)
    {
        $this->paymentRequest = $paymentRequest;
    }

    /**
    * Build the message.
    */
    public function build()
    {
        $amount = number_format((float) $this->paymentRequest->amount, 2);
        $clientName = $this->paymentRequest->client?->name ?? 'there';
        $statusLabel = $this->paymentRequest->getDisplayStatusLabel();
        $note = (string) ($this->paymentRequest->note ?? '');
        $loginUrl = route('login');

        $fallbackBody = view('emails.payment-request', [
            'paymentRequest' => $this->paymentRequest,
            'clientName' => $clientName,
            'amount' => $amount,
            'loginUrl' => $loginUrl,
        ])->render();

        $rendered = app(EmailTemplateService::class)->render(
            EmailTemplate::KEY_PAYMENT_REQUEST,
            [
                'client_name' => $clientName,
                'payment_reference' => $this->paymentRequest->display_reference,
                'amount' => $amount,
                'status' => $statusLabel,
                'note' => $note !== '' ? $note : 'No note provided',
                'login_url' => $loginUrl,
            ],
            "Payment Request {$this->paymentRequest->display_reference} - {$amount}",
            $fallbackBody
        );

        return $this->subject($rendered['subject'])
            ->html($rendered['body']);
    }
}
