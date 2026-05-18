@extends('layouts.bootstrap_master')

@section('title', 'Agent Dashboard')
@section('pageTitle', 'Agent Dashboard')
@section('pageSubtitle', 'Welcome back, ' . auth()->user()->name)

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-black me-3">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="stats-number">{{ $stats['total_clients'] }}</div>
                    <div class="stats-label">Assigned Clients</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-black me-3">
                    <i class="fas fa-check"></i>
                </div>
                <div>
                    <div class="stats-number">{{ $stats['submitted_today'] }}</div>
                    <div class="stats-label">Submitted Today</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-black me-3">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <div class="stats-number">{{ $stats['pending_submissions'] }}</div>
                    <div class="stats-label">Pending Today</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-black me-3">
                    <i class="fas fa-calendar"></i>
                </div>
                <div>
                    <div class="stats-number">{{ $stats['this_month'] }}</div>
                    <div class="stats-label">This Month</div>
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
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Service Ends</th>
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
                                            @if($clientStatus['has_submitted_today'])
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
                                            @if($clientStatus['has_submitted_today'])
                                                <a href="{{ route('workUpdates.show', $clientStatus['submission']->id) }}"
                                                   class="btn btn-sm btn-view" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('workUpdates.create', ['client_id' => $clientStatus['client']->id]) }}"
                                                   class="btn btn-sm btn-create" title="Submit Update">
                                                    <i class="fas fa-plus"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
                                        <div class="small text-muted">{{ $update->applied_date->format('M j') }}</div>
                                        <div class="fw-bold">{{ $update->job_title }}</div>
                                        <div class="small text-muted">{{ $update->company }}</div>
                                        <div class="small">
                                            <span class="badge bg-{{ $update->status === 'approved' ? 'success' : ($update->status === 'rejected' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($update->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('workUpdates.index') }}" class="btn btn-sm btn-outline-primary">
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
                        <a href="{{ route('workUpdates.create') }}" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Submit Work Update
                        </a>
                    @endif
                    <a href="{{ route('workUpdates.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-list me-2"></i>View My Updates
                    </a>
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">
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
@endsection
