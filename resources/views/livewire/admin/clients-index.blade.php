<div x-data="{ filtersOpen: false }" @keydown.escape.window="filtersOpen = false" class="work-updates-shell">
    <div class="work-updates-toolbar">
        <div>
            <p class="work-updates-eyebrow">Admin Overview</p>
            <h2 class="theme-display work-updates-heading">Client Management</h2>
            <p class="work-updates-copy">Review client status, onboarding progress, and active assignments through the same shared Livewire filter drawer.</p>
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
        <span><i class="fas fa-users"></i>{{ $clients->total() }} results</span>
        @if($this->activeFilterCount > 0)
            <span class="work-update-filter-summary-active">Filtered view active</span>
        @endif
    </div>

    <div class="work-updates-stat-grid">
        <article class="work-updates-stat-card">
            <div class="work-updates-stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <div class="work-updates-stat-value">{{ $clients->total() }}</div>
                <div class="work-updates-stat-label">Total Clients</div>
            </div>
        </article>

        <article class="work-updates-stat-card">
            <div class="work-updates-stat-icon">
                <i class="fas fa-link"></i>
            </div>
            <div>
                <div class="work-updates-stat-value">{{ $assignedCount }}</div>
                <div class="work-updates-stat-label">Assigned</div>
            </div>
        </article>

        <article class="work-updates-stat-card">
            <div class="work-updates-stat-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div>
                <div class="work-updates-stat-value">{{ $totalSubmissions }}</div>
                <div class="work-updates-stat-label">Recent Submissions</div>
            </div>
        </article>

        <article class="work-updates-stat-card">
            <div class="work-updates-stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <div class="work-updates-stat-value">{{ $activeServicesCount }}</div>
                <div class="work-updates-stat-label">Active Services</div>
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
                    <h3 class="theme-display mb-0 text-2xl text-stone-950">Client Filters</h3>
                </div>
                <button type="button" class="work-update-filter-close" @click="filtersOpen = false" aria-label="Close filters">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="work-update-filter-drawer-body">
                <div class="work-update-filter-group">
                    <label class="form-label" for="admin_client_live_search">Search</label>
                    <input id="admin_client_live_search" type="text" class="form-control" placeholder="Client name or email" wire:model.live="search">
                </div>

                <div class="work-update-filter-group">
                    <x-filter-select
                        wire-model="service_status"
                        label="Service Status"
                        placeholder="All Service States"
                        :options="[
                            ['value' => 'active', 'text' => 'Active Service'],
                            ['value' => 'expired', 'text' => 'Expired Service'],
                            ['value' => 'completed', 'text' => 'Completed Service'],
                            ['value' => 'unassigned', 'text' => 'Unassigned'],
                        ]"
                    />
                </div>

                <div class="work-update-filter-group">
                    <x-filter-select
                        wire-model="onboarding_status"
                        label="Onboarding"
                        placeholder="All Onboarding States"
                        :options="[
                            ['value' => \App\Models\ClientProfile::ONBOARDING_STATUS_COMPLETED, 'text' => 'Completed'],
                            ['value' => \App\Models\ClientProfile::ONBOARDING_STATUS_PENDING, 'text' => 'Pending'],
                            ['value' => \App\Models\ClientProfile::ONBOARDING_STATUS_REQUESTED_AGAIN, 'text' => 'Requested Again'],
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
        <i class="fas fa-spinner fa-spin me-2"></i>Updating clients...
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-users me-2"></i>Clients Overview
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mobile-data-table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Onboarding</th>
                            <th>Assigned Agent</th>
                            <th>Service End</th>
                            <th>Submissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $client)
                            <tr wire:key="admin-client-{{ $client->id }}">
                                <td data-label="Client" data-stack="true">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                {{ substr($client->name, 0, 1) }}
                                            </div>
                                        </div>
                                        <div>
                                            <strong>{{ $client->name }}</strong>
                                            <br><small class="text-muted d-block d-md-none">{{ $client->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Email">{{ $client->email }}</td>
                                <td data-label="Status">
                                    @if($client->assignment)
                                        @if($client->assignment->isServiceCompleted())
                                            <span class="badge bg-info text-dark">
                                                <i class="fas fa-flag-checkered me-1"></i>Completed
                                            </span>
                                        @elseif(!$client->assignment->service_end_date || $client->assignment->service_end_date > now())
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Active
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="fas fa-exclamation me-1"></i>Expired
                                            </span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-times me-1"></i>Not Assigned
                                        </span>
                                    @endif
                                </td>
                                <td data-label="Onboarding">
                                    @php
                                        $profile = $client->clientProfile;
                                        $onboardingStatus = $profile?->resolvedOnboardingStatus() ?? \App\Models\ClientProfile::ONBOARDING_STATUS_PENDING;
                                    @endphp
                                    <span class="badge bg-{{ $onboardingStatus === \App\Models\ClientProfile::ONBOARDING_STATUS_COMPLETED ? 'success' : ($onboardingStatus === \App\Models\ClientProfile::ONBOARDING_STATUS_REQUESTED_AGAIN ? 'info text-dark' : 'warning text-dark') }}">
                                        {{ $profile?->onboardingStatusLabel() ?? 'Pending' }}
                                    </span>
                                </td>
                                <td data-label="Assigned Agent">
                                    @if($client->assignment && $client->assignment->agent)
                                        {{ $client->assignment->agent->name }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td data-label="Service End" data-stack="true">
                                    @if($client->assignment && $client->assignment->service_end_date)
                                        @if($client->assignment->isServiceCompleted())
                                            <strong>{{ $client->assignment->service_end_date->format('M j, Y') }}</strong>
                                            <br>
                                            <small class="text-success">
                                                Completed {{ optional($client->assignment->service_completed_at)->diffForHumans() }}
                                            </small>
                                        @else
                                            @php
                                                $daysRemaining = rounded_time_value(now()->diffInDays($client->assignment->service_end_date, false));
                                            @endphp
                                            <strong>{{ $client->assignment->service_end_date->format('M j, Y') }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                @if($daysRemaining > 0)
                                                    {{ $daysRemaining }} days left
                                                @elseif($daysRemaining === 0)
                                                    Expires today
                                                @else
                                                    Expired {{ abs($daysRemaining) }} days ago
                                                @endif
                                            </small>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td data-label="Submissions">
                                    <strong>{{ $client->recent_submissions->count() }}</strong>
                                    <br><small class="text-muted">recent</small>
                                </td>
                                <td data-label="Actions">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-sm btn-view" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($client->assignment && !$client->assignment->isServiceCompleted())
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-success"
                                                title="Mark Service Completed"
                                                wire:click="markServiceCompleted({{ $client->id }})"
                                                onclick="if (!confirm('Mark this client service as completed?')) { event.stopImmediatePropagation(); }"
                                            >
                                                <i class="fas fa-flag-checkered"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-5 text-center">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No clients found</h5>
                                    <p class="text-muted mb-0">Adjust your filters or add more client accounts.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="work-updates-pagination">
                {{ $clients->links() }}
            </div>
        </div>
    </div>
</div>
