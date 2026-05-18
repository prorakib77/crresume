@extends('layouts.app')

@section('title', 'Agent Tracking Dashboard')
@section('pageTitle', 'Agent Tracking & Performance Reports')
@section('pageSubtitle', 'Monitor agent attendance, work hours, and performance metrics')

@section('content')
<div class="row mb-4">
    <!-- Today's Meeting Status -->
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-black text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>Today's Agent Tracking Status
                    @if($todayMeeting)
                        <span class="badge bg-success ms-2">
                            <i class="fas fa-sync-alt fa-spin me-1"></i>Live Tracking
                        </span>
                    @else
                        <span class="badge bg-warning ms-2">
                            <i class="fas fa-calendar-times me-1"></i>No Meeting
                        </span>
                    @endif
                </h5>
            </div>
            <div class="card-body">
                @if($todayMeeting)
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="mb-2">{{ $todayMeeting->title ?? 'Daily Agent Meeting' }}</h6>
                            <p class="text-muted mb-2">
                                <i class="fas fa-calendar me-1"></i>
                                {{ $todayMeeting->date->format('M d, Y') }}
                            </p>
                            <p class="text-muted mb-2">
                                <i class="fas fa-clock me-1"></i>
                                {{ $todayMeeting->start_time ? $todayMeeting->start_time->format('g:i A') : 'All Day' }} -
                                {{ $todayMeeting->end_time ? $todayMeeting->end_time->format('g:i A') : 'End of Day' }}
                            </p>
                            <p class="text-muted mb-0">
                                <i class="fas fa-link me-1"></i>
                                <a href="{{ $todayMeeting->meet_link }}" target="_blank" class="text-decoration-none">
                                    {{ $todayMeeting->meet_link }}
                                </a>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ $todayMeeting->meet_link }}" target="_blank" class="btn btn-black btn-lg mb-2">
                                <i class="fas fa-users me-2"></i>Join Agent Meeting
                            </a>
                            <br>
                            <small class="text-success">
                                <i class="fas fa-check-circle me-1"></i>
                                Auto-generated from Google API
                            </small>
                            <br>
                            <small class="text-info">
                                <i class="fas fa-user me-1"></i>
                                Host: {{ env('GOOGLE_ADMIN_EMAIL', 'caliroweteam@caliwfhresumes.com') }}
                            </small>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h5>No Agent Meeting Scheduled</h5>
                        <p class="text-muted">No agent tracking meeting has been scheduled for today.</p>
                        <p class="text-info">
                            <i class="fas fa-info-circle me-1"></i>
                            Agent meeting will be generated automatically
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Comprehensive Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-black me-3">
                    <i class="fas fa-users text-white"></i>
                </div>
                <div>
                    <div class="stats-number">{{ $attendanceStats['total_attended'] }}</div>
                    <div class="stats-label">Agents Attended</div>
                    <small class="text-muted">{{ $attendanceStats['total_agents'] }} total agents</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-black me-3">
                    <i class="fas fa-percentage text-white"></i>
                </div>
                <div>
                    <div class="stats-number">{{ $attendanceStats['attendance_rate'] }}%</div>
                    <div class="stats-label">Attendance Rate</div>
                    <small class="text-muted">Today's performance</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-black me-3">
                    <i class="fas fa-clock text-white"></i>
                </div>
                <div>
                    <div class="stats-number">{{ round($attendanceStats['average_duration']) }}m</div>
                    <div class="stats-label">Avg Duration</div>
                    <small class="text-muted">Per agent</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-black me-3">
                    <i class="fas fa-chart-line text-white"></i>
                </div>
                <div>
                    <div class="stats-number">{{ round($attendanceStats['total_work_hours'], 1) }}h</div>
                    <div class="stats-label">Total Work Hours</div>
                    <small class="text-muted">Today's total</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Screen Sharing Statistics -->

<!-- Weekly Summary -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-black text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Weekly Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-primary">{{ $weeklyStats['meetings_this_week'] }}</h4>
                            <p class="text-muted mb-0">Meetings This Week</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-success">{{ $weeklyStats['attendance_this_week'] }}</h4>
                            <p class="text-muted mb-0">Total Attendance</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-info">{{ round($weeklyStats['total_work_hours'], 1) }}h</h4>
                            <p class="text-muted mb-0">Total Work Hours</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-warning">{{ $weeklyStats['average_daily_attendance'] }}%</h4>
                            <p class="text-muted mb-0">Avg Daily Attendance</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Agent Performance Report -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-black text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-trophy me-2"></i>Agent Performance Report
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Agent</th>
                                <th>Attendance Count</th>
                                <th>Total Hours</th>
                                <th>Avg Duration</th>
                                <th>Last Attended</th>
                                <th>Attendance Rate</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($agentPerformance as $agent)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $agent['name'] }}</div>
                                            <small class="text-muted">{{ $agent['email'] }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $agent['attendance_count'] }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-success">{{ $agent['total_hours'] }}h</span>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ round($agent['average_duration']) }}m</span>
                                </td>
                                <td>
                                    @if($agent['last_attended'])
                                        <span class="text-success">{{ $agent['last_attended']->format('M d, Y') }}</span>
                                    @else
                                        <span class="text-muted">Never</span>
                                    @endif
                                </td>
                                <td>
                                    @if($agent['attendance_rate'] >= 80)
                                        <span class="badge bg-success">{{ $agent['attendance_rate'] }}%</span>
                                    @elseif($agent['attendance_rate'] >= 60)
                                        <span class="badge bg-warning">{{ $agent['attendance_rate'] }}%</span>
                                    @else
                                        <span class="badge bg-danger">{{ $agent['attendance_rate'] }}%</span>
                                    @endif
                                </td>
                                <td>
                                    @if($agent['attendance_rate'] >= 80)
                                        <span class="text-success">
                                            <i class="fas fa-star me-1"></i>Excellent
                                        </span>
                                    @elseif($agent['attendance_rate'] >= 60)
                                        <span class="text-warning">
                                            <i class="fas fa-star-half-alt me-1"></i>Good
                                        </span>
                                    @else
                                        <span class="text-danger">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Needs Improvement
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Today's Agent Attendance Details -->
@if($todayMeeting && $todayAttendance->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-black text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>Today's Agent Attendance Details
                    <span class="badge bg-success ms-2">
                        <i class="fas fa-sync-alt fa-spin me-1"></i>Live Tracking
                    </span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Agent</th>
                                <th>Joined At</th>
                                <th>Left At</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($todayAttendance as $record)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $record->agent ? $record->agent->name : 'Unknown Agent' }}</div>
                                            <small class="text-muted">{{ $record->agent ? $record->agent->email : 'unknown@example.com' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($record->join_time)
                                        <span class="badge bg-success">
                                            {{ $record->join_time->format('g:i A') }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($record->leave_time)
                                        <span class="badge bg-warning">
                                            {{ $record->leave_time->format('g:i A') }}
                                        </span>
                                    @else
                                        <span class="text-success">Still in meeting</span>
                                    @endif
                                </td>
                                <td>
                                    @if($record->duration_minutes)
                                        <span class="badge bg-info">{{ $record->duration_minutes }}m</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($record->status == 'joined')
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Left</span>
                                    @endif
                                </td>
                                <td>
                                    <button onclick="trackAgentJoin({{ $record->agent->id }})" class="btn btn-sm btn-success">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Historical Agent Meetings -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-black text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>Historical Agent Meetings (Last 30 Days)
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Meeting Title</th>
                                <th>Attended</th>
                                <th>Total Agents</th>
                                <th>Attendance Rate</th>
                                <th>Avg Duration</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($historicalMeetings as $meeting)
                            @php
                                $attended = $meeting->attendances->where('status', 'joined')->count();
                                $totalAgents = \App\Models\User::where('role_id', 2)->count();
                                $attendanceRate = $totalAgents > 0 ? round(($attended / $totalAgents) * 100) : 0;
                            @endphp
                            <tr>
                                <td>{{ $meeting->date->format('M d, Y') }}</td>
                                <td>{{ $meeting->title ?? 'Daily Agent Meeting' }}</td>
                                <td>
                                    <span class="badge bg-success">{{ $attended }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $totalAgents }}</span>
                                </td>
                                <td>
                                    @if($attendanceRate >= 80)
                                        <span class="badge bg-success">{{ $attendanceRate }}%</span>
                                    @elseif($attendanceRate >= 60)
                                        <span class="badge bg-warning">{{ $attendanceRate }}%</span>
                                    @else
                                        <span class="badge bg-danger">{{ $attendanceRate }}%</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ round($meeting->attendances->avg('duration_minutes')) }}m</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.meeting-details', $meeting) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Reports Section -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-black text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-download me-2"></i>Export Reports
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center">
                            <h6>Today's Attendance</h6>
                            <p class="text-muted">Export today's meeting attendance data</p>
                            @if($todayMeeting)
                                <a href="{{ route('admin.export-meeting-report', $todayMeeting) }}" class="btn btn-black">
                                    <i class="fas fa-download me-1"></i>Export CSV
                                </a>
                            @else
                                <button class="btn btn-secondary" disabled>
                                    <i class="fas fa-download me-1"></i>No Meeting Today
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h6>Weekly Report</h6>
                            <p class="text-muted">Export weekly attendance summary</p>
                            <button class="btn btn-black" onclick="exportWeeklyReport()">
                                <i class="fas fa-download me-1"></i>Export Weekly
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h6>Agent Performance</h6>
                            <p class="text-muted">Export agent performance data</p>
                            <button class="btn btn-black" onclick="exportAgentPerformance()">
                                <i class="fas fa-download me-1"></i>Export Performance
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Real-time attendance tracking
function refreshAttendanceData() {
    fetch('{{ route("admin.meeting-dashboard") }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        // Update only the attendance section
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newAttendanceSection = doc.querySelector('.table-responsive');
        if (newAttendanceSection) {
            const currentSection = document.querySelector('.table-responsive');
            if (currentSection) {
                currentSection.innerHTML = newAttendanceSection.innerHTML;
            }
        }
    })
    .catch(error => {
        console.error('Error refreshing data:', error);
    });
}

// Refresh attendance data every 10 seconds
setInterval(refreshAttendanceData, 10000);

function trackAgentJoin(agentId) {
    if (confirm('Mark this agent as joined the meeting?')) {
        fetch('{{ route("admin.track-agent-join") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ agent_id: agentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Agent join tracked successfully!');
                location.reload();
            } else {
                alert('Error tracking agent join: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error tracking agent join. Please try again.');
        });
    }
}

function exportWeeklyReport() {
    // Implement weekly report export
    alert('Weekly report export feature coming soon!');
}

function exportAgentPerformance() {
    // Implement agent performance export
    alert('Agent performance export feature coming soon!');
}
</script>
@endsection
