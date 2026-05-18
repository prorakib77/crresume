<x-app-layout>
    <x-slot name="title">Edit Email Template</x-slot>
    <x-slot name="pageTitle">Edit Email Template</x-slot>
    <x-slot name="pageSubtitle">{{ $template->template_name }}</x-slot>

    @php
        $variables = $template->variables;
        $accentColorValue = old('accent_color', $accentColor ?? '#C8A45D');
        $emailHeaderLogoUrlValue = old('email_header_logo_url', $emailHeaderLogoUrl ?? '');
        $emailHeaderBgUrlValue = old('email_header_bg_image_url', $emailHeaderBgImageUrl ?? '');
        $footerNoteValue = old('footer_note', $template->footer_note ?? \App\Models\EmailTemplate::defaultFooterNote($template->template_key) ?? 'This is an automated email.');
        $contentNoteValue = old('content_note', $template->content_note ?? \App\Models\EmailTemplate::defaultContentNote($template->template_key) ?? '');
        $fromNameValue = old('from_name', $template->from_name ?? '');
        $fromEmailValue = old('from_email', $template->from_email ?? '');
        $emailHeaderLogoUploadedPreview = $emailHeaderLogoUploadedUrl ?? null;
        $emailHeaderBgUploadedPreview = $emailHeaderBgUploadedUrl ?? null;
        $isDailyWorkUpdateTemplate = $template->template_key === \App\Models\EmailTemplate::KEY_DAILY_WORK_UPDATE;
    @endphp

    <form method="POST" action="{{ route('admin.email-templates.update', $template) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="rounded-[1.9rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#9b7431]">{{ $template->template_key }}</p>
                    <h2 class="theme-display mt-2 text-3xl text-stone-950">{{ $template->template_name }}</h2>
                    @if($template->description)
                        <p class="mt-3 text-sm text-stone-600">{{ $template->description }}</p>
                    @endif
                </div>
                <a href="{{ route('admin.email-templates.index') }}" class="btn btn-border-black">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.45fr)_minmax(0,0.85fr)]">
            <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
                <div class="grid gap-4">
                    <div>
                        <label for="subject_template" class="form-label">Email Subject</label>
                        <input
                            id="subject_template"
                            name="subject_template"
                            type="text"
                            class="form-control @error('subject_template') is-invalid @enderror"
                            value="{{ old('subject_template', $template->subject_template) }}"
                            maxlength="1000"
                            required
                        >
                        @error('subject_template')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="body_template" class="form-label">Email Body (HTML supported)</label>
                        <textarea
                            id="body_template"
                            name="body_template"
                            class="form-control font-monospace @error('body_template') is-invalid @enderror"
                            rows="20"
                            required
                        >{{ old('body_template', $template->body_template) }}</textarea>
                        @error('body_template')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="from_name" class="form-label">From Name Override</label>
                            <input
                                id="from_name"
                                name="from_name"
                                type="text"
                                class="form-control @error('from_name') is-invalid @enderror"
                                value="{{ $fromNameValue }}"
                                maxlength="190"
                                placeholder="{{ $defaultFromName }}"
                            >
                            <p class="mt-2 text-xs text-stone-500">Leave blank to use the default sender name: {{ $defaultFromName }}</p>
                            @error('from_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="from_email" class="form-label">From Email Override</label>
                            <input
                                id="from_email"
                                name="from_email"
                                type="email"
                                class="form-control @error('from_email') is-invalid @enderror"
                                value="{{ $fromEmailValue }}"
                                maxlength="255"
                                placeholder="{{ $defaultFromEmail }}"
                            >
                            <p class="mt-2 text-xs text-stone-500">Leave blank to use the default sender email: {{ $defaultFromEmail }}</p>
                            @error('from_email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-stone-800">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $template->is_active))>
                            Template Active
                        </label>
                    </div>

                    <div>
                        <label for="footer_note" class="form-label">Footer Note</label>
                        <textarea
                            id="footer_note"
                            name="footer_note"
                            class="form-control @error('footer_note') is-invalid @enderror"
                            rows="3"
                            maxlength="1000"
                            placeholder="This is an automated email."
                        >{{ $footerNoteValue }}</textarea>
                        <p class="mt-2 text-xs text-stone-500">This footer line is saved separately for this email template only.</p>
                        @error('footer_note')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($isDailyWorkUpdateTemplate)
                        <div>
                            <label for="content_note" class="form-label">Daily Work Update Notice</label>
                            <textarea
                                id="content_note"
                                name="content_note"
                                class="form-control @error('content_note') is-invalid @enderror"
                                rows="4"
                                maxlength="2000"
                                placeholder="This is an automated message and this inbox is not monitored. Please do not reply to this email."
                            >{{ $contentNoteValue }}</textarea>
                            <p class="mt-2 text-xs text-stone-500">This controls the notice inside the daily work update card footer.</p>
                            @error('content_note')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>
            </section>

            <aside class="space-y-6">
                <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#9b7431]">Placeholders</p>
                    <h3 class="theme-display mt-2 text-2xl text-stone-950">Available Variables</h3>

                    @if(empty($variables))
                        <p class="mt-4 text-sm text-stone-600">No dynamic placeholders for this template.</p>
                    @else
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach($variables as $variable)
                                <code class="rounded-full border border-[#e2d5bc] bg-[#fbf5e8] px-3 py-1 text-xs font-semibold text-stone-700">&#123;&#123;{{ $variable }}&#125;&#125;</code>
                            @endforeach
                        </div>
                    @endif

                    <p class="mt-4 text-xs text-stone-500">Use placeholder format exactly like <code>&#123;&#123;variable_name&#125;&#125;</code>.</p>
                </section>

                <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#9b7431]">Global Email Style</p>
                    <h3 class="theme-display mt-2 text-2xl text-stone-950">Branding Options</h3>
                    <p class="mt-2 text-xs text-stone-500">These options apply to all email templates.</p>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label for="accent_color" class="form-label">Highlight Color</label>
                            <div class="flex items-center gap-2">
                                <input id="accent_color" name="accent_color" type="color" value="{{ $accentColorValue }}" class="h-11 w-16 cursor-pointer rounded-xl border border-[#d8c6a1] bg-white p-1">
                                <input id="accent_color_hex" type="text" value="{{ $accentColorValue }}" class="form-control uppercase" maxlength="7" placeholder="#C8A45D">
                            </div>
                            @error('accent_color')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="rounded-[1.2rem] border border-[#ece3d2] bg-[#fffdfa] p-3">
                            <label for="email_header_logo" class="form-label">Header Logo Upload</label>
                            <input id="email_header_logo" name="email_header_logo" type="file" accept="image/*" class="form-control mt-2 @error('email_header_logo') is-invalid @enderror">
                            @error('email_header_logo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="mt-3 flex h-20 items-center justify-center rounded-xl border border-dashed border-[#d8c6a1] bg-white p-3">
                                <img id="email-header-logo-preview" src="{{ $emailHeaderLogoUploadedPreview }}" alt="Email logo preview" class="{{ $emailHeaderLogoUploadedPreview ? '' : 'hidden ' }}max-h-full w-auto object-contain">
                                <span id="email-header-logo-placeholder" class="{{ $emailHeaderLogoUploadedPreview ? 'hidden ' : '' }}text-[11px] font-semibold uppercase tracking-[0.24em] text-stone-400">No Uploaded Logo</span>
                            </div>
                        </div>

                        <div>
                            <label for="email_header_logo_url" class="form-label">Header Logo URL (Optional)</label>
                            <input
                                id="email_header_logo_url"
                                name="email_header_logo_url"
                                type="text"
                                class="form-control @error('email_header_logo_url') is-invalid @enderror"
                                value="{{ $emailHeaderLogoUrlValue }}"
                                placeholder="https://example.com/logo.png"
                            >
                            @error('email_header_logo_url')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="rounded-[1.2rem] border border-[#ece3d2] bg-[#fffdfa] p-3">
                            <label for="email_header_bg_image" class="form-label">Header Background Upload</label>
                            <input id="email_header_bg_image" name="email_header_bg_image" type="file" accept="image/*" class="form-control mt-2 @error('email_header_bg_image') is-invalid @enderror">
                            @error('email_header_bg_image')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="mt-3 flex h-20 items-center justify-center rounded-xl border border-dashed border-[#d8c6a1] bg-white p-3">
                                <img id="email-header-bg-preview" src="{{ $emailHeaderBgUploadedPreview }}" alt="Email background preview" class="{{ $emailHeaderBgUploadedPreview ? '' : 'hidden ' }}max-h-full w-auto object-contain">
                                <span id="email-header-bg-placeholder" class="{{ $emailHeaderBgUploadedPreview ? 'hidden ' : '' }}text-[11px] font-semibold uppercase tracking-[0.24em] text-stone-400">No Uploaded Background</span>
                            </div>
                        </div>

                        <div>
                            <label for="email_header_bg_image_url" class="form-label">Header Background URL (Optional)</label>
                            <input
                                id="email_header_bg_image_url"
                                name="email_header_bg_image_url"
                                type="text"
                                class="form-control @error('email_header_bg_image_url') is-invalid @enderror"
                                value="{{ $emailHeaderBgUrlValue }}"
                                placeholder="https://example.com/background.jpg"
                            >
                            @error('email_header_bg_image_url')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </section>

                <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
                    <button type="submit" class="btn btn-black w-100">
                        <i class="fas fa-save me-2"></i>Save Template
                    </button>
                </section>
            </aside>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.email-templates.reset', $template) }}" class="mt-4" onsubmit="return confirm('Reset this template to default?');">
        @csrf
        <button type="submit" class="btn btn-outline-danger">
            <i class="fas fa-rotate-left me-2"></i>Reset To Default
        </button>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const accentColorInput = document.getElementById('accent_color');
                const accentHexInput = document.getElementById('accent_color_hex');

                if (accentColorInput && accentHexInput) {
                    accentColorInput.addEventListener('input', function () {
                        accentHexInput.value = accentColorInput.value.toUpperCase();
                    });

                    accentHexInput.addEventListener('input', function () {
                        const value = accentHexInput.value.trim();
                        if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
                            accentColorInput.value = value.toUpperCase();
                        }
                    });

                    accentHexInput.addEventListener('blur', function () {
                        accentHexInput.value = accentColorInput.value.toUpperCase();
                    });
                }

                const bindFilePreview = function (inputId, imageId, placeholderId) {
                    const input = document.getElementById(inputId);
                    const image = document.getElementById(imageId);
                    const placeholder = document.getElementById(placeholderId);

                    if (!input || !image || !placeholder) return;

                    input.addEventListener('change', function () {
                        const file = input.files && input.files[0];
                        if (!file) return;

                        const reader = new FileReader();
                        reader.onload = function (event) {
                            image.src = event.target.result;
                            image.classList.remove('hidden');
                            placeholder.classList.add('hidden');
                        };
                        reader.readAsDataURL(file);
                    });
                };

                bindFilePreview('email_header_logo', 'email-header-logo-preview', 'email-header-logo-placeholder');
                bindFilePreview('email_header_bg_image', 'email-header-bg-preview', 'email-header-bg-placeholder');
            });
        </script>
    @endpush
</x-app-layout>
