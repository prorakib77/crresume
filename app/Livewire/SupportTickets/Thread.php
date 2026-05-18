<?php

namespace App\Livewire\SupportTickets;

use App\Models\SupportTicket;
use App\Models\User;
use App\Services\SupportTicketService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Thread extends Component
{
    public SupportTicket $ticket;

    public string $message = '';

    public string $assignAgentId = '';

    public string $closeRequestNote = '';

    public int $lastSeenMessageId = 0;

    public function mount(SupportTicket $ticket): void
    {
        $this->service()->authorize($ticket, auth()->user());

        $this->ticket = $ticket;
        $this->assignAgentId = (string) ($ticket->agent_id ?? '');
        $this->lastSeenMessageId = (int) ($ticket->messages()->max('id') ?? 0);
    }

    public function refreshThread(): void
    {
        $freshLastMessageId = (int) ($this->ticket->messages()->max('id') ?? 0);

        if ($freshLastMessageId > $this->lastSeenMessageId) {
            $this->lastSeenMessageId = $freshLastMessageId;
            $this->dispatch('support-ticket-scroll');
        }
    }

    public function sendMessage(?string $body = null): void
    {
        $this->message = trim((string) ($body ?? $this->message));

        $this->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $message = $this->service()->sendMessage(auth()->user(), $this->ticket, $this->message);

        $this->message = '';
        $this->lastSeenMessageId = $message->id;
        $this->ticket->refresh();

        $this->dispatch('support-ticket-message-sent', messageId: $message->id);
        $this->dispatch('support-ticket-scroll');
    }

    public function saveAssignment(): void
    {
        if ($this->assignAgentId !== '') {
            $this->validate([
                'assignAgentId' => [
                    Rule::exists('users', 'id')->where(fn ($query) => $query->where('role_id', User::ROLE_AGENT)),
                ],
            ]);
        }

        $this->service()->assignAgent(auth()->user(), $this->ticket, $this->assignAgentId !== '' ? (int) $this->assignAgentId : null);

        $this->ticket->refresh();
        session()->flash('success', 'Agent assignment updated.');
    }

    public function requestClose(): void
    {
        $this->validate([
            'closeRequestNote' => ['nullable', 'string', 'max:500'],
        ]);

        $this->service()->requestClose(auth()->user(), $this->ticket, $this->closeRequestNote !== '' ? $this->closeRequestNote : null);

        $this->closeRequestNote = '';
        $this->ticket->refresh();
        session()->flash('success', 'Close request sent to the client.');
    }

    public function approveClose(): void
    {
        $this->service()->approveClose(auth()->user(), $this->ticket);
        $this->ticket->refresh();
        session()->flash('success', 'Ticket closed successfully.');
    }

    public function declineClose(): void
    {
        $this->service()->declineClose(auth()->user(), $this->ticket);
        $this->ticket->refresh();
        session()->flash('success', 'Close request declined.');
    }

    public function closeTicket(): void
    {
        $this->service()->close(auth()->user(), $this->ticket);
        $this->ticket->refresh();
        session()->flash('success', 'Ticket closed successfully.');
    }

    public function render()
    {
        $user = auth()->user();

        $ticket = SupportTicket::query()
            ->with(['client', 'agent', 'creator', 'closer', 'closeRequester'])
            ->findOrFail($this->ticket->id);

        $this->service()->authorize($ticket, $user);

        $messages = $ticket->messages()
            ->with('sender')
            ->orderBy('created_at')
            ->get()
            ->map(function ($message) use ($user) {
                return [
                    'id' => $message->id,
                    'body' => $message->message,
                    'display_name' => $this->service()->displaySenderName($message, $user),
                    'is_mine' => (int) $message->sender_id === (int) $user->id,
                    'time' => $message->created_at?->diffForHumans(),
                    'stamp' => $message->created_at?->format('M j, Y g:i A'),
                ];
            });

        return view('livewire.support-tickets.thread', [
            'ticket' => $ticket,
            'threadMessages' => $messages,
            'availableAgents' => $user->isAdmin()
                ? User::query()->where('role_id', User::ROLE_AGENT)->orderBy('name')->get()
                : collect(),
            'clientAlias' => SupportTicket::CLIENT_ALIAS,
            'isAdmin' => $user->isAdmin(),
            'isAgent' => $user->isAgent(),
            'isClient' => $user->isClient(),
        ]);
    }

    protected function service(): SupportTicketService
    {
        return app(SupportTicketService::class);
    }
}
