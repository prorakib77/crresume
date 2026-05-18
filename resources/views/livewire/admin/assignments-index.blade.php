<div x-data="{ filtersOpen: false }" @keydown.escape.window="filtersOpen = false" class="work-updates-shell">
    <div class="work-updates-toolbar">
        <div>
            <p class="work-updates-eyebrow">Admin Overview</p>
            <h2 class="theme-display work-updates-heading">Agent Assignments</h2>
            <p class="work-updates-copy">Review client-agent pairings, service dates, and assignment status with the same shared drawer-based filters.</p>
        </div>

        <div class="work-updates-toolbar-actions">
            <a href="{{ route('admin.assignments.create') }}" class="btn btn-black">
                <i class="fas fa-plus me-2"></i>Create Assignment
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
        <span><i class="fas fa-link"></i>{{ $assignments->total() }} results</span>
        @if($this->activeFilterCount > 0)
            <span class="work-update-filter-summary-active">Filtered view active</span>
        @endif
    </div>

    <div class="work-updates-stat-grid">
        <article class="work-updates-stat-card">
            <div class="work-updates-stat-icon">
                <i class="fas fa-link"></i>
            </div>
            <div>
                <div class="work-updates-stat-value">{{ $stats['total'] }}</div>
                <div class="work-updates-stat-label">Total Assignments</div>
            </div>
        </article>

        <article class="work-updates-stat-card">
            <div class="work-updates-stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <div class="work-updates-stat-value">{{ $stats['active'] }}</div>
                <div class="work-updates-stat-label">Active</div>
            </div>
        </article>

        <article class="work-updates-stat-card">
            <div class="work-updates-stat-icon">
                <i class="fas fa-pause-circle"></i>
            </div>
            <div>
                <div class="work-updates-stat-value">{{ $stats['inactive'] }}</div>
                <div class="work-updates-stat-label">Inactive</div>
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
                    <h3 class="theme-display mb-0 text-2xl text-stone-950">Assignment Filters</h3>
                </div>
                <button type="button" class="work-update-filter-close" @click="filtersOpen = false" aria-label="Close filters">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="work-update-filter-drawer-body">
                <div class="work-update-filter-group">
                    <label class="form-label" for="admin_assignment_live_search">Search</label>
                    <input id="admin_assignment_live_search" type="text" class="form-control" placeholder="Agent or client name/email" wire:model.live="search">
                </div>

                <div class="work-update-filter-group">
                    <x-filter-select
                        wire-model="status"
                        label="Status"
                        placeholder="All Statuses"
                        :options="[
                            ['value' => 'active', 'text' => 'Active'],
                            ['value' => 'inactive', 'text' => 'Inactive'],
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
        <i class="fas fa-spinner fa-spin me-2"></i>Updating assignments...
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mobile-data-table">
                    <thead>
                            <tr>
                                <th>Agent</th>
                                <th>Client</th>
                                <th>Assigned Date</th>
                                <th>Service End Date</th>
                                <th>Min Updates</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $assignment)
                            <tr wire:key="admin-assignment-{{ $assignment->id }}">
                                <td data-label="Agent" data-stack="true">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                {{ $assignment->agent ? substr($assignment->agent->name, 0, 1) : '?' }}
                                            </div>
                                        </div>
                                        <div>
                                            <strong>{{ $assignment->agent ? $assignment->agent->name : 'Unknown Agent' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $assignment->agent ? $assignment->agent->email : 'unknown@example.com' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Client" data-stack="true">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                {{ $assignment->client ? substr($assignment->client->name, 0, 1) : '?' }}
                                            </div>
                                        </div>
                                        <div>
                                            <strong>{{ $assignment->client ? $assignment->client->name : 'Unknown Client' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $assignment->client ? $assignment->client->email : 'unknown@example.com' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Assigned Date">{{ $assignment->assigned_date ? $assignment->assigned_date->format('M j, Y') : 'N/A' }}</td>
                                <td data-label="Service End Date">
                                    @if($assignment->service_end_date)
                                        {{ $assignment->service_end_date->format('M j, Y') }}
                                        @php
                                            $daysRemaining = rounded_time_value(now()->diffInDays($assignment->service_end_date, false));
                                        @endphp
                                        @if($daysRemaining > 0)
                                            <br><small class="text-success">{{ $daysRemaining }} days left</small>
                                        @elseif($daysRemaining === 0)
                                            <br><small class="text-warning">Expires today</small>
                                        @else
                                            <br><small class="text-danger">Expired {{ abs($daysRemaining) }} days ago</small>
                                        @endif
                                    @else
                                        <span class="text-muted">Ongoing</span>
                                    @endif
                                </td>
                                <td data-label="Min Updates">
                                    <span class="badge bg-dark">
                                        {{ max(1, (int) ($assignment->minimum_work_updates ?? \App\Models\AgentClientAssignment::DEFAULT_MINIMUM_WORK_UPDATES)) }}
                                    </span>
                                </td>
                                <td data-label="Status">
                                    <span class="badge {{ $assignment->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $assignment->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td data-label="Actions">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.assignments.edit', $assignment) }}" class="btn btn-sm btn-edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.assignments.destroy', $assignment) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this assignment?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-link fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">No assignments found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="work-updates-pagination">
                {{ $assignments->links() }}
            </div>
        </div>
    </div>
</div>
