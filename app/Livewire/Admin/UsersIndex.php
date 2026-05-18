<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use App\Models\User;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class UsersIndex extends Component
{
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'role_id', except: '')]
    public string $role_id = '';

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
            'role_id',
            'status',
        ]);

        $this->resetPage();
    }

    public function getActiveFilterCountProperty(): int
    {
        return count(array_filter([
            $this->search,
            $this->role_id,
            $this->status,
        ], fn ($value) => filled($value)));
    }

    public function render()
    {
        $query = User::query()->with('role');

        if ($this->search !== '') {
            $search = trim($this->search);

            $query->where(function ($userQuery) use ($search) {
                $userQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($this->role_id !== '') {
            $query->where('role_id', $this->role_id);
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        $statsBaseQuery = clone $query;
        $users = $query->latest('created_at')->paginate(15);
        $roles = Role::orderBy('name')->get();

        return view('livewire.admin.users-index', [
            'users' => $users,
            'roles' => $roles,
            'stats' => [
                'total' => (clone $statsBaseQuery)->count(),
                'active' => (clone $statsBaseQuery)->where('status', User::STATUS_ACTIVE)->count(),
                'admins' => (clone $statsBaseQuery)->whereIn('role_id', [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN])->count(),
            ],
        ]);
    }
}
