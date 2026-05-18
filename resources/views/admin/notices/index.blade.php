<x-app-layout>
    <x-slot name="title">Notices</x-slot>
    <x-slot name="pageTitle">Notices</x-slot>
    <x-slot name="pageSubtitle">Create and manage important notices for agents and clients.</x-slot>

    <div class="row g-4">
        <div class="col-lg-5">
            <div
                class="card h-100"
                x-data="{
                    scope: @js(old('scope', 'client')),
                    selectedIcon: @js(old('icon_class', 'fa-solid fa-circle-info')),
                    customIcon: @js(old('custom_icon_class', '')),
                    backgroundColor: @js(old('background_color', '#fff7e0')),
                    previewIcon() {
                        return this.customIcon.trim() ? this.customIcon.trim() : this.selectedIcon;
                    }
                }"
            >
                <div class="card-header border-0 pb-0">
                    <h5 class="mb-0">Create Notice</h5>
                    <small class="text-muted">Send an important notice to agents, clients, or one specific person.</small>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.notices.store') }}" class="notice-admin-form">
                        @csrf

                        <div class="notice-admin-preview" :style="`--notice-preview-bg:${backgroundColor};`">
                            <span class="notice-admin-preview-icon">
                                <i :class="previewIcon()"></i>
                            </span>
                            <div>
                                <div class="notice-admin-preview-title">{{ old('title', 'Notice Preview') }}</div>
                                <div class="notice-admin-preview-copy">Choose the audience, icon, color, and message here.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notice Title</label>
                            <input type="text" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" maxlength="255" required>
                            @error('title')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notice Content</label>
                            <textarea name="content" rows="5" class="form-control @error('content') is-invalid @enderror" maxlength="5000" required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Target</label>
                            <select name="scope" class="form-control" x-model="scope">
                                <option value="client">All Clients</option>
                                <option value="agent">All Agents</option>
                                <option value="both">Agents & Clients</option>
                                <option value="specific">Single Person</option>
                            </select>
                        </div>

                        <div class="mb-3" x-show="scope === 'specific'" x-cloak>
                            <label class="form-label fw-semibold">Select Person</label>
                            <select name="recipient_user_id" class="form-control @error('recipient_user_id') is-invalid @enderror">
                                <option value="">Choose agent or client</option>
                                @foreach($targetUsers as $targetUser)
                                    <option value="{{ $targetUser->id }}" {{ (string) old('recipient_user_id') === (string) $targetUser->id ? 'selected' : '' }}>
                                        {{ $targetUser->name }} ({{ $targetUser->isAgent() ? 'Agent' : 'Client' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('recipient_user_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label fw-semibold">Icon</label>
                                <select name="icon_class" class="form-control" x-model="selectedIcon">
                                    @foreach($iconOptions as $iconOption)
                                        <option value="{{ $iconOption['value'] }}" {{ old('icon_class', 'fa-solid fa-circle-info') === $iconOption['value'] ? 'selected' : '' }}>
                                            {{ $iconOption['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Background</label>
                                <input type="color" name="background_color" value="{{ old('background_color', '#fff7e0') }}" class="form-control form-control-color @error('background_color') is-invalid @enderror" x-model="backgroundColor" required>
                                @error('background_color')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-3 mb-3">
                            <label class="form-label fw-semibold">Custom Font Awesome Icon Class</label>
                            <input type="text" name="custom_icon_class" value="{{ old('custom_icon_class') }}" class="form-control @error('custom_icon_class') is-invalid @enderror" x-model="customIcon" placeholder="Example: fa-solid fa-bullhorn">
                            <div class="form-text">Optional. Paste any Font Awesome icon class if you want something outside the dropdown list.</div>
                            @error('custom_icon_class')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Action URL (Optional)</label>
                            <input type="text" name="action_url" value="{{ old('action_url') }}" class="form-control @error('action_url') is-invalid @enderror" placeholder="/client/onboarding or https://example.com">
                            @error('action_url')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="notice-admin-checkbox">
                                <input
                                    type="checkbox"
                                    name="send_email"
                                    value="1"
                                    class="form-check-input mt-1 @error('send_email') is-invalid @enderror"
                                    {{ old('send_email', '1') ? 'checked' : '' }}
                                >
                                <span>
                                    <span class="d-block fw-semibold text-dark">Send email for this notice</span>
                                    <span class="d-block text-muted small">If unchecked, the notice will still be created in the dashboard without sending notice emails.</span>
                                </span>
                            </label>
                            @error('send_email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-black w-100">
                            <i class="fas fa-paper-plane me-2"></i>Create Notice
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h5 class="mb-0">Admin Notices</h5>
                    <small class="text-muted">Turn notices on or off here. Payment and onboarding notices are generated automatically outside this list.</small>
                </div>
                <div class="card-body">
                    <div class="notice-admin-list">
                        @forelse($manualNotices as $manualNotice)
                            <article class="notice-admin-item" style="--notice-admin-bg: {{ $manualNotice->background_color }}; --notice-admin-text: {{ $manualNotice->resolved_text_color }};">
                                <div class="notice-admin-item-preview">
                                    <span class="notice-admin-item-icon">
                                        <i class="{{ $manualNotice->resolved_icon_class }}"></i>
                                    </span>
                                    <div>
                                        <div class="notice-admin-item-title-row">
                                            <h3 class="notice-admin-item-title">{{ $manualNotice->title }}</h3>
                                            <span class="badge {{ $manualNotice->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $manualNotice->is_active ? 'Active' : 'Off' }}
                                            </span>
                                        </div>
                                        <p class="notice-admin-item-copy">{{ $manualNotice->content }}</p>
                                    </div>
                                </div>

                                <div class="notice-admin-item-meta">
                                    <span>Target: {{ $manualNotice->target_label }}</span>
                                    <span>Created {{ $manualNotice->created_at?->diffForHumans() }}</span>
                                </div>

                                <div class="notice-admin-item-actions">
                                    @if($manualNotice->action_url)
                                        <a href="{{ $manualNotice->action_url }}" class="btn btn-border-black btn-sm">
                                            <i class="fas fa-arrow-up-right-from-square me-2"></i>Open Link
                                        </a>
                                    @endif

                                    <form method="POST" action="{{ route('admin.notices.toggle', $manualNotice) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $manualNotice->is_active ? 'btn-outline-dark' : 'btn-black' }}">
                                            <i class="fas {{ $manualNotice->is_active ? 'fa-toggle-off' : 'fa-toggle-on' }} me-2"></i>
                                            {{ $manualNotice->is_active ? 'Turn Off' : 'Turn On' }}
                                        </button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="notice-admin-empty">
                                <i class="fas fa-bullhorn"></i>
                                <h3>No admin notices yet</h3>
                                <p>Create a notice to send an important message to agents, clients, or one specific person.</p>
                            </div>
                        @endforelse
                    </div>

                    @if($manualNotices->hasPages())
                        <div class="work-updates-pagination mt-4">
                            {{ $manualNotices->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .notice-admin-form {
            display: grid;
        }

        .notice-admin-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.95rem 1rem;
            border: 1px solid rgba(15, 15, 15, 0.08);
            border-radius: 1rem;
            background: rgba(255, 253, 250, 0.92);
        }

        .notice-admin-checkbox .form-check-input {
            flex: 0 0 auto;
        }

        .notice-admin-preview {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 0.9rem;
            align-items: center;
            margin-bottom: 1.2rem;
            padding: 1rem;
            border: 1px solid rgba(15, 15, 15, 0.08);
            border-radius: 1.25rem;
            background: var(--notice-preview-bg, #fff7e0);
        }

        .notice-admin-preview-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            border-radius: 1rem;
            background: rgba(17, 17, 17, 0.12);
            color: #111111;
            font-size: 1rem;
        }

        .notice-admin-preview-title {
            color: #111111;
            font-family: var(--display-font-family, 'Poppins'), sans-serif;
            font-size: 1rem;
            font-weight: 700;
        }

        .notice-admin-preview-copy {
            color: #5f584d;
            font-size: 0.84rem;
            line-height: 1.6;
            margin-top: 0.2rem;
        }

        .notice-admin-list {
            display: grid;
            gap: 1rem;
        }

        .notice-admin-item {
            border: 1px solid rgba(15, 15, 15, 0.08);
            border-radius: 1.35rem;
            background: var(--notice-admin-bg, #fff7e0);
            color: var(--notice-admin-text, #111111);
            box-shadow: 0 14px 30px rgba(15, 15, 15, 0.05);
            padding: 1rem;
        }

        .notice-admin-item-preview {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 0.9rem;
            align-items: flex-start;
        }

        .notice-admin-item-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            border-radius: 1rem;
            background: rgba(17, 17, 17, 0.12);
            font-size: 1rem;
        }

        .notice-admin-item-title-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .notice-admin-item-title {
            margin: 0;
            font-family: var(--display-font-family, 'Poppins'), sans-serif;
            font-size: 1rem;
            font-weight: 700;
        }

        .notice-admin-item-copy {
            margin: 0.45rem 0 0;
            color: inherit;
            opacity: 0.88;
            font-size: 0.9rem;
            line-height: 1.65;
        }

        .notice-admin-item-meta,
        .notice-admin-item-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 0.95rem;
        }

        .notice-admin-item-meta {
            color: inherit;
            opacity: 0.78;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .notice-admin-empty {
            display: grid;
            justify-items: center;
            gap: 0.55rem;
            padding: 3rem 1.5rem;
            text-align: center;
        }

        .notice-admin-empty i {
            font-size: 2rem;
            color: #9b7431;
        }

        .notice-admin-empty h3 {
            margin: 0;
            color: #111111;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .notice-admin-empty p {
            margin: 0;
            color: #756d61;
            font-size: 0.9rem;
            line-height: 1.65;
            max-width: 24rem;
        }

        @media (max-width: 768px) {
            .notice-admin-item-preview {
                grid-template-columns: 1fr;
            }

            .notice-admin-item-meta,
            .notice-admin-item-actions {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
    @endpush
</x-app-layout>
