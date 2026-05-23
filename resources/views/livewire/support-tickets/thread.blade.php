<div
    class="support-thread-shell"
    wire:poll.8s="refreshThread"
    x-data="{
        draftMessage: '',
        pendingMessages: [],
        sending: false,
        scrollThread() {
            const panel = this.$refs.threadPanel;

            if (!panel) {
                return;
            }

            panel.scrollTop = panel.scrollHeight;
        },
        async submitMessage() {
            const body = this.draftMessage.trim();

            if (!body || this.sending) {
                return;
            }

            const tempId = `pending-${Date.now()}`;

            this.pendingMessages.push({
                id: tempId,
                body,
                display_name: 'You',
                time: 'Sending...',
                stamp: new Date().toLocaleString(),
            });

            this.draftMessage = '';
            this.sending = true;

            this.$nextTick(() => this.scrollThread());

            try {
                await $wire.sendMessage(body);
            } catch (error) {
                this.pendingMessages = this.pendingMessages.filter((message) => message.id !== tempId);
                this.draftMessage = body;
                this.sending = false;
                this.$nextTick(() => {
                    this.scrollThread();
                    this.$refs.messageInput?.focus();
                });
            }
        },
        finishSend() {
            this.pendingMessages = [];
            this.sending = false;
            this.$nextTick(() => {
                this.scrollThread();
                this.$refs.messageInput?.focus();
            });
        }
    }"
    x-init="$nextTick(() => scrollThread())"
    x-on:support-ticket-scroll.window="$nextTick(() => scrollThread())"
    x-on:support-ticket-message-sent.window="finishSend()"
>
    @php
        $effectiveAgent = $ticket->effectiveAgent ?? $ticket->agent;
    @endphp

    @if($ticket->isCloseRequested())
        <div class="support-thread-banner support-thread-banner-warning">
            <div>
                <div class="support-thread-banner-title">
                    <i class="fas fa-hourglass-half"></i>
                    <span>Close request pending</span>
                </div>
                <div class="support-thread-banner-copy">
                    {{ $isClient ? 'Your agent requested to close this ticket.' : 'This ticket is waiting on the client to approve the close request.' }}
                    @if($ticket->close_request_note)
                        <span class="d-block mt-2">{{ $ticket->close_request_note }}</span>
                    @endif
                </div>
            </div>

            @if($isClient && !$ticket->isClosed())
                <div class="support-thread-banner-actions">
                    <button type="button" class="btn btn-black btn-sm" wire:click="approveClose">
                        <i class="fas fa-check me-1"></i>Approve
                    </button>
                    <button type="button" class="btn btn-white btn-sm" wire:click="declineClose">
                        <i class="fas fa-xmark me-1"></i>Decline
                    </button>
                </div>
            @endif
        </div>
    @endif

    <div class="support-thread-grid">
        <section class="support-thread-main">
            <div class="card support-thread-card">
                <div class="support-thread-card-header">
                    <div>
                        <p class="work-updates-eyebrow">Conversation</p>
                        <h2 class="theme-display support-thread-title">{{ $ticket->subject }}</h2>
                        <p class="support-thread-copy">
                            {{ $ticket->display_reference }} / {{ $ticket->status_label }} / Updated {{ $ticket->last_message_at?->diffForHumans() ?? $ticket->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <span class="{{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span>
                </div>

                <div class="support-thread-messages" x-ref="threadPanel">
                    @forelse($threadMessages as $message)
                        <article class="support-thread-message {{ $message['is_mine'] ? 'is-mine' : 'is-theirs' }}" wire:key="support-ticket-message-{{ $message['id'] }}">
                            <div class="support-thread-message-panel">
                                <div class="support-thread-message-top">
                                    <span class="support-thread-message-author">{{ $message['display_name'] }}</span>
                                    <span class="support-thread-message-time" title="{{ $message['stamp'] }}">{{ $message['time'] }}</span>
                                </div>
                                <div class="support-thread-message-body">{!! nl2br(e($message['body'])) !!}</div>
                            </div>
                        </article>
                    @empty
                        <div class="support-thread-empty" x-show="pendingMessages.length === 0">
                            <div class="support-thread-empty-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <div class="support-thread-empty-title">No messages yet</div>
                            <div class="support-thread-empty-copy">Start the conversation below.</div>
                        </div>
                    @endforelse

                    <template x-for="message in pendingMessages" :key="message.id">
                        <article class="support-thread-message is-mine">
                            <div class="support-thread-message-panel support-thread-message-panel-pending">
                                <div class="support-thread-message-top">
                                    <span class="support-thread-message-author" x-text="message.display_name"></span>
                                    <span class="support-thread-message-time" :title="message.stamp" x-text="message.time"></span>
                                </div>
                                <div class="support-thread-message-body" x-text="message.body"></div>
                            </div>
                        </article>
                    </template>
                </div>

                <div class="support-thread-composer">
                    @if(!$ticket->isClosed())
                        <form @submit.prevent="submitMessage()" class="support-thread-composer-form">
                            <div class="support-thread-composer-field">
                                <label class="form-label fw-semibold" for="support_thread_message">Reply</label>
                                <textarea
                                    id="support_thread_message"
                                    x-ref="messageInput"
                                    class="form-control"
                                    rows="4"
                                    x-model="draftMessage"
                                    @keydown.enter.exact.prevent="submitMessage()"
                                    placeholder="Write your reply here..."
                                ></textarea>
                                @error('message')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="support-thread-composer-actions">
                                <button type="submit" class="btn btn-black" :disabled="sending || !draftMessage.trim()">
                                    <span x-show="!sending">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </span>
                                    <span x-show="sending">
                                        <i class="fas fa-spinner fa-spin me-2"></i>Sending...
                                    </span>
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="support-thread-closed-note">
                            <i class="fas fa-lock me-2"></i>This ticket is closed. Start a new ticket if you need more help.
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <aside class="support-thread-side">
            <div class="card support-thread-side-card">
                <div class="card-header border-0 pb-0">
                    <h5 class="mb-0">Ticket Details</h5>
                </div>
                <div class="card-body support-thread-side-body">
                    <div class="support-thread-detail-row">
                        <span class="support-thread-detail-label">Reference</span>
                        <span class="support-thread-detail-value">{{ $ticket->display_reference }}</span>
                    </div>

                    <div class="support-thread-detail-row">
                        <span class="support-thread-detail-label">Status</span>
                        <span class="{{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span>
                    </div>

                    <div class="support-thread-detail-row">
                        <span class="support-thread-detail-label">Client</span>
                        <div class="text-end">
                            <div class="support-thread-detail-value">{{ $ticket->client?->name ?? 'Client' }}</div>
                            <div class="support-thread-detail-copy">{{ $ticket->client?->email }}</div>
                        </div>
                    </div>

                    <div class="support-thread-detail-row">
                        <span class="support-thread-detail-label">{{ $isClient ? 'Support' : 'Assigned' }}</span>
                        <div class="text-end">
                            <div class="support-thread-detail-value">
                                {{ $isClient ? $clientAlias : ($effectiveAgent?->name ?? 'Unassigned') }}
                            </div>
                            <div class="support-thread-detail-copy">
                                {{ $isClient ? 'Support Specialist' : ($effectiveAgent?->email ?? 'Waiting for assignment') }}
                            </div>
                        </div>
                    </div>

                    <div class="support-thread-detail-row">
                        <span class="support-thread-detail-label">Opened</span>
                        <div class="text-end">
                            <div class="support-thread-detail-value">{{ $ticket->created_at->format('M j, Y') }}</div>
                            <div class="support-thread-detail-copy">{{ $ticket->created_at->format('g:i A') }}</div>
                        </div>
                    </div>

                    <div class="support-thread-detail-row">
                        <span class="support-thread-detail-label">Last Activity</span>
                        <div class="text-end">
                            <div class="support-thread-detail-value">{{ $ticket->last_message_at?->diffForHumans() ?? 'Just now' }}</div>
                            <div class="support-thread-detail-copy">{{ $ticket->last_message_at?->format('M j, Y g:i A') ?? '' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if($isAdmin)
                <div class="card support-thread-side-card">
                    <div class="card-header border-0 pb-0">
                        <h5 class="mb-0">Assign Agent</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <x-filter-select
                                wire-model="assignAgentId"
                                label="Agent"
                                placeholder="Leave unassigned"
                                :options="$availableAgents->map(fn ($agent) => ['value' => $agent->id, 'text' => $agent->name . ' (' . $agent->email . ')'])->values()->all()"
                                searchable
                            />
                            @error('assignAgentId')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="button" class="btn btn-black w-100" wire:click="saveAssignment">
                            <i class="fas fa-save me-2"></i>Update Assignment
                        </button>
                    </div>
                </div>
            @endif

            <div class="card support-thread-side-card">
                <div class="card-header border-0 pb-0">
                    <h5 class="mb-0">Ticket Controls</h5>
                </div>
                <div class="card-body support-thread-side-body">
                    @if($ticket->isClosed())
                        <div class="support-thread-closed-state">
                            <i class="fas fa-circle-check"></i>
                            <div>
                                <div class="support-thread-detail-value">Closed</div>
                                <div class="support-thread-detail-copy">
                                    {{ $ticket->closed_at?->format('M j, Y g:i A') ?? 'Recently' }}
                                </div>
                            </div>
                        </div>
                    @elseif($canRequestClose)
                        <div class="support-thread-close-form">
                            <label class="form-label fw-semibold">Close request note</label>
                            <textarea
                                class="form-control"
                                rows="3"
                                wire:model.defer="closeRequestNote"
                                placeholder="Add an optional note for the client."
                            ></textarea>
                            @error('closeRequestNote')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <button type="button" class="btn btn-black w-100 mt-3" wire:click="requestClose">
                                <i class="fas fa-hand-paper me-2"></i>Request Close
                            </button>
                        </div>
                    @elseif($isClient)
                        <p class="support-thread-control-copy">Close this ticket once the issue is fully resolved.</p>
                        <button type="button" class="btn btn-black w-100" wire:click="closeTicket">
                            <i class="fas fa-lock me-2"></i>Close Ticket
                        </button>
                    @elseif($isAdmin)
                        <p class="support-thread-control-copy">Close this ticket directly when the conversation is complete.</p>
                        <button type="button" class="btn btn-black w-100" wire:click="closeTicket">
                            <i class="fas fa-lock me-2"></i>Close Ticket
                        </button>
                    @else
                        <p class="support-thread-control-copy">This ticket is waiting on the client to respond to the close request.</p>
                    @endif
                </div>
            </div>
        </aside>
    </div>
</div>

@push('styles')
<style>
    .support-thread-shell {
        display: grid;
        gap: 1.25rem;
    }

    .support-thread-grid {
        display: grid;
        gap: 1.25rem;
        grid-template-columns: minmax(0, 1.65fr) minmax(18rem, 0.95fr);
    }

    .support-thread-main,
    .support-thread-side {
        min-width: 0;
    }

    .support-thread-card,
    .support-thread-side-card {
        border-radius: 1.45rem;
    }

    .support-thread-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.35rem 1.35rem 1rem;
        border-bottom: 1px solid rgba(17, 17, 17, 0.06);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(249, 245, 236, 0.98));
    }

    .support-thread-title {
        margin: 0;
        color: #111111;
        font-size: clamp(1.15rem, 1.8vw, 1.45rem);
        font-weight: 700;
    }

    .support-thread-copy {
        margin: 0.45rem 0 0;
        color: #706557;
        font-size: 0.86rem;
        line-height: 1.6;
    }

    .support-thread-banner {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.1rem;
        border: 1px solid rgba(217, 119, 6, 0.18);
        border-radius: 1.25rem;
    }

    .support-thread-banner-warning {
        background: linear-gradient(180deg, rgba(255, 251, 235, 0.98), rgba(255, 255, 255, 0.98));
    }

    .support-thread-banner-title {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        color: #8b5e12;
        font-weight: 700;
    }

    .support-thread-banner-copy {
        margin-top: 0.35rem;
        color: #6f6555;
        font-size: 0.88rem;
        line-height: 1.6;
    }

    .support-thread-banner-actions {
        display: inline-flex;
        gap: 0.6rem;
        flex-wrap: wrap;
    }

    .support-thread-messages {
        display: grid;
        gap: 0.9rem;
        max-height: 34rem;
        padding: 1.2rem 1.2rem 0;
        overflow-y: auto;
        background: linear-gradient(180deg, rgba(249, 248, 245, 0.96), rgba(255, 255, 255, 0.98));
    }

    .support-thread-message {
        display: flex;
    }

    .support-thread-message.is-mine {
        justify-content: flex-end;
    }

    .support-thread-message.is-theirs {
        justify-content: flex-start;
    }

    .support-thread-message-panel {
        display: grid;
        gap: 0.55rem;
        width: min(100%, 38rem);
        padding: 0.95rem 1rem;
        border-radius: 1.15rem;
        box-shadow: 0 10px 26px rgba(17, 17, 17, 0.04);
    }

    .support-thread-message-panel-pending {
        position: relative;
        opacity: 0.82;
    }

    .support-thread-message-panel-pending::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        border: 1px dashed rgba(244, 213, 143, 0.32);
        pointer-events: none;
    }

    .support-thread-message.is-mine .support-thread-message-panel {
        background: linear-gradient(135deg, #111111 0%, #241f17 55%, #4c3920 100%);
        color: #ffffff;
        border-top-right-radius: 0.35rem;
    }

    .support-thread-message.is-theirs .support-thread-message-panel {
        border: 1px solid rgba(17, 17, 17, 0.06);
        background: #ffffff;
        color: #171717;
        border-top-left-radius: 0.35rem;
    }

    .support-thread-message-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .support-thread-message-author {
        font-size: 0.8rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .support-thread-message-time {
        font-size: 0.72rem;
        opacity: 0.72;
        white-space: nowrap;
    }

    .support-thread-message-body {
        font-size: 0.92rem;
        line-height: 1.65;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .support-thread-empty {
        display: grid;
        justify-items: center;
        gap: 0.45rem;
        padding: 3rem 1rem;
        color: #72685d;
        text-align: center;
    }

    .support-thread-empty-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 3.6rem;
        height: 3.6rem;
        border-radius: 1.15rem;
        background: linear-gradient(180deg, #191919 0%, #0d0d0d 100%);
        color: #f4d58f;
        font-size: 1rem;
    }

    .support-thread-empty-title {
        color: #111111;
        font-weight: 700;
    }

    .support-thread-empty-copy {
        font-size: 0.88rem;
    }

    .support-thread-composer {
        padding: 1.15rem 1.2rem 1.25rem;
        border-top: 1px solid rgba(17, 17, 17, 0.06);
        background: rgba(255, 255, 255, 0.98);
    }

    .support-thread-composer-form {
        display: grid;
        gap: 1rem;
    }

    .support-thread-composer-actions {
        display: flex;
        justify-content: flex-end;
    }

    .support-thread-closed-note {
        color: #6f6555;
        font-size: 0.9rem;
    }

    .support-thread-side-body {
        display: grid;
        gap: 0.85rem;
    }

    .support-thread-detail-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid rgba(17, 17, 17, 0.06);
    }

    .support-thread-detail-row:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .support-thread-detail-label {
        color: #8b7350;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
    }

    .support-thread-detail-value {
        color: #111111;
        font-size: 0.92rem;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .support-thread-detail-copy {
        color: #72685d;
        font-size: 0.8rem;
        overflow-wrap: anywhere;
    }

    .support-thread-control-copy {
        margin: 0 0 0.9rem;
        color: #706557;
        font-size: 0.86rem;
        line-height: 1.6;
    }

    .support-thread-close-form {
        display: grid;
        gap: 0.4rem;
    }

    .support-thread-closed-state {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.95rem 1rem;
        border: 1px solid rgba(16, 185, 129, 0.14);
        border-radius: 1rem;
        background: linear-gradient(180deg, rgba(236, 253, 243, 0.98), rgba(255, 255, 255, 0.98));
        color: #166534;
    }

    @media (max-width: 1200px) {
        .support-thread-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .support-thread-banner,
        .support-thread-card-header,
        .support-thread-detail-row {
            flex-direction: column;
            align-items: stretch;
        }

        .support-thread-card-header > .badge,
        .support-thread-card-header > [class*="badge"] {
            align-self: flex-start;
        }

        .support-thread-banner-actions,
        .support-thread-detail-row .text-end,
        .support-thread-composer-actions .btn {
            width: 100%;
        }

        .support-thread-detail-row .text-end {
            text-align: left !important;
        }

        .support-thread-messages {
            max-height: 28rem;
        }
    }

    @media (max-width: 576px) {
        .support-thread-card,
        .support-thread-side-card,
        .support-thread-banner {
            border-radius: 1.2rem;
        }

        .support-thread-card-header,
        .support-thread-composer {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .support-thread-messages {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .support-thread-message-panel {
            width: 100%;
            padding: 0.85rem 0.9rem;
        }

        .support-thread-message-top,
        .support-thread-closed-state {
            flex-direction: column;
            align-items: flex-start;
        }

        .support-thread-message-time {
            white-space: normal;
        }

        .support-thread-composer-actions .btn,
        .support-thread-banner-actions .btn {
            width: 100%;
        }

        .support-thread-banner-actions {
            display: grid;
        }

        .support-thread-detail-row {
            gap: 0.55rem;
        }
    }
</style>
@endpush
