@php
    /** @var \App\Models\Product|null $product */
    $product = $product ?? null;
    $imagePreview = old('image_url', $product?->image_source_url);
@endphp

<div class="grid gap-6 lg:grid-cols-[minmax(0,1.45fr)_minmax(0,0.85fr)]">
    <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#efe5d2] pb-4">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-[#9b7431]">Product Card</p>
                <h2 class="theme-display mt-2 text-3xl text-stone-950">Card Details</h2>
            </div>
            <span class="rounded-full border border-[#d8c6a1] bg-[#fbf5e8] px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-[#9b7431]">
                Welcome Slider
            </span>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div>
                <label for="type" class="form-label">Product Type</label>
                <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                    @foreach($typeOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('type', $product?->type ?? $selectedType) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')
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
                    value="{{ old('sort_order', $product?->sort_order ?? 0) }}"
                >
                @error('sort_order')
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
                    value="{{ old('title', $product?->title) }}"
                    maxlength="190"
                    required
                >
                @error('title')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="badge_text" class="form-label">Badge Text</label>
                <input
                    type="text"
                    id="badge_text"
                    name="badge_text"
                    class="form-control @error('badge_text') is-invalid @enderror"
                    value="{{ old('badge_text', $product?->badge_text ?? 'ONLY ONE SPOT LEFT') }}"
                    maxlength="120"
                >
                @error('badge_text')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="regular_price" class="form-label">Regular Price</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    id="regular_price"
                    name="regular_price"
                    class="form-control @error('regular_price') is-invalid @enderror"
                    value="{{ old('regular_price', $product?->regular_price) }}"
                >
                @error('regular_price')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="sale_price" class="form-label">Sale Price</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    id="sale_price"
                    name="sale_price"
                    class="form-control @error('sale_price') is-invalid @enderror"
                    value="{{ old('sale_price', $product?->sale_price) }}"
                    required
                >
                @error('sale_price')
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
                    value="{{ old('cta_label', $product?->cta_label ?? 'Buy Now') }}"
                    maxlength="40"
                >
                @error('cta_label')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="cta_link" class="form-label">Buy Now URL</label>
                <input
                    type="text"
                    id="cta_link"
                    name="cta_link"
                    class="form-control @error('cta_link') is-invalid @enderror"
                    value="{{ old('cta_link', $product?->cta_link ?? '#') }}"
                    placeholder="https://example.com/checkout"
                >
                @error('cta_link')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </section>

    <aside class="space-y-6">
        <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-[#9b7431]">Image</p>
            <h3 class="theme-display mt-2 text-2xl text-stone-950">Card Image</h3>

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
                        value="{{ old('image_url', $product?->image_url) }}"
                        placeholder="https://images.example.com/product.jpg"
                    >
                    @error('image_url')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                @if($product?->image_source_url)
                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-stone-700">
                        <input type="checkbox" name="remove_image" value="1" class="form-check-input">
                        Remove current image
                    </label>
                @endif
            </div>

            <div class="mt-5 overflow-hidden rounded-[1.4rem] border border-[#ecdcbc] bg-[#fffaf0]">
                <div class="aspect-[4/3] w-full bg-stone-100">
                    <img src="{{ $imagePreview }}" alt="Preview" class="{{ $imagePreview ? '' : 'hidden ' }}h-full w-full object-cover" id="product-image-preview">
                    <div class="{{ $imagePreview ? 'hidden ' : '' }}flex h-full items-center justify-center text-[11px] font-semibold uppercase tracking-[0.24em] text-stone-500" id="product-image-placeholder">
                        Product Image Preview
                    </div>
                </div>
                <div class="border-t border-[#ecdcbc] bg-white/90 px-4 py-3 text-sm text-stone-600">
                    Add a clean image to make the hero slider look polished.
                </div>
            </div>
        </section>

        <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-stone-800">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $product?->is_active ?? true))>
                Show this card on welcome page
            </label>
            <p class="mt-3 text-sm leading-7 text-stone-600">Inactive cards stay in admin but are hidden from the public slider.</p>
        </section>

        <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <button type="submit" class="btn btn-black w-100">
                <i class="fas fa-save me-2"></i>{{ $submitLabel }}
            </button>
            <a href="{{ route('admin.products.index', ['type' => old('type', $product?->type ?? $selectedType)]) }}" class="btn btn-border-black mt-3 w-100">
                <i class="fas fa-arrow-left me-2"></i>Back to Products
            </a>
        </section>
    </aside>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const imageFileInput = document.getElementById('image_file');
            const imageUrlInput = document.getElementById('image_url');
            const preview = document.getElementById('product-image-preview');
            const placeholder = document.getElementById('product-image-placeholder');

            if (!imageFileInput || !preview || !placeholder) {
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

            imageFileInput.addEventListener('change', function () {
                const file = imageFileInput.files && imageFileInput.files[0];

                if (!file) {
                    showPreview(imageUrlInput ? imageUrlInput.value.trim() : '');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (event) {
                    showPreview(event.target.result);
                };
                reader.readAsDataURL(file);
            });

            imageUrlInput?.addEventListener('input', function () {
                if (imageFileInput.files && imageFileInput.files.length > 0) {
                    return;
                }

                showPreview(imageUrlInput.value.trim());
            });
        });
    </script>
@endpush
