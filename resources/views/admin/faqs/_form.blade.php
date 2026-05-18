@php
    /** @var \App\Models\Faq|null $faq */
    $faq = $faq ?? null;
@endphp

<div class="grid gap-6 lg:grid-cols-[minmax(0,1.45fr)_minmax(0,0.85fr)]">
    <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#efe5d2] pb-4">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-[#9b7431]">FAQ Content</p>
                <h2 class="theme-display mt-2 text-3xl text-stone-950">Question & Answer</h2>
            </div>
            <span class="rounded-full border border-[#d8c6a1] bg-[#fbf5e8] px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-[#9b7431]">
                Public FAQ Page
            </span>
        </div>

        <div class="mt-6 grid gap-4">
            <div>
                <label for="question" class="form-label">Question</label>
                <input
                    type="text"
                    id="question"
                    name="question"
                    class="form-control @error('question') is-invalid @enderror"
                    value="{{ old('question', $faq?->question) }}"
                    maxlength="255"
                    required
                >
                @error('question')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="answer" class="form-label">Answer</label>
                <textarea
                    id="answer"
                    name="answer"
                    class="form-control @error('answer') is-invalid @enderror"
                    rows="12"
                    maxlength="12000"
                    required
                >{{ old('answer', $faq?->answer) }}</textarea>
                <p class="mt-2 text-xs text-stone-500">Line breaks are supported and will show cleanly on the public FAQ page.</p>
                @error('answer')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </section>

    <aside class="space-y-6">
        <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-[#9b7431]">Publishing</p>
            <h3 class="theme-display mt-2 text-2xl text-stone-950">Display Settings</h3>

            <div class="mt-5 space-y-4">
                <div>
                    <label for="sort_order" class="form-label">Sort Order</label>
                    <input
                        type="number"
                        id="sort_order"
                        name="sort_order"
                        class="form-control @error('sort_order') is-invalid @enderror"
                        min="0"
                        value="{{ old('sort_order', $faq?->sort_order ?? 0) }}"
                    >
                    @error('sort_order')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-stone-800">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $faq?->is_active ?? true))>
                    Show this FAQ on the website
                </label>
            </div>
        </section>

        <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <button type="submit" class="btn btn-black w-100">
                <i class="fas fa-save me-2"></i>{{ $submitLabel }}
            </button>
            <a href="{{ route('admin.faqs.index') }}" class="btn btn-border-black mt-3 w-100">
                <i class="fas fa-arrow-left me-2"></i>Back to FAQs
            </a>
        </section>
    </aside>
</div>
