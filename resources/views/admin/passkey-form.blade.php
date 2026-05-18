<x-app-layout>
    <x-slot name="title">Admin Pass Key</x-slot>
    <x-slot name="pageTitle">Admin Access</x-slot>
    <x-slot name="pageSubtitle">Enter admin pass key to access admin features</x-slot>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-key me-2"></i>Admin Pass Key Verification
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Admin Access Required</strong>
                        <p class="mb-0 mt-2">To access admin features, please enter the admin pass key. This access will be valid for 24 hours.</p>
                    </div>

                    <form method="POST" action="{{ route('admin.passkey.verify') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="pass_key" class="form-label">
                                <i class="fas fa-lock me-2"></i>Admin Pass Key
                            </label>
                            <input type="password"
                                   class="form-control @error('pass_key') is-invalid @enderror"
                                   id="pass_key"
                                   name="pass_key"
                                   placeholder="Enter admin pass key"
                                   required
                                   autofocus>
                            @error('pass_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-black">
                                <i class="fas fa-unlock me-2"></i>Verify Pass Key
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-border-black">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
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
                        <li><i class="fas fa-check text-success me-2"></i>Admin access is temporary (24 hours)</li>
                        <li><i class="fas fa-check text-success me-2"></i>All admin actions are logged</li>
                        <li><i class="fas fa-check text-success me-2"></i>Access can be revoked at any time</li>
                        <li><i class="fas fa-check text-success me-2"></i>Pass key is case-sensitive</li>
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
    </style>
</x-app-layout>
