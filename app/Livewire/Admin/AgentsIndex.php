<?php

namespace App\Livewire\Admin;

use App\Models\AgentActivity;
use App\Models\User;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AgentsIndex extends Component
{
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'activity_status', except: '')]
    public string $activity_status = '';

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
            'activity_status',
        ]);

        $this->resetPage();
    }

    public function getActiveFilterCountProperty(): int
    {
        return count(array_filter([
            $this->search,
            $this->activity_status,
        ], fn ($value) => filled($value)));
    }

    public function render()
    {
        $query = User::query()
            ->whereHas('role', function ($roleQuery) {
                $roleQuery->where('name', 'agent');
            });

        if ($this->search !== '') {
            $search = trim($this->search);

            $query->where(function ($agentQuery) use ($search) {
                $agentQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($this->activity_status === 'active_today') {
            $query->whereHas('agentActivities', function ($activityQuery) {
                $activityQuery->whereDate('activity_time', today());
            });
        } elseif ($this->activity_status === 'inactive_today') {
            $query->whereDoesntHave('agentActivities', function ($activityQuery) {
                $activityQuery->whereDate('activity_time', today());
            });
        }

        $agents = $query
            ->with([
                'role',
                'agentActivities' => function ($activityQuery) {
                    $activityQuery->whereDate('activity_time', today())->orderByDesc('activity_time');
                },
            ])
            ->orderBy('name')
            ->paginate(15);

        $activeTodayCount = 0;
        $checkedInCount = 0;
        $totalPageViews = 0;

        foreach ($agents as $agent) {
            $agent->today_activities = AgentActivity::getTodayActivities($agent->id);
            $agent->work_hours = AgentActivity::getTodayWorkHours($agent->id);
            $agent->page_visits = AgentActivity::getTodayPageVisits($agent->id);

            if ($agent->today_activities->count() > 0) {
                $activeTodayCount++;
            }

            if (($agent->work_hours['total_hours'] ?? 0) > 0) {
                $checkedInCount++;
            }

            $totalPageViews += $agent->page_visits->count();
        }

        return view('livewire.admin.agents-index', [
            'agents' => $agents,
            'activeTodayCount' => $activeTodayCount,
            'checkedInCount' => $checkedInCount,
            'totalPageViews' => $totalPageViews,
        ]);
    }
}
