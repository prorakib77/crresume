<x-app-layout>
    <x-slot name="title">Countdown Sales</x-slot>
    <x-slot name="pageTitle">Countdown Sales</x-slot>
    <x-slot name="pageSubtitle">Create and manage timed sales campaigns for the welcome page.</x-slot>

    <div class="space-y-6">
        <section class="rounded-[1.9rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-[#9b7431]">Campaigns</p>
                    <h2 class="theme-display mt-2 text-3xl text-stone-950">Welcome Countdown Offers</h2>
                </div>
                <a href="{{ route('admin.sale-countdowns.create') }}" class="btn btn-black">
                    <i class="fas fa-plus me-2"></i>Add Countdown
                </a>
            </div>
        </section>

        @if($countdowns->isEmpty())
            <section class="rounded-[1.9rem] border border-dashed border-[#d8c6a1] bg-[#fffaf1] p-10 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full border border-[#e6d2ad] bg-white text-[#9b7431]">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <h3 class="theme-display text-2xl text-stone-900">No countdown campaigns yet</h3>
                <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-stone-600">Create your first campaign and publish timed sales on the welcome page.</p>
                <a href="{{ route('admin.sale-countdowns.create') }}" class="btn btn-black mt-5">
                    <i class="fas fa-plus me-2"></i>Create First Countdown
                </a>
            </section>
        @else
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($countdowns as $countdown)
                    @php
                        $image = $countdown->image_source_url;
                        $ended = optional($countdown->end_at)->isPast();
                    @endphp
                    <article class="overflow-hidden rounded-[1.6rem] border border-[#e7dcc5] bg-white/95 shadow-[0_20px_48px_rgba(17,17,17,0.06)]">
                        <div class="relative aspect-[16/10] bg-stone-100">
                            @if($image)
                                <img src="{{ $image }}" alt="{{ $countdown->title }}" class="h-full w-full object-cover" loading="lazy">
                            @else
                                <div class="flex h-full items-center justify-center bg-gradient-to-br from-[#f4ead6] via-[#fdf8ed] to-[#f0e2c2] text-[#9b7431]">
                                    <span class="text-[10px] font-semibold uppercase tracking-[0.2em]">Sales Art</span>
                                </div>
                            @endif

                            <span class="absolute left-3 top-3 inline-flex rounded-full border border-[#e6c987] bg-[#111111]/92 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-[#f6dda7]">
                                {{ $countdown->badge_text ?: 'Limited Time Deal' }}
                            </span>
                        </div>

                        <div class="space-y-4 p-5">
                            <div>
                                <h3 class="theme-display text-xl leading-tight text-stone-950">{{ $countdown->title }}</h3>
                                <p class="mt-2 text-xs font-semibold uppercase tracking-[0.18em] {{ $ended ? 'text-rose-700' : 'text-emerald-700' }}">
                                    {{ $ended ? 'Ended' : 'Ends' }} {{ optional($countdown->end_at)->timezone(config('app.timezone'))->format('M d, Y h:i A') }}
                                </p>
                            </div>

                            @if($countdown->offer_text)
                                <div class="rounded-2xl border border-[#ece4d5] bg-[#faf7f2] p-3 text-sm font-semibold text-stone-700">
                                    {{ $countdown->offer_text }}
                                </div>
                            @endif

                            <div class="flex items-center justify-between gap-3 border-t border-[#efe5d2] pt-4">
                                <div class="text-xs font-semibold uppercase tracking-[0.15em] {{ $countdown->is_active ? 'text-emerald-700' : 'text-stone-500' }}">
                                    <i class="fas {{ $countdown->is_active ? 'fa-eye' : 'fa-eye-slash' }} me-1"></i>{{ $countdown->is_active ? 'Visible' : 'Hidden' }}
                                </div>
                                <div class="text-xs font-semibold uppercase tracking-[0.15em] text-stone-500">
                                    Order {{ $countdown->sort_order }}
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <a href="{{ route('admin.sale-countdowns.edit', $countdown) }}" class="btn btn-border-black btn-sm">
                                    <i class="fas fa-pen me-1"></i>Edit
                                </a>
                                <form action="{{ route('admin.sale-countdowns.destroy', $countdown) }}" method="POST" onsubmit="return confirm('Delete this countdown campaign?');">
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
                {{ $countdowns->links() }}
            </section>
        @endif
    </div>
</x-app-layout>
