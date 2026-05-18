<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WorkUpdateController;
use App\Http\Controllers\Api\WorkUpdateBatchController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ActivityController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API routes with authentication
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Work Updates API
    Route::apiResource('work-updates', WorkUpdateController::class);
    
    // Work Update specific actions
    Route::post('work-updates/{id}/submit', [WorkUpdateController::class, 'submit']);
    Route::post('work-updates/{id}/approve', [WorkUpdateController::class, 'approve']);
    Route::post('work-updates/{id}/reject', [WorkUpdateController::class, 'reject']);
    Route::post('work-updates/{id}/request-revision', [WorkUpdateController::class, 'requestRevision']);
    Route::post('work-updates/bulk-action', [WorkUpdateController::class, 'bulkAction']);
    
    // Work Update Batches API
    Route::apiResource('work-update-batches', WorkUpdateBatchController::class);
    Route::post('work-update-batches/{id}/submit', [WorkUpdateBatchController::class, 'submit']);
    Route::post('work-update-batches/{id}/approve', [WorkUpdateBatchController::class, 'approve']);
    Route::post('work-update-batches/{id}/reject', [WorkUpdateBatchController::class, 'reject']);
    
    // Search API
    Route::prefix('search')->group(function () {
        Route::get('global', [SearchController::class, 'globalSearch']);
        Route::get('{model}', [SearchController::class, 'searchModel']);
        Route::get('suggestions', [SearchController::class, 'suggestions']);
        Route::get('{model}/form-config', [SearchController::class, 'formConfig']);
        Route::get('{model}/stats', [SearchController::class, 'stats']);
        Route::post('{model}/export', [SearchController::class, 'export']);
    });
    
    // Notifications API
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('unread-count', [NotificationController::class, 'unreadCount']);
        Route::get('recent', [NotificationController::class, 'recent']);
        Route::post('{notification}/mark-read', [NotificationController::class, 'markAsRead']);
        Route::post('mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('{notification}', [NotificationController::class, 'destroy']);
        
        // Admin only routes
        Route::middleware(['permission:manage-notifications'])->group(function () {
            Route::get('statistics', [NotificationController::class, 'statistics']);
            Route::post('send-system', [NotificationController::class, 'sendSystemNotification']);
            Route::post('cleanup', [NotificationController::class, 'cleanup']);
        });
        
        // Development only
        Route::post('test', [NotificationController::class, 'test']);
    });
    
    // Activities API
    Route::prefix('activities')->group(function () {
        Route::get('recent', [ActivityController::class, 'recent']);
        Route::get('user', [ActivityController::class, 'userActivities']);
        
        // Admin only routes
        Route::middleware(['permission:view-activity-logs'])->group(function () {
            Route::get('/', [ActivityController::class, 'index']);
            Route::get('model/{type}/{id}', [ActivityController::class, 'modelActivities']);
            Route::get('statistics', [ActivityController::class, 'statistics']);
            Route::post('cleanup', [ActivityController::class, 'cleanup']);
            Route::post('export', [ActivityController::class, 'export']);
        });
    });
    
    // Dashboard API endpoints
    Route::prefix('dashboard')->group(function () {
        Route::get('stats', function (Request $request) {
            $user = $request->user();
            
            if ($user->isAgent()) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'performance' => $user->getPerformanceMetrics(),
                        'today_updates' => $user->getTodaysUpdatesCount(),
                        'monthly_updates' => $user->getMonthlyUpdatesCount(),
                        'assigned_clients' => $user->assignedClientProfiles()->count(),
                    ]
                ]);
            } elseif ($user->isClient()) {
                $totalUpdates = $user->clientWorkUpdates()->count();
                $approvedUpdates = $user->clientWorkUpdates()->where('status', 'approved')->count();
                $pendingUpdates = $user->clientWorkUpdates()->whereIn('status', ['submitted', 'under_review'])->count();
                
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'total_updates' => $totalUpdates,
                        'approved_updates' => $approvedUpdates,
                        'pending_updates' => $pendingUpdates,
                        'assigned_agents' => $user->agents()->count(),
                    ]
                ]);
            } elseif ($user->isAdmin() || $user->isAgentManager()) {
                $totalUsers = \App\Models\User::count();
                $totalAgents = \App\Models\User::whereHas('roles', function($q) {
                    $q->where('name', 'agent');
                })->count();
                $totalClients = \App\Models\User::whereHas('roles', function($q) {
                    $q->where('name', 'client');
                })->count();
                $pendingUpdates = \App\Models\WorkUpdate::whereIn('status', ['submitted', 'under_review'])->count();
                
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'total_users' => $totalUsers,
                        'total_agents' => $totalAgents,
                        'total_clients' => $totalClients,
                        'pending_updates' => $pendingUpdates,
                        'monthly_updates' => \App\Models\WorkUpdate::whereMonth('created_at', now()->month)->count(),
                    ]
                ]);
            }
            
            return response()->json([
                'status' => 'success',
                'data' => []
            ]);
        });
        
        Route::get('recent-activities', function (Request $request) {
            // Use the new Activity Log system
            $activities = \App\Models\ActivityLog::getRecentActivities(20);
            
            return response()->json([
                'status' => 'success',
                'data' => $activities->map(function($activity) {
                    return [
                        'id' => $activity->id,
                        'description' => $activity->formatted_description,
                        'action' => $activity->action,
                        'action_icon' => $activity->action_icon,
                        'action_color' => $activity->action_color,
                        'user' => $activity->user ? [
                            'id' => $activity->user->id,
                            'name' => $activity->user->name,
                            'email' => $activity->user->email
                        ] : null,
                        'created_at' => $activity->created_at,
                        'created_at_human' => $activity->created_at->diffForHumans()
                    ];
                })
            ]);
        });
    });
    
    // System settings API (for admins)
    Route::middleware(['permission:manage-settings'])->prefix('settings')->group(function () {
        Route::get('/', function () {
            return response()->json([
                'status' => 'success',
                'data' => \App\Models\SystemSetting::getAllGrouped()
            ]);
        });
        
        Route::get('public', function () {
            return response()->json([
                'status' => 'success',
                'data' => \App\Models\SystemSetting::getPublicSettings()
            ]);
        });
        
        Route::put('{category}/{key}', function (Request $request, string $category, string $key) {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'value' => 'required',
                'type' => 'sometimes|in:string,integer,boolean,json,array'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            try {
                $setting = \App\Models\SystemSetting::set(
                    "{$category}.{$key}",
                    $request->get('value'),
                    $request->get('type')
                );
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Setting updated successfully',
                    'data' => $setting
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to update setting',
                    'error' => $e->getMessage()
                ], 500);
            }
        });
    });
});

// Public API endpoints (no authentication required)
Route::prefix('public')->group(function () {
    Route::get('settings', function () {
        return response()->json([
            'status' => 'success',
            'data' => \App\Models\SystemSetting::getPublicSettings()
        ]);
    });
    
    Route::get('app-info', function () {
        return response()->json([
            'status' => 'success',
            'data' => [
                'name' => \App\Models\SystemSetting::get('general.app_name', 'W Automation'),
                'version' => '1.0.0',
                'features' => [
                    'work_updates' => true,
                    'batch_processing' => true,
                    'advanced_search' => true,
                    'role_permissions' => true,
                    'real_time_notifications' => true,
                ]
            ]
        ]);
    });
});