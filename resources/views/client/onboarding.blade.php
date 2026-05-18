@php
    use Illuminate\Support\Facades\Storage;

    $defaultInstructions = '
        <ul class="mb-2">
            <li>Full name, email, phone, and current location.</li>
            <li>Work authorization, years of experience, and key skills.</li>
            <li>Target roles/industries and salary expectations.</li>
            <li>Education, certifications, and notable achievements.</li>
            <li>Any gaps or special notes we should know.</li>
        </ul>
    ';

    $instructionsContent = trim((string) ($instructions ?? '')) !== '' ? $instructions : $defaultInstructions;
    $guideUrl = $guideFile ? storage_public_url($guideFile) : null;
@endphp

<x-app-layout>
    <x-slot name="title">Submit Onboarding</x-slot>
    <x-slot name="pageTitle">Submit Onboarding</x-slot>
    <x-slot name="pageSubtitle">Share your resume and onboarding details.</x-slot>

    <div class="onboarding-page-shell">
        <div class="onboarding-guide-toolbar mb-2">
            <button type="button" class="btn btn-border-black onboarding-guide-trigger" id="openOnboardingGuide">
                <i class="fas fa-book-open me-2"></i>Quick Guide
            </button>
        </div>
          <section class="card onboarding-hero-card mb-4">
            {{-- <p class="onboarding-eyebrow mb-2">Client Intake</p> --}}
            <h2 class="onboarding-hero-title mb-2">Onboarding Form</h2>
            <p class="onboarding-hero-copy mb-0">Please complete this with accurate details so the workflow can begin promptly.</p>
        </section>

        <section class="card onboarding-checklist-card mb-3">
            <div class="card-body">
                <h6 class="mb-2">Checklist</h6>
                <ul class="mb-0 ps-3 text-muted small d-grid gap-1" style="margin-left: 0px; list-style: disc;">
                    <li>Upload your latest resume.</li>
                    <li>Provide accurate contact and address details.</li>
                    <li>Add clear target roles and salary expectation.</li>
                    <li>If you do not have a resume, email <a href="mailto:caliwfh@outlook.com">caliwfh@outlook.com</a>.</li>
                </ul>
            </div>
        </section>



        <div class="row g-3 align-items-start">
            <div class="col-12">
                @if($errors->any())
                    <div class="alert alert-danger rounded-4 mb-4">
                        <div class="fw-semibold mb-1">Please fix the highlighted fields:</div>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card onboarding-main-card">
                    <div class="card-body p-3">
                        <form action="{{ route('client.onboarding.store') }}" method="POST" enctype="multipart/form-data" class="d-grid gap-3">
                            @csrf
                            @include('client.partials.onboarding.form-fields')

                            <div class="border rounded-4 p-3 bg-light d-grid gap-2">
                                <label class="form-check d-flex align-items-start gap-2 mb-0">
                                    <input
                                        type="checkbox"
                                        name="acknowledge_policies"
                                        value="1"
                                        class="form-check-input mt-1 @error('acknowledge_policies') is-invalid @enderror"
                                        {{ old('acknowledge_policies') ? 'checked' : '' }}
                                        required
                                    >
                                    <span class="small text-muted">
                                        I acknowledge that I have read and agree to the
                                        <a href="{{ route('terms-of-service.page') }}" target="_blank" rel="noopener">Terms of Service</a>,
                                        <a href="{{ route('booking-policy.page') }}" target="_blank" rel="noopener">Booking Policy</a>,
                                        <a href="{{ route('refund-policy.page') }}" target="_blank" rel="noopener">Refund Policy</a>
                                        <span class="text-danger fw-semibold">*</span>
                                    </span>
                                </label>
                                @error('acknowledge_policies')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="onboarding-submit-strip">
                                <p class="mb-0 text-muted small">Once you submit, this request will be marked as completed.</p>
                                <button type="submit" class="btn btn-black">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Onboarding
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <section class="onboarding-disclaimer-card" aria-label="Service disclaimer">
                    <div class="onboarding-disclaimer-icon" aria-hidden="true">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>
                    <div class="onboarding-disclaimer-copy">
                        <p class="onboarding-disclaimer-text mb-0">THIS IS A NON-REFUNDABLE SERVICE. OUR TEAM WILL WORK ON YOUR BEHALF FOR THE ALLOTTED TIME YOU SIGNED UP FOR TO THE BEST OF OUR ABILITY. THIS IS NOT A GUARANTEED JOB POSITION.</p>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="onboarding-guide-modal" id="onboardingGuideModal" aria-hidden="true">
        <div class="onboarding-guide-modal-backdrop" data-guide-close></div>
        <div class="onboarding-guide-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="onboardingGuideTitle">
            <button type="button" class="onboarding-guide-modal-close" id="closeOnboardingGuide" aria-label="Close guide">
                <i class="fas fa-times"></i>
            </button>

            <div class="onboarding-guide-modal-header">
                <p class="onboarding-guide-modal-eyebrow mb-1">Onboarding Help</p>
                <h5 class="mb-0" id="onboardingGuideTitle">Quick Guide</h5>
            </div>

            <div class="onboarding-guide-modal-body text-muted small">
                @if(str_contains($instructionsContent, '<'))
                    {!! $instructionsContent !!}
                @else
                    {!! nl2br(e($instructionsContent)) !!}
                @endif
            </div>

            @if($guideUrl)
                <div class="onboarding-guide-modal-footer">
                    <a href="{{ $guideUrl }}" class="btn btn-border-black w-100" target="_blank" rel="noopener">
                        <i class="fas fa-download me-2"></i>Download Full Guide
                    </a>
                </div>
            @endif
        </div>
    </div>

    @push('styles')
        <style>
            .onboarding-page-shell { display: grid; max-width: 1320px; margin-inline: auto; }
            .onboarding-guide-toolbar { display: flex; justify-content: flex-end; }
            .onboarding-guide-trigger { border-radius: 999px; padding: 0.5rem 0.95rem; font-size: 0.78rem; font-weight: 700; }
            .onboarding-checklist-card { border: 1px solid rgba(200, 164, 93, 0.18); border-radius: 0.95rem; background: linear-gradient(135deg, #ffffff 0%, #fbf6ec 100%); }
            .onboarding-checklist-card .card-body { padding: 0.78rem 0.9rem; }
            .onboarding-checklist-card h6 { font-size: 0.9rem; font-weight: 700; color: #111111; }
            .onboarding-checklist-card ul { margin: 0; columns: 2; column-gap: 1.4rem; }
            .onboarding-checklist-card li { break-inside: avoid; margin-bottom: 0.18rem; }
            .onboarding-hero-card { border: 1px solid rgba(200, 164, 93, 0.18); border-radius: 1rem; background: linear-gradient(135deg, #ffffff 0%, #f8f1e6 100%); padding: 0.95rem 1.1rem; }
            .onboarding-eyebrow { color: #8b7350; font-size: 0.68rem; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; }
            .onboarding-hero-title { color: #111111; font-size: clamp(1.05rem, 1.8vw, 1.45rem); font-weight: 800; }
            .onboarding-hero-copy { color: #6f6555; font-size: 0.86rem; max-width: 48rem; }
            .onboarding-main-card { border: 1px solid rgba(200, 164, 93, 0.16); border-radius: 1rem; }
            .onboarding-section-card { border: 1px solid rgba(200, 164, 93, 0.16); border-radius: 0.95rem; overflow: visible; }
            .onboarding-section-head { display: flex; gap: 0.65rem; align-items: flex-start; padding: 0.75rem 0.85rem; border-top-left-radius: 0.95rem; border-top-right-radius: 0.95rem; border-bottom: 1px solid rgba(200, 164, 93, 0.14); background: linear-gradient(180deg, rgba(251, 246, 236, 0.85) 0%, rgba(255, 255, 255, 0.95) 100%); }
            .onboarding-section-head h6 { font-size: 0.92rem; }
            .onboarding-section-head p { font-size: 0.78rem; line-height: 1.4; }
            .onboarding-step { display: inline-flex; align-items: center; justify-content: center; min-width: 3.7rem; border-radius: 999px; background: #111111; color: #f7ead0; padding: 0.28rem 0.5rem; font-size: 0.64rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; }
            .onboarding-section-body { padding: 0.82rem 0.85rem; }
            .onboarding-page-shell .form-label { color: #3f3a31; font-size: 0.78rem; font-weight: 600; margin-bottom: 0.3rem; }
            .onboarding-page-shell .form-control,
            .onboarding-page-shell .form-select { border-radius: 0.68rem; font-size: 0.85rem; min-height: 2.35rem; padding: 0.48rem 0.68rem; border: 1px solid rgba(17, 17, 17, 0.14); background-color: #fff; }
            .onboarding-page-shell .form-check-input:checked { background-color: #111111; border-color: #111111; }
            .onboarding-page-shell .form-check-input:focus { border-color: #111111; box-shadow: 0 0 0 0.2rem rgba(17, 17, 17, 0.12); }
            .onboarding-page-shell .form-select {
                appearance: none;
                -webkit-appearance: none;
                -moz-appearance: none;
                min-height: 3rem;
                padding: 0.72rem 3.1rem 0.72rem 0.95rem;
                border-radius: 0.95rem;
                border: 1px solid rgba(200, 164, 93, 0.26);
                background-color: #fffdfa;
                background-image:
                    linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 241, 229, 0.98) 100%),
                    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 14 9' fill='none'%3E%3Cpath d='M1.5 1.75L7 7.25L12.5 1.75' stroke='%238b7350' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
                background-position: 0 0, calc(100% - 0.95rem) 50%;
                background-size: 100% 100%, 0.9rem 0.6rem;
                background-repeat: no-repeat;
                box-shadow: 0 10px 22px rgba(17, 17, 17, 0.05), inset 0 1px 0 rgba(255, 255, 255, 0.72);
                color: #1f1b16;
                font-weight: 600;
                line-height: 1.35;
                cursor: pointer;
                transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease, background-color 0.18s ease;
            }
            .onboarding-page-shell .form-select.is-placeholder {
                color: #9a8d79;
                font-weight: 500;
            }
            .onboarding-page-shell .form-select:hover {
                border-color: rgba(200, 164, 93, 0.72);
                box-shadow: 0 14px 26px rgba(200, 164, 93, 0.12), inset 0 1px 0 rgba(255, 255, 255, 0.8);
                transform: translateY(-1px);
            }
            .onboarding-page-shell .form-select:focus {
                border-color: rgba(200, 164, 93, 0.88);
                box-shadow: 0 0 0 0.2rem rgba(200, 164, 93, 0.18), 0 14px 28px rgba(17, 17, 17, 0.08);
                background-image:
                    linear-gradient(135deg, rgba(255, 255, 255, 1) 0%, rgba(252, 246, 235, 1) 100%),
                    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 14 9' fill='none'%3E%3Cpath d='M1.5 1.75L7 7.25L12.5 1.75' stroke='%23b68c3a' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            }
            .onboarding-page-shell .form-select.is-invalid {
                border-color: rgba(220, 53, 69, 0.48);
                box-shadow: 0 10px 20px rgba(220, 53, 69, 0.08);
            }
            .onboarding-page-shell .form-select option {
                color: #1f1b16;
                background: #fffdf9;
                font-weight: 500;
            }
            .onboarding-page-shell .onboarding-select-native { display: none !important; }
            .onboarding-custom-select {
                position: relative;
                width: 100%;
                min-width: 0;
            }
            .onboarding-custom-select-trigger {
                position: relative;
                display: flex;
                align-items: center;
                width: 100%;
                min-height: 3rem;
                border: 1px solid rgba(200, 164, 93, 0.26);
                border-radius: 0.95rem;
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 241, 229, 0.98) 100%);
                box-shadow: 0 10px 22px rgba(17, 17, 17, 0.05), inset 0 1px 0 rgba(255, 255, 255, 0.72);
                color: #1f1b16;
                font-size: 0.85rem;
                font-weight: 600;
                line-height: 1.35;
                text-align: left;
                padding: 0.72rem 3rem 0.72rem 0.95rem;
                transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease, background-color 0.18s ease;
            }
            .onboarding-custom-select-trigger [data-onboarding-select-label] {
                display: block;
                width: 100%;
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .onboarding-custom-select-trigger::after {
                content: "";
                position: absolute;
                top: 50%;
                right: 1rem;
                width: 0.8rem;
                height: 0.52rem;
                transform: translateY(-50%);
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 14 9' fill='none'%3E%3Cpath d='M1.5 1.75L7 7.25L12.5 1.75' stroke='%238b7350' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
                background-repeat: no-repeat;
                background-size: contain;
                transition: transform 0.18s ease;
                pointer-events: none;
            }
            .onboarding-custom-select.is-placeholder .onboarding-custom-select-trigger {
                color: #9a8d79;
                font-weight: 500;
            }
            .onboarding-custom-select.is-open .onboarding-custom-select-trigger,
            .onboarding-custom-select-trigger:hover {
                border-color: rgba(200, 164, 93, 0.72);
                box-shadow: 0 14px 26px rgba(200, 164, 93, 0.12), inset 0 1px 0 rgba(255, 255, 255, 0.8);
                transform: translateY(-1px);
            }
            .onboarding-custom-select.is-open .onboarding-custom-select-trigger {
                background: linear-gradient(135deg, rgba(255, 255, 255, 1) 0%, rgba(252, 246, 235, 1) 100%);
            }
            .onboarding-custom-select.is-open .onboarding-custom-select-trigger::after {
                transform: translateY(-50%) rotate(180deg);
            }
            .onboarding-custom-select-trigger:focus {
                outline: none;
                border-color: rgba(200, 164, 93, 0.88);
                box-shadow: 0 0 0 0.2rem rgba(200, 164, 93, 0.18), 0 14px 28px rgba(17, 17, 17, 0.08);
            }
            .onboarding-custom-select.is-invalid .onboarding-custom-select-trigger {
                border-color: rgba(220, 53, 69, 0.48);
                box-shadow: 0 10px 20px rgba(220, 53, 69, 0.08);
            }
            .onboarding-custom-select-menu {
                position: absolute;
                top: calc(100% + 0.5rem);
                left: 0;
                right: 0;
                z-index: 50;
                display: grid;
                gap: 0.35rem;
                padding: 0.45rem;
                border: 1px solid rgba(200, 164, 93, 0.22);
                border-radius: 1rem;
                background: rgba(255, 255, 255, 0.98);
                box-shadow: 0 22px 48px rgba(17, 17, 17, 0.12);
                backdrop-filter: blur(12px);
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transform: translateY(-0.3rem);
                transition: opacity 0.16s ease, transform 0.16s ease, visibility 0.16s ease;
                max-height: 16rem;
                overflow-y: auto;
            }
            .onboarding-custom-select.is-open .onboarding-custom-select-menu {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                transform: translateY(0);
            }
            .onboarding-custom-select-option {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                width: 100%;
                border: 0;
                border-radius: 0.8rem;
                background: transparent;
                color: #2f2922;
                text-align: left;
                padding: 0.72rem 0.8rem;
                font-size: 0.84rem;
                font-weight: 600;
                line-height: 1.4;
                transition: background-color 0.16s ease, color 0.16s ease, transform 0.16s ease;
            }
            .onboarding-custom-select-option:hover,
            .onboarding-custom-select-option:focus {
                background: #f9f1e3;
                color: #111111;
                outline: none;
            }
            .onboarding-custom-select-option.is-placeholder-option {
                color: #8e816d;
                font-weight: 500;
            }
            .onboarding-custom-select-option.is-selected {
                background: linear-gradient(135deg, #111111 0%, #211c15 100%);
                color: #f6e7c4;
                box-shadow: 0 10px 22px rgba(17, 17, 17, 0.12);
            }
            .onboarding-custom-select-option-check {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 1.1rem;
                height: 1.1rem;
                border-radius: 999px;
                background: rgba(200, 164, 93, 0.16);
                color: #9b7431;
                font-size: 0.62rem;
                flex-shrink: 0;
                opacity: 0;
                transition: opacity 0.16s ease;
            }
            .onboarding-custom-select-option.is-selected .onboarding-custom-select-option-check {
                opacity: 1;
                background: rgba(246, 231, 196, 0.16);
                color: #f6e7c4;
            }
            .onboarding-page-shell textarea.form-control { min-height: 4.6rem; }
            .onboarding-page-shell .invalid-feedback { font-size: 0.76rem; }
            .onboarding-submit-strip { display: flex; align-items: center; justify-content: space-between; gap: 0.8rem; border: 1px solid rgba(200, 164, 93, 0.18); border-radius: 0.9rem; background: linear-gradient(135deg, #fffdf9 0%, #f6efe3 100%); padding: 0.72rem 0.82rem; }
            .onboarding-submit-strip p { font-size: 0.76rem; line-height: 1.35; }
            .onboarding-disclaimer-card { display: flex; align-items: flex-start; gap: 0.95rem; margin-top: 1rem; border: 1px solid rgba(185, 28, 28, 0.18); border-radius: 1rem; background: linear-gradient(135deg, #fff8f8 0%, #fff1f1 100%); box-shadow: 0 16px 34px rgba(127, 29, 29, 0.08); padding: 1rem 1.05rem; }
            .onboarding-disclaimer-icon { flex: 0 0 auto; width: 2.6rem; height: 2.6rem; border-radius: 999px; background: rgba(185, 28, 28, 0.12); color: #b91c1c; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; }
            .onboarding-disclaimer-copy { min-width: 0; }
            .onboarding-disclaimer-label { color: #7f1d1d; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.14em; text-transform: uppercase; }
            .onboarding-disclaimer-text { color: #b91c1c; font-size: 0.85rem; line-height: 1.65; font-weight: 700; letter-spacing: 0.02em; }
            .onboarding-education-block { border: 1px solid rgba(200, 164, 93, 0.14); border-radius: 0.82rem; background: #fffdfa; padding: 0.76rem; }

            .onboarding-resume-upload-input { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
            .onboarding-resume-dropzone { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.2rem; min-height: 8.1rem; border: 1.5px dashed rgba(200, 164, 93, 0.45); border-radius: 0.88rem; background: linear-gradient(135deg, #fffdf9 0%, #f8f2e8 100%); text-align: center; padding: 0.85rem; cursor: pointer; transition: 0.2s ease; }
            .onboarding-resume-dropzone:hover, .onboarding-resume-dropzone.is-active { border-color: rgba(200, 164, 93, 0.72); box-shadow: 0 0 0 4px rgba(200, 164, 93, 0.14); transform: translateY(-1px); }
            .onboarding-resume-dropzone.has-file { background: linear-gradient(135deg, #111111 0%, #1e1e1e 100%); border-color: rgba(200, 164, 93, 0.72); }
            .onboarding-resume-dropzone.is-invalid { border-color: rgba(220, 53, 69, 0.7); }
            .onboarding-resume-dropzone-icon { width: 2.35rem; height: 2.35rem; border-radius: 999px; background: #111111; color: #c8a45d; display: inline-flex; align-items: center; justify-content: center; font-size: 0.92rem; }
            .onboarding-resume-dropzone.has-file .onboarding-resume-dropzone-icon { background: rgba(200, 164, 93, 0.18); color: #f4d58f; }
            .onboarding-resume-dropzone-title { color: #111111; font-size: 0.88rem; font-weight: 700; overflow-wrap: anywhere; }
            .onboarding-resume-dropzone-copy { color: #6f6555; font-size: 0.78rem; font-weight: 500; }
            .onboarding-resume-dropzone-meta { color: #8b7350; font-size: 0.68rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
            .onboarding-resume-dropzone.has-file .onboarding-resume-dropzone-title,
            .onboarding-resume-dropzone.has-file .onboarding-resume-dropzone-copy,
            .onboarding-resume-dropzone.has-file .onboarding-resume-dropzone-meta { color: #f7ead0; }

            .onboarding-guide-modal {
                position: fixed;
                inset: 0;
                z-index: 3000;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 1rem;
            }
            .onboarding-guide-modal.is-open { display: flex; }
            .onboarding-guide-modal-backdrop {
                position: absolute;
                inset: 0;
                background: rgba(0, 0, 0, 0.54);
                backdrop-filter: blur(3px);
            }
            .onboarding-guide-modal-dialog {
                position: relative;
                width: min(42rem, calc(100vw - 2rem));
                max-height: min(38rem, calc(100vh - 2rem));
                background: #ffffff;
                border: 1px solid rgba(200, 164, 93, 0.3);
                border-radius: 1.15rem;
                box-shadow: 0 24px 56px rgba(17, 17, 17, 0.18);
                padding: 1rem 1rem 0.9rem;
                display: flex;
                flex-direction: column;
            }
            .onboarding-guide-modal-close {
                position: absolute;
                top: 0.6rem;
                right: 0.65rem;
                border: 0;
                background: transparent;
                color: #111111;
                width: 2rem;
                height: 2rem;
                border-radius: 999px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
            .onboarding-guide-modal-close:hover { background: rgba(17, 17, 17, 0.08); }
            .onboarding-guide-modal-header { padding-right: 2.2rem; margin-bottom: 0.6rem; }
            .onboarding-guide-modal-eyebrow { color: #8b7350; font-size: 0.66rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; }
            .onboarding-guide-modal-body {
                border: 1px solid rgba(200, 164, 93, 0.22);
                border-radius: 0.9rem;
                padding: 0.75rem 0.8rem;
                flex: 1 1 auto;
                min-height: 0;
                overflow-y: auto;
            }
            .onboarding-guide-modal-footer { margin-top: 0.75rem; }

            @media (max-width: 768px) {
                .onboarding-guide-toolbar { justify-content: stretch; }
                .onboarding-guide-trigger { width: 100%; border-radius: 0.75rem; }
                .onboarding-checklist-card ul { columns: 1; }
                .onboarding-hero-card { padding: 0.8rem 0.85rem; }
                .onboarding-section-head, .onboarding-section-body { padding: 0.72rem; }
                .onboarding-custom-select-menu { max-height: 13.5rem; }
                .onboarding-submit-strip { flex-direction: column; align-items: stretch; }
                .onboarding-submit-strip .btn { width: 100%; }
                .onboarding-disclaimer-card { flex-direction: column; align-items: center; text-align: center; padding: 0.9rem; }
                .onboarding-disclaimer-copy { text-align: center; }
                .onboarding-guide-modal { padding: 0.75rem; }
                .onboarding-guide-modal-dialog {
                    width: min(100%, 24rem);
                    max-height: min(32rem, calc(100vh - 1.5rem));
                    padding: 0.88rem 0.85rem 0.8rem;
                    border-radius: 1rem;
                }
                .onboarding-guide-modal-body {
                    padding: 0.72rem 0.75rem;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const resumeInput = document.getElementById('onboarding_resume_file');
                const resumeDropzone = document.getElementById('onboardingResumeDropzone');
                const resumeTitle = document.getElementById('onboardingResumeTitle');
                const resumeMeta = document.getElementById('onboardingResumeMeta');
                const openGuideBtn = document.getElementById('openOnboardingGuide');
                const closeGuideBtn = document.getElementById('closeOnboardingGuide');
                const guideModal = document.getElementById('onboardingGuideModal');
                const addBtn = document.getElementById('add-education');
                const wrapper = document.getElementById('education-wrapper');
                let bodyOverflowBeforeGuide = '';

                if (guideModal && guideModal.parentElement !== document.body) {
                    document.body.appendChild(guideModal);
                }

                const defaultResumeTitle = 'Drag and drop your old resume';
                const defaultResumeMeta = 'PDF, DOC, DOCX, CSV, XLSX, XLS up to 10MB';
                const onboardingSelects = document.querySelectorAll('.onboarding-page-shell select.form-select');
                const customSelectShells = [];

                const formatFileSize = (size) => {
                    if (!Number.isFinite(size) || size <= 0) return '0 B';
                    const units = ['B', 'KB', 'MB', 'GB'];
                    let value = size;
                    let unitIndex = 0;
                    while (value >= 1024 && unitIndex < units.length - 1) {
                        value /= 1024;
                        unitIndex++;
                    }
                    return `${value.toFixed(value >= 10 || unitIndex === 0 ? 0 : 1)} ${units[unitIndex]}`;
                };

                const syncResumeFileState = (file) => {
                    if (!resumeDropzone || !resumeTitle || !resumeMeta) return;
                    if (file) {
                        resumeDropzone.classList.add('has-file');
                        resumeTitle.textContent = file.name;
                        const fileType = file.type ? file.type.toUpperCase() : 'FILE';
                        resumeMeta.textContent = `${fileType} | ${formatFileSize(file.size)}`;
                        return;
                    }
                    resumeDropzone.classList.remove('has-file');
                    resumeTitle.textContent = defaultResumeTitle;
                    resumeMeta.textContent = defaultResumeMeta;
                };

                const closeCustomSelects = (exceptShell = null) => {
                    customSelectShells.forEach(({ shell, trigger }) => {
                        if (shell === exceptShell) {
                            return;
                        }

                        shell.classList.remove('is-open');
                        trigger.setAttribute('aria-expanded', 'false');
                    });
                };

                const syncCustomSelectState = (select, shell) => {
                    if (!(select instanceof HTMLSelectElement) || !(shell instanceof HTMLElement)) return;

                    const label = shell.querySelector('[data-onboarding-select-label]');
                    const selectedValue = select.value;
                    const selectedOption = Array.from(select.options).find((option) => option.value === selectedValue) ?? select.options[0] ?? null;

                    if (label) {
                        label.textContent = selectedOption ? selectedOption.textContent.trim() : '';
                    }

                    shell.classList.toggle('is-placeholder', selectedValue === '');
                    shell.classList.toggle('is-invalid', select.classList.contains('is-invalid'));

                    shell.querySelectorAll('[data-onboarding-select-value]').forEach((optionButton) => {
                        const isSelected = optionButton.getAttribute('data-onboarding-select-value') === selectedValue;
                        optionButton.classList.toggle('is-selected', isSelected);
                        optionButton.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                    });
                };

                const buildCustomSelect = (select, index) => {
                    if (!(select instanceof HTMLSelectElement) || select.multiple || select.size > 1 || select.dataset.customSelectReady === 'true') {
                        return;
                    }

                    select.dataset.customSelectReady = 'true';
                    select.classList.add('onboarding-select-native');
                    select.setAttribute('tabindex', '-1');
                    select.setAttribute('aria-hidden', 'true');

                    const shell = document.createElement('div');
                    shell.className = 'onboarding-custom-select';

                    const trigger = document.createElement('button');
                    trigger.type = 'button';
                    trigger.className = 'onboarding-custom-select-trigger';
                    trigger.setAttribute('aria-haspopup', 'listbox');
                    trigger.setAttribute('aria-expanded', 'false');

                    const label = document.createElement('span');
                    label.setAttribute('data-onboarding-select-label', 'true');
                    trigger.appendChild(label);

                    const menu = document.createElement('div');
                    menu.className = 'onboarding-custom-select-menu';
                    menu.setAttribute('role', 'listbox');
                    menu.id = select.id ? `${select.id}_custom_listbox` : `onboarding_custom_select_${index}`;
                    trigger.setAttribute('aria-controls', menu.id);

                    Array.from(select.options).forEach((option) => {
                        const optionButton = document.createElement('button');
                        optionButton.type = 'button';
                        optionButton.className = 'onboarding-custom-select-option';
                        optionButton.setAttribute('role', 'option');
                        optionButton.setAttribute('data-onboarding-select-value', option.value);
                        optionButton.disabled = option.disabled;

                        if (option.value === '') {
                            optionButton.classList.add('is-placeholder-option');
                        }

                        const optionLabel = document.createElement('span');
                        optionLabel.className = 'onboarding-custom-select-option-label';
                        optionLabel.textContent = option.textContent.trim();

                        const optionCheck = document.createElement('span');
                        optionCheck.className = 'onboarding-custom-select-option-check';
                        optionCheck.innerHTML = '<i class="fas fa-check"></i>';

                        optionButton.appendChild(optionLabel);
                        optionButton.appendChild(optionCheck);

                        optionButton.addEventListener('click', () => {
                            if (option.disabled) {
                                return;
                            }

                            select.value = option.value;
                            select.dispatchEvent(new Event('change', { bubbles: true }));
                            closeCustomSelects();
                            trigger.focus();
                        });

                        menu.appendChild(optionButton);
                    });

                    select.insertAdjacentElement('afterend', shell);
                    shell.appendChild(trigger);
                    shell.appendChild(menu);

                    trigger.addEventListener('click', () => {
                        const shouldOpen = !shell.classList.contains('is-open');
                        closeCustomSelects(shell);
                        shell.classList.toggle('is-open', shouldOpen);
                        trigger.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
                    });

                    trigger.addEventListener('keydown', (event) => {
                        if (!['ArrowDown', 'Enter', ' '].includes(event.key)) {
                            return;
                        }

                        event.preventDefault();

                        if (!shell.classList.contains('is-open')) {
                            closeCustomSelects(shell);
                            shell.classList.add('is-open');
                            trigger.setAttribute('aria-expanded', 'true');
                        }

                        menu.querySelector('.onboarding-custom-select-option:not(:disabled)')?.focus();
                    });

                    menu.addEventListener('keydown', (event) => {
                        const enabledOptions = Array.from(menu.querySelectorAll('.onboarding-custom-select-option:not(:disabled)'));
                        const currentIndex = enabledOptions.indexOf(document.activeElement);

                        if (event.key === 'Escape') {
                            event.preventDefault();
                            closeCustomSelects();
                            trigger.focus();
                            return;
                        }

                        if (event.key === 'ArrowDown') {
                            event.preventDefault();
                            enabledOptions[Math.min(currentIndex + 1, enabledOptions.length - 1)]?.focus();
                            return;
                        }

                        if (event.key === 'ArrowUp') {
                            event.preventDefault();
                            enabledOptions[Math.max(currentIndex - 1, 0)]?.focus();
                        }
                    });

                    select.addEventListener('change', () => syncCustomSelectState(select, shell));
                    syncCustomSelectState(select, shell);
                    customSelectShells.push({ shell, trigger });
                };

                const openGuideModal = () => {
                    if (!guideModal) return;
                    bodyOverflowBeforeGuide = document.body.style.overflow;
                    guideModal.classList.add('is-open');
                    guideModal.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                };

                const closeGuideModal = () => {
                    if (!guideModal) return;
                    guideModal.classList.remove('is-open');
                    guideModal.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = bodyOverflowBeforeGuide;
                };

                openGuideBtn?.addEventListener('click', openGuideModal);
                closeGuideBtn?.addEventListener('click', closeGuideModal);

                guideModal?.addEventListener('click', (event) => {
                    if (event.target instanceof Element && event.target.closest('[data-guide-close]')) {
                        closeGuideModal();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && guideModal?.classList.contains('is-open')) {
                        closeGuideModal();
                    }
                });

                if (resumeInput && resumeDropzone) {
                    syncResumeFileState(resumeInput.files?.[0] ?? null);
                    resumeInput.addEventListener('change', (event) => syncResumeFileState(event.target.files?.[0] ?? null));

                    ['dragenter', 'dragover'].forEach((eventName) => {
                        resumeDropzone.addEventListener(eventName, (event) => {
                            event.preventDefault();
                            resumeDropzone.classList.add('is-active');
                        });
                    });

                    ['dragleave', 'drop', 'dragend'].forEach((eventName) => {
                        resumeDropzone.addEventListener(eventName, (event) => {
                            event.preventDefault();
                            resumeDropzone.classList.remove('is-active');
                        });
                    });

                    resumeDropzone.addEventListener('drop', (event) => {
                        const droppedFile = event.dataTransfer?.files?.[0];
                        if (!droppedFile) return;

                        if (typeof DataTransfer !== 'undefined') {
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(droppedFile);
                            resumeInput.files = dataTransfer.files;
                            resumeInput.dispatchEvent(new Event('change', { bubbles: true }));
                        } else {
                            syncResumeFileState(droppedFile);
                        }
                    });
                }

                onboardingSelects.forEach((select, index) => buildCustomSelect(select, index));

                document.addEventListener('click', (event) => {
                    if (!(event.target instanceof Element) || event.target.closest('.onboarding-custom-select')) {
                        return;
                    }

                    closeCustomSelects();
                });

                const refreshEducation = () => {
                    if (!wrapper) return;
                    const blocks = wrapper.querySelectorAll('.education-block');
                    blocks.forEach((block, index) => {
                        const title = block.querySelector('.education-block-title');
                        const removeButton = block.querySelector('.remove-edu');
                        if (title) title.textContent = `Education #${index + 1}`;
                        if (removeButton) removeButton.style.display = index === 0 ? 'none' : '';
                    });
                };

                const newEducationBlock = (index) => `
                    <div class="education-block onboarding-education-block mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-semibold education-block-title">Education #${index}</span>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-edu"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Institution Name *</label>
                                <input type="text" name="education[institution][]" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Degree *</label>
                                <input type="text" name="education[degree][]" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Enrollment Date</label>
                                <input type="text" name="education[enrollment][]" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Graduation Date</label>
                                <input type="text" name="education[graduation][]" class="form-control">
                            </div>
                        </div>
                    </div>
                `;

                addBtn?.addEventListener('click', () => {
                    if (!wrapper) return;
                    const index = wrapper.querySelectorAll('.education-block').length + 1;
                    wrapper.insertAdjacentHTML('beforeend', newEducationBlock(index));
                    refreshEducation();
                });

                wrapper?.addEventListener('click', (event) => {
                    const removeButton = event.target.closest('.remove-edu');
                    if (!removeButton) return;
                    const blocks = wrapper.querySelectorAll('.education-block');
                    const block = removeButton.closest('.education-block');
                    if (!block || blocks.length <= 1) return;
                    block.remove();
                    refreshEducation();
                });

                refreshEducation();
            });
        </script>
    @endpush
</x-app-layout>
