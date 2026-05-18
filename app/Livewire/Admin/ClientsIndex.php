<?php

namespace App\Livewire\Admin;

use App\Models\AgentClientAssignment;
use App\Models\ClientSubmission;
use App\Models\User;
use App\Services\NoticeService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ClientsIndex extends Component
{
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'service_status', except: '')]
    public string $service_status = '';

    #[Url(as: 'onboarding_status', except: '')]
    public string $onboarding_status = '';

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
            'service_status',
            'onboarding_status',
        ]);

        $this->resetPage();
    }

    public function getActiveFilterCountProperty(): int
    {
        return count(array_filter([
            $this->search,
            $this->service_status,
            $this->onboarding_status,
        ], fn ($value) => filled($value)));
    }

    public function markServiceCompleted(int $clientId): void
    {
        $assignment = AgentClientAssignment::query()
            ->where('client_id', $clientId)
            ->where('is_active', true)
            ->latest('assigned_date')
            ->latest('id')
            ->first();

        if (!$assignment) {
            return;
        }

        if (!$assignment->isServiceCompleted()) {
            $assignment->update([
                'service_completed_at' => now(),
                'service_completed_by' => auth()->id(),
            ]);
        }

        $client = User::find($clientId);

        if ($client) {
            app(NoticeService::class)->syncClientServiceNotice($client, $assignment->fresh());
        }
    }

    public function render()
    {
        $query = User::query()
            ->whereHas('role', function ($roleQuery) {
                $roleQuery->where('name', 'client');
            });

        if ($this->search !== '') {
            $search = trim($this->search);

            $query->where(function ($clientQuery) use ($search) {
                $clientQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($this->service_status === 'active') {
            $query->whereHas('clientAssignments', function ($assignmentQuery) {
                $assignmentQuery->where('is_active', true)
                    ->whereNull('service_completed_at')
                    ->where(function ($serviceQuery) {
                        $serviceQuery->whereNull('service_end_date')
                            ->orWhereDate('service_end_date', '>=', today());
                    });
            });
        } elseif ($this->service_status === 'expired') {
            $query->whereHas('clientAssignments', function ($assignmentQuery) {
                $assignmentQuery->where('is_active', true)
                    ->whereNull('service_completed_at')
                    ->whereNotNull('service_end_date')
                    ->whereDate('service_end_date', '<', today());
            });
        } elseif ($this->service_status === 'completed') {
            $query->whereHas('clientAssignments', function ($assignmentQuery) {
                $assignmentQuery->where('is_active', true)
                    ->whereNotNull('service_completed_at');
            });
        } elseif ($this->service_status === 'unassigned') {
            $query->whereDoesntHave('clientAssignments', function ($assignmentQuery) {
                $assignmentQuery->where('is_active', true);
            });
        }

        if ($this->onboarding_status === \App\Models\ClientProfile::ONBOARDING_STATUS_COMPLETED) {
            $query->whereHas('clientProfile', function ($profileQuery) {
                $profileQuery->where('onboarding_status', \App\Models\ClientProfile::ONBOARDING_STATUS_COMPLETED);
            });
        } elseif ($this->onboarding_status === \App\Models\ClientProfile::ONBOARDING_STATUS_PENDING) {
            $query->whereHas('clientProfile', function ($profileQuery) {
                $profileQuery->where('onboarding_status', \App\Models\ClientProfile::ONBOARDING_STATUS_PENDING);
            });
        } elseif ($this->onboarding_status === \App\Models\ClientProfile::ONBOARDING_STATUS_REQUESTED_AGAIN) {
            $query->whereHas('clientProfile', function ($profileQuery) {
                $profileQuery->where('onboarding_status', \App\Models\ClientProfile::ONBOARDING_STATUS_REQUESTED_AGAIN);
            });
        }

        $query->addSelect([
            'latest_assignment_date' => AgentClientAssignment::query()
                ->select('assigned_date')
                ->whereColumn('client_id', 'users.id')
                ->where('is_active', true)
                ->newestFirst()
                ->limit(1),
            'latest_assignment_id' => AgentClientAssignment::query()
                ->select('id')
                ->whereColumn('client_id', 'users.id')
                ->where('is_active', true)
                ->newestFirst()
                ->limit(1),
        ]);

        $clients = $query
            ->with([
                'role',
                'clientProfile',
                'clientSubmissions' => function ($submissionQuery) {
                    $submissionQuery->latest('created_at');
                },
            ])
            ->orderByDesc('latest_assignment_date')
            ->orderByDesc('latest_assignment_id')
            ->orderBy('name')
            ->paginate(15);

        $assignedCount = 0;
        $totalSubmissions = 0;
        $activeServicesCount = 0;

        foreach ($clients as $client) {
            $client->assignment = AgentClientAssignment::query()
                ->where('client_id', $client->id)
                ->where('is_active', true)
                ->latest('assigned_date')
                ->latest('id')
                ->with('agent')
                ->first();

            $client->recent_submissions = ClientSubmission::where('client_id', $client->id)
                ->latest('created_at')
                ->limit(3)
                ->get();

            if ($client->assignment) {
                $assignedCount++;

                if (
                    !$client->assignment->isServiceCompleted()
                    && (!$client->assignment->service_end_date || $client->assignment->service_end_date > now())
                ) {
                    $activeServicesCount++;
                }
            }

            $totalSubmissions += $client->recent_submissions->count();
        }

        return view('livewire.admin.clients-index', [
            'clients' => $clients,
            'assignedCount' => $assignedCount,
            'totalSubmissions' => $totalSubmissions,
            'activeServicesCount' => $activeServicesCount,
        ]);
    }
}
