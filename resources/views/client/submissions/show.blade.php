<x-app-layout>
    <x-slot name="title">Submission Details</x-slot>
    <x-slot name="pageTitle">Submission #{{ $submission->id }}</x-slot>
    <x-slot name="pageSubtitle">View submission details and status</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-alt me-2"></i>Submission Details
                    </h5>
                    <a href="{{ route('client.submissions.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label fw-bold text-muted">SUBMISSION ID</label>
                                <p class="mb-0">
                                    <span class="badge bg-secondary fs-6">#{{ $submission->id }}</span>
                                </p>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-muted">COMPANY NAME</label>
                                <p class="mb-0 fs-5 fw-bold text-primary">{{ $submission->company_name }}</p>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-muted">VERIFICATION CODE</label>
                                <p class="mb-0">
                                    <code class="bg-light p-2 rounded fs-5">{{ $submission->otp }}</code>
                                </p>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-muted">STATUS</label>
                                <p class="mb-0">
                                    <span class="badge {{ $submission->getStatusBadgeClass() }} fs-6">
                                        {{ $submission->getStatusLabel() }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-muted">SUBMITTED DATE</label>
                                <p class="mb-0">
                                    <i class="fas fa-calendar me-2"></i>{{ $submission->created_at->format('l, F j, Y') }}
                                </p>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>{{ $submission->created_at->format('g:i A') }}
                                </small>
                            </div>

                            @if($submission->processed_at)
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-muted">PROCESSED DATE</label>
                                    <p class="mb-0">
                                        <i class="fas fa-calendar-check me-2"></i>{{ $submission->processed_at->format('l, F j, Y') }}
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>{{ $submission->processed_at->format('g:i A') }}
                                    </small>
                                </div>
                            @endif

                            @if($submission->notes)
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-muted">PROCESSING NOTES</label>
                                    <div class="bg-light p-3 rounded">
                                        <p class="mb-0">{{ $submission->notes }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Status Timeline -->
                    <div class="mt-4">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-history me-2"></i>Status Timeline
                        </h6>
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Submission Created</h6>
                                    <p class="text-muted mb-0">{{ $submission->created_at->format('M j, Y \a\t g:i A') }}</p>
                                </div>
                            </div>

                            @if($submission->processed_at)
                                <div class="timeline-item">
                                    <div class="timeline-marker {{ $submission->isProcessed() ? 'bg-success' : 'bg-danger' }}"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Status Updated to {{ $submission->getStatusLabel() }}</h6>
                                        <p class="text-muted mb-0">{{ $submission->processed_at->format('M j, Y \a\t g:i A') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .form-label {
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .avatar-sm {
            width: 40px;
            height: 40px;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e5e7eb;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-marker {
            position: absolute;
            left: -22px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #e5e7eb;
        }

        .timeline-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 3px solid #3b82f6;
        }

        code {
            font-size: 1rem;
            padding: 8px 12px;
        }
    </style>
</x-app-layout>
