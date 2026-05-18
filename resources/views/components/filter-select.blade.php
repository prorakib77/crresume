@props([
    'wireModel',
    'label' => '',
    'placeholder' => 'Select option',
    'options' => [],
    'id' => null,
    'searchable' => false,
])

@php
    $inputId = $id ?: 'filter_select_' . str_replace(['.', '[', ']'], '_', $wireModel);
    $normalizedOptions = collect($options)
        ->map(fn ($option) => [
            'value' => (string) ($option['value'] ?? ''),
            'text' => (string) ($option['text'] ?? ''),
        ])
        ->values()
        ->all();
@endphp

<div
    class="work-filter-field"
    x-data="{
        open: false,
        search: '',
        state: @entangle($wireModel).live,
        placeholder: @js($placeholder),
        options: @js($normalizedOptions),
        searchable: @js((bool) $searchable),
        get selectedOption() {
            return this.options.find((option) => String(option.value) === String(this.state ?? '')) ?? null;
        },
        get filteredOptions() {
            if (!this.searchable || this.search.trim() === '') {
                return this.options;
            }

            return this.options.filter((option) => option.text.toLowerCase().includes(this.search.trim().toLowerCase()));
        },
        get triggerText() {
            return this.selectedOption ? this.selectedOption.text : this.placeholder;
        },
        toggle() {
            this.open = !this.open;
            if (!this.open) {
                this.search = '';
            }
        },
        choose(value) {
            this.state = value === null ? '' : String(value);
            this.open = false;
            this.search = '';
        },
    }"
    @keydown.escape.stop="open = false"
    @click.outside="open = false"
>
    @if($label !== '')
        <label class="form-label" for="{{ $inputId }}_trigger">{{ $label }}</label>
    @endif

    <div class="work-filter-select" :class="{ 'is-open': open }">
        <button
            type="button"
            id="{{ $inputId }}_trigger"
            class="work-filter-select-trigger"
            @click="toggle()"
            :aria-expanded="open.toString()"
        >
            <span class="work-filter-select-trigger-text" :class="{ 'is-placeholder': !selectedOption }" x-text="triggerText"></span>
            <span class="work-filter-select-trigger-icon">
                <i class="fas fa-chevron-down"></i>
            </span>
        </button>

        <div x-cloak x-show="open" x-transition class="work-filter-select-panel">
            @if($searchable)
                <div class="work-filter-select-search">
                    <i class="fas fa-search"></i>
                    <input type="text" class="work-filter-select-search-input" placeholder="Search options" x-model="search">
                </div>
            @endif

            <div class="work-filter-select-options">
                <button
                    type="button"
                    class="work-filter-select-option"
                    :class="{ 'is-selected': String(state ?? '') === '' }"
                    @click="choose('')"
                >
                    <span>{{ $placeholder }}</span>
                    <i class="fas fa-check" x-show="String(state ?? '') === ''"></i>
                </button>

                <template x-if="filteredOptions.length === 0">
                    <div class="work-filter-select-empty">No options found</div>
                </template>

                <template x-for="option in filteredOptions" :key="option.value + '-' + option.text">
                    <button
                        type="button"
                        class="work-filter-select-option"
                        :class="{ 'is-selected': String(option.value) === String(state ?? '') }"
                        @click="choose(option.value)"
                    >
                        <span x-text="option.text"></span>
                        <i class="fas fa-check" x-show="String(option.value) === String(state ?? '')"></i>
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>
