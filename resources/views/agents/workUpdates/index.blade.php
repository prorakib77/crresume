@extends('layouts.bootstrap_master')

@section('title', 'Work Updates')
@section('page-title', 'Work Updates')
@section('page-subtitle', 'Track your submitted job applications')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="flex-grow-1">
                <h2 class="mb-1">My Work Updates</h2>
                <p class="text-muted mb-0 d-none d-md-block">Track your submitted job applications and their status</p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('workUpdates.create') }}" class="btn btn-create d-none d-md-inline-block">
                    <i class="fas fa-plus me-2"></i>Submit New Updates
                </a>
                <a href="{{ route('workUpdates.create') }}" class="btn btn-black d-md-none">
                    <i class="fas fa-plus"></i>
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            @php
                $totalUpdates = $workUpdates->total() ?? 0;
                $pendingUpdates = $workUpdates->where('status', 'pending')->count();
                $approvedUpdates = $workUpdates->where('status', 'approved')->count();
                $rejectedUpdates = $workUpdates->where('status', 'rejected')->count();
            @endphp
            <div class="col-lg-3 col-md-6 col-sm-6 col-6 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-black me-2 me-md-3">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stats-number">{{ $totalUpdates }}</div>
                            <div class="stats-label">Total Applications</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-6 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-black me-2 me-md-3">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stats-number">{{ $pendingUpdates }}</div>
                            <div class="stats-label">Pending Review</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-6 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-black me-2 me-md-3">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stats-number">{{ $approvedUpdates }}</div>
                            <div class="stats-label">Approved</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-6 mb-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-black me-2 me-md-3">
                            <i class="fas fa-times"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stats-number">{{ $rejectedUpdates }}</div>
                            <div class="stats-label">Rejected</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Work Updates Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clipboard-list me-2"></i>My Work Updates
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
                                    <th class="d-none-mobile" style="width: 120px;">Date</th>
                                    <th>Job Title</th>
                                    <th class="d-none-mobile">Company</th>
                                    <th class="d-none-mobile">Client</th>
                                    <th class="d-none-mobile" style="width: 120px;">Applied Date</th>
                                    <th style="width: 100px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($workUpdates as $update)
                                    <tr>
                                        <td class="d-none-mobile">
                                            <div class="d-flex flex-column">
                                                <strong class="text-dark">{{ $update->created_at->format('M j') }}</strong>
                                                <small class="text-muted">{{ $update->created_at->format('Y') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <strong>{{ $update->job_title }}</strong>
                                                <small class="text-muted d-block d-md-none">{{ $update->company }}</small>
                                                @if($update->job_link)
                                                    <a href="{{ $update->job_link }}" target="_blank" class="text-primary small">
                                                        <i class="fas fa-external-link-alt me-1"></i>View Job
                                                    </a>
                                                @endif
                                                <small class="text-muted d-block d-md-none">
                                                    <i class="fas fa-calendar me-1"></i>{{ $update->created_at->format('M j, Y') }}
                                                </small>
                                            </div>
                                        </td>
                                        <td class="d-none-mobile">{{ $update->company }}</td>
                                        <td class="d-none-mobile">
                                            @if($update->client)
                                                {{ $update->client->name }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td class="d-none-mobile">
                                            <div class="d-flex flex-column">
                                                <strong class="text-dark">{{ \Carbon\Carbon::parse($update->applied_date)->format('M j') }}</strong>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($update->applied_date)->format('Y') }}</small>
                                            </div>
                                        </td>
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
                        <a href="{{ route('workUpdates.create') }}" class="btn btn-create d-none d-md-inline-block">
                            <i class="fas fa-plus me-2"></i>Submit First Update
                        </a>
                        <a href="{{ route('workUpdates.create') }}" class="btn btn-black d-md-none">
                            <i class="fas fa-plus"></i>
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

@push('styles')
<style>
/* Mobile Table Optimizations for Work Updates */
@media (max-width: 768px) {
    .table thead th {
        font-size: 0.75rem;
        padding: 0.5rem 0.25rem;
        border-bottom: 2px solid #e5e7eb;
    }

    .table tbody td {
        font-size: 0.8rem;
        padding: 0.5rem 0.25rem;
        vertical-align: middle;
    }

    /* Job Title Column - Mobile Priority */
    .table tbody td:nth-child(2) {
        min-width: 200px;
    }

    /* Status Column */
    .table tbody td:nth-child(6) {
        width: 100px;
        min-width: 100px;
    }

    /* Date Columns - Better width */
    .table tbody td:nth-child(1),
    .table tbody td:nth-child(5) {
        width: 120px;
        min-width: 120px;
    }

    /* Date Display Styling */
    .table tbody td .d-flex.flex-column strong {
        font-size: 0.9rem;
        font-weight: 600;
        line-height: 1.2;
        color: #1f2937;
    }

    .table tbody td .d-flex.flex-column small {
        font-size: 0.75rem;
        line-height: 1.2;
        color: #6b7280;
    }

    /* Better spacing for date columns */
    .table tbody td:nth-child(1),
    .table tbody td:nth-child(5) {
        padding: 0.75rem 0.5rem;
        text-align: center;
    }

    /* Badge sizing for mobile */
    .table .badge {
        font-size: 0.65rem;
        padding: 0.25rem 0.5rem;
    }
}

@media (max-width: 480px) {
    .table thead th {
        font-size: 0.7rem;
        padding: 0.4rem 0.2rem;
    }

    .table tbody td {
        font-size: 0.75rem;
        padding: 0.4rem 0.2rem;
    }

    /* Even more compact on very small screens */
    .table tbody td .d-flex.flex-column small {
        font-size: 0.65rem;
    }

    .table .badge {
        font-size: 0.6rem;
        padding: 0.2rem 0.4rem;
    }

    /* Job title gets more space on very small screens */
    .table tbody td:nth-child(2) {
        min-width: 150px;
    }
}

/* Hide less important columns on mobile */
@media (max-width: 768px) {
    .table .d-none-mobile {
        display: none !important;
    }
}

/* Mobile-specific job title styling */
@media (max-width: 768px) {
    .table tbody td:nth-child(2) strong {
        font-size: 0.85rem;
        line-height: 1.3;
    }

    .table tbody td:nth-child(2) .text-muted {
        font-size: 0.7rem;
    }
}

/* Mobile Submit Button - Minimal Design */
@media (max-width: 768px) {
    .btn-black.d-md-none {
        background-color: #000000 !important;
        color: #ffffff !important;
        border: 1px solid #000000 !important;
        border-radius: 20px !important;
        padding: 0.5rem 1rem !important;
        font-size: 0.9rem !important;
        font-weight: 600 !important;
        min-width: 50px !important;
        height: 40px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
        transition: transform 0.3s ease, background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease !important;
    }

    .btn-black.d-md-none:hover {
        background-color: #333333 !important;
        border-color: #333333 !important;
        transform: scale(1.02) !important;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2) !important;
    }

    .btn-black.d-md-none:active {
        transform: scale(0.99) !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
    }

    .btn-black.d-md-none i {
        font-size: 0.9rem !important;
    }

    /* Empty state mobile button */
    .text-center .btn-black.d-md-none {
        margin-top: 1rem !important;
    }
}

/* Extra small mobile optimization */
@media (max-width: 480px) {
    .btn-black.d-md-none {
        padding: 0.4rem 0.8rem !important;
        font-size: 0.8rem !important;
        min-width: 45px !important;
        height: 36px !important;
        border-radius: 18px !important;
    }

    .btn-black.d-md-none i {
        font-size: 0.8rem !important;
    }
}
</style>
@endpush

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
