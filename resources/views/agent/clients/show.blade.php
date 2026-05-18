<x-app-layout>
    <x-slot name="title">Client Details</x-slot>
    <x-slot name="pageTitle">Client Details</x-slot>
    <x-slot name="pageSubtitle">{{ $client->name }} - Assignment Information</x-slot>

    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">{{ $client->name }}</h2>
                    <p class="text-muted mb-0">{{ $client->email }}</p>
                </div>
                <div>
                    <a href="{{ route('agent.clients.index') }}" class="btn btn-border-black">
                        <i class="fas fa-arrow-left me-2"></i>Back to Clients
                    </a>
                </div>
            </div>

            <!-- Client Information -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user me-2"></i>Client Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <strong>Name:</strong>
                                    <p class="text-muted">{{ $client->name }}</p>
                                </div>
                                <div class="col-6">
                                    <strong>Email:</strong>
                                    <p class="text-muted">{{ $client->email }}</p>
                                </div>
                            </div>

                            @if($client->clientProfile)
                                <div class="row">
                                    <div class="col-6">
                                        <strong>Service Type:</strong>
                                        <p class="text-muted">
                                            <span class="badge {{ ($client->clientProfile->service_type ?? \App\Models\ClientProfile::SERVICE_TYPE_REGULAR) === \App\Models\ClientProfile::SERVICE_TYPE_VIP ? 'bg-dark text-white' : 'bg-info text-dark' }}">
                                                {{ $client->clientProfile->serviceTypeLabel() }}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-6">
                                        <strong>Service Status:</strong>
                                        <p class="text-muted">
                                            @php
                                                $assignmentStatus = 'Active';
                                                $assignmentColor = 'success';
                                                if ($assignment && !$assignment->is_active) {
                                                    $assignmentStatus = 'Inactive';
                                                    $assignmentColor = 'secondary';
                                                } elseif ($assignment && $assignment->service_end_date && $assignment->service_end_date->isPast()) {
                                                    $assignmentStatus = 'Expired';
                                                    $assignmentColor = 'danger';
                                                }
                                            @endphp
                                            <span class="badge bg-{{ $assignmentColor }}">{{ $assignmentStatus }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <strong>Service Package:</strong>
                                        <p class="text-muted">
                                            @if($client->clientProfile->service_package)
                                                <span class="badge bg-primary">{{ ucwords(str_replace('-', ' ', $client->clientProfile->service_package)) }}</span>
                                            @else
                                                Not set
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-6">
                                        <strong>Service Start:</strong>
                                        <p class="text-muted">{{ $client->clientProfile->service_start_date?->format('M d, Y') ?? $assignment?->assigned_date?->format('M d, Y') ?? 'Not set' }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-link me-2"></i>Assignment Details
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($assignment)
                                <div class="row">
                                    <div class="col-6">
                                        <strong>Assignment Date:</strong>
                                        <p class="text-muted">{{ $assignment->created_at->format('M d, Y') }}</p>
                                    </div>
                                    <div class="col-6">
                                        <strong>Service End Date:</strong>
                                        <p class="text-muted">
                                            @if($assignment->service_end_date)
                                                {{ \Carbon\Carbon::parse($assignment->service_end_date)->format('M d, Y') }}
                                            @else
                                                Not specified
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                @if($assignment->apply_to)
                                    <div class="row">
                                        <div class="col-12">
                                            <strong>Apply To:</strong>
                                            <div class="text-muted">{!! $assignment->apply_to !!}</div>
                                        </div>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-12">
                                        <strong>Note for Agent:</strong>
                                        @if($assignment->note_for_agent)
                                            <div class="text-muted">{!! $assignment->note_for_agent !!}</div>
                                        @else
                                            <p class="text-muted"><em>No specific notes provided for this assignment.</em></p>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <p class="text-muted">No assignment details available</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignment Files -->
            @if($assignment && ($assignment->resume_file || $assignment->onboarding_form_file || $assignment->cover_letters))
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-file-alt me-2"></i>Assignment Files
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @if($assignment->resume_file)
                                        <div class="col-md-4 mb-3">
                                            <div class="card border">
                                                <div class="card-body text-center">
                                                    <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                                                    <h6 class="card-title">Resume</h6>
                                                    <button type="button"
                                                            class="btn btn-black btn-sm js-download-file"
                                                            data-url="{{ storage_public_url($assignment->resume_file) }}"
                                                            data-filename="{{ basename($assignment->resume_file) }}">
                                                        <i class="fas fa-download me-2"></i>Download
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if($assignment->onboarding_form_file)
                                        <div class="col-md-4 mb-3">
                                            <div class="card border">
                                                <div class="card-body text-center">
                                                    <i class="fas fa-file-alt fa-3x text-primary mb-3"></i>
                                                    <h6 class="card-title">Onboarding Form</h6>
                                                    <button type="button"
                                                            class="btn btn-black btn-sm js-download-file"
                                                            data-url="{{ storage_public_url($assignment->onboarding_form_file) }}"
                                                            data-filename="{{ basename($assignment->onboarding_form_file) }}">
                                                        <i class="fas fa-download me-2"></i>Download
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if($assignment->cover_letters)
                                        <div class="col-md-4 mb-3">
                                            <div class="card border">
                                                <div class="card-body text-center">
                                                    <i class="fas fa-file-text fa-3x text-info mb-3"></i>
                                                    <h6 class="card-title">Cover Letters</h6>
                                                    <div class="d-grid gap-2">
                                                        @php
                                                            $coverLetters = is_array($assignment->cover_letters)
                                                                ? $assignment->cover_letters
                                                                : json_decode($assignment->cover_letters, true);
                                                        @endphp
                                                        @foreach($coverLetters as $index => $coverLetter)
                                                            <button type="button"
                                                                    class="btn btn-border-black btn-sm js-download-file"
                                                                    data-url="{{ storage_public_url($coverLetter) }}"
                                                                    data-filename="{{ basename($coverLetter) }}">
                                                                <i class="fas fa-download me-2"></i>Cover Letter {{ $index + 1 }}
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Recent Work Updates -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-tasks me-2"></i>Recent Work Updates
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($client->workUpdates->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Title</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($client->workUpdates as $update)
                                                <tr>
                                                    <td>{{ $update->created_at->format('M d, Y H:i') }}</td>
                                                    <td>{{ $update->title }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $update->status === 'completed' ? 'success' : 'warning' }}">
                                                            {{ ucfirst($update->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('agent.work-updates.index') }}"
                                                           class="btn btn-sm btn-view">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No work updates submitted yet</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Client Submissions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-upload me-2"></i>Client Submissions
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($client->clientSubmissions->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($client->clientSubmissions as $submission)
                                                <tr>
                                                    <td>{{ $submission->created_at->format('M d, Y H:i') }}</td>
                                                    <td>{{ ucfirst($submission->submission_type) }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $submission->status === 'approved' ? 'success' : ($submission->status === 'rejected' ? 'danger' : 'warning') }}">
                                                            {{ ucfirst($submission->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('agent.submissions.show', $submission) }}"
                                                           class="btn btn-sm btn-view">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-upload fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No client submissions yet</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
