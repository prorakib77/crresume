<x-app-layout>
    <x-slot name="title">Product Cards</x-slot>
    <x-slot name="pageTitle">Product Cards</x-slot>
    <x-slot name="pageSubtitle">Manage welcome-page slider cards and external Buy Now links.</x-slot>

    @php
        $currentTypeLabel = $typeOptions[$selectedType] ?? 'Products';
    @endphp

    <div class="space-y-6">
        <section class="rounded-[1.9rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-[#9b7431]">Catalog</p>
                    <h2 class="theme-display mt-2 text-3xl text-stone-950">{{ $currentTypeLabel }}</h2>
                </div>
                <a href="{{ route('admin.products.create', ['type' => $selectedType]) }}" class="btn btn-black">
                    <i class="fas fa-plus me-2"></i>Add Product Card
                </a>
            </div>

            <div class="mt-5 flex flex-wrap gap-2">
                @foreach($typeOptions as $value => $label)
                    <a
                        href="{{ route('admin.products.index', ['type' => $value]) }}"
                        class="inline-flex items-center rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] transition {{ $selectedType === $value ? 'border-black bg-black text-white' : 'border-[#d8c6a1] bg-[#fffaf1] text-stone-700 hover:scale-[1.02]' }}"
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </section>

        @if($products->isEmpty())
            <section class="rounded-[1.9rem] border border-dashed border-[#d8c6a1] bg-[#fffaf1] p-10 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full border border-[#e6d2ad] bg-white text-[#9b7431]">
                    <i class="fas fa-box-open"></i>
                </div>
                <h3 class="theme-display text-2xl text-stone-900">No product cards yet</h3>
                <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-stone-600">Create your first card and it will appear in the welcome page hero slider immediately when active.</p>
                <a href="{{ route('admin.products.create', ['type' => $selectedType]) }}" class="btn btn-black mt-5">
                    <i class="fas fa-plus me-2"></i>Create First Card
                </a>
            </section>
        @else
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($products as $product)
                    @php
                        $imageUrl = $product->image_source_url;
                    @endphp
                    <article class="overflow-hidden rounded-[1.6rem] border border-[#e7dcc5] bg-white/95 shadow-[0_20px_48px_rgba(17,17,17,0.06)]">
                        <div class="relative aspect-[4/3] bg-stone-100">
                            @if($imageUrl)
                                <img src="{{ $imageUrl }}" alt="{{ $product->title }}" class="h-full w-full object-cover" loading="lazy">
                            @else
                                <div class="flex h-full items-center justify-center bg-gradient-to-br from-[#f4ead6] via-[#fdf8ed] to-[#f0e2c2] text-[#9b7431]">
                                    <span class="text-xs font-semibold uppercase tracking-[0.26em]">No Image</span>
                                </div>
                            @endif

                            <span class="absolute left-3 top-3 inline-flex rounded-full border border-[#e6c987] bg-[#111111]/92 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-[#f6dda7]">
                                {{ $product->badge_text ?: 'ONLY ONE SPOT LEFT' }}
                            </span>
                        </div>

                        <div class="space-y-4 p-5">
                            <div>
                                <h3 class="theme-display text-xl leading-tight text-stone-950">{{ $product->title }}</h3>
                                <p class="mt-2 text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">{{ $typeOptions[$product->type] ?? ucfirst(str_replace('_', ' ', $product->type)) }}</p>
                            </div>

                            <div class="flex items-end justify-between gap-3">
                                <div class="space-y-1">
                                    @if(!is_null($product->regular_price))
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Regular Price</p>
                                        <p class="text-base font-semibold text-stone-500 line-through">${{ number_format((float) $product->regular_price, 2) }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Sale Price</p>
                                    <p class="theme-display text-2xl text-stone-950">${{ number_format((float) $product->sale_price, 2) }}</p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-3 border-t border-[#efe5d2] pt-4">
                                <div class="text-xs font-semibold uppercase tracking-[0.15em] {{ $product->is_active ? 'text-emerald-700' : 'text-stone-500' }}">
                                    <i class="fas {{ $product->is_active ? 'fa-eye' : 'fa-eye-slash' }} me-1"></i>{{ $product->is_active ? 'Visible' : 'Hidden' }}
                                </div>
                                <div class="text-xs font-semibold uppercase tracking-[0.15em] text-stone-500">
                                    Order {{ $product->sort_order }}
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-border-black btn-sm">
                                    <i class="fas fa-pen me-1"></i>Edit
                                </a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete this product card?');">
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
                {{ $products->links() }}
            </section>
        @endif
    </div>
</x-app-layout>
