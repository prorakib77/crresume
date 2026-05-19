<section class="onboarding-section-card">
    <div class="onboarding-section-head">
        <span class="onboarding-step">Step 5</span>
        <div>
            <h6 class="mb-1 fw-bold">Service and Notes</h6>
            <p class="mb-0 text-muted small">Choose your package and let us know if you have any suggestions.</p>
        </div>
    </div>
    <div class="onboarding-section-body">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Service Package *</label>
                <select name="service_package" class="form-select @error('service_package') is-invalid @enderror" required>
                    <option value="">Select service</option>
                    <option value="2-weeks" {{ old('service_package') === '2-weeks' ? 'selected' : '' }}>2 Weeks</option>
                    <option value="3-weeks" {{ old('service_package') === '3-weeks' ? 'selected' : '' }}>3 Weeks</option>
                    <option value="4-weeks" {{ old('service_package') === '4-weeks' ? 'selected' : '' }}>4 Weeks</option>
                    <option value="5-weeks" {{ old('service_package') === '5-weeks' ? 'selected' : '' }}>5 Weeks</option>
                    <option value="6-weeks" {{ old('service_package') === '6-weeks' ? 'selected' : '' }}>6 Weeks</option>
                </select>
                @error('service_package')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Note (optional)</label>
                <textarea class="form-control @error('onboarding_note') is-invalid @enderror" name="onboarding_note" rows="2" placeholder="Anything specific we should know?">{{ old('onboarding_note') }}</textarea>
                @error('onboarding_note')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</section>
