<x-app-layout>
    <x-slot name="title">Create Assignment</x-slot>
    <x-slot name="pageTitle">Create Agent Assignment</x-slot>
    <x-slot name="pageSubtitle">Pair a client with an agent and prepare the handoff package.</x-slot>

    <div class="assignment-create-page">
        <div class="mx-auto max-w-6xl">
            <form method="POST" action="{{ route('admin.assignments.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <section class="assignment-panel">
                    <div class="assignment-section-header">
                        <div>
                            <span class="assignment-section-eyebrow">Core Match</span>
                            <h3 class="assignment-section-title">Assignment Identity</h3>
                        </div>
                        <span class="assignment-section-chip">Required setup</span>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="assignment-field-shell">
                            <x-searchable-select
                                name="agent_id"
                                label="Select Agent"
                                placeholder="Choose an agent..."
                                :options="$agents->map(fn($agent) => ['value' => $agent->id, 'text' => $agent->name . ' (' . $agent->email . ')'])->toArray()"
                                :value="old('agent_id')"
                                search-url="{{ route('admin.assignments.search-agents') }}"
                                required="true"
                            />
                            <p class="assignment-field-note">Open the dropdown to see all available agents, or type to narrow the list instantly.</p>
                        </div>

                        <div class="assignment-field-shell">
                            <x-searchable-select
                                name="client_id"
                                label="Select Client"
                                placeholder="Choose a client..."
                                :options="$clients->map(fn($client) => ['value' => $client->id, 'text' => $client->name . ' (' . $client->email . ')'])->toArray()"
                                :value="old('client_id')"
                                search-url="{{ route('admin.assignments.search-clients') }}"
                                required="true"
                            />
                            <p class="assignment-field-note">The full client list is available on open, and the selector stays searchable.</p>
                        </div>

                        <div class="assignment-field-shell lg:max-w-sm">
                            <label for="service_end_date" class="form-label">Service End Date</label>
                            <input
                                type="date"
                                class="form-control @error('service_end_date') is-invalid @enderror"
                                id="service_end_date"
                                name="service_end_date"
                                value="{{ old('service_end_date') }}"
                                min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                            >
                            <p class="assignment-field-note">Leave this blank when the service should stay open-ended.</p>
                            @error('service_end_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="assignment-field-shell lg:max-w-sm">
                            <label for="minimum_work_updates" class="form-label">Minimum Work Updates <span class="text-danger">*</span></label>
                            <input
                                type="number"
                                class="form-control @error('minimum_work_updates') is-invalid @enderror"
                                id="minimum_work_updates"
                                name="minimum_work_updates"
                                value="{{ old('minimum_work_updates', \App\Models\AgentClientAssignment::DEFAULT_MINIMUM_WORK_UPDATES) }}"
                                min="1"
                                max="50"
                                step="1"
                                required
                            >
                            <p class="assignment-field-note">Agent must submit at least this many draft updates before they can submit for this client.</p>
                            @error('minimum_work_updates')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
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
                        <p class="assignment-field-note mb-3">Outline the role types, industries, target companies, and any non-negotiable constraints for the agent.</p>
                        <div id="apply_to_editor" class="assignment-editor assignment-editor-lg"></div>
                        <textarea
                            class="form-control @error('apply_to') is-invalid @enderror"
                            id="apply_to"
                            name="apply_to"
                            style="display: none;"
                        >{{ old('apply_to') }}</textarea>
                        @error('apply_to')
                            <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </section>

                <section class="assignment-panel">
                    <div class="assignment-section-header">
                        <div>
                            <span class="assignment-section-eyebrow">Files & Assets</span>
                            <h3 class="assignment-section-title">Upload Assignment Materials</h3>
                        </div>
                        <span class="assignment-section-chip">Optional files</span>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-2">
                        <div class="assignment-upload-card">
                            <label for="resume_file" class="form-label">Resume File</label>
                            <input
                                type="file"
                                class="form-control @error('resume_file') is-invalid @enderror"
                                id="resume_file"
                                name="resume_file"
                                accept=".pdf,.doc,.docx,.csv,.xlsx,.xls"
                            >
                            <p class="assignment-field-note">Accepted formats: PDF, DOC, DOCX, CSV, XLSX, XLS.</p>
                            @error('resume_file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="assignment-upload-card">
                            <label for="onboarding_form_file" class="form-label">Onboarding Form</label>
                            <input
                                type="file"
                                class="form-control @error('onboarding_form_file') is-invalid @enderror"
                                id="onboarding_form_file"
                                name="onboarding_form_file"
                                accept=".pdf,.doc,.docx,.csv,.xlsx,.xls"
                            >
                            <p class="assignment-field-note">Use this for intake sheets, role notes, or onboarding documents.</p>
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

                        <div id="cover-letters-container" class="space-y-3">
                            <div class="cover-letter-item assignment-file-row">
                                <div class="input-group">
                                    <input type="file" class="form-control" name="cover_letters[]" accept=".pdf,.doc,.docx,.csv,.xlsx,.xls">
                                    <button type="button" class="btn btn-outline-danger" onclick="removeCoverLetter(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <p class="assignment-field-note mt-3">Add one or more optional cover letters. If you only keep one row, removing it will just clear the field.</p>
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
                        <p class="assignment-field-note mb-3">Use this for handoff notes, expectations, or client-specific instructions that should stay attached to the assignment.</p>
                        <div id="note_for_agent_editor" class="assignment-editor assignment-editor-sm"></div>
                        <textarea
                            class="form-control @error('note_for_agent') is-invalid @enderror"
                            id="note_for_agent"
                            name="note_for_agent"
                            style="display: none;"
                        >{{ old('note_for_agent') }}</textarea>
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
                        <i class="fas fa-save me-2"></i>Create Assignment
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
            --assignment-soft: rgba(200, 164, 93, 0.12);
            --assignment-gold: #c89c40;
        }

        .assignment-hero {
            display: grid;
            gap: 1.5rem;
            border: 1px solid var(--assignment-border);
            border-radius: 2rem;
            background:
                radial-gradient(circle at top right, rgba(200, 164, 93, 0.18), transparent 32%),
                linear-gradient(180deg, #ffffff 0%, #f8f4eb 100%);
            box-shadow: 0 24px 58px rgba(15, 15, 15, 0.08);
            padding: 1.5rem;
        }

        .assignment-kicker,
        .assignment-section-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #9b7431;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.24em;
            text-transform: uppercase;
        }

        .assignment-hero-title {
            margin: 0.6rem 0 0;
            color: #111111;
            font-family: var(--display-font-family, 'Poppins'), sans-serif;
            font-size: clamp(1.7rem, 2.2vw, 2.55rem);
            font-weight: 700;
            letter-spacing: -0.03em;
            line-height: 1.08;
            max-width: 14ch;
        }

        .assignment-hero-text {
            margin: 1rem 0 0;
            color: #665f54;
            font-size: 0.96rem;
            line-height: 1.7;
            max-width: 44rem;
        }

        .assignment-hero-metrics {
            display: grid;
            gap: 0.85rem;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .assignment-metric-card {
            display: grid;
            gap: 0.3rem;
            border: 1px solid var(--assignment-border);
            border-radius: 1.35rem;
            background: rgba(255, 255, 255, 0.78);
            padding: 1rem 1.1rem;
        }

        .assignment-metric-card.is-accent {
            background: linear-gradient(180deg, #1b1b1b 0%, #101010 100%);
            border-color: rgba(200, 164, 93, 0.16);
        }

        .assignment-metric-value {
            color: #111111;
            font-size: 1.45rem;
            font-weight: 700;
            line-height: 1;
        }

        .assignment-metric-card.is-accent .assignment-metric-value {
            color: #f4d58f;
        }

        .assignment-metric-label {
            color: #766f63;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .assignment-metric-card.is-accent .assignment-metric-label {
            color: #d6c6a4;
        }

        .assignment-panel,
        .assignment-side-panel {
            border: 1px solid var(--assignment-border);
            border-radius: 1.75rem;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(250, 247, 239, 0.98));
            box-shadow: 0 18px 44px rgba(15, 15, 15, 0.06);
            padding: 1.35rem;
        }

        .assignment-side-panel-dark {
            background:
                radial-gradient(circle at top right, rgba(200, 164, 93, 0.26), transparent 34%),
                linear-gradient(180deg, #181818 0%, #0d0d0d 100%);
            color: #f8f3e8;
        }

        .assignment-section-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.2rem;
        }

        .assignment-section-title,
        .assignment-side-title {
            margin: 0.28rem 0 0;
            color: #111111;
            font-family: var(--display-font-family, 'Poppins'), sans-serif;
            font-size: 1.28rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .assignment-side-panel-dark .assignment-side-title {
            color: #ffffff;
        }

        .assignment-section-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(200, 164, 93, 0.12);
            color: #8d6b2d;
            font-size: 0.73rem;
            font-weight: 700;
            min-height: 2rem;
            padding: 0.3rem 0.8rem;
            white-space: nowrap;
        }

        .assignment-field-shell .form-group {
            margin-bottom: 0;
        }

        .assignment-field-note,
        .assignment-side-text,
        .assignment-step-text,
        .assignment-checklist li {
            color: #756d61;
            font-size: 0.83rem;
            line-height: 1.65;
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

        .assignment-side-stat-grid {
            display: grid;
            gap: 0.85rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 1.4rem;
        }

        .assignment-side-stat {
            border: 1px solid rgba(228, 196, 122, 0.16);
            border-radius: 1.1rem;
            background: rgba(255, 255, 255, 0.04);
            padding: 0.9rem;
        }

        .assignment-side-stat-label {
            display: block;
            color: #c6b89e;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .assignment-side-stat-value {
            display: block;
            color: #ffffff;
            font-size: 1.2rem;
            font-weight: 700;
            margin-top: 0.35rem;
        }

        .assignment-step-list {
            display: grid;
            gap: 1rem;
            margin-top: 1rem;
        }

        .assignment-step-item {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 0.9rem;
            align-items: start;
        }

        .assignment-step-index {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 2.35rem;
            width: 2.35rem;
            border-radius: 0.95rem;
            background: linear-gradient(180deg, #181818 0%, #0d0d0d 100%);
            color: #f4d58f;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.08em;
        }

        .assignment-step-title {
            color: #111111;
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
        }

        .assignment-checklist {
            display: grid;
            gap: 0.7rem;
            margin: 1rem 0 0;
            padding-left: 1.1rem;
        }

        .assignment-checklist li::marker {
            color: var(--assignment-gold);
        }

        @media (max-width: 1024px) {
            .assignment-hero-metrics {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767px) {
            .assignment-hero,
            .assignment-panel,
            .assignment-side-panel,
            .assignment-action-bar {
                border-radius: 1.4rem;
                padding: 1rem;
            }

            .assignment-hero-metrics,
            .assignment-side-stat-grid {
                grid-template-columns: 1fr;
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
