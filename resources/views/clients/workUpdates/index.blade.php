@extends('layouts.bootstrap_master')

@section('title', 'My Work Updates')
@section('page-title', 'My Work Updates')
@section('page-subtitle', 'View all job applications submitted on your behalf')

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Applications
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_updates'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-briefcase fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                This Month
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['this_month'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Last Update
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['last_update'] ? $stats['last_update']->applied_date->format('M j, Y') : 'No updates yet' }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Work Updates Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">My Job Applications</h6>
            <div class="dropdown">
                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i> Download
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('workUpdates.download.pdf') }}">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('workUpdates.download.doc') }}">
                        <i class="fas fa-file-word"></i> Download Word
                    </a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            @if($workUpdates->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Job Title</th>
                                <th>Company</th>
                                <th>Applied Method</th>
                                <th>Status</th>
                                <th>Agent</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($workUpdates as $update)
                                <tr>
                                    <td>{{ $update->applied_date->format('M j, Y') }}</td>
                                    <td>
                                        <strong>{{ $update->job_title }}</strong>
                                        @if($update->job_link)
                                            <br><a href="{{ $update->job_link }}" target="_blank" class="text-primary">
                                                <i class="fas fa-external-link-alt"></i> View Job
                                            </a>
                                        @endif
                                    </td>
                                    <td>{{ $update->company }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $update->getAppliedMethodLabel() }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $update->application_status === 'hired' ? 'success' : ($update->application_status === 'rejected' ? 'danger' : 'info') }}">
                                            {{ ucfirst($update->application_status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                                    S
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fw-bold">Support Team</div>
                                                <small class="text-muted">Updates submitted by our team</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('workUpdates.show', $update) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Beautiful Pagination -->
                <x-beautiful-pagination :paginator="$workUpdates" />
            @else
                <div class="text-center py-5">
                    <i class="fas fa-briefcase fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">No Job Applications Yet</h5>
                    <p class="text-muted">Your agent hasn't submitted any job applications for you yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
