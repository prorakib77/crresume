<x-app-layout>
    <x-slot name="title">OAuth Settings</x-slot>
    <x-slot name="pageTitle">Google OAuth & Meet Settings</x-slot>
    <x-slot name="pageSubtitle">Manage Google Meet API credentials and meeting automation</x-slot>

    <div class="container-fluid">
        <!-- Status Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon bg-{{ $oauthSettings && $oauthSettings->is_active ? 'success' : 'danger' }} me-3">
                                <i class="fas fa-{{ $oauthSettings && $oauthSettings->is_active ? 'check' : 'times' }} text-white"></i>
                            </div>
                            <div>
                                <div class="stats-number">{{ $oauthSettings && $oauthSettings->is_active ? 'Active' : 'Inactive' }}</div>
                                <div class="stats-label">OAuth Status</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon bg-{{ $oauthSettings && $oauthSettings->auto_generate_meetings ? 'info' : 'secondary' }} me-3">
                                <i class="fas fa-{{ $oauthSettings && $oauthSettings->auto_generate_meetings ? 'play' : 'pause' }} text-white"></i>
                            </div>
                            <div>
                                <div class="stats-number">{{ $oauthSettings && $oauthSettings->auto_generate_meetings ? 'Enabled' : 'Disabled' }}</div>
                                <div class="stats-label">Auto Meetings</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon bg-primary me-3">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                            <div>
                                <div class="stats-number">{{ $oauthSettings ? $oauthSettings->meeting_start_time : 'N/A' }}</div>
                                <div class="stats-label">Start Time</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon bg-warning me-3">
                                <i class="fas fa-sync text-white"></i>
                            </div>
                            <div>
                                <div class="stats-number">{{ $oauthSettings && $oauthSettings->last_sync_at ? $oauthSettings->last_sync_at->diffForHumans() : 'Never' }}</div>
                                <div class="stats-label">Last Sync</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Settings -->
        @if($oauthSettings)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-black text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cog me-2"></i>Current OAuth Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Connection Details</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Admin Email:</strong></td>
                                        <td>{{ $oauthSettings->admin_email }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Calendar ID:</strong></td>
                                        <td>{{ $oauthSettings->calendar_id }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Timezone:</strong></td>
                                        <td>{{ $oauthSettings->timezone }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            <span class="badge bg-{{ $oauthSettings->is_active ? 'success' : 'danger' }}">
                                                {{ $oauthSettings->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Meeting Settings</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Auto Generate:</strong></td>
                                        <td>
                                            <span class="badge bg-{{ $oauthSettings->auto_generate_meetings ? 'success' : 'secondary' }}">
                                                {{ $oauthSettings->auto_generate_meetings ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Duration:</strong></td>
                                        <td>{{ $oauthSettings->meeting_duration_minutes }} minutes</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Notifications:</strong></td>
                                        <td>
                                            <span class="badge bg-{{ $oauthSettings->send_notifications ? 'success' : 'secondary' }}">
                                                {{ $oauthSettings->send_notifications ? 'Enabled' : 'Disabled' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Calendar Events:</strong></td>
                                        <td>
                                            <span class="badge bg-{{ $oauthSettings->create_calendar_events ? 'success' : 'secondary' }}">
                                                {{ $oauthSettings->create_calendar_events ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- OAuth 2.0 Status Display -->
                        <div class="mt-3">
                            <h6>OAuth 2.0 Status</h6>
                            @if(session('google_access_token'))
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>OAuth 2.0 Connected</strong><br>
                                    <small>User: {{ auth()->user()->email }} | Session Active</small>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>OAuth 2.0 Not Connected</strong><br>
                                    <small>Please connect to Google OAuth 2.0 for reliable meeting creation.</small>
                                    <div class="mt-2">
                                        <a href="{{ route('google.oauth.redirect') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-google me-2"></i>Connect Google OAuth
                                        </a>
                                    </div>
                                </div>
                            @endif

                            @if($oauthSettings->sync_status && !str_contains($oauthSettings->sync_status, 'deleted') && !str_contains($oauthSettings->sync_status, 'Project'))
                            <div class="alert alert-info mt-2">
                                <small><strong>Legacy Service Account Status:</strong> {{ $oauthSettings->sync_status }}</small>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-black text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tools me-2"></i>OAuth Management
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('admin.oauth.create') }}" class="btn btn-black w-100">
                                    <i class="fas fa-edit me-2"></i>Configure Settings
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button type="button" class="btn btn-info w-100" onclick="testConnection()">
                                    <i class="fas fa-plug me-2"></i>Test OAuth 2.0
                                </button>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button type="button" class="btn btn-warning w-100" onclick="generateTestMeeting()">
                                    <i class="fas fa-video me-2"></i>Test Meeting
                                </button>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button type="button" class="btn btn-danger w-100" onclick="resetSettings()">
                                    <i class="fas fa-undo me-2"></i>Reset Settings
                                </button>
                            </div>
                        </div>

                        <!-- OAuth 2.0 Connection Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                @if(session('google_access_token'))
                                    <div class="alert alert-success">
                                        <h6><i class="fas fa-check-circle me-2"></i>OAuth 2.0 Connected</h6>
                                        <p class="mb-2">✅ Connected to Google OAuth 2.0 - Ready for meeting creation!</p>
                                        <small>User: {{ auth()->user()->email }} | Session Active</small>
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <h6><i class="fas fa-exclamation-triangle me-2"></i>OAuth 2.0 Not Connected</h6>
                                        <p class="mb-2">⚠️ Please connect to Google OAuth 2.0 for reliable meeting creation.</p>
                                        <small>This replaces the old service account method that was causing errors.</small>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                @if(session('google_access_token'))
                                    <button type="button" class="btn btn-success w-100" disabled>
                                        <i class="fas fa-check me-2"></i>OAuth Connected
                                    </button>
                                @else
                                    <a href="{{ route('google.oauth.redirect') }}" class="btn btn-primary w-100">
                                        <i class="fas fa-google me-2"></i>Connect Google OAuth
                                    </a>
                                @endif
                            </div>
                            <div class="col-md-4 mb-3">
                                <button type="button" class="btn btn-primary w-100" onclick="createOAuthMeetRoom()">
                                    <i class="fas fa-plus me-2"></i>Create OAuth Meeting
                                </button>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="{{ route('google.oauth.disconnect') }}" class="btn btn-secondary w-100">
                                    <i class="fas fa-unlink me-2"></i>Disconnect OAuth
                                </a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('admin.oauth.setup-guide') }}" class="btn btn-info w-100">
                                    <i class="fas fa-book me-2"></i>Setup Guide
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-black text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <a href="{{ route('admin.meeting-dashboard') }}" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-chart-line me-2"></i>Meeting Dashboard
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="{{ route('admin.meeting-reports') }}" class="btn btn-outline-info w-100">
                                    <i class="fas fa-file-alt me-2"></i>Meeting Reports
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="{{ route('admin.generate-meeting') }}" class="btn btn-outline-success w-100">
                                    <i class="fas fa-plus me-2"></i>Generate Meeting
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function testConnection() {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Testing OAuth 2.0...';
            btn.disabled = true;

            fetch('{{ route("admin.oauth.test-connection") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ OAuth 2.0 Connection Successful!\n' +
                          'User: ' + (data.user_email || 'N/A') + '\n' +
                          'Calendars: ' + (data.calendar_count || 0) + '\n' +
                          'Status: Ready for meeting creation');
                    // Reload page to update OAuth status display
                    location.reload();
                } else {
                    if (data.redirect) {
                        alert('❌ OAuth 2.0 Not Connected\nPlease connect to Google OAuth first.\nRedirecting to OAuth flow...');
                        window.location.href = data.redirect;
                    } else {
                        alert('❌ OAuth 2.0 Connection Failed\n' + data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ OAuth 2.0 connection test failed. Please try again.');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }

        function generateTestMeeting() {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';
            btn.disabled = true;

            fetch('{{ route("admin.oauth.test-meeting") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message + (data.meeting_link ? '\nMeeting Link: ' + data.meeting_link : ''));
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Test meeting generation failed. Please try again.');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }

        // OAuth 2.0 Meeting Creation (same as dashboard)
        function createOAuthMeetRoom() {
            if (confirm('Create a new Google Meet room using OAuth 2.0? This will create a proper Google Meet room with correct permissions.')) {
                // Show loading state
                const button = event.target;
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Creating...';
                button.disabled = true;

                // Make AJAX request to create OAuth meeting (same as dashboard)
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

        function resetSettings() {
            if (confirm('Are you sure you want to reset all OAuth settings to defaults? This action cannot be undone.')) {
                window.location.href = '{{ route("admin.oauth.reset") }}';
            }
        }
    </script>
    @endpush
</x-app-layout>
