{{-- @extends('layouts.dashboard_master')

@section('content')
<div class="container">
    <h2>Submit Multiple Work Updates</h2>

    @if($errors->any())
        <div class="alert alert-danger">{{ implode(', ', $errors->all()) }}</div>
    @endif

    <form action="{{ route('workUpdates.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label>Client</label>
            <select name="client_id" class="form-control" required>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->email }})</option>
                @endforeach
            </select>
        </div>

        <div id="work-update-container">
            <div class="work-update-group border p-3 mb-3 rounded">
                <div class="mb-3"><label>Job Title</label><input type="text" name="job_title[]" class="form-control"></div>
                <div class="mb-3"><label>Company</label><input type="text" name="company[]" class="form-control"></div>
                <div class="mb-3"><label>Applied Date</label><input type="date" name="applied_date[]" class="form-control"></div>
                <div class="mb-3"><label>Job Link</label><input type="url" name="job_link[]" class="form-control"></div>
                <div class="mb-3">
                    <label>Applied Method</label>
                    <select name="applied_method[]" class="form-control">
                        <option>Web</option>
                        <option>LinkedIn</option>
                        <option>Referral</option>
                    </select>
                </div>
                <div class="mb-3"><label>Note</label><textarea name="note[]" class="form-control"></textarea></div>
                <div class="mb-3"><label>Remarks</label><textarea name="remarks[]" class="form-control"></textarea></div>
                <div class="mb-3"><label>Applied Proof</label><input type="file" name="applied_proof[]" class="form-control"></div>
                <button type="button" class="btn btn-danger remove-update">Remove</button>
            </div>
        </div>

        <button type="button" id="add-update" class="btn btn-secondary">+ Add More</button>
        <br><br>
        <button class="btn btn-black">Submit All</button>
    </form>
</div>

<script>
    document.getElementById('add-update').addEventListener('click', function () {
        let container = document.getElementById('work-update-container');
        let clone = container.querySelector('.work-update-group').cloneNode(true);

        // clear cloned inputs
        clone.querySelectorAll('input, textarea').forEach(el => el.value = '');
        container.appendChild(clone);

        // add remove event
        clone.querySelector('.remove-update').addEventListener('click', function(){
            clone.remove();
        });
    });

    // allow remove on first block clones
    document.querySelectorAll('.remove-update').forEach(btn => {
        btn.addEventListener('click', function(){
            btn.closest('.work-update-group').remove();
        });
    });
</script>
@endsection --}}

@extends('layouts.bootstrap_master')

@section('title', 'Submit Daily Work Update')
@section('page-title', 'Submit Daily Work Update')
@section('page-subtitle', 'Submit today\'s job applications for your assigned client')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <!-- Daily Work Update Form -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-day me-2"></i>Daily Work Update
                </h5>
                <p class="mb-0 mt-2 opacity-75">Submit multiple job applications for your client</p>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</h6>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('agent.work-updates.store') }}" method="POST" enctype="multipart/form-data" id="workUpdateForm">
                    @csrf
                    <!-- Debugging info -->
                    <div class="alert alert-info mb-3">
                        <strong>Debug Info:</strong><br>
                        Form Action: {{ route('agent.work-updates.store') }}<br>
                        Full URL: {{ url('/agent/work-updates') }}<br>
                        Current Route: {{ Route::currentRouteName() ?? 'N/A' }}
                    </div>

                    <!-- Client Selection -->
                    <div class="mb-4">
                        <label for="client_id" class="form-label fw-bold">
                            <i class="fas fa-user me-2"></i>Select Client <span class="text-danger">*</span>
                        </label>
                        @if($selectedClientId)
                            @php
                                $selectedClient = $assignedClients->find($selectedClientId);
                            @endphp
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Creating updates for: <strong>{{ $selectedClient->name }}</strong> ({{ $selectedClient->email }})
                            </div>
                            <input type="hidden" name="client_id" value="{{ $selectedClientId }}">
                        @else
                            <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
                                <option value="">Choose a client...</option>
                                @foreach($assignedClients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }} ({{ $client->email }})
                                        @if($client->pivot->service_end_date)
                                            - Service ends: {{ \Carbon\Carbon::parse($client->pivot->service_end_date)->format('M j, Y') }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif

                        @if($assignedClients->count() === 0)
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                No clients assigned to you. Please contact your manager to assign clients.
                            </div>
                        @endif
                    </div>

                    <!-- Applied Date (Common for all jobs) -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Applied Date <span class="text-danger">*</span></label>
                        <input type="date" name="applied_date" class="form-control @error('applied_date') is-invalid @enderror"
                               value="{{ old('applied_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
                        @error('applied_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">This date will apply to all job applications submitted below.</div>
                    </div>

                    <!-- Job Applications Container -->
                    <div id="jobApplicationsContainer">
                        <h5 class="mb-3"><i class="fas fa-briefcase me-2"></i>Job Applications</h5>

                        <!-- Job Entry Template (Hidden) -->
                        <div class="job-entry-template d-none">
                            <div class="border rounded p-4 mb-4 bg-light job-entry">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Job Application <span class="job-number">1</span></h6>
                                    <button type="button" class="btn btn-sm btn-danger remove-job">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Job Title <span class="text-danger">*</span></label>
                                            <input type="text" name="jobs[0][job_title]" class="form-control" placeholder="e.g., Software Developer" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Company <span class="text-danger">*</span></label>
                                            <input type="text" name="jobs[0][company]" class="form-control" placeholder="e.g., Tech Corp Inc." required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Job Link</label>
                                            <input type="url" name="jobs[0][job_link]" class="form-control" placeholder="https://company.com/careers/job-posting">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Job Success Link</label>
                                            <input type="url" name="jobs[0][job_success_link]" class="form-control" placeholder="https://link-to-successful-application">
                                            <div class="form-text">Link to proof of successful application submission</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Application Method <span class="text-danger">*</span></label>
                                            <select name="jobs[0][applied_method]" class="form-select" required>
                                                <option value="">Select method...</option>
                                                <option value="web">Company Website</option>
                                                <option value="linkedin">LinkedIn</option>
                                                <option value="referral">Referral</option>
                                                <option value="direct">Direct Application</option>
                                                <option value="email">Email</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Application Status <span class="text-danger">*</span></label>
                                            <select name="jobs[0][application_status]" class="form-select" required>
                                                <option value="">Select status...</option>
                                                <option value="applied">Applied</option>
                                                <option value="incomplete">Incomplete Application</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea name="jobs[0][note]" class="form-control" rows="2" placeholder="Additional details about the application..."></textarea>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Add Job Button -->
                    <div class="mb-4">
                        <button type="button" id="addJobButton" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-2"></i>Add Another Job Application
                        </button>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('agent.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                        <button type="submit" class="btn btn-black btn-lg" id="submitButton" style="display: inline-block !important;">
                            <i class="fas fa-paper-plane me-2"></i>Submit All Updates
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Daily Submission Info -->
        <div class="card mt-4">
            <div class="card-body bg-light">
                <div class="row align-items-center">
                    <div class="col-md-1 text-center">
                        <i class="fas fa-info-circle fa-2x text-info"></i>
                    </div>
                    <div class="col-md-11">
                        <h6 class="mb-1">Daily Submission Rules</h6>
                        <ul class="mb-0 small text-muted">
                            <li>You can submit <strong>multiple job applications per client per day</strong></li>
                            <li>All applications will use the same applied date</li>
                            <li>All fields marked with <span class="text-danger">*</span> are required</li>
                            <li>Service continues until the client's service end date</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let jobCount = 1;
    const container = document.getElementById('jobApplicationsContainer');
    const template = document.querySelector('.job-entry-template');
    const addButton = document.getElementById('addJobButton');

    // Auto-focus on first input field
    const firstInput = document.querySelector('input[name="jobs[0][job_title]"]');
    if (firstInput) {
        firstInput.focus();
    }

    // Set max date to today for applied_date field
    const dateInput = document.querySelector('input[name="applied_date"]');
    if (dateInput) {
        dateInput.setAttribute('max', new Date().toISOString().split('T')[0]);
    }

    // Add new job entry
    addButton.addEventListener('click', function() {
        const newEntry = template.cloneNode(true);
        newEntry.classList.remove('d-none');
        newEntry.classList.remove('job-entry-template');

        container.appendChild(newEntry);

        // Renumber all jobs to ensure proper indexing
        renumberJobs();
        updateRemoveButtons();
        updateSubmitButton();

        // Debug field names after adding a job
        setTimeout(() => {
            console.log('After adding job:');
            debugFieldNames();
        }, 100);

        // Focus on the first input of the new entry
        const firstInput = newEntry.querySelector('input');
        if (firstInput) {
            firstInput.focus();
        }
    });

    // Remove job entry
    function updateRemoveButtons() {
        const removeButtons = document.querySelectorAll('.remove-job');
        removeButtons.forEach(button => {
            button.addEventListener('click', function() {
                if (document.querySelectorAll('.job-entry').length > 1) {
                    this.closest('.job-entry').remove();
                    renumberJobs();
                    updateSubmitButton();
                } else {
                    alert('You must have at least one job application.');
                }
            });
        });
    }

    // Renumber jobs after removal or addition
    function renumberJobs() {
        const entries = document.querySelectorAll('.job-entry');
        console.log('Renumbering jobs. Total entries found:', entries.length);

        entries.forEach((entry, index) => {
            // Update job number display
            const jobNumberElement = entry.querySelector('.job-number');
            if (jobNumberElement) {
                jobNumberElement.textContent = index + 1;
            }

            // Update all input names with proper array indexing
            const inputs = entry.querySelectorAll('[name]');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                if (name && name.includes('[') && name.includes(']')) {
                    // Replace any existing array index with the correct one
                    const newName = name.replace(/\[\d+\]/, `[${index}]`);
                    input.setAttribute('name', newName);
                    console.log(`Updated input name: ${name} -> ${newName}`);
                }
            });
        });

        jobCount = entries.length;
        console.log('Job renumbering complete. New jobCount:', jobCount);

        // Debug field names after renumbering
        setTimeout(() => {
            console.log('After renumbering:');
            debugFieldNames();
        }, 50);
    }

    // Function to update submit button visibility
    function updateSubmitButton() {
        const jobEntries = document.querySelectorAll('.job-entry');
        const submitButton = document.getElementById('submitButton');
        const jobCount = jobEntries.length;

        console.log('Debug Info: Updates Count:', jobCount, '| Submit Button Found:', !!submitButton, '| Submit Button Enabled:', submitButton ? !submitButton.disabled : 'N/A', '| Form Action:', document.querySelector('#workUpdateForm').action);

        if (submitButton) {
            // Always show the submit button if there's at least 1 job entry
            if (jobCount >= 1) {
                submitButton.style.display = 'inline-block !important';
                submitButton.disabled = false;
                submitButton.classList.remove('d-none');
                submitButton.style.visibility = 'visible';
                submitButton.style.opacity = '1';
                console.log('Submit button should now be visible');
            } else {
                submitButton.style.display = 'none';
                submitButton.disabled = true;
                submitButton.classList.add('d-none');
                console.log('Submit button hidden - no job entries');
            }
        } else {
            console.error('Submit button not found!');
        }
    }

    // Add form submission debugging
    const form = document.getElementById('workUpdateForm');
    if (form) {
        form.addEventListener('submit', function(event) {
            const jobEntries = document.querySelectorAll('.job-entry');
            console.log('=== FORM SUBMISSION DEBUG ===');
            console.log('Form Action:', form.action);
            console.log('Form Method:', form.method);
            console.log('Number of job entries:', jobEntries.length);

            // Debug: Check all input names before submission
            console.log('=== INPUT NAMES DEBUG ===');
            const allInputs = form.querySelectorAll('input, select, textarea');
            allInputs.forEach((input, index) => {
                if (input.name && (input.name.includes('jobs[') || input.name.includes('work_updates['))) {
                    console.log(`Input ${index}: name="${input.name}", value="${input.value}"`);
                }
            });
            console.log('=== END INPUT NAMES DEBUG ===');

            // Log all form data with focus on job arrays
            const formData = new FormData(form);
            const jobData = {};
            for (let [key, value] of formData.entries()) {
                if (key.includes('jobs[') || key.includes('work_updates[')) {
                    jobData[key] = value;
                }
                console.log(key + ':', value);
            }
            console.log('Job-related form data:', jobData);
            console.log('=== END DEBUG ===');
        });
    }

    // Debug function to check field names
    function debugFieldNames() {
        const allInputs = document.querySelectorAll('#workUpdateForm input, #workUpdateForm select, #workUpdateForm textarea');
        console.log('=== FIELD NAMES DEBUG ===');
        allInputs.forEach((input, index) => {
            if (input.name && (input.name.includes('jobs[') || input.name.includes('work_updates[') || input.name.includes('client_id') || input.name.includes('applied_date'))) {
                console.log(`Field ${index}: name="${input.name}", value="${input.value}", type="${input.type}"`);
            }
        });
        console.log('=== END FIELD NAMES DEBUG ===');
    }

    // Initial setup
    renumberJobs(); // Ensure proper indexing from the start
    updateRemoveButtons();
    updateSubmitButton();

    // Debug field names on page load
    setTimeout(() => {
        debugFieldNames();
    }, 500);

    // Force show submit button on page load
    setTimeout(function() {
        const submitButton = document.getElementById('submitButton');
        if (submitButton) {
            submitButton.style.display = 'inline-block !important';
            submitButton.style.visibility = 'visible';
            submitButton.style.opacity = '1';
            submitButton.disabled = false;
            console.log('Force showing submit button on page load');
        }
    }, 100);

    // Update submit button whenever jobs are added or removed
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                updateSubmitButton();
            }
        });
    });

    // Observe changes to the job container
    observer.observe(container, { childList: true, subtree: true });
});
</script>
@endpush
