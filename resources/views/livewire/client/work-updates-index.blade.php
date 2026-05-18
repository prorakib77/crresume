@php use Illuminate\Support\Str; @endphp

<div x-data="{ filtersOpen: false }" @keydown.escape.window="filtersOpen = false" class="work-updates-shell client-work-updates-shell">
    <div class="work-updates-toolbar">
        <div>
            <p class="work-updates-eyebrow">Work Updates</p>
            <h2 class="theme-display work-updates-heading" style="font-weight: 600;">My Work Updates</h2>
            <p class="work-updates-copy">Review submitted applications.</p>
        </div>

        <div class="work-updates-toolbar-actions work-updates-toolbar-actions-compact">
            <a href="{{ $this->pdfUrl }}" class="btn btn-save">
                <i class="fas fa-file-pdf"></i>
                <span>PDF</span>
            </a>
            <a href="{{ $this->csvUrl }}" class="btn btn-view">
                <i class="fas fa-file-csv"></i>
                <span>CSV</span>
            </a>
            <button type="button" class="work-update-filter-button" @click="filtersOpen = true">
                <i class="fas fa-filter"></i>
                <span class="work-update-filter-label">Filters</span>
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

    <div class="work-updates-stat-grid work-updates-stat-grid-compact">
        <article class="work-updates-stat-card">
            <div class="work-updates-stat-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div>
                <div class="work-updates-stat-value">{{ $stats['total_updates'] }}</div>
                <div class="work-updates-stat-label">Total Updates</div>
            </div>
        </article>

        <article class="work-updates-stat-card">
            <div class="work-updates-stat-icon">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div>
                <div class="work-updates-stat-value">{{ $stats['this_month'] }}</div>
                <div class="work-updates-stat-label">This Month</div>
            </div>
        </article>

        <article class="work-updates-stat-card">
            <div class="work-updates-stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <div class="work-updates-stat-value">{{ $stats['last_update'] ? $stats['last_update']->created_at->diffForHumans() : 'Never' }}</div>
                <div class="work-updates-stat-label">Last Update</div>
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
                    <label class="form-label" for="client_live_search">Search</label>
                    <input id="client_live_search" type="text" class="form-control" placeholder="Job title, company, or note" wire:model.live="search">
                </div>

                <div class="work-update-filter-group">
                    <x-filter-select
                        wire-model="application_status"
                        label="Application Status"
                        placeholder="All Statuses"
                        :options="collect(\App\Models\WorkUpdate::getApplicationStatuses())->map(fn ($label, $value) => ['value' => $value, 'text' => $label])->values()->all()"
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

    @if($groupedUpdates->isNotEmpty())
        <div class="work-updates-group-list">
            @foreach($groupedUpdates as $date => $updates)
                <section class="work-updates-group-card" wire:key="client-group-{{ $date }}">
                    <div class="work-updates-group-header">
                        <div>
                            <p class="work-updates-group-label">Work Updates</p>
                            <h3 class="work-updates-group-title">{{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}</h3>
                        </div>
                        <span class="work-updates-group-count">{{ $updates->count() }} updates</span>
                    </div>

                    <div class="work-updates-card-grid">
                        @foreach($updates as $update)
                            @php
                                $statusClass = match($update->application_status) {
                                    'hired' => 'status-hired',
                                    'interview' => 'status-interview',
                                    'rejected' => 'status-rejected',
                                    'applied' => 'status-applied',
                                    default => 'status-default',
                                };
                            @endphp

                            <article class="work-update-card" wire:key="client-work-update-{{ $update->id }}">
                                <div class="work-update-card-head">
                                    <div class="min-w-0">
                                        <h4 class="work-update-card-title">{{ $update->job_title }}</h4>
                                        <p class="work-update-card-company">{{ $update->company }}</p>
                                    </div>
                                    <span class="status-pill {{ $statusClass }}">{{ $update->getApplicationStatusLabel() }}</span>
                                </div>

                                <dl class="work-update-card-meta">
                                    <div>
                                        <dt>Applied Via</dt>
                                        <dd>{{ $update->getAppliedMethodLabel() }}</dd>
                                    </div>
                                    <div>
                                        <dt>Applied Date</dt>
                                        <dd>{{ ($update->applied_date ?? $update->created_at)?->format('M j, Y') }}</dd>
                                    </div>
                                    <div>
                                        <dt>Submitted</dt>
                                        <dd>{{ $update->created_at->format('g:i A') }}</dd>
                                    </div>
                                </dl>

                                @if($update->note)
                                    <div class="work-update-card-note">
                                        {{ Str::limit($update->note, 160) }}
                                    </div>
                                @endif

                                <div class="work-update-card-actions">
                                    @if($update->job_link)
                                        <a href="{{ $update->job_link }}" target="_blank" class="btn btn-sm btn-black">
                                            <i class="fas fa-external-link-alt me-1"></i>Job Link
                                        </a>
                                    @endif
                                    @if($update->job_success_link)
                                        <a href="{{ $update->job_success_link }}" target="_blank" class="btn btn-sm btn-save">
                                            <i class="fas fa-check-circle me-1"></i>Success Link
                                        </a>
                                    @endif
                                    <a href="{{ route('client.work-updates.edit', $update->id) }}" class="btn btn-sm btn-white">
                                        <i class="fas fa-pen-to-square me-1"></i>Update Status
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No work updates found</h5>
                <p class="text-muted mb-0">Adjust your filters or check back after your next submission.</p>
            </div>
        </div>
    @endif

    <div class="work-updates-pagination">
        {{ $workUpdates->links() }}
    </div>
</div>
