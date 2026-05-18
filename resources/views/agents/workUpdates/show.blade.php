@extends('layouts.bootstrap_master')

@section('title', 'Work Update Details')
@section('page-title', 'Work Update Details')
@section('page-subtitle', 'View job application details')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">
                        <i class="fas fa-briefcase me-2"></i>{{ $workUpdate->job_title }}
                    </h5>
                    <small class="text-muted">{{ $workUpdate->company }}</small>
                </div>
                <div>
                    @if($workUpdate->status === 'pending')
                        <span class="badge bg-warning fs-6">Pending Review</span>
                    @elseif($workUpdate->status === 'approved')
                        <span class="badge bg-success fs-6">Approved</span>
                    @elseif($workUpdate->status === 'rejected')
                        <span class="badge bg-danger fs-6">Rejected</span>
                    @else
                        <span class="badge bg-secondary fs-6">{{ ucfirst($workUpdate->status) }}</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2"></i>Application Details</h6>

                        <div class="mb-3">
                            <label class="fw-bold">Job Title:</label>
                            <p class="mb-1">{{ $workUpdate->job_title }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="fw-bold">Company:</label>
                            <p class="mb-1">{{ $workUpdate->company }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="fw-bold">Applied Date:</label>
                            <p class="mb-1">{{ \Carbon\Carbon::parse($workUpdate->applied_date)->format('F d, Y') }}</p>
                        </div>

                        @if($workUpdate->job_link)
                            <div class="mb-3">
                                <label class="fw-bold">Job Link:</label>
                                <p class="mb-1">
                                    <a href="{{ $workUpdate->job_link }}" target="_blank" class="text-primary">
                                        <i class="fas fa-external-link-alt me-1"></i>View Job Posting
                                    </a>
                                </p>
                            </div>
                        @endif

                        @if($workUpdate->job_success_link)
                            <div class="mb-3">
                                <label class="fw-bold">Job Success Link:</label>
                                <p class="mb-1">
                                    <a href="{{ $workUpdate->job_success_link }}" target="_blank" class="text-success">
                                        <i class="fas fa-check-circle me-1"></i>View Success Proof
                                    </a>
                                </p>
                            </div>
                        @endif

                        @if($workUpdate->applied_method)
                            <div class="mb-3">
                                <label class="fw-bold">Application Method:</label>
                                <p class="mb-1">{{ ucfirst($workUpdate->applied_method) }}</p>
                            </div>
                        @endif

                        @if($workUpdate->application_status)
                            <div class="mb-3">
                                <label class="fw-bold">Application Status:</label>
                                <p class="mb-1">
                                    <span class="badge bg-info">{{ ucfirst($workUpdate->application_status) }}</span>
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3"><i class="fas fa-users me-2"></i>Client & Agent Info</h6>

                        <div class="mb-3">
                            <label class="fw-bold">Client:</label>
                            <p class="mb-1">
                                @if($workUpdate->client)
                                    {{ $workUpdate->client->name }}
                                    <br><small class="text-muted">{{ $workUpdate->client->email }}</small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="fw-bold">Agent:</label>
                            <p class="mb-1">
                                @if($workUpdate->agent)
                                    {{ $workUpdate->agent->name }}
                                    <br><small class="text-muted">{{ $workUpdate->agent->email }}</small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="fw-bold">Submitted On:</label>
                            <p class="mb-1">{{ $workUpdate->created_at->format('F d, Y \\a\\t g:i A') }}</p>
                        </div>

                        @if($workUpdate->status === 'approved' && $workUpdate->approved_at)
                            <div class="mb-3">
                                <label class="fw-bold">Approved On:</label>
                                <p class="mb-1 text-success">{{ $workUpdate->approved_at->format('F d, Y \\a\\t g:i A') }}</p>
                            </div>
                        @endif

                        @if($workUpdate->status === 'rejected' && $workUpdate->rejected_at)
                            <div class="mb-3">
                                <label class="fw-bold">Rejected On:</label>
                                <p class="mb-1 text-danger">{{ $workUpdate->rejected_at->format('F d, Y \\a\\t g:i A') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                @if($workUpdate->note)
                    <hr>
                    <div class="mb-3">
                        <h6 class="fw-bold mb-3"><i class="fas fa-comment me-2"></i>Notes</h6>
                        <div class="bg-light p-3 rounded">
                            {{ $workUpdate->note }}
                        </div>
                    </div>
                @endif

                @if($workUpdate->status === 'rejected' && $workUpdate->rejection_reason)
                    <hr>
                    <div class="mb-3">
                        <h6 class="fw-bold mb-3 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Rejection Reason</h6>
                        <div class="alert alert-danger">
                            {{ $workUpdate->rejection_reason }}
                        </div>
                    </div>
                @endif

                @if($workUpdate->applied_proof)
                    <hr>
                    <div class="mb-3">
                        <h6 class="fw-bold mb-3"><i class="fas fa-paperclip me-2"></i>Applied Proof</h6>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-file-alt fa-2x text-primary me-3"></i>
                            <div>
                                <p class="mb-1">
                                    <a href="{{ storage_public_url($workUpdate->applied_proof) }}" target="_blank" class="text-primary">
                                        <i class="fas fa-download me-1"></i>Download Proof
                                    </a>
                                </p>
                                <small class="text-muted">{{ basename($workUpdate->applied_proof) }}</small>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('workUpdates.index') }}" class="btn btn-white">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>

                    {{-- Delete option hidden for agents --}}
                </div>
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
function deleteUpdate(updateId) {
    const form = document.getElementById('deleteForm');
    form.action = `/work-updates/${updateId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
