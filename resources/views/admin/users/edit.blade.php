<x-app-layout>
    <x-slot name="title">Edit User</x-slot>
    <x-slot name="pageTitle">Edit User: {{ $user->name }}</x-slot>
    <x-slot name="pageSubtitle">Update user information and role</x-slot>

    @php
        $clientRoleId = optional($roles->first(fn($role) => strtolower($role->name) === 'client'))->id ?? \App\Models\User::ROLE_CLIENT;
        $clientProfile = $user->clientProfile;
    @endphp

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-edit me-2"></i>User Information
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="role_id" class="form-label">Role *</label>
                                <select class="form-control @error('role_id') is-invalid @enderror"
                                        id="role_id" name="role_id" required>
                                    <option value="">Select a role...</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                            {{ ucfirst($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-control @error('status') is-invalid @enderror"
                                        id="status" name="status" required>
                                    <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="suspended" {{ old('status', $user->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 client-only-field">
                                <label for="onboarding_collected" class="form-label">Onboarding Collected? *</label>
                                <select class="form-control @error('onboarding_collected') is-invalid @enderror"
                                        id="onboarding_collected" name="onboarding_collected">
                                    <option value="no" {{ old('onboarding_collected', $clientProfile && !$clientProfile->onboarding_visible ? 'yes' : 'no') === 'no' ? 'selected' : '' }}>No</option>
                                    <option value="yes" {{ old('onboarding_collected', $clientProfile && !$clientProfile->onboarding_visible ? 'yes' : 'no') === 'yes' ? 'selected' : '' }}>Yes</option>
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
                                    value="{{ old('estimated_resume_completion_date', optional($clientProfile?->estimated_resume_completion_date)->format('Y-m-d')) }}"
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
                                    value="{{ old('estimated_cover_letter_completion_date', optional($clientProfile?->estimated_cover_letter_completion_date)->format('Y-m-d')) }}"
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
                                    value="{{ old('estimated_application_start_date', optional($clientProfile?->estimated_application_start_date)->format('Y-m-d')) }}"
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
                                <i class="fas fa-save me-2"></i>Update User
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
                        <i class="fas fa-info-circle me-2"></i>User Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Created:</strong>
                        <p class="text-muted small mb-0">{{ $user->created_at->format('M j, Y g:i A') }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Last Updated:</strong>
                        <p class="text-muted small mb-0">{{ $user->updated_at->format('M j, Y g:i A') }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Email Verified:</strong>
                        <p class="text-muted small mb-0">
                            @if($user->email_verified_at)
                                <span class="badge bg-success">Verified</span>
                            @else
                                <span class="badge bg-warning">Not Verified</span>
                            @endif
                        </p>
                    </div>
                    @if($user->isAgent())
                        <div class="mb-3">
                            <strong>Assigned Clients:</strong>
                            <p class="text-muted small mb-0">{{ $user->getActiveClientsAttribute()->count() }} clients</p>
                        </div>
                    @endif
                    @if($user->isClient())
                        <div class="mb-3">
                            <strong>Assigned Agents:</strong>
                            <p class="text-muted small mb-0">{{ $user->getActiveAgentsAttribute()->count() }} agents</p>
                        </div>
                    @endif
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

                const syncClientFieldsVisibility = () => {
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

                roleField?.addEventListener('change', syncClientFieldsVisibility);
                syncClientFieldsVisibility();
            });
        </script>
    @endpush
</x-app-layout>
