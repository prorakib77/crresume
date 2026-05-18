<x-app-layout>
    <x-slot name="title">OTP Submissions</x-slot>
    <x-slot name="pageTitle">OTP Submissions</x-slot>
    <x-slot name="pageSubtitle">Manage OTP submissions from your assigned clients</x-slot>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['total'] }}</h4>
                            <p class="mb-0">Total Submissions</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-list fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['pending'] }}</h4>
                            <p class="mb-0">Pending</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['approved'] }}</h4>
                            <p class="mb-0">Approved</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['rejected'] }}</h4>
                            <p class="mb-0">Rejected</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-inbox me-2"></i>OTP Submissions
                    </h5>
                </div>
                <div class="card-body">
                    @if($clientSubmissions->count() > 0 || $otpSubmissions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Client</th>
                                        <th>Title</th>
                                        <th>Details</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($clientSubmissions as $submission)
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">#{{ $submission->id }}</span>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $submission->client->name ?? 'Unknown Client' }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $submission->client->email ?? 'No email' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <strong>{{ $submission->title ?? 'Client Submission' }}</strong>
                                            </td>
                                            <td>
                                                <div class="text-muted small">
                                                    {{ $submission->description ?? 'No description' }}
                                                </div>
                                            </td>
                                    <td>
                                        <span class="badge {{ $submission->status === 'pending' ? 'bg-warning' : ($submission->status === 'processed' ? 'bg-success' : ($submission->status === 'rejected' ? 'bg-danger' : 'bg-primary')) }}">
                                            {{ ucfirst($submission->status ?? 'pending') }}
                                        </span>
                                    </td>
                                            <td>
                                                <div>
                                                    {{ $submission->created_at->format('M j, Y') }}
                                                    <br>
                                                    <small class="text-muted">{{ $submission->created_at->format('g:i A') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('agent.submissions.show', $submission) }}"
                                                       class="btn btn-sm btn_hover_dg btn-view">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    @if($submission->status === 'pending')
                                                        <button type="button"
                                                                class="btn btn-sm btn-success"
                                                                onclick="updateStatus('{{ route('agent.submissions.update-status', $submission) }}', {{ $submission->id }}, 'processed')">
                                                            <i class="fas fa-check"></i> Process
                                                        </button>
                                                        <button type="button"
                                                                class="btn btn-sm btn-danger"
                                                                onclick="updateStatus('{{ route('agent.submissions.update-status', $submission) }}', {{ $submission->id }}, 'rejected')">
                                                            <i class="fas fa-times"></i> Reject
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @foreach($otpSubmissions as $submission)
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">#{{ $submission->id }}</span>
                                                <span class="badge bg-info ms-1">OTP</span>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $submission->client->name ?? 'Unknown Client' }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $submission->client->email ?? 'No email' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <strong>OTP Submission - {{ $submission->company_name }}</strong>
                                            </td>
                                            <td>
                                                <div class="text-muted small">
                                                    Company: {{ $submission->company_name }} | OTP: {{ $submission->otp_code }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge {{ $submission->status === 'pending' ? 'bg-warning' : ($submission->status === 'processed' || $submission->status === 'approved' ? 'bg-success' : ($submission->status === 'rejected' ? 'bg-danger' : 'bg-primary')) }}">
                                                    {{ ucfirst($submission->status ?? 'pending') }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    {{ $submission->created_at->format('M j, Y') }}
                                                    <br>
                                                    <small class="text-muted">{{ $submission->created_at->format('g:i A') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('agent.submissions.show', $submission) }}?otp=1"
                                                       class="btn btn-sm btn_hover_dg btn-view">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    @if($submission->status === 'pending')
                                                        <button type="button"
                                                                class="btn btn-sm btn-success"
                                                                onclick="updateStatus('{{ route('agent.submissions.update-status', $submission) }}', {{ $submission->id }}, 'approved', true)">
                                                            <i class="fas fa-check"></i> Accept
                                                        </button>
                                                        <button type="button"
                                                                class="btn btn-sm btn-danger"
                                                                onclick="updateStatus('{{ route('agent.submissions.update-status', $submission) }}', {{ $submission->id }}, 'rejected', true)">
                                                            <i class="fas fa-times"></i> Reject
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Submissions Yet</h5>
                            <p class="text-muted">Your clients haven't submitted any company information yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Status Modal -->
    <div id="customStatusModal" class="custom-modal" aria-hidden="true">
        <div class="custom-modal-backdrop" onclick="closeStatusModal()"></div>
        <div class="custom-modal-dialog">
            <div class="custom-modal-header">
                <h5 class="mb-0">Update Submission Status</h5>
                <button type="button" class="btn-close" aria-label="Close" onclick="closeStatusModal()"></button>
            </div>
            <form id="statusForm" class="custom-modal-body" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="submission_id" id="submission_id">
                <input type="hidden" name="otp" id="otp_flag" value="">
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="pending">Pending</option>
                        <option value="processed">Processed</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Add any notes about this submission..."></textarea>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-white" onclick="closeStatusModal()">Cancel</button>
                    <button type="submit" class="btn btn-black" id="statusSubmitBtn">
                        <i class="fas fa-save me-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function updateStatus(actionUrl, submissionId, status, isOtp = false) {
            document.getElementById('submission_id').value = submissionId;
            document.getElementById('otp_flag').value = isOtp ? '1' : '';
            document.getElementById('status').value = status;
            document.getElementById('notes').value = '';
            document.getElementById('statusForm').action = actionUrl;
            document.getElementById('customStatusModal').classList.add('show');
            document.body.classList.add('modal-open');
        }

        function closeStatusModal() {
            document.getElementById('customStatusModal').classList.remove('show');
            document.body.classList.remove('modal-open');
        }

        function showAlert(type, message) {
            // Remove existing alerts
            const existingAlerts = document.querySelectorAll('.alert');
            existingAlerts.forEach(alert => alert.remove());

            // Create new alert
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            // Insert at top of page
            const container = document.querySelector('.container-fluid');
            container.insertBefore(alertDiv, container.firstChild);

            // Keep alert visible; remove manually
        }
    </script>
    @endpush

    <style>
        /* Custom modal */
        .custom-modal { display: none; }
        .custom-modal.show { display: block; }
        .custom-modal-backdrop {
            position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 1040;
        }
        .custom-modal-dialog {
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
            z-index: 1050;
            width: min(520px, 92vw);
            padding: 1rem 1.25rem;
        }
        .custom-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 0.75rem;
            margin-bottom: 1rem;
        }
        .custom-modal-body {
            padding: 0;
        }

        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
            color: #374151;
        }

        .badge {
            font-size: 0.75rem;
        }

        .btn {
            border-radius: 6px;
        }

        code {
            font-size: 0.875rem;
        }

        .card {
            border: 1px solid #e5e7eb;
            /* border-radius: 10px; */
            /* box-shadow: 0 2px 4px rgba(0,0,0,0.1); */
        }
        @media (max-width: 576px) {
    .card {
        border-radius: 0px !important;
    }
}
.btn_hover_dg:hover{
    color: #fff !important;
    background: #000 !important;
}
    </style>
</x-app-layout>
