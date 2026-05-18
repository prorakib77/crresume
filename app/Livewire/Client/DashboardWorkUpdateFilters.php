<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Url;
use Livewire\Component;

class DashboardWorkUpdateFilters extends Component
{
    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'date_from', except: '')]
    public string $date_from = '';

    #[Url(as: 'date_to', except: '')]
    public string $date_to = '';

    #[Url(as: 'application_status', except: '')]
    public string $application_status = '';

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'date_from',
            'date_to',
            'application_status',
        ]);
    }

    public function getActiveFilterCountProperty(): int
    {
        return count(array_filter([
            $this->search,
            $this->date_from,
            $this->date_to,
            $this->application_status,
        ], fn ($value) => filled($value)));
    }

    public function getViewUrlProperty(): string
    {
        return route('client.work-updates.index', $this->filters());
    }

    public function getPdfUrlProperty(): string
    {
        return route('client.work-updates.download.pdf', $this->filters());
    }

    public function getCsvUrlProperty(): string
    {
        return route('client.work-updates.download.csv', $this->filters());
    }

    public function render()
    {
        return view('livewire.client.dashboard-work-update-filters');
    }

    private function filters(): array
    {
        return array_filter([
            'search' => $this->search,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
            'application_status' => $this->application_status,
        ], fn ($value) => filled($value));
    }
}
