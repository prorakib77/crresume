<x-app-layout>
    <x-slot name="title">Edit PDF Template</x-slot>
    <x-slot name="pageTitle">Edit PDF Template</x-slot>
    <x-slot name="pageSubtitle">{{ $templateDefinition['name'] }}</x-slot>

    @php
        $settingValue = function (string $key) use ($settings) {
            $storedValue = $settings->get($key)?->setting_value;

            if ($storedValue === null || $storedValue === '') {
                return \App\Models\CustomizationSetting::defaultValue($key, '');
            }

            return $storedValue;
        };
    @endphp

    <form method="POST" action="{{ route('admin.pdf-templates.update', ['template' => $templateDefinition['key']]) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="rounded-[1.9rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#9b7431]">{{ $templateDefinition['key'] }}</p>
                    <h2 class="theme-display mt-2 text-3xl text-stone-950">{{ $templateDefinition['name'] }}</h2>
                    <p class="mt-3 text-sm text-stone-600">{{ $templateDefinition['description'] }}</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('admin.pdf-templates.index') }}" class="btn btn-border-black">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                    <a href="{{ route('admin.customization.section', ['section' => 'pdf']) }}" class="btn btn-white">
                        <i class="fas fa-palette me-2"></i>Global PDF Style
                    </a>
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.45fr)_minmax(0,0.85fr)]">
            <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
                <div class="grid gap-4 xl:grid-cols-2">
                    @foreach($templateDefinition['fields'] as $field)
                        <div class="{{ $field['type'] === 'textarea' ? 'xl:col-span-2' : '' }}">
                            <label for="{{ $field['key'] }}" class="form-label">{{ $field['label'] }}</label>
                            @if($field['type'] === 'textarea')
                                <textarea
                                    id="{{ $field['key'] }}"
                                    name="{{ $field['key'] }}"
                                    rows="{{ $field['rows'] ?? 3 }}"
                                    class="form-control @error($field['key']) is-invalid @enderror"
                                    maxlength="{{ $field['max'] ?? 5000 }}"
                                    placeholder="{{ \App\Models\CustomizationSetting::defaultValue($field['key'], '') }}"
                                >{{ old($field['key'], $settingValue($field['key'])) }}</textarea>
                            @else
                                <input
                                    id="{{ $field['key'] }}"
                                    name="{{ $field['key'] }}"
                                    type="text"
                                    class="form-control @error($field['key']) is-invalid @enderror"
                                    value="{{ old($field['key'], $settingValue($field['key'])) }}"
                                    maxlength="{{ $field['max'] ?? 500 }}"
                                    placeholder="{{ \App\Models\CustomizationSetting::defaultValue($field['key'], '') }}"
                                >
                            @endif
                            @error($field['key'])
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </section>

            <aside class="space-y-6">
                <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#9b7431]">Placeholders</p>
                    <h3 class="theme-display mt-2 text-2xl text-stone-950">Available Tokens</h3>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($templateDefinition['tokens'] as $token)
                            <code class="rounded-full border border-[#e2d5bc] bg-[#fbf5e8] px-3 py-1 text-xs font-semibold text-stone-700">{{ $token }}</code>
                        @endforeach
                    </div>
                    <p class="mt-4 text-xs text-stone-500">Use these tokens anywhere in this template text. They are replaced automatically when the PDF is generated.</p>
                </section>

                <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#9b7431]">Scope</p>
                    <h3 class="theme-display mt-2 text-2xl text-stone-950">What You Control</h3>
                    <ul class="mt-4 space-y-3 text-sm leading-6 text-stone-600">
                        <li>Document title, subtitle, intro copy, metric labels, table headers, fallback text, and footer note.</li>
                        <li>All values here affect this PDF template only.</li>
                        <li>Global logo, colors, header/footer styling, and shared PDF copy stay under `Customization -> PDF Customizer`.</li>
                    </ul>
                </section>

                <section class="rounded-[1.8rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_60px_rgba(17,17,17,0.06)]">
                    <button type="submit" class="btn btn-black w-100">
                        <i class="fas fa-save me-2"></i>Save Template
                    </button>
                </section>
            </aside>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.pdf-templates.reset', ['template' => $templateDefinition['key']]) }}" class="mt-4" onsubmit="return confirm('Reset this PDF template to default?');">
        @csrf
        <button type="submit" class="btn btn-outline-danger">
            <i class="fas fa-rotate-left me-2"></i>Reset To Default
        </button>
    </form>
</x-app-layout>
