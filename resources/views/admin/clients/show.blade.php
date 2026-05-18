@extends('layouts.app')

@section('title', 'Client Details')

@section('content')
@php
    $profile = $client->clientProfile;
    $onboardingStatus = $profile?->resolvedOnboardingStatus() ?? \App\Models\ClientProfile::ONBOARDING_STATUS_PENDING;
    $assignedAgent = $assignment?->agent;
@endphp
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Client Details</h1>
                    <p class="text-muted mb-0">View client information and assignment details</p>
                </div>
                <div>
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-border-black">
                        <i class="fas fa-arrow-left me-2"></i>Back to Clients
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-danger mb-0">
                    <div class="fw-semibold mb-2">Please fix the following issues:</div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Client Profile Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-black text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>Client Profile
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Name:</strong>
                                <p class="text-muted">{{ $client->name }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Email:</strong>
                                <p class="text-muted">{{ $client->email }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Phone:</strong>
                                <p class="text-muted">{{ $profile?->phone ?? 'Not provided' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Service Type:</strong>
                                <p class="text-muted">
                                    <span class="badge {{ ($profile?->service_type ?? \App\Models\ClientProfile::SERVICE_TYPE_REGULAR) === \App\Models\ClientProfile::SERVICE_TYPE_VIP ? 'bg-dark text-white' : 'bg-info text-dark' }}">
                                        {{ $profile?->serviceTypeLabel() ?? 'Regular' }}
                                    </span>
                                </p>
                            </div>
                            <div class="mb-3">
                                <strong>Service Package:</strong>
                                <p class="text-muted">
                                    @if($profile?->service_package)
                                        <span class="badge bg-primary">{{ ucwords(str_replace('-', ' ', $profile->service_package)) }}</span>
                                    @else
                                        Not set
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Registration Date:</strong>
                                <p class="text-muted">{{ $client->created_at->format('M d, Y') }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Last Login:</strong>
                                <p class="text-muted">{{ $client->last_login_at ? $client->last_login_at->format('M d, Y H:i') : 'Never' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Status:</strong>
                                <span class="badge bg-{{ $client->status === \App\Models\User::STATUS_ACTIVE ? 'success' : ($client->status === \App\Models\User::STATUS_SUSPENDED ? 'danger' : 'secondary') }}">
                                    {{ ucfirst($client->status ?? 'inactive') }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <strong>Estimated Timeline:</strong>
                                <div class="text-muted small mt-2">
                                    <div>Resume Completion: {{ $profile?->estimated_resume_completion_date?->format('M d, Y') ?? 'Not set' }}</div>
                                    <div>Cover Letter Completion: {{ $profile?->estimated_cover_letter_completion_date?->format('M d, Y') ?? 'Not set' }}</div>
                                    <div>Job Apply Start: {{ $profile?->estimated_application_start_date?->format('M d, Y') ?? 'Not set' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-7 mb-4 mb-lg-0">
            <div class="card h-100">
                <div class="card-header bg-black text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-sliders me-2"></i>Client Settings
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.clients.update-details', $client) }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Onboarding Status</label>
                            <select name="onboarding_status" class="form-select">
                                <option value="{{ \App\Models\ClientProfile::ONBOARDING_STATUS_PENDING }}" @selected(old('onboarding_status', $onboardingStatus) === \App\Models\ClientProfile::ONBOARDING_STATUS_PENDING)>Pending</option>
                                <option value="{{ \App\Models\ClientProfile::ONBOARDING_STATUS_COMPLETED }}" @selected(old('onboarding_status', $onboardingStatus) === \App\Models\ClientProfile::ONBOARDING_STATUS_COMPLETED)>Completed</option>
                                <option value="{{ \App\Models\ClientProfile::ONBOARDING_STATUS_REQUESTED_AGAIN }}" @selected(old('onboarding_status', $onboardingStatus) === \App\Models\ClientProfile::ONBOARDING_STATUS_REQUESTED_AGAIN)>Requested Again</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Service Type</label>
                            <select name="service_type" class="form-select">
                                <option value="{{ \App\Models\ClientProfile::SERVICE_TYPE_REGULAR }}" @selected(old('service_type', $profile?->service_type ?? \App\Models\ClientProfile::SERVICE_TYPE_REGULAR) === \App\Models\ClientProfile::SERVICE_TYPE_REGULAR)>Regular</option>
                                <option value="{{ \App\Models\ClientProfile::SERVICE_TYPE_VIP }}" @selected(old('service_type', $profile?->service_type) === \App\Models\ClientProfile::SERVICE_TYPE_VIP)>VIP</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Service Package</label>
                            <select name="service_package" class="form-select">
                                <option value="">Reset / Not set</option>
                                @foreach(['2-weeks', '3-weeks', '4-weeks', '5-weeks', '6-weeks'] as $packageOption)
                                    <option value="{{ $packageOption }}" @selected(old('service_package', $profile?->service_package) === $packageOption)>{{ ucwords(str_replace('-', ' ', $packageOption)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Service Start Date</label>
                            <input type="date" name="service_start_date" value="{{ old('service_start_date', optional($profile?->service_start_date)->format('Y-m-d')) }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Resume Completion Date</label>
                            <input type="date" name="estimated_resume_completion_date" value="{{ old('estimated_resume_completion_date', optional($profile?->estimated_resume_completion_date)->format('Y-m-d')) }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Cover Letter Completion</label>
                            <input type="date" name="estimated_cover_letter_completion_date" value="{{ old('estimated_cover_letter_completion_date', optional($profile?->estimated_cover_letter_completion_date)->format('Y-m-d')) }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Application Start Date</label>
                            <input type="date" name="estimated_application_start_date" value="{{ old('estimated_application_start_date', optional($profile?->estimated_application_start_date)->format('Y-m-d')) }}" class="form-control">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-black">
                                <i class="fas fa-save me-2"></i>Save Client Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header bg-black text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-envelope me-2"></i>Send Custom Email
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.clients.send-email', $client) }}" class="d-grid gap-3">
                        @csrf
                        <div>
                            <label class="form-label fw-semibold">Subject</label>
                            <input type="text" name="subject" value="{{ old('subject') }}" class="form-control" maxlength="190" required>
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Message</label>
                            <textarea name="body" rows="8" class="form-control" maxlength="12000" required>{{ old('body') }}</textarea>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-black">
                                <i class="fas fa-paper-plane me-2"></i>Send Email
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Onboarding -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center bg-black text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-alt me-2"></i>Onboarding Package
                    </h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-{{ $onboardingStatus === \App\Models\ClientProfile::ONBOARDING_STATUS_COMPLETED ? 'success' : ($onboardingStatus === \App\Models\ClientProfile::ONBOARDING_STATUS_REQUESTED_AGAIN ? 'info text-dark' : 'warning text-dark') }}">
                            {{ $profile?->onboardingStatusLabel() ?? 'Pending' }}
                        </span>
                        <form method="POST" action="{{ route('admin.clients.request-onboarding', $client) }}">
                            @csrf
                            <button type="submit" class="btn btn-white btn-sm">
                                <i class="fas fa-undo me-1"></i>Request Again
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    @if($profile && ($profile->onboarding_resume_file || $profile->onboarding_form_file || $profile->onboarding_text))
                        <div class="row g-3">
                            @if($profile->onboarding_resume_file)
                                <div class="col-md-4">
                                    <div class="p-3 border rounded">
                                        <div class="fw-semibold mb-2">Old Resume</div>
                                        <a href="{{ route('admin.clients.onboarding-file', [$client, 'resume']) }}" class="btn btn-black btn-sm" target="_blank">
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                    </div>
                                </div>
                            @endif
                            @if($profile->onboarding_form_file)
                                <div class="col-md-4">
                                    <div class="p-3 border rounded">
                                        <div class="fw-semibold mb-2">Onboarding File</div>
                                        <a href="{{ route('admin.clients.onboarding-file', [$client, 'form']) }}" class="btn btn-black btn-sm" target="_blank">
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                    </div>
                                </div>
                            @endif
                            @if($profile->onboarding_text)
                                <div class="col-md-4">
                                    <div class="p-3 border rounded h-100">
                                        <div class="fw-semibold mb-2">Onboarding Text</div>
                                        <p class="text-muted small mb-2">{{ \Illuminate\Support\Str::limit(strip_tags($profile->onboarding_text), 120) }}</p>
                                        <a href="{{ route('admin.clients.onboarding-text', $client) }}" class="btn btn-border-black btn-sm">
                                            <i class="fas fa-file-pdf me-1"></i>Download PDF
                                        </a>
                                    </div>
                                </div>
                            @endif
                            @if($profile->onboarding_note)
                                <div class="col-12">
                                    <div class="p-3 border rounded">
                                        <div class="fw-semibold mb-2">Client Note</div>
                                        <div class="text-muted">{!! nl2br(e($profile->onboarding_note)) !!}</div>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-6">
                                <div class="p-3 border rounded h-100">
                                    <div class="fw-semibold mb-2">Policy Acknowledgement</div>
                                    <div class="text-muted">{{ $profile->policy_acknowledged_at?->format('M d, Y h:i A') ?? 'Not recorded' }}</div>
                                </div>
                            </div>
                            @if($profile->client_signature_path)
                                <div class="col-md-6">
                                    <div class="p-3 border rounded h-100">
                                        <div class="fw-semibold mb-2">Client Signature</div>
                                        <a href="{{ storage_public_url($profile->client_signature_path) }}" target="_blank" rel="noopener">
                                            <img src="{{ storage_public_url($profile->client_signature_path) }}" alt="Client signature" class="img-fluid rounded border" style="max-height: 180px;">
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-muted mb-0">No onboarding package submitted yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Information -->
    @if($assignment)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-black text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tasks me-2"></i>Assignment Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>Assigned Agent:</strong>
                                    <p class="text-muted">
                                        @if($assignedAgent)
                                            <a href="{{ route('admin.agents.show', $assignedAgent) }}" class="text-decoration-none">
                                                {{ $assignedAgent->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">Not assigned yet</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <strong>Assignment Date:</strong>
                                    <p class="text-muted">{{ $assignment->assigned_date ? $assignment->assigned_date->format('M d, Y') : 'Not set' }}</p>
                                </div>
                                <div class="mb-3">
                                    <strong>Service End Date:</strong>
                                    <p class="text-muted">
                                        @if($assignment->service_end_date)
                                            {{ $assignment->service_end_date->format('M d, Y') }}
                                            @php
                                                $daysRemaining = rounded_time_value(now()->diffInDays($assignment->service_end_date, false));
                                            @endphp
                                            @if($daysRemaining > 0)
                                                <span class="badge bg-{{ $daysRemaining <= 3 ? 'danger' : ($daysRemaining <= 7 ? 'warning' : 'success') }}">
                                                    {{ $daysRemaining }} days remaining
                                                </span>
                                            @else
                                                <span class="badge bg-danger">Expired</span>
                                            @endif
                                        @else
                                            Not set
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>Assignment Status:</strong>
                                    <span class="badge bg-{{ $assignment->is_active ? 'success' : 'danger' }}">
                                        {{ $assignment->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                @if($assignment->notes)
                                    <div class="mb-3">
                                        <strong>Notes:</strong>
                                        <p class="text-muted">{{ $assignment->notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignment Files -->
        @if($assignment->resume_file || $assignment->onboarding_form_file || $assignment->cover_letters)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-black text-white">
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
                                    @php
                                        $coverLetters = is_array($assignment->cover_letters)
                                            ? $assignment->cover_letters
                                            : json_decode($assignment->cover_letters, true);
                                    @endphp
                                    @if($coverLetters && count($coverLetters) > 0)
                                        @foreach($coverLetters as $index => $coverLetter)
                                            <div class="col-md-4 mb-3">
                                                <div class="card border">
                                                    <div class="card-body text-center">
                                                        <i class="fas fa-file-text fa-3x text-success mb-3"></i>
                                                        <h6 class="card-title">Cover Letter {{ $index + 1 }}</h6>
                                                        <button type="button"
                                                                class="btn btn-black btn-sm js-download-file"
                                                                data-url="{{ storage_public_url($coverLetter) }}"
                                                                data-filename="{{ basename($coverLetter) }}">
                                                            <i class="fas fa-download me-2"></i>Download
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                @endif
                            </div>

                            @if($assignment->apply_to)
                                <div class="mt-4">
                                    <h6><strong>Apply To:</strong></h6>
                                    <div class="card border">
                                        <div class="card-body">
                                            <div class="text-muted">{!! $assignment->apply_to !!}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($assignment->note_for_agent)
                                <div class="mt-4">
                                    <h6><strong>Note for Agent:</strong></h6>
                                    <div class="card border">
                                        <div class="card-body">
                                            <div class="text-muted">{!! $assignment->note_for_agent !!}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h5>No Assignment Found</h5>
                        <p class="text-muted">This client has not been assigned to any agent yet.</p>
                        <a href="{{ route('admin.assignments.create') }}" class="btn btn-black">
                            <i class="fas fa-plus me-2"></i>Create Assignment
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Recent Submissions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-black text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-upload me-2"></i>Recent Submissions
                    </h5>
                </div>
                <div class="card-body">
                    @if($submissions && $submissions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Submission Date</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($submissions as $submission)
                                        <tr>
                                            <td>{{ $submission->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ ucfirst($submission->type) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $submission->status === 'approved' ? 'success' : ($submission->status === 'rejected' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst($submission->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.work-updates') }}?submission={{ $submission->getRouteKey() }}"
                                                   class="btn btn-sm btn-border-black">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $submissions->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-upload fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No submissions found for this client</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
