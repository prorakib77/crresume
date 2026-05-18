const SELECTOR = 'select.form-control, select.form-select';
const SEARCH_THRESHOLD = 8;

let isInitialized = false;
let domObserver = null;
let openInstance = null;

function isSingleSelect(select) {
    if (!(select instanceof HTMLSelectElement)) {
        return false;
    }

    if (select.multiple) {
        return false;
    }

    const size = Number(select.getAttribute('size') || '1');
    return Number.isNaN(size) || size <= 1;
}

function shouldEnhance(select) {
    return isSingleSelect(select)
        && !select.dataset.nativeSelect
        && !select.dataset.appSelectEnhanced
        && !select.closest('.searchable-select-shell')
        && !select.closest('.filter-select')
        && !select.closest('.ts-wrapper')
        && !select.closest('.app-select');
}

function getOptionText(option) {
    return (option.textContent || '').trim();
}

function getPlaceholderText(select) {
    const emptyOption = Array.from(select.options).find((option) => option.value === '');
    return emptyOption ? getOptionText(emptyOption) : 'Select an option';
}

function getSelectedOption(select) {
    return Array.from(select.options).find((option) => option.selected) || select.options[select.selectedIndex] || null;
}

function getVisibleItems(select, query = '') {
    const normalizedQuery = query.trim().toLowerCase();
    const items = [];

    Array.from(select.children).forEach((child) => {
        if (child instanceof HTMLOptGroupElement) {
            const groupOptions = Array.from(child.children).filter((option) => {
                if (!(option instanceof HTMLOptionElement) || option.hidden) {
                    return false;
                }

                if (!normalizedQuery) {
                    return true;
                }

                return getOptionText(option).toLowerCase().includes(normalizedQuery);
            });

            if (groupOptions.length) {
                items.push({
                    type: 'group',
                    label: child.label,
                });

                groupOptions.forEach((option) => {
                    items.push({
                        type: 'option',
                        option,
                    });
                });
            }

            return;
        }

        if (!(child instanceof HTMLOptionElement) || child.hidden) {
            return;
        }

        if (normalizedQuery && !getOptionText(child).toLowerCase().includes(normalizedQuery)) {
            return;
        }

        items.push({
            type: 'option',
            option: child,
        });
    });

    return items;
}

function closeOpenInstance(except = null) {
    if (openInstance && openInstance !== except) {
        openInstance.close();
    }
}

function buildOptionButton(option, select, close, syncTrigger) {
    const button = document.createElement('button');
    const isPlaceholder = option.value === '';
    const isSelected = String(select.value) === String(option.value);

    button.type = 'button';
    button.className = 'app-select-option';
    button.setAttribute('role', 'option');
    button.dataset.value = option.value;
    button.textContent = getOptionText(option);

    if (isPlaceholder) {
        button.classList.add('is-placeholder');
    }

    if (isSelected) {
        button.classList.add('is-selected');
        button.setAttribute('aria-selected', 'true');
    } else {
        button.setAttribute('aria-selected', 'false');
    }

    if (option.disabled) {
        button.disabled = true;
        button.classList.add('is-disabled');
    }

    button.addEventListener('click', () => {
        if (option.disabled) {
            return;
        }

        select.value = option.value;
        select.dispatchEvent(new Event('input', { bubbles: true }));
        select.dispatchEvent(new Event('change', { bubbles: true }));
        syncTrigger();
        close();
    });

    return button;
}

function renderMenu(menuBody, select, close, syncTrigger, query = '') {
    menuBody.innerHTML = '';

    const items = getVisibleItems(select, query);

    if (!items.length) {
        const emptyState = document.createElement('div');
        emptyState.className = 'app-select-empty';
        emptyState.textContent = query ? 'No matching options' : 'No options available';
        menuBody.appendChild(emptyState);
        return;
    }

    items.forEach((item) => {
        if (item.type === 'group') {
            const label = document.createElement('div');
            label.className = 'app-select-group-label';
            label.textContent = item.label;
            menuBody.appendChild(label);
            return;
        }

        menuBody.appendChild(buildOptionButton(item.option, select, close, syncTrigger));
    });
}

function enhanceSelect(select) {
    if (!shouldEnhance(select)) {
        return;
    }

    select.dataset.appSelectEnhanced = 'true';

    const wrapper = document.createElement('div');
    wrapper.className = 'app-select';

    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);
    select.classList.add('app-select-native');

    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'app-select-trigger';
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');

    const value = document.createElement('span');
    value.className = 'app-select-value';

    const caret = document.createElement('span');
    caret.className = 'app-select-caret';

    trigger.append(value, caret);

    const menu = document.createElement('div');
    menu.className = 'app-select-menu';
    menu.setAttribute('role', 'listbox');
    menu.hidden = true;

    const searchShell = document.createElement('div');
    searchShell.className = 'app-select-search-shell';
    searchShell.hidden = true;

    const searchInput = document.createElement('input');
    searchInput.type = 'search';
    searchInput.className = 'app-select-search-input';
    searchInput.placeholder = 'Search options';
    searchInput.autocomplete = 'off';

    const optionsBody = document.createElement('div');
    optionsBody.className = 'app-select-options';

    searchShell.appendChild(searchInput);
    menu.append(searchShell, optionsBody);
    wrapper.append(trigger, menu);

    const shouldShowSearch = () => Array.from(select.options).filter((option) => !option.hidden).length >= SEARCH_THRESHOLD;

    const syncState = () => {
        const isInvalid = select.classList.contains('is-invalid');
        const isDisabled = select.disabled;

        wrapper.classList.toggle('is-invalid', isInvalid);
        wrapper.classList.toggle('is-disabled', isDisabled);
        trigger.disabled = isDisabled;
    };

    const syncTrigger = () => {
        const selectedOption = getSelectedOption(select);
        const isPlaceholder = !selectedOption || selectedOption.value === '';

        value.textContent = isPlaceholder
            ? getPlaceholderText(select)
            : getOptionText(selectedOption);

        trigger.classList.toggle('has-placeholder', isPlaceholder);
        searchShell.hidden = !shouldShowSearch();
        renderMenu(optionsBody, select, close, syncTrigger, searchInput.value);
        syncState();
    };

    const focusSelectedOption = () => {
        const selectedButton = optionsBody.querySelector('.app-select-option.is-selected:not(:disabled)');
        const target = selectedButton || optionsBody.querySelector('.app-select-option:not(:disabled)');

        if (target instanceof HTMLElement) {
            target.focus();
            target.scrollIntoView({ block: 'nearest' });
        }
    };

    const open = () => {
        if (select.disabled) {
            return;
        }

        closeOpenInstance(instance);
        wrapper.classList.add('is-open');
        menu.hidden = false;
        trigger.setAttribute('aria-expanded', 'true');
        openInstance = instance;

        if (shouldShowSearch()) {
            searchInput.value = '';
            renderMenu(optionsBody, select, close, syncTrigger, '');
        }
    };

    const close = () => {
        wrapper.classList.remove('is-open');
        menu.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');

        if (openInstance === instance) {
            openInstance = null;
        }
    };

    const toggle = () => {
        if (wrapper.classList.contains('is-open')) {
            close();
            return;
        }

        open();

        if (!searchShell.hidden) {
            searchInput.focus();
            return;
        }

        focusSelectedOption();
    };

    const instance = { close };

    trigger.addEventListener('click', () => {
        toggle();
    });

    trigger.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            open();
            focusSelectedOption();
        }

        if (event.key === 'Escape') {
            close();
        }
    });

    menu.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            event.preventDefault();
            close();
            trigger.focus();
        }
    });

    searchInput.addEventListener('input', () => {
        renderMenu(optionsBody, select, close, syncTrigger, searchInput.value);
    });

    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            const firstOption = optionsBody.querySelector('.app-select-option:not(:disabled)');

            if (firstOption instanceof HTMLElement) {
                firstOption.focus();
            }
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            close();
            trigger.focus();
        }
    });

    select.addEventListener('change', () => {
        wrapper.classList.remove('is-invalid');
        syncTrigger();
    });

    select.addEventListener('invalid', () => {
        wrapper.classList.add('is-invalid');
    });

    select.addEventListener('focus', () => {
        trigger.focus();
    });

    if (select.form) {
        select.form.addEventListener('reset', () => {
            window.setTimeout(syncTrigger, 0);
        });
    }

    const selectObserver = new MutationObserver(() => {
        syncTrigger();
    });

    selectObserver.observe(select, {
        attributes: true,
        attributeFilter: ['class', 'disabled'],
        childList: true,
        subtree: true,
    });

    syncTrigger();
}

function enhanceWithin(root = document) {
    if (!(root instanceof Element) && root !== document) {
        return;
    }

    if (root instanceof HTMLSelectElement) {
        enhanceSelect(root);
        return;
    }

    root.querySelectorAll(SELECTOR).forEach((select) => {
        enhanceSelect(select);
    });
}

function bindGlobalListeners() {
    if (isInitialized) {
        return;
    }

    isInitialized = true;

    document.addEventListener('click', (event) => {
        const target = event.target;

        if (!(target instanceof Element) || !target.closest('.app-select')) {
            closeOpenInstance();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeOpenInstance();
        }
    });
}

function observeDom() {
    if (domObserver || !document.body) {
        return;
    }

    domObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node instanceof HTMLSelectElement) {
                    enhanceSelect(node);
                    return;
                }

                if (node instanceof Element) {
                    enhanceWithin(node);
                }
            });
        });
    });

    domObserver.observe(document.body, {
        childList: true,
        subtree: true,
    });
}

export function initFormSelectEnhancer() {
    const boot = () => {
        bindGlobalListeners();
        enhanceWithin(document);
        observeDom();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
        return;
    }

    boot();
}
