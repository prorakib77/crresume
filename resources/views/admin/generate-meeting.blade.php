<x-app-layout>
    <x-slot name="title">Generate Meeting</x-slot>
    <x-slot name="pageTitle">Generate Daily Meeting</x-slot>
    <x-slot name="pageSubtitle">Create and manage Google Meet meetings for agents</x-slot>

    <div class="container-fluid">
        <!-- Current Meeting Status -->
        @if($todayMeeting)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-check-circle me-2"></i>Today's Meeting Already Exists
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h6 class="mb-2">{{ $todayMeeting->title ?? 'Daily Agent Meeting' }}</h6>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $todayMeeting->date->format('M d, Y') }}
                                </p>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $todayMeeting->start_time->format('g:i A') }} - {{ $todayMeeting->end_time->format('g:i A') }}
                                </p>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-link me-1"></i>
                                    <a href="{{ $todayMeeting->meet_link }}" target="_blank" class="text-decoration-none">
                                        {{ $todayMeeting->meet_link }}
                                    </a>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="{{ $todayMeeting->meet_link }}" target="_blank" class="btn btn-success btn-lg mb-2">
                                    <i class="fas fa-video me-2"></i>Join Meeting
                                </a>
                                <br>
                                <small class="text-success">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Meeting is active and ready
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>No Meeting for Today
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">No meeting has been generated for today. You can create one using the options below.</p>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary" onclick="generateMeeting()">
                                <i class="fas fa-plus me-2"></i>Generate Today's Meeting
                            </button>
                            <button type="button" class="btn btn-info" onclick="testConnection()">
                                <i class="fas fa-plug me-2"></i>Test OAuth Connection
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- OAuth Settings Status -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-black text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cog me-2"></i>OAuth Settings Status
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($oauthSettings)
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Connection Details</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            <span class="badge bg-{{ $oauthSettings->is_active ? 'success' : 'danger' }}">
                                                {{ $oauthSettings->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
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
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Meeting Settings</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Auto Generate:</strong></td>
                                        <td>
                                            <span class="badge bg-{{ $oauthSettings->auto_generate_meetings ? 'success' : 'secondary' }}">
                                                {{ $oauthSettings->auto_generate_meetings ? 'Enabled' : 'Disabled' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Duration:</strong></td>
                                        <td>{{ $oauthSettings->meeting_duration_minutes }} minutes</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Start Time:</strong></td>
                                        <td>{{ $oauthSettings->meeting_start_time }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>End Time:</strong></td>
                                        <td>{{ $oauthSettings->meeting_end_time }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        @if($oauthSettings->sync_status)
                        <div class="mt-3">
                            <h6>Last Sync Status</h6>
                            <div class="alert alert-{{ str_contains($oauthSettings->sync_status, 'successful') ? 'success' : 'danger' }}">
                                {{ $oauthSettings->sync_status }}
                            </div>
                        </div>
                        @endif
                        @else
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>OAuth Settings Not Configured</h6>
                            <p class="mb-2">Google OAuth settings are not configured. Please configure them to generate meetings.</p>
                            <a href="{{ route('admin.oauth.index') }}" class="btn btn-warning">
                                <i class="fas fa-cog me-2"></i>Configure OAuth Settings
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Meeting Generation Options -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-black text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tools me-2"></i>Meeting Generation Options
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="d-grid">
                                    <button type="button" class="btn btn-primary btn-lg" onclick="generateMeeting()">
                                        <i class="fas fa-plus me-2"></i>Generate Today's Meeting
                                    </button>
                                    <small class="text-muted mt-1">Create a meeting for today</small>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="d-grid">
                                    <button type="button" class="btn btn-info btn-lg" onclick="testConnection()">
                                        <i class="fas fa-plug me-2"></i>Test OAuth Connection
                                    </button>
                                    <small class="text-muted mt-1">Verify Google API connection</small>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="d-grid">
                                    <a href="{{ route('admin.oauth.index') }}" class="btn btn-warning btn-lg">
                                        <i class="fas fa-cog me-2"></i>OAuth Settings
                                    </a>
                                    <small class="text-muted mt-1">Configure Google credentials</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Meeting Generation Methods -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-black text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Meeting Generation System
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6><i class="fas fa-key me-2"></i>OAuth Service Account</h6>
                                <p class="text-muted">Uses Google Service Account credentials stored in OAuth settings for authentication.</p>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Secure authentication</li>
                                    <li><i class="fas fa-check text-success me-2"></i>No user interaction required</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Automatic meeting creation</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <h6><i class="fas fa-calendar me-2"></i>Google Calendar API</h6>
                                <p class="text-muted">Creates calendar events and generates Google Meet links automatically.</p>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Calendar integration</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Event notifications</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Attendee management</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <h6><i class="fas fa-video me-2"></i>Google Meet Integration</h6>
                                <p class="text-muted">Generates meeting links and manages meeting rooms for agents.</p>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Automatic room creation</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Link generation</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Meeting tracking</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h6>Configuration Fields in OAuth Settings:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li><strong>Credentials JSON:</strong> Google Service Account credentials</li>
                                        <li><strong>Admin Email:</strong> Meeting host email address</li>
                                        <li><strong>Calendar ID:</strong> Google Calendar to use</li>
                                        <li><strong>Timezone:</strong> Meeting timezone</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li><strong>Meeting Duration:</strong> Meeting length in minutes</li>
                                        <li><strong>Start/End Times:</strong> Meeting time range</li>
                                        <li><strong>Auto Generate:</strong> Automatic daily meetings</li>
                                        <li><strong>Notifications:</strong> Email notifications to attendees</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function generateMeeting() {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';
            btn.disabled = true;

            fetch('{{ route("admin.generate-meeting.post") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    // Reload the page to show the new meeting
                    window.location.reload();
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Meeting generation failed. Please try again.');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }

        function testConnection() {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Testing...';
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
                    alert('✅ ' + data.message + (data.calendar_title ? '\nCalendar: ' + data.calendar_title : ''));
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Connection test failed. Please try again.');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }
    </script>
    @endpush
</x-app-layout>
