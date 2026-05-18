<x-app-layout>
    <x-slot name="title">Change Admin Pass Key</x-slot>
    <x-slot name="pageTitle">Change Admin Pass Key</x-slot>
    <x-slot name="pageSubtitle">Update the admin pass key for temporary admin access</x-slot>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-key me-2"></i>Change Admin Pass Key
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Important:</strong> Changing the pass key will invalidate all current temporary admin sessions. Users will need to re-enter the new pass key to access admin features.
                    </div>

                    <form method="POST" action="{{ route('admin.passkey.update') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="current_pass_key" class="form-label">
                                <i class="fas fa-lock me-2"></i>Current Pass Key
                            </label>
                            <input type="password"
                                   class="form-control @error('current_pass_key') is-invalid @enderror"
                                   id="current_pass_key"
                                   name="current_pass_key"
                                   placeholder="Enter current admin pass key"
                                   required
                                   autofocus>
                            @error('current_pass_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="new_pass_key" class="form-label">
                                <i class="fas fa-key me-2"></i>New Pass Key
                            </label>
                            <input type="password"
                                   class="form-control @error('new_pass_key') is-invalid @enderror"
                                   id="new_pass_key"
                                   name="new_pass_key"
                                   placeholder="Enter new admin pass key (minimum 6 characters)"
                                   required>
                            @error('new_pass_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Minimum 6 characters required</div>
                        </div>

                        <div class="mb-4">
                            <label for="new_pass_key_confirmation" class="form-label">
                                <i class="fas fa-check-circle me-2"></i>Confirm New Pass Key
                            </label>
                            <input type="password"
                                   class="form-control @error('new_pass_key_confirmation') is-invalid @enderror"
                                   id="new_pass_key_confirmation"
                                   name="new_pass_key_confirmation"
                                   placeholder="Confirm new admin pass key"
                                   required>
                            @error('new_pass_key_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-border-black">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-2"></i>Update Pass Key
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Guidelines -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-shield-alt me-2"></i>Security Guidelines
                    </h6>
                    <ul class="list-unstyled mb-0">
                        <li><i class="fas fa-check text-success me-2"></i>Use a strong, unique pass key</li>
                        <li><i class="fas fa-check text-success me-2"></i>Minimum 6 characters recommended</li>
                        <li><i class="fas fa-check text-success me-2"></i>Avoid common words or patterns</li>
                        <li><i class="fas fa-check text-success me-2"></i>Consider using a mix of letters, numbers, and symbols</li>
                        <li><i class="fas fa-check text-success me-2"></i>Keep the pass key secure and don't share it unnecessarily</li>
                    </ul>
                </div>
            </div>

            <!-- Current Pass Key Info -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-info-circle me-2"></i>Current Pass Key Information
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Current Pass Key:</strong>
                            <span class="text-muted">Hidden for security</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Last Updated:</strong>
                            <span class="text-muted">{{ now()->format('M j, Y g:i A') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            border-radius: 12px 12px 0 0 !important;
        }

        .btn {
            border-radius: 8px;
            font-weight: 500;
        }

        .alert {
            border-radius: 8px;
            border: none;
        }

        .form-control:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.25);
        }
    </style>
</x-app-layout>
