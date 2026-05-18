<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkUpdate;
use App\Models\WorkUpdateBatch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WorkUpdateController extends Controller
{
    /**
     * Display a listing of work updates with search and filters.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = WorkUpdate::with(['agent:id,name,email', 'client:id,name,email', 'approver:id,name,email', 'batch:id,submission_date,status']);
            
            // Apply user-specific filters based on role
            $user = Auth::user();
            if ($user->isAgent()) {
                $query->where('agent_id', $user->id);
            } elseif ($user->isClient()) {
                $query->where('client_id', $user->id);
            }
            // Admins and managers can see all updates
            
            // Use the search functionality from the Searchable trait
            $results = WorkUpdate::advancedSearchWithPagination($request, 20);
            
            return response()->json([
                'status' => 'success',
                'data' => $results['data'],
                'pagination' => $results['pagination'],
                'links' => $results['links'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch work updates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created work update.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'client_id' => 'required|exists:users,id',
                'job_title' => 'required|string|max:255',
                'company' => 'required|string|max:255',
                'applied_date' => 'required|date|before_or_equal:today',
                'job_link' => 'nullable|url|max:500',
                'applied_method' => 'required|in:web,linkedin,referral,direct,email,other',
                'note' => 'nullable|string|max:1000',
                'applied_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120', // 5MB
                'batch_id' => 'nullable|exists:work_update_batches,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            
            // Ensure agent can only create updates for assigned clients
            if ($user->isAgent()) {
                $isAssigned = $user->assignedClientProfiles()
                                  ->whereHas('user', function($q) use ($request) {
                                      $q->where('id', $request->client_id);
                                  })->exists();
                                  
                if (!$isAssigned) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'You are not assigned to this client'
                    ], 403);
                }
            }

            DB::beginTransaction();

            $data = $validator->validated();
            $data['agent_id'] = $user->id;
            $data['status'] = WorkUpdate::STATUS_DRAFT;

            // Handle file upload
            if ($request->hasFile('applied_proof')) {
                $file = $request->file('applied_proof');
                $path = $file->store('work-update-proofs', 'public');
                $data['applied_proof'] = $path;
            }

            // Create or get today's batch for the agent
            if (!isset($data['batch_id'])) {
                $batch = WorkUpdateBatch::getOrCreateTodaysBatch($user->id);
                $data['batch_id'] = $batch->id;
            }

            $workUpdate = WorkUpdate::create($data);
            
            // Update batch statistics
            if ($workUpdate->batch) {
                $workUpdate->batch->updateStatistics();
            }

            DB::commit();

            $workUpdate->load(['agent:id,name,email', 'client:id,name,email', 'batch:id,submission_date,status']);

            return response()->json([
                'status' => 'success',
                'message' => 'Work update created successfully',
                'data' => $workUpdate
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create work update',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified work update.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $workUpdate = WorkUpdate::with([
                'agent:id,name,email', 
                'client:id,name,email', 
                'approver:id,name,email', 
                'batch:id,submission_date,status'
            ])->findOrFail($id);

            $user = Auth::user();
            
            // Check access permissions
            if ($user->isAgent() && $workUpdate->agent_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You can only view your own work updates'
                ], 403);
            }
            
            if ($user->isClient() && $workUpdate->client_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You can only view your own work updates'
                ], 403);
            }

            return response()->json([
                'status' => 'success',
                'data' => $workUpdate
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Work update not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified work update.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $workUpdate = WorkUpdate::findOrFail($id);
            $user = Auth::user();

            // Check if user can edit this work update
            if ($user->isAgent() && $workUpdate->agent_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You can only edit your own work updates'
                ], 403);
            }

            if (!$workUpdate->canBeEdited()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This work update cannot be edited in its current status'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'job_title' => 'sometimes|required|string|max:255',
                'company' => 'sometimes|required|string|max:255',
                'applied_date' => 'sometimes|required|date|before_or_equal:today',
                'job_link' => 'sometimes|nullable|url|max:500',
                'applied_method' => 'sometimes|required|in:web,linkedin,referral,direct,email,other',
                'note' => 'sometimes|nullable|string|max:1000',
                'applied_proof' => 'sometimes|nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $data = $validator->validated();

            // Handle file upload
            if ($request->hasFile('applied_proof')) {
                // Delete old file if exists
                if ($workUpdate->applied_proof) {
                    Storage::disk('public')->delete($workUpdate->applied_proof);
                }
                
                $file = $request->file('applied_proof');
                $path = $file->store('work-update-proofs', 'public');
                $data['applied_proof'] = $path;
            }

            $workUpdate->update($data);
            
            // Update batch statistics
            if ($workUpdate->batch) {
                $workUpdate->batch->updateStatistics();
            }

            DB::commit();

            $workUpdate->load(['agent:id,name,email', 'client:id,name,email', 'batch:id,submission_date,status']);

            return response()->json([
                'status' => 'success',
                'message' => 'Work update updated successfully',
                'data' => $workUpdate
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update work update',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified work update.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $workUpdate = WorkUpdate::findOrFail($id);
            $user = Auth::user();

            // Check permissions
            if ($user->isAgent() && $workUpdate->agent_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You can only delete your own work updates'
                ], 403);
            }

            if (!$workUpdate->canBeEdited()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This work update cannot be deleted in its current status'
                ], 422);
            }

            DB::beginTransaction();

            // Delete associated file
            if ($workUpdate->applied_proof) {
                Storage::disk('public')->delete($workUpdate->applied_proof);
            }

            $batch = $workUpdate->batch;
            $workUpdate->delete();
            
            // Update batch statistics
            if ($batch) {
                $batch->updateStatistics();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Work update deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete work update',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit work update for approval.
     */
    public function submit(Request $request, string $id): JsonResponse
    {
        try {
            $workUpdate = WorkUpdate::findOrFail($id);
            $user = Auth::user();

            if ($user->isAgent() && $workUpdate->agent_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You can only submit your own work updates'
                ], 403);
            }

            if (!$workUpdate->canBeSubmitted()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This work update cannot be submitted in its current status'
                ], 422);
            }

            DB::beginTransaction();

            $workUpdate->submit();
            
            // Update batch statistics
            if ($workUpdate->batch) {
                $workUpdate->batch->updateStatistics();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Work update submitted for approval',
                'data' => $workUpdate->fresh(['agent:id,name,email', 'client:id,name,email', 'batch:id,submission_date,status'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit work update',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve work update.
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        try {
            $workUpdate = WorkUpdate::findOrFail($id);
            $user = Auth::user();

            if (!$user->hasPermission('approve-work-updates')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to approve work updates'
                ], 403);
            }

            if (!$workUpdate->canBeApproved()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This work update cannot be approved in its current status'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'remarks' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $workUpdate->approve($user->id, $request->get('remarks'));
            
            // Update batch statistics
            if ($workUpdate->batch) {
                $workUpdate->batch->updateStatistics();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Work update approved successfully',
                'data' => $workUpdate->fresh(['agent:id,name,email', 'client:id,name,email', 'approver:id,name,email', 'batch:id,submission_date,status'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to approve work update',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject work update.
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        try {
            $workUpdate = WorkUpdate::findOrFail($id);
            $user = Auth::user();

            if (!$user->hasPermission('approve-work-updates')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to reject work updates'
                ], 403);
            }

            if (!$workUpdate->canBeRejected()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This work update cannot be rejected in its current status'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'reason' => 'required|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $workUpdate->reject($user->id, $request->get('reason'));
            
            // Update batch statistics
            if ($workUpdate->batch) {
                $workUpdate->batch->updateStatistics();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Work update rejected',
                'data' => $workUpdate->fresh(['agent:id,name,email', 'client:id,name,email', 'approver:id,name,email', 'batch:id,submission_date,status'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reject work update',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Request revision for work update.
     */
    public function requestRevision(Request $request, string $id): JsonResponse
    {
        try {
            $workUpdate = WorkUpdate::findOrFail($id);
            $user = Auth::user();

            if (!$user->hasPermission('approve-work-updates')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission to request revisions'
                ], 403);
            }

            if (!$workUpdate->canBeRejected()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot request revision for this work update in its current status'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'reason' => 'required|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $workUpdate->requestRevision($user->id, $request->get('reason'));
            
            // Update batch statistics
            if ($workUpdate->batch) {
                $workUpdate->batch->updateStatistics();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Revision requested for work update',
                'data' => $workUpdate->fresh(['agent:id,name,email', 'client:id,name,email', 'approver:id,name,email', 'batch:id,submission_date,status'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to request revision',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk operations on work updates.
     */
    public function bulkAction(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user->hasPermission('bulk-operations')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have permission for bulk operations'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'action' => 'required|in:approve,reject,delete,submit',
                'work_update_ids' => 'required|array|min:1',
                'work_update_ids.*' => 'exists:work_updates,id',
                'reason' => 'required_if:action,reject|string|max:1000',
                'remarks' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $workUpdateIds = $request->get('work_update_ids');
            $action = $request->get('action');
            $reason = $request->get('reason');
            $remarks = $request->get('remarks');

            $workUpdates = WorkUpdate::whereIn('id', $workUpdateIds)->get();
            $results = [];
            $errors = [];

            DB::beginTransaction();

            foreach ($workUpdates as $workUpdate) {
                try {
                    switch ($action) {
                        case 'approve':
                            if ($workUpdate->canBeApproved()) {
                                $workUpdate->approve($user->id, $remarks);
                                $results[] = $workUpdate->id;
                            } else {
                                $errors[] = "Work update {$workUpdate->id} cannot be approved";
                            }
                            break;
                            
                        case 'reject':
                            if ($workUpdate->canBeRejected()) {
                                $workUpdate->reject($user->id, $reason);
                                $results[] = $workUpdate->id;
                            } else {
                                $errors[] = "Work update {$workUpdate->id} cannot be rejected";
                            }
                            break;
                            
                        case 'submit':
                            if ($workUpdate->canBeSubmitted()) {
                                $workUpdate->submit();
                                $results[] = $workUpdate->id;
                            } else {
                                $errors[] = "Work update {$workUpdate->id} cannot be submitted";
                            }
                            break;
                            
                        case 'delete':
                            if ($workUpdate->canBeEdited()) {
                                if ($workUpdate->applied_proof) {
                                    Storage::disk('public')->delete($workUpdate->applied_proof);
                                }
                                $workUpdate->delete();
                                $results[] = $workUpdate->id;
                            } else {
                                $errors[] = "Work update {$workUpdate->id} cannot be deleted";
                            }
                            break;
                    }
                    
                    // Update batch statistics
                    if ($workUpdate->batch && $action !== 'delete') {
                        $workUpdate->batch->updateStatistics();
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error processing work update {$workUpdate->id}: {$e->getMessage()}";
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Bulk {$action} completed",
                'processed' => $results,
                'errors' => $errors,
                'total_processed' => count($results),
                'total_errors' => count($errors)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Bulk operation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
