<x-app-layout>
    <x-slot name="title">System Settings</x-slot>
    <x-slot name="pageTitle">System Settings</x-slot>
    <x-slot name="pageSubtitle">Manage system configuration and integrations</x-slot>

    <div class="row">
        <!-- Mailchimp Integration -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fab fa-mailchimp me-2"></i>Mailchimp Integration
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Connection Status:</span>
                        <span class="badge {{ $mailchimpStatus ? 'bg-success' : 'bg-danger' }}">
                            {{ $mailchimpStatus ? 'Connected' : 'Disconnected' }}
                        </span>
                    </div>

                    @if($mailchimpInfo)
                        <div class="mb-3">
                            <strong>Account Name:</strong> {{ $mailchimpInfo['account_name'] ?? 'N/A' }}<br>
                            <strong>Email:</strong> {{ $mailchimpInfo['email'] ?? 'N/A' }}<br>
                            <strong>Server:</strong> {{ config('services.mailchimp.server_prefix') }}
                        </div>
                    @endif

                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.mailchimp.index') }}" class="btn btn-primary">
                            <i class="fas fa-cog me-2"></i>Configure Mailchimp
                        </a>
                        <button class="btn btn-info" onclick="testMailchimp()">
                            <i class="fas fa-sync me-2"></i>Test Connection
                        </button>
                        <small class="text-muted">
                            API Key: {{ $mailchimpSettings && $mailchimpSettings->api_key ? 'Configured' : 'Not Set' }}<br>
                            List ID: {{ $mailchimpSettings && $mailchimpSettings->list_id ? 'Configured' : 'Not Set' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Maintenance -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tools me-2"></i>System Maintenance
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Perform system maintenance tasks to keep the application running smoothly.</p>

                    <div class="d-grid gap-2">
                        <form method="POST" action="{{ route('admin.settings.clear-cache') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="fas fa-broom me-2"></i>Clear Cache
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.settings.optimize') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-rocket me-2"></i>Optimize Application
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>System Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Laravel Version:</strong></td>
                                    <td>{{ app()->version() }}</td>
                                </tr>
                                <tr>
                                    <td><strong>PHP Version:</strong></td>
                                    <td>{{ PHP_VERSION }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Environment:</strong></td>
                                    <td>
                                        <span class="badge {{ app()->environment('production') ? 'bg-success' : 'bg-warning' }}">
                                            {{ app()->environment() }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Debug Mode:</strong></td>
                                    <td>
                                        <span class="badge {{ config('app.debug') ? 'bg-danger' : 'bg-success' }}">
                                            {{ config('app.debug') ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Database:</strong></td>
                                    <td>{{ config('database.default') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Cache Driver:</strong></td>
                                    <td>{{ config('cache.default') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Queue Driver:</strong></td>
                                    <td>{{ config('queue.default') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Mail Driver:</strong></td>
                                    <td>{{ config('mail.default') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function testMailchimp() {
            const button = event.target;
            const originalText = button.innerHTML;

            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Testing...';
            button.disabled = true;

            fetch('{{ route("admin.settings.test-mailchimp") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    // Reload page to update status
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                showAlert('danger', 'An error occurred while testing the connection.');
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.querySelector('.content-area').insertBefore(alertDiv, document.querySelector('.content-area').firstChild);

            // Auto-hide after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    </script>
    @endpush
</x-app-layout>
