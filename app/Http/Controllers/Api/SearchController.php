<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkUpdate;
use App\Models\ClientProfile;
use App\Models\WorkUpdateBatch;
use App\Models\Role;
use App\Models\Permission;
use App\Services\SearchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    protected SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Global search across multiple models.
     */
    public function globalSearch(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q', '');
            $models = $request->get('models', ['User', 'WorkUpdate', 'ClientProfile']);
            $limit = min((int)$request->get('limit', 5), 20);
            
            if (strlen($query) < 2) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Search query must be at least 2 characters long'
                ], 422);
            }

            $results = [];
            $user = Auth::user();

            foreach ($models as $model) {
                if (!$this->canSearchModel($user, $model)) {
                    continue;
                }

                $modelResults = $this->searchModel($model, $query, $limit, $user);
                if (!empty($modelResults)) {
                    $results[$model] = $modelResults;
                }
            }

            return response()->json([
                'status' => 'success',
                'query' => $query,
                'results' => $results,
                'total_results' => array_sum(array_map('count', $results))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search a specific model.
     */
    public function searchModel(Request $request, string $model): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$this->canSearchModel($user, $model)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to search this model'
                ], 403);
            }

            $modelClass = $this->getModelClass($model);
            if (!$modelClass) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid model specified'
                ], 422);
            }

            $results = $modelClass::advancedSearchWithPagination($request);
            
            // Apply user-specific filters
            $results = $this->applyUserFilters($results, $model, $user);

            return response()->json([
                'status' => 'success',
                'model' => $model,
                'data' => $results['data'],
                'pagination' => $results['pagination'],
                'links' => $results['links']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Model search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get search suggestions for a field.
     */
    public function suggestions(Request $request): JsonResponse
    {
        try {
            $model = $request->get('model');
            $field = $request->get('field');
            $query = $request->get('q', '');
            $limit = min((int)$request->get('limit', 10), 50);

            if (!$model || !$field) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Model and field are required'
                ], 422);
            }

            $user = Auth::user();
            if (!$this->canSearchModel($user, $model)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to search this model'
                ], 403);
            }

            $modelClass = $this->getModelClass($model);
            if (!$modelClass) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid model specified'
                ], 422);
            }

            $suggestions = $modelClass::getSearchSuggestions($field, $query, $limit);

            return response()->json([
                'status' => 'success',
                'model' => $model,
                'field' => $field,
                'query' => $query,
                'suggestions' => $suggestions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get suggestions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get search form configuration for a model.
     */
    public function formConfig(string $model): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$this->canSearchModel($user, $model)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to access this model'
                ], 403);
            }

            $modelClass = $this->getModelClass($model);
            if (!$modelClass) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid model specified'
                ], 422);
            }

            $config = $modelClass::getSearchFormData();

            return response()->json([
                'status' => 'success',
                'model' => $model,
                'config' => $config
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get form configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export search results.
     */
    public function export(Request $request, string $model): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasPermission('export-data')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to export data'
                ], 403);
            }

            if (!$this->canSearchModel($user, $model)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to export this model'
                ], 403);
            }

            $modelClass = $this->getModelClass($model);
            if (!$modelClass) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid model specified'
                ], 422);
            }

            $format = $request->get('format', 'json');
            $columns = $request->get('columns', []);
            
            $data = $modelClass::exportSearchResults($request, $columns);
            
            // Apply user-specific filters to exported data
            $data = $this->filterExportData($data, $model, $user);

            return response()->json([
                'status' => 'success',
                'model' => $model,
                'format' => $format,
                'data' => $data,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Export failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get quick statistics for search results.
     */
    public function stats(Request $request, string $model): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$this->canSearchModel($user, $model)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to view stats for this model'
                ], 403);
            }

            $modelClass = $this->getModelClass($model);
            if (!$modelClass) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid model specified'
                ], 422);
            }

            $query = $modelClass::query();
            $query = (new $modelClass())->scopeAdvancedSearch($query, $request);
            
            // Apply user-specific filters
            $query = $this->applyUserQueryFilters($query, $model, $user);
            
            $stats = $query->getQuickStats();

            return response()->json([
                'status' => 'success',
                'model' => $model,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user can search a specific model.
     */
    private function canSearchModel(User $user, string $model): bool
    {
        return match($model) {
            'User' => $user->hasAnyPermission(['manage-users', 'view-reports']),
            'WorkUpdate' => $user->hasAnyPermission(['submit-work-updates', 'approve-work-updates', 'view-own-updates', 'view-all-updates']),
            'ClientProfile' => $user->hasAnyPermission(['manage-clients', 'view-reports']),
            'WorkUpdateBatch' => $user->hasAnyPermission(['approve-work-updates', 'view-all-updates']),
            'Role' => $user->hasPermission('manage-roles'),
            'Permission' => $user->hasPermission('manage-roles'),
            default => false
        };
    }

    /**
     * Get model class from string.
     */
    private function getModelClass(string $model): ?string
    {
        return match($model) {
            'User' => User::class,
            'WorkUpdate' => WorkUpdate::class,
            'ClientProfile' => ClientProfile::class,
            'WorkUpdateBatch' => WorkUpdateBatch::class,
            'Role' => Role::class,
            'Permission' => Permission::class,
            default => null
        };
    }

    /**
     * Apply user-specific filters to search results.
     */
    private function applyUserFilters(array $results, string $model, User $user): array
    {
        if ($model === 'WorkUpdate' && ($user->isAgent() || $user->isClient())) {
            $filteredData = array_filter($results['data'], function($item) use ($user) {
                if ($user->isAgent()) {
                    return $item['agent_id'] === $user->id;
                }
                if ($user->isClient()) {
                    return $item['client_id'] === $user->id;
                }
                return true;
            });
            
            $results['data'] = array_values($filteredData);
            $results['pagination']['total'] = count($filteredData);
        }

        return $results;
    }

    /**
     * Apply user-specific filters to query.
     */
    private function applyUserQueryFilters($query, string $model, User $user)
    {
        if ($model === 'WorkUpdate') {
            if ($user->isAgent()) {
                $query->where('agent_id', $user->id);
            } elseif ($user->isClient()) {
                $query->where('client_id', $user->id);
            }
        }

        return $query;
    }

    /**
     * Filter export data based on user permissions.
     */
    private function filterExportData(array $data, string $model, User $user): array
    {
        if ($model === 'WorkUpdate' && ($user->isAgent() || $user->isClient())) {
            return array_filter($data, function($item) use ($user) {
                if ($user->isAgent()) {
                    return ($item['agent_id'] ?? null) === $user->id;
                }
                if ($user->isClient()) {
                    return ($item['client_id'] ?? null) === $user->id;
                }
                return true;
            });
        }

        return $data;
    }

    /**
     * Search within a specific model with basic query.
     */
    private function searchModel(string $model, string $query, int $limit, User $user): array
    {
        $modelClass = $this->getModelClass($model);
        if (!$modelClass) {
            return [];
        }

        $queryBuilder = $modelClass::globalSearch($query)->limit($limit);
        
        // Apply user-specific filters
        $queryBuilder = $this->applyUserQueryFilters($queryBuilder, $model, $user);
        
        $results = $queryBuilder->get();
        
        return $results->map(function($item) use ($model) {
            return [
                'id' => $item->id,
                'title' => $this->getItemTitle($item, $model),
                'subtitle' => $this->getItemSubtitle($item, $model),
                'url' => $this->getItemUrl($item, $model),
                'model' => $model
            ];
        })->toArray();
    }

    /**
     * Get display title for search result item.
     */
    private function getItemTitle($item, string $model): string
    {
        return match($model) {
            'User' => $item->name,
            'WorkUpdate' => $item->job_title . ' at ' . $item->company,
            'ClientProfile' => $item->user->name ?? 'Client Profile',
            'WorkUpdateBatch' => 'Batch for ' . ($item->agent->name ?? 'Unknown Agent'),
            'Role' => $item->display_name,
            'Permission' => $item->display_name,
            default => 'Item #' . $item->id
        };
    }

    /**
     * Get display subtitle for search result item.
     */
    private function getItemSubtitle($item, string $model): string
    {
        return match($model) {
            'User' => $item->email . ' (' . ($item->role->display_name ?? 'No Role') . ')',
            'WorkUpdate' => 'Applied on ' . $item->applied_date->format('M j, Y') . ' - Status: ' . $item->getStatusLabel(),
            'ClientProfile' => $item->user->email ?? '',
            'WorkUpdateBatch' => $item->submission_date->format('M j, Y') . ' - ' . $item->getStatusLabel(),
            'Role' => $item->description ?? '',
            'Permission' => $item->description ?? '',
            default => ''
        };
    }

    /**
     * Get URL for search result item.
     */
    private function getItemUrl($item, string $model): string
    {
        return match($model) {
            'User' => '/users/' . $item->id,
            'WorkUpdate' => '/work-updates/' . $item->id,
            'ClientProfile' => '/clients/' . $item->id,
            'WorkUpdateBatch' => '/batches/' . $item->id,
            'Role' => '/roles/' . $item->id,
            'Permission' => '/permissions/' . $item->id,
            default => '#'
        };
    }
}
