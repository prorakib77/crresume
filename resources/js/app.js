import './bootstrap';
import './legacy-compat';
import './bootstrap-compat';
import { initFormSelectEnhancer } from './form-select-enhancer';

import { Livewire } from '../../vendor/livewire/livewire/dist/livewire.esm.js';

function initNativeDatePickerEnhancer() {
    const pickerSelector = [
        'input[type="date"]',
        'input[type="datetime-local"]',
        'input[type="month"]',
        'input[type="week"]',
        'input[type="time"]',
    ].join(', ');

    const openPicker = (input) => {
        if (!(input instanceof HTMLInputElement) || typeof input.showPicker !== 'function') {
            return;
        }

        if (input.disabled || input.readOnly) {
            return;
        }

        try {
            if (document.activeElement !== input) {
                input.focus({ preventScroll: true });
            }

            input.showPicker();
        } catch (error) {
            // Browsers can reject showPicker outside a trusted user gesture.
        }
    };

    document.addEventListener('pointerdown', (event) => {
        const input = event.target instanceof Element ? event.target.closest(pickerSelector) : null;
        if (!input) {
            return;
        }

        openPicker(input);
    }, true);

    document.addEventListener('keydown', (event) => {
        const input = event.target instanceof Element ? event.target.closest(pickerSelector) : null;
        if (!input) {
            return;
        }

        if (!['Enter', ' ', 'ArrowDown'].includes(event.key)) {
            return;
        }

        event.preventDefault();
        openPicker(input);
    });
}

Livewire.start();
initFormSelectEnhancer();
initNativeDatePickerEnhancer();
