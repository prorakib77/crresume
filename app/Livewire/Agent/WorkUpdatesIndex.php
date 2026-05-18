<?php

namespace App\Livewire\Agent;

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

    #[Url(as: 'client_id', except: '')]
    public string $client_id = '';

    #[Url(as: 'application_status', except: '')]
    public string $application_status = '';

    #[Url(as: 'status', except: '')]
    public string $status = '';

    #[Url(as: 'date_from', except: '')]
    public string $date_from = '';

    #[Url(as: 'date_to', except: '')]
    public string $date_to = '';

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
            'client_id',
            'application_status',
            'status',
            'date_from',
            'date_to',
        ]);

        $this->resetPage();
    }

    public function getActiveFilterCountProperty(): int
    {
        return count(WorkUpdateFilters::clean($this->filters()));
    }

    public function getPdfUrlProperty(): string
    {
        return route('agent.work-updates.download.pdf', WorkUpdateFilters::clean($this->filters()));
    }

    public function getCsvUrlProperty(): string
    {
        return route('agent.work-updates.download.csv', WorkUpdateFilters::clean($this->filters()));
    }

    public function render()
    {
        $user = Auth::user();
        $query = WorkUpdateFilters::agent($user, $this->filters());

        return view('livewire.agent.work-updates-index', [
            'workUpdates' => (clone $query)
                ->latest('applied_date')
                ->latest('created_at')
                ->paginate(15),
            'clients' => $user->active_clients->sortBy('name')->values(),
        ]);
    }

    private function filters(): array
    {
        return [
            'search' => $this->search,
            'client_id' => $this->client_id,
            'application_status' => $this->application_status,
            'status' => $this->status,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
        ];
    }
}
