<x-app-layout>
    <x-slot name="title">Mailchimp Settings</x-slot>
    <x-slot name="pageTitle">Mailchimp Integration Settings</x-slot>
    <x-slot name="pageSubtitle">Manage Mailchimp API configuration and email settings</x-slot>

    <div class="container-fluid">
        <!-- Status Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-{{ $mailchimpSettings && $mailchimpSettings->is_active ? 'success' : 'danger' }} text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon me-3">
                                <i class="fas fa-{{ $mailchimpSettings && $mailchimpSettings->is_active ? 'check' : 'times' }}"></i>
                            </div>
                            <div>
                                <div class="stats-number">{{ $mailchimpSettings && $mailchimpSettings->is_active ? 'Active' : 'Inactive' }}</div>
                                <div class="stats-label">Mailchimp Status</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-{{ $mailchimpSettings && $mailchimpSettings->auto_subscribe ? 'info' : 'secondary' }} text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon me-3">
                                <i class="fas fa-{{ $mailchimpSettings && $mailchimpSettings->auto_subscribe ? 'user-plus' : 'user-minus' }}"></i>
                            </div>
                            <div>
                                <div class="stats-number">{{ $mailchimpSettings && $mailchimpSettings->auto_subscribe ? 'Enabled' : 'Disabled' }}</div>
                                <div class="stats-label">Auto Subscribe</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-{{ $mailchimpSettings && $mailchimpSettings->send_welcome_email ? 'warning' : 'secondary' }} text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon me-3">
                                <i class="fas fa-{{ $mailchimpSettings && $mailchimpSettings->send_welcome_email ? 'envelope' : 'envelope-open' }}"></i>
                            </div>
                            <div>
                                <div class="stats-number">{{ $mailchimpSettings && $mailchimpSettings->send_welcome_email ? 'Enabled' : 'Disabled' }}</div>
                                <div class="stats-label">Welcome Emails</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon me-3">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <div class="stats-number">{{ $mailchimpSettings && $mailchimpSettings->last_sync_at ? $mailchimpSettings->last_sync_at->diffForHumans() : 'Never' }}</div>
                                <div class="stats-label">Last Sync</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Settings -->
        @if($mailchimpSettings)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-black text-white">
                        <h5 class="card-title mb-0">
                            <i class="fab fa-mailchimp me-2"></i>Current Mailchimp Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Connection Details</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>API Key:</strong></td>
                                        <td>{{ $mailchimpSettings->api_key ? 'Configured' : 'Not Set' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Server Prefix:</strong></td>
                                        <td>{{ $mailchimpSettings->server_prefix }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>List ID:</strong></td>
                                        <td>{{ $mailchimpSettings->list_id ?: 'Not Set' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>From Name:</strong></td>
                                        <td>{{ $mailchimpSettings->from_name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>From Email:</strong></td>
                                        <td>{{ $mailchimpSettings->from_email ?: 'Not Set' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            <span class="badge bg-{{ $mailchimpSettings->is_active ? 'success' : 'danger' }}">
                                                {{ $mailchimpSettings->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Email Settings</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Auto Subscribe:</strong></td>
                                        <td>
                                            <span class="badge bg-{{ $mailchimpSettings->auto_subscribe ? 'success' : 'secondary' }}">
                                                {{ $mailchimpSettings->auto_subscribe ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Welcome Emails:</strong></td>
                                        <td>
                                            <span class="badge bg-{{ $mailchimpSettings->send_welcome_email ? 'success' : 'secondary' }}">
                                                {{ $mailchimpSettings->send_welcome_email ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Reply To:</strong></td>
                                        <td>{{ $mailchimpSettings->reply_to ?: 'Not Set' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Merge Fields:</strong></td>
                                        <td>{{ $mailchimpSettings->merge_fields ? count($mailchimpSettings->merge_fields) . ' configured' : 'None' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tags:</strong></td>
                                        <td>{{ $mailchimpSettings->tags ? count($mailchimpSettings->tags) . ' configured' : 'None' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        @if($mailchimpSettings->sync_status)
                        <div class="mt-3">
                            <h6>Sync Status</h6>
                            <div class="alert alert-{{ str_contains($mailchimpSettings->sync_status, 'successful') ? 'success' : 'danger' }}">
                                {{ $mailchimpSettings->sync_status }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Management Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-black text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tools me-2"></i>Mailchimp Management
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('admin.mailchimp.create') }}" class="btn btn-black w-100">
                                    <i class="fas fa-edit me-2"></i>Configure Settings
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button type="button" class="btn btn-info w-100" onclick="testConnection()">
                                    <i class="fas fa-plug me-2"></i>Test Connection
                                </button>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button type="button" class="btn btn-warning w-100" onclick="sendTestEmail()">
                                    <i class="fas fa-envelope me-2"></i>Send Test Email
                                </button>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button type="button" class="btn btn-danger w-100" onclick="resetSettings()">
                                    <i class="fas fa-undo me-2"></i>Reset Settings
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function testConnection() {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Testing...';
            btn.disabled = true;

            fetch('{{ route("admin.mailchimp.test-connection") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message + (data.account_info ? '\nAccount: ' + data.account_info.account_name : ''));
                    location.reload();
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Connection test failed. Please try again.');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }

        function sendTestEmail() {
            const testEmail = prompt('Enter test email address:');
            if (!testEmail) return;

            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
            btn.disabled = true;

            fetch('{{ route("admin.mailchimp.send-test-email") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ test_email: testEmail })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Failed to send test email. Please try again.');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }

        function resetSettings() {
            if (confirm('Are you sure you want to reset all Mailchimp settings to defaults? This action cannot be undone.')) {
                window.location.href = '{{ route("admin.mailchimp.reset") }}';
            }
        }
    </script>
    @endpush
</x-app-layout>
