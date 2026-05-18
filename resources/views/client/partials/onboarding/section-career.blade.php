<section class="onboarding-section-card">
    <div class="onboarding-section-head">
        <span class="onboarding-step">Step 3</span>
        <div>
            <h6 class="mb-1 fw-bold">Career Details</h6>
            <p class="mb-0 text-muted small">Define target roles, industries, salary, and key strengths.</p>
        </div>
    </div>
    <div class="onboarding-section-body">
        <div class="row g-3">
            <div class="col-lg-6">
                <label class="form-label">Remote roles you are targeting *</label>
                <textarea name="target_roles" class="form-control @error('target_roles') is-invalid @enderror" rows="2">{{ old('target_roles') }}</textarea>
                @error('target_roles')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-lg-6">
                <label class="form-label">Industries you are interested in and experienced in *</label>
                <textarea name="industries" class="form-control @error('industries') is-invalid @enderror" rows="2">{{ old('industries') }}</textarea>
                @error('industries')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-lg-6">
                <label class="form-label">Certifications</label>
                <textarea name="certifications" class="form-control @error('certifications') is-invalid @enderror" rows="2">{{ old('certifications') }}</textarea>
                @error('certifications')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-lg-6">
                <label class="form-label">Professional licenses</label>
                <textarea name="licenses" class="form-control @error('licenses') is-invalid @enderror" rows="2">{{ old('licenses') }}</textarea>
                @error('licenses')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Desired Pay Type *</label>
                <select name="salary_type" class="form-select @error('salary_type') is-invalid @enderror">
                    <option value="">Select</option>
                    <option value="hourly" {{ old('salary_type') === 'hourly' ? 'selected' : '' }}>Hourly</option>
                    <option value="yearly" {{ old('salary_type') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                </select>
                @error('salary_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-8">
                <label class="form-label">Amount *</label>
                <input type="text" name="salary_amount" class="form-control @error('salary_amount') is-invalid @enderror" placeholder="e.g., 30/hr or 70,000/yr" value="{{ old('salary_amount') }}">
                @error('salary_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-lg-6">
                <label class="form-label">Details that strengthen your resume or job search</label>
                <textarea name="extra_strengths" class="form-control @error('extra_strengths') is-invalid @enderror" rows="2">{{ old('extra_strengths') }}</textarea>
                @error('extra_strengths')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-lg-6">
                <label class="form-label">Work experience not listed on your resume</label>
                <textarea name="extra_experience" class="form-control @error('extra_experience') is-invalid @enderror" rows="2">{{ old('extra_experience') }}</textarea>
                @error('extra_experience')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-lg-6">
                <label class="form-label">Soft skills</label>
                <textarea name="soft_skills" class="form-control @error('soft_skills') is-invalid @enderror" rows="2">{{ old('soft_skills') }}</textarea>
                @error('soft_skills')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-lg-6">
                <label class="form-label">Software/tools</label>
                <textarea name="tools" class="form-control @error('tools') is-invalid @enderror" rows="2">{{ old('tools') }}</textarea>
                @error('tools')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Languages</label>
                <input type="text" name="languages" class="form-control @error('languages') is-invalid @enderror" value="{{ old('languages') }}">
                @error('languages')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</section>
