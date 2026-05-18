<x-app-layout>
    <x-slot name="title">Login as User</x-slot>
    <x-slot name="pageTitle">Admin Login as User</x-slot>
    <x-slot name="pageSubtitle">Login as any user using admin pass key</x-slot>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-secret me-2"></i>Login as User
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Admin Only Feature</strong>
                        <p class="mb-0 mt-2">This feature allows you to login as any user in the system. Use with caution and only for legitimate administrative purposes.</p>
                    </div>

                    <form method="POST" action="{{ route('admin.login-as-user') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2"></i>User Email
                            </label>
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="email"
                                   name="email"
                                   placeholder="Enter user's email address"
                                   value="{{ old('email') }}"
                                   required
                                   autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="admin_pass_key" class="form-label">
                                <i class="fas fa-key me-2"></i>Admin Pass Key
                            </label>
                            <input type="password"
                                   class="form-control @error('admin_pass_key') is-invalid @enderror"
                                   id="admin_pass_key"
                                   name="admin_pass_key"
                                   placeholder="Enter admin pass key"
                                   required>
                            @error('admin_pass_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-border-black">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-sign-in-alt me-2"></i>Login as User
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-shield-alt me-2"></i>Security Notice
                    </h6>
                    <ul class="list-unstyled mb-0">
                        <li><i class="fas fa-check text-success me-2"></i>All login actions are logged</li>
                        <li><i class="fas fa-check text-success me-2"></i>Use only for legitimate administrative purposes</li>
                        <li><i class="fas fa-check text-success me-2"></i>You will be logged in as the selected user</li>
                        <li><i class="fas fa-check text-success me-2"></i>Original admin session will be replaced</li>
                    </ul>
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
            border-color: #dc2626;
            box-shadow: 0 0 0 0.2rem rgba(220, 38, 38, 0.25);
        }
    </style>
</x-app-layout>
