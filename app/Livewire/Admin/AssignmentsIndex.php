<?php

namespace App\Livewire\Admin;

use App\Models\AgentClientAssignment;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AssignmentsIndex extends Component
{
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public string $status = '';

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
            'status',
        ]);

        $this->resetPage();
    }

    public function getActiveFilterCountProperty(): int
    {
        return count(array_filter([
            $this->search,
            $this->status,
        ], fn ($value) => filled($value)));
    }

    public function render()
    {
        $query = AgentClientAssignment::query()
            ->with(['agent', 'client'])
            ->whereHas('agent')
            ->whereHas('client');

        if ($this->search !== '') {
            $search = trim($this->search);

            $query->where(function ($assignmentQuery) use ($search) {
                $assignmentQuery->whereHas('agent', function ($agentQuery) use ($search) {
                    $agentQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                })->orWhereHas('client', function ($clientQuery) use ($search) {
                    $clientQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            });
        }

        if ($this->status === 'active') {
            $query->where('is_active', true);
        } elseif ($this->status === 'inactive') {
            $query->where('is_active', false);
        }

        $assignments = $query->latest('created_at')->paginate(15);

        return view('livewire.admin.assignments-index', [
            'assignments' => $assignments,
            'stats' => [
                'total' => (clone $query)->count(),
                'active' => (clone $query)->where('is_active', true)->count(),
                'inactive' => (clone $query)->where('is_active', false)->count(),
            ],
        ]);
    }
}
