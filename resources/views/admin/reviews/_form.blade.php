@php
    /** @var \App\Models\Review|null $review */
    $review = $review ?? null;
    $imagePreview = old('image_url', $review?->image_source_url);
@endphp

<div class="grid gap-6 lg:grid-cols-[minmax(0,1.45fr)_minmax(0,0.85fr)]">
    <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#efe5d2] pb-4">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-[#9b7431]">Review Card</p>
                <h2 class="theme-display mt-2 text-3xl text-stone-950">Customer Details</h2>
            </div>
            <span class="rounded-full border border-[#d8c6a1] bg-[#fbf5e8] px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-[#9b7431]">
                Welcome Reviews
            </span>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div>
                <label for="customer_name" class="form-label">Customer Name</label>
                <input
                    type="text"
                    id="customer_name"
                    name="customer_name"
                    class="form-control @error('customer_name') is-invalid @enderror"
                    value="{{ old('customer_name', $review?->customer_name) }}"
                    maxlength="120"
                    required
                >
                @error('customer_name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="country_label" class="form-label">Country Label</label>
                <input
                    type="text"
                    id="country_label"
                    name="country_label"
                    class="form-control @error('country_label') is-invalid @enderror"
                    value="{{ old('country_label', $review?->country_label ?? 'US') }}"
                    maxlength="16"
                    placeholder="US"
                >
                @error('country_label')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="headline" class="form-label">Review Headline</label>
                <input
                    type="text"
                    id="headline"
                    name="headline"
                    class="form-control @error('headline') is-invalid @enderror"
                    value="{{ old('headline', $review?->headline) }}"
                    maxlength="190"
                    required
                >
                @error('headline')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="review_text" class="form-label">Review Content</label>
                <textarea
                    id="review_text"
                    name="review_text"
                    class="form-control @error('review_text') is-invalid @enderror"
                    rows="6"
                    maxlength="4000"
                    required
                >{{ old('review_text', $review?->review_text) }}</textarea>
                @error('review_text')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="product_name" class="form-label">Product Label (Optional)</label>
                <input
                    type="text"
                    id="product_name"
                    name="product_name"
                    class="form-control @error('product_name') is-invalid @enderror"
                    value="{{ old('product_name', $review?->product_name) }}"
                    maxlength="190"
                    placeholder="Q-Rejuvalight Pro LED Mask"
                >
                @error('product_name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="product_link" class="form-label">Product URL (Optional)</label>
                <input
                    type="url"
                    id="product_link"
                    name="product_link"
                    class="form-control @error('product_link') is-invalid @enderror"
                    value="{{ old('product_link', $review?->product_link) }}"
                    placeholder="https://example.com/product"
                >
                @error('product_link')
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
                    value="{{ old('sort_order', $review?->sort_order ?? 0) }}"
                >
                @error('sort_order')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex flex-col justify-end gap-3 pb-1">
                <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-stone-800">
                    <input type="checkbox" name="is_verified" value="1" class="form-check-input" @checked(old('is_verified', $review?->is_verified ?? true))>
                    Verified customer badge
                </label>

                <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-stone-800">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $review?->is_active ?? true))>
                    Show this card on welcome page
                </label>
            </div>
        </div>
    </section>

    <aside class="space-y-6">
        <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-[#9b7431]">Review Image</p>
            <h3 class="theme-display mt-2 text-2xl text-stone-950">Upload Image</h3>

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
                        value="{{ old('image_url', $review?->before_image_url ?: $review?->after_image_url) }}"
                        placeholder="https://images.example.com/review.jpg"
                    >
                    @error('image_url')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                @if($review?->image_source_url)
                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-stone-700">
                        <input type="checkbox" name="remove_image" value="1" class="form-check-input">
                        Remove current image
                    </label>
                @endif
            </div>

            <div class="mt-5 overflow-hidden rounded-[1.4rem] border border-[#ecdcbc] bg-[#fffaf0]">
                <div class="aspect-[4/3] w-full bg-stone-100">
                    <img src="{{ $imagePreview }}" alt="Review preview" class="{{ $imagePreview ? '' : 'hidden ' }}h-full w-full object-cover" id="review-image-preview">
                    <div class="{{ $imagePreview ? 'hidden ' : '' }}flex h-full items-center justify-center text-[11px] font-semibold uppercase tracking-[0.24em] text-stone-500" id="review-image-placeholder">
                        Review Image Preview
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <button type="submit" class="btn btn-black w-100">
                <i class="fas fa-save me-2"></i>{{ $submitLabel }}
            </button>
            <a href="{{ route('admin.reviews.index') }}" class="btn btn-border-black mt-3 w-100">
                <i class="fas fa-arrow-left me-2"></i>Back to Reviews
            </a>
        </section>
    </aside>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fileInput = document.getElementById('image_file');
            const urlInput = document.getElementById('image_url');
            const preview = document.getElementById('review-image-preview');
            const placeholder = document.getElementById('review-image-placeholder');

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
