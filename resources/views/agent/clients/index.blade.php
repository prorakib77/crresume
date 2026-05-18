<x-app-layout>
    <x-slot name="title">My Assigned Clients</x-slot>
    <x-slot name="pageTitle">My Assigned Clients</x-slot>
    <x-slot name="pageSubtitle">Filter your client list, review service details, and request verification codes</x-slot>

    @php
        $activeFilterCount = collect($filters ?? [])
            ->filter(fn ($value) => trim((string) $value) !== '')
            ->count();
        $hasActiveFilters = $activeFilterCount > 0;
    @endphp

    <div x-data="{ filtersOpen: false }" @keydown.escape.window="filtersOpen = false" class="work-updates-shell agent-clients-shell">
        <div class="work-updates-toolbar">
            <div>
                <p class="work-updates-eyebrow">Agent Workspace</p>
                <h2 class="theme-display work-updates-heading">My Clients</h2>
                <p class="work-updates-copy">Review your assigned clients, filter by service status or type, and request verification codes from one place.</p>
            </div>

            <div class="work-updates-toolbar-actions">
                @if($hasActiveFilters)
                    <a href="{{ route('agent.clients.index') }}" class="btn btn-white">
                        <i class="fas fa-rotate-left me-2"></i>Reset
                    </a>
                @endif
                <button type="button" class="work-update-filter-button" @click="filtersOpen = true">
                    <i class="fas fa-filter"></i>
                    <span>Filters</span>
                    @if($activeFilterCount > 0)
                        <span class="work-update-filter-badge">{{ $activeFilterCount }}</span>
                    @endif
                </button>
            </div>
        </div>

        <div class="work-update-filter-summary">
            <span><i class="fas fa-users"></i>{{ $assignedClients->count() }} results</span>
            @if($hasActiveFilters)
                <span class="work-update-filter-summary-active">Filtered view active</span>
            @endif
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
            <form method="GET" action="{{ route('agent.clients.index') }}" class="work-update-filter-drawer-inner">
                <div class="work-update-filter-drawer-header">
                    <div>
                        <p class="work-updates-eyebrow">Filter Panel</p>
                        <h3 class="theme-display mb-0 text-2xl text-stone-950">My Client Filters</h3>
                    </div>
                    <button type="button" class="work-update-filter-close" @click="filtersOpen = false" aria-label="Close filters">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="work-update-filter-drawer-body">
                    <div class="work-update-filter-group">
                        <label class="form-label" for="agent_client_search">Search</label>
                        <input
                            id="agent_client_search"
                            type="text"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            class="form-control"
                            placeholder="Client name or email"
                        >
                    </div>

                    <div class="work-update-filter-group">
                        <label class="form-label" for="agent_client_status">Service Status</label>
                        <select id="agent_client_status" name="service_status" class="form-select">
                            <option value="">All Service Statuses</option>
                            <option value="active" @selected(($filters['service_status'] ?? '') === 'active')>Active</option>
                            <option value="expired" @selected(($filters['service_status'] ?? '') === 'expired')>Expired</option>
                            <option value="completed" @selected(($filters['service_status'] ?? '') === 'completed')>Completed</option>
                            <option value="inactive" @selected(($filters['service_status'] ?? '') === 'inactive')>Inactive</option>
                        </select>
                    </div>

                    <div class="work-update-filter-group">
                        <label class="form-label" for="agent_client_type">Service Type</label>
                        <select id="agent_client_type" name="service_type" class="form-select">
                            <option value="">All Service Types</option>
                            <option value="{{ \App\Models\ClientProfile::SERVICE_TYPE_REGULAR }}" @selected(($filters['service_type'] ?? '') === \App\Models\ClientProfile::SERVICE_TYPE_REGULAR)>Regular</option>
                            <option value="{{ \App\Models\ClientProfile::SERVICE_TYPE_VIP }}" @selected(($filters['service_type'] ?? '') === \App\Models\ClientProfile::SERVICE_TYPE_VIP)>VIP</option>
                        </select>
                    </div>
                </div>

                <div class="work-update-filter-drawer-footer">
                    <a href="{{ route('agent.clients.index') }}" class="btn btn-white w-100">
                        <i class="fas fa-rotate-left me-2"></i>Reset Filters
                    </a>
                    <button type="submit" class="btn btn-black w-100">
                        <i class="fas fa-check me-2"></i>Apply Filters
                    </button>
                </div>
            </form>
        </aside>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i>Assigned Clients
                            <span class="badge bg-primary ms-2">{{ $assignedClients->count() }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($assignedClients->count() > 0)
                            <div class="row">
                                @foreach($assignedClients as $client)
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card h-100 client-card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="avatar bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $client->name }}</h6>
                                                        <small class="text-muted">{{ $client->email }}</small>
                                                    </div>
                                                </div>

                                                @if($client->clientProfile)
                                                    <div class="mb-3">
                                                        <small class="text-muted">Service Details:</small>
                                                        <div class="mt-2 d-flex flex-wrap gap-2">
                                                            <span class="badge {{ ($client->clientProfile->service_type ?? \App\Models\ClientProfile::SERVICE_TYPE_REGULAR) === \App\Models\ClientProfile::SERVICE_TYPE_VIP ? 'bg-dark text-white' : 'bg-info text-dark' }}">
                                                                {{ $client->clientProfile->serviceTypeLabel() }}
                                                            </span>
                                                            @if($client->clientProfile->service_package)
                                                                <span class="badge bg-primary">
                                                                    {{ ucwords(str_replace('-', ' ', $client->clientProfile->service_package)) }}
                                                                </span>
                                                            @endif
                                                            @php
                                                                $serviceStatusClass = match($client->service_status_key) {
                                                                    'active' => 'bg-success',
                                                                    'expired' => 'bg-danger',
                                                                    'completed' => 'bg-info text-dark',
                                                                    default => 'bg-secondary',
                                                                };
                                                            @endphp
                                                            <span class="badge {{ $serviceStatusClass }}">
                                                                {{ $client->service_status_label }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if($client->workUpdates->count() > 0)
                                                    <div class="mb-3">
                                                        <small class="text-muted">Latest Update:</small>
                                                        <div class="mt-1">
                                                            <span class="badge bg-success">
                                                                {{ $client->workUpdates->first()->created_at->format('M j, Y') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="d-grid gap-2">
                                                    <a href="{{ route('agent.clients.show', $client) }}"
                                                       class="btn btn-view btn-sm btn_white">
                                                        <i class="fas fa-eye me-2"></i>View Client Details
                                                    </a>

                                                    <button type="button"
                                                            class="btn btn-border-black btn-sm request-otp-btn"
                                                            data-client-id="{{ $client->id }}"
                                                            data-client-name="{{ $client->name }}">
                                                        <i class="fas fa-key me-2"></i>Request OTP
                                                    </button>

                                                    @if(($client->service_status_key ?? '') === 'active')
                                                        <a href="{{ route('agent.work-updates.create', ['client_id' => $client->getRouteKey()]) }}"
                                                           class="btn btn-create btn-sm">
                                                            <i class="fas fa-plus me-2"></i>Submit Work Update
                                                        </a>
                                                    @else
                                                        <button type="button" class="btn btn-light btn-sm" disabled>
                                                            <i class="fas fa-lock me-2"></i>Work Updates Locked
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                @if($hasActiveFilters)
                                    <h5 class="text-muted">No Clients Match These Filters</h5>
                                    <p class="text-muted mb-3">Try adjusting your filter selections or clear them to see your full assigned client list.</p>
                                    <a href="{{ route('agent.clients.index') }}" class="btn btn-border-black">
                                        <i class="fas fa-rotate-left me-2"></i>Clear Filters
                                    </a>
                                @else
                                    <h5 class="text-muted">No Clients Assigned</h5>
                                    <p class="text-muted">You don't have any clients assigned to you yet.</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- OTP Request Modal -->
    <div id="otpRequestModal" class="custom-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="otpRequestModalTitle">
        <div class="custom-modal-overlay" onclick="closeOtpRequestModal()"></div>
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <h5 class="custom-modal-title" id="otpRequestModalTitle">
                    <i class="fas fa-key me-2"></i>Request Verification Code
                </h5>
                <button type="button" class="custom-modal-close" onclick="closeOtpRequestModal()">&times;</button>
            </div>
            <form id="otpRequestForm">
                <div class="custom-modal-body">
                    <div class="mb-3">
                        <label class="form-label">Client</label>
                        <input type="text" class="form-control" id="clientName" readonly>
                        <input type="hidden" id="clientId" name="client_id">
                    </div>

                    <div class="mb-3">
                        <label for="message" class="form-label">Additional Message (Optional)</label>
                        <textarea class="form-control" id="message" name="message" rows="3"
                                  placeholder="Add any additional context or instructions for the client..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="expiry_minutes" class="form-label">OTP Expiry Time (Minutes)</label>
                        <input
                            type="number"
                            class="form-control"
                            id="expiry_minutes"
                            name="expiry_minutes"
                            min="1"
                            max="10080"
                            step="1"
                            value="10"
                            required
                        >
                        <small class="text-muted">Set how long this verification code will remain valid.</small>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> This will send an email to the client requesting a verification code.
                        The client will receive a link to submit their OTP with your selected expiry time.
                    </div>
                </div>
                <div class="custom-modal-footer">
                    <button type="button" class="btn btn-white" onclick="closeOtpRequestModal()">Cancel</button>
                    <button type="submit" class="btn btn-black" id="submitOtpRequest">
                        <i class="fas fa-paper-plane me-2"></i>Send OTP Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const requestOtpBtns = document.querySelectorAll('.request-otp-btn');
            const modal = document.getElementById('otpRequestModal');
            const form = document.getElementById('otpRequestForm');
            const submitBtn = document.getElementById('submitOtpRequest');
            const clientNameInput = document.getElementById('clientName');
            const clientIdInput = document.getElementById('clientId');

            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }

            requestOtpBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const clientId = this.getAttribute('data-client-id');
                    const clientName = this.getAttribute('data-client-name');

                    clientIdInput.value = clientId;
                    clientNameInput.value = clientName;

                    openOtpRequestModal();
                });
            });

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                const submitButton = submitBtn;

                // Disable submit button and show loading
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';

                fetch('{{ route("agent.request-otp") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        client_id: formData.get('client_id'),
                        message: formData.get('message'),
                        expiry_minutes: Number(formData.get('expiry_minutes') || 10),
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showAlert('success', data.message);
                        closeOtpRequestModal();
                    } else {
                        showAlert('danger', data.message || 'Failed to send OTP request');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('danger', 'An error occurred while sending the OTP request');
                })
                .finally(() => {
                    // Re-enable submit button
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send OTP Request';
                });
            });

            function showAlert(type, message) {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
                alertDiv.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;

                // Insert at the top of the page
                const container = document.querySelector('.card-body');
                container.insertBefore(alertDiv, container.firstChild);

                // Auto-remove after 5 seconds
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.parentNode.removeChild(alertDiv);
                    }
                }, 5000);
            }

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                    closeOtpRequestModal();
                }
            });
        });
    </script>
    @endpush

    <style>
        .btn_white:hover{
            color: #374151!important   ;
        }
        .client-card {
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out, background-color 0.2s ease-in-out;
            border: 1px solid #e9ecef;
        }

        .avatar {
            font-size: 1.2rem;
        }

        .request-otp-btn {
            transition: transform 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        }

        .request-otp-btn:hover {
            transform: scale(1.02);
        }

        /* Custom Modal Styles */
        .custom-modal {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
        }

        .custom-modal.is-open {
            display: flex;
        }

        .custom-modal-overlay {
            position: absolute;
            inset: 0;
            background-color: rgba(15, 23, 42, 0.58);
            backdrop-filter: blur(4px);
            z-index: 1;
        }

        .custom-modal-content {
            position: relative;
            background: white;
            border-radius: 1.25rem;
            box-shadow: 0 28px 60px rgba(15, 23, 42, 0.2);
            z-index: 2;
            width: min(100%, 34rem);
            max-height: calc(100vh - 2.5rem);
            overflow-y: auto;
            border: 1px solid rgba(226, 232, 240, 0.92);
        }

        .custom-modal-header {
            padding: 1.15rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .custom-modal-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .custom-modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
            padding: 0;
            width: 2.25rem;
            min-width: 2.25rem;
            height: 2.25rem;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .custom-modal-close:hover {
            color: #374151;
            background: #f3f4f6;
        }

        .custom-modal-body {
            padding: 1.5rem;
        }

        .custom-modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        body.otp-request-modal-open {
            overflow: hidden;
        }

        @media (max-width: 767.98px) {
            .custom-modal {
                padding: 0.85rem;
                align-items: center;
            }

            .custom-modal-content {
                width: 100%;
                max-height: calc(100vh - 1.7rem);
                border-radius: 1rem;
            }

            .custom-modal-header,
            .custom-modal-body,
            .custom-modal-footer {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .custom-modal-title {
                font-size: 1.05rem;
            }

            .custom-modal-footer {
                flex-direction: column-reverse;
            }

            .custom-modal-footer .btn {
                width: 100%;
            }
        }
    </style>

    <script>
        function openOtpRequestModal() {
            const modal = document.getElementById('otpRequestModal');

            if (!modal) {
                return;
            }

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('otp-request-modal-open');
        }

        function closeOtpRequestModal() {
            const modal = document.getElementById('otpRequestModal');
            const form = document.getElementById('otpRequestForm');

            if (!modal) {
                return;
            }

            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('otp-request-modal-open');

            if (form) {
                form.reset();
            }
        }
    </script>
</x-app-layout>
