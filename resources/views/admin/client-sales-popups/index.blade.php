<x-app-layout>
    <x-slot name="title">Client Sales Popups</x-slot>
    <x-slot name="pageTitle">Client Sales Popups</x-slot>
    <x-slot name="pageSubtitle">Manage sales popup campaigns that appear only on client dashboard.</x-slot>

    <div class="space-y-6">
        <section class="rounded-[1.9rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-[#9b7431]">Campaigns</p>
                    <h2 class="theme-display mt-2 text-3xl text-stone-950">Client Dashboard Popups</h2>
                </div>
                <a href="{{ route('admin.client-sales-popups.create') }}" class="btn btn-black">
                    <i class="fas fa-plus me-2"></i>Add Popup
                </a>
            </div>
        </section>

        @if($popups->isEmpty())
            <section class="rounded-[1.9rem] border border-dashed border-[#d8c6a1] bg-[#fffaf1] p-10 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full border border-[#e6d2ad] bg-white text-[#9b7431]">
                    <i class="fas fa-window-maximize"></i>
                </div>
                <h3 class="theme-display text-2xl text-stone-900">No popup campaigns yet</h3>
                <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-stone-600">Create a recurring sales popup or target one client with a dedicated offer.</p>
                <a href="{{ route('admin.client-sales-popups.create') }}" class="btn btn-black mt-5">
                    <i class="fas fa-plus me-2"></i>Create First Popup
                </a>
            </section>
        @else
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($popups as $popup)
                    @php
                        $image = $popup->image_source_url;
                        $targetLabel = $targetTypeOptions[$popup->target_type] ?? ucfirst((string) $popup->target_type);
                        $liveNow = (!$popup->starts_at || $popup->starts_at->isPast()) && (!$popup->ends_at || $popup->ends_at->isFuture());
                    @endphp
                    <article class="overflow-hidden rounded-[1.6rem] border border-[#e7dcc5] bg-white/95 shadow-[0_20px_48px_rgba(17,17,17,0.06)]">
                        <div class="relative aspect-[16/10] bg-stone-100">
                            @if($image)
                                <img src="{{ $image }}" alt="{{ $popup->title }}" class="h-full w-full object-cover" loading="lazy">
                            @else
                                <div class="flex h-full items-center justify-center bg-gradient-to-br from-[#f4ead6] via-[#fdf8ed] to-[#f0e2c2] text-[#9b7431]">
                                    <span class="text-[10px] font-semibold uppercase tracking-[0.2em]">Client Popup Art</span>
                                </div>
                            @endif

                            <span class="absolute left-3 top-3 inline-flex rounded-full border border-[#e6c987] bg-[#111111]/92 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-[#f6dda7]">
                                {{ $popup->badge_text ?: 'Exclusive Offer' }}
                            </span>
                        </div>

                        <div class="space-y-4 p-5">
                            <div>
                                <h3 class="theme-display text-xl leading-tight text-stone-950">{{ $popup->title }}</h3>
                                <p class="mt-2 text-xs font-semibold uppercase tracking-[0.18em] text-stone-600">
                                    {{ $targetLabel }}
                                    @if($popup->target_type === \App\Models\ClientSalesPopup::TARGET_SPECIFIC && $popup->client)
                                        - {{ $popup->client->name }}
                                    @endif
                                </p>
                            </div>

                            @if($popup->price_text)
                                <div class="rounded-2xl border border-[#ece4d5] bg-[#faf7f2] p-3 text-sm font-semibold text-stone-700">
                                    {{ $popup->price_text }}
                                </div>
                            @endif

                            <div class="grid gap-1 text-xs text-stone-600">
                                <span>Delay: {{ $popup->show_delay }}s</span>
                                @if($popup->starts_at)
                                    <span>Starts: {{ $popup->starts_at->timezone(config('app.timezone'))->format('M d, Y h:i A') }}</span>
                                @endif
                                @if($popup->ends_at)
                                    <span>Ends: {{ $popup->ends_at->timezone(config('app.timezone'))->format('M d, Y h:i A') }}</span>
                                @endif
                            </div>

                            <div class="flex items-center justify-between gap-3 border-t border-[#efe5d2] pt-4">
                                <div class="text-xs font-semibold uppercase tracking-[0.15em] {{ $popup->is_active ? 'text-emerald-700' : 'text-stone-500' }}">
                                    <i class="fas {{ $popup->is_active ? 'fa-eye' : 'fa-eye-slash' }} me-1"></i>{{ $popup->is_active ? 'Visible' : 'Hidden' }}
                                </div>
                                <div class="text-xs font-semibold uppercase tracking-[0.15em] {{ $liveNow ? 'text-emerald-700' : 'text-rose-700' }}">
                                    {{ $liveNow ? 'Live Window' : 'Out of Window' }}
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <a href="{{ route('admin.client-sales-popups.edit', $popup) }}" class="btn btn-border-black btn-sm">
                                    <i class="fas fa-pen me-1"></i>Edit
                                </a>
                                <form action="{{ route('admin.client-sales-popups.destroy', $popup) }}" method="POST" onsubmit="return confirm('Delete this popup campaign?');">
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
                {{ $popups->links() }}
            </section>
        @endif
    </div>
</x-app-layout>
