<x-app-layout>
    <x-slot name="title">Configure Mailchimp Settings</x-slot>
    <x-slot name="pageTitle">Mailchimp Configuration</x-slot>
    <x-slot name="pageSubtitle">Configure Mailchimp API credentials and email settings</x-slot>

    <div class="container-fluid">
        <form action="{{ route('admin.mailchimp.store') }}" method="POST">
            @csrf

            <!-- API Credentials Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-black text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-key me-2"></i>Mailchimp API Credentials
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="api_key" class="form-label">API Key</label>
                                        <input type="text" class="form-control" id="api_key" name="api_key"
                                               value="{{ $mailchimpSettings->api_key ?? '' }}" required>
                                        <div class="form-text">Your Mailchimp API key (starts with letters and numbers)</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="server_prefix" class="form-label">Server Prefix</label>
                                        <select class="form-control" id="server_prefix" name="server_prefix" required>
                                            <option value="us1" {{ ($mailchimpSettings->server_prefix ?? '') == 'us1' ? 'selected' : '' }}>US1</option>
                                            <option value="us2" {{ ($mailchimpSettings->server_prefix ?? '') == 'us2' ? 'selected' : '' }}>US2</option>
                                            <option value="us3" {{ ($mailchimpSettings->server_prefix ?? '') == 'us3' ? 'selected' : '' }}>US3</option>
                                            <option value="us4" {{ ($mailchimpSettings->server_prefix ?? '') == 'us4' ? 'selected' : '' }}>US4</option>
                                            <option value="us5" {{ ($mailchimpSettings->server_prefix ?? '') == 'us5' ? 'selected' : '' }}>US5</option>
                                            <option value="us6" {{ ($mailchimpSettings->server_prefix ?? '') == 'us6' ? 'selected' : '' }}>US6</option>
                                            <option value="us7" {{ ($mailchimpSettings->server_prefix ?? '') == 'us7' ? 'selected' : '' }}>US7</option>
                                            <option value="us8" {{ ($mailchimpSettings->server_prefix ?? '') == 'us8' ? 'selected' : '' }}>US8</option>
                                            <option value="us9" {{ ($mailchimpSettings->server_prefix ?? '') == 'us9' ? 'selected' : '' }}>US9</option>
                                            <option value="us10" {{ ($mailchimpSettings->server_prefix ?? '') == 'us10' ? 'selected' : '' }}>US10</option>
                                            <option value="us11" {{ ($mailchimpSettings->server_prefix ?? '') == 'us11' ? 'selected' : '' }}>US11</option>
                                            <option value="us12" {{ ($mailchimpSettings->server_prefix ?? '') == 'us12' ? 'selected' : '' }}>US12</option>
                                            <option value="us13" {{ ($mailchimpSettings->server_prefix ?? '') == 'us13' ? 'selected' : '' }}>US13</option>
                                            <option value="us14" {{ ($mailchimpSettings->server_prefix ?? '') == 'us14' ? 'selected' : '' }}>US14</option>
                                            <option value="us15" {{ ($mailchimpSettings->server_prefix ?? '') == 'us15' ? 'selected' : '' }}>US15</option>
                                            <option value="us16" {{ ($mailchimpSettings->server_prefix ?? '') == 'us16' ? 'selected' : '' }}>US16</option>
                                            <option value="us17" {{ ($mailchimpSettings->server_prefix ?? '') == 'us17' ? 'selected' : '' }}>US17</option>
                                            <option value="us18" {{ ($mailchimpSettings->server_prefix ?? 'us18') == 'us18' ? 'selected' : '' }}>US18</option>
                                            <option value="us19" {{ ($mailchimpSettings->server_prefix ?? '') == 'us19' ? 'selected' : '' }}>US19</option>
                                            <option value="us20" {{ ($mailchimpSettings->server_prefix ?? '') == 'us20' ? 'selected' : '' }}>US20</option>
                                            <option value="us21" {{ ($mailchimpSettings->server_prefix ?? '') == 'us21' ? 'selected' : '' }}>US21</option>
                                        </select>
                                        <div class="form-text">Your Mailchimp server prefix (usually us18)</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="list_id" class="form-label">Audience List ID</label>
                                        <input type="text" class="form-control" id="list_id" name="list_id"
                                               value="{{ $mailchimpSettings->list_id ?? '' }}" required>
                                        <div class="form-text">Your Mailchimp audience list ID</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-info" onclick="getLists()">
                                            <i class="fas fa-list me-2"></i>Get Lists
                                        </button>
                                        <div class="form-text">Click to fetch your Mailchimp lists</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Settings -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-black text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-envelope me-2"></i>Email Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="from_name" class="form-label">From Name</label>
                                        <input type="text" class="form-control" id="from_name" name="from_name"
                                               value="{{ $mailchimpSettings->from_name ?? 'W-Automation' }}" required>
                                        <div class="form-text">Name that appears in emails</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="from_email" class="form-label">From Email</label>
                                        <input type="email" class="form-control" id="from_email" name="from_email"
                                               value="{{ $mailchimpSettings->from_email ?? '' }}" required>
                                        <div class="form-text">Email address for sending emails</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="reply_to" class="form-label">Reply To Email</label>
                                        <input type="email" class="form-control" id="reply_to" name="reply_to"
                                               value="{{ $mailchimpSettings->reply_to ?? '' }}">
                                        <div class="form-text">Optional: Reply-to email address</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Automation Settings -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-black text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-robot me-2"></i>Automation Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                               {{ ($mailchimpSettings->is_active ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <strong>Enable Mailchimp Integration</strong>
                                        </label>
                                        <div class="form-text">Enable Mailchimp functionality</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="auto_subscribe" name="auto_subscribe" value="1"
                                               {{ ($mailchimpSettings->auto_subscribe ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="auto_subscribe">
                                            <strong>Auto Subscribe Clients</strong>
                                        </label>
                                        <div class="form-text">Automatically subscribe new clients</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="send_welcome_email" name="send_welcome_email" value="1"
                                               {{ ($mailchimpSettings->send_welcome_email ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="send_welcome_email">
                                            <strong>Send Welcome Emails</strong>
                                        </label>
                                        <div class="form-text">Send welcome emails to new subscribers</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Templates -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-black text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-file-alt me-2"></i>Email Templates
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="welcome_email_template" class="form-label">Welcome Email Template</label>
                                        <textarea class="form-control" id="welcome_email_template" name="welcome_email_template" rows="4">{{ $mailchimpSettings->welcome_email_template ?? '' }}</textarea>
                                        <div class="form-text">Template for welcome emails (HTML allowed)</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="work_update_template" class="form-label">Work Update Email Template</label>
                                        <textarea class="form-control" id="work_update_template" name="work_update_template" rows="4">{{ $mailchimpSettings->work_update_template ?? '' }}</textarea>
                                        <div class="form-text">Template for work update emails (HTML allowed)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Settings -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-black text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-cogs me-2"></i>Advanced Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="merge_fields" class="form-label">Merge Fields (JSON)</label>
                                        <textarea class="form-control" id="merge_fields" name="merge_fields" rows="3"
                                                  placeholder='{"FNAME": "First Name", "LNAME": "Last Name", "COMPANY": "Company"}'>{{ $mailchimpSettings && $mailchimpSettings->merge_fields ? json_encode($mailchimpSettings->merge_fields, JSON_PRETTY_PRINT) : '' }}</textarea>
                                        <div class="form-text">Custom merge fields for personalization</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tags" class="form-label">Tags (JSON)</label>
                                        <textarea class="form-control" id="tags" name="tags" rows="3"
                                                  placeholder='["client", "work-updates", "automation"]'>{{ $mailchimpSettings && $mailchimpSettings->tags ? json_encode($mailchimpSettings->tags, JSON_PRETTY_PRINT) : '' }}</textarea>
                                        <div class="form-text">Tags to apply to subscribers</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('admin.mailchimp.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Mailchimp Settings
                                </a>
                                <div>
                                    <button type="button" class="btn btn-info me-2" onclick="testConnection()">
                                        <i class="fas fa-plug me-2"></i>Test Connection
                                    </button>
                                    <button type="submit" class="btn btn-black">
                                        <i class="fas fa-save me-2"></i>Save Settings
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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

        function getLists() {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
            btn.disabled = true;

            fetch('{{ route("admin.mailchimp.get-lists") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let listOptions = 'Select a list:\n\n';
                    data.lists.forEach((list, index) => {
                        listOptions += `${index + 1}. ${list.name} (ID: ${list.id})\n`;
                    });

                    const selection = prompt(listOptions + '\nEnter the number of the list you want to use:');
                    if (selection && data.lists[selection - 1]) {
                        document.getElementById('list_id').value = data.lists[selection - 1].id;
                        alert('List ID set to: ' + data.lists[selection - 1].id);
                    }
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Failed to get lists. Please try again.');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }
    </script>
    @endpush
</x-app-layout>
