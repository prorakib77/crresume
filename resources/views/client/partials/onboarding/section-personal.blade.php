<section class="onboarding-section-card">
    <div class="onboarding-section-head">
        <span class="onboarding-step">Step 2</span>
        <div>
            <h6 class="mb-1 fw-bold">Personal Information</h6>
            <p class="mb-0 text-muted small">Use accurate details exactly as they should appear in applications.</p>
        </div>
    </div>
    <div class="onboarding-section-body">
        <div class="row g-3">
            <div class="col-lg-6">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name') }}" required>
                @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 col-lg-3">
                <label class="form-label">Date of Birth</label>
                <input type="text" name="dob" class="form-control @error('dob') is-invalid @enderror" placeholder="MM/DD/YYYY" value="{{ old('dob') }}">
                @error('dob')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 col-lg-3">
                <label class="form-label">Social Security Number</label>
                <input type="text" name="ssn" class="form-control @error('ssn') is-invalid @enderror" value="{{ old('ssn') }}">
                @error('ssn')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">Current Address *</label>
                <input type="text" name="address_street" class="form-control @error('address_street') is-invalid @enderror" placeholder="Street" value="{{ old('address_street') }}" required>
                @error('address_street')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <input type="text" name="address_city" class="form-control @error('address_city') is-invalid @enderror" placeholder="City" value="{{ old('address_city') }}" required>
                @error('address_city')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <input type="text" name="address_state" class="form-control @error('address_state') is-invalid @enderror" placeholder="State" value="{{ old('address_state') }}" required>
                @error('address_state')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <input type="text" name="address_zip" class="form-control @error('address_zip') is-invalid @enderror" placeholder="ZIP" value="{{ old('address_zip') }}" required>
                @error('address_zip')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 col-lg-3">
                <label class="form-label">Best Contact Number *</label>
                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" required>
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 col-lg-3">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 col-lg-3">
                <label class="form-label">Sex *</label>
                <input type="text" name="sex" class="form-control @error('sex') is-invalid @enderror" value="{{ old('sex') }}" required>
                @error('sex')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 col-lg-3">
                <label class="form-label">Ethnicity</label>
                <input type="text" name="ethnicity" class="form-control @error('ethnicity') is-invalid @enderror" value="{{ old('ethnicity') }}">
                @error('ethnicity')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Are you a felon? *</label>
                <select name="is_felon" class="form-select @error('is_felon') is-invalid @enderror" required>
                    <option value="">Select</option>
                    <option value="yes" {{ old('is_felon') === 'yes' ? 'selected' : '' }}>Yes</option>
                    <option value="no" {{ old('is_felon') === 'no' ? 'selected' : '' }}>No</option>
                </select>
                @error('is_felon')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-8">
                <label class="form-label">Disabilities or accommodations</label>
                <textarea name="accommodations" class="form-control @error('accommodations') is-invalid @enderror" rows="2">{{ old('accommodations') }}</textarea>
                @error('accommodations')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</section>
