<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\WorkUpdate;
use App\Models\AgentClientAssignment;
use App\Models\User;
use App\Models\Meeting;
use App\Models\Attendance;
use App\Services\NotificationService;
use App\Services\NoticeService;
use App\Services\MailchimpService;
use App\Services\GoogleMeetService;
use App\Support\WorkUpdateFilters;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    protected $notificationService;
    protected $noticeService;
    protected $mailchimpService;
    protected $googleMeetService;

    public function __construct(NotificationService $notificationService, NoticeService $noticeService, MailchimpService $mailchimpService, GoogleMeetService $googleMeetService)
    {
        $this->notificationService = $notificationService;
        $this->noticeService = $noticeService;
        $this->mailchimpService = $mailchimpService;
        $this->googleMeetService = $googleMeetService;
    }

    public function index()
    {
        $user = Auth::user();

        $assignedClients = $user->assignedClients();
        $activeClients = $user->active_clients;

        // Get today's submissions status for each client
        $clientsStatus = [];
        foreach ($activeClients as $client) {
            $todaySubmission = WorkUpdate::getTodaysSubmission($user->id, $client->id);

            // Check for existing draft for this client today
            $existingDraft = WorkUpdate::where('agent_id', $user->id)
                                      ->where('client_id', $client->id)
                                      ->where('status', WorkUpdate::STATUS_DRAFT)
                                      ->whereDate('created_at', today())
                                      ->first();

            // Get assignment details
            $assignment = AgentClientAssignment::where('agent_id', $user->id)
                                             ->where('client_id', $client->id)
                                             ->first();

            $daysRemaining = $assignment?->getDaysRemaining();
            if (!$assignment || !$assignment->is_active) {
                $serviceStatus = 'inactive';
                $serviceLabel = 'Inactive';
            } elseif ($assignment->service_end_date && $daysRemaining !== null && $daysRemaining < 0) {
                $serviceStatus = 'expired';
                $serviceLabel = 'Expired';
            } else {
                $serviceStatus = 'active';
                $serviceLabel = 'Active';
            }

            $clientsStatus[] = [
                'client' => $client,
                'has_submitted_today' => $todaySubmission !== null,
                'submission' => $todaySubmission,
                'has_draft' => $existingDraft !== null,
                'draft' => $existingDraft,
                'service_end_date' => $assignment?->service_end_date,
                'days_remaining' => $daysRemaining,
                'service_status' => $serviceStatus,
                'service_status_label' => $serviceLabel,
                'assignment' => $assignment
            ];
        }

        // Get recent work updates
        $recentUpdates = WorkUpdate::where('agent_id', $user->id)
                                 ->with('client')
                                 ->latest()
                                 ->limit(10)
                                 ->get();

        // Get statistics (excluding drafts)
        $stats = [
            'total_clients' => $assignedClients->count(),
            'active_clients' => $activeClients->count(),
            'submitted_today' => WorkUpdate::where('agent_id', $user->id)
                                          ->whereDate('created_at', today())
                                          ->where('status', '!=', WorkUpdate::STATUS_DRAFT)
                                          ->count(),
            'pending_submissions' => max($activeClients->count() - WorkUpdate::where('agent_id', $user->id)
                                                                           ->whereDate('created_at', today())
                                                                           ->where('status', '!=', WorkUpdate::STATUS_DRAFT)
                                                                           ->count(), 0),
            'this_month' => WorkUpdate::where('agent_id', $user->id)
                                    ->whereMonth('created_at', now()->month)
                                    ->where('status', '!=', WorkUpdate::STATUS_DRAFT)
                                    ->count(),
        ];

        // Get today's meeting
        $todayMeeting = $this->googleMeetService->getTodaysMeeting();
        $isCheckedIn = $this->isAgentCheckedIn($user->id);
        $dashboardNotices = $this->noticeService->getDashboardNotices($user);

        return view('agent.dashboard', compact('clientsStatus', 'recentUpdates', 'stats', 'todayMeeting', 'isCheckedIn', 'dashboardNotices'));
    }

    public function createWorkUpdate(Request $request)
    {
        $user = Auth::user();
        $selectedClientId = null;
        $selectedClientRouteKey = trim((string) $request->query('client_id', ''));

        // Get assigned clients for this agent
        $assignedClients = $user->active_clients;

        if ($assignedClients->isEmpty()) {
            return redirect()->route('agent.dashboard')
                           ->with('warning', 'No clients assigned to you. Please contact your manager.');
        }

        // If client_id is specified, validate the assignment and check daily limit
        if ($selectedClientRouteKey !== '') {
            $resolvedClient = (new User())->resolveRouteBinding($selectedClientRouteKey);
            $client = $resolvedClient ? $assignedClients->firstWhere('id', $resolvedClient->id) : null;

            if (!$client) {
                return redirect()->route('agent.dashboard')
                               ->with('error', 'You are not assigned to this client.');
            }

            $selectedClientId = $client->id;
        }

        $minimumByClientId = AgentClientAssignment::query()
            ->where('agent_id', $user->id)
            ->where('is_active', true)
            ->whereIn('client_id', $assignedClients->pluck('id')->all())
            ->latest('id')
            ->get()
            ->unique('client_id')
            ->mapWithKeys(function (AgentClientAssignment $assignment) {
                return [$assignment->client_id => $assignment->minimumWorkUpdatesRequired()];
            })
            ->toArray();

        return view('agent.work-updates.create', compact('assignedClients', 'selectedClientId', 'minimumByClientId'));
    }

    public function storeWorkUpdate(Request $request)
    {
        $user = Auth::user();
        $action = $request->input('action', 'draft'); // draft or submit

        $workUpdatesData = $request->input('work_updates', []);
        $agentStatuses = array_keys(WorkUpdate::getAgentApplicationStatuses());

        $rules = [
            'client_id' => 'required|exists:users,id',
            'work_updates' => 'required|array|min:1',
            'work_updates.*.job_title' => 'required|string|max:255',
            'work_updates.*.company' => 'required|string|max:255',
            'work_updates.*.applied_date' => 'required|date',
            'work_updates.*.applied_method' => 'required|string',
            'work_updates.*.application_status' => ['required', 'string', Rule::in($agentStatuses)],
            'work_updates.*.job_link' => 'required|url',
            'work_updates.*.job_success_link' => 'required|url',
        ];

        if ($action === 'submit') {
            $rules['work_updates'] = 'required|array|min:1';
        }

        $request->validate($rules, [
            'client_id.required' => 'Client selection is required.',
            'work_updates.required' => 'Add at least one work update.',
            'work_updates.*.application_status.in' => 'Agents can only set Applied or Incomplete Application.',
        ]);

        // Ensure assignment is active
        $assignment = AgentClientAssignment::where('agent_id', $user->id)
                                         ->where('client_id', $request->client_id)
                                         ->where('is_active', true)
                                         ->latest('id')
                                         ->first();
        if (!$assignment) {
            return back()->with('error', 'You are not assigned to this client.')->withInput();
        }

        // Prevent submission if service ended
        if ($action === 'submit' && $assignment->service_end_date && $assignment->service_end_date->isPast()) {
            return back()->with('error', 'Service has ended for this client. You cannot submit new work updates.')->withInput();
        }

        try {
            DB::transaction(function () use ($request, $user, $assignment, $workUpdatesData, $action) {
                // Save incoming updates as drafts first
                foreach ($workUpdatesData as $job) {
                    WorkUpdate::create([
                        'agent_id' => $user->id,
                        'client_id' => $request->client_id,
                        'job_title' => $job['job_title'],
                        'company' => $job['company'],
                        'applied_date' => $job['applied_date'] ?? now()->toDateString(),
                        'job_link' => $job['job_link'] ?? null,
                        'job_success_link' => $job['job_success_link'] ?? null,
                        'applied_method' => $job['applied_method'],
                        'application_status' => $job['application_status'],
                        'note' => $job['note'] ?? null,
                        'service_end_date' => $assignment ? $assignment->service_end_date : null,
                        'status' => WorkUpdate::STATUS_DRAFT,
                    ]);
                }

                if ($action === 'draft') {
                    return;
                }

                // Submit: ensure assignment minimum draft count exists for this client
                $this->submitDraftsForClient($user, $request->client_id, $assignment);
            });

            if ($action === 'draft') {
                return redirect()->route('agent.work-updates.drafts')
                    ->with('success', 'Draft saved for this client.');
            }

            return redirect()->route('agent.dashboard')
                ->with('success', 'Work updates submitted to the client.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        } catch (\Exception $e) {
            Log::error('Work Update Submission Failed', [
                'user_id' => $user->id,
                'client_id' => $request->client_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Failed to process work updates. Please try again.')
                ->withInput();
        }
    }

    /**
     * Submit existing drafts for a client (assignment minimum, service must be active).
     */
    private function submitDraftsForClient($user, $clientId, $assignment = null)
    {
        if (!$assignment) {
            $assignment = AgentClientAssignment::where('agent_id', $user->id)
                ->where('client_id', $clientId)
                ->where('is_active', true)
                ->latest('id')
                ->first();
        }

        if (!$assignment) {
            throw new \RuntimeException('You are not assigned to this client.');
        }

        if ($assignment->service_end_date && $assignment->service_end_date->isPast()) {
            throw new \RuntimeException('Service has ended for this client. You cannot submit new work updates.');
        }

        $requiredMinimum = $assignment->minimumWorkUpdatesRequired();

        $drafts = WorkUpdate::where('agent_id', $user->id)
            ->where('client_id', $clientId)
            ->where('status', WorkUpdate::STATUS_DRAFT)
            ->get();

        if ($drafts->count() < $requiredMinimum) {
            throw new \RuntimeException("You need at least {$requiredMinimum} work updates in draft to submit.");
        }

        $drafts->each(function ($draft) {
            $draft->update([
                'status' => WorkUpdate::STATUS_APPROVED,
                'approved_at' => now(),
            ]);
        });

        $client = User::find($clientId);

        // Notify client without exposing agent name
        $this->notificationService->notify(
            $client,
            'New Work Updates',
            'New work updates have been submitted to your account.',
            \App\Models\Notification::TYPE_WORK_UPDATE,
            ['work_updates_count' => $drafts->count()],
            \App\Models\Notification::PRIORITY_NORMAL,
            null,
            route('client.dashboard')
        );

        // Notify admins with agent context
        $admins = User::whereIn('role_id', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->get();
        $this->notificationService->notifyMany(
            $admins,
            'Work Updates Submitted',
            "Agent {$user->name} submitted work updates for {$client->name}.",
            \App\Models\Notification::TYPE_INFO,
            ['agent_id' => $user->id, 'client_id' => $client->id],
            \App\Models\Notification::PRIORITY_NORMAL,
            null,
            route('admin.work-updates')
        );

        // Email to client (agent name not included in template)
        $this->sendDailyWorkUpdateEmail($clientId, $drafts);
    }

    /**
     * Submit drafts from drafts page (per client).
     */
    public function submitDraftGroup(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();
        try {
            DB::transaction(function () use ($user, $request) {
                $assignment = AgentClientAssignment::where('agent_id', $user->id)
                    ->where('client_id', $request->client_id)
                    ->where('is_active', true)
                    ->latest('id')
                    ->first();

                $this->submitDraftsForClient($user, $request->client_id, $assignment);
            });

            return redirect()->route('agent.work-updates.drafts')->with('success', 'Drafts submitted to client.');
        } catch (\RuntimeException $e) {
            return redirect()->route('agent.work-updates.drafts')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Submit draft group failed', [
                'agent_id' => $user->id,
                'client_id' => $request->client_id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('agent.work-updates.drafts')->with('error', 'Failed to submit drafts. Please try again.');
        }
    }

    public function myWorkUpdates(Request $request)
    {
        return view('agent.work-updates.index');
    }

    public function downloadWorkUpdatesPdf(Request $request)
    {
        $user = Auth::user();

        $workUpdates = WorkUpdateFilters::agent($user, $request->only([
            'search',
            'client_id',
            'application_status',
            'status',
            'date_from',
            'date_to',
        ]))
            ->latest('applied_date')
            ->latest('created_at')
            ->get();

        $pdf = Pdf::loadView('agent.work-updates-pdf', compact('workUpdates', 'user'));

        return $pdf->download('agent-work-updates-' . now()->format('Y-m-d') . '.pdf');
    }

    public function downloadWorkUpdatesCsv(Request $request)
    {
        $user = Auth::user();

        $workUpdates = WorkUpdateFilters::agent($user, $request->only([
            'search',
            'client_id',
            'application_status',
            'status',
            'date_from',
            'date_to',
        ]))
            ->latest('applied_date')
            ->latest('created_at')
            ->get();

        $filename = 'agent-work-updates-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($workUpdates) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Applied Date',
                'Submitted At',
                'Client',
                'Job Title',
                'Company',
                'Application Status',
                'Applied Method',
                'Submission Status',
                'Job Link',
                'Success Link',
                'Note',
            ]);

            foreach ($workUpdates as $update) {
                fputcsv($file, [
                    optional($update->applied_date ?? $update->created_at)->format('Y-m-d'),
                    optional($update->created_at)->format('Y-m-d H:i:s'),
                    $update->client?->name,
                    $update->job_title,
                    $update->company,
                    $update->getApplicationStatusLabel(),
                    $update->getAppliedMethodLabel(),
                    $update->getStatusLabel(),
                    $update->job_link,
                    $update->job_success_link,
                    $update->note,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function sendDailyWorkUpdateEmail($clientId, $workUpdates)
    {
        try {
            $client = \App\Models\User::find($clientId);
            $date = now();

            // Convert WorkUpdate models or arrays to array format expected by MailchimpService
            $workUpdatesArray = [];
            foreach ($workUpdates as $workUpdate) {
                // Check if it's a model or array
                if (is_object($workUpdate) && method_exists($workUpdate, 'job_title')) {
                    // It's a WorkUpdate model
                    $workUpdatesArray[] = [
                        'job_title' => $workUpdate->job_title,
                        'company' => $workUpdate->company,
                        'applied_date' => $workUpdate->applied_date,
                        'job_link' => $workUpdate->job_link,
                        'job_success_link' => $workUpdate->job_success_link,
                        'applied_method' => $workUpdate->applied_method,
                        'application_status' => $workUpdate->application_status,
                        'note' => $workUpdate->note,
                    ];
                } else {
                    // It's already an array
                    $workUpdatesArray[] = [
                        'job_title' => $workUpdate['job_title'] ?? '',
                        'company' => $workUpdate['company'] ?? '',
                        'applied_date' => $workUpdate['applied_date'] ?? '',
                        'job_link' => $workUpdate['job_link'] ?? '',
                        'job_success_link' => $workUpdate['job_success_link'] ?? '',
                        'applied_method' => $workUpdate['applied_method'] ?? '',
                        'application_status' => $workUpdate['application_status'] ?? '',
                        'note' => $workUpdate['note'] ?? '',
                    ];
                }
            }

            // Send email using Mailchimp service
            $mailchimpService = new \App\Services\MailchimpService();

            \Illuminate\Support\Facades\Log::info('Attempting to send daily work update email', [
                'client_id' => $clientId,
                'client_email' => $client->email,
                'updates_count' => count($workUpdatesArray),
                'updates_data' => $workUpdatesArray
            ]);

            $result = $mailchimpService->sendDailyWorkUpdate($client, $workUpdatesArray, $date);

            if ($result) {
                \Illuminate\Support\Facades\Log::info('Daily work update email sent successfully', [
                    'client_id' => $clientId,
                    'client_email' => $client->email,
                    'updates_count' => count($workUpdatesArray)
                ]);
            } else {
                \Illuminate\Support\Facades\Log::warning('Failed to send daily work update email', [
                    'client_id' => $clientId,
                    'client_email' => $client->email
                ]);
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send daily work update email', [
                'client_id' => $clientId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Display draft work updates
     */
    public function drafts()
    {
        $user = Auth::user();

        $draftRows = WorkUpdate::with('client')
            ->where('agent_id', $user->id)
            ->where('status', WorkUpdate::STATUS_DRAFT)
            ->orderBy('client_id')
            ->get();

        $minimumByClientId = AgentClientAssignment::query()
            ->where('agent_id', $user->id)
            ->where('is_active', true)
            ->whereIn('client_id', $draftRows->pluck('client_id')->unique()->values()->all())
            ->latest('id')
            ->get()
            ->unique('client_id')
            ->mapWithKeys(function (AgentClientAssignment $assignment) {
                return [$assignment->client_id => $assignment->minimumWorkUpdatesRequired()];
            });

        $drafts = $draftRows
            ->groupBy('client_id')
            ->map(function ($group, $clientId) use ($minimumByClientId) {
                $latestDraft = $group->sortByDesc(function ($draft) {
                    return $draft->draft_saved_at ?? $draft->updated_at;
                })->first();

                return [
                    'client' => $group->first()->client,
                    'count' => $group->count(),
                    'latest' => $group->max('draft_saved_at') ?? $group->max('updated_at'),
                    'draft' => $latestDraft,
                    'draft_id' => $latestDraft?->id,
                    'minimum_required' => (int) ($minimumByClientId[$clientId] ?? AgentClientAssignment::DEFAULT_MINIMUM_WORK_UPDATES),
                ];
            })
            ->sortByDesc(function ($draftInfo) {
                return ($draftInfo['latest'] ?? now())->getTimestamp();
            });

        return view('agent.work-updates.drafts', compact('drafts'));
    }

    /**
     * Save work update as draft
     */
    public function saveDraft(Request $request)
    {
        $user = Auth::user();

        // Debug logging
        Log::info('Draft save request received', [
            'user_id' => $user->id,
            'request_data' => $request->all(),
            'has_work_updates' => $request->has('work_updates'),
            'work_updates_count' => count($request->input('work_updates', [])),
        ]);

        // Handle both 'jobs' and 'work_updates' arrays (form might send either)
        $jobsData = $request->input('jobs', []);
        $workUpdatesData = $request->input('work_updates', []);

        // Use whichever array has data
        $finalJobsData = !empty($jobsData) ? $jobsData : $workUpdatesData;

        // Basic validation - only require client_id
        $request->validate([
            'client_id' => 'required|exists:users,id',
        ]);

        // Only validate job data if we have some
        if (!empty($finalJobsData)) {
            $request->validate([
                'work_updates' => 'array',
                'work_updates.*.job_title' => 'nullable|string|max:255',
                'work_updates.*.company' => 'nullable|string|max:255',
                'work_updates.*.applied_date' => 'nullable|date',
                'work_updates.*.applied_method' => 'nullable|string',
                'work_updates.*.application_status' => 'nullable|string',
            ]);
        }

        try {
            // Check if there's already a draft for this client today
            $existingDraft = WorkUpdate::where('agent_id', $user->id)
                ->where('client_id', $request->client_id)
                ->where('status', WorkUpdate::STATUS_DRAFT)
                ->whereDate('created_at', now()->toDateString())
                ->first();

            if ($existingDraft) {
                // Get existing draft data
                $existingDraftData = $existingDraft->getDraftData() ?? [];

                // Merge new work updates with existing ones
                $newWorkUpdates = $request->input('work_updates', []);
                $existingWorkUpdates = $existingDraftData['work_updates'] ?? [];

                // Combine existing and new work updates
                $combinedWorkUpdates = array_merge($existingWorkUpdates, $newWorkUpdates);

                // Create merged data
                $mergedData = $request->all();
                $mergedData['work_updates'] = $combinedWorkUpdates;

                // Update existing draft with merged data
                $existingDraft->saveDraft($mergedData);
                $draft = $existingDraft;
            } else {
                // Create new draft with minimal required fields
                $draft = WorkUpdate::create([
                    'agent_id' => $user->id,
                    'client_id' => $request->client_id,
                    'status' => WorkUpdate::STATUS_DRAFT,
                    'job_title' => 'Draft Work Update',
                    'company' => 'Draft Company',
                    'applied_date' => now()->toDateString(),
                    'applied_method' => 'web',
                    'application_status' => 'applied',
                    'draft_data' => $request->all(),
                    'draft_saved_at' => now(),
                ]);
            }

            Log::info('Work update draft saved', [
                'draft_id' => $draft->id,
                'agent_id' => $user->id,
                'client_id' => $request->client_id,
                'jobs_count' => count($finalJobsData),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Draft saved successfully',
                'draft_id' => $draft->id,
                'saved_at' => $draft->getDraftSavedTime(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to save work update draft', [
                'agent_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save draft. Please try again.',
            ], 500);
        }
    }

    /**
     * Load draft data for editing
     */
    public function loadDraft(WorkUpdate $draft)
    {
        $user = Auth::user();

        $isMine = ($draft->agent_id === $user->id) || $draft->agent_id === null;
        $isAssignedClient = AgentClientAssignment::where('agent_id', $user->id)
            ->where('client_id', $draft->client_id)
            ->where('is_active', true)
            ->exists();
        $isAdmin = $user->isAdmin() || $user->isSuperAdmin();

        if ((!$isMine && !$isAssignedClient && !$isAdmin) || !$draft->isDraft()) {
            abort(403, 'Unauthorized access to draft.');
        }

        $draftData = $draft->getDraftData();

        if (!$draftData) {
            return response()->json([
                'success' => false,
                'message' => 'No draft data found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'draft_data' => $draftData,
            'saved_at' => $draft->getDraftSavedTime(),
        ]);
    }

    /**
     * Edit a draft
     */
    public function editDraft(Request $request, WorkUpdate $draft)
    {
        $user = Auth::user();

        // Ensure the draft belongs to the authenticated agent or the agent is assigned to this client (legacy safety)
        $isMine = ($draft->agent_id === $user->id) || $draft->agent_id === null;
        $isAssignedClient = AgentClientAssignment::where('agent_id', $user->id)
            ->where('client_id', $draft->client_id)
            ->where('is_active', true)
            ->exists();
        $isAdmin = $user->isAdmin() || $user->isSuperAdmin();

        if (!$isMine && !$isAssignedClient && !$isAdmin) {
            abort(403, 'Unauthorized access to draft.');
        }
        if (!$draft->isDraft()) {
            return redirect()->route('agent.work-updates.drafts')
                ->with('error', 'This work update is no longer a draft.');
        }

        // Get the agent's clients
        $clients = $user->clients;

        // Load all draft rows for this client
        $drafts = WorkUpdate::where('agent_id', $user->id)
            ->where('client_id', $draft->client_id)
            ->where('status', WorkUpdate::STATUS_DRAFT)
            ->orderBy('created_at')
            ->get();

        if ($drafts->isEmpty()) {
            return redirect()->route('agent.work-updates.drafts')
                ->with('error', 'No drafts found for this client.');
        }

        $assignment = AgentClientAssignment::where('agent_id', $user->id)
            ->where('client_id', $draft->client_id)
            ->latest('id')
            ->first();
        $minimumRequired = $assignment?->minimumWorkUpdatesRequired() ?? AgentClientAssignment::DEFAULT_MINIMUM_WORK_UPDATES;

        return view('agent.work-updates.edit', [
            'draft' => $draft,
            'clients' => $clients,
            'drafts' => $drafts,
            'minimumRequired' => $minimumRequired,
        ]);
    }

    /**
     * Update a draft
     */
    public function updateDraft(Request $request, WorkUpdate $draft)
    {
        $user = Auth::user();
        $agentStatuses = array_keys(WorkUpdate::getAgentApplicationStatuses());

        // Ensure the draft belongs to the authenticated agent
        $isMine = ($draft->agent_id === $user->id) || $draft->agent_id === null;
        $isAdmin = $user->isAdmin() || $user->isSuperAdmin();
        $isAssignedClient = AgentClientAssignment::where('agent_id', $user->id)
            ->where('client_id', $draft->client_id)
            ->where('is_active', true)
            ->exists();

        if (!$isMine && !$isAdmin && !$isAssignedClient) {
            abort(403, 'Unauthorized access to draft.');
        }

        // Load all drafts for this client
        $draftsQuery = WorkUpdate::where('client_id', $draft->client_id)
            ->where('status', WorkUpdate::STATUS_DRAFT);

        if (!$isAdmin) {
            $draftsQuery->where(function ($q) use ($user) {
                $q->where('agent_id', $user->id)
                  ->orWhereNull('agent_id');
            });
        }

        $drafts = $draftsQuery->orderBy('created_at')->get();

        if ($drafts->isEmpty()) {
            return redirect()->route('agent.work-updates.drafts')
                ->with('error', 'No drafts found for this client.');
        }

        // Validation
        $request->validate([
            'client_id' => 'required|exists:users,id',
            'updates' => 'required|array|min:1',
            'updates.*.job_title' => 'required|string|max:255',
            'updates.*.company' => 'required|string|max:255',
            'updates.*.applied_date' => 'required|date',
            'updates.*.applied_method' => 'required|string',
            'updates.*.application_status' => ['required', 'string', Rule::in($agentStatuses)],
            'updates.*.job_link' => 'required|url',
            'updates.*.job_success_link' => 'required|url',
            'updates.*.note' => 'nullable|string',
        ], [
            'updates.*.application_status.in' => 'Agents can only set Applied or Incomplete Application.',
        ]);

        $updates = $request->input('updates', []);

        DB::transaction(function () use ($updates, $drafts, $user, $request, $draft) {
            // Update each draft row with provided data
            foreach ($drafts as $row) {
                if (!isset($updates[$row->id])) {
                    continue;
                }
                $data = $updates[$row->id];
                $row->update([
                    'job_title' => $data['job_title'],
                    'company' => $data['company'],
                    'applied_date' => $data['applied_date'],
                    'applied_method' => $data['applied_method'],
                    'application_status' => $data['application_status'],
                    'job_link' => $data['job_link'] ?? null,
                    'job_success_link' => $data['job_success_link'] ?? null,
                    'note' => $data['note'] ?? null,
                ]);
            }

            // If submitting, ensure assignment minimum and mark as submitted/approved via existing helper
            if ($request->has('_submit')) {
                $this->submitDraftsForClient($user, $draft->client_id);
            }
        });

        if ($request->has('_submit')) {
            return redirect()->route('agent.work-updates.index')
                ->with('success', 'Work updates submitted successfully!');
        }

        return redirect()->route('agent.work-updates.drafts')
            ->with('success', 'Draft updated successfully!');
    }

    /**
     * Delete a single draft item from the draft edit page.
     */
    public function deleteDraft(WorkUpdate $draft)
    {
        $user = Auth::user();

        $isMine = ($draft->agent_id === $user->id) || $draft->agent_id === null;
        $isAdmin = $user->isAdmin() || $user->isSuperAdmin();

        if ((!$isMine && !$isAdmin) || !$draft->isDraft()) {
            abort(403, 'Unauthorized access to draft.');
        }

        $clientId = $draft->client_id;
        $draftId = $draft->id;

        try {
            $remainingDraft = DB::transaction(function () use ($clientId, $draftId, $isAdmin, $user) {
                WorkUpdate::query()->whereKey($draftId)->delete();

                $remainingDraftQuery = WorkUpdate::query()
                    ->where('client_id', $clientId)
                    ->where('status', WorkUpdate::STATUS_DRAFT)
                    ->orderBy('created_at');

                if (!$isAdmin) {
                    $remainingDraftQuery->where(function ($query) use ($user) {
                        $query->where('agent_id', $user->id)
                            ->orWhereNull('agent_id');
                    });
                }

                return $remainingDraftQuery->first();
            });

            Log::info('Work update draft item deleted', [
                'draft_id' => $draftId,
                'client_id' => $clientId,
                'agent_id' => $user->id,
            ]);

            if ($remainingDraft) {
                return redirect()
                    ->route('agent.work-updates.edit-draft', ['draft' => $remainingDraft->id])
                    ->with('success', 'Draft item deleted successfully.');
            }

            return redirect()
                ->route('agent.work-updates.drafts')
                ->with('success', 'Draft item deleted successfully. No more drafts remain for this client.');
        } catch (\Throwable $e) {
            Log::error('Failed to delete work update draft item', [
                'draft_id' => $draftId,
                'client_id' => $clientId,
                'agent_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to delete draft item. Please try again.');
        }
    }

    /**
     * Delete all draft items for a client from the drafts list page.
     */
    public function deleteDraftGroup(WorkUpdate $draft)
    {
        $user = Auth::user();

        $isMine = ($draft->agent_id === $user->id) || $draft->agent_id === null;
        $isAdmin = $user->isAdmin() || $user->isSuperAdmin();

        if ((!$isMine && !$isAdmin) || !$draft->isDraft()) {
            abort(403, 'Unauthorized access to draft.');
        }

        $draftsQuery = WorkUpdate::query()
            ->where('client_id', $draft->client_id)
            ->where('status', WorkUpdate::STATUS_DRAFT);

        if (!$isAdmin) {
            $draftsQuery->where(function ($query) use ($user) {
                $query->where('agent_id', $user->id)
                    ->orWhereNull('agent_id');
            });
        }

        $drafts = $draftsQuery->get();

        if ($drafts->isEmpty()) {
            return redirect()
                ->route('agent.work-updates.drafts')
                ->with('error', 'No drafts found for this client.');
        }

        try {
            $deletedCount = $drafts->count();
            $clientId = $draft->client_id;
            $draftIds = $drafts->pluck('id')->all();

            DB::transaction(function () use ($draftIds) {
                WorkUpdate::query()->whereIn('id', $draftIds)->delete();
            });

            Log::info('Work update draft group deleted', [
                'client_id' => $clientId,
                'agent_id' => $user->id,
                'deleted_count' => $deletedCount,
            ]);

            return redirect()
                ->route('agent.work-updates.drafts')
                ->with('success', "{$deletedCount} draft item(s) deleted successfully.");
        } catch (\Throwable $e) {
            Log::error('Failed to delete work update draft group', [
                'client_id' => $draft->client_id,
                'agent_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('agent.work-updates.drafts')
                ->with('error', 'Failed to delete drafts. Please try again.');
        }
    }

    public function clients(Request $request)
    {
        $user = Auth::user();
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'service_status' => trim((string) $request->query('service_status', '')),
            'service_type' => trim((string) $request->query('service_type', '')),
        ];

        $assignedClients = $user->assignedClients();
        $assignedClients->load([
            'clientProfile',
            'workUpdates' => function ($query) {
                $query->latest();
            },
        ]);

        $assignments = AgentClientAssignment::query()
            ->where('agent_id', $user->id)
            ->with('client')
            ->newestFirst()
            ->get()
            ->unique('client_id')
            ->keyBy('client_id');

        $assignedClients = $assignedClients
            ->map(function (User $client) use ($assignments) {
                $assignment = $assignments->get($client->id);
                $client->current_assignment = $assignment;
                $client->service_status_key = $this->resolveAssignmentStatus($assignment);
                $client->service_status_label = ucfirst(str_replace('_', ' ', $client->service_status_key));

                return $client;
            })
            ->filter(function (User $client) use ($filters) {
                if ($filters['search'] !== '') {
                    $search = strtolower($filters['search']);
                    $haystack = strtolower($client->name . ' ' . $client->email);

                    if (!str_contains($haystack, $search)) {
                        return false;
                    }
                }

                if ($filters['service_status'] !== '' && $client->service_status_key !== $filters['service_status']) {
                    return false;
                }

                if ($filters['service_type'] !== '' && ($client->clientProfile?->service_type ?? '') !== $filters['service_type']) {
                    return false;
                }

                return true;
            })
            ->values();

        return view('agent.clients.index', compact('assignedClients', 'filters'));
    }

    public function showClient(User $client)
    {
        $user = Auth::user();

        $assignment = AgentClientAssignment::where('agent_id', $user->id)
            ->where('client_id', $client->id)
            ->newestFirst()
            ->first();

        if (!$assignment) {
            abort(404, 'Client not found or not assigned to you');
        }

        // Load client relationships
        $client->load([
            'clientProfile',
            'workUpdates' => function($query) {
                $query->latest()->limit(10);
            },
            'clientSubmissions' => function($query) {
                $query->latest()->limit(10);
            }
        ]);

        // Assignment details are already loaded above

        return view('agent.clients.show', compact('client', 'assignment'));
    }

    protected function resolveAssignmentStatus(?AgentClientAssignment $assignment): string
    {
        if (!$assignment) {
            return 'unassigned';
        }

        if ($assignment->isServiceCompleted()) {
            return 'completed';
        }

        if (!$assignment->is_active) {
            return 'inactive';
        }

        if ($assignment->service_end_date && $assignment->service_end_date->isPast()) {
            return 'expired';
        }

        return 'active';
    }

    public function requestOtp(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:users,id',
            'message' => 'nullable|string|max:500',
            'expiry_minutes' => 'required|integer|min:1|max:10080',
        ]);

        try {
            $user = Auth::user();

            // Resolve client access: admins can pick any client; agents only assigned clients
            if ($user->isAdmin()) {
                $client = User::where('id', $request->client_id)
                    ->where('role_id', User::ROLE_CLIENT)
                    ->first();
            } else {
                $assignedClients = $user->assignedClients();
                $client = $assignedClients->where('id', $request->client_id)->first();
            }

            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Client not found or not assigned to you.'
                ], 404);
            }

            // Create OTP verification
            $otpVerification = \App\Models\OtpVerification::createForClient(
                $user->id,
                $request->client_id,
                $request->message,
                (int) $request->expiry_minutes
            );

            $mailSent = true;

            // Do not fail the OTP request if SMTP is unavailable.
            try {
                Mail::to($client->email)->send(new \App\Mail\OtpRequestMail($otpVerification));
            } catch (\Throwable $mailError) {
                $mailSent = false;

                Log::warning('OTP mail send failed', [
                    'error' => $mailError->getMessage(),
                    'client_id' => $client->id,
                    'otp_id' => $otpVerification->id,
                ]);
            }

            // Create notification for client
            \App\Models\Notification::create([
                'user_id' => $request->client_id,
                'type' => 'otp_request',
                'title' => 'OTP Request Received',
                'message' => "Your agent {$user->name} has requested company information and OTP for a job application",
                'data' => [
                    'otp_verification_id' => $otpVerification->id,
                    'agent_name' => $user->name,
                    'message' => $request->message,
                    'expires_at' => optional($otpVerification->expires_at)->toDateTimeString(),
                    'expiry_minutes' => (int) $request->expiry_minutes,
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => $mailSent
                    ? 'OTP request sent successfully to ' . $client->name . ' (expires in ' . (int) $request->expiry_minutes . ' minutes)'
                    : 'OTP request was saved, but the notification email could not be sent. Please verify your mail settings.'
            ]);

        } catch (\Exception $e) {
            Log::error('OTP request failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP request. Please try again.'
            ], 500);
        }
    }

    /**
     * Check if agent is currently checked in
     */
    private function isAgentCheckedIn($agentId)
    {
        // Check if agent has an active check-in session today
        $today = now()->format('Y-m-d');

        // Check if agent has checked in today and hasn't checked out
        $checkInToday = DB::table('agent_activities')
            ->where('agent_id', $agentId)
            ->where('activity_type', 'check_in')
            ->whereDate('activity_time', $today)
            ->exists();

        $checkOutToday = DB::table('agent_activities')
            ->where('agent_id', $agentId)
            ->where('activity_type', 'check_out')
            ->whereDate('activity_time', $today)
            ->exists();

        // Agent is checked in if they checked in today but haven't checked out
        return $checkInToday && !$checkOutToday;
    }

    public function trackJoin(Request $request)
    {
        try {
            $agentId = Auth::id();
            $todayMeeting = Meeting::where('date', today())->first();

            if (!$todayMeeting) {
                return response()->json([
                    'success' => false,
                    'message' => 'No meeting found for today'
                ]);
            }

            // Check if agent already has attendance record
            $attendance = Attendance::where('meeting_id', $todayMeeting->id)
                                      ->where('agent_id', $agentId)
                                      ->first();

            if (!$attendance) {
                // Create new attendance record
                Attendance::create([
                    'meeting_id' => $todayMeeting->id,
                    'agent_id' => $agentId,
                    'join_time' => now(),
                    'status' => 'joined',
                    'screen_shared' => false
                ]);
            } else {
                // Update existing record
                $attendance->update([
                    'join_time' => now(),
                    'status' => 'joined'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Your attendance has been tracked successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error tracking agent join: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error tracking attendance: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Join today's meeting
     */
    public function joinMeeting()
    {
        try {
            $user = Auth::user();

            // Record agent joining meeting
            $attendance = $this->googleMeetService->recordAgentJoin($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Successfully joined the meeting!',
                'attendance' => $attendance
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to join meeting: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to join meeting: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Leave today's meeting
     */
    public function leaveMeeting()
    {
        try {
            $user = Auth::user();

            // Record agent leaving meeting
            $attendance = $this->googleMeetService->recordAgentLeave($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Successfully left the meeting!',
                'attendance' => $attendance
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to leave meeting: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to leave meeting: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get meeting attendance status
     */
    public function getMeetingStatus()
    {
        try {
            $user = Auth::user();
            $todayMeeting = $this->googleMeetService->getTodaysMeeting();

            if (!$todayMeeting) {
                return response()->json([
                    'success' => false,
                    'message' => 'No meeting scheduled for today'
                ]);
            }

            // Check if agent is currently in the meeting
            $attendance = Attendance::where('agent_id', $user->id)
                                 ->where('meeting_id', $todayMeeting->id)
                                 ->where('status', 'joined')
                                 ->whereNull('leave_time')
                                 ->first();

            return response()->json([
                'success' => true,
                'in_meeting' => $attendance ? true : false,
                'meeting' => $todayMeeting,
                'attendance' => $attendance
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get meeting status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get meeting status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start screen sharing
     */
    public function startScreenSharing()
    {
        try {
            $user = Auth::user();

            // Start screen sharing
            $attendance = $this->googleMeetService->startScreenSharing($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Screen sharing started!',
                'attendance' => $attendance
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to start screen sharing: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to start screen sharing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Stop screen sharing
     */
    public function stopScreenSharing()
    {
        try {
            $user = Auth::user();

            // Stop screen sharing
            $attendance = $this->googleMeetService->stopScreenSharing($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Screen sharing stopped!',
                'attendance' => $attendance
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to stop screen sharing: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to stop screen sharing: ' . $e->getMessage()
            ], 500);
        }
    }
}
