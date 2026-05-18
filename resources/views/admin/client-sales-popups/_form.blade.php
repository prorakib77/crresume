@php
    /** @var \App\Models\ClientSalesPopup|null $popup */
    $popup = $popup ?? null;
    $imagePreview = old('image_url', $popup?->image_source_url);
@endphp

<div class="grid gap-6 lg:grid-cols-[minmax(0,1.45fr)_minmax(0,0.85fr)]">
    <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#efe5d2] pb-4">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-[#9b7431]">Client Dashboard Popup</p>
                <h2 class="theme-display mt-2 text-3xl text-stone-950">Popup Details</h2>
            </div>
            <span class="rounded-full border border-[#d8c6a1] bg-[#fbf5e8] px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-[#9b7431]">
                Client Only
            </span>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="title" class="form-label">Popup Title</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    class="form-control @error('title') is-invalid @enderror"
                    value="{{ old('title', $popup?->title) }}"
                    maxlength="190"
                    required
                >
                @error('title')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="badge_text" class="form-label">Badge Text</label>
                <input
                    type="text"
                    id="badge_text"
                    name="badge_text"
                    class="form-control @error('badge_text') is-invalid @enderror"
                    value="{{ old('badge_text', $popup?->badge_text ?? 'Exclusive Offer') }}"
                    maxlength="120"
                >
                @error('badge_text')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="price_text" class="form-label">Price Text</label>
                <input
                    type="text"
                    id="price_text"
                    name="price_text"
                    class="form-control @error('price_text') is-invalid @enderror"
                    value="{{ old('price_text', $popup?->price_text) }}"
                    maxlength="80"
                    placeholder="e.g. $249 (Renewal Deal)"
                >
                @error('price_text')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="message" class="form-label">Popup Message</label>
                <textarea
                    id="message"
                    name="message"
                    class="form-control @error('message') is-invalid @enderror"
                    rows="4"
                    maxlength="3000"
                    placeholder="Add the sales details for recurring or individual clients..."
                >{{ old('message', $popup?->message) }}</textarea>
                @error('message')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="cta_label" class="form-label">Button Label</label>
                <input
                    type="text"
                    id="cta_label"
                    name="cta_label"
                    class="form-control @error('cta_label') is-invalid @enderror"
                    value="{{ old('cta_label', $popup?->cta_label ?? 'Book Now') }}"
                    maxlength="50"
                >
                @error('cta_label')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="cta_link" class="form-label">Button URL</label>
                <input
                    type="text"
                    id="cta_link"
                    name="cta_link"
                    class="form-control @error('cta_link') is-invalid @enderror"
                    value="{{ old('cta_link', $popup?->cta_link ?? '#') }}"
                    placeholder="https://example.com/renewal"
                >
                @error('cta_link')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="target_type" class="form-label">Target</label>
                <select id="target_type" name="target_type" class="form-select @error('target_type') is-invalid @enderror">
                    @foreach($targetTypeOptions as $targetValue => $targetLabel)
                        <option value="{{ $targetValue }}" @selected(old('target_type', $popup?->target_type ?? \App\Models\ClientSalesPopup::TARGET_RECURRING) === $targetValue)>{{ $targetLabel }}</option>
                    @endforeach
                </select>
                @error('target_type')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div id="target_client_wrapper" class="{{ old('target_type', $popup?->target_type ?? \App\Models\ClientSalesPopup::TARGET_RECURRING) === \App\Models\ClientSalesPopup::TARGET_SPECIFIC ? '' : 'hidden' }}">
                <x-searchable-select
                    name="target_client_id"
                    label="Select Client"
                    placeholder="Choose a client..."
                    :options="$clients->map(fn($client) => ['value' => $client->id, 'text' => $client->name . ' (' . $client->email . ')'])->toArray()"
                    :value="old('target_client_id', $popup?->target_client_id)"
                />
                @error('target_client_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="show_delay" class="form-label">Show Delay (seconds)</label>
                <input
                    type="number"
                    id="show_delay"
                    name="show_delay"
                    class="form-control @error('show_delay') is-invalid @enderror"
                    min="0"
                    max="15"
                    value="{{ old('show_delay', $popup?->show_delay ?? 1) }}"
                >
                @error('show_delay')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="sort_order" class="form-label">Sort Order</label>
                <input
                    type="number"
                    id="sort_order"
                    name="sort_order"
                    class="form-control @error('sort_order') is-invalid @enderror"
                    min="0"
                    value="{{ old('sort_order', $popup?->sort_order ?? 0) }}"
                >
                @error('sort_order')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="starts_at" class="form-label">Start Date & Time</label>
                <input
                    type="datetime-local"
                    id="starts_at"
                    name="starts_at"
                    class="form-control @error('starts_at') is-invalid @enderror"
                    value="{{ old('starts_at', optional($popup?->starts_at)->format('Y-m-d\TH:i')) }}"
                >
                @error('starts_at')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="ends_at" class="form-label">End Date & Time</label>
                <input
                    type="datetime-local"
                    id="ends_at"
                    name="ends_at"
                    class="form-control @error('ends_at') is-invalid @enderror"
                    value="{{ old('ends_at', optional($popup?->ends_at)->format('Y-m-d\TH:i')) }}"
                >
                @error('ends_at')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="bg_color" class="form-label">Background Color</label>
                <input
                    type="color"
                    id="bg_color"
                    name="bg_color"
                    class="h-12 w-full cursor-pointer rounded-2xl border border-[#d8c6a1] bg-white p-1 @error('bg_color') is-invalid @enderror"
                    value="{{ old('bg_color', $popup?->bg_color ?? '#111111') }}"
                >
                @error('bg_color')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="text_color" class="form-label">Text Color</label>
                <input
                    type="color"
                    id="text_color"
                    name="text_color"
                    class="h-12 w-full cursor-pointer rounded-2xl border border-[#d8c6a1] bg-white p-1 @error('text_color') is-invalid @enderror"
                    value="{{ old('text_color', $popup?->text_color ?? '#FFFFFF') }}"
                >
                @error('text_color')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="accent_color" class="form-label">Accent Color</label>
                <input
                    type="color"
                    id="accent_color"
                    name="accent_color"
                    class="h-12 w-full cursor-pointer rounded-2xl border border-[#d8c6a1] bg-white p-1 @error('accent_color') is-invalid @enderror"
                    value="{{ old('accent_color', $popup?->accent_color ?? '#C8A45D') }}"
                >
                @error('accent_color')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </section>

    <aside class="space-y-6">
        <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-[#9b7431]">Sales Art</p>
            <h3 class="theme-display mt-2 text-2xl text-stone-950">Popup Image</h3>

            <div class="mt-5 space-y-4">
                <div>
                    <label for="image_file" class="form-label">Upload Image</label>
                    <input type="file" id="image_file" name="image_file" class="form-control @error('image_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp,.gif">
                    @error('image_file')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="image_url" class="form-label">Or External Image URL</label>
                    <input
                        type="url"
                        id="image_url"
                        name="image_url"
                        class="form-control @error('image_url') is-invalid @enderror"
                        value="{{ old('image_url', $popup?->image_url) }}"
                        placeholder="https://images.example.com/promo.jpg"
                    >
                    @error('image_url')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                @if($popup?->image_source_url)
                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-stone-700">
                        <input type="checkbox" name="remove_image" value="1" class="form-check-input">
                        Remove current image
                    </label>
                @endif
            </div>

            <div class="mt-5 overflow-hidden rounded-[1.4rem] border border-[#ecdcbc] bg-[#fffaf0]">
                <div class="aspect-[4/3] w-full bg-stone-100">
                    <img src="{{ $imagePreview }}" alt="Popup image preview" class="{{ $imagePreview ? '' : 'hidden ' }}h-full w-full object-cover" id="client-popup-image-preview">
                    <div class="{{ $imagePreview ? 'hidden ' : '' }}flex h-full items-center justify-center text-[11px] font-semibold uppercase tracking-[0.24em] text-stone-500" id="client-popup-image-placeholder">
                        Popup Image Preview
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-stone-800">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $popup?->is_active ?? true))>
                Show this popup on client dashboard
            </label>
            <p class="mt-3 text-sm leading-7 text-stone-600">You can keep popups saved as inactive and enable later.</p>
        </section>

        <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <button type="submit" class="btn btn-black w-100">
                <i class="fas fa-save me-2"></i>{{ $submitLabel }}
            </button>
            <a href="{{ route('admin.client-sales-popups.index') }}" class="btn btn-border-black mt-3 w-100">
                <i class="fas fa-arrow-left me-2"></i>Back to Client Popups
            </a>
        </section>
    </aside>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fileInput = document.getElementById('image_file');
            const urlInput = document.getElementById('image_url');
            const preview = document.getElementById('client-popup-image-preview');
            const placeholder = document.getElementById('client-popup-image-placeholder');
            const targetType = document.getElementById('target_type');
            const targetClientWrapper = document.getElementById('target_client_wrapper');
            const targetClientSelect = document.getElementById('target_client_id');

            const showPreview = function (src) {
                if (!preview || !placeholder) {
                    return;
                }

                if (!src) {
                    preview.classList.add('hidden');
                    placeholder.classList.remove('hidden');
                    return;
                }

                preview.src = src;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
            };

            const toggleTargetClientField = function () {
                if (!targetType || !targetClientWrapper) {
                    return;
                }

                const isSpecific = targetType.value === '{{ \App\Models\ClientSalesPopup::TARGET_SPECIFIC }}';
                targetClientWrapper.classList.toggle('hidden', !isSpecific);

                if (targetClientSelect) {
                    targetClientSelect.required = isSpecific;
                }
            };

            fileInput?.addEventListener('change', function () {
                const file = fileInput.files && fileInput.files[0];

                if (!file) {
                    showPreview(urlInput ? urlInput.value.trim() : '');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (event) {
                    showPreview(event.target.result);
                };
                reader.readAsDataURL(file);
            });

            urlInput?.addEventListener('input', function () {
                if (fileInput?.files && fileInput.files.length > 0) {
                    return;
                }

                showPreview(urlInput.value.trim());
            });

            targetType?.addEventListener('change', toggleTargetClientField);
            toggleTargetClientField();
        });
    </script>
@endpush
