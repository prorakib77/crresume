@php
    $educationInstitutions = old('education.institution', []);
    $educationCount = max(1, count($educationInstitutions));
@endphp

<section class="onboarding-section-card">
    <div class="onboarding-section-head">
        <span class="onboarding-step">Step 1</span>
        <div>
            <h6 class="mb-1 fw-bold">Resume Upload</h6>
            <p class="mb-0 text-muted small">Upload your latest resume file.</p>
        </div>
    </div>
    <div class="onboarding-section-body">
        <label class="form-label fw-semibold" for="onboarding_resume_file">Old Resume</label>
        <input
            id="onboarding_resume_file"
            type="file"
            name="onboarding_resume_file"
            class="onboarding-resume-upload-input @error('onboarding_resume_file') is-invalid @enderror"
            accept=".pdf,.doc,.docx,.csv,.xlsx,.xls"
        >
        <label
            for="onboarding_resume_file"
            id="onboardingResumeDropzone"
            class="onboarding-resume-dropzone @error('onboarding_resume_file') is-invalid @enderror"
        >
            <span class="onboarding-resume-dropzone-icon">
                <i class="fas fa-cloud-arrow-up"></i>
            </span>
            <span id="onboardingResumeTitle" class="onboarding-resume-dropzone-title">Drag and drop your old resume</span>
            <span class="onboarding-resume-dropzone-copy">or click here to choose a file</span>
            <span id="onboardingResumeMeta" class="onboarding-resume-dropzone-meta">PDF, DOC, DOCX, CSV, XLSX, XLS up to 10MB</span>
        </label>
        @error('onboarding_resume_file')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</section>

@include('client.partials.onboarding.section-personal')
@include('client.partials.onboarding.section-career')
@include('client.partials.onboarding.section-education', ['educationCount' => $educationCount])
@include('client.partials.onboarding.section-service')

<section class="onboarding-section-card">
    <div class="onboarding-section-head">
        <span class="onboarding-step">Step 6</span>
        <div>
            <h6 class="mb-1 fw-bold">Client Signature</h6>
            <p class="mb-0 text-muted small">Upload your signature photo from your gallery or take one with your camera before submitting the onboarding form.</p>
        </div>
    </div>
    <div class="onboarding-section-body">
        <label class="form-label fw-semibold" for="client_signature_camera">
            Upload signature photo <span class="text-danger">*</span>
        </label>
        <input
            id="client_signature_camera"
            type="file"
            name="client_signature_camera"
            accept="image/*"
            class="form-control @error('client_signature_camera') is-invalid @enderror"
            required
        >
        <div class="form-text">You can upload from your gallery or take a photo on your device. JPG, PNG, or WEBP up to 5MB.</div>
        @error('client_signature_camera')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</section>
