@props([
    'name' => '',
    'id' => '',
    'label' => '',
    'placeholder' => 'Choose an option...',
    'options' => [],
    'value' => '',
    'searchUrl' => '',
    'searchParam' => 'search',
    'required' => false,
    'class' => '',
    'error' => null
])

@php
    $inputId = $id ?: $name;
    $searchId = $inputId . '_search';
    $dropdownId = $inputId . '_dropdown';
    $wrapperId = $inputId . '_wrapper';
@endphp

<div class="form-group">
    @if($label)
        <label for="{{ $searchId }}" class="form-label">{{ $label }} @if($required) * @endif</label>
    @endif

    <div class="position-relative searchable-select-shell" id="{{ $wrapperId }}">
        <select
            class="form-control searchable-native-select {{ $class }} @error($name) is-invalid @enderror"
            id="{{ $inputId }}"
            name="{{ $name }}"
            @if($required) required @endif
        >
            <option value="">{{ $placeholder }}</option>
            @foreach($options as $option)
                <option value="{{ $option['value'] }}" {{ $value == $option['value'] ? 'selected' : '' }}>
                    {{ $option['text'] }}
                </option>
            @endforeach
        </select>

        <div class="searchable-select-overlay">
            <input
                type="text"
                class="form-control searchable-input"
                id="{{ $searchId }}"
                placeholder="{{ $placeholder }}"
                autocomplete="off"
                role="combobox"
                aria-expanded="false"
                aria-controls="{{ $dropdownId }}"
            >
            <button type="button" class="searchable-toggle" aria-label="Toggle options">
                <span class="searchable-toggle-icon"></span>
            </button>
            <div id="{{ $dropdownId }}" class="dropdown-menu searchable-dropdown w-100" style="display: none;"></div>
        </div>
    </div>

    @if($error)
        <div class="invalid-feedback d-block">{{ $error }}</div>
    @elseif($errors->has($name))
        <div class="invalid-feedback d-block">{{ $errors->first($name) }}</div>
    @endif
</div>

@push('styles')
<style>
    .searchable-select-shell {
        position: relative;
    }

    .searchable-native-select {
        position: absolute;
        inset: 0;
        opacity: 0;
        pointer-events: none;
    }

    .searchable-select-overlay {
        position: relative;
    }

    .searchable-input {
        position: relative;
        z-index: 2;
        min-height: 3.45rem;
        padding-right: 4.45rem;
        font-weight: 600;
        color: #17120d;
        border-color: rgba(15, 15, 15, 0.1);
        border-radius: 1.3rem;
        background: linear-gradient(180deg, #ffffff 0%, #fbfaf7 100%);
        box-shadow: 0 14px 32px rgba(15, 15, 15, 0.06), inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }

    .searchable-select-shell.is-open .searchable-input {
        border-color: rgba(200, 164, 93, 0.52);
        background: linear-gradient(180deg, #ffffff 0%, #fffaf1 100%);
        box-shadow: 0 0 0 4px rgba(200, 164, 93, 0.12), 0 18px 36px rgba(15, 15, 15, 0.08);
    }

    .searchable-toggle {
        position: absolute;
        top: 0.45rem;
        right: 0.5rem;
        z-index: 3;
        display: inline-flex;
        height: calc(100% - 0.9rem);
        width: 2.45rem;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(15, 15, 15, 0.08);
        border-radius: 0.95rem;
        background: linear-gradient(180deg, #181818 0%, #0d0d0d 100%);
        box-shadow: 0 12px 20px rgba(15, 15, 15, 0.14);
        cursor: pointer;
        transition: background-color 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .searchable-toggle:hover {
        box-shadow: 0 14px 26px rgba(15, 15, 15, 0.18);
    }

    .searchable-toggle-icon {
        display: inline-block;
        height: 0.55rem;
        width: 0.55rem;
        border-right: 2px solid #e2bf73;
        border-bottom: 2px solid #e2bf73;
        transform: rotate(45deg) translateY(-1px);
        transition: transform 0.18s ease;
    }

    .searchable-select-shell.is-open .searchable-toggle {
        border-color: rgba(200, 164, 93, 0.42);
        background: linear-gradient(180deg, #ebcc84 0%, #c89c40 100%);
        box-shadow: 0 14px 28px rgba(173, 129, 39, 0.28);
    }

    .searchable-select-shell.is-open .searchable-toggle-icon {
        border-right-color: #111111;
        border-bottom-color: #111111;
        transform: rotate(225deg) translateY(-1px);
    }

    .searchable-dropdown.dropdown-menu {
        position: absolute;
        top: calc(100% + 0.5rem);
        left: 0;
        right: 0;
        z-index: 1000;
        max-height: 16rem;
        overflow-y: auto;
        border: 1px solid rgba(15, 15, 15, 0.08);
        border-radius: 1.35rem;
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 28px 64px rgba(15, 15, 15, 0.16);
        backdrop-filter: blur(14px);
        padding: 0.55rem;
    }

    .searchable-option {
        display: block;
        width: 100%;
        padding: 0.82rem 0.95rem;
        border: 1px solid transparent;
        border-radius: 1rem;
        background: transparent;
        color: #2f2419;
        font-weight: 600;
        cursor: pointer;
        text-align: left;
        transition: background-color 0.18s ease, color 0.18s ease, border-color 0.18s ease;
    }

    .searchable-option:hover,
    .searchable-option:focus-visible {
        background: #f7f5ef;
        border-color: rgba(200, 164, 93, 0.26);
        color: #111111;
        outline: none;
    }

    .searchable-option.is-selected {
        position: relative;
        padding-right: 2.5rem;
        border-color: rgba(20, 20, 20, 0.02);
        background: linear-gradient(180deg, #1c1c1c 0%, #101010 100%);
        box-shadow: 0 12px 24px rgba(15, 15, 15, 0.14);
        color: #f4d58f;
    }

    .searchable-option.is-selected::after {
        content: "";
        position: absolute;
        right: 1rem;
        top: 50%;
        height: 0.45rem;
        width: 0.8rem;
        border-left: 2px solid #f4d58f;
        border-bottom: 2px solid #f4d58f;
        transform: translateY(-65%) rotate(-45deg);
    }

    .searchable-empty {
        padding: 0.85rem 1rem;
        color: #8c7c66;
        text-align: center;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('{{ $inputId }}');
    const searchInput = document.getElementById('{{ $searchId }}');
    const dropdown = document.getElementById('{{ $dropdownId }}');
    const wrapper = document.getElementById('{{ $wrapperId }}');
    const toggle = wrapper ? wrapper.querySelector('.searchable-toggle') : null;

    if (!select || !searchInput || !dropdown || !wrapper || !toggle) return;

    const placeholder = @json($placeholder);
    const remoteSearchUrl = @json($searchUrl);
    const remoteSearchParam = @json($searchParam);
    let searchTimeout;

    function getSelectedText() {
        const selectedOption = Array.from(select.options).find(option => option.selected && option.value);
        return selectedOption ? selectedOption.text : '';
    }

    function syncVisibleValue() {
        searchInput.value = getSelectedText();
        searchInput.placeholder = placeholder;
    }

    function getOpenQuery() {
        const currentValue = searchInput.value.trim();
        return currentValue === getSelectedText() ? '' : currentValue;
    }

    function openDropdown() {
        wrapper.classList.add('is-open');
        searchInput.setAttribute('aria-expanded', 'true');
        dropdown.style.display = 'block';
    }

    function closeDropdown(resetValue = true) {
        wrapper.classList.remove('is-open');
        searchInput.setAttribute('aria-expanded', 'false');
        dropdown.style.display = 'none';

        if (resetValue) {
            syncVisibleValue();
        }
    }

    function renderEmptyState(message) {
        dropdown.innerHTML = `<div class="searchable-empty">${message}</div>`;
        openDropdown();
    }

    function populateDropdown(items) {
        dropdown.innerHTML = '';

        if (!items.length) {
            renderEmptyState('No results found');
            return;
        }

        items.forEach(item => {
            const optionButton = document.createElement('button');
            optionButton.type = 'button';
            optionButton.className = `searchable-option${String(select.value) === String(item.value) ? ' is-selected' : ''}`;
            optionButton.textContent = item.text;
            optionButton.addEventListener('click', function () {
                let targetOption = Array.from(select.options).find(option => String(option.value) === String(item.value));

                if (!targetOption) {
                    targetOption = new Option(item.text, item.value, true, true);
                    select.add(targetOption);
                }

                select.value = item.value;
                searchInput.value = item.text;
                closeDropdown(false);
                select.dispatchEvent(new Event('change', { bubbles: true }));
            });

            dropdown.appendChild(optionButton);
        });

        openDropdown();
    }

    function runLocalSearch(query) {
        const filteredOptions = Array.from(select.options)
            .filter(option => option.value)
            .filter(option => option.text.toLowerCase().includes(query.toLowerCase()))
            .map(option => ({
                value: option.value,
                text: option.text
            }));

        populateDropdown(filteredOptions);
    }

    function runSearch(query) {
        clearTimeout(searchTimeout);

        searchTimeout = setTimeout(() => {
            const normalizedQuery = query.trim();

            if (!normalizedQuery) {
                runLocalSearch('');
                return;
            }

            if (remoteSearchUrl) {
                if (normalizedQuery.length < 2) {
                    runLocalSearch(normalizedQuery);
                    return;
                }

                fetch(`${remoteSearchUrl}?${remoteSearchParam}=${encodeURIComponent(normalizedQuery)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }

                        return response.json();
                    })
                    .then(data => {
                        if (Array.isArray(data) && data.length) {
                            populateDropdown(data);
                            return;
                        }

                        runLocalSearch(normalizedQuery);
                    })
                    .catch(() => {
                        runLocalSearch(normalizedQuery);
                    });
            } else {
                runLocalSearch(normalizedQuery);
            }
        }, 180);
    }

    syncVisibleValue();

    searchInput.addEventListener('focus', function () {
        runSearch(getOpenQuery());
    });

    searchInput.addEventListener('click', function () {
        runSearch(getOpenQuery());
    });

    searchInput.addEventListener('input', function () {
        runSearch(this.value.trim());
    });

    toggle.addEventListener('click', function () {
        if (wrapper.classList.contains('is-open')) {
            closeDropdown();
            return;
        }

        searchInput.focus();
        runSearch(getOpenQuery());
    });

    select.addEventListener('change', syncVisibleValue);

    document.addEventListener('click', function (event) {
        if (!wrapper.contains(event.target)) {
            closeDropdown();
        }
    });

    searchInput.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeDropdown();
            searchInput.blur();
        }
    });
});
</script>
@endpush
