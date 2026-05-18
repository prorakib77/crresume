@extends('layouts.bootstrap_master')

@section('title', 'Clients')
@section('page-title', 'Clients Management')
@section('page-subtitle', 'Manage all client profiles')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>All Clients
                    </h5>
                    <a href="{{ route('clients.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Client
                    </a>
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

                    <div class="table-responsive">
                        <table class="table table-hover" id="clientsTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Service Period</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clients as $client)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                                    <i class="fas fa-user text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $client->user->name ?? '-' }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $client->user->email ?? '-' }}</td>
                                        <td>{{ $client->phone ?? '-' }}</td>
                                        <td>{{ Str::limit($client->address ?? '-', 30) }}</td>
                                        <td>
                                            @if($client->service_start_date && $client->service_end_date)
                                                <small class="text-muted">
                                                    {{ Carbon\Carbon::parse($client->service_start_date)->format('M d, Y') }}<br>
                                                    to {{ Carbon\Carbon::parse($client->service_end_date)->format('M d, Y') }}
                                                </small>
                                            @else
                                                <span class="text-muted">Not set</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($client->status == 1)
                                                <span class="badge bg-success">Assigned</span>
                                            @else
                                                <span class="badge bg-secondary">Not Assigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @if($client->resume)
                                                    <a href="#" class="btn btn-sm btn-outline-primary"
                                                       data-bs-toggle="modal" data-bs-target="#filePreviewModal"
                                                       data-file="{{ asset($client->resume) }}" title="View Resume">
                                                        <i class="fas fa-file-alt"></i>
                                                    </a>
                                                @endif
                                                <a href="#" class="btn btn-sm btn-outline-secondary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-users fa-2x mb-3"></i>
                                            <p class="mb-0">No clients found.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- File Preview Modal -->
<div class="modal fade" id="filePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">File Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="height: 80vh;">
                <iframe id="fileFrame" src="" width="100%" height="100%" frameborder="0"></iframe>
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

    // File preview modal
    $('#filePreviewModal').on('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const fileUrl = button.getAttribute('data-file');
        const extension = fileUrl.split('.').pop().toLowerCase();
        let viewerUrl = '';

        if (extension === 'pdf') {
            viewerUrl = fileUrl;
        } else if (['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(extension)) {
            viewerUrl = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(fileUrl)}`;
        } else {
            viewerUrl = fileUrl;
        }

        $('#fileFrame').attr('src', viewerUrl);
    });

    // Clear iframe when modal closes
    $('#filePreviewModal').on('hidden.bs.modal', function () {
        $('#fileFrame').attr('src', '');
    });
});
</script>
@endpush
