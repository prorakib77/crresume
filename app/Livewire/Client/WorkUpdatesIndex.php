<?php

namespace App\Livewire\Client;

use App\Support\WorkUpdateFilters;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class WorkUpdatesIndex extends Component
{
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'date_from', except: '')]
    public string $date_from = '';

    #[Url(as: 'date_to', except: '')]
    public string $date_to = '';

    #[Url(as: 'application_status', except: '')]
    public string $application_status = '';

    public function updated($name): void
    {
        if ($name === 'page' || str_starts_with((string) $name, 'paginators.')) {
            return;
        }

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'date_from',
            'date_to',
            'application_status',
        ]);

        $this->resetPage();
    }

    public function getActiveFilterCountProperty(): int
    {
        return count(WorkUpdateFilters::clean($this->filters()));
    }

    public function getPdfUrlProperty(): string
    {
        return route('client.work-updates.download.pdf', WorkUpdateFilters::clean($this->filters()));
    }

    public function getCsvUrlProperty(): string
    {
        return route('client.work-updates.download.csv', WorkUpdateFilters::clean($this->filters()));
    }

    public function render()
    {
        $user = Auth::user();
        $query = WorkUpdateFilters::client($user, $this->filters());

        $workUpdates = (clone $query)
            ->latest('applied_date')
            ->latest('created_at')
            ->paginate(12);

        return view('livewire.client.work-updates-index', [
            'workUpdates' => $workUpdates,
            'groupedUpdates' => $workUpdates->getCollection()->groupBy(function ($update) {
                return ($update->applied_date ?? $update->created_at)->format('Y-m-d');
            }),
            'stats' => [
                'total_updates' => (clone $query)->count(),
                'this_month' => (clone $query)
                    ->whereMonth('applied_date', now()->month)
                    ->whereYear('applied_date', now()->year)
                    ->count(),
                'last_update' => (clone $query)
                    ->latest('applied_date')
                    ->latest('created_at')
                    ->first(),
            ],
        ]);
    }

    private function filters(): array
    {
        return [
            'search' => $this->search,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
            'application_status' => $this->application_status,
        ];
    }
}
