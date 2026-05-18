@extends('layouts.bootstrap_master')

@section('title', 'Approved Work Updates')
@section('page-title', 'Approved Work Updates')
@section('page-subtitle', 'View approved job applications submitted for you')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Approved Work Updates</h2>
                <p class="text-muted mb-0">View job applications submitted by your assigned agents</p>
            </div>
        </div>

        <!-- Stats Card -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3>{{ $workUpdates->total() ?? 0 }}</h3>
                        <p class="mb-0">Approved Applications</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3>{{ $workUpdates->where('created_at', '>=', now()->startOfMonth())->count() }}</h3>
                        <p class="mb-0">This Month</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3>{{ $workUpdates->where('created_at', '>=', now()->startOfWeek())->count() }}</h3>
                        <p class="mb-0">This Week</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h3>{{ $workUpdates->where('created_at', '>=', now()->startOfDay())->count() }}</h3>
                        <p class="mb-0">Today</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Work Updates Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-check-circle me-2"></i>Approved Applications
                </h5>
            </div>
            <div class="card-body">
                <!-- Search Box -->
                <div class="table-search mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" id="workUpdateSearch" class="form-control search-input"
                                   placeholder="Search by job title, company, agent...">
                        </div>
                    </div>
                </div>

                @if($workUpdates->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover" id="workUpdatesTable">
                            <thead>
                                <tr>
                                    <th>Submitted Date</th>
                                    <th>Job Title</th>
                                    <th>Company</th>
                                    <th>Agent</th>
                                    <th>Applied Date</th>
                                    <th>Approved Date</th>
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
                                            @if($update->agent)
                                                {{ $update->agent->name }}
                                                <br><small class="text-muted">{{ $update->agent->email }}</small>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($update->applied_date)->format('M d, Y') }}</td>
                                        <td>
                                            @if($update->approved_at)
                                                {{ $update->approved_at->format('M d, Y') }}
                                                <br><small class="text-muted">{{ $update->approved_at->format('g:i A') }}</small>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('workUpdates.show', $update) }}" class="btn btn-sm btn-view" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
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
                        <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No approved work updates yet</h5>
                        <p class="text-muted mb-4">Your assigned agents haven't submitted any approved applications yet.</p>
                    </div>
                @endif
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
</script>
@endpush
