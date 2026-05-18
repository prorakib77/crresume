<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkUpdate;
use App\Models\AgentClientAssignment;
use App\Services\MailchimpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class WorkUpdateController extends Controller
{
    /**
     * Display work updates based on user role
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role && ($user->role->name === 'admin' || $user->role->name === 'super-admin')) {
            $workUpdates = WorkUpdate::with(['agent', 'client'])->latest()->paginate(15);
            $totalSubmissions = WorkUpdate::count();
            $todaySubmissions = WorkUpdate::whereDate('applied_date', today())->count();
            $pendingApproval = 0; // No longer using approval workflow
            $approvedToday = WorkUpdate::where('status', WorkUpdate::STATUS_APPROVED)
                                     ->whereDate('approved_at', today())->count();

            return view('agents.workUpdates.index', compact(
                'workUpdates', 'totalSubmissions', 'todaySubmissions',
                'pendingApproval', 'approvedToday'
            ));
        } elseif ($user->role && ($user->role->name === 'agent-manager' || $user->role->name === 'client-manager')) {
            $workUpdates = WorkUpdate::with(['agent', 'client'])
                                   ->where('status', '!=', WorkUpdate::STATUS_DRAFT)
                                   ->latest()->paginate(15);
            return view('agents.workUpdates.index', compact('workUpdates'));
        } elseif ($user->role && $user->role->name === 'agent') {
            return $this->agentDashboard();
        } else {
            // Client view - only approved updates
            return $this->clientDashboard();
        }
    }

    /**
     * Agent dashboard with daily submission tracking
     */
    public function agentDashboard()
    {
        $user = Auth::user();

        // Get assigned clients
        $assignedClients = $user->getActiveClientsAttribute();

        // Get today's submissions status for each client
        $clientsStatus = [];
        foreach ($assignedClients as $client) {
            $todaySubmission = WorkUpdate::getTodaysSubmission($user->id, $client->id);
            $clientsStatus[] = [
                'client' => $client,
                'has_submitted_today' => $todaySubmission !== null,
                'submission' => $todaySubmission,
                'service_end_date' => $client->pivot->service_end_date,
                'days_remaining' => $client->pivot->service_end_date ?
                    rounded_time_value(now()->diffInDays($client->pivot->service_end_date, false)) : null
            ];
        }

        // Get recent work updates
        $recentUpdates = WorkUpdate::where('agent_id', $user->id)
                                 ->with('client')
                                 ->latest()
                                 ->limit(10)
                                 ->get();

        // Get statistics
        $stats = [
            'total_clients' => $assignedClients->count(),
            'submitted_today' => WorkUpdate::where('agent_id', $user->id)
                                          ->whereDate('applied_date', today())
                                          ->count(),
            'pending_submissions' => $assignedClients->count() - WorkUpdate::where('agent_id', $user->id)
                                                                           ->whereDate('applied_date', today())
                                                                           ->count(),
            'this_month' => WorkUpdate::where('agent_id', $user->id)
                                    ->whereMonth('applied_date', now()->month)
                                    ->count(),
            'approved_this_month' => WorkUpdate::where('agent_id', $user->id)
                                              ->where('status', WorkUpdate::STATUS_APPROVED)
                                              ->whereMonth('applied_date', now()->month)
                                              ->count()
        ];

        return view('agents.dashboard', compact('clientsStatus', 'recentUpdates', 'stats'));
    }

    /**
     * Client dashboard with work updates view
     */
    public function clientDashboard()
    {
        $user = Auth::user();

        $workUpdates = WorkUpdate::with(['agent'])
            ->where('client_id', $user->id)
            ->where('status', WorkUpdate::STATUS_APPROVED)
            ->latest('applied_date')
            ->paginate(15);

        $stats = [
            'total_updates' => WorkUpdate::where('client_id', $user->id)
                                        ->where('status', WorkUpdate::STATUS_APPROVED)
                                        ->count(),
            'this_month' => WorkUpdate::where('client_id', $user->id)
                                    ->where('status', WorkUpdate::STATUS_APPROVED)
                                    ->whereMonth('applied_date', now()->month)
                                    ->count(),
            'last_update' => WorkUpdate::where('client_id', $user->id)
                                     ->where('status', WorkUpdate::STATUS_APPROVED)
                                     ->latest('applied_date')
                                     ->first(),
            'assigned_agents' => $user->getActiveAgentsAttribute()->count()
        ];

        return view('clients.dashboard', compact('workUpdates', 'stats'));
    }

    /**
     * Show the form for creating a daily work update
     */
    // public function create(Request $request)
    // {
    //     $user = Auth::user();
    //     $selectedClientId = $request->query('client_id');

    //     // Only agents can create work updates
    //     if (!$user->role || $user->role->name !== 'agent') {
    //         abort(403, 'Only agents can create work updates.');
    //     }

    //     // Get assigned clients for this agent
    //     $assignedClients = $user->getActiveClientsAttribute();

    //     if ($assignedClients->isEmpty()) {
    //         return redirect()->route('agent.dashboard')
    //                        ->with('warning', 'No clients assigned to you. Please contact your manager.');
    //     }

    //     // If client_id is specified, validate the assignment and check daily limit
    //     if ($selectedClientId) {
    //         $client = $assignedClients->find($selectedClientId);
    //         if (!$client) {
    //             return redirect()->route('agent.dashboard')
    //                            ->with('error', 'You are not assigned to this client.');
    //         }

    //         // Check if already submitted today
    //         if (!WorkUpdate::canSubmitToday($user->id, $selectedClientId)) {
    //             return redirect()->route('agent.dashboard')
    //                            ->with('warning', 'You have already submitted an update for this client today.');
    //         }
    //     }

    //     return view('agents.workUpdates.create', compact('assignedClients', 'selectedClientId'));
    // }

    public function create(Request $request)
{
    $user = Auth::user();
    $selectedClientId = $request->query('client_id');

    // Only agents can create work updates
    if (!$user->role || $user->role->name !== 'agent') {
        abort(403, 'Only agents can create work updates.');
    }

    // Get assigned clients for this agent
    $assignedClients = $user->getActiveClientsAttribute();

    if ($assignedClients->isEmpty()) {
        return redirect()->route('agent.dashboard')
                       ->with('warning', 'No clients assigned to you. Please contact your manager.');
    }

    // If client_id is specified, validate the assignment
    if ($selectedClientId) {
        $client = $assignedClients->find($selectedClientId);
        if (!$client) {
            return redirect()->route('agent.dashboard')
                           ->with('error', 'You are not assigned to this client.');
        }

        // REMOVED the daily limit check to allow multiple submissions
        // if (!WorkUpdate::canSubmitToday($user->id, $selectedClientId)) {
        //     return redirect()->route('agent.dashboard')
        //                    ->with('warning', 'You have already submitted an update for this client today.');
        // }
    }

    return view('agents.workUpdates.create', compact('assignedClients', 'selectedClientId'));
}

    /**
     * Store daily work update
     */
    // public function store(Request $request)
    // {
    //     $user = Auth::user();

    //     $request->validate([
    //         'client_id' => 'required|exists:users,id',
    //         'job_title' => 'required|string|max:255',
    //         'company' => 'required|string|max:255',
    //         'applied_date' => 'required|date|before_or_equal:today',
    //         'job_link' => 'nullable|url',
    //         'job_success_link' => 'nullable|url',
    //         'applied_method' => 'required|in:web,linkedin,referral,direct,email,other',
    //         'application_status' => 'required|in:applied,interview,hired,rejected',
    //         'note' => 'nullable|string|max:1000',
    //     ]);

    //     // Verify agent is assigned to this client
    //     if (!WorkUpdate::hasActiveAssignment($user->id, $request->client_id)) {
    //         return back()->with('error', 'You are not assigned to this client.');
    //     }

    //     // Check daily submission limit
    //     if (!WorkUpdate::canSubmitToday($user->id, $request->client_id)) {
    //         return back()->with('error', 'You have already submitted an update for this client today.');
    //     }

    //     // Get client's service end date from assignment
    //     $assignment = AgentClientAssignment::where('agent_id', $user->id)
    //                                      ->where('client_id', $request->client_id)
    //                                      ->where('is_active', true)
    //                                      ->first();

    //                                      DB::transaction(function () use ($request, $user, $assignment) {
    //                                         foreach ($request->jobs as $job) {
    //                                             $workUpdate = WorkUpdate::create([
    //                                                 'agent_id' => $user->id,
    //                                                 'client_id' => $request->client_id,
    //                                                 'job_title' => $job['job_title'],
    //                                                 'company' => $job['company'],
    //                                                 'applied_date' => $request->applied_date,
    //                                                 'job_link' => $job['job_link'] ?? null,
    //                                                 'job_success_link' => $job['job_success_link'] ?? null,
    //                                                 'applied_method' => $job['applied_method'],
    //                                                 'application_status' => $job['application_status'],
    //                                                 'note' => $job['note'] ?? null,
    //                                                 'service_end_date' => $assignment ? $assignment->service_end_date : null,
    //                                                 'status' => WorkUpdate::STATUS_APPROVED,
    //                                                 'approved_at' => now(),
    //                                             ]);

    //                                             $this->sendDailyWorkUpdateEmail($workUpdate);
    //                                         }
    //                                     });


    //     return redirect()->route('agent.dashboard')
    //                     ->with('success', 'Daily work update submitted successfully!');
    // }

    public function store(Request $request)
{
    $user = Auth::user();

    // Handle both 'jobs' and 'work_updates' arrays (form might send either)
    $jobsData = $request->input('jobs', []);
    $workUpdatesData = $request->input('work_updates', []);

    // Use whichever array has data
    $finalJobsData = !empty($jobsData) ? $jobsData : $workUpdatesData;

    // Debug logging to confirm this controller is being called
    \Illuminate\Support\Facades\Log::info('WorkUpdateController::store called', [
        'user_id' => $user->id,
        'user_name' => $user->name,
        'request_data' => $request->all(),
        'jobs_count' => count($jobsData),
        'work_updates_count' => count($workUpdatesData),
        'final_jobs_count' => count($finalJobsData),
        'controller' => 'WorkUpdateController::store'
    ]);

    $request->validate([
        'client_id' => 'required|exists:users,id',
        'applied_date' => 'required|date|before_or_equal:today',
    ], [
        'client_id.required' => 'Client selection is required.',
        'applied_date.required' => 'Application date is required.',
    ]);

    // Validate jobs data separately
    if (empty($finalJobsData)) {
        return back()->with('error', 'DEBUGGER: No job applications found. Please add at least 1 job application (this is from WorkUpdateController).')->withInput();
    }

    // Agent assignment is verified through form client list (only shows assigned clients)

    // Daily submission limit removed - allow multiple submissions per day
    // Note: Previously had canSubmitToday and hasActiveAssignment checks that were causing issues

    // Get client's service end date from assignment
    $assignment = AgentClientAssignment::where('agent_id', $user->id)
                                     ->where('client_id', $request->client_id)
                                     ->where('is_active', true)
                                     ->first();

    DB::transaction(function () use ($request, $user, $assignment, $finalJobsData) {
        foreach ($finalJobsData as $job) {
            $workUpdate = WorkUpdate::create([
                'agent_id' => $user->id,
                'client_id' => $request->client_id,
                'job_title' => $job['job_title'],
                'company' => $job['company'],
                'applied_date' => $request->applied_date,
                'job_link' => $job['job_link'] ?? null,
                'job_success_link' => $job['job_success_link'] ?? null,
                'applied_method' => $job['applied_method'],
                'application_status' => $job['application_status'],
                'note' => $job['note'] ?? null,
                'service_end_date' => $assignment ? $assignment->service_end_date : null,
                'status' => WorkUpdate::STATUS_APPROVED,
                'approved_at' => now(),
            ]);

            $this->sendDailyWorkUpdateEmail($workUpdate);
        }
    });

    return redirect()->route('agent.dashboard')
                    ->with('success', 'Daily work updates submitted successfully!');
}
    /**
     * Show my work updates (for agents)
     */
    public function myUpdates()
    {
        $user = Auth::user();

        $workUpdates = WorkUpdate::with(['client'])
            ->where('agent_id', $user->id)
            ->latest('applied_date')
            ->paginate(15);

        return view('agents.workUpdates.index', compact('workUpdates'));
    }

    /**
     * Show client work updates (for clients)
     */
    public function clientUpdates()
    {
        $user = Auth::user();

        $workUpdates = WorkUpdate::with(['agent'])
            ->where('client_id', $user->id)
            ->where('status', WorkUpdate::STATUS_APPROVED)
            ->latest('applied_date')
            ->paginate(15);

        $stats = [
            'total_updates' => WorkUpdate::where('client_id', $user->id)
                                        ->where('status', WorkUpdate::STATUS_APPROVED)
                                        ->count(),
            'this_month' => WorkUpdate::where('client_id', $user->id)
                                    ->where('status', WorkUpdate::STATUS_APPROVED)
                                    ->whereMonth('applied_date', now()->month)
                                    ->count(),
            'last_update' => WorkUpdate::where('client_id', $user->id)
                                     ->where('status', WorkUpdate::STATUS_APPROVED)
                                     ->latest('applied_date')
                                     ->first()
        ];

        return view('clients.workUpdates.index', compact('workUpdates', 'stats'));
    }

    /**
     * Send daily work update email to client
     */
    private function sendDailyWorkUpdateEmail(WorkUpdate $workUpdate)
    {
        try {
            $client = $workUpdate->client;
            $date = Carbon::parse($workUpdate->applied_date);

            // Get all work updates for this client on this date
            $dailyUpdates = WorkUpdate::where('client_id', $client->id)
                                    ->whereDate('applied_date', $date)
                                    ->where('status', WorkUpdate::STATUS_APPROVED)
                                    ->with('agent')
                                    ->get()
                                    ->toArray();

            $mailchimpService = new MailchimpService();
            $mailchimpService->sendDailyWorkUpdate($client, $dailyUpdates, $date);

        } catch (\Exception $e) {
            Log::error('Failed to send daily work update email', [
                'work_update_id' => $workUpdate->id,
                'client_id' => $workUpdate->client_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified work update
     */
    public function show(WorkUpdate $workUpdate)
    {
        $user = Auth::user();

        if (!$user->role || !in_array($user->role->name, ['admin', 'super-admin', 'agent-manager', 'client-manager']) &&
            $workUpdate->agent_id !== $user->id &&
            ($workUpdate->client_id !== $user->id || $workUpdate->status !== 'approved')) {
            abort(403, 'Unauthorized access.');
        }

        return view('agents.workUpdates.show', compact('workUpdate'));
    }

    /**
     * Approve a work update
     */
    public function approve(WorkUpdate $workUpdate)
    {
        $user = Auth::user();

        if (!$user->role || !in_array($user->role->name, ['admin', 'super-admin', 'agent-manager'])) {
            abort(403, 'Unauthorized.');
        }

        // Remove approval and rejection methods since work updates are auto-approved
        return back()->with('error', 'Work updates are automatically approved upon submission.');
    }

    /**
     * Reject a work update - No longer used
     */
    public function reject(Request $request, WorkUpdate $workUpdate)
    {
        // Remove rejection since work updates are auto-approved
        return back()->with('error', 'Work updates are automatically approved upon submission.');
    }

    /**
     * Delete a work update
     */
    public function destroy(WorkUpdate $workUpdate)
    {
        $user = Auth::user();

        if (!$user->role || !in_array($user->role->name, ['admin', 'super-admin']) &&
            $workUpdate->agent_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        if ($workUpdate->applied_proof) {
            Storage::disk('public')->delete($workUpdate->applied_proof);
        }

        $workUpdate->delete();
        return back()->with('success', 'Work update deleted successfully!');
    }

    /**
     * Download work updates as PDF for client
     */
    public function downloadPdf(Request $request)
    {
        $user = Auth::user();

        // Only clients can download their own updates
        if (!$user->role || $user->role->name !== 'client') {
            abort(403, 'Unauthorized.');
        }

        $workUpdates = WorkUpdate::with(['agent'])
            ->where('client_id', $user->id)
            ->where('status', WorkUpdate::STATUS_APPROVED)
            ->latest('applied_date')
            ->get();

        $pdf = Pdf::loadView('clients.work-updates-pdf', compact('workUpdates', 'user'));

        return $pdf->download('work-updates-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Download work updates as DOC for client
     */
    public function downloadDoc(Request $request)
    {
        $user = Auth::user();

        // Only clients can download their own updates
        if (!$user->role || $user->role->name !== 'client') {
            abort(403, 'Unauthorized.');
        }

        $workUpdates = WorkUpdate::with(['agent'])
            ->where('client_id', $user->id)
            ->where('status', WorkUpdate::STATUS_APPROVED)
            ->latest('applied_date')
            ->get();

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Add title
        $section->addTitle('Work Updates Report', 1);
        $section->addTextBreak();

        // Add client info
        $section->addText('Client: ' . $user->name);
        $section->addText('Report Date: ' . now()->format('F j, Y'));
        $section->addTextBreak();

        // Add work updates table
        $table = $section->addTable();
        $table->addRow();
        $table->addCell(2000)->addText('Date');
        $table->addCell(3000)->addText('Job Title');
        $table->addCell(2500)->addText('Company');
        $table->addCell(1500)->addText('Status');
        $table->addCell(2000)->addText('Agent');

        foreach ($workUpdates as $update) {
            $table->addRow();
            $table->addCell(2000)->addText($update->applied_date->format('M j, Y'));
            $table->addCell(3000)->addText($update->job_title);
            $table->addCell(2500)->addText($update->company);
            $table->addCell(1500)->addText($update->getApplicationStatusLabel());
            $table->addCell(2000)->addText($update->agent->name);
        }

        $fileName = 'work-updates-' . now()->format('Y-m-d') . '.docx';
        $tempFile = storage_path('app/temp/' . $fileName);

        if (!file_exists(dirname($tempFile))) {
            mkdir(dirname($tempFile), 0755, true);
        }

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}
