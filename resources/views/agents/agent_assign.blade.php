@extends('layouts.dashboard_master')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Agent Assignment Management</h2>
            <a href="{{ route('assign.form') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Assign New Client
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle"></i> {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow-sm mb-5">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users"></i> Client Assignment Overview
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 20%">Client Name</th>
                                <th style="width: 15%">Status</th>
                                <th style="width: 35%">Assigned Agents</th>
                                <th style="width: 25%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clients as $client)
                                @php
                                    $clientProfile = $client->clientProfile;
                                    $assignedAgents = $client->assignedAgents ?? collect();

                                    // Map integer status to readable text
                                    $statusMap = [
                                        0 => 'Inactive',
                                        1 => 'Assigned',
                                        2 => 'Active',
                                        3 => 'Completed',
                                    ];

                                    // Map status to badge color
                                    $statusColorMap = [
                                        0 => 'secondary', // Inactive
                                        1 => 'primary',   // Assigned
                                        2 => 'success',   // Active
                                        3 => 'info',      // Completed
                                    ];

                                    // Default values for clients without profiles
                                    $statusText = 'Not set';
                                    $statusColor = 'warning';

                                    // Only set status values if client profile exists and has status
                                    if ($clientProfile && isset($clientProfile->status)) {
                                        $statusText = $statusMap[$clientProfile->status] ?? 'Unknown';
                                        $statusColor = $statusColorMap[$clientProfile->status] ?? 'warning';
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $client->name ?? '-' }}</div>
                                        <small class="text-muted">{{ $client->email }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $statusColor }} rounded-pill">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($assignedAgents && $assignedAgents->count() > 0)
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($assignedAgents as $agent)
                                                    <span class="badge bg-success rounded-pill">
                                                        <i class="fas fa-user-tie"></i> {{ $agent->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted fst-italic">
                                                <i class="fas fa-exclamation-triangle"></i> No agents assigned
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('assign.form', ['client' => $client->id]) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="Manage Assignments">
                                                <i class="fas fa-edit"></i> Manage
                                            </a>

                                            @if($assignedAgents && $assignedAgents->count() > 0)
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-info"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#viewAssignmentsModal{{ $client->id }}"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                {{-- Modal for viewing assignments --}}
                                @if($assignedAgents && $assignedAgents->count() > 0)
                                    <div class="modal fade" id="viewAssignmentsModal{{ $client->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">
                                                        <i class="fas fa-user"></i> {{ $client->name }} - Agent Assignments
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="list-group">
                                                        @foreach($assignedAgents as $agent)
                                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <h6 class="mb-1">{{ $agent->name }}</h6>
                                                                    <small class="text-muted">{{ $agent->email }}</small>
                                                                </div>
                                                                <form action="{{ route('assign.remove') }}" method="POST"
                                                                      onsubmit="return confirm('Are you sure you want to remove this assignment?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                                                                    <input type="hidden" name="agent_id" value="{{ $agent->id }}">
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                        <i class="fas fa-trash"></i> Remove
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                        <h5>No clients found</h5>
                                        <p>Start by <a href="{{ route('clients.create') }}">creating a new client</a></p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Summary Statistics --}}
        <div class="row">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3>{{ $clients->count() }}</h3>
                        <p class="mb-0">Total Clients</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3>{{ $clients->filter(function($c) { return $c->assignedAgents && $c->assignedAgents->count() > 0; })->count() }}</h3>
                        <p class="mb-0">Assigned Clients</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h3>{{ $clients->filter(function($c) { return !$c->assignedAgents || $c->assignedAgents->count() == 0; })->count() }}</h3>
                        <p class="mb-0">Unassigned Clients</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3>{{ $agents->count() }}</h3>
                        <p class="mb-0">Available Agents</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
