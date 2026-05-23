<?php

namespace App\Services;

use App\Mail\SupportTicketMessageMail;
use App\Models\AgentClientAssignment;
use App\Models\Notification;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SupportTicketService
{
    public function queryFor(User $user): Builder
    {
        $query = SupportTicket::query();

        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isAgent()) {
            return $this->applyAssignedAgentFilter($query, (int) $user->getKey());
        }

        $clientProfileId = $user->clientProfile?->id;

        return $query->where(function (Builder $ticketQuery) use ($user, $clientProfileId) {
            $ticketQuery->where('client_id', $user->id);

            if ($clientProfileId) {
                $ticketQuery->orWhere('client_id', $clientProfileId);
            }
        });
    }

    public function authorize(SupportTicket $ticket, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($user->isClient() && $this->clientOwnsTicket($ticket, $user)) {
            return;
        }

        if ($user->isAgent() && $this->canAgentAccessTicket($ticket, $user)) {
            return;
        }

        Log::warning('Unauthorized support ticket access', [
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
        ]);

        abort(403);
    }

    public function createTicket(User $actor, array $attributes): SupportTicket
    {
        if (!$actor->isAdmin() && !$actor->isClient()) {
            abort(403);
        }

        $client = $actor;
        $agent = null;

        if ($actor->isAdmin()) {
            $client = User::query()
                ->whereKey($attributes['client_id'] ?? null)
                ->where('role_id', User::ROLE_CLIENT)
                ->firstOrFail();

            if (filled($attributes['agent_id'] ?? null)) {
                $agent = User::query()
                    ->whereKey($attributes['agent_id'])
                    ->where('role_id', User::ROLE_AGENT)
                    ->firstOrFail();
            }
        } else {
            $agent = $actor->active_agents->first();
        }

        $ticket = SupportTicket::create([
            'client_id' => $client->id,
            'agent_id' => $agent?->id,
            'created_by' => $actor->id,
            'subject' => trim((string) $attributes['subject']),
            'status' => SupportTicket::STATUS_OPEN,
            'last_message_at' => now(),
        ]);

        $message = $ticket->messages()->create([
            'sender_id' => $actor->id,
            'message' => $this->normalizeMessageBody((string) $attributes['message']),
        ]);

        $this->sendMessageEmails($ticket->fresh(['client', 'agent']), $message->fresh('sender'), $actor);

        return $ticket->fresh(['client', 'agent', 'latestMessage.sender']);
    }

    public function sendMessage(User $actor, SupportTicket $ticket, string $messageBody): SupportTicketMessage
    {
        $this->authorize($ticket, $actor);

        if ($ticket->isClosed()) {
            abort(422, 'This ticket is already closed.');
        }

        $message = $ticket->messages()->create([
            'sender_id' => $actor->id,
            'message' => $this->normalizeMessageBody($messageBody),
        ]);

        $ticket->update([
            'last_message_at' => now(),
        ]);

        $this->sendMessageEmails($ticket->fresh(['client', 'agent']), $message->fresh('sender'), $actor);

        return $message;
    }

    public function assignAgent(User $actor, SupportTicket $ticket, ?int $agentId): void
    {
        if (!$actor->isAdmin()) {
            abort(403);
        }

        $this->authorize($ticket, $actor);

        if ($agentId === null) {
            $ticket->update(['agent_id' => null]);
            return;
        }

        $agent = User::query()
            ->whereKey($agentId)
            ->where('role_id', User::ROLE_AGENT)
            ->firstOrFail();

        $ticket->update([
            'agent_id' => $agent->id,
        ]);
    }

    public function requestClose(User $actor, SupportTicket $ticket, ?string $note = null): void
    {
        $this->authorize($ticket, $actor);

        if (!$actor->isAgent() || !$this->canAgentAccessTicket($ticket, $actor)) {
            abort(403);
        }

        if ($ticket->isClosed()) {
            abort(422, 'This ticket is already closed.');
        }

        $ticket->update([
            'status' => SupportTicket::STATUS_CLOSE_REQUESTED,
            'close_requested_by' => $actor->id,
            'close_requested_at' => now(),
            'close_request_note' => filled($note) ? trim($note) : null,
            'last_message_at' => now(),
        ]);

        $message = $ticket->messages()->create([
            'sender_id' => $actor->id,
            'message' => filled($note)
                ? $this->normalizeMessageBody($note)
                : 'Requested to close the ticket.',
        ]);

        $this->sendMessageEmails($ticket->fresh(['client', 'agent']), $message->fresh('sender'), $actor);
    }

    public function approveClose(User $actor, SupportTicket $ticket): void
    {
        $this->authorize($ticket, $actor);

        if (!$actor->isClient()) {
            abort(403);
        }

        if (!$ticket->isCloseRequested()) {
            abort(422, 'No close request is pending for this ticket.');
        }

        $ticket->update([
            'status' => SupportTicket::STATUS_CLOSED,
            'closed_by' => $actor->id,
            'closed_at' => now(),
            'close_requested_by' => null,
            'close_requested_at' => null,
            'close_request_note' => null,
            'last_message_at' => now(),
        ]);
    }

    public function declineClose(User $actor, SupportTicket $ticket): void
    {
        $this->authorize($ticket, $actor);

        if (!$actor->isClient()) {
            abort(403);
        }

        if (!$ticket->isCloseRequested()) {
            abort(422, 'No close request is pending for this ticket.');
        }

        $ticket->update([
            'status' => SupportTicket::STATUS_OPEN,
            'close_requested_by' => null,
            'close_requested_at' => null,
            'close_request_note' => null,
            'last_message_at' => now(),
        ]);
    }

    public function close(User $actor, SupportTicket $ticket): void
    {
        $this->authorize($ticket, $actor);

        if (!$actor->isAdmin() && !$this->clientOwnsTicket($ticket, $actor)) {
            abort(403);
        }

        if ($ticket->isClosed()) {
            return;
        }

        $ticket->update([
            'status' => SupportTicket::STATUS_CLOSED,
            'closed_by' => $actor->id,
            'closed_at' => now(),
            'close_requested_by' => null,
            'close_requested_at' => null,
            'close_request_note' => null,
            'last_message_at' => now(),
        ]);
    }

    public function routePrefix(User $user): string
    {
        if ($user->isAdmin()) {
            return 'admin';
        }

        if ($user->isAgent()) {
            return 'agent';
        }

        return 'client';
    }

    public function displaySenderName(SupportTicketMessage $message, User $viewer): string
    {
        $sender = $message->sender;

        if ($sender && $sender->id === $viewer->id) {
            return 'You';
        }

        if ($viewer->isClient() && $sender && ($sender->isAgent() || $sender->isAdmin())) {
            return SupportTicket::CLIENT_ALIAS;
        }

        return $sender?->name ?? SupportTicket::CLIENT_ALIAS;
    }

    public function canAgentAccessTicket(SupportTicket $ticket, User $user): bool
    {
        if (!$user->isAgent()) {
            return false;
        }

        $userId = (int) $user->getKey();

        if ((int) ($ticket->agent_id ?? 0) === $userId) {
            return true;
        }

        if (filled($ticket->agent_id)) {
            return false;
        }

        return AgentClientAssignment::query()
            ->active()
            ->where('agent_id', $userId)
            ->where('client_id', (int) $ticket->client_id)
            ->exists();
    }

    public function applyAssignedAgentFilter(Builder $query, int $agentId): Builder
    {
        return $query->where(function (Builder $ticketQuery) use ($agentId) {
            $ticketQuery->where('agent_id', $agentId)
                ->orWhere(function (Builder $fallbackQuery) use ($agentId) {
                    $fallbackQuery->whereNull('agent_id');
                    $this->applyActiveAssignmentConstraint($fallbackQuery, $agentId);
                });
        });
    }

    public function applyAssignmentStateFilter(Builder $query, string $assignmentState): Builder
    {
        if ($assignmentState === 'assigned') {
            return $query->where(function (Builder $ticketQuery) {
                $ticketQuery->whereNotNull('agent_id')
                    ->orWhere(function (Builder $fallbackQuery) {
                        $fallbackQuery->whereNull('agent_id');
                        $this->applyActiveAssignmentConstraint($fallbackQuery);
                    });
            });
        }

        if ($assignmentState === 'unassigned') {
            return $query->whereNull('agent_id')
                ->where(function (Builder $ticketQuery) {
                    $this->applyActiveAssignmentConstraint($ticketQuery, null, true);
                });
        }

        return $query;
    }

    public function hydrateEffectiveAgents(iterable $tickets): void
    {
        $ticketCollection = $tickets instanceof Collection ? $tickets : collect($tickets);

        $clientIdsNeedingFallback = $ticketCollection
            ->filter(fn ($ticket) => $ticket instanceof SupportTicket && !$ticket->agent && blank($ticket->agent_id) && filled($ticket->client_id))
            ->map(fn (SupportTicket $ticket) => (int) $ticket->client_id)
            ->filter()
            ->unique()
            ->values();

        $assignmentsByClientId = AgentClientAssignment::query()
            ->active()
            ->with('agent')
            ->when(
                $clientIdsNeedingFallback->isNotEmpty(),
                fn (Builder $assignmentQuery) => $assignmentQuery->whereIn('client_id', $clientIdsNeedingFallback->all()),
                fn (Builder $assignmentQuery) => $assignmentQuery->whereRaw('1 = 0')
            )
            ->newestFirst()
            ->get()
            ->groupBy(fn (AgentClientAssignment $assignment) => (int) $assignment->client_id)
            ->map(fn (Collection $assignments) => $assignments->first());

        $ticketCollection->each(function ($ticket) use ($assignmentsByClientId): void {
            if (!$ticket instanceof SupportTicket) {
                return;
            }

            $effectiveAgent = $ticket->agent;

            if (!$effectiveAgent && blank($ticket->agent_id)) {
                $effectiveAgent = optional($assignmentsByClientId->get((int) $ticket->client_id))->agent;
            }

            $ticket->setRelation('effectiveAgent', $effectiveAgent);
        });
    }

    protected function sendMessageEmails(SupportTicket $ticket, SupportTicketMessage $message, ?User $sender = null): void
    {
        $ticket->loadMissing('client', 'agent', 'creator');
        $this->hydrateEffectiveAgents([$ticket]);

        $sender ??= $message->relationLoaded('sender')
            ? $message->sender
            : $message->sender()->first();

        if (!$sender) {
            return;
        }

        $recipients = $this->messageRecipients($ticket, $sender);

        if ($recipients->isEmpty()) {
            Log::warning('Support ticket message skipped delivery: no recipients', [
                'ticket_id' => $ticket->id,
                'client_id' => $ticket->client_id,
                'sender_id' => $sender->id,
            ]);

            return;
        }

        foreach ($recipients as $recipient) {
            if (filled($recipient->email)) {
                Mail::to($recipient->email)->send(
                    new SupportTicketMessageMail(
                        $ticket,
                        $message,
                        $recipient,
                        $this->senderDisplayNameForRecipient($recipient, $sender)
                    )
                );
            }

            $this->notificationService()->notify(
                $recipient,
                'New Support Message ' . $ticket->display_reference,
                $this->messageNotificationText($ticket, $message, $sender, $recipient),
                Notification::TYPE_INFO,
                [
                    'support_ticket_id' => $ticket->id,
                    'support_ticket_reference' => $ticket->display_reference,
                    'support_ticket_message_id' => $message->id,
                    'sender_id' => $sender->id,
                    'sender_name' => $sender->name,
                    'category' => 'support_ticket',
                ],
                Notification::PRIORITY_HIGH,
                $ticket,
                route($this->routePrefix($recipient) . '.support-tickets.show', $ticket)
            );
        }
    }

    protected function messageRecipients(SupportTicket $ticket, User $sender): Collection
    {
        $recipients = collect([
            $ticket->client,
            $ticket->getRelation('effectiveAgent'),
            $ticket->creator,
        ])
            ->filter(fn ($user) => $user instanceof User)
            ->unique('id')
            ->reject(fn (User $user) => $user->id === $sender->id)
            ->values();

        if ($recipients->isNotEmpty()) {
            return $recipients;
        }

        if ($sender->isClient()) {
            return User::query()
                ->whereIn('role_id', [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN])
                ->orderBy('id')
                ->get()
                ->reject(fn (User $user) => $user->id === $sender->id)
                ->values();
        }

        return collect();
    }

    protected function senderDisplayNameForRecipient(User $recipient, User $sender): string
    {
        if ($recipient->isClient() && ($sender->isAdmin() || $sender->isAgent())) {
            return SupportTicket::CLIENT_ALIAS;
        }

        return $sender->name;
    }

    protected function messageNotificationText(
        SupportTicket $ticket,
        SupportTicketMessage $message,
        User $sender,
        User $recipient
    ): string {
        $preview = Str::limit(trim(preg_replace('/\s+/', ' ', $message->message)), 120);
        $senderName = $this->senderDisplayNameForRecipient($recipient, $sender);

        return $senderName . ' replied to "' . $ticket->subject . '": ' . $preview;
    }

    protected function notificationService(): NotificationService
    {
        return app(NotificationService::class);
    }

    protected function normalizeMessageBody(string $message): string
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $message);
        $lines = collect(explode("\n", $normalized))
            ->map(function (string $line): string {
                $trimmedLine = trim($line);

                if ($trimmedLine === '') {
                    return '';
                }

                return (string) preg_replace('/[ \t]+/', ' ', $trimmedLine);
            })
            ->implode("\n");

        $lines = (string) preg_replace("/\n{3,}/", "\n\n", $lines);

        return trim($lines);
    }

    protected function clientOwnsTicket(SupportTicket $ticket, User $user): bool
    {
        $ticketClientId = (int) ($ticket->client_id ?? 0);
        $userId = (int) $user->getKey();
        $clientProfileId = (int) ($user->clientProfile?->id ?? 0);

        if ($ticketClientId === $userId || (int) ($ticket->created_by ?? 0) === $userId) {
            return true;
        }

        if ($clientProfileId > 0 && $ticketClientId === $clientProfileId) {
            return true;
        }

        return (int) ($ticket->client?->getKey() ?? 0) === $userId;
    }

    protected function applyActiveAssignmentConstraint(
        Builder $query,
        ?int $agentId = null,
        bool $negate = false
    ): Builder {
        $comparisonDate = now()->toDateString();
        $method = $negate ? 'whereNotExists' : 'whereExists';

        return $query->{$method}(function ($assignmentQuery) use ($agentId, $comparisonDate) {
            $assignmentQuery->selectRaw('1')
                ->from('agent_client_assignments')
                ->whereColumn('agent_client_assignments.client_id', 'support_tickets.client_id')
                ->where('agent_client_assignments.is_active', true)
                ->whereNull('agent_client_assignments.service_completed_at')
                ->where(function ($activeQuery) use ($comparisonDate) {
                    $activeQuery->whereNull('agent_client_assignments.service_end_date')
                        ->orWhere('agent_client_assignments.service_end_date', '>=', $comparisonDate);
                });

            if ($agentId !== null) {
                $assignmentQuery->where('agent_client_assignments.agent_id', $agentId);
            }
        });
    }
}
