<?php

namespace App\Livewire\SupportTickets;

use App\Models\SupportTicket;
use App\Models\User;
use App\Services\SupportTicketService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public string $status = '';

    #[Url(as: 'client', except: '')]
    public string $clientId = '';

    #[Url(as: 'agent', except: '')]
    public string $agentId = '';

    #[Url(as: 'assignment', except: '')]
    public string $assignmentState = '';

    public bool $openComposer = false;

    public string $subject = '';

    public string $message = '';

    public string $createClientId = '';

    public string $createAgentId = '';

    public function mount(bool $openComposer = false): void
    {
        $this->openComposer = $openComposer;

        $user = auth()->user();

        if ($user->isClient()) {
            $this->createClientId = (string) $user->id;
            $this->createAgentId = (string) optional($user->active_agents->first())->id;
        }
    }

    public function updated($name): void
    {
        if ($name === 'page' || str_starts_with((string) $name, 'paginators.')) {
            return;
        }

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'status',
            'clientId',
            'agentId',
            'assignmentState',
        ]);

        $this->resetPage();
    }

    public function createTicket()
    {
        $user = auth()->user();

        $rules = [
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ];

        if ($user->isAdmin()) {
            $rules['createClientId'] = [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role_id', User::ROLE_CLIENT)),
            ];

            if ($this->createAgentId !== '') {
                $rules['createAgentId'] = [
                    Rule::exists('users', 'id')->where(fn ($query) => $query->where('role_id', User::ROLE_AGENT)),
                ];
            }
        }

        $this->validate($rules);

        $ticket = $this->service()->createTicket($user, [
            'client_id' => $this->createClientId,
            'agent_id' => $this->createAgentId !== '' ? $this->createAgentId : null,
            'subject' => $this->subject,
            'message' => $this->message,
        ]);

        $this->reset([
            'subject',
            'message',
        ]);

        if ($user->isAdmin()) {
            $this->reset([
                'createClientId',
                'createAgentId',
            ]);
        }

        session()->flash('success', 'Support ticket created successfully.');

        return $this->redirectRoute(
            $this->routePrefix() . '.support-tickets.show',
            ['supportTicket' => $ticket],
            navigate: true
        );
    }

    public function getActiveFilterCountProperty(): int
    {
        return count(array_filter([
            $this->search,
            $this->status,
            $this->clientId,
            $this->agentId,
            $this->assignmentState,
        ], fn ($value) => filled($value)));
    }

    public function render()
    {
        $user = auth()->user();

        $visibleQuery = $this->service()->queryFor($user);

        $stats = [
            'total' => (clone $visibleQuery)->count(),
            'open' => (clone $visibleQuery)->where('status', SupportTicket::STATUS_OPEN)->count(),
            'close_requested' => (clone $visibleQuery)->where('status', SupportTicket::STATUS_CLOSE_REQUESTED)->count(),
            'closed' => (clone $visibleQuery)->where('status', SupportTicket::STATUS_CLOSED)->count(),
            'unassigned' => $user->isAdmin()
                ? $this->service()->applyAssignmentStateFilter(clone $visibleQuery, 'unassigned')->count()
                : null,
        ];

        $query = (clone $visibleQuery)
            ->with(['client', 'agent', 'latestMessage.sender'])
            ->latest('last_message_at')
            ->latest('created_at');

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->clientId !== '' && !$user->isClient()) {
            $query->where('client_id', $this->clientId);
        }

        if ($this->agentId !== '' && $user->isAdmin()) {
            $this->service()->applyAssignedAgentFilter($query, (int) $this->agentId);
        }

        if ($this->assignmentState !== '' && $user->isAdmin()) {
            $this->service()->applyAssignmentStateFilter($query, $this->assignmentState);
        }

        if ($this->search !== '') {
            $search = trim($this->search);

            $query->where(function ($ticketQuery) use ($search) {
                if (is_numeric($search)) {
                    $ticketQuery->orWhere('id', (int) $search)
                        ->orWhere('reference_number', (int) $search);
                }

                if (preg_match('/^st-(\d+)$/i', $search, $matches)) {
                    $ticketQuery->orWhere('reference_number', (int) $matches[1])
                        ->orWhere('id', (int) $matches[1]);
                }

                $ticketQuery->orWhere('subject', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%')
                    ->orWhereHas('client', function ($clientQuery) use ($search) {
                        $clientQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('agent', function ($agentQuery) use ($search) {
                        $agentQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('latestMessage', function ($messageQuery) use ($search) {
                        $messageQuery->where('message', 'like', '%' . $search . '%');
                    });
            });
        }

        $tickets = $query->paginate(12);
        $this->service()->hydrateEffectiveAgents($tickets->getCollection());

        return view('livewire.support-tickets.index', [
            'tickets' => $tickets,
            'stats' => $stats,
            'routePrefix' => $this->routePrefix(),
            'availableClients' => $user->isAdmin()
                ? User::query()->where('role_id', User::ROLE_CLIENT)->orderBy('name')->get()
                : collect(),
            'availableAgents' => $user->isAdmin()
                ? User::query()->where('role_id', User::ROLE_AGENT)->orderBy('name')->get()
                : collect(),
            'filterClients' => ($user->isAdmin() || $user->isAgent())
                ? $this->service()->queryFor($user)
                    ->with('client')
                    ->get()
                    ->pluck('client')
                    ->filter()
                    ->unique('id')
                    ->sortBy('name')
                    ->values()
                : collect(),
            'clientAlias' => SupportTicket::CLIENT_ALIAS,
            'canCompose' => $user->isAdmin() || $user->isClient(),
            'isAdmin' => $user->isAdmin(),
            'isAgent' => $user->isAgent(),
            'isClient' => $user->isClient(),
            'assignedAgent' => $user->isClient() ? $user->active_agents->first() : null,
        ]);
    }

    protected function routePrefix(): string
    {
        return $this->service()->routePrefix(auth()->user());
    }

    protected function service(): SupportTicketService
    {
        return app(SupportTicketService::class);
    }
}
