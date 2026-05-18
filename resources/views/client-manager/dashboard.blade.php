@extends('layouts.bootstrap_master')

@section('title', 'Client Manager Dashboard')
@section('page-title', 'Client Manager Dashboard')
@section('page-subtitle', 'Welcome back, ' . auth()->user()->name)

@section('content')
<div class="row">
    <!-- Stats Cards -->
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-user-friends fa-2x text-primary"></i>
                    </div>
                </div>
                @php $totalClients = \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'client'))->count(); @endphp
                <h4 class="text-dark mb-1">{{ $totalClients }}</h4>
                <small class="text-muted">Total Clients</small>
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
                @php $activeClients = \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'client'))->where('status', 'active')->count(); @endphp
                <h4 class="text-dark mb-1">{{ $activeClients }}</h4>
                <small class="text-muted">Active Clients</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-clipboard-list fa-2x text-warning"></i>
                    </div>
                </div>
                @php $clientUpdates = \App\Models\WorkUpdate::count(); @endphp
                <h4 class="text-dark mb-1">{{ $clientUpdates }}</h4>
                <small class="text-muted">Work Updates</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="bg-info bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-user-plus fa-2x text-info"></i>
                    </div>
                </div>
                @php $thisMonth = \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'client'))->whereMonth('created_at', now()->month)->count(); @endphp
                <h4 class="text-dark mb-1">{{ $thisMonth }}</h4>
                <small class="text-muted">New This Month</small>
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
                        <a href="{{ route('clients.create') }}" class="btn btn-primary w-100">
                            <i class="fas fa-user-plus d-block mb-1"></i>
                            <small>Add Client</small>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('clients.index') }}" class="btn btn-success w-100">
                            <i class="fas fa-list d-block mb-1"></i>
                            <small>Manage Clients</small>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('workUpdates.index') }}" class="btn btn-view w-100">
                            <i class="fas fa-clipboard-check d-block mb-1"></i>
                            <small>View Updates</small>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('agents.assign') }}" class="btn btn-white w-100">
                            <i class="fas fa-link d-block mb-1"></i>
                            <small>Agent Assignments</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Clients -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-friends me-2"></i>Recent Clients
                </h5>
                <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @php $recentClients = \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'client'))->latest()->take(5)->get(); @endphp
                @if($recentClients->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentClients as $client)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-1">{{ $client->name }}</h6>
                                    <small class="text-muted">{{ $client->email }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $client->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($client->status ?? 'active') }}
                                    </span>
                                    <br>
                                    <small class="text-muted">{{ $client->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-user-plus fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No clients yet</p>
                        <a href="{{ route('clients.create') }}" class="btn btn-sm btn-primary mt-2">Add First Client</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Client Overview -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>Client Overview
                </h5>
                <div>
                    <span class="badge bg-primary">{{ $totalClients }} Total</span>
                    <span class="badge bg-success">{{ $activeClients }} Active</span>
                </div>
            </div>
            <div class="card-body">
                @if($recentClients->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Work Updates</th>
                                    <th>Last Login</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentClients->take(10) as $client)
                                    <tr>
                                        <td>{{ $client->name }}</td>
                                        <td>{{ $client->email }}</td>
                                        <td>
                                            <span class="badge bg-{{ $client->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($client->status ?? 'active') }}
                                            </span>
                                        </td>
                                        <td>
                                            @php $clientWorkUpdates = \App\Models\WorkUpdate::where('client_id', $client->id)->count(); @endphp
                                            <span class="badge bg-info">{{ $clientWorkUpdates }}</span>
                                        </td>
                                        <td>{{ $client->last_login_at ? $client->last_login_at->diffForHumans() : 'Never' }}</td>
                                        <td>{{ $client->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('workUpdates.index') }}?client={{ $client->id }}" class="btn btn-primary btn-sm" title="View Updates">
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
                        <i class="fas fa-user-friends fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No clients found</p>
                        <a href="{{ route('clients.create') }}" class="btn btn-primary mt-2">Add Your First Client</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
