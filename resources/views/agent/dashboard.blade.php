@php
    use Illuminate\Support\Facades\Storage;
@endphp

<x-app-layout>
    <x-slot name="title">Agent Dashboard</x-slot>
    <x-slot name="pageTitle">Agent Dashboard</x-slot>
    <x-slot name="pageSubtitle">Welcome back, {{ auth()->user()->name }}</x-slot>

    @include('partials.dashboard-notices')

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-sm-6 col-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-black me-2 me-md-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stats-number">{{ $stats['total_clients'] }}</div>
                        <div class="stats-label">Assigned Clients</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6 col-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-black me-2 me-md-3">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stats-number">{{ $stats['active_clients'] }}</div>
                        <div class="stats-label">Active Clients</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6 col-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-black me-2 me-md-3">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stats-number">{{ $stats['submitted_today'] }}</div>
                        <div class="stats-label">Submitted Today</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6 col-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-black me-2 me-md-3">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stats-number">{{ $stats['pending_submissions'] }}</div>
                        <div class="stats-label">Pending Today</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6 col-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-black me-2 me-md-3">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stats-number">{{ $stats['this_month'] }}</div>
                        <div class="stats-label">This Month</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- Daily/Upcoming Agent Meeting -->
        @if($todayMeeting)
        @php $isTodayMeeting = $todayMeeting->date->isToday(); @endphp
        <div id="meeting-section" class="row mb-4">
            <div class="col-12">
                <div class="card meeting-card">
                    <div class="card-header meeting-card__header">
                        <div>
                            <p class="meeting-label mb-1 text-uppercase">{{ $isTodayMeeting ? "Today's Agent Meeting" : 'Next Scheduled Meeting' }}</p>
                            <h5 class="card-title mb-0 text-white">
                                <i class="fas fa-video me-2"></i>{{ $todayMeeting->title }}
                            </h5>
                        </div>
                        <span class="meeting-pill">{{ $todayMeeting->date->format('M d, Y') }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="meeting-meta">
                                    <div>
                                        <i class="fas fa-clock me-1 text-success"></i>
                                        {{ optional($todayMeeting->start_time)->format('g:i A') }} - {{ optional($todayMeeting->end_time)->format('g:i A') }}
                                    </div>
                                    <div>
                                        <i class="fas fa-calendar me-1 text-success"></i>
                                        {{ $todayMeeting->date->format('M d, Y') }}
                                    </div>
                                    <div class="text-muted small">
                                        <i class="fas fa-link me-1"></i>
                                        <span>{{ $todayMeeting->meet_link }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="{{ $todayMeeting->meet_link }}"
                                   target="_blank"
                                   class="btn btn-success btn-lg mb-2 meeting-cta">
                                    <i class="fas fa-video me-2"></i>Join Meeting
                                </a>
                                <br>
                                <div id="meeting-controls">
                                    <button id="join-btn" onclick="joinMeeting()" class="btn btn-info btn-sm me-2">
                                        <i class="fas fa-sign-in-alt me-1"></i>Mark as Joined
                                    </button>
                                    <button id="leave-btn" onclick="leaveMeeting()" class="btn btn-warning btn-sm" style="display: none;">
                                        <i class="fas fa-sign-out-alt me-1"></i>Mark as Left
                                    </button>
                                </div>
                                <div id="screen-sharing-controls" class="mt-2" style="display: none;">
                                    <button id="screen-share-btn" onclick="toggleScreenSharing()" class="btn btn-success btn-sm">
                                        <i class="fas fa-share-screen me-1"></i>Mark as Screen Sharing
                                    </button>
                                    <button id="stop-screen-share-btn" onclick="toggleScreenSharing()" class="btn btn-danger btn-sm" style="display: none;">
                                        <i class="fas fa-stop me-1"></i>Stop Screen Sharing
                                    </button>
                                </div>
                                <p class="text-muted small mt-2 mb-0">
                                    <i class="fas fa-users me-1"></i>
                                    Open meeting - join directly with your registered email
                                </p>
                                <p class="text-info small mt-1 mb-0">
                                    <i class="fas fa-link me-1"></i>
                                    Same meeting link for everyone
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="row mb-4">
            <div class="col-12">
                <div class="card meeting-card meeting-card--empty">
                    <div class="card-header meeting-card__header">
                        <div>
                            <p class="meeting-label mb-1 text-uppercase">Meetings</p>
                            <h5 class="card-title mb-0 text-white">
                                <i class="fas fa-exclamation-triangle me-2"></i>No Meeting Scheduled
                            </h5>
                        </div>
                        <span class="meeting-pill">N/A</span>
                    </div>
                    <div class="card-body">
                        <p class="mb-0 text-muted">No daily meeting has been scheduled for today. Please contact admin.</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <a href="{{ route('agent.work-updates.create') }}" class="btn btn-create btn-lg w-100">
                                <i class="fas fa-plus me-2"></i>Submit Work Updates
                            </a>
                            <small class="text-muted d-block mt-2">Submit daily work updates for your clients</small>
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <a href="{{ route('agent.submissions.index') }}" class="btn btn-view btn-lg w-100">
                                <i class="fas fa-inbox me-2"></i>Client Submissions
                            </a>
                            <small class="text-muted d-block mt-2">View and manage verification code</small>
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <a href="{{ route('agent.work-updates.index') }}" class="btn btn-border-black btn-lg w-100">
                                <i class="fas fa-history me-2"></i>My Work Updates
                            </a>
                            <small class="text-muted d-block mt-2">View your work update history</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tools me-2"></i>Additional Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="{{ route('agent.work-updates.drafts') }}" class="btn btn-edit btn-lg w-100">
                                <i class="fas fa-save me-2"></i>View Drafts
                            </a>
                            <small class="text-muted d-block mt-2">Manage your saved draft work updates</small>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('agent.work-updates.create') }}" class="btn btn-create btn-lg w-100">
                                <i class="fas fa-edit me-2"></i>Continue Draft
                            </a>
                            <small class="text-muted d-block mt-2">Continue working on a saved draft</small>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('agent.clients.index') }}" class="btn btn-view btn-lg w-100">
                                <i class="fas fa-users me-2"></i>My Clients
                            </a>
                            <small class="text-muted d-block mt-2">Manage clients and request OTP codes</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Client Assignment Status -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>Daily Submission Status
                    </h5>
                    <small class="text-muted">{{ now()->format('M j, Y') }}</small>
                </div>
                <div class="card-body">
                    @if($clientsStatus && count($clientsStatus) > 0)
                        <!-- Desktop Table View -->
                        <div class="d-none d-lg-block">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Client</th>
                                            <th>Email</th>
                                            <th>Submission</th>
                                            <th>Service Ends</th>
                                            <th>Service Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($clientsStatus as $clientStatus)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3">
                                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                {{ substr($clientStatus['client']->name, 0, 1) }}
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <strong>{{ $clientStatus['client']->name }}</strong>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $clientStatus['client']->email }}</td>
                                                <td>
                                                @php
                                                    $submittedToday = \App\Models\WorkUpdate::where('agent_id', auth()->id())
                                                        ->where('client_id', $clientStatus['client']->id)
                                                        ->whereDate('updated_at', today())
                                                        ->where('status', \App\Models\WorkUpdate::STATUS_APPROVED)
                                                        ->exists();
                                                @endphp
                                                @if($submittedToday)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check me-1"></i>Submitted
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-clock me-1"></i>Pending
                                                    </span>
                                                @endif
                                                </td>
                                                <td>
                                                @if($clientStatus['service_end_date'])
                                                    {{ \Carbon\Carbon::parse($clientStatus['service_end_date'])->format('M j, Y') }}
                                                    @if($clientStatus['days_remaining'] !== null)
                                                        <small class="text-muted d-block">
                                                            ({{ $clientStatus['days_remaining'] > 0 ? $clientStatus['days_remaining'] . ' days left' : 'Expired' }})
                                                        </small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Ongoing</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $statusColor = match($clientStatus['service_status']) {
                                                        'active' => 'success',
                                                        'expired' => 'danger',
                                                        'inactive' => 'secondary',
                                                        default => 'secondary',
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $statusColor }}">{{ $clientStatus['service_status_label'] }}</span>
                                            </td>
                                            <td>
                                                @if($clientStatus['has_submitted_today'])
                                                    <span class="badge bg-success">Completed</span>
                                                @else
                                                    @if($clientStatus['has_draft'])
                                                        <a href="{{ route('agent.work-updates.drafts') }}"
                                                           class="btn btn-sm btn-warning">
                                                            <i class="fas fa-edit"></i> Continue Drafts
                                                        </a>
                                                    @else
                                                        <a href="{{ route('agent.work-updates.create', ['client_id' => $clientStatus['client']->getRouteKey()]) }}"
                                                           class="btn btn-sm btn-success">
                                                            <i class="fas fa-plus"></i> Submit Update
                                                        </a>
                                                    @endif
                                                @endif
                                            </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="d-lg-none">
                            @foreach($clientsStatus as $clientStatus)
                                <div class="daily-card mb-3">
                                    <div class="daily-card-body">
                                        <div class="d-flex align-items-start justify-content-between gap-2">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="daily-avatar">
                                                    {{ substr($clientStatus['client']->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $clientStatus['client']->name }}</div>
                                                    <div class="text-muted small">{{ $clientStatus['client']->email }}</div>
                                                    @php
                                                        $statusColor = match($clientStatus['service_status']) {
                                                            'active' => 'pill-active',
                                                            'expired' => 'pill-expired',
                                                            'inactive' => 'pill-inactive',
                                                            default => 'pill-default',
                                                        };
                                                    @endphp
                                                    <span class="mini-pill {{ $statusColor }}">{{ $clientStatus['service_status_label'] }}</span>
                                                </div>
                                            </div>
                                            @php
                                                $submittedToday = \App\Models\WorkUpdate::where('agent_id', auth()->id())
                                                    ->where('client_id', $clientStatus['client']->id)
                                                    ->whereDate('updated_at', today())
                                                    ->where('status', \App\Models\WorkUpdate::STATUS_APPROVED)
                                                    ->exists();
                                            @endphp
                                            <span class="daily-pill {{ $submittedToday ? 'pill-submitted' : 'pill-pending' }}">
                                                <i class="fas {{ $submittedToday ? 'fa-check' : 'fa-clock' }} me-1"></i>
                                                {{ $submittedToday ? 'Submitted' : 'Pending' }}
                                            </span>
                                        </div>

                                        <div class="daily-meta mt-3">
                                            <div>
                                                <i class="fas fa-calendar-alt me-2 text-muted"></i>
                                                @if($clientStatus['service_end_date'])
                                                    {{ \Carbon\Carbon::parse($clientStatus['service_end_date'])->format('M j, Y') }}
                                                @else
                                                    Ongoing service
                                                @endif
                                            </div>
                                            @if($clientStatus['days_remaining'] !== null)
                                                <div>
                                                    <i class="fas fa-hourglass-half me-2 text-muted"></i>
                                                    {{ $clientStatus['days_remaining'] > 0 ? $clientStatus['days_remaining'] . ' days left' : 'Expired' }}
                                                </div>
                                            @endif
                                        </div>

                                        <div class="d-flex flex-wrap gap-2 mt-3">
                                            @if($clientStatus['has_submitted_today'])
                                                <span class="mini-pill pill-submitted px-3 py-2">Completed</span>
                                            @else
                                                @if($clientStatus['has_draft'])
                                                    <a href="{{ route('agent.work-updates.drafts') }}"
                                                       class="btn btn-sm btn-accent">
                                                        <i class="fas fa-edit me-1"></i>Continue Drafts
                                                    </a>
                                                @else
                                                    <a href="{{ route('agent.work-updates.create', ['client_id' => $clientStatus['client']->getRouteKey()]) }}"
                                                       class="btn btn-sm btn-accent">
                                                        <i class="fas fa-plus me-1"></i>Submit Update
                                                    </a>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Clients Assigned</h5>
                            <p class="text-muted">Contact your manager to get client assignments.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>


        <!-- Recent Work Updates -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Recent Updates
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentUpdates && count($recentUpdates) > 0)
                        <div class="timeline">
                            @foreach($recentUpdates->take(5) as $update)
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <div class="bg-{{ $update->status === 'approved' ? 'success' : ($update->status === 'rejected' ? 'danger' : 'warning') }} text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                <i class="fas fa-{{ $update->status === 'approved' ? 'check' : ($update->status === 'rejected' ? 'times' : 'clock') }} fa-sm"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="small text-muted">{{ $update->created_at->format('M j') }}</div>
                                            <div class="fw-bold">{{ $update->job_title }}</div>
                                            <div class="small text-muted">{{ $update->company }}</div>
                                            <div class="small">
                                                <span class="badge bg-{{ $update->status === 'approved' ? 'success' : ($update->status === 'rejected' ? 'danger' : 'warning') }}">
                                                    {{ $update->getStatusLabel() }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('agent.work-updates.index') }}" class="btn btn-sm btn-outline-primary">
                                View All Updates
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-clipboard-list fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No updates yet</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($stats['pending_submissions'] > 0)
                            <a href="{{ route('agent.work-updates.create') }}" class="btn btn-create">
                                <i class="fas fa-plus me-2"></i>Submit Work Update
                            </a>
                        @endif
                        <a href="{{ route('agent.work-updates.index') }}" class="btn btn-border-black">
                            <i class="fas fa-list me-2"></i>View My Updates
                        </a>
                        <a href="{{ route('profile.edit') }}" class="btn btn-border-black">
                            <i class="fas fa-user me-2"></i>Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .timeline-item {
        position: relative;
    }
    .timeline-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 17px;
        top: 45px;
        height: calc(100% - 10px);
        width: 2px;
        background: #e5e7eb;
    }
    </style>

    <script>
    // Check meeting status on page load
    document.addEventListener('DOMContentLoaded', function() {
        checkMeetingStatus();
    });

    function checkMeetingStatus() {
        fetch('{{ route("agent.meeting.status") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.in_meeting) {
                document.getElementById('join-btn').style.display = 'none';
                document.getElementById('leave-btn').style.display = 'inline-block';
                document.getElementById('screen-sharing-controls').style.display = 'block';
            } else {
                document.getElementById('join-btn').style.display = 'inline-block';
                document.getElementById('leave-btn').style.display = 'none';
                document.getElementById('screen-sharing-controls').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error checking meeting status:', error);
        });
    }

    function joinMeeting() {
        if (confirm('Mark yourself as joined the meeting? This will track your attendance.')) {
            fetch('{{ route("agent.meeting.join") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Successfully joined the meeting!');
                    document.getElementById('join-btn').style.display = 'none';
                    document.getElementById('leave-btn').style.display = 'inline-block';
                    document.getElementById('screen-sharing-controls').style.display = 'block';
                } else {
                    alert('Error joining meeting: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error joining meeting. Please try again.');
            });
        }
    }

    function leaveMeeting() {
        if (confirm('Mark yourself as left the meeting? This will record your attendance duration.')) {
            fetch('{{ route("agent.meeting.leave") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Successfully left the meeting!');
                    document.getElementById('join-btn').style.display = 'inline-block';
                    document.getElementById('leave-btn').style.display = 'none';
                } else {
                    alert('Error leaving meeting: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error leaving meeting. Please try again.');
            });
        }
    }

    // Legacy function for backward compatibility
    function trackJoin() {
        joinMeeting();
    }

    function toggleScreenSharing() {
        const screenShareBtn = document.getElementById('screen-share-btn');
        const stopScreenShareBtn = document.getElementById('stop-screen-share-btn');

        if (screenShareBtn.style.display !== 'none') {
            // Start screen sharing
            if (confirm('Mark yourself as screen sharing? This will track your screen sharing activity.')) {
                fetch('{{ route("agent.screen-sharing.start") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Screen sharing started!');
                        screenShareBtn.style.display = 'none';
                        stopScreenShareBtn.style.display = 'inline-block';
                    } else {
                        alert('Error starting screen sharing: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error starting screen sharing. Please try again.');
                });
            }
        } else {
            // Stop screen sharing
            if (confirm('Stop screen sharing? This will record your screen sharing duration.')) {
                fetch('{{ route("agent.screen-sharing.stop") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Screen sharing stopped!');
                        screenShareBtn.style.display = 'inline-block';
                        stopScreenShareBtn.style.display = 'none';
                    } else {
                        alert('Error stopping screen sharing: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error stopping screen sharing. Please try again.');
                });
            }
        }
    }

    // Floating Check-in Button functionality
    document.addEventListener('DOMContentLoaded', function() {
        checkCheckInStatus();

        document.getElementById('floating-checkin-btn').addEventListener('click', function() {
            toggleCheckIn();
        });
    });

    function checkCheckInStatus() {
        fetch('{{ route("agent.checkin.status") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            const btn = document.getElementById('floating-checkin-btn');
            const icon = document.getElementById('floating-btn-icon');
            const meetingSection = document.getElementById('meeting-section');

            if (data.is_checked_in) {
                // Agent is checked in - show check-out button
                btn.className = 'btn btn-danger btn-lg rounded-circle shadow-lg';
                btn.title = 'Check-out';
                icon.className = 'fas fa-sign-out-alt';

                // Show meeting section
                if (meetingSection) {
                    meetingSection.style.display = 'block';
                }
            } else {
                // Agent is not checked in - show check-in button
                btn.className = 'btn btn-primary btn-lg rounded-circle shadow-lg';
                btn.title = 'Check-in';
                icon.className = 'fas fa-sign-in-alt';

                // Hide meeting section
                if (meetingSection) {
                    meetingSection.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error checking check-in status:', error);
        });
    }

    function toggleCheckIn() {
        fetch('{{ route("agent.checkin.status") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.is_checked_in) {
                // Currently checked in - perform check-out
                performCheckOut();
            } else {
                // Currently not checked in - perform check-in
                performCheckIn();
            }
        })
        .catch(error => {
            console.error('Error checking status:', error);
        });
    }

    function performCheckIn() {
        if (confirm('Check in for today?')) {
            fetch('{{ route("agent.checkin.check-in") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Successfully checked in!');
                    checkCheckInStatus(); // Refresh status
                } else {
                    alert('Error checking in: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error checking in. Please try again.');
            });
        }
    }

    function performCheckOut() {
        if (confirm('Check out for today?')) {
            fetch('{{ route("agent.checkin.check-out") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Successfully checked out!');
                    checkCheckInStatus(); // Refresh status
                } else {
                    alert('Error checking out: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error checking out. Please try again.');
            });
        }
    }
    </script>

    @push('floating-ui')
        <div class="floating-checkin-btn">
            <button id="floating-checkin-btn" class="btn btn-primary btn-lg rounded-circle shadow-lg" title="Check-in/Check-out">
                <i class="fas fa-clock" id="floating-btn-icon"></i>
            </button>
        </div>
    @endpush

    <style>
        .floating-checkin-btn {
            position: fixed;
            right: max(1.35rem, env(safe-area-inset-right));
            bottom: max(1.35rem, env(safe-area-inset-bottom));
            transform: none;
            z-index: 1200;
            pointer-events: none;
        }

        .floating-checkin-btn .btn {
            pointer-events: auto;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 999px;
            box-shadow: 0 18px 42px rgba(17, 17, 17, 0.22);
            backdrop-filter: blur(12px);
            transition: transform 0.25s ease, background-color 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease, opacity 0.25s ease;
        }

        .floating-checkin-btn .btn.btn-primary {
            animation: pulse-blue 2s infinite;
        }

        .floating-checkin-btn .btn.btn-danger {
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-blue {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.7);
            }
            70% {
                transform: scale(1.05);
                box-shadow: 0 0 0 10px rgba(13, 110, 253, 0);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(13, 110, 253, 0);
            }
        }

        @keyframes pulse-red {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
            }
            70% {
                transform: scale(1.05);
                box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
            }
        }

        .floating-checkin-btn .btn:hover {
            animation: none;
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 22px 48px rgba(17, 17, 17, 0.26);
        }

        /* Meeting card */
        .meeting-card {
            border: 1px solid #111827;
            border-radius: 14px;
            overflow: hidden;
        }

        .meeting-card__header {
            background: linear-gradient(135deg, #0b0f19, #111827);
            color: #fff;
            padding: 1rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .meeting-label {
            letter-spacing: 0.08em;
            font-size: 0.75rem;
            color: #fce7f3;
        }

        .meeting-pill {
            background: #ecfdf3;
            color: #166534;
            font-weight: 700;
            padding: 0.4rem 0.8rem;
            border-radius: 999px;
            font-size: 0.85rem;
            border: 1px solid #bbf7d0;
            white-space: nowrap;
        }

        .meeting-card--empty .meeting-pill {
            background: #fdf2f8;
            color: #9d174d;
            border-color: #fbcfe8;
        }

        .meeting-meta {
            display: grid;
            gap: 0.4rem;
            color: #1f2937;
            font-weight: 600;
        }

        .meeting-cta {
            border-radius: 12px;
            font-weight: 700;
            border: 2px solid #16a34a;
            box-shadow: 0 12px 30px rgba(22, 163, 74, 0.25);
        }

        :root {
            --accent: #2563eb;
            --accent-soft: #e8f0ff;
        }

        .daily-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.05);
        }

        .daily-card-body {
            padding: 1.1rem;
        }

        .daily-avatar {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #0f172a, #1f2937);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .daily-pill {
            border-radius: 999px;
            padding: 0.35rem 0.75rem;
            font-weight: 700;
            font-size: 0.82rem;
        }

        .mini-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.65rem;
            font-weight: 700;
            font-size: 0.78rem;
            border-radius: 999px;
            margin-top: 0.35rem;
        }

        .pill-submitted { background: #ecfdf3; color: #15803d; }
        .pill-pending { background: #fff7ed; color: #b45309; }
        .pill-active { background: #e0f2fe; color: #0f172a; }
        .pill-expired { background: #fef2f2; color: #b91c1c; }
        .pill-inactive { background: #f1f5f9; color: #475569; }
        .pill-default { background: #e2e8f0; color: #1f2937; }

        .daily-meta {
            color: #475569;
            font-size: 0.9rem;
            display: grid;
            gap: 0.25rem;
        }

        .btn-accent {
            background: var(--accent-soft);
            border: 1px solid var(--accent);
            color: #0f172a;
            font-weight: 700;
            border-radius: 10px;
        }

        .btn-accent:hover {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .stats-card {
                margin-bottom: 1rem;
            }

            .stats-card .d-flex {
                flex-direction: column;
                text-align: center;
            }

            .stats-icon {
                margin: 0 auto 0.5rem auto !important;
                width: 50px;
                height: 50px;
            }

            .stats-number {
                font-size: 1.5rem;
            }

            .stats-label {
                font-size: 0.8rem;
            }

            .card-body {
                padding: 1rem;
            }

            .table-responsive {
                font-size: 0.8rem;
            }

            .btn {
                font-size: 0.8rem;
                padding: 0.5rem 0.75rem;
            }

            .floating-checkin-btn {
                right: 20px;
                bottom: 20px;
                transform: none;
            }

            .floating-checkin-btn .btn {
                width: 50px;
                height: 50px;
                font-size: 16px;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .page-subtitle {
                font-size: 1rem;
            }
        }

        @media (max-width: 576px) {
            .stats-card {
                padding: 1rem;
            }

            .stats-icon {
                width: 40px;
                height: 40px;
                font-size: 0.9rem;
            }

            .stats-number {
                font-size: 1.25rem;
            }

            .stats-label {
                font-size: 0.75rem;
            }

            .table-responsive {
                font-size: 0.75rem;
            }

            .btn {
                font-size: 0.75rem;
                padding: 0.4rem 0.6rem;
            }

            .floating-checkin-btn {
                right: 15px;
                bottom: 15px;
                transform: none;
            }

            .floating-checkin-btn .btn {
                width: 45px;
                height: 45px;
                font-size: 14px;
            }

            .page-title {
                font-size: 1.25rem;
            }

            .page-subtitle {
                font-size: 0.9rem;
            }
        }
    </style>
</x-app-layout>
