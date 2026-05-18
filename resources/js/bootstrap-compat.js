const modalInstances = new WeakMap();
const dropdownInstances = new WeakMap();

function triggerBootstrapEvent(element, name, detail = {}) {
    const nativeEvent = new CustomEvent(name, {
        bubbles: true,
        cancelable: true,
        detail,
    });

    if (Object.prototype.hasOwnProperty.call(detail, 'relatedTarget')) {
        nativeEvent.relatedTarget = detail.relatedTarget;
    }

    element.dispatchEvent(nativeEvent);

    if (window.jQuery) {
        const jqueryEvent = window.jQuery.Event(name);

        if (Object.prototype.hasOwnProperty.call(detail, 'relatedTarget')) {
            jqueryEvent.relatedTarget = detail.relatedTarget;
        }

        window.jQuery(element).trigger(jqueryEvent);

        if (jqueryEvent.isDefaultPrevented()) {
            nativeEvent.preventDefault();
        }
    }

    return nativeEvent;
}

function getFocusableElements(container) {
    return container.querySelectorAll(
        'a[href], button:not([disabled]), textarea, input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])',
    );
}

class Alert {
    constructor(element) {
        this._element = element;
    }

    close() {
        const closeEvent = triggerBootstrapEvent(this._element, 'close.bs.alert');

        if (closeEvent.defaultPrevented) {
            return;
        }

        this._element.classList.remove('show');
        this._element.classList.add('closing');

        window.setTimeout(() => {
            this._element.remove();
            triggerBootstrapEvent(this._element, 'closed.bs.alert');
        }, 150);
    }
}

class Modal {
    constructor(element) {
        this._element = element;
        this._dialog = element.querySelector('.modal-dialog');
        this._backdrop = null;
        this._isShown = false;
        this._boundKeyHandler = this._handleKeydown.bind(this);
        this._boundClickHandler = this._handleBackdropClick.bind(this);

        modalInstances.set(element, this);
    }

    static getInstance(element) {
        return modalInstances.get(element) || null;
    }

    static getOrCreateInstance(element) {
        return Modal.getInstance(element) || new Modal(element);
    }

    show(relatedTarget = null) {
        if (this._isShown) {
            return;
        }

        const showEvent = triggerBootstrapEvent(this._element, 'show.bs.modal', { relatedTarget });
        if (showEvent.defaultPrevented) {
            return;
        }

        this._isShown = true;
        this._element.style.display = 'block';
        this._element.removeAttribute('aria-hidden');
        document.body.classList.add('modal-open');

        this._createBackdrop();
        document.addEventListener('keydown', this._boundKeyHandler);
        this._element.addEventListener('click', this._boundClickHandler);

        requestAnimationFrame(() => {
            this._element.classList.add('show');

            if (this._backdrop) {
                this._backdrop.classList.add('show');
            }
        });

        window.setTimeout(() => {
            const focusableElements = getFocusableElements(this._element);
            if (focusableElements.length > 0) {
                focusableElements[0].focus();
            }

            triggerBootstrapEvent(this._element, 'shown.bs.modal', { relatedTarget });
        }, 160);
    }

    hide() {
        if (!this._isShown) {
            return;
        }

        const hideEvent = triggerBootstrapEvent(this._element, 'hide.bs.modal');
        if (hideEvent.defaultPrevented) {
            return;
        }

        this._isShown = false;
        this._element.classList.remove('show');

        if (this._backdrop) {
            this._backdrop.classList.remove('show');
        }

        window.setTimeout(() => {
            this._element.style.display = 'none';
            this._element.setAttribute('aria-hidden', 'true');
            this._element.removeEventListener('click', this._boundClickHandler);
            document.removeEventListener('keydown', this._boundKeyHandler);

            if (this._backdrop) {
                this._backdrop.remove();
                this._backdrop = null;
            }

            if (!document.querySelector('.modal.show')) {
                document.body.classList.remove('modal-open');
            }

            triggerBootstrapEvent(this._element, 'hidden.bs.modal');
        }, 160);
    }

    toggle(relatedTarget = null) {
        if (this._isShown) {
            this.hide();
            return;
        }

        this.show(relatedTarget);
    }

    _createBackdrop() {
        this._backdrop = document.createElement('div');
        this._backdrop.className = 'modal-backdrop fade';
        this._backdrop.addEventListener('click', () => this.hide());
        document.body.appendChild(this._backdrop);
    }

    _handleBackdropClick(event) {
        if (!this._dialog) {
            return;
        }

        if (!this._dialog.contains(event.target)) {
            this.hide();
        }
    }

    _handleKeydown(event) {
        if (event.key === 'Escape') {
            this.hide();
        }
    }
}

class Dropdown {
    constructor(toggleElement) {
        this._toggleElement = toggleElement;
        this._menuElement = this._resolveMenu(toggleElement);

        dropdownInstances.set(toggleElement, this);
    }

    static getInstance(toggleElement) {
        return dropdownInstances.get(toggleElement) || null;
    }

    static getOrCreateInstance(toggleElement) {
        return Dropdown.getInstance(toggleElement) || new Dropdown(toggleElement);
    }

    static clearMenus(except = null) {
        document.querySelectorAll('[data-bs-toggle="dropdown"], .dropdown-toggle').forEach((toggleElement) => {
            const instance = Dropdown.getInstance(toggleElement);

            if (instance && instance !== except) {
                instance.hide();
            }
        });
    }

    toggle() {
        if (!this._menuElement) {
            return;
        }

        if (this._menuElement.classList.contains('show')) {
            this.hide();
            return;
        }

        this.show();
    }

    show() {
        if (!this._menuElement || this._menuElement.classList.contains('show')) {
            return;
        }

        const showEvent = triggerBootstrapEvent(this._toggleElement, 'show.bs.dropdown');
        if (showEvent.defaultPrevented) {
            return;
        }

        Dropdown.clearMenus(this);
        this._menuElement.classList.add('show');
        this._toggleElement.classList.add('show');
        this._toggleElement.setAttribute('aria-expanded', 'true');

        triggerBootstrapEvent(this._toggleElement, 'shown.bs.dropdown');
    }

    hide() {
        if (!this._menuElement || !this._menuElement.classList.contains('show')) {
            return;
        }

        const hideEvent = triggerBootstrapEvent(this._toggleElement, 'hide.bs.dropdown');
        if (hideEvent.defaultPrevented) {
            return;
        }

        this._menuElement.classList.remove('show');
        this._toggleElement.classList.remove('show');
        this._toggleElement.setAttribute('aria-expanded', 'false');

        triggerBootstrapEvent(this._toggleElement, 'hidden.bs.dropdown');
    }

    _resolveMenu(toggleElement) {
        const explicitTarget = toggleElement.getAttribute('data-bs-target');
        if (explicitTarget) {
            return document.querySelector(explicitTarget);
        }

        const siblingMenu = toggleElement.nextElementSibling;
        if (siblingMenu && siblingMenu.classList.contains('dropdown-menu')) {
            return siblingMenu;
        }

        return toggleElement.closest('.dropdown, .position-relative')?.querySelector('.dropdown-menu') || null;
    }
}

function resolveTarget(selectorSource) {
    const selector = selectorSource?.getAttribute('data-bs-target') || selectorSource?.getAttribute('href');

    if (!selector || !selector.startsWith('#')) {
        return null;
    }

    return document.querySelector(selector);
}

document.addEventListener('click', (event) => {
    const modalTrigger = event.target.closest('[data-bs-toggle="modal"]');
    if (modalTrigger) {
        event.preventDefault();
        const target = resolveTarget(modalTrigger);

        if (target) {
            Modal.getOrCreateInstance(target).show(modalTrigger);
        }

        return;
    }

    const modalDismissTrigger = event.target.closest('[data-bs-dismiss="modal"]');
    if (modalDismissTrigger) {
        event.preventDefault();
        const modalElement = modalDismissTrigger.closest('.modal');

        if (modalElement) {
            Modal.getOrCreateInstance(modalElement).hide();
        }

        return;
    }

    const alertDismissTrigger = event.target.closest('[data-bs-dismiss="alert"]');
    if (alertDismissTrigger) {
        event.preventDefault();
        const alertElement = alertDismissTrigger.closest('.alert');

        if (alertElement) {
            new Alert(alertElement).close();
        }

        return;
    }

    const dropdownTrigger = event.target.closest('[data-bs-toggle="dropdown"], .dropdown-toggle');
    if (dropdownTrigger) {
        event.preventDefault();
        Dropdown.getOrCreateInstance(dropdownTrigger).toggle();
        return;
    }

    if (!event.target.closest('.dropdown')) {
        Dropdown.clearMenus();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        Dropdown.clearMenus();
    }
});

document.addEventListener('click', (event) => {
    if (event.target.closest('.dropdown-menu .dropdown-item, .dropdown-menu .dropdown-item-text')) {
        Dropdown.clearMenus();
    }
});

window.bootstrap = {
    Alert,
    Dropdown,
    Modal,
};
