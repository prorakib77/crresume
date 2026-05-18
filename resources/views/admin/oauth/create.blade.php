<x-app-layout>
    <x-slot name="title">Configure OAuth Settings</x-slot>
    <x-slot name="pageTitle">Google OAuth Configuration</x-slot>
    <x-slot name="pageSubtitle">Configure Google Meet API credentials and automation settings</x-slot>

    <div class="container-fluid">
        <form action="{{ route('admin.oauth.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Credentials Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-black text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-key me-2"></i>Google Service Account Credentials
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="credentials_file" class="form-label">Upload Credentials JSON File</label>
                                        <input type="file" class="form-control" id="credentials_file" name="credentials_file" accept=".json">
                                        <div class="form-text">Upload your Google Service Account JSON file</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="credentials_json" class="form-label">Or Paste JSON Content</label>
                                        <textarea class="form-control" id="credentials_json" name="credentials_json" rows="8"
                                                  placeholder="Paste your service account JSON credentials here...">{{ $oauthSettings->credentials_json ?? '' }}</textarea>
                                        <div class="form-text">Paste the contents of your service account JSON file</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Basic Settings -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-black text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-cog me-2"></i>Basic Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="admin_email" class="form-label">Admin Email Address</label>
                                        <input type="email" class="form-control" id="admin_email" name="admin_email"
                                               value="{{ $oauthSettings->admin_email ?? '' }}" required>
                                        <div class="form-text">Email address for meeting host</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="calendar_id" class="form-label">Calendar ID</label>
                                        <input type="text" class="form-control" id="calendar_id" name="calendar_id"
                                               value="{{ $oauthSettings->calendar_id ?? 'primary' }}" required>
                                        <div class="form-text">Google Calendar ID (use 'primary' for main calendar)</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="timezone" class="form-label">Timezone</label>
                                        <select class="form-control" id="timezone" name="timezone" required>
                                            <option value="Asia/Dhaka" {{ ($oauthSettings->timezone ?? '') == 'Asia/Dhaka' ? 'selected' : '' }}>Asia/Dhaka</option>
                                            <option value="UTC" {{ ($oauthSettings->timezone ?? '') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                            <option value="America/New_York" {{ ($oauthSettings->timezone ?? '') == 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                                            <option value="Europe/London" {{ ($oauthSettings->timezone ?? '') == 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="meet_room_link" class="form-label">Default Meet Room Link</label>
                                        <input type="url" class="form-control" id="meet_room_link" name="meet_room_link"
                                               value="{{ $oauthSettings->meet_room_link ?? '' }}">
                                        <div class="form-text">Optional: Default Google Meet room link</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Meeting Settings -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-black text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-video me-2"></i>Meeting Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="meeting_start_time" class="form-label">Start Time</label>
                                        <input type="time" class="form-control" id="meeting_start_time" name="meeting_start_time"
                                               value="{{ $oauthSettings->meeting_start_time ?? '09:00' }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="meeting_end_time" class="form-label">End Time</label>
                                        <input type="time" class="form-control" id="meeting_end_time" name="meeting_end_time"
                                               value="{{ $oauthSettings->meeting_end_time ?? '17:00' }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="meeting_duration_minutes" class="form-label">Duration (minutes)</label>
                                        <input type="number" class="form-control" id="meeting_duration_minutes" name="meeting_duration_minutes"
                                               value="{{ $oauthSettings->meeting_duration_minutes ?? 60 }}" min="15" max="480" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="meeting_description" class="form-label">Meeting Description</label>
                                        <textarea class="form-control" id="meeting_description" name="meeting_description" rows="3">{{ $oauthSettings->meeting_description ?? '' }}</textarea>
                                        <div class="form-text">Default description for generated meetings</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Meeting Generation Settings -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-black text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-calendar-plus me-2"></i>Meeting Generation Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="meeting_attendees" class="form-label">Default Attendees (Email addresses, one per line)</label>
                                        <textarea class="form-control" id="meeting_attendees" name="meeting_attendees" rows="4"
                                                  placeholder="agent1@example.com&#10;agent2@example.com&#10;admin@example.com">{{ $oauthSettings && $oauthSettings->meeting_attendees ? implode("\n", $oauthSettings->meeting_attendees) : '' }}</textarea>
                                        <div class="form-text">Email addresses to invite to meetings (optional)</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="meeting_settings" class="form-label">Advanced Meeting Settings (JSON)</label>
                                        <textarea class="form-control" id="meeting_settings" name="meeting_settings" rows="4"
                                                  placeholder='{"conference_solution": "google_meet", "send_updates": "all", "reminders": {"use_default": true}}'>{{ $oauthSettings && $oauthSettings->meeting_settings ? json_encode($oauthSettings->meeting_settings, JSON_PRETTY_PRINT) : '' }}</textarea>
                                        <div class="form-text">Advanced Google Calendar event settings (optional)</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle me-2"></i>Meeting Generation Process</h6>
                                        <p class="mb-2">The system uses the following process to generate meetings:</p>
                                        <ol class="mb-0">
                                            <li><strong>OAuth Authentication:</strong> Uses Google Service Account credentials</li>
                                            <li><strong>Calendar API:</strong> Creates events in Google Calendar</li>
                                            <li><strong>Meet Integration:</strong> Generates Google Meet links automatically</li>
                                            <li><strong>Notifications:</strong> Sends email invitations to attendees</li>
                                            <li><strong>Database Storage:</strong> Saves meeting details for tracking</li>
                                        </ol>
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
                                <div class="col-md-6">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                               {{ ($oauthSettings->is_active ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <strong>Enable OAuth Integration</strong>
                                        </label>
                                        <div class="form-text">Enable Google OAuth functionality</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="auto_generate_meetings" name="auto_generate_meetings" value="1"
                                               {{ ($oauthSettings->auto_generate_meetings ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="auto_generate_meetings">
                                            <strong>Auto Generate Meetings</strong>
                                        </label>
                                        <div class="form-text">Automatically generate daily meetings</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="send_notifications" name="send_notifications" value="1"
                                               {{ ($oauthSettings->send_notifications ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="send_notifications">
                                            <strong>Send Notifications</strong>
                                        </label>
                                        <div class="form-text">Send meeting notifications to attendees</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="create_calendar_events" name="create_calendar_events" value="1"
                                               {{ ($oauthSettings->create_calendar_events ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="create_calendar_events">
                                            <strong>Create Calendar Events</strong>
                                        </label>
                                        <div class="form-text">Create events in Google Calendar</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="auto_join_enabled" name="auto_join_enabled" value="1"
                                               {{ ($oauthSettings->auto_join_enabled ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="auto_join_enabled">
                                            <strong>Auto Join Enabled</strong>
                                        </label>
                                        <div class="form-text">Enable automatic joining of meetings</div>
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
                                <a href="{{ route('admin.oauth.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to OAuth Settings
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

            fetch('{{ route("admin.oauth.test-connection") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message + (data.calendar_title ? '\nCalendar: ' + data.calendar_title : ''));
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

        // Handle file upload
        document.getElementById('credentials_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type === 'application/json') {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('credentials_json').value = e.target.result;
                };
                reader.readAsText(file);
            }
        });
    </script>
    @endpush
</x-app-layout>
