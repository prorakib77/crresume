@extends('layouts.app')

@section('title', 'Meeting Reports')
@section('pageTitle', 'Meeting Attendance Reports')
@section('pageSubtitle', 'Track agent participation and screen sharing activity')

@section('content')
<div class="row mb-4">
    <!-- Today's Meeting Status -->
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-black text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>Today's Meeting Report
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
                            <a href="{{ $todayMeeting->meet_link }}" target="_blank" class="btn btn-black btn-lg mb-2">
                                <i class="fas fa-video me-2"></i>Join Meeting
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
                        <h5>No Meeting Scheduled</h5>
                        <p class="text-muted">No meeting has been scheduled for today.</p>
                        <p class="text-info">
                            <i class="fas fa-info-circle me-1"></i>
                            Meeting will be generated automatically
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
                    <small class="text-muted">{{ $attendanceStats['attendance_rate'] }}% attendance rate</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-black me-3">
                    <i class="fas fa-share-screen text-white"></i>
                </div>
                <div>
                    <div class="stats-number">{{ $attendanceStats['total_screen_shared'] }}</div>
                    <div class="stats-label">Screen Shared</div>
                    <small class="text-muted">Active screen sharing</small>
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
</div>
@if($todayMeeting && $attendance->count() > 0)
<!-- Agent Attendance Report -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-black text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Agent Attendance Report
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
                                <th>Screen Shared</th>
                                <th>Screen Share Duration</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendance as $record)
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
                                        <span class="text-muted">Still in meeting</span>
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
                                    @if($record->screen_shared)
                                        <span class="badge bg-info">
                                            <i class="fas fa-share-screen me-1"></i>Yes
                                        </span>
                                    @else
                                        <span class="text-muted">No</span>
                                    @endif
                                </td>
                                <td>
                                    @if($record->screenSharingLogs->count() > 0)
                                        @php
                                            $totalScreenTime = $record->screenSharingLogs->sum('duration_minutes');
                                        @endphp
                                        <span class="badge bg-secondary">{{ $totalScreenTime }}m</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($record->status === 'joined')
                                        <span class="badge bg-success">Attended</span>
                                    @else
                                        <span class="badge bg-danger">Not Attended</span>
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
@endif

<!-- Historical Meeting Reports -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-black text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>Historical Meeting Reports
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
                                <th>Screen Shared</th>
                                <th>Avg Duration</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($historicalMeetings as $meeting)
                            <tr>
                                <td>{{ $meeting->date->format('M d, Y') }}</td>
                                <td>{{ $meeting->title ?? 'Daily Agent Meeting' }}</td>
                                <td>
                                    <span class="badge bg-success">{{ $meeting->attendances->where('status', 'joined')->count() }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $meeting->attendances->where('screen_shared', true)->count() }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning">{{ round($meeting->attendances->avg('duration_minutes')) }}m</span>
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
@endsection
