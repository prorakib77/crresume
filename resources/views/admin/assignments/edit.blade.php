<x-app-layout>
    <x-slot name="title">Edit Assignment</x-slot>
    <x-slot name="pageTitle">Edit Assignment</x-slot>
    <x-slot name="pageSubtitle">Update the pairing, files, and handoff details.</x-slot>

    @php
        $existingCoverLetters = is_array($assignment->cover_letters)
            ? $assignment->cover_letters
            : (json_decode($assignment->cover_letters, true) ?? []);

        $daysRemaining = $assignment->service_end_date
            ? rounded_time_value(now()->diffInDays($assignment->service_end_date, false))
            : null;
    @endphp

    <div class="assignment-create-page">
        <div class="mx-auto max-w-6xl space-y-6">
            <section class="assignment-summary-strip">
                <article class="assignment-summary-card">
                    <span class="assignment-summary-label">Current Agent</span>
                    <div class="assignment-person-row">
                        <span class="assignment-person-avatar">{{ strtoupper(substr($assignment->agent->name, 0, 1)) }}</span>
                        <div>
                            <div class="assignment-person-name">{{ $assignment->agent->name }}</div>
                            <div class="assignment-person-meta">{{ $assignment->agent->email }}</div>
                        </div>
                    </div>
                </article>

                <article class="assignment-summary-card">
                    <span class="assignment-summary-label">Current Client</span>
                    <div class="assignment-person-row">
                        <span class="assignment-person-avatar is-client">{{ strtoupper(substr($assignment->client->name, 0, 1)) }}</span>
                        <div>
                            <div class="assignment-person-name">{{ $assignment->client->name }}</div>
                            <div class="assignment-person-meta">{{ $assignment->client->email }}</div>
                        </div>
                    </div>
                </article>

                <article class="assignment-summary-card">
                    <span class="assignment-summary-label">Status</span>
                    <div class="assignment-summary-value">
                        <span class="assignment-status-pill {{ $assignment->is_active ? 'is-active' : 'is-inactive' }}">
                            {{ $assignment->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="assignment-person-meta">
                        Assigned {{ $assignment->assigned_date ? $assignment->assigned_date->format('M j, Y') : 'N/A' }}
                    </div>
                </article>

                <article class="assignment-summary-card">
                    <span class="assignment-summary-label">Service Window</span>
                    <div class="assignment-summary-value">
                        @if($assignment->service_end_date)
                            {{ $assignment->service_end_date->format('M j, Y') }}
                        @else
                            Ongoing
                        @endif
                    </div>
                    <div class="assignment-person-meta">
                        @if($assignment->service_end_date)
                            @if($daysRemaining > 0)
                                {{ $daysRemaining }} days remaining
                            @elseif($daysRemaining === 0)
                                Expires today
                            @else
                                Expired {{ abs($daysRemaining) }} days ago
                            @endif
                        @else
                            No end date set
                        @endif
                    </div>
                </article>
            </section>

            <form action="{{ route('admin.assignments.update', $assignment) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <section class="assignment-panel">
                    <div class="assignment-section-header">
                        <div>
                            <span class="assignment-section-eyebrow">Core Match</span>
                            <h3 class="assignment-section-title">Assignment Identity</h3>
                        </div>
                        <span class="assignment-section-chip">Editable</span>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="assignment-field-shell">
                            <x-searchable-select
                                name="agent_id"
                                label="Select Agent"
                                placeholder="Choose an agent..."
                                :options="$agents->map(fn($agent) => ['value' => $agent->id, 'text' => $agent->name . ' (' . $agent->email . ')'])->toArray()"
                                :value="old('agent_id', $assignment->agent_id)"
                                search-url="{{ route('admin.assignments.search-agents') }}"
                                required="true"
                            />
                            <p class="assignment-field-note">Open the list to view every agent, or search by name or email.</p>
                        </div>

                        <div class="assignment-field-shell">
                            <x-searchable-select
                                name="client_id"
                                label="Select Client"
                                placeholder="Choose a client..."
                                :options="$clients->map(fn($client) => ['value' => $client->id, 'text' => $client->name . ' (' . $client->email . ')'])->toArray()"
                                :value="old('client_id', $assignment->client_id)"
                                search-url="{{ route('admin.assignments.search-clients') }}"
                                required="true"
                            />
                            <p class="assignment-field-note">The full client list is available on open, and search works from the same field.</p>
                        </div>

                        <div class="assignment-field-shell">
                            <label for="service_end_date" class="form-label">Service End Date</label>
                            <input
                                type="date"
                                name="service_end_date"
                                id="service_end_date"
                                class="form-control @error('service_end_date') is-invalid @enderror"
                                value="{{ old('service_end_date', $assignment->service_end_date ? $assignment->service_end_date->format('Y-m-d') : '') }}"
                                min="{{ old('service_end_date', $assignment->service_end_date ? $assignment->service_end_date->format('Y-m-d') : date('Y-m-d', strtotime('+1 day'))) }}"
                            >
                            <p class="assignment-field-note">Leave this empty if the assignment should continue without an end date.</p>
                            @error('service_end_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="assignment-field-shell">
                            <label for="minimum_work_updates" class="form-label">Minimum Work Updates <span class="text-danger">*</span></label>
                            <input
                                type="number"
                                class="form-control @error('minimum_work_updates') is-invalid @enderror"
                                id="minimum_work_updates"
                                name="minimum_work_updates"
                                value="{{ old('minimum_work_updates', $assignment->minimum_work_updates ?? \App\Models\AgentClientAssignment::DEFAULT_MINIMUM_WORK_UPDATES) }}"
                                min="1"
                                max="50"
                                step="1"
                                required
                            >
                            <p class="assignment-field-note">Agent must keep this minimum draft count before submitting updates for this client.</p>
                            @error('minimum_work_updates')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="assignment-upload-card">
                            <label class="form-label">Assignment Status</label>
                            <div class="assignment-toggle-row">
                                <label class="assignment-toggle-shell" for="is_active">
                                    <input
                                        class="assignment-toggle-input"
                                        type="checkbox"
                                        name="is_active"
                                        id="is_active"
                                        value="1"
                                        {{ old('is_active', $assignment->is_active) ? 'checked' : '' }}
                                    >
                                    <span class="assignment-toggle-track"></span>
                                    <span class="assignment-toggle-copy">
                                        <span class="assignment-toggle-title">Active Assignment</span>
                                        <span class="assignment-toggle-note">Turn this off to pause the assignment without removing its history.</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="assignment-panel">
                    <div class="assignment-section-header">
                        <div>
                            <span class="assignment-section-eyebrow">Application Scope</span>
                            <h3 class="assignment-section-title">Apply-To Brief</h3>
                        </div>
                        <span class="assignment-section-chip">Rich text</span>
                    </div>

                    <div class="assignment-editor-card">
                        <label for="apply_to" class="form-label">Apply To <span class="text-danger">*</span></label>
                        <p class="assignment-field-note mb-3">Update the role targets, industries, preferred companies, or any constraints the agent should follow.</p>
                        <div id="apply_to_editor" class="assignment-editor assignment-editor-lg"></div>
                        <textarea class="form-control @error('apply_to') is-invalid @enderror" id="apply_to" name="apply_to" style="display: none;">{{ old('apply_to', $assignment->apply_to) }}</textarea>
                        @error('apply_to')
                            <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </section>

                <section class="assignment-panel">
                    <div class="assignment-section-header">
                        <div>
                            <span class="assignment-section-eyebrow">Files & Assets</span>
                            <h3 class="assignment-section-title">Update Assignment Materials</h3>
                        </div>
                        <span class="assignment-section-chip">Files stay preserved</span>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-2">
                        <div class="assignment-upload-card">
                            <label for="resume_file" class="form-label">Resume File</label>
                            <input type="file" class="form-control @error('resume_file') is-invalid @enderror" id="resume_file" name="resume_file" accept=".pdf,.doc,.docx,.csv,.xlsx,.xls">
                            @if($assignment->resume_file)
                                <div class="assignment-file-chip-row">
                                    <button type="button" class="btn btn-border-black btn-sm js-download-file" data-url="{{ storage_public_url($assignment->resume_file) }}" data-filename="{{ basename($assignment->resume_file) }}">
                                        <i class="fas fa-download me-2"></i>Current Resume
                                    </button>
                                </div>
                            @endif
                            <p class="assignment-field-note">Upload a new file only if the current resume needs to be replaced.</p>
                            @error('resume_file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="assignment-upload-card">
                            <label for="onboarding_form_file" class="form-label">Onboarding Form</label>
                            <input type="file" class="form-control @error('onboarding_form_file') is-invalid @enderror" id="onboarding_form_file" name="onboarding_form_file" accept=".pdf,.doc,.docx,.csv,.xlsx,.xls">
                            @if($assignment->onboarding_form_file)
                                <div class="assignment-file-chip-row">
                                    <a href="{{ storage_public_url($assignment->onboarding_form_file) }}" target="_blank" class="btn btn-border-black btn-sm">
                                        <i class="fas fa-download me-2"></i>Current Onboarding Form
                                    </a>
                                </div>
                            @endif
                            <p class="assignment-field-note">Keep the existing file or replace it with a refreshed onboarding document.</p>
                            @error('onboarding_form_file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="assignment-upload-card mt-4">
                        <div class="assignment-section-header mb-4">
                            <div>
                                <span class="assignment-section-eyebrow">Supporting Files</span>
                                <h3 class="assignment-section-title text-[1.05rem]">Cover Letters</h3>
                            </div>
                            <button type="button" class="btn btn-border-black btn-sm" onclick="addCoverLetter()">
                                <i class="fas fa-plus me-2"></i>Add File
                            </button>
                        </div>

                        @if(!empty($existingCoverLetters))
                            <div class="assignment-existing-files">
                                <div class="assignment-existing-files-label">Current files</div>
                                <div class="assignment-file-chip-row">
                                    @foreach($existingCoverLetters as $index => $file)
                                        <button type="button" class="btn btn-border-black btn-sm js-download-file" data-url="{{ storage_public_url($file) }}" data-filename="{{ basename($file) }}">
                                            <i class="fas fa-download me-2"></i>Cover Letter {{ $index + 1 }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div id="cover-letters-container" class="space-y-3">
                            <div class="cover-letter-item assignment-file-row">
                                <div class="input-group">
                                    <input type="file" class="form-control @error('cover_letters.*') is-invalid @enderror" name="cover_letters[]" accept=".pdf,.doc,.docx,.csv,.xlsx,.xls">
                                    <button type="button" class="btn btn-outline-danger" onclick="removeCoverLetter(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <p class="assignment-field-note mt-3">Existing cover letters remain saved. New uploads are added without removing the current ones.</p>
                        @error('cover_letters.*')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </section>

                <section class="assignment-panel">
                    <div class="assignment-section-header">
                        <div>
                            <span class="assignment-section-eyebrow">Agent Guidance</span>
                            <h3 class="assignment-section-title">Internal Note</h3>
                        </div>
                        <span class="assignment-section-chip">Optional</span>
                    </div>

                    <div class="assignment-editor-card">
                        <label for="note_for_agent" class="form-label">Note for Agent</label>
                        <p class="assignment-field-note mb-3">Update any private notes, preferences, or execution instructions attached to this assignment.</p>
                        <div id="note_for_agent_editor" class="assignment-editor assignment-editor-sm"></div>
                        <textarea class="form-control @error('note_for_agent') is-invalid @enderror" id="note_for_agent" name="note_for_agent" style="display: none;">{{ old('note_for_agent', $assignment->note_for_agent) }}</textarea>
                        @error('note_for_agent')
                            <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </section>

                <section class="assignment-action-bar">
                    <a href="{{ route('admin.assignments') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Assignments
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Assignment
                    </button>
                </section>
            </form>
        </div>
    </div>
    @push('styles')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .assignment-create-page {
            --assignment-border: rgba(15, 15, 15, 0.08);
        }

        .assignment-summary-strip {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .assignment-summary-card,
        .assignment-panel {
            border: 1px solid var(--assignment-border);
            border-radius: 1.75rem;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(250, 247, 239, 0.98));
            box-shadow: 0 18px 44px rgba(15, 15, 15, 0.06);
            padding: 1.35rem;
        }

        .assignment-summary-label,
        .assignment-section-eyebrow {
            display: inline-flex;
            align-items: center;
            color: #9b7431;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
        }

        .assignment-person-row {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-top: 0.85rem;
        }

        .assignment-person-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 2.8rem;
            width: 2.8rem;
            border-radius: 1rem;
            background: linear-gradient(180deg, #191919 0%, #0d0d0d 100%);
            color: #f4d58f;
            font-size: 0.95rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .assignment-person-avatar.is-client {
            background: linear-gradient(180deg, #e8c97f 0%, #c89c40 100%);
            color: #111111;
        }

        .assignment-person-name,
        .assignment-summary-value {
            color: #111111;
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.35;
            margin-top: 0.85rem;
        }

        .assignment-person-row .assignment-person-name {
            margin-top: 0;
        }

        .assignment-person-meta,
        .assignment-field-note,
        .assignment-toggle-note {
            color: #756d61;
            font-size: 0.83rem;
            line-height: 1.65;
        }

        .assignment-status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2rem;
            padding: 0.35rem 0.8rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .assignment-status-pill.is-active {
            background: rgba(34, 197, 94, 0.12);
            color: #15803d;
        }

        .assignment-status-pill.is-inactive {
            background: rgba(239, 68, 68, 0.12);
            color: #b91c1c;
        }

        .assignment-section-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.2rem;
        }

        .assignment-section-title {
            margin: 0.28rem 0 0;
            color: #111111;
            font-family: var(--display-font-family, 'Poppins'), sans-serif;
            font-size: 1.28rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .assignment-section-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2rem;
            padding: 0.3rem 0.8rem;
            border-radius: 999px;
            background: rgba(200, 164, 93, 0.12);
            color: #8d6b2d;
            font-size: 0.73rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .assignment-field-shell .form-group {
            margin-bottom: 0;
        }

        .assignment-editor-card,
        .assignment-upload-card {
            border: 1px solid rgba(15, 15, 15, 0.06);
            border-radius: 1.35rem;
            background: rgba(255, 255, 255, 0.84);
            padding: 1rem;
        }

        .assignment-editor {
            overflow: hidden;
            border: 1px solid rgba(15, 15, 15, 0.08);
            border-radius: 1.25rem;
            background: #ffffff;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.86);
        }

        .assignment-editor-lg {
            min-height: 20rem;
        }

        .assignment-editor-sm {
            min-height: 14rem;
        }

        .assignment-editor .ql-toolbar.ql-snow {
            border: 0;
            border-bottom: 1px solid rgba(15, 15, 15, 0.06);
            background: linear-gradient(180deg, #fffdf8 0%, #f8f4eb 100%);
            padding: 0.85rem 0.9rem;
        }

        .assignment-editor .ql-container.ql-snow {
            border: 0;
            font-family: inherit;
            font-size: 0.95rem;
        }

        .assignment-editor .ql-editor {
            min-height: inherit;
            color: #1a1713;
            line-height: 1.75;
            padding: 1rem 1rem 1.15rem;
        }

        .assignment-existing-files {
            margin-bottom: 1rem;
        }

        .assignment-existing-files-label {
            color: #8d6b2d;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            margin-bottom: 0.65rem;
            text-transform: uppercase;
        }

        .assignment-file-chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
            margin-top: 0.85rem;
        }

        .assignment-file-row .input-group {
            align-items: stretch;
            gap: 0.75rem;
        }

        .assignment-file-row .input-group > .form-control {
            border-radius: 1rem !important;
        }

        .assignment-file-row .input-group > .btn {
            border-radius: 1rem !important;
            min-width: 3.25rem;
        }

        .assignment-toggle-row {
            margin-top: 0.25rem;
        }

        .assignment-toggle-shell {
            display: flex;
            align-items: flex-start;
            gap: 0.9rem;
            cursor: pointer;
        }

        .assignment-toggle-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .assignment-toggle-track {
            position: relative;
            display: inline-flex;
            height: 1.8rem;
            width: 3.2rem;
            flex-shrink: 0;
            border-radius: 999px;
            background: #d8d2c6;
            margin-top: 0.1rem;
            transition: background-color 0.18s ease;
        }

        .assignment-toggle-track::after {
            content: "";
            position: absolute;
            top: 0.2rem;
            left: 0.2rem;
            height: 1.4rem;
            width: 1.4rem;
            border-radius: 999px;
            background: #ffffff;
            box-shadow: 0 6px 16px rgba(15, 15, 15, 0.18);
            transition: transform 0.18s ease;
        }

        .assignment-toggle-input:checked + .assignment-toggle-track {
            background: linear-gradient(180deg, #e8c97f 0%, #c89c40 100%);
        }

        .assignment-toggle-input:checked + .assignment-toggle-track::after {
            transform: translateX(1.4rem);
        }

        .assignment-toggle-copy {
            display: grid;
            gap: 0.2rem;
        }

        .assignment-toggle-title {
            color: #111111;
            font-size: 0.95rem;
            font-weight: 700;
        }

        .assignment-action-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            border: 1px solid var(--assignment-border);
            border-radius: 1.45rem;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 16px 40px rgba(15, 15, 15, 0.05);
            padding: 1rem 1.1rem;
        }

        @media (max-width: 1024px) {
            .assignment-summary-strip {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767px) {
            .assignment-summary-strip {
                grid-template-columns: 1fr;
            }

            .assignment-summary-card,
            .assignment-panel,
            .assignment-action-bar {
                border-radius: 1.4rem;
                padding: 1rem;
            }

            .assignment-section-header,
            .assignment-action-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .assignment-action-bar > * {
                width: 100%;
            }

            .assignment-file-row .input-group {
                gap: 0.5rem;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        function addCoverLetter() {
            const container = document.getElementById('cover-letters-container');
            const newItem = document.createElement('div');
            newItem.className = 'cover-letter-item assignment-file-row';
            newItem.innerHTML = `
                <div class="input-group">
                    <input type="file" class="form-control" name="cover_letters[]" accept=".pdf,.doc,.docx,.csv,.xlsx,.xls">
                    <button type="button" class="btn btn-outline-danger" onclick="removeCoverLetter(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            container.appendChild(newItem);
        }

        function removeCoverLetter(button) {
            const row = button.closest('.cover-letter-item');
            const container = document.getElementById('cover-letters-container');

            if (container.children.length > 1) {
                row.remove();
                return;
            }

            const input = row ? row.querySelector('input[type="file"]') : null;
            if (input) {
                input.value = '';
            }
        }

        const quillToolbar = [
            [{ header: [1, 2, 3, 4, false] }],
            ['bold', 'italic', 'underline'],
            [{ list: 'ordered' }, { list: 'bullet' }],
            [{ align: [] }],
            ['link', 'blockquote'],
            ['clean']
        ];

        const quillApplyTo = new Quill('#apply_to_editor', {
            theme: 'snow',
            modules: { toolbar: quillToolbar },
            placeholder: 'Describe the job positions, industries, companies, locations, or requirements to target...'
        });

        const quillNote = new Quill('#note_for_agent_editor', {
            theme: 'snow',
            modules: { toolbar: quillToolbar },
            placeholder: 'Add private notes, preferences, or special instructions for the assigned agent...'
        });

        quillApplyTo.on('text-change', function () {
            document.getElementById('apply_to').value = quillApplyTo.root.innerHTML;
        });

        quillNote.on('text-change', function () {
            document.getElementById('note_for_agent').value = quillNote.root.innerHTML;
        });

        document.querySelector('form').addEventListener('submit', function (event) {
            const applyToEditor = document.getElementById('apply_to');
            const noteEditor = document.getElementById('note_for_agent');
            const applyToContent = quillApplyTo.root.innerHTML;
            const noteContent = quillNote.root.innerHTML;

            applyToEditor.value = applyToContent;
            noteEditor.value = noteContent;

            if (!applyToContent || applyToContent.trim() === '' || applyToContent === '<p><br></p>') {
                event.preventDefault();
                alert('Please fill in the Apply To field.');
            }
        });

        const existingApplyToContent = document.getElementById('apply_to').value;
        if (existingApplyToContent) {
            quillApplyTo.root.innerHTML = existingApplyToContent;
        }

        const existingNoteContent = document.getElementById('note_for_agent').value;
        if (existingNoteContent) {
            quillNote.root.innerHTML = existingNoteContent;
        }
    </script>
    @endpush
</x-app-layout>
