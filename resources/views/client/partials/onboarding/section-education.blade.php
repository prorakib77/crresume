<section class="onboarding-section-card">
    <div class="onboarding-section-head">
        <span class="onboarding-step">Step 4</span>
        <div>
            <h6 class="mb-1 fw-bold">Education History</h6>
            <p class="mb-0 text-muted small">Add your relevant educational qualifications for resume building and job search.</p>
        </div>
    </div>
    <div class="onboarding-section-body">
        <div id="education-wrapper">
            @for($i = 0; $i < $educationCount; $i++)
                <div class="education-block onboarding-education-block mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-semibold education-block-title">Education #{{ $i + 1 }}</span>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-edu">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Institution Name *</label>
                            <input type="text" name="education[institution][]" class="form-control @error('education.institution.'.$i) is-invalid @enderror" value="{{ old('education.institution.'.$i) }}" required>
                            @error('education.institution.'.$i)<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Degree *</label>
                            <input type="text" name="education[degree][]" class="form-control @error('education.degree.'.$i) is-invalid @enderror" value="{{ old('education.degree.'.$i) }}" required>
                            @error('education.degree.'.$i)<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Enrollment Date</label>
                            <input type="text" name="education[enrollment][]" class="form-control" value="{{ old('education.enrollment.'.$i) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Graduation Date</label>
                            <input type="text" name="education[graduation][]" class="form-control" value="{{ old('education.graduation.'.$i) }}">
                        </div>
                    </div>
                </div>
            @endfor
        </div>

        <button type="button" class="btn btn-border-black btn-sm" id="add-education">
            <i class="fas fa-plus me-1"></i>Add Education
        </button>
    </div>
</section>
