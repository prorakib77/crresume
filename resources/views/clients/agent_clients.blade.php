@extends('layouts.bootstrap_master')

@section('title', 'My Clients')
@section('page-title', 'My Assigned Clients')
@section('page-subtitle', 'Manage your assigned clients')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>My Assigned Clients
                </h5>
                <span class="badge bg-primary">{{ $clients->count() }} Client(s)</span>
            </div>
            <div class="card-body">
                <!-- Search Box -->
                <div class="table-search mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" id="clientSearch" class="form-control search-input"
                                   placeholder="Search clients by name, email, phone...">
                        </div>
                    </div>
                </div>

                @if($clients->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover" id="clientsTable">
                            <thead>
                                <tr>
                                    <th>Client Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Work Updates</th>
                                    <th>Last Update</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clients as $client)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $client->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $client->email }}</td>
                                        <td>{{ $client->clientProfile->phone ?? 'N/A' }}</td>
                                        <td>
                                            @if($client->clientProfile)
                                                @if($client->clientProfile->status == 1)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            @else
                                                <span class="badge bg-warning">No Profile</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $updateCount = \App\Models\WorkUpdate::where('agent_id', auth()->id())
                                                    ->where('client_id', $client->id)
                                                    ->count();
                                            @endphp
                                            <span class="badge bg-info">{{ $updateCount }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $lastUpdate = \App\Models\WorkUpdate::where('agent_id', auth()->id())
                                                    ->where('client_id', $client->id)
                                                    ->latest()
                                                    ->first();
                                            @endphp
                                            {{ $lastUpdate ? $lastUpdate->created_at->diffForHumans() : 'Never' }}
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('workUpdates.create') }}?client_id={{ $client->id }}" class="btn btn-sm btn-primary" title="Create Work Update">
                                                    <i class="fas fa-plus"></i>
                                                </a>
                                                <a href="mailto:{{ $client->email }}" class="btn btn-sm btn-outline-primary" title="Send Email">
                                                    <i class="fas fa-envelope"></i>
                                                </a>
                                            </div>
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
                        <p class="text-muted mb-3">You don't have any clients assigned yet. Contact your administrator to get assigned to clients.</p>
                        <a href="{{ route('agent.dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Client Details Modal -->
<div class="modal fade" id="clientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Client Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="clientDetails">
                    <!-- Client details will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Search functionality
    $('#clientSearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#clientsTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>
@endpush
