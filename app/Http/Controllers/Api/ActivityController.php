<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    /**
     * Get recent activities for dashboard
     */
    public function recent(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 20), 100);
        
        $activities = ActivityLog::with(['user', 'subject'])
                                ->latest()
                                ->limit($limit)
                                ->get()
                                ->map(function ($activity) {
                                    return [
                                        'id' => $activity->id,
                                        'description' => $activity->formatted_description,
                                        'action' => $activity->action,
                                        'action_icon' => $activity->action_icon,
                                        'action_color' => $activity->action_color,
                                        'user' => $activity->user ? [
                                            'id' => $activity->user->id,
                                            'name' => $activity->user->name,
                                            'email' => $activity->user->email,
                                            'avatar' => $activity->user->avatar_url ?? null
                                        ] : null,
                                        'subject_type' => $activity->subject_type,
                                        'subject_id' => $activity->subject_id,
                                        'context' => $activity->context,
                                        'created_at' => $activity->created_at,
                                        'created_at_human' => $activity->created_at->diffForHumans()
                                    ];
                                });

        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }

    /**
     * Get user's activities
     */
    public function userActivities(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = min($request->get('per_page', 20), 100);
        
        $query = ActivityLog::with(['subject'])
                           ->where('user_id', $user->id)
                           ->latest();

        // Filter by action
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Filter by subject type
        if ($request->has('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $activities = $query->paginate($perPage);

        $data = $activities->getCollection()->map(function ($activity) {
            return [
                'id' => $activity->id,
                'description' => $activity->formatted_description,
                'action' => $activity->action,
                'action_icon' => $activity->action_icon,
                'action_color' => $activity->action_color,
                'subject_type' => $activity->subject_type,
                'subject_id' => $activity->subject_id,
                'properties' => $activity->properties,
                'context' => $activity->context,
                'ip_address' => $activity->ip_address,
                'created_at' => $activity->created_at,
                'created_at_human' => $activity->created_at->diffForHumans()
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage(),
                'per_page' => $activities->perPage(),
                'total' => $activities->total()
            ]
        ]);
    }

    /**
     * Get activities for specific model (admin only)
     */
    public function modelActivities(Request $request, string $type, int $id): JsonResponse
    {
        $this->authorize('viewAny', ActivityLog::class);

        $limit = min($request->get('limit', 20), 50);
        
        $activities = ActivityLog::with(['user'])
                                ->where('subject_type', $type)
                                ->where('subject_id', $id)
                                ->latest()
                                ->limit($limit)
                                ->get()
                                ->map(function ($activity) {
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
                                        'properties' => $activity->properties,
                                        'context' => $activity->context,
                                        'created_at' => $activity->created_at,
                                        'created_at_human' => $activity->created_at->diffForHumans()
                                    ];
                                });

        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }

    /**
     * Get all activities with filters (admin only)
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ActivityLog::class);

        $perPage = min($request->get('per_page', 20), 100);
        
        $query = ActivityLog::with(['user', 'subject'])
                           ->latest();

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Filter by subject type
        if ($request->has('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        // Filter by context
        if ($request->has('context')) {
            $query->where('context', $request->context);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $activities = $query->paginate($perPage);

        $data = $activities->getCollection()->map(function ($activity) {
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
                'subject_type' => $activity->subject_type,
                'subject_id' => $activity->subject_id,
                'properties' => $activity->properties,
                'context' => $activity->context,
                'ip_address' => $activity->ip_address,
                'created_at' => $activity->created_at,
                'created_at_human' => $activity->created_at->diffForHumans()
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage(),
                'per_page' => $activities->perPage(),
                'total' => $activities->total()
            ]
        ]);
    }

    /**
     * Get activity statistics (admin only)
     */
    public function statistics(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ActivityLog::class);

        $filters = $request->only(['user_id', 'date_from', 'date_to']);
        $statistics = ActivityLog::getStatistics($filters);

        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }

    /**
     * Clean up old activities (admin only)
     */
    public function cleanup(Request $request): JsonResponse
    {
        $this->authorize('delete', ActivityLog::class);

        $request->validate([
            'days' => 'integer|min:1|max:365'
        ]);

        $days = $request->get('days', 90);
        $deletedCount = ActivityLog::cleanupOld($days);

        return response()->json([
            'success' => true,
            'message' => "Cleaned up {$deletedCount} old activity logs",
            'data' => [
                'deleted_count' => $deletedCount,
                'days' => $days
            ]
        ]);
    }

    /**
     * Export activities (admin only)
     */
    public function export(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ActivityLog::class);

        $request->validate([
            'format' => 'in:csv,json',
            'date_from' => 'date',
            'date_to' => 'date|after_or_equal:date_from'
        ]);

        $query = ActivityLog::with(['user'])
                           ->latest();

        // Apply filters
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        if ($request->has('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $activities = $query->limit(10000)->get(); // Limit to prevent memory issues

        // Log the export activity
        ActivityLog::logActivity(
            ActivityLog::ACTION_EXPORT,
            null,
            Auth::user(),
            [
                'export_type' => 'activity_logs',
                'format' => $request->get('format', 'json'),
                'filters' => $request->only(['user_id', 'action', 'subject_type', 'date_from', 'date_to']),
                'record_count' => $activities->count()
            ],
            'Exported activity logs'
        );

        return response()->json([
            'success' => true,
            'message' => "Export prepared with {$activities->count()} records",
            'data' => [
                'record_count' => $activities->count(),
                'download_url' => route('api.activities.download', [
                    'token' => encrypt([
                        'user_id' => Auth::id(),
                        'timestamp' => now()->timestamp,
                        'filters' => $request->only(['user_id', 'action', 'subject_type', 'date_from', 'date_to']),
                        'format' => $request->get('format', 'json')
                    ])
                ])
            ]
        ]);
    }
}