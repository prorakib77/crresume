<?php

namespace App\Support;

use App\Models\User;
use App\Models\WorkUpdate;
use Illuminate\Database\Eloquent\Builder;

class WorkUpdateFilters
{
    public static function admin(array $filters): Builder
    {
        $filters = static::normalize($filters);

        $query = WorkUpdate::with(['agent', 'client'])
            ->whereHas('agent')
            ->whereHas('client');

        if (filled($filters['submission'] ?? null)) {
            $submission = (new WorkUpdate())->resolveRouteBinding($filters['submission']);

            if ($submission) {
                $query->whereKey($submission->getKey());
            }
        }

        if (filled($filters['search'] ?? null)) {
            $query->search($filters['search']);
        }

        if (filled($filters['client_id'] ?? null)) {
            $query->where('client_id', $filters['client_id']);
        }

        if (filled($filters['agent_id'] ?? null)) {
            $query->where('agent_id', $filters['agent_id']);
        }

        if (filled($filters['application_status'] ?? null)) {
            $query->where('application_status', $filters['application_status']);
        }

        if (filled($filters['status'] ?? null)) {
            $query->where('status', $filters['status']);
        }

        static::applyDateRange($query, $filters);

        return $query;
    }

    public static function agent(User $user, array $filters): Builder
    {
        $filters = static::normalize($filters);

        $query = WorkUpdate::with(['client'])
            ->where('agent_id', $user->id);

        if (filled($filters['search'] ?? null)) {
            $search = $filters['search'];

            $query->where(function (Builder $workUpdateQuery) use ($search) {
                $workUpdateQuery->where('job_title', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%")
                    ->orWhereHas('client', function (Builder $clientQuery) use ($search) {
                        $clientQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if (filled($filters['client_id'] ?? null)) {
            $query->where('client_id', $filters['client_id']);
        }

        if (filled($filters['application_status'] ?? null)) {
            $query->where('application_status', $filters['application_status']);
        }

        if (filled($filters['status'] ?? null)) {
            $query->where('status', $filters['status']);
        }

        static::applyDateRange($query, $filters);

        return $query;
    }

    public static function client(User $user, array $filters): Builder
    {
        $filters = static::normalize($filters);

        $query = WorkUpdate::query()
            ->where('client_id', $user->id)
            ->whereIn('status', [WorkUpdate::STATUS_SUBMITTED, WorkUpdate::STATUS_APPROVED]);

        if (filled($filters['search'] ?? null)) {
            $search = $filters['search'];

            $query->where(function (Builder $workUpdateQuery) use ($search) {
                $workUpdateQuery->where('job_title', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%");
            });
        }

        if (filled($filters['application_status'] ?? null)) {
            $query->where('application_status', $filters['application_status']);
        }

        static::applyDateRange($query, $filters);

        return $query;
    }

    public static function clean(array $filters): array
    {
        return collect(static::normalize($filters))
            ->filter(function ($value) {
                return filled($value);
            })
            ->all();
    }

    private static function normalize(array $filters): array
    {
        return collect($filters)
            ->map(function ($value) {
                return is_string($value) ? trim($value) : $value;
            })
            ->all();
    }

    private static function applyDateRange(Builder $query, array $filters): void
    {
        if (filled($filters['date_from'] ?? null)) {
            $query->whereDate('applied_date', '>=', $filters['date_from']);
        }

        if (filled($filters['date_to'] ?? null)) {
            $query->whereDate('applied_date', '<=', $filters['date_to']);
        }
    }
}
