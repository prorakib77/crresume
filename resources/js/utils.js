/**
 * Utility functions for Work Automation System
 * Common helper functions used throughout the application
 */

// Date and Time Utilities
export const DateUtils = {
    formatDate(dateString, options = {}) {
        const defaultOptions = {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        };
        const formatOptions = { ...defaultOptions, ...options };
        return new Date(dateString).toLocaleDateString('en-US', formatOptions);
    },

    formatDateTime(dateString) {
        return this.formatDate(dateString, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    formatTime(dateString) {
        return new Date(dateString).toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    getRelativeTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInMinutes = Math.floor((now - date) / (1000 * 60));

        if (diffInMinutes < 1) return 'Just now';
        if (diffInMinutes < 60) return `${diffInMinutes} minute${diffInMinutes > 1 ? 's' : ''} ago`;
        
        const diffInHours = Math.floor(diffInMinutes / 60);
        if (diffInHours < 24) return `${diffInHours} hour${diffInHours > 1 ? 's' : ''} ago`;
        
        const diffInDays = Math.floor(diffInHours / 24);
        if (diffInDays < 7) return `${diffInDays} day${diffInDays > 1 ? 's' : ''} ago`;
        
        return this.formatDate(dateString);
    },

    isToday(dateString) {
        const date = new Date(dateString);
        const today = new Date();
        return date.toDateString() === today.toDateString();
    },

    isThisWeek(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const weekStart = new Date(now.setDate(now.getDate() - now.getDay()));
        const weekEnd = new Date(now.setDate(now.getDate() - now.getDay() + 6));
        return date >= weekStart && date <= weekEnd;
    }
};

// String Utilities
export const StringUtils = {
    capitalize(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    },

    truncate(string, length = 100, suffix = '...') {
        if (string.length <= length) return string;
        return string.substring(0, length) + suffix;
    },

    slugify(string) {
        return string
            .toLowerCase()
            .replace(/[^a-z0-9 -]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    },

    highlightText(text, query) {
        if (!query) return text;
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<mark class="bg-yellow-200">$1</mark>');
    },

    removeHtml(html) {
        const div = document.createElement('div');
        div.innerHTML = html;
        return div.textContent || div.innerText || '';
    }
};

// Number Utilities
export const NumberUtils = {
    formatCurrency(amount, currency = 'USD') {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(amount);
    },

    formatPercentage(value, decimals = 1) {
        return `${(value * 100).toFixed(decimals)}%`;
    },

    formatNumber(number, decimals = 0) {
        return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(number);
    },

    abbreviateNumber(number) {
        if (number < 1000) return number.toString();
        if (number < 1000000) return (number / 1000).toFixed(1) + 'K';
        if (number < 1000000000) return (number / 1000000).toFixed(1) + 'M';
        return (number / 1000000000).toFixed(1) + 'B';
    }
};

// Validation Utilities
export const ValidationUtils = {
    isEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },

    isRequired(value) {
        return value !== null && value !== undefined && value.toString().trim() !== '';
    },

    minLength(value, min) {
        return value && value.length >= min;
    },

    maxLength(value, max) {
        return !value || value.length <= max;
    },

    isNumeric(value) {
        return !isNaN(value) && !isNaN(parseFloat(value));
    },

    isPositive(value) {
        return this.isNumeric(value) && parseFloat(value) > 0;
    },

    validateForm(data, rules) {
        const errors = {};
        
        Object.keys(rules).forEach(field => {
            const fieldRules = rules[field];
            const value = data[field];
            
            fieldRules.forEach(rule => {
                if (typeof rule === 'function') {
                    const result = rule(value);
                    if (result !== true) {
                        if (!errors[field]) errors[field] = [];
                        errors[field].push(result);
                    }
                } else if (typeof rule === 'object') {
                    const { method, message, params = [] } = rule;
                    if (this[method] && !this[method](value, ...params)) {
                        if (!errors[field]) errors[field] = [];
                        errors[field].push(message);
                    }
                }
            });
        });
        
        return {
            isValid: Object.keys(errors).length === 0,
            errors
        };
    }
};

// DOM Utilities
export const DomUtils = {
    createElement(tag, attributes = {}, children = []) {
        const element = document.createElement(tag);
        
        Object.keys(attributes).forEach(key => {
            if (key === 'className') {
                element.className = attributes[key];
            } else if (key.startsWith('data-')) {
                element.setAttribute(key, attributes[key]);
            } else {
                element[key] = attributes[key];
            }
        });
        
        children.forEach(child => {
            if (typeof child === 'string') {
                element.appendChild(document.createTextNode(child));
            } else {
                element.appendChild(child);
            }
        });
        
        return element;
    },

    addEventListenerOnce(element, event, handler) {
        const wrappedHandler = function(e) {
            handler(e);
            element.removeEventListener(event, wrappedHandler);
        };
        element.addEventListener(event, wrappedHandler);
    },

    isElementInViewport(element) {
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    },

    scrollToElement(element, offset = 0) {
        const elementPosition = element.offsetTop;
        const offsetPosition = elementPosition - offset;
        
        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });
    },

    copyToClipboard(text) {
        if (navigator.clipboard) {
            return navigator.clipboard.writeText(text);
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                document.body.removeChild(textArea);
                return Promise.resolve();
            } catch (err) {
                document.body.removeChild(textArea);
                return Promise.reject(err);
            }
        }
    }
};

// Local Storage Utilities
export const StorageUtils = {
    set(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (error) {
            console.error('Failed to save to localStorage:', error);
            return false;
        }
    },

    get(key, defaultValue = null) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : defaultValue;
        } catch (error) {
            console.error('Failed to read from localStorage:', error);
            return defaultValue;
        }
    },

    remove(key) {
        try {
            localStorage.removeItem(key);
            return true;
        } catch (error) {
            console.error('Failed to remove from localStorage:', error);
            return false;
        }
    },

    clear() {
        try {
            localStorage.clear();
            return true;
        } catch (error) {
            console.error('Failed to clear localStorage:', error);
            return false;
        }
    },

    exists(key) {
        return localStorage.getItem(key) !== null;
    }
};

// Debounce and Throttle Utilities
export const TimingUtils = {
    debounce(func, wait, immediate = false) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                timeout = null;
                if (!immediate) func(...args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func(...args);
        };
    },

    throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
};

// URL Utilities
export const UrlUtils = {
    getParam(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    },

    setParam(name, value) {
        const url = new URL(window.location);
        url.searchParams.set(name, value);
        window.history.pushState({}, '', url);
    },

    removeParam(name) {
        const url = new URL(window.location);
        url.searchParams.delete(name);
        window.history.pushState({}, '', url);
    },

    buildUrl(base, params = {}) {
        const url = new URL(base, window.location.origin);
        Object.keys(params).forEach(key => {
            if (params[key] !== null && params[key] !== undefined) {
                url.searchParams.set(key, params[key]);
            }
        });
        return url.toString();
    }
};

// Export all utilities as a single object for easier importing
export default {
    DateUtils,
    StringUtils,
    NumberUtils,
    ValidationUtils,
    DomUtils,
    StorageUtils,
    TimingUtils,
    UrlUtils
};

// Make utilities available globally
window.Utils = {
    DateUtils,
    StringUtils,
    NumberUtils,
    ValidationUtils,
    DomUtils,
    StorageUtils,
    TimingUtils,
    UrlUtils
};