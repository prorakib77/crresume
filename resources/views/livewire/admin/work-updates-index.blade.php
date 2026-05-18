@php
    use App\Models\WorkUpdate;
    use Illuminate\Support\Str;
@endphp

<div x-data="{ filtersOpen: false }" @keydown.escape.window="filtersOpen = false" class="work-updates-shell">
    <div class="work-updates-toolbar">
        <div>
            <p class="work-updates-eyebrow">Admin Overview</p>
            <h2 class="theme-display work-updates-heading">All Work Updates</h2>
            <p class="work-updates-copy">Monitor submissions across agents and clients, then export exactly the filtered result set you are looking at.</p>
        </div>

        <div class="work-updates-toolbar-actions">
            <a href="{{ $this->pdfUrl }}" class="btn btn-save">
                <i class="fas fa-file-pdf me-2"></i>PDF
            </a>
            <a href="{{ $this->csvUrl }}" class="btn btn-view">
                <i class="fas fa-file-csv me-2"></i>CSV
            </a>
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
        <span><i class="fas fa-layer-group"></i>{{ $workUpdates->total() }} results</span>
        @if($this->activeFilterCount > 0)
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
        <div class="work-update-filter-drawer-inner">
            <div class="work-update-filter-drawer-header">
                <div>
                    <p class="work-updates-eyebrow">Filter Panel</p>
                    <h3 class="theme-display mb-0 text-2xl text-stone-950">Admin Filters</h3>
                </div>
                <button type="button" class="work-update-filter-close" @click="filtersOpen = false" aria-label="Close filters">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="work-update-filter-drawer-body">
                <div class="work-update-filter-group">
                    <label class="form-label" for="admin_live_search">Search</label>
                    <input id="admin_live_search" type="text" class="form-control" placeholder="Agent, client, company, or title" wire:model.live="search">
                </div>

                <div class="work-update-filter-group">
                    <x-filter-select
                        wire-model="client_id"
                        label="Client"
                        placeholder="All Clients"
                        :options="$clients->map(fn ($client) => ['value' => $client->id, 'text' => $client->name])->values()->all()"
                        searchable
                    />
                </div>

                <div class="work-update-filter-group">
                    <x-filter-select
                        wire-model="agent_id"
                        label="Agent"
                        placeholder="All Agents"
                        :options="$agents->map(fn ($agent) => ['value' => $agent->id, 'text' => $agent->name])->values()->all()"
                        searchable
                    />
                </div>

                <div class="work-update-filter-group">
                    <x-filter-select
                        wire-model="application_status"
                        label="Application Status"
                        placeholder="All Application Statuses"
                        :options="collect(WorkUpdate::getApplicationStatuses())->map(fn ($label, $value) => ['value' => $value, 'text' => $label])->values()->all()"
                    />
                </div>

                <div class="work-update-filter-group">
                    <x-filter-select
                        wire-model="status"
                        label="Submission Status"
                        placeholder="All Submission Statuses"
                        :options="collect(WorkUpdate::getStatuses())->map(fn ($label, $value) => ['value' => $value, 'text' => $label])->values()->all()"
                    />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="work-update-filter-group">
                        <x-filter-date-picker wire-model="date_from" label="Date From" placeholder="Start date" />
                    </div>

                    <div class="work-update-filter-group">
                        <x-filter-date-picker wire-model="date_to" label="Date To" placeholder="End date" />
                    </div>
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
        <i class="fas fa-spinner fa-spin me-2"></i>Updating results...
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mobile-data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Agent</th>
                            <th>Client</th>
                            <th>Job Title</th>
                            <th>Company</th>
                            <th>Application Status</th>
                            <th>Applied Method</th>
                            <th>Submission Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workUpdates as $update)
                            <tr wire:key="admin-work-update-{{ $update->id }}">
                                <td data-label="Applied Date" data-stack="true">
                                    <div>
                                        <strong>{{ ($update->applied_date ?? $update->created_at)->format('M j, Y') }}</strong>
                                        <br>
                                        <small class="text-muted">Submitted {{ $update->created_at->format('g:i A') }}</small>
                                    </div>
                                </td>
                                <td data-label="Agent" data-stack="true">
                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 12px;">
                                                {{ $update->agent ? substr($update->agent->name, 0, 1) : '?' }}
                                            </div>
                                        </div>
                                        <div>
                                            <strong>{{ $update->agent?->name ?? 'Unknown Agent' }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Client" data-stack="true">
                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 12px;">
                                                {{ $update->client ? substr($update->client->name, 0, 1) : '?' }}
                                            </div>
                                        </div>
                                        <div>
                                            <strong>{{ $update->client?->name ?? 'Unknown Client' }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Job Title" data-stack="true">
                                    <strong>{{ $update->job_title }}</strong>
                                    @if($update->note)
                                        <br>
                                        <small class="text-muted">{{ Str::limit($update->note, 50) }}</small>
                                    @endif
                                </td>
                                <td data-label="Company">{{ $update->company }}</td>
                                <td data-label="Application">
                                    <span class="badge bg-{{ $update->application_status === 'hired' ? 'success' : ($update->application_status === 'interview' ? 'warning' : ($update->application_status === 'rejected' ? 'danger' : 'primary')) }}">
                                        {{ $update->getApplicationStatusLabel() }}
                                    </span>
                                </td>
                                <td data-label="Applied Via">
                                    <span class="badge bg-info">{{ $update->getAppliedMethodLabel() }}</span>
                                </td>
                                <td data-label="Submission Status">
                                    <span class="badge bg-{{ $update->status === 'approved' ? 'success' : ($update->status === 'submitted' ? 'warning' : 'secondary') }}">
                                        {{ $update->getStatusLabel() }}
                                    </span>
                                </td>
                                <td data-label="Actions">
                                    <div class="admin-work-update-actions">
                                        @if($update->job_link)
                                            <a href="{{ $update->job_link }}" target="_blank" class="admin-work-update-action" title="View Job">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        @endif
                                        @if($update->job_success_link)
                                            <a href="{{ $update->job_success_link }}" target="_blank" class="admin-work-update-action" title="Success Link">
                                                <i class="fas fa-check-circle"></i>
                                            </a>
                                        @endif
                                        @if(!$update->job_link && !$update->job_success_link)
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-clipboard-list fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">No work updates found for the current filter set.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="work-updates-pagination">
                {{ $workUpdates->links() }}
            </div>
        </div>
    </div>
</div>
