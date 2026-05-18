@extends('layouts.bootstrap_master')

@section('title', 'Agent Manager Dashboard')
@section('page-title', 'Agent Manager Dashboard')
@section('page-subtitle', 'Welcome back, ' . auth()->user()->name)

@section('content')
<div class="row">
    <!-- Stats Cards -->
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-clipboard-list fa-2x text-primary"></i>
                    </div>
                </div>
                @php $totalUpdates = \App\Models\WorkUpdate::count(); @endphp
                <h4 class="text-dark mb-1">{{ $totalUpdates }}</h4>
                <small class="text-muted">Total Work Updates</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-clock fa-2x text-warning"></i>
                    </div>
                </div>
                @php $pendingUpdates = \App\Models\WorkUpdate::where('status', 'submitted')->count(); @endphp
                <h4 class="text-dark mb-1">{{ $pendingUpdates }}</h4>
                <small class="text-muted">Pending Approvals</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                </div>
                @php $approvedUpdates = \App\Models\WorkUpdate::where('status', 'approved')->count(); @endphp
                <h4 class="text-dark mb-1">{{ $approvedUpdates }}</h4>
                <small class="text-muted">Approved Updates</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="bg-info bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-users fa-2x text-info"></i>
                    </div>
                </div>
                @php $activeAgents = \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'agent-manager'))->count(); @endphp
                <h4 class="text-dark mb-1">{{ $activeAgents }}</h4>
                <small class="text-muted">Agent Managers</small>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
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
                        <a href="{{ route('workUpdates.create') }}" class="btn btn-primary w-100">
                            <i class="fas fa-plus d-block mb-1"></i>
                            <small>Submit Work Update</small>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('workUpdates.index') }}" class="btn btn-success w-100">
                            <i class="fas fa-check-circle d-block mb-1"></i>
                            <small>Review & Approve</small>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('agents.assign') }}" class="btn btn-info w-100">
                            <i class="fas fa-link d-block mb-1"></i>
                            <small>Agent Assignments</small>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('workUpdates.my') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-list d-block mb-1"></i>
                            <small>My Updates</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Work Updates -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clipboard-list me-2"></i>Recent Work Updates
                </h5>
                <a href="{{ route('workUpdates.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @php $recentUpdates = \App\Models\WorkUpdate::with(['agent', 'client'])->latest()->take(5)->get(); @endphp
                @if($recentUpdates->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentUpdates as $update)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-1">{{ $update->job_title }}</h6>
                                    <small class="text-muted">{{ $update->company }} • {{ $update->agent->name ?? 'N/A' }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $update->status === 'approved' ? 'success' : ($update->status === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($update->status) }}
                                    </span>
                                    <br>
                                    <small class="text-muted">{{ $update->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-clipboard fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No work updates yet</p>
                        <a href="{{ route('workUpdates.create') }}" class="btn btn-sm btn-primary mt-2">Submit First Update</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Pending Approvals -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clock me-2"></i>Pending Approvals
                </h5>
                <span class="badge bg-warning">{{ $pendingUpdates }} Pending</span>
            </div>
            <div class="card-body">
                @php $pendingWorkUpdates = \App\Models\WorkUpdate::with(['agent', 'client'])->where('status', 'submitted')->latest()->take(10)->get(); @endphp
                @if($pendingWorkUpdates->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Job Title</th>
                                    <th>Company</th>
                                    <th>Agent</th>
                                    <th>Client</th>
                                    <th>Date Applied</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingWorkUpdates as $update)
                                    <tr>
                                        <td>{{ $update->job_title }}</td>
                                        <td>{{ $update->company }}</td>
                                        <td>{{ $update->agent->name ?? 'N/A' }}</td>
                                        <td>{{ $update->client->name ?? 'N/A' }}</td>
                                        <td>{{ $update->applied_date->format('M d, Y') }}</td>
                                        <td>{{ $update->created_at->diffForHumans() }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <form method="POST" action="{{ route('workUpdates.approve', $update) }}" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm" title="Approve">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <a href="{{ route('workUpdates.show', $update) }}" class="btn btn-primary btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <p class="text-muted mb-0">No pending approvals!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
