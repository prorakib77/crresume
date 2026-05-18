<x-app-layout>
    <x-slot name="title">My Submissions</x-slot>
    <x-slot name="pageTitle">Verification Code Submissions</x-slot>
    <x-slot name="pageSubtitle">View and manage your submissions</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>Submission History
                    </h5>
                    <a href="{{ route('client.submissions.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>New Submission
                    </a>
                </div>
                <div class="card-body">
                    @if($submissions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Company Name</th>
                                        <th>Verification Code</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($submissions as $submission)
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">#{{ $submission->id }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $submission->company_name }}</strong>
                                            </td>
                                            <td>
                                                <code class="bg-light p-1 rounded">{{ $submission->otp }}</code>
                                            </td>
                                            <td>
                                                <span class="badge {{ $submission->getStatusBadgeClass() }}">
                                                    {{ $submission->getStatusLabel() }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    {{ $submission->created_at->format('M j, Y') }}
                                                    <br>
                                                    <small class="text-muted">{{ $submission->created_at->format('g:i A') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('client.submissions.show', $submission) }}"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Beautiful Pagination -->
                        <x-beautiful-pagination :paginator="$submissions" />
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Submissions Yet</h5>
                            <p class="text-muted">You haven't submitted any company information yet.</p>
                            <a href="{{ route('client.submissions.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Make Your First Submission
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
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
    </style>
</x-app-layout>
