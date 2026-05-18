<x-app-layout>
    <x-slot name="title">Submit Company & Verification Code</x-slot>
    <x-slot name="pageTitle">Submit Verification Code</x-slot>
    <x-slot name="pageSubtitle">Submit your company name and verification code for processing</x-slot>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-building me-2"></i>Verification Code Submission Form
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('client.submissions.store') }}" id="submissionForm">
                        @csrf


                        <!-- Company Name -->
                        <div class="mb-4">
                            <label for="company_name" class="form-label">
                                <i class="fas fa-building me-2"></i>Company Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="company_name"
                                   id="company_name"
                                   class="form-control @error('company_name') is-invalid @enderror"
                                   value="{{ old('company_name') }}"
                                   placeholder="Enter the company name"
                                   required>
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Enter the full company name as it appears officially.</div>
                        </div>

                        <!-- Verification Code -->
                        <div class="mb-4">
                            <label for="otp" class="form-label">
                                <i class="fas fa-key me-2"></i>Verification Code <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="otp"
                                   id="otp"
                                   class="form-control @error('otp') is-invalid @enderror"
                                   value="{{ old('otp') }}"
                                   placeholder="Enter the verification code"
                                   required>
                            @error('otp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Enter the verification code provided by the company or system.</div>
                        </div>

                        <!-- Submit Button -->
                        <div class="submission-form-actions d-flex justify-content-between align-items-center">
                            <a href="{{ route('client.submissions.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Submissions
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-paper-plane me-2"></i>Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('submissionForm').addEventListener('submit', function(e) {
            const companyName = document.getElementById('company_name').value.trim();
            const otp = document.getElementById('otp').value.trim();

            if (!companyName || !otp) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }

            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';

            console.log('Submitting form with:', { companyName, otp });
        });
    </script>
    @endpush

    <style>
        .form-label {
            font-weight: 600;
            color: #374151;
        }

        .btn {
            border-radius: 8px;
        }

        .alert {
            border-radius: 8px;
        }

        .form-control {
            border-radius: 8px;
        }

        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }

        @media (max-width: 576px) {
            .submission-form-actions {
                flex-direction: column;
                align-items: stretch !important;
                gap: 0.85rem;
            }

            .submission-form-actions .btn {
                width: 100%;
            }
        }
    </style>
</x-app-layout>
