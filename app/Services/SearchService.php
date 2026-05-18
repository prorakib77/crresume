<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchService
{
    /**
     * Apply search and filters to a query.
     */
    public function applySearch(Builder $query, Request $request, array $searchableFields = [], array $filterFields = []): Builder
    {
        // Apply search
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query = $this->applySearchTerm($query, $searchTerm, $searchableFields);
        }

        // Apply filters
        foreach ($filterFields as $field => $config) {
            if ($request->filled($field)) {
                $value = $request->get($field);
                $query = $this->applyFilter($query, $field, $value, $config);
            }
        }

        // Apply date range filters
        if ($request->filled('date_from') || $request->filled('date_to')) {
            $query = $this->applyDateRange($query, $request, $filterFields['date_field'] ?? 'created_at');
        }

        // Apply sorting
        if ($request->filled('sort_by')) {
            $sortBy = $request->get('sort_by');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortBy, $sortDirection);
        }

        return $query;
    }

    /**
     * Apply search term to query.
     */
    private function applySearchTerm(Builder $query, string $searchTerm, array $searchableFields): Builder
    {
        return $query->where(function ($q) use ($searchTerm, $searchableFields) {
            foreach ($searchableFields as $field => $config) {
                if (is_numeric($field)) {
                    // Simple field name
                    $q->orWhere($config, 'like', "%{$searchTerm}%");
                } elseif (is_array($config)) {
                    // Field with configuration
                    if (isset($config['type']) && $config['type'] === 'relationship') {
                        $q->orWhereHas($field, function ($relationQuery) use ($searchTerm, $config) {
                            foreach ($config['fields'] as $relationField) {
                                $relationQuery->orWhere($relationField, 'like', "%{$searchTerm}%");
                            }
                        });
                    } elseif (isset($config['type']) && $config['type'] === 'json') {
                        $q->orWhereJsonContains($field, $searchTerm);
                    } else {
                        $q->orWhere($field, 'like', "%{$searchTerm}%");
                    }
                } else {
                    // Field with custom operator
                    $q->orWhere($field, 'like', "%{$searchTerm}%");
                }
            }
        });
    }

    /**
     * Apply filter to query.
     */
    private function applyFilter(Builder $query, string $field, $value, array $config): Builder
    {
        $type = $config['type'] ?? 'exact';
        $column = $config['column'] ?? $field;

        return match($type) {
            'exact' => $query->where($column, $value),
            'in' => $query->whereIn($column, is_array($value) ? $value : [$value]),
            'range' => $this->applyRangeFilter($query, $column, $value),
            'relationship' => $this->applyRelationshipFilter($query, $field, $value, $config),
            'boolean' => $query->where($column, filter_var($value, FILTER_VALIDATE_BOOLEAN)),
            'like' => $query->where($column, 'like', "%{$value}%"),
            default => $query->where($column, $value),
        };
    }

    /**
     * Apply range filter (for numbers, dates).
     */
    private function applyRangeFilter(Builder $query, string $column, array $value): Builder
    {
        if (isset($value['min'])) {
            $query->where($column, '>=', $value['min']);
        }
        if (isset($value['max'])) {
            $query->where($column, '<=', $value['max']);
        }
        return $query;
    }

    /**
     * Apply relationship filter.
     */
    private function applyRelationshipFilter(Builder $query, string $relationship, $value, array $config): Builder
    {
        $relationColumn = $config['relation_column'] ?? 'id';
        
        return $query->whereHas($relationship, function ($relationQuery) use ($relationColumn, $value) {
            $relationQuery->where($relationColumn, $value);
        });
    }

    /**
     * Apply date range filter.
     */
    private function applyDateRange(Builder $query, Request $request, string $dateField): Builder
    {
        if ($request->filled('date_from')) {
            $query->whereDate($dateField, '>=', $request->get('date_from'));
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate($dateField, '<=', $request->get('date_to'));
        }
        
        return $query;
    }

    /**
     * Get paginated results with metadata.
     */
    public function getPaginatedResults(Builder $query, Request $request, int $defaultPerPage = 20): array
    {
        $perPage = $request->get('per_page', $defaultPerPage);
        $perPage = min(max((int)$perPage, 1), 100); // Limit between 1 and 100
        
        $results = $query->paginate($perPage);
        
        return [
            'data' => $results->items(),
            'pagination' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
                'from' => $results->firstItem(),
                'to' => $results->lastItem(),
            ],
            'links' => [
                'first' => $results->url(1),
                'last' => $results->url($results->lastPage()),
                'prev' => $results->previousPageUrl(),
                'next' => $results->nextPageUrl(),
            ],
        ];
    }

    /**
     * Get search configuration for different models.
     */
    public static function getSearchConfig(string $model): array
    {
        return match($model) {
            'User' => [
                'searchable_fields' => [
                    'name',
                    'email',
                    'role' => [
                        'type' => 'relationship',
                        'fields' => ['name', 'display_name']
                    ]
                ],
                'filter_fields' => [
                    'role_id' => ['type' => 'exact', 'column' => 'role_id'],
                    'status' => ['type' => 'exact'],
                    'created_at' => ['type' => 'range'],
                ]
            ],
            'WorkUpdate' => [
                'searchable_fields' => [
                    'job_title',
                    'company',
                    'note',
                    'agent' => [
                        'type' => 'relationship',
                        'fields' => ['name', 'email']
                    ],
                    'client' => [
                        'type' => 'relationship',
                        'fields' => ['name', 'email']
                    ]
                ],
                'filter_fields' => [
                    'status' => ['type' => 'exact'],
                    'applied_method' => ['type' => 'exact'],
                    'agent_id' => ['type' => 'exact'],
                    'client_id' => ['type' => 'exact'],
                    'applied_date' => ['type' => 'range'],
                    'has_proof' => ['type' => 'boolean', 'column' => 'applied_proof'],
                ]
            ],
            'ClientProfile' => [
                'searchable_fields' => [
                    'user' => [
                        'type' => 'relationship',
                        'fields' => ['name', 'email']
                    ],
                    'phone',
                    'address',
                    'apply_to',
                ],
                'filter_fields' => [
                    'status' => ['type' => 'exact'],
                    'service_start_date' => ['type' => 'range'],
                    'service_end_date' => ['type' => 'range'],
                ]
            ],
            'WorkUpdateBatch' => [
                'searchable_fields' => [
                    'agent' => [
                        'type' => 'relationship',
                        'fields' => ['name', 'email']
                    ],
                    'notes',
                ],
                'filter_fields' => [
                    'status' => ['type' => 'exact'],
                    'agent_id' => ['type' => 'exact'],
                    'submission_date' => ['type' => 'range'],
                ]
            ],
            'Role' => [
                'searchable_fields' => [
                    'name',
                    'display_name',
                    'description',
                ],
                'filter_fields' => [
                    'is_active' => ['type' => 'boolean'],
                    'hierarchy_level' => ['type' => 'range'],
                ]
            ],
            'Permission' => [
                'searchable_fields' => [
                    'name',
                    'display_name',
                    'description',
                ],
                'filter_fields' => [
                    'category' => ['type' => 'exact'],
                    'is_active' => ['type' => 'boolean'],
                ]
            ],
            default => [
                'searchable_fields' => [],
                'filter_fields' => []
            ]
        };
    }

    /**
     * Build filter options for frontend.
     */
    public static function getFilterOptions(string $model): array
    {
        return match($model) {
            'User' => [
                'role_id' => \App\Models\Role::active()->pluck('display_name', 'id')->toArray(),
                'status' => ['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended'],
            ],
            'WorkUpdate' => [
                'status' => \App\Models\WorkUpdate::getStatuses(),
                'applied_method' => \App\Models\WorkUpdate::getAppliedMethods(),
                'agent_id' => \App\Models\User::whereHas('role', function($q) {
                    $q->where('name', 'agent');
                })->pluck('name', 'id')->toArray(),
            ],
            'ClientProfile' => [
                'status' => ['active' => 'Active', 'inactive' => 'Inactive', 'expired' => 'Expired'],
            ],
            'WorkUpdateBatch' => [
                'status' => \App\Models\WorkUpdateBatch::getStatuses(),
                'agent_id' => \App\Models\User::whereHas('role', function($q) {
                    $q->where('name', 'agent');
                })->pluck('name', 'id')->toArray(),
            ],
            'Role' => [
                'is_active' => ['1' => 'Active', '0' => 'Inactive'],
            ],
            'Permission' => [
                'category' => \App\Models\Permission::getCategories(),
                'is_active' => ['1' => 'Active', '0' => 'Inactive'],
            ],
            default => []
        };
    }
}