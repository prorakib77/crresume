<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Support\WorkUpdateFilters;
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

    #[Url(as: 'agent_id', except: '')]
    public string $agent_id = '';

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
        // Livewire 4 tracks pagination updates under paginators.*
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
            'agent_id',
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
        return route('admin.work-updates.download.pdf', WorkUpdateFilters::clean($this->filters()));
    }

    public function getCsvUrlProperty(): string
    {
        return route('admin.work-updates.download.csv', WorkUpdateFilters::clean($this->filters()));
    }

    public function render()
    {
        $query = WorkUpdateFilters::admin($this->filters());

        return view('livewire.admin.work-updates-index', [
            'workUpdates' => (clone $query)
                ->latest('applied_date')
                ->latest('created_at')
                ->paginate(15),
            'agents' => User::where('role_id', User::ROLE_AGENT)->orderBy('name')->get(),
            'clients' => User::where('role_id', User::ROLE_CLIENT)->orderBy('name')->get(),
        ]);
    }

    private function filters(): array
    {
        return [
            'search' => $this->search,
            'client_id' => $this->client_id,
            'agent_id' => $this->agent_id,
            'application_status' => $this->application_status,
            'status' => $this->status,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
        ];
    }
}
