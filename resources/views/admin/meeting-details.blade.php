@extends('layouts.app')

@section('title', 'Meeting Details')
@section('pageTitle', 'Meeting Details')
@section('pageSubtitle', 'Detailed view of meeting attendance and screen sharing')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-dark border-secondary">
            <div class="card-header bg-dark text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar me-2"></i>Meeting Information
                </h5>
            </div>
            <div class="card-body bg-dark text-light">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-light">{{ $meeting->title ?? 'Daily Agent Meeting' }}</h6>
                        <p class="text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            {{ $meeting->date->format('M d, Y') }}
                        </p>
                        <p class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            {{ $meeting->start_time->format('g:i A') }} - {{ $meeting->end_time->format('g:i A') }}
                        </p>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="{{ $meeting->meet_link }}" target="_blank" class="btn btn-success">
                            <i class="fas fa-video me-2"></i>Join Meeting
                        </a>
                    </div>
                </div>

                <hr class="border-secondary">

                <!-- Statistics -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="stats-card bg-dark border-secondary">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-success me-3">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <div class="stats-number text-light">{{ $attendanceStats['total_attended'] }}</div>
                                    <div class="stats-label text-muted">Agents Attended</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-dark border-secondary">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-info me-3">
                                    <i class="fas fa-share-screen"></i>
                                </div>
                                <div>
                                    <div class="stats-number text-light">{{ $attendanceStats['total_screen_shared'] }}</div>
                                    <div class="stats-label text-muted">Screen Shared</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-dark border-secondary">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-warning me-3">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <div class="stats-number text-light">{{ round($attendanceStats['average_duration']) }}m</div>
                                    <div class="stats-label text-muted">Avg Duration</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-dark border-secondary">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-primary me-3">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div>
                                    <div class="stats-number text-light">{{ $meeting->attendances->count() }}</div>
                                    <div class="stats-label text-muted">Total Records</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Details -->
<div class="row">
    <div class="col-12">
        <div class="card bg-dark border-secondary">
            <div class="card-header bg-dark text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Agent Attendance Details
                </h5>
            </div>
            <div class="card-body bg-dark text-light">
                <div class="table-responsive">
                    <table class="table table-striped table-dark">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-light">Agent</th>
                                <th class="text-light">Joined At</th>
                                <th class="text-light">Left At</th>
                                <th class="text-light">Duration</th>
                                <th class="text-light">Screen Shared</th>
                                <th class="text-light">Screen Share Duration</th>
                                <th class="text-light">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($meeting->attendances as $record)
                            <tr class="table-dark">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-light">{{ $record->agent ? $record->agent->name : 'Unknown Agent' }}</div>
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
@endsection
