<div x-data="{ filtersOpen: false }" @keydown.escape.window="filtersOpen = false" class="work-updates-shell">
    <div class="work-updates-toolbar">
        <div>
            <p class="work-updates-eyebrow">Work Updates</p>
            <h2 class="theme-display work-updates-heading">Quick Filters</h2>
            <p class="work-updates-copy">Jump straight into your work update history or export the same filtered set without leaving the dashboard.</p>
        </div>

        <div class="work-updates-toolbar-actions">
            <a href="{{ $this->viewUrl }}" class="btn btn-black">
                <i class="fas fa-list me-2"></i>View Updates
            </a>
            <a href="{{ $this->pdfUrl }}" class="btn btn-save">
                <i class="fas fa-file-pdf me-2"></i>PDF
            </a>
            <a href="{{ $this->csvUrl }}" class="btn btn-view">
                <i class="fas fa-file-csv me-2"></i>CSV
            </a>
            <button type="button" class="work-update-filter-button" @click="filtersOpen = true">
                <i class="fas fa-filter"></i>
                <span>Filters</span>
                @if($this->activeFilterCount > 0)
                    <span class="work-update-filter-badge">{{ $this->activeFilterCount }}</span>
                @endif
            </button>
        </div>
    </div>

    <div class="work-update-filter-summary">
        <span><i class="fas fa-arrow-right"></i>Open filtered results</span>
        @if($this->activeFilterCount > 0)
            <span class="work-update-filter-summary-active">Filter shortcut ready</span>
        @endif
    </div>

    <div x-cloak x-show="filtersOpen" class="work-update-filter-backdrop" @click="filtersOpen = false"></div>

    <aside
        x-cloak
        x-show="filtersOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full opacity-0"
        x-transition:enter-end="translate-x-0 opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0 opacity-100"
        x-transition:leave-end="-translate-x-full opacity-0"
        class="work-update-filter-drawer"
    >
        <div class="work-update-filter-drawer-inner">
            <div class="work-update-filter-drawer-header">
                <div>
                    <p class="work-updates-eyebrow">Filter Panel</p>
                    <h3 class="theme-display mb-0 text-2xl text-stone-950">Work Update Filters</h3>
                </div>
                <button type="button" class="work-update-filter-close" @click="filtersOpen = false" aria-label="Close filters">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="work-update-filter-drawer-body">
                <div class="work-update-filter-group">
                    <label class="form-label" for="client_dashboard_work_update_search">Search</label>
                    <input id="client_dashboard_work_update_search" type="text" class="form-control" placeholder="Job title, company, or note" wire:model.live="search">
                </div>

                <div class="work-update-filter-group">
                    <x-filter-select
                        wire-model="application_status"
                        label="Application Status"
                        placeholder="All Statuses"
                        :options="[
                            ['value' => 'applied', 'text' => 'Applied'],
                            ['value' => 'interview', 'text' => 'Interview'],
                            ['value' => 'hired', 'text' => 'Hired'],
                            ['value' => 'rejected', 'text' => 'Rejected'],
                            ['value' => 'incomplete', 'text' => 'Incomplete'],
                        ]"
                    />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="work-update-filter-group">
                        <x-filter-date-picker wire-model="date_from" label="Date From" placeholder="Start date" />
                    </div>

                    <div class="work-update-filter-group">
                        <x-filter-date-picker wire-model="date_to" label="Date To" placeholder="End date" />
                    </div>
                </div>
            </div>

            <div class="work-update-filter-drawer-footer">
                <button type="button" class="btn btn-white w-100" wire:click="resetFilters">
                    <i class="fas fa-rotate-left me-2"></i>Reset Filters
                </button>
                <button type="button" class="btn btn-black w-100" @click="filtersOpen = false">
                    <i class="fas fa-check me-2"></i>Done
                </button>
            </div>
        </div>
    </aside>
</div>
