function normalizeElements(input) {
    if (!input) {
        return [];
    }

    if (input instanceof MiniQueryCollection) {
        return input.elements;
    }

    if (typeof input === 'string') {
        return Array.from(document.querySelectorAll(input));
    }

    if (input === window || input === document || input instanceof Element || input instanceof HTMLDocument) {
        return [input];
    }

    if (input instanceof NodeList || Array.isArray(input)) {
        return Array.from(input).filter(Boolean);
    }

    return [];
}

class MiniQueryCollection {
    constructor(elements) {
        this.elements = elements.filter(Boolean);
        this.length = this.elements.length;

        this.elements.forEach((element, index) => {
            this[index] = element;
        });
    }

    each(callback) {
        this.elements.forEach((element, index) => {
            callback.call(element, index, element);
        });

        return this;
    }

    ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
            return this;
        }

        callback();
        return this;
    }

    on(events, handler) {
        events.split(/\s+/).filter(Boolean).forEach((eventName) => {
            this.each(function () {
                this.addEventListener(eventName, function (event) {
                    handler.call(this, event);
                });
            });
        });

        return this;
    }

    click(handler) {
        if (handler) {
            return this.on('click', handler);
        }

        return this.each(function () {
            this.click();
        });
    }

    submit(handler) {
        if (handler) {
            return this.on('submit', handler);
        }

        return this.each(function () {
            if (typeof this.requestSubmit === 'function') {
                this.requestSubmit();
                return;
            }

            if (typeof this.submit === 'function') {
                this.submit();
            }
        });
    }

    show() {
        return this.each(function () {
            this.style.display = '';

            if (window.getComputedStyle(this).display === 'none') {
                this.style.display = 'block';
            }
        });
    }

    hide() {
        return this.each(function () {
            this.style.display = 'none';
        });
    }

    toggle(force) {
        return this.each(function () {
            const shouldShow = typeof force === 'boolean'
                ? force
                : window.getComputedStyle(this).display === 'none';

            this.style.display = shouldShow ? '' : 'none';

            if (shouldShow && window.getComputedStyle(this).display === 'none') {
                this.style.display = 'block';
            }
        });
    }

    filter(callback) {
        if (typeof callback !== 'function') {
            return new MiniQueryCollection(this.elements);
        }

        const filtered = [];

        this.elements.forEach((element, index) => {
            if (callback.call(element, index, element)) {
                filtered.push(element);
            }
        });

        return new MiniQueryCollection(filtered);
    }

    find(selector) {
        const nested = this.elements.flatMap((element) => Array.from(element.querySelectorAll(selector)));
        return new MiniQueryCollection(nested);
    }

    val(value) {
        if (typeof value === 'undefined') {
            return this.elements[0]?.value ?? '';
        }

        return this.each(function () {
            this.value = value;
        });
    }

    text(value) {
        if (typeof value === 'undefined') {
            return this.elements.map((element) => element.textContent ?? '').join('');
        }

        return this.each(function () {
            this.textContent = value;
        });
    }

    attr(name, value) {
        if (typeof value === 'undefined') {
            return this.elements[0]?.getAttribute(name);
        }

        return this.each(function () {
            if (value === null) {
                this.removeAttribute(name);
                return;
            }

            this.setAttribute(name, value);
        });
    }

    prop(name, value) {
        if (typeof value === 'undefined') {
            return this.elements[0]?.[name];
        }

        return this.each(function () {
            this[name] = value;
        });
    }

    trigger(eventOrName) {
        return this.each(function () {
            const eventObject = typeof eventOrName === 'string'
                ? createJQueryEvent(eventOrName)
                : eventOrName;

            const domEvent = new CustomEvent(eventObject.type, {
                bubbles: true,
                cancelable: true,
                detail: eventObject.detail ?? {},
            });

            if (Object.prototype.hasOwnProperty.call(eventObject, 'relatedTarget')) {
                domEvent.relatedTarget = eventObject.relatedTarget;
            }

            const result = this.dispatchEvent(domEvent);

            if (!result || domEvent.defaultPrevented) {
                eventObject.preventDefault();
            }
        });
    }

    serialize() {
        const form = this.elements[0];

        if (!(form instanceof HTMLFormElement)) {
            return '';
        }

        return new URLSearchParams(new FormData(form)).toString();
    }
}

function createJQueryEvent(type) {
    let defaultPrevented = false;

    return {
        type,
        detail: {},
        preventDefault() {
            defaultPrevented = true;
        },
        isDefaultPrevented() {
            return defaultPrevented;
        },
    };
}

function jqueryLite(input) {
    if (typeof input === 'function') {
        return new MiniQueryCollection([document]).ready(input);
    }

    return new MiniQueryCollection(normalizeElements(input));
}

jqueryLite.Event = createJQueryEvent;

jqueryLite.ajax = function ajax(options) {
    const method = (options.method || options.type || 'GET').toUpperCase();
    const headers = { ...(options.headers || {}) };
    let url = options.url;
    let body = null;

    if (options.data) {
        if (typeof options.data === 'string') {
            body = options.data;
        } else if (options.data instanceof FormData) {
            body = options.data;
        } else {
            body = new URLSearchParams(options.data).toString();
        }
    }

    if (method === 'GET' && body) {
        const separator = url.includes('?') ? '&' : '?';
        url = `${url}${separator}${body}`;
        body = null;
    } else if (body && !(body instanceof FormData) && !headers['Content-Type']) {
        headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
    }

    return fetch(url, {
        method,
        body,
        headers,
        credentials: 'same-origin',
    })
        .then(async (response) => {
            const contentType = response.headers.get('content-type') || '';
            const payload = contentType.includes('application/json')
                ? await response.json()
                : await response.text();

            if (response.ok) {
                options.success?.(payload, 'success', response);
                return payload;
            }

            const xhrLike = {
                responseText: typeof payload === 'string' ? payload : JSON.stringify(payload),
                status: response.status,
                statusText: response.statusText,
            };

            options.error?.(xhrLike, 'error', response.statusText);
            return null;
        })
        .catch((error) => {
            if (!error || typeof error.status === 'undefined') {
                options.error?.(
                    { responseText: error?.message ?? 'Request failed', status: 0, statusText: 'error' },
                    'error',
                    error?.message ?? 'Request failed',
                );
            }

            return null;
        })
        .finally(() => {
            options.complete?.();
        });
};

window.$ = jqueryLite;
window.jQuery = jqueryLite;
