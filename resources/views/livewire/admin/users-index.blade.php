<div x-data="{ filtersOpen: false }" @keydown.escape.window="filtersOpen = false" class="work-updates-shell">
    <div class="work-updates-toolbar">
        <div>
            <p class="work-updates-eyebrow">Admin Overview</p>
            <h2 class="theme-display work-updates-heading">User Management</h2>
            <p class="work-updates-copy">Manage system accounts with the same shared Livewire filter drawer used across dashboard management pages.</p>
        </div>

        <div class="work-updates-toolbar-actions">
            <a href="{{ route('admin.user-email.index', ['recipient_scope' => 'individual']) }}" class="btn btn-white">
                <i class="fas fa-paper-plane me-2"></i>Custom Email
            </a>
            <a href="{{ route('admin.users.create') }}" class="btn btn-black">
                <i class="fas fa-plus me-2"></i>Add New User
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
        <span><i class="fas fa-users"></i>{{ $users->total() }} results</span>
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
                <div class="work-updates-stat-value">{{ $stats['total'] }}</div>
                <div class="work-updates-stat-label">Total Users</div>
            </div>
        </article>

        <article class="work-updates-stat-card">
            <div class="work-updates-stat-icon">
                <i class="fas fa-user-check"></i>
            </div>
            <div>
                <div class="work-updates-stat-value">{{ $stats['active'] }}</div>
                <div class="work-updates-stat-label">Active Accounts</div>
            </div>
        </article>

        <article class="work-updates-stat-card">
            <div class="work-updates-stat-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <div>
                <div class="work-updates-stat-value">{{ $stats['admins'] }}</div>
                <div class="work-updates-stat-label">Admin Roles</div>
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
                    <h3 class="theme-display mb-0 text-2xl text-stone-950">User Filters</h3>
                </div>
                <button type="button" class="work-update-filter-close" @click="filtersOpen = false" aria-label="Close filters">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="work-update-filter-drawer-body">
                <div class="work-update-filter-group">
                    <label class="form-label" for="admin_user_live_search">Search</label>
                    <input id="admin_user_live_search" type="text" class="form-control" placeholder="Name or email" wire:model.live="search">
                </div>

                <div class="work-update-filter-group">
                    <x-filter-select
                        wire-model="role_id"
                        label="Role"
                        placeholder="All Roles"
                        :options="$roles->map(fn ($role) => ['value' => $role->id, 'text' => ucfirst($role->name)])->values()->all()"
                        searchable
                    />
                </div>

                <div class="work-update-filter-group">
                    <x-filter-select
                        wire-model="status"
                        label="Status"
                        placeholder="All Statuses"
                        :options="[
                            ['value' => 'active', 'text' => 'Active'],
                            ['value' => 'inactive', 'text' => 'Inactive'],
                            ['value' => 'suspended', 'text' => 'Suspended'],
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
        <i class="fas fa-spinner fa-spin me-2"></i>Updating users...
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mobile-data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr wire:key="admin-user-{{ $user->id }}">
                                <td data-label="User" data-stack="true">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                        </div>
                                        <div>
                                            <strong>{{ $user->name }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Email">{{ $user->email }}</td>
                                <td data-label="Role">
                                    <span class="badge bg-primary">{{ $user->role->name ?? 'No Role' }}</span>
                                </td>
                                <td data-label="Status">
                                    <span class="badge {{ $user->status === 'active' ? 'bg-success' : ($user->status === 'suspended' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                        {{ ucfirst($user->status ?? 'active') }}
                                    </span>
                                </td>
                                <td data-label="Created">{{ $user->created_at->format('M j, Y') }}</td>
                                <td data-label="Actions">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if(filled($user->email))
                                            <a href="{{ route('admin.user-email.index', ['recipient_scope' => 'individual', 'user_id' => $user->id]) }}" class="btn btn-sm btn-white" title="Compose Email">
                                                <i class="fas fa-paper-plane"></i>
                                            </a>
                                        @endif
                                        @if(auth()->user()->isSuperAdmin() && !$user->isSuperAdmin())
                                            <a href="{{ route('admin.impersonate', $user) }}" class="btn btn-sm btn-warning" title="Impersonate">
                                                <i class="fas fa-user-secret"></i>
                                            </a>
                                        @endif
                                        @if(!$user->isSuperAdmin() || auth()->user()->isSuperAdmin())
                                            <form method="POST" action="{{ route('admin.users.delete', $user) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">No users found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="work-updates-pagination">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
