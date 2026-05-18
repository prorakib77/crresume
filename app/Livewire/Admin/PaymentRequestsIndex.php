<?php

namespace App\Livewire\Admin;

use App\Models\PaymentRequest;
use App\Models\User;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentRequestsIndex extends Component
{
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public string $status = '';

    #[Url(as: 'service_status', except: '')]
    public string $service_status = '';

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
            'service_status',
        ]);

        $this->resetPage();
    }

    public function getActiveFilterCountProperty(): int
    {
        return count(array_filter([
            $this->search,
            $this->status,
            $this->service_status,
        ], fn ($value) => filled($value)));
    }

    public function render()
    {
        $query = PaymentRequest::query()
            ->with(['client', 'requester', 'approver', 'rejector', 'canceller'])
            ->latest();

        if ($this->status !== '') {
            if ($this->status === PaymentRequest::STATUS_REJECTED) {
                $query->where('status', PaymentRequest::STATUS_PENDING)
                    ->whereNotNull('rejected_at')
                    ->whereNull('cancelled_at');
            } elseif ($this->status === PaymentRequest::STATUS_CANCELLED) {
                $query->whereNotNull('cancelled_at');
            } elseif ($this->status === PaymentRequest::STATUS_PENDING) {
                $query->where('status', PaymentRequest::STATUS_PENDING)
                    ->whereNull('rejected_at')
                    ->whereNull('cancelled_at');
            } else {
                $query->where('status', $this->status)
                    ->whereNull('cancelled_at');
            }
        }

        if ($this->search !== '') {
            $search = trim($this->search);

            $query->where(function ($paymentRequestQuery) use ($search) {
                $paymentRequestQuery->where('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($clientQuery) use ($search) {
                        $clientQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($this->service_status === 'active') {
            $query->whereHas('client', function ($clientQuery) {
                $clientQuery->whereHas('clientProfile', function ($profileQuery) {
                    $profileQuery->where(function ($serviceQuery) {
                        $serviceQuery->whereNull('service_end_date')
                            ->orWhere('service_end_date', '>=', now());
                    });
                });
            });
        } elseif ($this->service_status === 'expired') {
            $query->whereHas('client', function ($clientQuery) {
                $clientQuery->whereHas('clientProfile', function ($profileQuery) {
                    $profileQuery->whereNotNull('service_end_date')
                        ->where('service_end_date', '<', now());
                });
            });
        }

        $requests = $query->paginate(15);
        $clients = User::where('role_id', User::ROLE_CLIENT)->orderBy('name')->get();

        return view('livewire.admin.payment-requests-index', [
            'requests' => $requests,
            'clients' => $clients,
            'stats' => [
                'total' => (clone $query)->count(),
                'pending' => (clone $query)->where('status', PaymentRequest::STATUS_PENDING)->whereNull('rejected_at')->whereNull('cancelled_at')->count(),
                'approved' => (clone $query)->where('status', PaymentRequest::STATUS_APPROVED)->whereNull('cancelled_at')->count(),
                'marked' => (clone $query)->where('status', PaymentRequest::STATUS_CLIENT_MARKED)->whereNull('cancelled_at')->count(),
                'cancelled' => (clone $query)->whereNotNull('cancelled_at')->count(),
            ],
        ]);
    }
}
