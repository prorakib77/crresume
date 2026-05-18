@extends('layouts.app')

@section('title', 'Agent Details')
@section('pageTitle', 'Agent Details')
@section('pageSubtitle', 'View agent activities and performance')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">{{ $agent->name }}</h2>
                <p class="text-muted mb-0">{{ $agent->email }} - Agent Profile</p>
            </div>
            <div>
                <a href="{{ route('admin.agents.index') }}" class="btn btn-border-black">
                    <i class="fas fa-arrow-left me-2"></i>Back to Agents
                </a>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="d-flex align-items-center gap-3">
                    <label for="date" class="form-label mb-0">Select Date:</label>
                    <input type="date" name="date" id="date" class="form-control" style="width: 200px;"
                           value="{{ $selectedDate->format('Y-m-d') }}">
                    <button type="submit" class="btn btn-black">
                        <i class="fas fa-search me-2"></i>Filter
                    </button>
                </form>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="stats-icon bg-black">
                        <i class="fas fa-clock text-white"></i>
                    </div>
                    <div>
                        <div class="stats-number">{{ $workHours['total_hours'] ?? 0 }}</div>
                        <div class="stats-label">Hours Worked</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="stats-icon bg-black">
                        <i class="fas fa-sign-in-alt text-white"></i>
                    </div>
                    <div>
                        <div class="stats-number">{{ $workHours['sessions'] ? count($workHours['sessions']) : 0 }}</div>
                        <div class="stats-label">Check-ins</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="stats-icon bg-black">
                        <i class="fas fa-eye text-white"></i>
                    </div>
                    <div>
                        <div class="stats-number">{{ $pageVisits->count() }}</div>
                        <div class="stats-label">Page Views</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="stats-icon bg-black">
                        <i class="fas fa-tasks text-white"></i>
                    </div>
                    <div>
                        <div class="stats-number">{{ $activities->count() }}</div>
                        <div class="stats-label">Activities</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Work Hours Chart -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Work Hours Timeline</h5>
                    </div>
                    <div class="card-body">
                        @if($workHours['sessions'] && count($workHours['sessions']) > 0)
                            <div class="timeline">
                                @foreach($workHours['sessions'] as $session)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Check-in: {{ $session['check_in'] }}</h6>
                                            @if($session['check_out'])
                                                <p class="text-muted mb-1">Check-out: {{ $session['check_out'] }}</p>
                                                <small class="text-success">Duration: {{ $session['duration'] }}</small>
                                            @else
                                                <p class="text-warning">Currently checked in</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No work sessions recorded for this date</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Activities Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Activities</h5>
                    </div>
                    <div class="card-body">
                        @if($activities->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Time</th>
                                            <th>Activity</th>
                                            <th>Page</th>
                                            <th>IP & Location</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activities as $activity)
                                            <tr>
                                                <td>{{ $activity->activity_time->format('H:i:s') }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $activity->activity_type === 'check_in' ? 'success' : ($activity->activity_type === 'check_out' ? 'danger' : 'info') }}">
                                                        {{ ucfirst(str_replace('_', ' ', $activity->activity_type)) }}
                                                    </span>
                                                </td>
                                                <td>{{ $activity->page_url ?? 'N/A' }}</td>
                                                <td>
                                                    <div class="fw-semibold">{{ $activity->ip_address ?? 'N/A' }}</div>
                                                    <small class="text-muted">
                                                        {{ $activity->location_city }}, {{ $activity->location_country }}
                                                    </small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No activities recorded for this date</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e5e7eb;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e5e7eb;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #10b981;
}
</style>
@endsection
