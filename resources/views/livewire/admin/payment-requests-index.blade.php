@php
    use App\Models\PaymentRequest;
@endphp

<div
    class="row g-4"
    x-data="{
        filtersOpen: false,
        rejectModalOpen: false,
        rejectAction: '',
        rejectClient: '',
        rejectAmount: '',
        rejectReason: '',
        cancelModalOpen: false,
        cancelAction: '',
        cancelClient: '',
        cancelAmount: '',
        cancelReason: ''
    }"
    @keydown.escape.window="filtersOpen = false; rejectModalOpen = false; cancelModalOpen = false"
>
    <div class="col-lg-8">
        <div class="work-updates-shell">
            <div class="work-updates-toolbar">
                <div>
                    <p class="work-updates-eyebrow">Admin Overview</p>
                    <h2 class="theme-display work-updates-heading">Payment Requests</h2>
                    <p class="work-updates-copy">Track client payment requests with the same shared filter drawer and keep the request form available alongside the list.</p>
                </div>

                <div class="work-updates-toolbar-actions">
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
                <span><i class="fas fa-wallet"></i>{{ $requests->total() }} results</span>
                @if($this->activeFilterCount > 0)
                    <span class="work-update-filter-summary-active">Filtered view active</span>
                @endif
            </div>

            <div class="work-updates-stat-grid">
                <article class="work-updates-stat-card">
                    <div class="work-updates-stat-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div>
                        <div class="work-updates-stat-value">{{ $stats['total'] }}</div>
                        <div class="work-updates-stat-label">Total Requests</div>
                    </div>
                </article>

                <article class="work-updates-stat-card">
                    <div class="work-updates-stat-icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div>
                        <div class="work-updates-stat-value">{{ $stats['pending'] }}</div>
                        <div class="work-updates-stat-label">Pending</div>
                    </div>
                </article>

                <article class="work-updates-stat-card">
                    <div class="work-updates-stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="work-updates-stat-value">{{ $stats['approved'] }}</div>
                        <div class="work-updates-stat-label">Approved</div>
                    </div>
                </article>

                <article class="work-updates-stat-card">
                    <div class="work-updates-stat-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div>
                        <div class="work-updates-stat-value">{{ $stats['marked'] }}</div>
                        <div class="work-updates-stat-label">Client Marked</div>
                    </div>
                </article>

                <article class="work-updates-stat-card">
                    <div class="work-updates-stat-icon">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div>
                        <div class="work-updates-stat-value">{{ $stats['cancelled'] }}</div>
                        <div class="work-updates-stat-label">Cancelled</div>
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
                            <h3 class="theme-display mb-0 text-2xl text-stone-950">Payment Filters</h3>
                        </div>
                        <button type="button" class="work-update-filter-close" @click="filtersOpen = false" aria-label="Close filters">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="work-update-filter-drawer-body">
                        <div class="work-update-filter-group">
                            <label class="form-label" for="admin_payment_live_search">Search</label>
                            <input id="admin_payment_live_search" type="text" class="form-control" placeholder="Payment ID, client name, or email" wire:model.live="search">
                        </div>

                        <div class="work-update-filter-group">
                            <x-filter-select
                                wire-model="status"
                                label="Status"
                                placeholder="All Statuses"
                                :options="[
                                    ['value' => PaymentRequest::STATUS_PENDING, 'text' => 'Pending'],
                                    ['value' => PaymentRequest::STATUS_CLIENT_MARKED, 'text' => 'Client Marked Paid'],
                                    ['value' => PaymentRequest::STATUS_APPROVED, 'text' => 'Approved'],
                                    ['value' => PaymentRequest::STATUS_REJECTED, 'text' => 'Rejected'],
                                    ['value' => PaymentRequest::STATUS_CANCELLED, 'text' => 'Cancelled'],
                                ]"
                            />
                        </div>

                        <div class="work-update-filter-group">
                            <x-filter-select
                                wire-model="service_status"
                                label="Service Status"
                                placeholder="All Service States"
                                :options="[
                                    ['value' => 'active', 'text' => 'Active'],
                                    ['value' => 'expired', 'text' => 'Expired'],
                                ]"
                            />
                        </div>
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
                <i class="fas fa-spinner fa-spin me-2"></i>Updating payment requests...
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 mobile-data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Timeline</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $request)
                                    <tr wire:key="admin-payment-request-{{ $request->id }}">
                                        <td data-label="ID" data-stack="true">
                                            <div class="fw-semibold">{{ $request->display_reference }}</div>
                                            <div class="small text-muted">Created {{ $request->created_at?->format('M j, Y') }}</div>
                                        </td>
                                        <td data-label="Client" data-stack="true">
                                            <div class="fw-semibold">{{ $request->client?->name ?? 'Unknown' }}</div>
                                            <div class="small text-muted">{{ $request->client?->email }}</div>
                                        </td>
                                        <td data-label="Amount" data-stack="true">
                                            <div class="fw-bold">${{ number_format($request->amount, 2) }}</div>
                                            @if($request->payment_link)
                                                <a href="{{ $request->payment_link }}" target="_blank" rel="noopener" class="btn btn-border-black btn-sm mt-2">
                                                    <i class="fas fa-link me-1"></i>Payment Link
                                                </a>
                                            @endif
                                            @if($request->hasPaymentProof())
                                                <a href="{{ $request->payment_proof_url }}" target="_blank" rel="noopener" class="btn btn-border-black btn-sm mt-2">
                                                    <i class="fas fa-image me-1"></i>View Proof
                                                </a>
                                            @elseif($request->status === PaymentRequest::STATUS_CLIENT_MARKED)
                                                <div class="small text-danger mt-2">Proof missing</div>
                                            @endif
                                        </td>
                                        <td data-label="Status">
                                            <span class="badge {{ $request->getStatusBadgeClass() }}">{{ $request->getDisplayStatusLabel() }}</span>
                                            @if($request->isRejected())
                                                <div class="small text-danger mt-2">{{ \Illuminate\Support\Str::limit($request->rejection_reason, 90) }}</div>
                                            @elseif($request->isCancelled() && $request->cancellation_reason)
                                                <div class="small text-muted mt-2">{{ \Illuminate\Support\Str::limit($request->cancellation_reason, 90) }}</div>
                                            @endif
                                        </td>
                                        <td data-label="Timeline" data-stack="true">
                                            <div class="small text-muted">Requested {{ $request->created_at?->format('M j, Y') }}</div>
                                            @if($request->client_marked_at)
                                                <div class="small text-info">Client marked {{ $request->client_marked_at->format('M j, Y') }}</div>
                                            @endif
                                            @if($request->payment_proof_uploaded_at)
                                                <div class="small text-muted">Proof uploaded {{ $request->payment_proof_uploaded_at->format('M j, Y') }}</div>
                                            @endif
                                            @if($request->rejected_at)
                                                <div class="small text-danger">Rejected {{ $request->rejected_at->format('M j, Y') }}</div>
                                            @endif
                                            @if($request->cancelled_at)
                                                <div class="small text-dark">Cancelled {{ $request->cancelled_at->format('M j, Y') }}</div>
                                            @endif
                                            @if($request->approved_at)
                                                <div class="small text-success">Approved {{ $request->approved_at->format('M j, Y') }}</div>
                                            @endif
                                        </td>
                                        <td data-label="Actions" class="text-end">
                                            @if($request->note)
                                                <span class="d-block text-muted small mb-2">{{ \Illuminate\Support\Str::limit($request->note, 90) }}</span>
                                            @endif
                                            @if($request->canBeRejected())
                                                <form method="POST" action="{{ route('admin.payment-requests.approve', $request) }}" class="d-inline-block mb-2 mb-sm-0">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="fas fa-check me-1"></i>Approve
                                                    </button>
                                                </form>
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-danger btn-sm ms-2"
                                                    data-action="{{ route('admin.payment-requests.reject', $request) }}"
                                                    data-client="{{ $request->client?->name ?? 'Client' }}"
                                                    data-amount="${{ number_format($request->amount, 2) }}"
                                                    @click="
                                                        rejectModalOpen = true;
                                                        rejectAction = $el.dataset.action;
                                                        rejectClient = $el.dataset.client;
                                                        rejectAmount = $el.dataset.amount;
                                                        rejectReason = '';
                                                    "
                                                >
                                                    <i class="fas fa-ban me-1"></i>Reject
                                                </button>
                                                @if($request->canBeCancelled())
                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-dark btn-sm ms-0 ms-sm-2 mt-2 mt-sm-0"
                                                        data-action="{{ route('admin.payment-requests.cancel', $request) }}"
                                                        data-client="{{ $request->client?->name ?? 'Client' }}"
                                                        data-amount="${{ number_format($request->amount, 2) }}"
                                                        @click="
                                                            cancelModalOpen = true;
                                                            cancelAction = $el.dataset.action;
                                                            cancelClient = $el.dataset.client;
                                                            cancelAmount = $el.dataset.amount;
                                                            cancelReason = '';
                                                        "
                                                    >
                                                        <i class="fas fa-xmark me-1"></i>Cancel
                                                    </button>
                                                @endif
                                            @elseif($request->status === PaymentRequest::STATUS_APPROVED)
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($request->isCancelled())
                                                <span class="badge bg-dark text-white">Cancelled</span>
                                            @elseif($request->isRejected())
                                                <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                                    <span class="badge bg-danger-subtle text-danger">Waiting for client</span>
                                                    @if($request->canBeCancelled())
                                                        <button
                                                            type="button"
                                                            class="btn btn-outline-dark btn-sm"
                                                            data-action="{{ route('admin.payment-requests.cancel', $request) }}"
                                                            data-client="{{ $request->client?->name ?? 'Client' }}"
                                                            data-amount="${{ number_format($request->amount, 2) }}"
                                                            @click="
                                                                cancelModalOpen = true;
                                                                cancelAction = $el.dataset.action;
                                                                cancelClient = $el.dataset.client;
                                                                cancelAmount = $el.dataset.amount;
                                                                cancelReason = '';
                                                            "
                                                        >
                                                            <i class="fas fa-xmark me-1"></i>Cancel
                                                        </button>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                                    <span class="badge bg-secondary">Awaiting client</span>
                                                    @if($request->canBeCancelled())
                                                        <button
                                                            type="button"
                                                            class="btn btn-outline-dark btn-sm"
                                                            data-action="{{ route('admin.payment-requests.cancel', $request) }}"
                                                            data-client="{{ $request->client?->name ?? 'Client' }}"
                                                            data-amount="${{ number_format($request->amount, 2) }}"
                                                            @click="
                                                                cancelModalOpen = true;
                                                                cancelAction = $el.dataset.action;
                                                                cancelClient = $el.dataset.client;
                                                                cancelAmount = $el.dataset.amount;
                                                                cancelReason = '';
                                                            "
                                                        >
                                                            <i class="fas fa-xmark me-1"></i>Cancel
                                                        </button>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No payment requests found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="work-updates-pagination">
                        {{ $requests->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header border-0 pb-0">
                    <h5 class="mb-0">Request Payment</h5>
                    <small class="text-muted">Send a due notice to a client.</small>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.payment-requests.store') }}">
                        @csrf
                        <div class="mb-3">
                            <x-searchable-select
                                name="client_id"
                                label="Client"
                                placeholder="Select client..."
                                :options="$clients->map(fn($client) => ['value' => $client->id, 'text' => $client->name . ' (' . $client->email . ')'])->toArray()"
                                :value="old('client_id')"
                                required="true"
                            />
                            <p class="mt-2 text-sm text-stone-500">Open to see all clients, or type to search by name or email.</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Amount (USD)</label>
                            <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount') }}" class="form-control" required>
                            @error('amount')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Note (optional)</label>
                        <textarea name="note" rows="3" class="form-control" placeholder="Add details for the client">{{ old('note') }}</textarea>
                        @error('note')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Link (optional)</label>
                        <input type="url" name="payment_link" value="{{ old('payment_link') }}" class="form-control" placeholder="https://example.com/pay">
                        @error('payment_link')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-black w-100">
                        <i class="fas fa-paper-plane me-2"></i>Send Request
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div
        x-cloak
        x-show="rejectModalOpen"
        class="work-update-filter-backdrop"
        @click="rejectModalOpen = false"
    ></div>

    <div
        x-cloak
        x-show="rejectModalOpen"
        x-transition.opacity
        class="payment-reject-modal-shell"
    >
        <div class="payment-reject-modal" @click.outside="rejectModalOpen = false">
            <div class="payment-reject-modal-header">
                <div>
                    <p class="work-updates-eyebrow">Payment Review</p>
                    <h3 class="theme-display mb-0 text-2xl text-stone-950">Reject Payment</h3>
                </div>
                <button type="button" class="work-update-filter-close" @click="rejectModalOpen = false" aria-label="Close rejection form">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="payment-reject-modal-copy">
                <p class="mb-1 text-sm text-stone-700">
                    Tell <strong x-text="rejectClient"></strong> why the payment cannot be approved yet.
                </p>
                <p class="mb-0 text-sm text-stone-500">
                    This reason will appear on the client dashboard and in their notice history for <span x-text="rejectAmount"></span>.
                </p>
            </div>

            <form method="POST" :action="rejectAction" class="payment-reject-form">
                @csrf
                <label for="rejection_reason" class="form-label fw-semibold">Reason</label>
                <textarea
                    id="rejection_reason"
                    name="rejection_reason"
                    class="form-control"
                    rows="4"
                    maxlength="2000"
                    x-model="rejectReason"
                    placeholder="Explain why the payment is being rejected and what the client should correct."
                    required
                ></textarea>

                <div class="payment-reject-actions">
                    <button type="button" class="btn btn-white" @click="rejectModalOpen = false">Cancel</button>
                    <button type="submit" class="btn btn-danger" :disabled="!rejectReason.trim()">
                        <i class="fas fa-ban me-2"></i>Reject Payment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div
        x-cloak
        x-show="cancelModalOpen"
        class="work-update-filter-backdrop"
        @click="cancelModalOpen = false"
    ></div>

    <div
        x-cloak
        x-show="cancelModalOpen"
        x-transition.opacity
        class="payment-reject-modal-shell"
    >
        <div class="payment-reject-modal" @click.outside="cancelModalOpen = false">
            <div class="payment-reject-modal-header">
                <div>
                    <p class="work-updates-eyebrow">Payment Review</p>
                    <h3 class="theme-display mb-0 text-2xl text-stone-950">Cancel Payment Request</h3>
                </div>
                <button type="button" class="work-update-filter-close" @click="cancelModalOpen = false" aria-label="Close cancellation form">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="payment-reject-modal-copy">
                <p class="mb-1 text-sm text-stone-700">
                    Cancel the payment request for <strong x-text="cancelClient"></strong>.
                </p>
                <p class="mb-0 text-sm text-stone-500">
                    Cancelled requests stop showing in the client payment alert. You can leave an optional note for <span x-text="cancelAmount"></span>.
                </p>
            </div>

            <form method="POST" :action="cancelAction" class="payment-reject-form">
                @csrf
                <label for="cancellation_reason" class="form-label fw-semibold">Reason (optional)</label>
                <textarea
                    id="cancellation_reason"
                    name="cancellation_reason"
                    class="form-control"
                    rows="4"
                    maxlength="2000"
                    x-model="cancelReason"
                    placeholder="Add an optional note about why this payment request is being cancelled."
                ></textarea>

                <div class="payment-reject-actions">
                    <button type="button" class="btn btn-white" @click="cancelModalOpen = false">Back</button>
                    <button type="submit" class="btn btn-dark">
                        <i class="fas fa-xmark me-2"></i>Cancel Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .payment-reject-modal-shell {
        position: fixed;
        inset: 0;
        z-index: 1200;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .payment-reject-modal {
        width: min(100%, 32rem);
        border: 1px solid rgba(15, 15, 15, 0.08);
        border-radius: 1.5rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(250, 247, 239, 0.98));
        box-shadow: 0 28px 64px rgba(15, 15, 15, 0.18);
        overflow: hidden;
    }

    .payment-reject-modal-header,
    .payment-reject-modal-copy,
    .payment-reject-form {
        padding-left: 1.2rem;
        padding-right: 1.2rem;
    }

    .payment-reject-modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding-top: 1.2rem;
    }

    .payment-reject-modal-copy {
        padding-top: 0.35rem;
        padding-bottom: 0.95rem;
    }

    .payment-reject-form {
        padding-bottom: 1.2rem;
    }

    .payment-reject-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        margin-top: 1rem;
    }

    @media (max-width: 640px) {
        .payment-reject-actions {
            flex-direction: column-reverse;
        }

        .payment-reject-actions > * {
            width: 100%;
        }
    }
</style>
@endpush
