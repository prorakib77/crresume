@php
    use App\Models\SupportTicket;
    use Illuminate\Support\Str;

    $attentionValue = $isAdmin ? $stats['unassigned'] : $stats['close_requested'];
    $attentionLabel = $isAdmin ? 'Unassigned' : 'Close Requests';
    $attentionIcon = $isAdmin ? 'fa-user-clock' : 'fa-hourglass-half';
@endphp

<div
    x-data="{ filtersOpen: false }"
    x-init="
        if (@js($openComposer)) {
            requestAnimationFrame(() => {
                document.getElementById('support-ticket-composer')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        }
    "
    @keydown.escape.window="filtersOpen = false"
    class="support-center-shell {{ $canCompose ? 'has-composer' : '' }}"
>
    <div class="support-center-main">
        <div class="work-updates-shell">
            <div class="work-updates-toolbar">
                <div>
                    @if($isAdmin || $isAgent)
                        <p class="work-updates-eyebrow">{{ $isAdmin ? 'Admin Desk' : 'Agent Desk' }}</p>
                    @endif
                    <h2 class="theme-display work-updates-heading font-black" style="font-weight: 700 !important;">{{ $isClient ? 'Support Tickets' : 'Support Center' }}</h2>
                    <p class="work-updates-copy">
                        {{ $isAdmin
                            ? 'Create client tickets, assign the right agent, and keep every support conversation moving from one responsive workspace.'
                            : ($isAgent
                                ? 'Track client support requests, jump into open conversations.'
                                : 'Create a ticket, track replies from our support team, and manage all conversations in one organized timeline.') }}
                    </p>
                </div>

                <div class="work-updates-toolbar-actions support-center-toolbar-actions">
                    @if($canCompose)
                        <button
                            type="button"
                            class="btn btn-black"
                            @click="document.getElementById('support-ticket-composer')?.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                        >
                            <i class="fas fa-plus"></i>
                            <span>{{ $isAdmin || $isClient ? 'Create Ticket' : 'New Ticket' }}</span>
                        </button>
                    @endif

                    <button type="button" class="work-update-filter-button" @click="filtersOpen = true">
                        <i class="fas fa-filter"></i>
                        <span>Filters</span>
                        @if($this->activeFilterCount > 0)
                            <span class="work-update-filter-badge">{{ $this->activeFilterCount }}</span>
                        @endif
                    </button>
                </div>
            </div>

            <div class="work-update-filter-summary">
                <span><i class="fas fa-life-ring"></i>{{ $tickets->total() }} tickets</span>
                @if($this->activeFilterCount > 0)
                    <span class="work-update-filter-summary-active">Filtered view active</span>
                @endif
            </div>

            <div class="work-updates-stat-grid support-center-stat-grid">
                <article class="work-updates-stat-card">
                    <div class="work-updates-stat-icon">
                        <i class="fas fa-ticket"></i>
                    </div>
                    <div>
                        <div class="work-updates-stat-value">{{ $stats['total'] }}</div>
                        <div class="work-updates-stat-label">Total Tickets</div>
                    </div>
                </article>

                <article class="work-updates-stat-card">
                    <div class="work-updates-stat-icon">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <div>
                        <div class="work-updates-stat-value">{{ $stats['open'] }}</div>
                        <div class="work-updates-stat-label">Open</div>
                    </div>
                </article>

                <article class="work-updates-stat-card">
                    <div class="work-updates-stat-icon">
                        <i class="fas {{ $attentionIcon }}"></i>
                    </div>
                    <div>
                        <div class="work-updates-stat-value">{{ $attentionValue }}</div>
                        <div class="work-updates-stat-label">{{ $attentionLabel }}</div>
                    </div>
                </article>

                <article class="work-updates-stat-card">
                    <div class="work-updates-stat-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div>
                        <div class="work-updates-stat-value">{{ $stats['closed'] }}</div>
                        <div class="work-updates-stat-label">Closed</div>
                    </div>
                </article>
            </div>

            <div x-cloak x-show="filtersOpen" class="work-update-filter-backdrop" @click="filtersOpen = false"></div>

            <aside
                x-cloak
                x-show="filtersOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="-translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="-translate-x-full opacity-0"
                class="work-update-filter-drawer"
            >
                <div class="work-update-filter-drawer-inner">
                    <div class="work-update-filter-drawer-header">
                        <div>
                            <p class="work-updates-eyebrow">Filter Panel</p>
                            <h3 class="theme-display mb-0 text-2xl text-stone-950">Ticket Filters</h3>
                        </div>
                        <button type="button" class="work-update-filter-close" @click="filtersOpen = false" aria-label="Close filters">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="work-update-filter-drawer-body">
                        <div class="work-update-filter-group">
                            <label class="form-label" for="support_ticket_search">Search</label>
                            <input id="support_ticket_search" type="text" class="form-control" placeholder="Ticket ID, subject, client, agent, or message" wire:model.live="search">
                        </div>

                        <div class="work-update-filter-group">
                            <x-filter-select
                                wire-model="status"
                                label="Status"
                                placeholder="All Statuses"
                                :options="[
                                    ['value' => SupportTicket::STATUS_OPEN, 'text' => 'Open'],
                                    ['value' => SupportTicket::STATUS_CLOSE_REQUESTED, 'text' => 'Close Requested'],
                                    ['value' => SupportTicket::STATUS_CLOSED, 'text' => 'Closed'],
                                ]"
                            />
                        </div>

                        @if(!$isClient)
                            <div class="work-update-filter-group">
                                <x-filter-select
                                    wire-model="clientId"
                                    label="Client"
                                    placeholder="All Clients"
                                    :options="$filterClients->map(fn ($client) => ['value' => $client->id, 'text' => $client->name . ' (' . $client->email . ')'])->values()->all()"
                                    searchable
                                />
                            </div>
                        @endif

                        @if($isAdmin)
                            <div class="work-update-filter-group">
                                <x-filter-select
                                    wire-model="agentId"
                                    label="Assigned Agent"
                                    placeholder="All Agents"
                                    :options="$availableAgents->map(fn ($agent) => ['value' => $agent->id, 'text' => $agent->name . ' (' . $agent->email . ')'])->values()->all()"
                                    searchable
                                />
                            </div>

                            <div class="work-update-filter-group">
                                <x-filter-select
                                    wire-model="assignmentState"
                                    label="Assignment"
                                    placeholder="All Assignment States"
                                    :options="[
                                        ['value' => 'assigned', 'text' => 'Assigned'],
                                        ['value' => 'unassigned', 'text' => 'Unassigned'],
                                    ]"
                                />
                            </div>
                        @endif
                    </div>

                    <div class="work-update-filter-drawer-footer">
                        <button type="button" class="btn btn-white w-100" wire:click="resetFilters">
                            <i class="fas fa-rotate-left me-2"></i>Reset Filters
                        </button>
                        <button type="button" class="btn btn-black w-100" @click="filtersOpen = false">
                            <i class="fas fa-check me-2"></i>Done
                        </button>
                    </div>
                </div>
            </aside>

            <div wire:loading.delay class="work-updates-loading">
                <i class="fas fa-spinner fa-spin me-2"></i>Updating support tickets...
            </div>

            @if($tickets->count())
                <div class="support-ticket-list">
                    @foreach($tickets as $ticket)
                        @php
                            $assignedLabel = $isClient
                                ? $clientAlias
                                : ($ticket->agent?->name ?? 'Unassigned');
                            $latestMessage = $ticket->latestMessage?->message ?: 'No messages yet.';
                        @endphp

                        <article class="support-ticket-card" wire:key="support-ticket-{{ $ticket->id }}">
                            <div class="support-ticket-card-top">
                                <div class="support-ticket-card-reference-group">
                                    <span class="support-ticket-reference">{{ $ticket->display_reference }}</span>
                                    <span class="{{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span>
                                </div>

                                <a href="{{ route($routePrefix . '.support-tickets.show', $ticket) }}" class="btn btn-black btn-sm">
                                    <i class="fas fa-arrow-right me-1"></i>Open
                                </a>
                            </div>

                            <h3 class="support-ticket-card-title">{{ $ticket->subject }}</h3>
                            <p class="support-ticket-card-copy">{{ Str::limit($latestMessage, 150) }}</p>

                            <div class="support-ticket-card-meta-grid">
                                @if(!$isClient)
                                    <div class="support-ticket-card-meta-item">
                                        <span class="support-ticket-card-meta-label">Client</span>
                                        <span class="support-ticket-card-meta-value">{{ $ticket->client?->name ?? 'Client' }}</span>
                                        <span class="support-ticket-card-meta-copy">{{ $ticket->client?->email }}</span>
                                    </div>
                                @endif

                                <div class="support-ticket-card-meta-item">
                                    <span class="support-ticket-card-meta-label">{{ $isClient ? 'Support' : 'Assigned' }}</span>
                                    <span class="support-ticket-card-meta-value">{{ $assignedLabel }}</span>
                                    <span class="support-ticket-card-meta-copy">
                                        {{ $isClient ? 'Support Specialist' : ($ticket->agent?->email ?? 'Waiting for assignment') }}
                                    </span>
                                </div>

                                <div class="support-ticket-card-meta-item">
                                    <span class="support-ticket-card-meta-label">Last Activity</span>
                                    <span class="support-ticket-card-meta-value">{{ $ticket->last_message_at?->diffForHumans() ?? $ticket->created_at->diffForHumans() }}</span>
                                    <span class="support-ticket-card-meta-copy">{{ $ticket->created_at->format('M j, Y g:i A') }}</span>
                                </div>
                            </div>

                            @if($ticket->isCloseRequested() && filled($ticket->close_request_note))
                                <div class="support-ticket-request-note">
                                    <span class="support-ticket-request-note-label">Close Request Note</span>
                                    <span>{{ Str::limit($ticket->close_request_note, 150) }}</span>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>

                <div class="work-updates-pagination">
                    {{ $tickets->links() }}
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="support-ticket-empty-icon">
                            <i class="fas fa-life-ring"></i>
                        </div>
                        <h3 class="support-ticket-empty-title">No tickets found</h3>
                        <p class="support-ticket-empty-copy">
                            {{ $canCompose ? 'Create a new ticket or adjust your filters to find the right conversation.' : 'Adjust the filters or check back when a new ticket is assigned to you.' }}
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($canCompose)
        <aside class="support-center-side" id="support-ticket-composer">
            <div class="card support-composer-card">
                <div class="card-header border-0 pb-0">
                    <h5 class="mb-0 text-bold" style="font-weight: 700 !important;">{{ $isAdmin ? 'Create Support Ticket' : 'Create New Ticket' }}</h5>
                    <small class="text-muted">
                        {{ $isAdmin ? 'Start a client conversation and route it to the right agent.' : 'Tell support what you need help with and keep the thread in one place.' }}
                    </small>
                </div>
                <div class="card-body">
                    <form wire:submit="createTicket" class="support-composer-form">
                        @if($isAdmin)
                            <div class="mb-3">
                                <x-filter-select
                                    wire-model="createClientId"
                                    label="Client"
                                    placeholder="Select client"
                                    :options="$availableClients->map(fn ($client) => ['value' => $client->id, 'text' => $client->name . ' (' . $client->email . ')'])->values()->all()"
                                    searchable
                                />
                                @error('createClientId')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <x-filter-select
                                    wire-model="createAgentId"
                                    label="Assign Agent"
                                    placeholder="Leave unassigned"
                                    :options="$availableAgents->map(fn ($agent) => ['value' => $agent->id, 'text' => $agent->name . ' (' . $agent->email . ')'])->values()->all()"
                                    searchable
                                />
                                @error('createAgentId')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        @else
                            <div class="support-composer-profile">
                                <div class="support-composer-profile-icon">
                                    <i class="fas fa-user-headset"></i>
                                </div>
                                <div>
                                    <div class="support-composer-profile-title">{{ $clientAlias }}</div>
                                    <div class="support-composer-profile-copy">
                                        {{ $assignedAgent ? 'Your assigned specialist will be notified right away.' : 'Your next available specialist will be assigned as soon as you submit.' }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Subject</label>
                            <input type="text" class="form-control" wire:model.blur="subject" placeholder="Short title for this issue">
                            @error('subject')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Message</label>
                            <textarea class="form-control" rows="6" wire:model.blur="message" placeholder="Add the details support needs to help you quickly."></textarea>
                            @error('message')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-black w-100">
                            <i class="fas fa-paper-plane me-2"></i>{{ $isAdmin || $isClient ? 'Create Ticket' : 'Send Ticket' }}
                        </button>
                    </form>
                </div>
            </div>
        </aside>
    @endif
</div>

@push('styles')
<style>
    .support-center-shell {
        display: grid;
        gap: 1.5rem;
        grid-template-columns: minmax(0, 1fr);
        align-items: start;
    }

    .support-center-shell.has-composer {
        grid-template-columns: minmax(0, 1.6fr) minmax(18rem, 0.9fr);
    }

    .support-center-main,
    .support-center-side {
        min-width: 0;
    }

    .support-center-toolbar-actions {
        align-items: center;
    }

    .support-center-stat-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .support-ticket-list {
        display: grid;
        gap: 1rem;
    }

    .support-ticket-card {
        display: grid;
        gap: 0.95rem;
        border: 1px solid rgba(200, 164, 93, 0.18);
        border-radius: 1.45rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(250, 247, 239, 0.98));
        padding: 1.15rem;
        box-shadow: 0 16px 36px rgba(17, 17, 17, 0.05);
    }

    .support-ticket-card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }

    .support-ticket-card-reference-group {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        flex-wrap: wrap;
    }

    .support-ticket-reference {
        color: #9b7431;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.18em;
        text-transform: uppercase;
    }

    .support-ticket-card-title {
        margin: 0;
        color: #111111;
        font-family: var(--display-font-family, 'Poppins'), sans-serif;
        font-size: 1.08rem;
        font-weight: 700;
        line-height: 1.4;
    }

    .support-ticket-card-copy {
        margin: 0;
        color: #5f564b;
        font-size: 0.9rem;
        line-height: 1.65;
    }

    .support-ticket-card-meta-grid {
        display: grid;
        gap: 0.8rem;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .support-ticket-card-meta-item {
        display: grid;
        gap: 0.18rem;
        padding: 0.9rem 0.95rem;
        border: 1px solid rgba(17, 17, 17, 0.06);
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.88);
        min-width: 0;
    }

    .support-ticket-card-meta-label {
        color: #9a7c46;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
    }

    .support-ticket-card-meta-value {
        color: #111111;
        font-size: 0.92rem;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .support-ticket-card-meta-copy {
        color: #72685d;
        font-size: 0.8rem;
        overflow-wrap: anywhere;
    }

    .support-ticket-request-note {
        display: grid;
        gap: 0.25rem;
        padding: 0.85rem 0.95rem;
        border: 1px solid rgba(217, 119, 6, 0.14);
        border-radius: 1rem;
        background: linear-gradient(180deg, rgba(255, 251, 235, 0.98), rgba(255, 255, 255, 0.98));
        color: #6f5212;
        font-size: 0.84rem;
        line-height: 1.55;
    }

    .support-ticket-request-note-label {
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .support-composer-card {
        position: sticky;
        top: 6rem;
    }

    .support-composer-form {
        display: grid;
        gap: 0.1rem;
    }

    .support-composer-profile {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        margin-bottom: 1rem;
        padding: 0.95rem 1rem;
        border: 1px solid rgba(200, 164, 93, 0.2);
        border-radius: 1.1rem;
        background: linear-gradient(180deg, rgba(255, 253, 247, 0.98), rgba(250, 245, 235, 0.98));
    }

    .support-composer-profile-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.7rem;
        height: 2.7rem;
        border-radius: 0.9rem;
        background: #111111;
        color: #f2d18b;
        flex-shrink: 0;
    }

    .support-composer-profile-title {
        color: #111111;
        font-weight: 700;
    }

    .support-composer-profile-copy {
        margin-top: 0.18rem;
        color: #6f6555;
        font-size: 0.84rem;
        line-height: 1.55;
    }

    .support-ticket-empty-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 4rem;
        height: 4rem;
        margin-bottom: 1rem;
        border-radius: 1.2rem;
        background: linear-gradient(180deg, #191919 0%, #0d0d0d 100%);
        color: #f4d58f;
        font-size: 1.2rem;
    }

    .support-ticket-empty-title {
        margin: 0;
        color: #111111;
        font-size: 1.1rem;
        font-weight: 700;
    }

    .support-ticket-empty-copy {
        margin: 0.65rem auto 0;
        max-width: 28rem;
        color: #72685d;
        font-size: 0.92rem;
        line-height: 1.65;
    }

    @media (max-width: 1200px) {
        .support-center-shell,
        .support-center-shell.has-composer {
            grid-template-columns: 1fr;
        }

        .support-composer-card {
            position: static;
        }
    }

    @media (max-width: 768px) {
        .support-center-stat-grid,
        .support-ticket-card-meta-grid {
            grid-template-columns: 1fr;
        }

        .support-ticket-card-top {
            flex-direction: column;
            align-items: stretch;
        }

        .support-ticket-card-top .btn {
            width: 100%;
            justify-content: center;
        }

        .support-composer-profile {
            flex-direction: column;
        }
    }

    @media (max-width: 576px) {
        .support-center-shell,
        .support-ticket-list {
            gap: 0.85rem;
        }

        .support-ticket-card {
            padding: 0.95rem;
            border-radius: 1.2rem;
        }

        .support-ticket-card-reference-group {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.45rem;
        }

        .support-ticket-card-title {
            font-size: 0.98rem;
            line-height: 1.45;
        }

        .support-ticket-card-copy {
            font-size: 0.84rem;
        }

        .support-ticket-card-meta-item {
            padding: 0.8rem 0.85rem;
        }

        .support-composer-profile {
            gap: 0.7rem;
            padding: 0.85rem 0.9rem;
        }

        .support-composer-profile-icon {
            width: 2.45rem;
            height: 2.45rem;
            border-radius: 0.8rem;
        }

        .support-composer-profile-copy {
            font-size: 0.8rem;
        }
    }
</style>
@endpush
