@php
$currentUser = auth()->user();
$appName = setting('general.app_name', 'W Automation');
$logoPath = setting('ui.logo_path');
@endphp

<div class="flex flex-col h-full bg-white border-r border-gray-200">
    <!-- Logo/Brand -->
    <div class="flex items-center justify-center h-16 px-4 border-b border-gray-200">
        <div class="flex items-center space-x-3">
            @if($logoPath && Storage::disk('public')->exists($logoPath))
                <img src="{{ storage_public_url($logoPath) }}" alt="{{ $appName }}" class="h-8 w-8 rounded">
            @else
                <div class="h-8 w-8 bg-gradient-to-br from-primary-500 to-primary-700 rounded-lg flex items-center justify-center">
                    <i class="fas fa-cogs text-white text-sm"></i>
                </div>
            @endif
            <span class="text-lg font-bold text-gray-900 truncate">{{ $appName }}</span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        <!-- Dashboard -->
        <div class="mb-6">
            @if($currentUser->isAdmin())
                <a href="{{ route('admin.dashboard') }}"
                   class="nav-link {{ request()->routeIs('admin.dashboard') ? 'nav-link-active' : 'nav-link-inactive' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Admin Dashboard</span>
                </a>
            @elseif($currentUser->isAgent())
                <a href="{{ route('dashboard') }}"
                   class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : 'nav-link-inactive' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Agent Dashboard</span>
                </a>
            @else
                <a href="{{ route('client.dashboard') }}"
                   class="nav-link {{ request()->routeIs('client.dashboard') ? 'nav-link-active' : 'nav-link-inactive' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('client.work-updates.index') }}"
                   class="nav-link {{ request()->routeIs('client.work-updates.*') ? 'nav-link-active' : 'nav-link-inactive' }}">
                    <i class="fas fa-briefcase"></i>
                    <span>Work Updates</span>
                </a>
                <a href="{{ route('client.otp-requests.index') }}"
                   class="nav-link {{ request()->routeIs('client.otp-requests.*') ? 'nav-link-active' : 'nav-link-inactive' }}">
                    <i class="fas fa-key"></i>
                    <span>Verification Codes</span>
                </a>
            @endif
        </div>

        <!-- Admin Section -->
        @if($currentUser->hasAnyPermission(['manage-users', 'manage-roles', 'view-admin-panel']))
            <div class="mb-6">
                <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                    Administration
                </h3>

                @if($currentUser->hasPermission('manage-roles'))
                    <!-- Roles & Permissions -->
                    <div x-data="{ open: {{ request()->routeIs('roles.*') || request()->routeIs('roles_permission') ? 'true' : 'false' }} }">
                        <button @click="open = !open"
                                class="w-full nav-link-inactive group flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-shield-alt"></i>
                                <span>Roles & Permissions</span>
                            </div>
                            <i class="fas fa-chevron-right transition-transform" :class="{ 'rotate-90': open }"></i>
                        </button>
                        <div x-show="open" x-collapse class="ml-6 mt-1 space-y-1">
                            <a href="{{ route('roles_permission') }}"
                               class="nav-link {{ request()->routeIs('roles_permission') ? 'nav-link-active' : 'nav-link-inactive' }} text-sm">
                                <i class="fas fa-eye"></i>
                                <span>View All</span>
                            </a>
                            <a href="{{ route('roles.index') }}"
                               class="nav-link {{ request()->routeIs('roles.*') ? 'nav-link-active' : 'nav-link-inactive' }} text-sm">
                                <i class="fas fa-users-cog"></i>
                                <span>Manage Roles</span>
                            </a>
                        </div>
                    </div>
                @endif

                @if($currentUser->hasPermission('manage-users'))
                    <!-- User Management -->
                    <a href="{{ route('users.index') }}"
                       class="nav-link {{ request()->routeIs('users.*') ? 'nav-link-active' : 'nav-link-inactive' }}">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                @endif

                @if($currentUser->hasPermission('manage-settings'))
                    <!-- System Settings -->
                    <a href="{{ route('settings.index') }}"
                       class="nav-link {{ request()->routeIs('settings.*') ? 'nav-link-active' : 'nav-link-inactive' }}">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                @endif
            </div>
        @endif

        <!-- Client Management -->
        @if($currentUser->hasAnyPermission(['manage-users', 'view-clients']))
            <div class="mb-6">
                <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                    Client Management
                </h3>

                @if($currentUser->hasPermission('manage-users'))
                    <a href="{{ route('clients.index') }}"
                       class="nav-link {{ request()->routeIs('clients.*') ? 'nav-link-active' : 'nav-link-inactive' }}">
                        <i class="fas fa-user-tie"></i>
                        <span>Clients</span>
                    </a>
                @endif

                @if($currentUser->hasPermission('assign-agent'))
                    <!-- Agent Assignments -->
                    <div x-data="{ open: {{ request()->routeIs('agents.*') || request()->routeIs('assign.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open"
                                class="w-full nav-link-inactive group flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-link"></i>
                                <span>Assignments</span>
                            </div>
                            <i class="fas fa-chevron-right transition-transform" :class="{ 'rotate-90': open }"></i>
                        </button>
                        <div x-show="open" x-collapse class="ml-6 mt-1 space-y-1">
                            <a href="{{ route('agents.assign') }}"
                               class="nav-link {{ request()->routeIs('agents.assign') ? 'nav-link-active' : 'nav-link-inactive' }} text-sm">
                                <i class="fas fa-list"></i>
                                <span>View All</span>
                            </a>
                            <a href="{{ route('assign.form') }}"
                               class="nav-link {{ request()->routeIs('assign.form') ? 'nav-link-active' : 'nav-link-inactive' }} text-sm">
                                <i class="fas fa-plus"></i>
                                <span>New Assignment</span>
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Work Management -->
        @if($currentUser->isAgent() || $currentUser->hasAnyPermission(['view-work-updates', 'manage-work-updates']))
            <div class="mb-6">
                <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                    Work Management
                </h3>

                @if($currentUser->isAgent())
                    <a href="{{ route('agent.clients.index') }}"
                       class="nav-link {{ request()->routeIs('agent.clients*') ? 'nav-link-active' : 'nav-link-inactive' }}">
                        <i class="fas fa-user-friends"></i>
                        <span>My Clients</span>
                    </a>
                    <a href="{{ route('agent.work-updates.index') }}"
                       class="nav-link {{ request()->routeIs('agent.work-updates.index') ? 'nav-link-active' : 'nav-link-inactive' }}">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Work History</span>
                    </a>
                    <a href="{{ route('agent.work-updates.drafts') }}"
                       class="nav-link {{ request()->routeIs('agent.work-updates.drafts*') ? 'nav-link-active' : 'nav-link-inactive' }}">
                        <i class="fas fa-save"></i>
                        <span>Drafts</span>
                    </a>
                    <a href="{{ route('agent.work-updates.create') }}"
                       class="nav-link {{ request()->routeIs('agent.work-updates.create') ? 'nav-link-active' : 'nav-link-inactive' }}">
                        <i class="fas fa-plus-circle"></i>
                        <span>Submit Update</span>
                    </a>
                    <a href="{{ route('agent.submissions.index') }}"
                       class="nav-link {{ request()->routeIs('agent.submissions.*') ? 'nav-link-active' : 'nav-link-inactive' }}">
                        <i class="fas fa-key"></i>
                        <span>OTP Codes</span>
                    </a>
                @elseif($currentUser->hasPermission('view-work-updates'))
                    <a href="{{ route('workUpdates.index') }}"
                       class="nav-link {{ request()->routeIs('workUpdates.*') ? 'nav-link-active' : 'nav-link-inactive' }}">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Work Updates</span>
                    </a>
                @endif
            </div>
        @endif

        <!-- Analytics & Reports -->
        @if($currentUser->hasAnyPermission(['view-analytics', 'view-reports']))
            <div class="mb-6">
                <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                    Analytics
                </h3>

                @if($currentUser->hasPermission('view-reports'))
                    <a href="#" onclick="alert('Reports module coming soon!')"
                       class="nav-link">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports</span>
                    </a>
                @endif

                @if($currentUser->hasPermission('view-analytics'))
                    <a href="#" onclick="alert('Analytics module coming soon!')"
                       class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        <span>Analytics</span>
                    </a>
                @endif
            </div>
        @endif

        <!-- Quick Actions -->
        @if($currentUser->isClient())
            <div class="mb-6">
                <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                    My Account
                </h3>

                <a href="{{ route('profile.edit') }}"
                   class="nav-link {{ request()->routeIs('profile.*') ? 'nav-link-active' : 'nav-link-inactive' }}">
                    <i class="fas fa-user-edit"></i>
                    <span>Profile</span>
                </a>

                <a href="{{ route('client.status') }}"
                   class="nav-link {{ request()->routeIs('client.status') ? 'nav-link-active' : 'nav-link-inactive' }}">
                    <i class="fas fa-info-circle"></i>
                    <span>Status</span>
                </a>
            </div>
        @endif
    </nav>

    <!-- Footer -->
    <div class="p-4 border-t border-gray-200">
        <div class="text-xs text-gray-500 text-center">
            <div class="mb-1">{{ $appName }}</div>
            <div>© {{ date('Y') }} All rights reserved</div>
        </div>
    </div>
</div>
