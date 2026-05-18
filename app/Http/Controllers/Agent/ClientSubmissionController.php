<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\ClientSubmission;
use App\Models\OtpSubmission;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClientSubmissionController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of client submissions for the agent
     */
    public function index()
    {
        $user = Auth::user();

        // Get regular client submissions
        $clientSubmissions = ClientSubmission::with(['client'])
            ->where('agent_id', $user->id)
            ->latest()
            ->get();

        // Get OTP submissions
        $otpSubmissions = OtpSubmission::with(['client'])
            ->where('agent_id', $user->id)
            ->latest()
            ->get();

        // Get statistics
        $stats = [
            'total' => $clientSubmissions->count() + $otpSubmissions->count(),
            'pending' => $clientSubmissions->where('status', 'pending')->count() + $otpSubmissions->where('status', 'pending')->count(),
            'processed' => $clientSubmissions->where('status', 'processed')->count() + $otpSubmissions->where('status', 'processed')->count(),
            'approved' => $clientSubmissions->where('status', 'approved')->count() + $otpSubmissions->where('status', 'approved')->count(),
            'rejected' => $clientSubmissions->where('status', 'rejected')->count() + $otpSubmissions->where('status', 'rejected')->count(),
        ];

        return view('agent.submissions.index', compact('clientSubmissions', 'otpSubmissions', 'stats'));
    }

    /**
     * Display the specified submission
     */
    public function show(Request $request, $submissionRouteKey)
    {
        $user = Auth::user();
        $submission = null;
        $type = 'client_submission';
        $isOtpSubmission = $request->has('otp');

        if ($isOtpSubmission) {
            $submission = $this->resolveSubmissionModel($submissionRouteKey, true)?->load('client');
            $type = 'otp_submission';
        } else {
            $submission = $this->resolveSubmissionModel($submissionRouteKey, false)?->load('client');
        }

        if (!$submission) {
            abort(404, 'Submission not found.');
        }

        $canonicalRouteKey = (string) $submission->getRouteKey();
        $incomingRouteKey = trim((string) $submissionRouteKey);

        if ($incomingRouteKey !== $canonicalRouteKey) {
            $query = $request->query();
            $canonicalUrl = route('agent.submissions.show', $submission);

            if (!empty($query)) {
                $canonicalUrl .= '?' . http_build_query($query);
            }

            return redirect()->to($canonicalUrl, 301);
        }

        // Ensure the agent can only view submissions assigned to them (or admins/super admins)
        $isOwner = $submission->agent_id === $user->id;
        $isAdmin = $user->isAdmin() || $user->isSuperAdmin();
        $isAssignedClient = \App\Models\AgentClientAssignment::where('agent_id', $user->id)
            ->where('client_id', $submission->client_id)
            ->where('is_active', true)
            ->exists();

        if (!$isOwner && !$isAssignedClient && !$isAdmin) {
            abort(403, 'Unauthorized access to submission.');
        }

        return view('agent.submissions.show', compact('submission', 'type'));
    }

    /**
     * Update the status of a submission
     */
    public function updateStatus(Request $request, $submissionRouteKey)
    {
        $user = Auth::user();
        $submission = null;
        $isOtpSubmission = false;

        // Check if it's an OTP submission
        if ($request->has('otp') || $request->input('type') === 'otp') {
            $submission = $this->resolveSubmissionModel($submissionRouteKey, true);
            $isOtpSubmission = true;
        } else {
            $submission = $this->resolveSubmissionModel($submissionRouteKey, false);
        }

        if (!$submission) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Submission not found.'
                ], 404);
            }
            return redirect()->back()->with('error', 'Submission not found.');
        }

        // Ensure the agent can update: own submission OR assigned to the client, or admin/super admin
        $isOwner = $submission->agent_id === $user->id;
        $isAdmin = $user->isAdmin() || $user->isSuperAdmin();
        $isAssignedClient = \App\Models\AgentClientAssignment::where('agent_id', $user->id)
            ->where('client_id', $submission->client_id)
            ->where('is_active', true)
            ->exists();

        if (!$isOwner && !$isAssignedClient && !$isAdmin) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to submission.'
                ], 403);
            }
            return redirect()->back()->with('error', 'Unauthorized access to submission.');
        }

        $request->validate([
            'status' => 'required|in:pending,processed,rejected,approved',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            if ($isOtpSubmission) {
                // Update OTP submission
                $submission->update([
                    'status' => $request->status,
                    'notes' => $request->notes,
                    'reviewed_at' => $request->status !== 'pending' ? now() : null,
                ]);
            } else {
                // Update regular client submission
                $submission->update([
                    'status' => $request->status,
                    'notes' => $request->notes,
                    'processed_at' => $request->status !== 'pending' ? now() : null,
                ]);
            }

            // Send notification to client about status update
            $this->notificationService->notify(
                $submission->client,
                'Submission Status Updated',
                "Your submission status has been updated to: {$request->status}",
                \App\Models\Notification::TYPE_INFO,
                ['submission_id' => $submission->id, 'status' => $request->status],
                \App\Models\Notification::PRIORITY_NORMAL,
                null,
                $isOtpSubmission
                    ? route('client.otp-requests.index')
                    : route('client.submissions.show', $submission)
            );

            Log::info('Submission status updated', [
                'submission_id' => $submission->id,
                'agent_id' => $user->id,
                'client_id' => $submission->client_id,
                'new_status' => $request->status,
                'is_otp_submission' => $isOtpSubmission,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Submission status updated successfully.'
                ]);
            }
            return redirect()->back()->with('success', 'Submission status updated successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to update submission status', [
                'submission_id' => $submission->id,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update submission status. Please try again.'
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to update submission status. Please try again.');
        }
    }

    /**
     * Get submissions for a specific client
     */
    public function getClientSubmissions(User $client)
    {
        $user = Auth::user();

        // Verify the agent is assigned to this client
        $submissions = ClientSubmission::with(['client'])
            ->where('agent_id', $user->id)
            ->where('client_id', $client->id)
            ->latest()
            ->get();

        return response()->json($submissions);
    }

    private function resolveSubmissionModel(string|int $routeKey, bool $isOtp): ?Model
    {
        $model = $isOtp ? new OtpSubmission() : new ClientSubmission();

        return $model->resolveRouteBinding($routeKey);
    }
}
