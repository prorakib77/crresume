@extends('layouts.bootstrap_master')

@section('title', 'My Work Updates')
@section('page-title', 'My Work Updates')
@section('page-subtitle', 'Track your submitted job applications')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">My Work Updates</h2>
                <p class="text-muted mb-0">Track your submitted job applications and their status</p>
            </div>
            <a href="{{ route('workUpdates.create') }}" class="btn btn-create">
                <i class="fas fa-plus me-2"></i>Submit New Updates
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            @php
                $totalUpdates = $workUpdates->total() ?? 0;
                $pendingUpdates = $workUpdates->where('status', 'pending')->count();
                $approvedUpdates = $workUpdates->where('status', 'approved')->count();
                $rejectedUpdates = $workUpdates->where('status', 'rejected')->count();
            @endphp
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3>{{ $totalUpdates }}</h3>
                        <p class="mb-0">Total Applications</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h3>{{ $pendingUpdates }}</h3>
                        <p class="mb-0">Pending Review</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3>{{ $approvedUpdates }}</h3>
                        <p class="mb-0">Approved</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h3>{{ $rejectedUpdates }}</h3>
                        <p class="mb-0">Rejected</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Work Updates Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clipboard-list me-2"></i>My Applications
                </h5>
            </div>
            <div class="card-body">
                <!-- Search Box -->
                <div class="table-search mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" id="workUpdateSearch" class="form-control search-input"
                                   placeholder="Search by job title, company, client...">
                        </div>
                    </div>
                </div>

                @if($workUpdates->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover" id="workUpdatesTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Job Title</th>
                                    <th>Company</th>
                                    <th>Client</th>
                                    <th>Applied Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($workUpdates as $update)
                                    <tr>
                                        <td>{{ $update->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <strong>{{ $update->job_title }}</strong>
                                            @if($update->job_link)
                                                <br><a href="{{ $update->job_link }}" target="_blank" class="text-primary small">
                                                    <i class="fas fa-external-link-alt me-1"></i>View Job
                                                </a>
                                            @endif
                                        </td>
                                        <td>{{ $update->company }}</td>
                                        <td>
                                            @if($update->client)
                                                {{ $update->client->name }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($update->applied_date)->format('M d, Y') }}</td>
                                        <td>
                                            @if($update->status === 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif($update->status === 'approved')
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($update->status === 'rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($update->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('workUpdates.show', $update) }}" class="btn btn-sm btn-view" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                {{-- Delete option hidden for agents --}}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if(method_exists($workUpdates, 'links'))
                        <x-beautiful-pagination :paginator="$workUpdates" />
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No work updates submitted yet</h5>
                        <p class="text-muted mb-4">Start tracking your job applications by submitting your first work update.</p>
                        <a href="{{ route('workUpdates.create') }}" class="btn btn-create">
                            <i class="fas fa-plus me-2"></i>Submit First Update
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Work Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this work update? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Search functionality
    $('#workUpdateSearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#workUpdatesTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});

function deleteUpdate(updateId) {
    const form = document.getElementById('deleteForm');
    form.action = `/work-updates/${updateId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
