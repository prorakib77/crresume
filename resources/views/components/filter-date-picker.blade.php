@props([
    'wireModel',
    'label' => '',
    'placeholder' => 'Select date',
    'id' => null,
])

@php
    $inputId = $id ?: 'filter_date_' . str_replace(['.', '[', ']'], '_', $wireModel);
@endphp

<div
    class="work-filter-field"
    x-data="{
        open: false,
        state: @entangle($wireModel).live,
        placeholder: @js($placeholder),
        weekdays: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        viewYear: 0,
        viewMonth: 0,
        init() {
            const baseDate = this.parseDate(this.state) ?? new Date();
            this.viewYear = baseDate.getFullYear();
            this.viewMonth = baseDate.getMonth();

            this.$watch('state', (value) => {
                const parsed = this.parseDate(value);
                if (parsed) {
                    this.viewYear = parsed.getFullYear();
                    this.viewMonth = parsed.getMonth();
                }
            });
        },
        parseDate(value) {
            if (!value) {
                return null;
            }

            const [year, month, day] = String(value).split('-').map(Number);

            if (!year || !month || !day) {
                return null;
            }

            return new Date(year, month - 1, day);
        },
        formatDate(value) {
            const parsed = this.parseDate(value);

            if (!parsed) {
                return this.placeholder;
            }

            return parsed.toLocaleDateString(undefined, {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
            });
        },
        get triggerText() {
            return this.state ? this.formatDate(this.state) : this.placeholder;
        },
        get monthLabel() {
            return new Date(this.viewYear, this.viewMonth, 1).toLocaleDateString(undefined, {
                month: 'long',
                year: 'numeric',
            });
        },
        get monthDays() {
            const firstDay = new Date(this.viewYear, this.viewMonth, 1);
            const lastDay = new Date(this.viewYear, this.viewMonth + 1, 0);
            const days = [];

            for (let i = 0; i < firstDay.getDay(); i += 1) {
                days.push({ blank: true, key: `blank-${i}` });
            }

            for (let day = 1; day <= lastDay.getDate(); day += 1) {
                const value = `${this.viewYear}-${String(this.viewMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                days.push({
                    blank: false,
                    day,
                    value,
                    isToday: value === this.todayValue(),
                });
            }

            return days;
        },
        previousMonth() {
            if (this.viewMonth === 0) {
                this.viewMonth = 11;
                this.viewYear -= 1;
                return;
            }

            this.viewMonth -= 1;
        },
        nextMonth() {
            if (this.viewMonth === 11) {
                this.viewMonth = 0;
                this.viewYear += 1;
                return;
            }

            this.viewMonth += 1;
        },
        todayValue() {
            const today = new Date();
            return `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
        },
        choose(value) {
            this.state = value;
            this.open = false;
        },
        clear() {
            this.state = '';
            this.open = false;
        },
        isSelected(value) {
            return String(this.state ?? '') === String(value);
        },
    }"
    @keydown.escape.stop="open = false"
    @click.outside="open = false"
>
    @if($label !== '')
        <label class="form-label" for="{{ $inputId }}_trigger">{{ $label }}</label>
    @endif

    <div class="work-filter-date" :class="{ 'is-open': open }">
        <button
            type="button"
            id="{{ $inputId }}_trigger"
            class="work-filter-date-trigger"
            @click="open = !open"
            :aria-expanded="open.toString()"
        >
            <span class="work-filter-date-trigger-icon">
                <i class="fas fa-calendar-alt"></i>
            </span>
            <span class="work-filter-date-trigger-text" :class="{ 'is-placeholder': !state }" x-text="triggerText"></span>
        </button>

        <div x-cloak x-show="open" x-transition class="work-filter-date-panel">
            <div class="work-filter-date-header">
                <button type="button" class="work-filter-date-nav" @click="previousMonth()">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="work-filter-date-title" x-text="monthLabel"></div>
                <button type="button" class="work-filter-date-nav" @click="nextMonth()">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <div class="work-filter-date-weekdays">
                <template x-for="weekday in weekdays" :key="weekday">
                    <span x-text="weekday"></span>
                </template>
            </div>

            <div class="work-filter-date-grid">
                <template x-for="entry in monthDays" :key="entry.key ?? entry.value">
                    <div>
                        <template x-if="entry.blank">
                            <span class="work-filter-date-day is-blank"></span>
                        </template>

                        <template x-if="!entry.blank">
                            <button
                                type="button"
                                class="work-filter-date-day"
                                :class="{
                                    'is-selected': isSelected(entry.value),
                                    'is-today': entry.isToday && !isSelected(entry.value)
                                }"
                                @click="choose(entry.value)"
                                x-text="entry.day"
                            ></button>
                        </template>
                    </div>
                </template>
            </div>

            <div class="work-filter-date-footer">
                <button type="button" class="work-filter-date-link" @click="choose(todayValue())">Today</button>
                <button type="button" class="work-filter-date-link" @click="clear()">Clear</button>
            </div>
        </div>
    </div>
</div>
