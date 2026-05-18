<x-app-layout>
    <x-slot name="title">Review Cards</x-slot>
    <x-slot name="pageTitle">Review Cards</x-slot>
    <x-slot name="pageSubtitle">Manage testimonial cards shown in the welcome page slider.</x-slot>

    <div class="space-y-6">
        <section class="rounded-[1.9rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-[#9b7431]">Testimonials</p>
                    <h2 class="theme-display mt-2 text-3xl text-stone-950">Review Slider Cards</h2>
                </div>
                <a href="{{ route('admin.reviews.create') }}" class="btn btn-black">
                    <i class="fas fa-plus me-2"></i>Add Review Card
                </a>
            </div>
        </section>

        @if($reviews->isEmpty())
            <section class="rounded-[1.9rem] border border-dashed border-[#d8c6a1] bg-[#fffaf1] p-10 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full border border-[#e6d2ad] bg-white text-[#9b7431]">
                    <i class="fas fa-comments"></i>
                </div>
                <h3 class="theme-display text-2xl text-stone-900">No review cards yet</h3>
                <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-stone-600">Create cards with a review image and they will show in the welcome page review slider when active.</p>
                <a href="{{ route('admin.reviews.create') }}" class="btn btn-black mt-5">
                    <i class="fas fa-plus me-2"></i>Create First Review Card
                </a>
            </section>
        @else
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($reviews as $review)
                    @php
                        $image = $review->image_source_url;
                    @endphp
                    <article class="overflow-hidden rounded-[1.6rem] border border-[#e7dcc5] bg-white/95 shadow-[0_20px_48px_rgba(17,17,17,0.06)]">
                        <div class="relative aspect-[16/10] bg-stone-100">
                            @if($image)
                                <img src="{{ $image }}" alt="Review image - {{ $review->customer_name }}" class="h-full w-full object-cover" loading="lazy">
                            @else
                                <div class="flex h-full items-center justify-center bg-gradient-to-br from-[#f4ead6] via-[#fdf8ed] to-[#f0e2c2] text-[#9b7431]">
                                    <span class="text-[10px] font-semibold uppercase tracking-[0.2em]">Review Image</span>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-4 p-5">
                            <div class="flex items-center justify-between gap-3">
                                <div class="inline-flex items-center gap-2">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-[#e5d6b7] bg-[#fff6e5] text-[10px] font-bold text-[#8b6728]">{{ $review->country_label ?: 'US' }}</span>
                                    <span class="text-sm font-semibold text-stone-900">{{ $review->customer_name }}</span>
                                </div>
                                @if($review->is_verified)
                                    <span class="inline-flex items-center rounded-full border border-[#d6eadf] bg-[#eef8f2] px-3 py-1 text-[10px] font-semibold text-emerald-700">
                                        <i class="fas fa-check-circle me-1"></i>Verified
                                    </span>
                                @endif
                            </div>

                            <div>
                                <h3 class="theme-display text-xl leading-tight text-stone-950">{{ $review->headline }}</h3>
                                <p class="mt-2 line-clamp-4 text-sm leading-7 text-stone-600">{{ $review->review_text }}</p>
                            </div>

                            @if($review->product_name)
                                <div class="rounded-2xl border border-[#ece4d5] bg-[#faf7f2] p-3">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-stone-500">{{ $review->customer_name }} uses...</p>
                                    @if($review->product_link)
                                        <a href="{{ $review->product_link }}" target="_blank" rel="noopener noreferrer" class="mt-1 inline-block text-sm font-semibold text-stone-900 underline decoration-[#c8a45d] decoration-2 underline-offset-4">{{ $review->product_name }}</a>
                                    @else
                                        <p class="mt-1 text-sm font-semibold text-stone-900">{{ $review->product_name }}</p>
                                    @endif
                                </div>
                            @endif

                            <div class="flex items-center justify-between gap-3 border-t border-[#efe5d2] pt-4">
                                <div class="text-xs font-semibold uppercase tracking-[0.15em] {{ $review->is_active ? 'text-emerald-700' : 'text-stone-500' }}">
                                    <i class="fas {{ $review->is_active ? 'fa-eye' : 'fa-eye-slash' }} me-1"></i>{{ $review->is_active ? 'Visible' : 'Hidden' }}
                                </div>
                                <div class="text-xs font-semibold uppercase tracking-[0.15em] text-stone-500">
                                    Order {{ $review->sort_order }}
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <a href="{{ route('admin.reviews.edit', $review) }}" class="btn btn-border-black btn-sm">
                                    <i class="fas fa-pen me-1"></i>Edit
                                </a>
                                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Delete this review card?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                        <i class="fas fa-trash me-1"></i>Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            <section class="rounded-[1.6rem] border border-[#e7dcc5] bg-white/95 px-4 py-3 shadow-[0_16px_38px_rgba(17,17,17,0.05)]">
                {{ $reviews->links() }}
            </section>
        @endif
    </div>
</x-app-layout>
