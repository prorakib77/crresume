<x-app-layout>
    <x-slot name="title">FAQs</x-slot>
    <x-slot name="pageTitle">FAQs</x-slot>
    <x-slot name="pageSubtitle">Manage the questions and answers shown on the public FAQ page.</x-slot>

    <div class="space-y-6">
        <section class="rounded-[1.9rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-[#9b7431]">Website Content</p>
                    <h2 class="theme-display mt-2 text-3xl text-stone-950">Frequently Asked Questions</h2>
                </div>
                <a href="{{ route('admin.faqs.create') }}" class="btn btn-black">
                    <i class="fas fa-plus me-2"></i>Add FAQ
                </a>
            </div>
        </section>

        @if($faqs->isEmpty())
            <section class="rounded-[1.9rem] border border-dashed border-[#d8c6a1] bg-[#fffaf1] p-10 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full border border-[#e6d2ad] bg-white text-[#9b7431]">
                    <i class="fas fa-circle-question"></i>
                </div>
                <h3 class="theme-display text-2xl text-stone-900">No FAQs yet</h3>
                <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-stone-600">Add your first FAQ and it will appear on the public FAQ page when active.</p>
                <a href="{{ route('admin.faqs.create') }}" class="btn btn-black mt-5">
                    <i class="fas fa-plus me-2"></i>Create First FAQ
                </a>
            </section>
        @else
            <section class="grid gap-4 xl:grid-cols-2">
                @foreach($faqs as $faq)
                    <article class="rounded-[1.6rem] border border-[#e7dcc5] bg-white/95 p-5 shadow-[0_20px_48px_rgba(17,17,17,0.06)]">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full border border-[#e2d2af] bg-[#fbf5e8] text-sm font-bold text-[#9b7431]">
                                    {{ str_pad((string) ($loop->iteration + (($faqs->currentPage() - 1) * $faqs->perPage())), 2, '0', STR_PAD_LEFT) }}
                                </span>
                                <div class="min-w-0">
                                    <h3 class="theme-display text-xl leading-tight text-stone-950">{{ $faq->question }}</h3>
                                    <p class="mt-2 text-sm leading-7 text-stone-600">{{ \Illuminate\Support\Str::limit($faq->answer, 220) }}</p>
                                </div>
                            </div>
                            <span class="rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] {{ $faq->is_active ? 'border border-emerald-200 bg-emerald-50 text-emerald-700' : 'border border-stone-200 bg-stone-100 text-stone-500' }}">
                                {{ $faq->is_active ? 'Visible' : 'Hidden' }}
                            </span>
                        </div>

                        <div class="mt-4 flex items-center justify-between gap-3 border-t border-[#efe5d2] pt-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.15em] text-stone-500">
                                Order {{ $faq->sort_order }}
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.faqs.edit', $faq) }}" class="btn btn-border-black btn-sm">
                                    <i class="fas fa-pen me-1"></i>Edit
                                </a>
                                <form action="{{ route('admin.faqs.destroy', $faq) }}" method="POST" onsubmit="return confirm('Delete this FAQ item?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-trash me-1"></i>Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            <section class="rounded-[1.6rem] border border-[#e7dcc5] bg-white/95 px-4 py-3 shadow-[0_16px_38px_rgba(17,17,17,0.05)]">
                {{ $faqs->links() }}
            </section>
        @endif
    </div>
</x-app-layout>
