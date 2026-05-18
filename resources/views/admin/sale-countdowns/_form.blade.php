@php
    /** @var \App\Models\SaleCountdown|null $saleCountdown */
    $saleCountdown = $saleCountdown ?? null;
    $imagePreview = old('image_url', $saleCountdown?->image_source_url);
@endphp

<div class="grid gap-6 lg:grid-cols-[minmax(0,1.45fr)_minmax(0,0.85fr)]">
    <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#efe5d2] pb-4">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-[#9b7431]">Countdown Sale</p>
                <h2 class="theme-display mt-2 text-3xl text-stone-950">Campaign Details</h2>
            </div>
            <span class="rounded-full border border-[#d8c6a1] bg-[#fbf5e8] px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-[#9b7431]">
                Welcome Page
            </span>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div>
                <label for="sort_order" class="form-label">Sort Order</label>
                <input
                    type="number"
                    id="sort_order"
                    name="sort_order"
                    class="form-control @error('sort_order') is-invalid @enderror"
                    min="0"
                    value="{{ old('sort_order', $saleCountdown?->sort_order ?? 0) }}"
                >
                @error('sort_order')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="end_at" class="form-label">End Date & Time</label>
                <input
                    type="datetime-local"
                    id="end_at"
                    name="end_at"
                    class="form-control @error('end_at') is-invalid @enderror"
                    value="{{ old('end_at', optional($saleCountdown?->end_at)->format('Y-m-d\TH:i')) }}"
                    required
                >
                @error('end_at')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="title" class="form-label">Title</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    class="form-control @error('title') is-invalid @enderror"
                    value="{{ old('title', $saleCountdown?->title) }}"
                    maxlength="190"
                    required
                >
                @error('title')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="subtitle" class="form-label">Subtitle</label>
                <textarea
                    id="subtitle"
                    name="subtitle"
                    class="form-control @error('subtitle') is-invalid @enderror"
                    rows="4"
                    maxlength="2000"
                    placeholder="Add short details about the sales offer..."
                >{{ old('subtitle', $saleCountdown?->subtitle) }}</textarea>
                @error('subtitle')
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
                    value="{{ old('badge_text', $saleCountdown?->badge_text ?? 'Limited Time Deal') }}"
                    maxlength="120"
                >
                @error('badge_text')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="offer_text" class="form-label">Offer Text</label>
                <input
                    type="text"
                    id="offer_text"
                    name="offer_text"
                    class="form-control @error('offer_text') is-invalid @enderror"
                    value="{{ old('offer_text', $saleCountdown?->offer_text) }}"
                    maxlength="190"
                    placeholder="e.g. Save up to 30% today"
                >
                @error('offer_text')
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
                    value="{{ old('cta_label', $saleCountdown?->cta_label ?? 'Book Now') }}"
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
                    value="{{ old('cta_link', $saleCountdown?->cta_link ?? '#') }}"
                    placeholder="https://example.com/sales"
                >
                @error('cta_link')
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
                    value="{{ old('bg_color', $saleCountdown?->bg_color ?? '#111111') }}"
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
                    value="{{ old('text_color', $saleCountdown?->text_color ?? '#FFFFFF') }}"
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
                    value="{{ old('accent_color', $saleCountdown?->accent_color ?? '#C8A45D') }}"
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
            <h3 class="theme-display mt-2 text-2xl text-stone-950">Countdown Image</h3>

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
                        value="{{ old('image_url', $saleCountdown?->image_url) }}"
                        placeholder="https://images.example.com/sales.jpg"
                    >
                    @error('image_url')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                @if($saleCountdown?->image_source_url)
                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-stone-700">
                        <input type="checkbox" name="remove_image" value="1" class="form-check-input">
                        Remove current image
                    </label>
                @endif
            </div>

            <div class="mt-5 overflow-hidden rounded-[1.4rem] border border-[#ecdcbc] bg-[#fffaf0]">
                <div class="aspect-[4/3] w-full bg-stone-100">
                    <img src="{{ $imagePreview }}" alt="Sales art preview" class="{{ $imagePreview ? '' : 'hidden ' }}h-full w-full object-cover" id="countdown-image-preview">
                    <div class="{{ $imagePreview ? 'hidden ' : '' }}flex h-full items-center justify-center text-[11px] font-semibold uppercase tracking-[0.24em] text-stone-500" id="countdown-image-placeholder">
                        Countdown Image Preview
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-stone-800">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $saleCountdown?->is_active ?? true))>
                Show this countdown on welcome page
            </label>
            <p class="mt-3 text-sm leading-7 text-stone-600">Inactive countdowns stay in admin and can be reused later.</p>
        </section>

        <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <button type="submit" class="btn btn-black w-100">
                <i class="fas fa-save me-2"></i>{{ $submitLabel }}
            </button>
            <a href="{{ route('admin.sale-countdowns.index') }}" class="btn btn-border-black mt-3 w-100">
                <i class="fas fa-arrow-left me-2"></i>Back to Countdown Sales
            </a>
        </section>
    </aside>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fileInput = document.getElementById('image_file');
            const urlInput = document.getElementById('image_url');
            const preview = document.getElementById('countdown-image-preview');
            const placeholder = document.getElementById('countdown-image-placeholder');

            if (!fileInput || !preview || !placeholder) {
                return;
            }

            const showPreview = function (src) {
                if (!src) {
                    preview.classList.add('hidden');
                    placeholder.classList.remove('hidden');
                    return;
                }

                preview.src = src;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
            };

            fileInput.addEventListener('change', function () {
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
                if (fileInput.files && fileInput.files.length > 0) {
                    return;
                }

                showPreview(urlInput.value.trim());
            });
        });
    </script>
@endpush
