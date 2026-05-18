<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportTicketMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public SupportTicket $ticket;
    public SupportTicketMessage $message;
    public User $recipient;
    public string $senderDisplayName;
    public string $portalUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(SupportTicket $ticket, SupportTicketMessage $message, User $recipient, string $senderDisplayName)
    {
        $this->ticket = $ticket;
        $this->message = $message;
        $this->recipient = $recipient;
        $this->senderDisplayName = $senderDisplayName;
        $this->portalUrl = $this->resolvePortalUrl();
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $fallbackBody = view('emails.support-ticket-message', [
            'ticket' => $this->ticket,
            'messageModel' => $this->message,
            'recipient' => $this->recipient,
            'senderDisplayName' => $this->senderDisplayName,
            'portalUrl' => $this->portalUrl,
        ])->render();

        $rendered = app(EmailTemplateService::class)->render(
            EmailTemplate::KEY_SUPPORT_TICKET_MESSAGE,
            [
                'recipient_name' => $this->recipient->name,
                'sender_name' => $this->senderDisplayName,
                'ticket_reference' => $this->ticket->display_reference,
                'ticket_subject' => $this->ticket->subject,
                'ticket_status' => $this->ticket->status_label,
                'message' => nl2br(e($this->message->message)),
                'posted_at' => optional($this->message->created_at)->format('M d, Y h:i A'),
                'portal_url' => $this->portalUrl,
            ],
            'Support Ticket ' . $this->ticket->display_reference . ' - ' . $this->ticket->subject,
            $fallbackBody
        );

        return $this->from(config('mail.from.address'), $this->senderDisplayName)
            ->subject($rendered['subject'])
            ->html($rendered['body']);
    }

    /**
     * Resolve the portal URL based on the recipient role.
     */
    protected function resolvePortalUrl(): string
    {
        if ($this->recipient->isAdmin()) {
            return route('admin.support-tickets.show', $this->ticket);
        }

        if ($this->recipient->isAgent()) {
            return route('agent.support-tickets.show', $this->ticket);
        }

        return route('client.support-tickets.show', $this->ticket);
    }
}
