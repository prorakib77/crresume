@extends('layouts.app')

@section('title', 'Client Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Header -->
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 #0f172a">My Work Updates</h1>
                <div class="text-muted">{{ now()->format('F j, Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Applications
                            </div>
                            <div class="h5 mb-0 font-weight-bold #0f172a">{{ $stats['total_updates'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-briefcase fa-2x text-muted300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                This Month
                            </div>
                            <div class="h5 mb-0 font-weight-bold #0f172a">{{ $stats['this_month'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-muted300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Assigned Agents
                            </div>
                            <div class="h5 mb-0 font-weight-bold #0f172a">{{ $stats['assigned_agents'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-tie fa-2x text-muted300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Last Update
                            </div>
                            <div class="h6 mb-0 font-weight-bold #0f172a">
                                @if($stats['last_update'])
                                    {{ $stats['last_update']->applied_date->format('M j') }}
                                @else
                                    None
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-muted300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Download Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h6 class="mb-0">
                                <i class="fas fa-download me-2"></i>Download Reports
                            </h6>
                            <p class="text-muted mb-0 mt-1">Export your work updates as PDF or Word document</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <a href="{{ route('workUpdates.download.pdf') }}" class="btn btn-danger me-2">
                                <i class="fas fa-file-pdf me-2"></i>Download PDF
                            </a>
                            <a href="{{ route('workUpdates.download.doc') }}" class="btn btn-primary">
                                <i class="fas fa-file-word me-2"></i>Download DOC
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Work Updates Grid -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>Work Updates
                    </h6>
                </div>
                <div class="card-body">
                    @if($workUpdates->count() > 0)
                        <div class="row">
                            @foreach($workUpdates as $update)
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3 h-100 bg-white">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="fw-bold text-dark">{{ $update->job_title }}</div>
                                                <small class="text-muted">{{ $update->company }}</small>
                                            </div>
                                            @php
                                                $statusColors = [
                                                    'applied' => 'primary',
                                                    'interview' => 'warning',
                                                    'hired' => 'success',
                                                    'rejected' => 'danger',
                                                    'incomplete' => 'secondary',
                                                ];
                                                $color = $statusColors[$update->application_status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }}">{{ $update->getApplicationStatusLabel() }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <div>
                                                <small class="text-muted d-block">Applied</small>
                                                <div class="fw-semibold">{{ $update->applied_date->format('M j, Y') }}</div>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted d-block">Method</small>
                                                <div class="fw-semibold">{{ $update->getAppliedMethodLabel() }}</div>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">Links</small>
                                            <div class="d-flex gap-2 mt-1">
                                                @if($update->job_link)
                                                    <a href="{{ $update->job_link }}" target="_blank" class="btn btn-outline-primary btn-sm">Job</a>
                                                @endif
                                                @if($update->job_success_link)
                                                    <a href="{{ $update->job_success_link }}" target="_blank" class="btn btn-outline-primary btn-sm">Success</a>
                                                @endif
                                            </div>
                                        </div>
                                        @if($update->note)
                                            <div class="mt-2 p-2 rounded border bg-light">
                                                <small class="text-muted d-block mb-1">Note</small>
                                                <div>{{ $update->note }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted small">
                                Showing {{ $workUpdates->firstItem() ?? 0 }} to {{ $workUpdates->lastItem() ?? 0 }} of {{ $workUpdates->total() }} results
                            </div>
                            <div>
                                {{ $workUpdates->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-list fa-4x text-muted300 mb-4"></i>
                            <h5 class="text-muted600">No Work Updates Yet</h5>
                            <p class="text-muted">Your assigned agents haven't submitted any work updates for your account yet.</p>
                            <div class="mt-4">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>What happens next?</strong><br>
                                    Your assigned agents will submit daily job applications on your behalf.
                                    All approved applications will appear here, and you can download reports at any time.
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-left: 0.25rem solid #4e73df !important;
}
.card {
    border-left: 0.25rem solid #1cc88a !important;
}
.card {
    border-left: 0.25rem solid #36b9cc !important;
}
.card {
    border-left: 0.25rem solid #f6c23e !important;
}

.table td {
    vertical-align: middle;
}

.btn-group-vertical .btn {
    margin-bottom: 2px;
}
</style>
@endsection




