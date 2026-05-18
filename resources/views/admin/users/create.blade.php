<x-app-layout>
    <x-slot name="title">Create User</x-slot>
    <x-slot name="pageTitle">Create New User</x-slot>
    <x-slot name="pageSubtitle">Add a new user to the system</x-slot>

    @php
        $clientRoleId = optional($roles->first(fn($role) => strtolower($role->name) === 'client'))->id ?? \App\Models\User::ROLE_CLIENT;
    @endphp

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-plus me-2"></i>User Information
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                       id="password" name="password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control"
                                       id="password_confirmation" name="password_confirmation" required>
                            </div>

                            <div class="col-md-6">
                                <label for="role_id" class="form-label">Role *</label>
                                <select class="form-control @error('role_id') is-invalid @enderror"
                                        id="role_id" name="role_id" required>
                                    <option value="">Select a role...</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                            {{ ucfirst($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 client-only-field" id="onboarding_collected_wrapper">
                                <label for="onboarding_collected" class="form-label">Onboarding Collected? *</label>
                                <select class="form-control @error('onboarding_collected') is-invalid @enderror"
                                        id="onboarding_collected" name="onboarding_collected">
                                    <option value="no" {{ old('onboarding_collected', 'no') === 'no' ? 'selected' : '' }}>No</option>
                                    <option value="yes" {{ old('onboarding_collected') === 'yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                                <small class="text-muted">If yes, onboarding will be hidden on the client dashboard.</small>
                                @error('onboarding_collected')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 client-only-field">
                                <label for="estimated_resume_completion_date" class="form-label">Resume Est. Completion</label>
                                <input
                                    type="date"
                                    class="form-control @error('estimated_resume_completion_date') is-invalid @enderror"
                                    id="estimated_resume_completion_date"
                                    name="estimated_resume_completion_date"
                                    value="{{ old('estimated_resume_completion_date') }}"
                                >
                                @error('estimated_resume_completion_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 client-only-field">
                                <label for="estimated_cover_letter_completion_date" class="form-label">Cover Letter Est. Completion</label>
                                <input
                                    type="date"
                                    class="form-control @error('estimated_cover_letter_completion_date') is-invalid @enderror"
                                    id="estimated_cover_letter_completion_date"
                                    name="estimated_cover_letter_completion_date"
                                    value="{{ old('estimated_cover_letter_completion_date') }}"
                                >
                                @error('estimated_cover_letter_completion_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 client-only-field">
                                <label for="estimated_application_start_date" class="form-label">Job Apply Start Date</label>
                                <input
                                    type="date"
                                    class="form-control @error('estimated_application_start_date') is-invalid @enderror"
                                    id="estimated_application_start_date"
                                    name="estimated_application_start_date"
                                    value="{{ old('estimated_application_start_date') }}"
                                >
                                @error('estimated_application_start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Users
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Role Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Super Admin:</strong>
                        <p class="text-muted small mb-0">Full system access, can manage all users and settings</p>
                    </div>
                    <div class="mb-3">
                        <strong>Admin:</strong>
                        <p class="text-muted small mb-0">Full access except cannot delete super admins</p>
                    </div>
                    <div class="mb-3">
                        <strong>Agent:</strong>
                        <p class="text-muted small mb-0">Can submit work updates for assigned clients</p>
                    </div>
                    <div class="mb-3">
                        <strong>Client:</strong>
                        <p class="text-muted small mb-0">Can view and download their work updates</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const roleField = document.getElementById('role_id');
                const clientFields = Array.from(document.querySelectorAll('.client-only-field'));
                const clientRoleId = '{{ (string) $clientRoleId }}';

                const syncOnboardingVisibility = () => {
                    const isClient = roleField && roleField.value === clientRoleId;

                    if (!clientFields.length) {
                        return;
                    }

                    clientFields.forEach((fieldWrap) => {
                        fieldWrap.style.display = isClient ? '' : 'none';

                        fieldWrap.querySelectorAll('input, select, textarea').forEach((field) => {
                            field.disabled = !isClient;
                        });
                    });
                };

                roleField?.addEventListener('change', syncOnboardingVisibility);
                syncOnboardingVisibility();
            });
        </script>
    @endpush
</x-app-layout>
