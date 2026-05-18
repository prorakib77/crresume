<x-app-layout>
    @php($isOtpSubmission = ($type ?? 'client_submission') === 'otp_submission')

    <x-slot name="title">Submission Details</x-slot>
    <x-slot name="pageTitle">Submission #{{ $submission->id }}</x-slot>
    <x-slot name="pageSubtitle">View and manage client submission</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-alt me-2"></i>Submission Details
                    </h5>
                    <div>
                        <a href="{{ route('agent.submissions.index') }}" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-left me-2"></i>Back to List
                        </a>
                        @if($submission->isPending())
                            <button type="button" class="btn btn-success me-2" onclick="updateStatus('processed')">
                                <i class="fas fa-check me-2"></i>Mark as Processed
                            </button>
                            <button type="button" class="btn btn-danger" onclick="updateStatus('rejected')">
                                <i class="fas fa-times me-2"></i>Reject
                            </button>
                        @endif
                    </div>
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
                                <label class="form-label fw-bold text-muted">CLIENT NAME</label>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 fw-bold fs-5">{{ $submission->client->name }}</p>
                                        <small class="text-muted">{{ $submission->client->email }}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-muted">COMPANY NAME</label>
                                <p class="mb-0 fs-5 fw-bold text-primary">{{ $submission->company_name }}</p>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-muted">OTP</label>
                                <p class="mb-0">
                                    <code class="bg-light p-2 rounded fs-5">{{ $submission->otp_code }}</code>
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label fw-bold text-muted">CURRENT STATUS</label>
                                <p class="mb-0">
                                    <span class="badge {{ $submission->getStatusBadgeClass() }} fs-6">
                                        {{ $submission->getStatusLabel() }}
                                    </span>
                                </p>
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

                            @if($submission->reviewed_at)
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-muted">REVIEWED DATE</label>
                                    <p class="mb-0">
                                        <i class="fas fa-calendar-check me-2"></i>{{ $submission->reviewed_at->format('l, F j, Y') }}
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>{{ $submission->reviewed_at->format('g:i A') }}
                                    </small>
                                </div>
                            @endif

                            @if($submission->notes)
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-muted">NOTES</label>
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
                                    <h6 class="mb-1">Submission Received</h6>
                                    <p class="text-muted mb-0">{{ $submission->created_at->format('M j, Y \a\t g:i A') }}</p>
                                </div>
                            </div>

                            @if($submission->reviewed_at)
                                <div class="timeline-item">
                                    <div class="timeline-marker {{ $submission->isProcessed() ? 'bg-success' : 'bg-danger' }}"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Status Updated to {{ $submission->getStatusLabel() }}</h6>
                                        <p class="text-muted mb-0">{{ $submission->reviewed_at->format('M j, Y \a\t g:i A') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div id="customStatusModal" class="custom-status-modal" aria-hidden="true">
        <div class="custom-status-modal-backdrop" onclick="closeStatusModal()"></div>
        <div class="custom-status-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="statusModalTitle">
            <div class="custom-status-modal-header">
                <div>
                    <p class="custom-status-modal-overline">Submission Review</p>
                    <h5 class="custom-status-modal-title" id="statusModalTitle">Update Submission Status</h5>
                </div>
                <button type="button" class="custom-status-modal-close" aria-label="Close" onclick="closeStatusModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="statusForm" class="custom-status-modal-body" method="POST" action="{{ route('agent.submissions.update-status', $submission) }}">
                @csrf
                @method('PATCH')
                @if($isOtpSubmission)
                    <input type="hidden" name="type" value="otp">
                @endif

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="pending" {{ $submission->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processed" {{ $submission->status === 'processed' ? 'selected' : '' }}>Processed</option>
                        <option value="rejected" {{ $submission->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes (Optional)</label>
                    <textarea
                        name="notes"
                        id="notes"
                        class="form-control"
                        rows="4"
                        placeholder="Add any notes about this submission..."
                    >{{ $submission->notes }}</textarea>
                </div>
                <div class="custom-status-modal-footer">
                    <button type="button" class="btn btn-white" onclick="closeStatusModal()">Cancel</button>
                    <button type="submit" class="btn btn-black">Update Status</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('customStatusModal');

            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
        });

        function updateStatus(status) {
            const statusSelect = document.getElementById('status');
            statusSelect.value = status;

            const modal = document.getElementById('customStatusModal');
            if (!modal) return;

            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');

            window.setTimeout(function () {
                statusSelect.focus();
            }, 30);
        }

        function closeStatusModal() {
            const modal = document.getElementById('customStatusModal');
            if (!modal) return;

            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeStatusModal();
            }
        });
    </script>
    @endpush

    <style>
        .custom-status-modal {
            display: none;
        }

        .custom-status-modal.show {
            display: block;
        }

        .custom-status-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 15, 15, 0.62);
            backdrop-filter: blur(4px);
            z-index: 3000;
        }

        .custom-status-modal-dialog {
            position: fixed;
            top: 50%;
            left: 50%;
            width: min(34rem, calc(100vw - 2rem));
            max-height: calc(100vh - 2rem);
            transform: translate(-50%, -50%);
            overflow-y: auto;
            border: 1px solid rgba(17, 17, 17, 0.08);
            border-radius: 1.25rem;
            background: linear-gradient(180deg, #ffffff 0%, #fbfaf6 100%);
            box-shadow: 0 32px 80px rgba(0, 0, 0, 0.28);
            z-index: 3010;
        }

        .custom-status-modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.2rem 1.25rem 0.85rem;
            border-bottom: 1px solid rgba(17, 17, 17, 0.08);
        }

        .custom-status-modal-overline {
            margin: 0 0 0.3rem;
            color: #8b7350;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .custom-status-modal-title {
            margin: 0;
            color: #111111;
            font-size: 1.05rem;
            font-weight: 700;
        }

        .custom-status-modal-close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border: 0;
            border-radius: 9999px;
            background: transparent;
            color: #57534e;
        }

        .custom-status-modal-body {
            padding: 1.15rem 1.25rem 1.25rem;
        }

        .custom-status-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-top: 1.2rem;
        }

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

        .btn {
            border-radius: 8px;
        }

        body.modal-open {
            overflow: hidden;
        }

        @media (max-width: 576px) {
            .custom-status-modal-dialog {
                width: calc(100vw - 1rem);
                max-height: calc(100vh - 1rem);
            }

            .custom-status-modal-header,
            .custom-status-modal-body {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .custom-status-modal-footer {
                flex-direction: column-reverse;
            }

            .custom-status-modal-footer .btn {
                width: 100%;
            }
        }
    </style>
</x-app-layout>
