<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Services\SearchService;

trait Searchable
{
    /**
     * Apply search and filters to the model query.
     */
    public function scopeAdvancedSearch(Builder $query, Request $request): Builder
    {
        $searchService = new SearchService();
        $modelName = class_basename($this);
        $config = SearchService::getSearchConfig($modelName);
        
        return $searchService->applySearch(
            $query,
            $request,
            $config['searchable_fields'] ?? [],
            $config['filter_fields'] ?? []
        );
    }

    /**
     * Get search configuration for this model.
     */
    public static function getSearchConfiguration(): array
    {
        $modelName = class_basename(static::class);
        return SearchService::getSearchConfig($modelName);
    }

    /**
     * Get filter options for this model.
     */
    public static function getFilterOptions(): array
    {
        $modelName = class_basename(static::class);
        return SearchService::getFilterOptions($modelName);
    }

    /**
     * Perform advanced search with pagination.
     */
    public static function advancedSearchWithPagination(Request $request, int $defaultPerPage = 20): array
    {
        $searchService = new SearchService();
        $query = static::query();
        
        // Apply search and filters
        $query = (new static())->scopeAdvancedSearch($query, $request);
        
        // Get paginated results
        return $searchService->getPaginatedResults($query, $request, $defaultPerPage);
    }

    /**
     * Build search form data for frontend.
     */
    public static function getSearchFormData(): array
    {
        $modelName = class_basename(static::class);
        $config = SearchService::getSearchConfig($modelName);
        $filterOptions = SearchService::getFilterOptions($modelName);
        
        return [
            'searchable_fields' => array_keys($config['searchable_fields'] ?? []),
            'filter_fields' => array_keys($config['filter_fields'] ?? []),
            'filter_options' => $filterOptions,
            'sortable_fields' => static::getSortableFields(),
        ];
    }

    /**
     * Get sortable fields for this model.
     * Override in model if needed.
     */
    public static function getSortableFields(): array
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Export search results to array.
     */
    public static function exportSearchResults(Request $request, array $columns = []): array
    {
        $query = static::query();
        $query = (new static())->scopeAdvancedSearch($query, $request);
        
        if (empty($columns)) {
            return $query->get()->toArray();
        }
        
        return $query->get($columns)->toArray();
    }

    /**
     * Get search suggestions based on a field.
     */
    public static function getSearchSuggestions(string $field, string $term, int $limit = 10): array
    {
        $query = static::query();
        
        // Check if field exists on the model
        if (!in_array($field, (new static())->getFillable()) && 
            !in_array($field, ['id', 'created_at', 'updated_at'])) {
            return [];
        }
        
        return $query->where($field, 'like', "%{$term}%")
                    ->distinct()
                    ->limit($limit)
                    ->pluck($field)
                    ->filter()
                    ->values()
                    ->toArray();
    }

    /**
     * Get quick stats for the model with current filters.
     */
    public function scopeGetQuickStats(Builder $query): array
    {
        $total = $query->count();
        
        $stats = [
            'total' => $total,
        ];
        
        // Add model-specific stats
        if (method_exists($this, 'getModelSpecificStats')) {
            $stats = array_merge($stats, $this->getModelSpecificStats($query));
        }
        
        return $stats;
    }

    /**
     * Perform a global search across multiple fields.
     */
    public function scopeGlobalSearch(Builder $query, string $term): Builder
    {
        $modelName = class_basename($this);
        $config = SearchService::getSearchConfig($modelName);
        $searchableFields = $config['searchable_fields'] ?? [];
        
        if (empty($searchableFields)) {
            return $query;
        }
        
        return $query->where(function ($q) use ($term, $searchableFields) {
            foreach ($searchableFields as $field => $config) {
                if (is_numeric($field)) {
                    // Simple field
                    $q->orWhere($config, 'like', "%{$term}%");
                } elseif (is_array($config) && isset($config['type']) && $config['type'] === 'relationship') {
                    // Relationship field
                    $q->orWhereHas($field, function ($relationQuery) use ($term, $config) {
                        foreach ($config['fields'] as $relationField) {
                            $relationQuery->orWhere($relationField, 'like', "%{$term}%");
                        }
                    });
                } else {
                    // Regular field
                    $q->orWhere($field, 'like', "%{$term}%");
                }
            }
        });
    }
}