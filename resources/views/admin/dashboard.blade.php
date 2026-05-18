<x-app-layout>
    <x-slot name="title">Admin Dashboard</x-slot>
    <x-slot name="pageTitle">Admin Dashboard</x-slot>
    <x-slot name="pageSubtitle">Welcome back, {{ auth()->user()->name }}</x-slot>

    @if(session('admin_pass_key_verified'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-key me-2"></i>
            <strong>Admin Access via Pass Key</strong> - You are accessing admin features with a temporary pass key.
            <form method="POST" action="{{ route('admin.passkey.revoke') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-border-black ms-2">
                    <i class="fas fa-times me-1"></i>Revoke Access
                </button>
            </form>
        </div>
    @endif

    <!-- Admin Pass Key Management -->
    @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('super-admin'))
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-key me-2"></i>Admin Pass Key Management
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <p class="mb-2">Manage admin access and user impersonation. Change the pass key or login as any user in the system.</p>
                                <small class="text-muted">Use admin pass key to login as any user or change the pass key for security.</small>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="{{ route('admin.passkey.change') }}" class="btn btn-warning me-2">
                                    <i class="fas fa-edit me-2"></i>Change Pass Key
                                </a>
                                <a href="{{ route('admin.login-as-user') }}" class="btn btn-danger">
                                    <i class="fas fa-user-secret me-2"></i>Login as User
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-black me-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="stats-number">{{ $stats['total_users'] }}</div>
                        <div class="stats-label">Total Users</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-black me-3">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div>
                        <div class="stats-number">{{ $stats['total_agents'] }}</div>
                        <div class="stats-label">Agents</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-black me-3">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div>
                        <div class="stats-number">{{ $stats['total_clients'] }}</div>
                        <div class="stats-label">Clients</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-black me-3">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div>
                        <div class="stats-number">{{ $stats['total_work_updates'] }}</div>
                        <div class="stats-label">Work Updates</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily/Upcoming Agent Meeting -->
    @if($todayMeeting)
    @php $isTodayMeeting = $todayMeeting->date->isToday(); @endphp
    <div class="row mb-4">
        <div class="col-12">
            <div class="card" style="border-top-left-radius: 15px !important; border-top-right-radius: 15px !important;">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-video me-2"></i>{{ $isTodayMeeting ? "Today's Agent Meeting" : 'Next Scheduled Meeting' }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-2">{{ $todayMeeting->title }}</h6>
                            <p class="text-muted mb-2">
                                <i class="fas fa-clock me-1"></i>
                                {{ optional($todayMeeting->start_time)->format('g:i A') }} - {{ optional($todayMeeting->end_time)->format('g:i A') }}
                            </p>
                            <p class="text-muted mb-2">
                                <i class="fas fa-calendar me-1"></i>
                                {{ $todayMeeting->date->format('M d, Y') }}
                            </p>
                            <p class="text-muted mb-0">
                                <i class="fas fa-link me-1"></i>
                                <small>{{ $todayMeeting->meet_link }}</small>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ $todayMeeting->meet_link }}"
                               target="_blank"
                               class="btn btn-success btn-lg mb-2">
                                <i class="fas fa-video me-2"></i>Join Meeting
                            </a>
                            <br>
                            <!-- OAuth Controls -->
                            @if(!session('google_access_token'))
                                <a href="{{ route('google.oauth.redirect') }}" class="btn btn-info btn-sm me-2 mb-2">
                                    <i class="fas fa-google me-1"></i>Connect Google
                                </a>
                            @else
                                <button onclick="createOAuthMeetRoom()" class="btn btn-primary btn-sm me-2 mb-2">
                                    <i class="fas fa-plus me-1"></i>Create OAuth Meeting
                                </button>
                                <a href="{{ route('google.oauth.disconnect') }}" class="btn btn-secondary btn-sm me-2 mb-2">
                                    <i class="fas fa-unlink me-1"></i>Disconnect
                                </a>
                            @endif

                            <button onclick="generateMeeting()" class="btn btn-info btn-sm me-2">
                                <i class="fas fa-sync me-1"></i>Generate New
                            </button>
                            <a href="{{ route('admin.meeting-export') }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-download me-1"></i>Export Report
                            </a>
                            <br>
                            <small class="text-success mt-2 d-block">
                                <i class="fas fa-check-circle me-1"></i>
                                Open meeting - agents join directly with registered email
                            </small>
                            <small class="text-info mt-1 d-block">
                                <i class="fas fa-crown me-1"></i>
                                Permanent Host: {{ env('GOOGLE_ADMIN_EMAIL', 'caliroweteam@caliwfhresumes.com') }}
                            </small>
                            <small class="text-warning mt-1 d-block">
                                <i class="fas fa-info-circle me-1"></i>
                                You remain host even if joining later
                            </small>
                            <small class="text-info mt-1 d-block">
                                <i class="fas fa-users me-1"></i>
                                Everyone joins the same meeting
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Meeting Statistics -->
    @if(isset($meetingStats) && !empty($meetingStats))
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title text-success">{{ $meetingStats['total_agents'] ?? 0 }}</h5>
                    <p class="card-text">Total Agents</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title text-info">{{ $meetingStats['joined_agents'] ?? 0 }}</h5>
                    <p class="card-text">Joined Today</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title text-warning">{{ $meetingStats['total_duration'] ?? 0 }}m</h5>
                    <p class="card-text">Total Duration</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title text-primary">{{ round($meetingStats['average_duration'] ?? 0, 1) }}m</h5>
                    <p class="card-text">Avg Duration</p>
                </div>
            </div>
        </div>
    </div>
    @endif
    @else
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>No Meeting Scheduled
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">No daily meeting has been scheduled for today. Generate a meeting link using the command: <code>php artisan meet:generate-daily</code></p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Quick Actions -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="{{ route('admin.users.create') }}" class="btn btn-create w-100">
                                <i class="fas fa-user-plus d-block mb-1"></i>
                                <small>Add User</small>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('admin.assignments.create') }}" class="btn btn-create w-100">
                                <i class="fas fa-link d-block mb-1"></i>
                                <small>Assign Agent</small>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('admin.assignments') }}" class="btn btn-view w-100">
                                <i class="fas fa-list d-block mb-1"></i>
                                <small>View Assignments</small>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('admin.work-updates') }}" class="btn btn-white w-100">
                                <i class="fas fa-clipboard d-block mb-1"></i>
                                <small>Work Updates</small>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('admin.meeting-dashboard') }}" class="btn btn-success w-100">
                                <i class="fas fa-chalkboard-teacher d-block mb-1"></i>
                                <small>Agent Virtual Room</small>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('admin.meeting-reports') }}" class="btn btn-info w-100">
                                <i class="fas fa-chart-line d-block mb-1"></i>
                                <small>Meeting Reports</small>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('admin.oauth.index') }}" class="btn btn-warning w-100">
                                <i class="fas fa-cog d-block mb-1"></i>
                                <small>OAuth Settings</small>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('admin.meeting-test') }}" class="btn btn-warning w-100">
                                <i class="fas fa-video d-block mb-1"></i>
                                <small>Test System</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clock me-2"></i>Recent Work Updates
                    </h5>
                    <a href="{{ route('admin.work-updates') }}" class="btn btn-sm btn-border-black">View All</a>
                </div>
                <div class="card-body">
                    @if($recent_updates->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recent_updates as $update)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <h6 class="mb-1">{{ $update->job_title }}</h6>
                                        <small class="text-muted">{{ $update->company }} • {{ $update->agent->name ?? 'N/A' }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-success">{{ ucfirst($update->status) }}</span>
                                        <br>
                                        <small class="text-muted">{{ $update->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-clipboard-list fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No work updates yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Recent Users -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>Recent Users
                    </h5>
                    <a href="{{ route('admin.users') }}" class="btn btn-sm btn-border-black">Manage Users</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent_users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $user->role->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                                {{ ucfirst($user->status ?? 'active') }}
                                            </span>
                                        </td>
                                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-edit" title="Edit User">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if(auth()->user()->isSuperAdmin() && !$user->isSuperAdmin())
                                                    <a href="{{ route('admin.impersonate', $user) }}" class="btn btn-sm btn-warning" title="Impersonate">
                                                        <i class="fas fa-user-secret"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Stats -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i>System Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <small>Today's Updates</small>
                            <strong>{{ $stats['today_updates'] }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <small>Active Assignments</small>
                            <strong>{{ $stats['active_assignments'] }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <small>Total Work Updates</small>
                            <strong>{{ $stats['total_work_updates'] }}</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <a href="{{ route('admin.users') }}" class="btn btn-black btn-sm">
                            <i class="fas fa-cog me-1"></i>Manage System
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function generateMeeting() {
            if (confirm('Generate a new daily meeting for today? This will create a new meeting link.')) {
                // Show loading state
                const button = event.target;
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Generating...';
                button.disabled = true;

                // Make AJAX request to generate meeting
                fetch('{{ route("admin.generate-meeting") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Meeting generated successfully! The page will reload to show the new meeting link.');
                        location.reload();
                    } else {
                        alert('Error generating meeting: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error generating meeting. Please try again.');
                })
                .finally(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            }
        }

        function createOAuthMeetRoom() {
            if (confirm('Create a new Google Meet room using OAuth? This will create a proper Google Meet room with correct permissions.')) {
                // Show loading state
                const button = event.target;
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Creating...';
                button.disabled = true;

                // Make AJAX request to create OAuth meeting
                fetch('{{ route("google.oauth.create-meet") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Google Meet room created successfully!\nMeet Link: ' + data.meet_link);
                        location.reload();
                    } else {
                        alert('Error creating OAuth meeting: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error creating OAuth meeting. Please try again.');
                })
                .finally(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            }
        }

    </script>
</x-app-layout>
