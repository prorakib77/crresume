<div x-data="{ filtersOpen: false }" @keydown.escape.window="filtersOpen = false" class="work-updates-shell">
    <div class="work-updates-toolbar">
        <div>
            <p class="work-updates-eyebrow">Admin Overview</p>
            <h2 class="theme-display work-updates-heading">Agent Management</h2>
            <p class="work-updates-copy">Monitor agent activity, check-in progress, and daily traffic with the same quick filter drawer used across dashboard lists.</p>
        </div>

        <div class="work-updates-toolbar-actions">
            <a href="{{ route('admin.agents.daily-report') }}" class="btn btn-view">
                <i class="fas fa-chart-bar me-2"></i>Daily Report
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
        <span><i class="fas fa-users"></i>{{ $agents->total() }} results</span>
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
                <div class="work-updates-stat-value">{{ $agents->total() }}</div>
                <div class="work-updates-stat-label">Total Agents</div>
            </div>
        </article>

        <article class="work-updates-stat-card">
            <div class="work-updates-stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <div class="work-updates-stat-value">{{ $activeTodayCount }}</div>
                <div class="work-updates-stat-label">Active Today</div>
            </div>
        </article>

        <article class="work-updates-stat-card">
            <div class="work-updates-stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <div class="work-updates-stat-value">{{ $checkedInCount }}</div>
                <div class="work-updates-stat-label">Checked In</div>
            </div>
        </article>

        <article class="work-updates-stat-card">
            <div class="work-updates-stat-icon">
                <i class="fas fa-eye"></i>
            </div>
            <div>
                <div class="work-updates-stat-value">{{ $totalPageViews }}</div>
                <div class="work-updates-stat-label">Page Views Today</div>
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
                    <h3 class="theme-display mb-0 text-2xl text-stone-950">Agent Filters</h3>
                </div>
                <button type="button" class="work-update-filter-close" @click="filtersOpen = false" aria-label="Close filters">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="work-update-filter-drawer-body">
                <div class="work-update-filter-group">
                    <label class="form-label" for="admin_agent_live_search">Search</label>
                    <input id="admin_agent_live_search" type="text" class="form-control" placeholder="Agent name or email" wire:model.live="search">
                </div>

                <div class="work-update-filter-group">
                    <x-filter-select
                        wire-model="activity_status"
                        label="Activity State"
                        placeholder="All Activity States"
                        :options="[
                            ['value' => 'active_today', 'text' => 'Active Today'],
                            ['value' => 'inactive_today', 'text' => 'No Activity Today'],
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
        <i class="fas fa-spinner fa-spin me-2"></i>Updating agents...
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-users me-2"></i>Agents Overview
            </h5>
        </div>
        <div class="card-body">
            @if($agents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mobile-data-table">
                        <thead>
                            <tr>
                                <th>Agent</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Work Hours</th>
                                <th>Page Views</th>
                                <th>Last Activity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($agents as $agent)
                                <tr wire:key="admin-agent-{{ $agent->id }}">
                                    <td data-label="Agent" data-stack="true">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    {{ substr($agent->name, 0, 1) }}
                                                </div>
                                            </div>
                                            <div>
                                                <strong>{{ $agent->name }}</strong>
                                                <br><small class="text-muted d-block d-md-none">{{ $agent->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="Email">{{ $agent->email }}</td>
                                    <td data-label="Status">
                                        @if(($agent->work_hours['total_hours'] ?? 0) > 0)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Checked In
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock me-1"></i>Not Checked In
                                            </span>
                                        @endif
                                    </td>
                                    <td data-label="Work Hours">
                                        <strong>{{ number_format($agent->work_hours['total_hours'] ?? 0, 1) }}h</strong>
                                        <br><small class="text-muted">{{ $agent->work_hours['total_minutes'] ?? 0 }}m</small>
                                    </td>
                                    <td data-label="Page Views">
                                        <strong>{{ $agent->page_visits->count() }}</strong>
                                        <br><small class="text-muted">views today</small>
                                    </td>
                                    <td data-label="Last Activity">
                                        @if($agent->today_activities->count() > 0)
                                            <small class="text-muted">{{ $agent->today_activities->first()->activity_time->format('H:i') }}</small>
                                        @else
                                            <small class="text-muted">No activity</small>
                                        @endif
                                    </td>
                                    <td data-label="Actions">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.agents.show', $agent) }}" class="btn btn-sm btn-view" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="work-updates-pagination">
                    {{ $agents->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No agents found</h5>
                    <p class="text-muted mb-0">Adjust your filters or add more agent accounts.</p>
                </div>
            @endif
        </div>
    </div>
</div>
