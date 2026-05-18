<x-app-layout>
    <x-slot name="title">PDF Templates</x-slot>
    <x-slot name="pageTitle">PDF Templates</x-slot>
    <x-slot name="pageSubtitle">Edit the content of every PDF export from one place.</x-slot>

    <div class="space-y-6">
        <section class="rounded-[1.9rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-[#9b7431]">PDF Library</p>
                    <h2 class="theme-display mt-2 text-3xl text-stone-950">System PDF Templates</h2>
                    <p class="mt-3 max-w-3xl text-sm text-stone-600">Manage titles, table headers, empty states, footer notes, and other static copy for all active PDF exports.</p>
                </div>
                <a href="{{ route('admin.customization.section', ['section' => 'pdf']) }}" class="btn btn-border-black">
                    <i class="fas fa-palette me-2"></i>Open PDF Customizer
                </a>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach($templates as $template)
                <article class="rounded-[1.5rem] border border-[#e7dcc5] bg-white/95 p-5 shadow-[0_18px_40px_rgba(17,17,17,0.05)]">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#9b7431]">{{ $template['key'] }}</p>
                            <h3 class="theme-display mt-2 text-xl text-stone-950">{{ $template['name'] }}</h3>
                        </div>
                        <span class="rounded-full bg-[#fbf5e8] px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-stone-700">
                            {{ count($template['fields']) }} fields
                        </span>
                    </div>

                    <p class="mt-3 text-sm leading-6 text-stone-600">{{ $template['description'] }}</p>

                    <div class="mt-4 rounded-xl border border-[#efe5d2] bg-[#faf7f2] p-3">
                        <p class="mb-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-stone-500">Tokens</p>
                        <p class="text-xs leading-6 text-stone-700">{{ implode(', ', $template['tokens']) }}</p>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('admin.pdf-templates.edit', ['template' => $template['key']]) }}" class="btn btn-black btn-sm w-100">
                            <i class="fas fa-pen me-2"></i>Edit Template
                        </a>
                    </div>
                </article>
            @endforeach
        </section>
    </div>
</x-app-layout>
