@extends('layouts.app')

@section('title', 'Daily Agent Report')
@section('pageTitle', 'Daily Agent Report')
@section('pageSubtitle', 'Overview of all agent activities for {{ $selectedDate->format("M d, Y") }}')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Daily Agent Report</h2>
                <p class="text-muted mb-0">Comprehensive overview of agent activities and performance</p>
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
                        <i class="fas fa-search me-2"></i>Generate Report
                    </button>
                </form>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="stats-icon bg-black">
                        <i class="fas fa-users text-white"></i>
                    </div>
                    <div>
                        <div class="stats-number">{{ $report['total_agents'] }}</div>
                        <div class="stats-label">Total Agents</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="stats-icon bg-black">
                        <i class="fas fa-user-check text-white"></i>
                    </div>
                    <div>
                        <div class="stats-number">{{ $report['active_agents'] }}</div>
                        <div class="stats-label">Active Today</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="stats-icon bg-black">
                        <i class="fas fa-clock text-white"></i>
                    </div>
                    <div>
                        <div class="stats-number">{{ $report['total_hours'] }}</div>
                        <div class="stats-label">Total Hours</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="stats-icon bg-black">
                        <i class="fas fa-eye text-white"></i>
                    </div>
                    <div>
                        <div class="stats-number">{{ $report['total_page_views'] }}</div>
                        <div class="stats-label">Page Views</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Agent Performance Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Agent Performance Summary</h5>
                    </div>
                    <div class="card-body">
                        @if($report['agents'] && count($report['agents']) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Agent</th>
                                            <th>Status</th>
                                            <th>Work Hours</th>
                                            <th>Check-ins</th>
                                            <th>Page Views</th>
                                            <th>Last Activity</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($report['agents'] as $agent)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                            {{ substr($agent['name'], 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold">{{ $agent['name'] }}</div>
                                                            <small class="text-muted">{{ $agent['email'] }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($agent['is_active'])
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="fw-bold">{{ $agent['work_hours'] }}</span>
                                                    <small class="text-muted">hours</small>
                                                </td>
                                                <td>
                                                    <span class="fw-bold">{{ $agent['check_ins'] }}</span>
                                                    <small class="text-muted">sessions</small>
                                                </td>
                                                <td>
                                                    <span class="fw-bold">{{ $agent['page_views'] }}</span>
                                                    <small class="text-muted">views</small>
                                                </td>
                                                <td>
                                                    @if($agent['last_activity'])
                                                        <span class="text-success">{{ $agent['last_activity']->format('H:i:s') }}</span>
                                                        <small class="text-muted d-block">{{ $agent['last_activity']->format('M d') }}</small>
                                                    @else
                                                        <span class="text-muted">No activity</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.agents.show', $agent['route_key']) }}?date={{ $selectedDate->format('Y-m-d') }}"
                                                       class="btn btn-sm btn-view">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No agent data available for this date</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Timeline -->
        @if($report['recent_activities'] && count($report['recent_activities']) > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Activities</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @foreach($report['recent_activities'] as $activity)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-{{ $activity['type'] === 'check_in' ? 'success' : ($activity['type'] === 'check_out' ? 'danger' : 'info') }}"></div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $activity['agent_name'] }}</h6>
                                                <p class="text-muted mb-1">{{ ucfirst(str_replace('_', ' ', $activity['type'])) }}</p>
                                                @if($activity['page'])
                                                    <small class="text-info">{{ $activity['page'] }}</small>
                                                @endif
                                            </div>
                                            <small class="text-muted">{{ $activity['time'] }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 14px;
}

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
