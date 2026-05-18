<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentClientAssignment;
use App\Models\Attendance;
use App\Models\ClientProfile;
use App\Models\Meeting;
use App\Models\Role;
use App\Models\ScreenSharingLog;
use App\Models\User;
use App\Models\WorkUpdate;
use App\Services\NotificationService;
use App\Services\GoogleMeetService;
use App\Services\NoticeService;
use App\Services\UserCommunicationService;
use App\Support\WorkUpdateFilters;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class DashboardController extends Controller
{
    protected $notificationService;
    protected $googleMeetService;
    protected $noticeService;
    protected $communicationService;

    public function __construct(NotificationService $notificationService, GoogleMeetService $googleMeetService, NoticeService $noticeService, UserCommunicationService $communicationService)
    {
        $this->notificationService = $notificationService;
        $this->googleMeetService = $googleMeetService;
        $this->noticeService = $noticeService;
        $this->communicationService = $communicationService;
    }

    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_agents' => User::where('role_id', 3)->count(),
            'total_clients' => User::where('role_id', 4)->count(),
            'total_work_updates' => WorkUpdate::count(),
            'today_updates' => WorkUpdate::whereDate('created_at', today())->count(),
            'active_assignments' => AgentClientAssignment::where('is_active', true)->count(),
        ];

        $recent_users = User::with('role')->latest()->take(5)->get();
        $recent_updates = WorkUpdate::with(['agent', 'client'])->latest()->take(5)->get();

        // Get today's meeting
        $todayMeeting = $this->googleMeetService->getTodaysMeeting();

        // Get meeting attendance data
        $meetingStats = [];
        if ($todayMeeting) {
            $meetingStats = $this->googleMeetService->getMeetingStats($todayMeeting->id);
            $meetingStats['attendance_report'] = $this->googleMeetService->getAttendanceReport($todayMeeting->id);
        }

        return view('admin.dashboard', compact('stats', 'recent_users', 'recent_updates', 'todayMeeting', 'meetingStats'));
    }

    public function users(Request $request)
    {
        return view('admin.users.index');
    }

    public function userEmail(Request $request)
    {
        $selectedUserId = $request->query('user_id', $request->session()->getOldInput('email_user_id'));
        $recipientScope = $request->query('recipient_scope', 'individual');

        $recipientOptions = User::query()
            ->select('id', 'name', 'email')
            ->whereNotNull('email')
            ->where('email', '!=', '');
        $recipientCount = (clone $recipientOptions)->count();
        $recipientOptions = $recipientOptions
            ->orderBy('name')
            ->limit(40)
            ->get()
            ->map(fn (User $user) => [
                'value' => $user->id,
                'text' => $user->name . ' (' . $user->email . ')',
            ]);

        $selectedUser = null;

        if (filled($selectedUserId)) {
            $selectedUser = User::query()
                ->with('role')
                ->whereKey($selectedUserId)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->first();

            if ($selectedUser) {
                $alreadyIncluded = $recipientOptions->contains(
                    fn (array $option) => (string) $option['value'] === (string) $selectedUser->id
                );

                if (!$alreadyIncluded) {
                    $recipientOptions->prepend([
                        'value' => $selectedUser->id,
                        'text' => $selectedUser->name . ' (' . $selectedUser->email . ')',
                    ]);
                }
            }
        }

        return view('admin.user-email.index', compact('selectedUser', 'recipientScope', 'recipientOptions', 'recipientCount'));
    }

    public function createUser()
    {
        $roles = \App\Models\Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function storeUser(Request $request)
    {
        $isClientRole = (int) $request->input('role_id') === User::ROLE_CLIENT;

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
            'onboarding_collected' => [$isClientRole ? 'required' : 'nullable', 'in:yes,no'],
            'estimated_resume_completion_date' => ['nullable', 'date'],
            'estimated_cover_letter_completion_date' => ['nullable', 'date'],
            'estimated_application_start_date' => ['nullable', 'date'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        if ((int) $user->role_id === User::ROLE_CLIENT) {
            $onboardingCollected = $request->input('onboarding_collected', 'no') === 'yes';

            ClientProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'status' => 0,
                    'onboarding_visible' => !$onboardingCollected,
                    'onboarding_status' => $onboardingCollected
                        ? ClientProfile::ONBOARDING_STATUS_COMPLETED
                        : ClientProfile::ONBOARDING_STATUS_PENDING,
                    'onboarding_submitted_at' => $onboardingCollected ? now() : null,
                    'onboarding_requested_at' => $onboardingCollected ? null : now(),
                    'estimated_resume_completion_date' => $request->input('estimated_resume_completion_date'),
                    'estimated_cover_letter_completion_date' => $request->input('estimated_cover_letter_completion_date'),
                    'estimated_application_start_date' => $request->input('estimated_application_start_date'),
                    'service_type' => ClientProfile::SERVICE_TYPE_REGULAR,
                ]
            );

            $this->noticeService->syncOnboardingNotice($user, Auth::user());
            $this->communicationService->sendClientWelcomeEmail($user);
        }

        return redirect()->route('admin.users')->with('success', 'User created successfully.');
    }

    public function editUser(User $user)
    {
        $roles = \App\Models\Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function updateUser(Request $request, User $user)
    {
        $isClientRole = (int) $request->input('role_id') === User::ROLE_CLIENT;

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['required', 'in:active,inactive,suspended'],
            'onboarding_collected' => [$isClientRole ? 'required' : 'nullable', 'in:yes,no'],
            'estimated_resume_completion_date' => ['nullable', 'date'],
            'estimated_cover_letter_completion_date' => ['nullable', 'date'],
            'estimated_application_start_date' => ['nullable', 'date'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'status' => $request->status,
        ]);

        if ((int) $user->role_id === User::ROLE_CLIENT) {
            $onboardingCollected = $request->input('onboarding_collected', 'no') === 'yes';

            $profile = ClientProfile::firstOrNew(['user_id' => $user->id]);
            $profile->status = $profile->status ?? 0;
            $profile->onboarding_visible = !$onboardingCollected;
            $profile->onboarding_status = $onboardingCollected
                ? ClientProfile::ONBOARDING_STATUS_COMPLETED
                : ClientProfile::ONBOARDING_STATUS_PENDING;
            $profile->onboarding_submitted_at = $onboardingCollected
                ? ($profile->onboarding_submitted_at ?? now())
                : null;
            $profile->onboarding_requested_at = $onboardingCollected
                ? null
                : ($profile->onboarding_requested_at ?? now());
            $profile->estimated_resume_completion_date = $request->input('estimated_resume_completion_date');
            $profile->estimated_cover_letter_completion_date = $request->input('estimated_cover_letter_completion_date');
            $profile->estimated_application_start_date = $request->input('estimated_application_start_date');
            $profile->service_type = $profile->service_type ?: ClientProfile::SERVICE_TYPE_REGULAR;
            $profile->save();

            $this->noticeService->syncOnboardingNotice($user, Auth::user());
        }

        return redirect()->route('admin.users')->with('success', 'User updated successfully.');
    }

    public function deleteUser(User $user)
    {
        // Prevent deletion of super admins
        if ($user->hasRole('super-admin') && !Auth::user()->hasRole('super-admin')) {
            return back()->with('error', 'Cannot delete super admin users.');
        }

        $user->delete();
        return redirect()->route('admin.users')->with('success', 'User deleted successfully.');
    }

    public function sendUserEmail(Request $request)
    {
        $data = $request->validate([
            'recipient_scope' => ['required', Rule::in(['all', 'individual'])],
            'email_user_id' => [
                Rule::requiredIf(fn () => $request->input('recipient_scope') === 'individual'),
                'nullable',
                'exists:users,id',
            ],
            'email_subject' => ['required', 'string', 'max:190'],
            'email_body' => ['required', 'string', 'max:12000'],
        ]);

        $sender = $request->user();
        $subject = trim((string) $data['email_subject']);
        $body = trim((string) $data['email_body']);

        if ($data['recipient_scope'] === 'all') {
            $recipientsQuery = User::query()
                ->whereNotNull('email')
                ->where('email', '!=', '');

            $recipientCount = (clone $recipientsQuery)->count();

            if ($recipientCount === 0) {
                return redirect()
                    ->route('admin.user-email.index', ['recipient_scope' => 'all'])
                    ->with('error', 'No users with valid email addresses were found.');
            }

            $sentCount = 0;
            $failedCount = 0;

            foreach ((clone $recipientsQuery)->orderBy('id')->cursor() as $recipient) {
                $sent = $this->communicationService->sendCustomEmail(
                    $recipient,
                    $subject,
                    $body,
                    false,
                    $sender?->email,
                    $sender?->name
                );

                if (!$sent) {
                    $failedCount++;
                    continue;
                }

                $sentCount++;

                $this->communicationService->notify(
                    $recipient,
                    'New message from admin',
                    'Admin sent you a new email message: ' . $subject,
                    \App\Models\Notification::TYPE_INFO,
                    ['category' => 'custom_email'],
                    \App\Models\Notification::PRIORITY_NORMAL,
                    null,
                    $this->resolveDashboardRouteForUser($recipient)
                );
            }

            if ($sentCount === 0) {
                return redirect()
                    ->route('admin.user-email.index', ['recipient_scope' => 'all'])
                    ->with('error', 'Custom email could not be sent to any users. Please check the mail configuration and try again.');
            }

            return redirect()
                ->route('admin.user-email.index', ['recipient_scope' => 'all'])
                ->with(
                    $failedCount > 0 ? 'warning' : 'success',
                    $failedCount > 0
                        ? "Custom email sent to {$sentCount} users. {$failedCount} deliveries failed."
                        : "Custom email sent to {$sentCount} users successfully."
                );
        }

        $recipient = User::query()->findOrFail((int) $data['email_user_id']);

        if (blank($recipient->email)) {
            return redirect()
                ->route('admin.user-email.index', [
                    'recipient_scope' => 'individual',
                    'user_id' => $recipient->id,
                ])
                ->with('error', 'Selected user does not have a valid email address.');
        }

        $sent = $this->communicationService->sendCustomEmail(
            $recipient,
            $subject,
            $body,
            false,
            $sender?->email,
            $sender?->name
        );

        if (!$sent) {
            return redirect()
                ->route('admin.user-email.index', [
                    'recipient_scope' => 'individual',
                    'user_id' => $recipient->id,
                ])
                ->with('error', 'Custom email could not be sent. Please check the mail configuration and try again.');
        }

        $this->communicationService->notify(
            $recipient,
            'New message from admin',
            'Admin sent you a new email message: ' . $subject,
            \App\Models\Notification::TYPE_INFO,
            ['category' => 'custom_email'],
            \App\Models\Notification::PRIORITY_NORMAL,
            null,
            $this->resolveDashboardRouteForUser($recipient)
        );

        return redirect()
            ->route('admin.user-email.index', [
                'recipient_scope' => 'individual',
                'user_id' => $recipient->id,
            ])
            ->with('success', 'Custom email sent successfully.');
    }

    public function searchEmailUsers(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $users = User::query()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->limit(50)
            ->get()
            ->map(fn (User $user) => [
                'value' => $user->id,
                'text' => $user->name . ' (' . $user->email . ')',
            ]);

        return response()->json($users->values());
    }

    public function assignments(Request $request)
    {
        return view('admin.assignments.index');
    }

    private function resolveDashboardRouteForUser(User $user): ?string
    {
        return match ((int) $user->role_id) {
            User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN => route('admin.dashboard'),
            User::ROLE_AGENT => route('agent.dashboard'),
            User::ROLE_CLIENT => route('client.dashboard'),
            default => null,
        };
    }

    public function createAssignment(Request $request)
    {
        $agentSearch = $request->get('agent_search', '');
        $clientSearch = $request->get('client_search', '');

        $agentsQuery = User::where('role_id', User::ROLE_AGENT)->orderBy('name');
        $clientsQuery = User::where('role_id', User::ROLE_CLIENT)->orderBy('name');

        if (!empty($agentSearch)) {
            $agentsQuery->where('name', 'like', '%' . $agentSearch . '%');
        }

        if (!empty($clientSearch)) {
            $clientsQuery->where('name', 'like', '%' . $clientSearch . '%');
        }

        $agents = $agentsQuery->get();
        $clients = $clientsQuery->get();

        return view('admin.assignments.create', compact('agents', 'clients', 'agentSearch', 'clientSearch'));
    }

    public function storeAssignment(Request $request)
    {
        $request->validate([
            'agent_id' => ['required', 'exists:users,id'],
            'client_id' => ['required', 'exists:users,id'],
            'service_end_date' => ['nullable', 'date', 'after:today'],
            'minimum_work_updates' => ['required', 'integer', 'min:1', 'max:50'],
            'apply_to' => ['required', 'string', 'max:2000'],
            'resume_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,csv,xlsx,xls', 'max:10240'],
            'onboarding_form_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,csv,xlsx,xls', 'max:10240'],
            'cover_letters.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,csv,xlsx,xls', 'max:10240'],
            'note_for_agent' => ['nullable', 'string', 'max:1000'],
        ]);

        // Validate that the selected users have the correct roles
        $agent = User::find($request->agent_id);
        $client = User::find($request->client_id);

        if (!$agent || $agent->role_id != 3) {
            return back()->withErrors(['agent_id' => 'Selected user is not an agent.'])->withInput();
        }

        if (!$client || $client->role_id != 4) {
            return back()->withErrors(['client_id' => 'Selected user is not a client.'])->withInput();
        }

        // Handle file uploads
        $resumeFile = null;
        $onboardingFile = null;
        $coverLetters = [];

        if ($request->hasFile('resume_file')) {
            $resumeFile = $request->file('resume_file')->store('assignments/resumes', 'public');
        }

        if ($request->hasFile('onboarding_form_file')) {
            $onboardingFile = $request->file('onboarding_form_file')->store('assignments/onboarding', 'public');
        }

        if ($request->hasFile('cover_letters')) {
            foreach ($request->file('cover_letters') as $file) {
                if ($file) {
                    $coverLetters[] = $file->store('assignments/cover-letters', 'public');
                }
            }
        }

        // Check if assignment already exists
        $existing = AgentClientAssignment::where('agent_id', $request->agent_id)
                                        ->where('client_id', $request->client_id)
                                        ->first();

        $assignmentData = [
            'is_active' => true,
            'service_end_date' => $request->service_end_date,
            'minimum_work_updates' => (int) $request->minimum_work_updates,
            'assigned_date' => now(),
            'apply_to' => $request->apply_to,
            'resume_file' => $resumeFile,
            'onboarding_form_file' => $onboardingFile,
            'cover_letters' => $coverLetters,
            'note_for_agent' => $request->note_for_agent,
        ];

        if ($existing) {
            $existing->update($assignmentData);
            $assignment = $existing->fresh();
        } else {
            $assignmentData['agent_id'] = $request->agent_id;
            $assignmentData['client_id'] = $request->client_id;
            $assignment = AgentClientAssignment::create($assignmentData);
        }

        $this->noticeService->syncClientServiceNotice($client, $assignment);

        return redirect()->route('admin.assignments')->with('success', 'Assignment created successfully.');
    }

    public function editAssignment(AgentClientAssignment $assignment)
    {
        $agents = User::where('role_id', User::ROLE_AGENT)->orderBy('name')->get();
        $clients = User::where('role_id', User::ROLE_CLIENT)->orderBy('name')->get();

        return view('admin.assignments.edit', compact('assignment', 'agents', 'clients'));
    }

    public function updateAssignment(Request $request, AgentClientAssignment $assignment)
    {
        $previousClient = $assignment->client;

        $request->validate([
            'agent_id' => ['required', 'exists:users,id'],
            'client_id' => ['required', 'exists:users,id'],
            'service_end_date' => ['nullable', 'date', 'after:today'],
            'minimum_work_updates' => ['required', 'integer', 'min:1', 'max:50'],
            'apply_to' => ['required', 'string'],
            'note_for_agent' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'resume_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,csv,xlsx,xls'],
            'onboarding_form_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,csv,xlsx,xls'],
            'cover_letters.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,csv,xlsx,xls'],
        ]);

        // Validate that the selected users have the correct roles
        $agent = User::find($request->agent_id);
        $client = User::find($request->client_id);

        if (!$agent || $agent->role_id != User::ROLE_AGENT) {
            return back()->withErrors(['agent_id' => 'Selected user is not an agent.'])->withInput();
        }

        if (!$client || $client->role_id != User::ROLE_CLIENT) {
            return back()->withErrors(['client_id' => 'Selected user is not a client.'])->withInput();
        }

        // Preserve existing file paths and merge new uploads
        $resumePath = $assignment->resume_file;
        $onboardingPath = $assignment->onboarding_form_file;
        $coverLetters = is_array($assignment->cover_letters)
            ? $assignment->cover_letters
            : (json_decode($assignment->cover_letters, true) ?? []);

        if ($request->hasFile('resume_file')) {
            $resumePath = $request->file('resume_file')->store('assignments/resumes', 'public');
        }

        if ($request->hasFile('onboarding_form_file')) {
            $onboardingPath = $request->file('onboarding_form_file')->store('assignments/onboarding', 'public');
        }

        if ($request->hasFile('cover_letters')) {
            foreach ($request->file('cover_letters') as $file) {
                if ($file) {
                    $coverLetters[] = $file->store('assignments/cover-letters', 'public');
                }
            }
        }

        $assignment->update([
            'agent_id' => $request->agent_id,
            'client_id' => $request->client_id,
            'service_end_date' => $request->service_end_date,
            'minimum_work_updates' => (int) $request->minimum_work_updates,
            'apply_to' => $request->apply_to,
            'note_for_agent' => $request->note_for_agent,
            'is_active' => $request->has('is_active'),
            'resume_file' => $resumePath,
            'onboarding_form_file' => $onboardingPath,
            'cover_letters' => $coverLetters,
        ]);

        $assignment->refresh();
        $this->noticeService->syncClientServiceNotice($client, $assignment);

        if ($previousClient && $previousClient->id !== $client->id) {
            $this->noticeService->syncClientServiceNotice($previousClient);
        }

        return redirect()->route('admin.assignments')->with('success', 'Assignment updated successfully.');
    }

    public function destroyAssignment(AgentClientAssignment $assignment)
    {
        // Get the agent and client before deleting
        $client = $assignment->client;

        $assignment->delete();

        if ($client) {
            $this->noticeService->syncClientServiceNotice($client);
        }


        return redirect()->route('admin.assignments')->with('success', 'Assignment removed successfully.');
    }

    public function workUpdates(Request $request)
    {
        return view('admin.work-updates.index');
    }

    public function downloadWorkUpdatesPdf(Request $request)
    {
        $workUpdates = WorkUpdateFilters::admin($request->only([
            'submission',
            'search',
            'client_id',
            'agent_id',
            'application_status',
            'status',
            'date_from',
            'date_to',
        ]))
            ->latest('applied_date')
            ->latest('created_at')
            ->get();

        $pdf = Pdf::loadView('admin.work-updates-pdf', compact('workUpdates'));

        return $pdf->download('admin-work-updates-' . now()->format('Y-m-d') . '.pdf');
    }

    public function downloadWorkUpdatesCsv(Request $request)
    {
        $workUpdates = WorkUpdateFilters::admin($request->only([
            'submission',
            'search',
            'client_id',
            'agent_id',
            'application_status',
            'status',
            'date_from',
            'date_to',
        ]))
            ->latest('applied_date')
            ->latest('created_at')
            ->get();

        $filename = 'admin-work-updates-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($workUpdates) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Applied Date',
                'Submitted At',
                'Agent',
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
                    $update->agent?->name,
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

    public function impersonate(User $user)
    {
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('super-admin')) {
            return back()->with('error', 'Unauthorized to impersonate users.');
        }

        // Store original user ID in session
        session(['impersonating' => Auth::id()]);

        // Login as the impersonated user
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Now impersonating ' . $user->name);
    }

    public function stopImpersonating()
    {
        if (!session()->has('impersonating')) {
            return redirect()->route('dashboard');
        }

        $originalUserId = session('impersonating');
        session()->forget('impersonating');

        Auth::loginUsingId($originalUserId);

        return redirect()->route('admin.dashboard')->with('success', 'Stopped impersonating user.');
    }

    // AJAX search methods for assignment creation
    public function searchAgents(Request $request)
    {
        $search = $request->get('search', '');

        $agents = User::where('role_id', User::ROLE_AGENT)
            ->when($search, function($query, $search) {
                return $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        // Format the response for the searchable-select component
        $formattedAgents = $agents->map(function($agent) {
            return [
                'value' => $agent->id,
                'text' => $agent->name . ' (' . $agent->email . ')'
            ];
        });

        return response()->json($formattedAgents);
    }

    public function searchClients(Request $request)
    {
        $search = $request->get('search', '');

        $clients = User::where('role_id', User::ROLE_CLIENT)
            ->when($search, function($query, $search) {
                return $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        // Format the response for the searchable-select component
        $formattedClients = $clients->map(function($client) {
            return [
                'value' => $client->id,
                'text' => $client->name . ' (' . $client->email . ')'
            ];
        });

        return response()->json($formattedClients);
    }


    public function updateMeetingLink(Request $request)
    {
        try {
            $request->validate([
                'meeting_link' => 'required|url'
            ]);

            // Get today's meeting
            $meeting = Meeting::where('date', today())->first();

            if (!$meeting) {
                return response()->json([
                    'success' => false,
                    'message' => 'No meeting found for today. Please generate a meeting first.'
                ]);
            }

            // Update the meeting link
            $meeting->update([
                'google_meet_link' => $request->meeting_link
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Meeting link updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating meeting link: ' . $e->getMessage()
            ]);
        }
    }

    public function meetingReports()
    {
        // Get today's meeting
        $todayMeeting = Meeting::where('date', today())->first();

        $attendanceStats = [
            'total_attended' => 0,
            'total_screen_shared' => 0,
            'average_duration' => 0,
            'attendance_rate' => 0
        ];

        $attendance = collect();

        if ($todayMeeting) {
            $attendance = Attendance::where('meeting_id', $todayMeeting->id)
                                   ->whereHas('agent') // Only get attendance records with valid agents
                                   ->with(['agent', 'screenSharingLogs'])
                                   ->get();

            $totalAgents = \App\Models\User::where('role_id', 2)->count(); // Role ID 2 is agent

            $attendanceStats = [
                'total_attended' => $attendance->where('status', 'joined')->count(),
                'total_screen_shared' => $attendance->where('screen_shared', true)->count(),
                'average_duration' => $attendance->avg('duration_minutes') ?: 0,
                'attendance_rate' => $totalAgents > 0 ? round(($attendance->where('status', 'joined')->count() / $totalAgents) * 100) : 0
            ];
        }

        // Get historical meetings
        $historicalMeetings = Meeting::with('attendances')
                                         ->where('date', '<', today())
                                         ->orderBy('date', 'desc')
                                         ->take(10)
                                         ->get();

        return view('admin.meeting-reports', compact(
            'todayMeeting',
            'attendanceStats',
            'attendance',
            'historicalMeetings'
        ));
    }

    public function meetingDetails(Meeting $meeting)
    {
        $meeting->load([
            'attendances' => function ($query) {
                $query->whereHas('agent');
            },
            'attendances.agent',
            'attendances.screenSharingLogs',
        ]);

        $attendanceStats = [
            'total_attended' => $meeting->attendances->where('status', 'joined')->count(),
            'total_screen_shared' => $meeting->attendances->where('screen_shared', true)->count(),
            'average_duration' => $meeting->attendances->avg('duration_minutes') ?: 0,
        ];

        return view('admin.meeting-details', compact('meeting', 'attendanceStats'));
    }

    public function meetingTest()
    {
        // Get today's meeting
        $todayMeeting = Meeting::where('date', today())->first();

        return view('admin.meeting-test', compact('todayMeeting'));
    }

    public function meetingDashboard()
    {
        try {
            // Get all agents first - use fully qualified name to avoid issues
            $totalAgents = \App\Models\User::where('role_id', 2)->count(); // Role ID 2 is agent

            // Initialize basic stats
            $attendanceStats = [
                'total_attended' => 0,
                'total_screen_shared' => 0,
                'average_duration' => 0,
                'attendance_rate' => 0,
                'total_agents' => $totalAgents,
                'active_agents' => 0,
                'total_work_hours' => 0
            ];

            $attendance = collect();
            $todayAttendance = collect();
            $todayMeeting = null;

            // Try to get today's meeting
            try {
                $todayMeeting = $this->googleMeetService->getTodaysMeeting();
            } catch (\Exception $e) {
                Log::warning('Could not get today\'s meeting: ' . $e->getMessage());
            }

            if ($todayMeeting) {
                try {
                    // Get today's attendance using the Attendance model
                    $todayAttendance = Attendance::where('meeting_id', $todayMeeting->id)
                                                ->with(['agent'])
                                                ->get();

                    // Get screen sharing data
                    $screenSharingData = Attendance::where('meeting_id', $todayMeeting->id)
                        ->where('screen_shared', true)
                        ->with('screenSharingLogs')
                        ->get();

                    $totalScreenShareTime = $screenSharingData->sum(function($attendance) {
                        return $attendance->screenSharingLogs->sum('duration_minutes');
                    });

                    $attendanceStats = [
                        'total_attended' => $todayAttendance->where('status', 'joined')->count(),
                        'total_screen_shared' => $screenSharingData->count(),
                        'total_screen_share_time' => $totalScreenShareTime,
                        'average_screen_share_time' => $screenSharingData->count() > 0 ? round($totalScreenShareTime / $screenSharingData->count()) : 0,
                        'average_duration' => $todayAttendance->avg('duration_minutes') ?: 0,
                        'attendance_rate' => $totalAgents > 0 ? round(($todayAttendance->where('status', 'joined')->count() / $totalAgents) * 100) : 0,
                        'total_agents' => $totalAgents,
                        'active_agents' => $todayAttendance->where('status', 'joined')->whereNull('leave_time')->count(),
                        'total_work_hours' => $todayAttendance->sum('duration_minutes') / 60
                    ];
                } catch (\Exception $e) {
                    Log::warning('Could not get attendance data: ' . $e->getMessage());
                }
            }

            // Get simplified reports
            $reports = [];
            $weeklyStats = [];
            $agentPerformance = collect();
            $meetingTrends = [];
            $historicalMeetings = collect();

            try {
                $reports = $this->getMeetingReports();
            } catch (\Exception $e) {
                Log::warning('Could not get meeting reports: ' . $e->getMessage());
            }

            try {
                $weeklyStats = $this->getWeeklyStats();
            } catch (\Exception $e) {
                Log::warning('Could not get weekly stats: ' . $e->getMessage());
            }

            try {
                $agentPerformance = $this->getAgentPerformance();
            } catch (\Exception $e) {
                Log::warning('Could not get agent performance: ' . $e->getMessage());
            }

            try {
                $meetingTrends = $this->getMeetingTrends();
            } catch (\Exception $e) {
                Log::warning('Could not get meeting trends: ' . $e->getMessage());
            }

            try {
                $historicalMeetings = Meeting::with(['attendances' => function($query) {
                                                $query->whereHas('agent');
                                            }, 'attendances.agent'])
                                           ->where('date', '<', today())
                                           ->orderBy('date', 'desc')
                                           ->take(30)
                                           ->get();
            } catch (\Exception $e) {
                Log::warning('Could not get historical meetings: ' . $e->getMessage());
            }

            return view('admin.meeting-dashboard', [
                'todayMeeting' => $todayMeeting,
                'attendanceStats' => $attendanceStats,
                'attendance' => $attendance,
                'todayAttendance' => $todayAttendance,
                'historicalMeetings' => $historicalMeetings,
                'reports' => $reports,
                'weeklyStats' => $weeklyStats,
                'agentPerformance' => $agentPerformance,
                'meetingTrends' => $meetingTrends,
                'totalAgents' => $totalAgents
            ]);

        } catch (\Exception $e) {
            // Log the error and return a simplified view
            Log::error('Meeting Dashboard Error: ' . $e->getMessage());

            // Return basic data without complex queries
            $totalAgents = 0;
            try {
                $totalAgents = \App\Models\User::where('role_id', 2)->count();
            } catch (\Exception $e) {
                Log::error('Could not count agents: ' . $e->getMessage());
            }

            $attendanceStats = [
                'total_attended' => 0,
                'total_screen_shared' => 0,
                'average_duration' => 0,
                'attendance_rate' => 0,
                'total_agents' => $totalAgents,
                'active_agents' => 0,
                'total_work_hours' => 0
            ];

            return view('admin.meeting-dashboard', [
                'todayMeeting' => null,
                'attendanceStats' => $attendanceStats,
                'attendance' => collect(),
                'todayAttendance' => collect(),
                'historicalMeetings' => collect(),
                'reports' => [],
                'weeklyStats' => [],
                'agentPerformance' => collect(),
                'meetingTrends' => [],
                'totalAgents' => $totalAgents
            ]);
        }
    }

    public function trackAgentJoin(Request $request)
    {
        try {
            $request->validate([
                'agent_id' => 'required|exists:users,id'
            ]);

            $agentId = $request->agent_id;
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
                'message' => 'Agent join tracked successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error tracking agent join: ' . $e->getMessage()
            ]);
        }
    }

    public function trackScreenShare(Request $request)
    {
        try {
            $request->validate([
                'agent_id' => 'required|exists:users,id',
                'action' => 'required|in:start,stop'
            ]);

            $agentId = $request->agent_id;
            $action = $request->action;
            $todayMeeting = Meeting::where('date', today())->first();

            if (!$todayMeeting) {
                return response()->json([
                    'success' => false,
                    'message' => 'No meeting found for today'
                ]);
            }

            $attendance = Attendance::where('meeting_id', $todayMeeting->id)
                                      ->where('agent_id', $agentId)
                                      ->first();

            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Agent attendance record not found'
                ]);
            }

            if ($action === 'start') {
                // Start screen sharing
                ScreenSharingLog::create([
                    'attendance_id' => $attendance->id,
                    'agent_id' => $agentId,
                    'started_at' => now(),
                    'is_active' => true
                ]);

                $attendance->update(['screen_shared' => true]);
            } else {
                // Stop screen sharing
                $screenLog = ScreenSharingLog::where('attendance_id', $attendance->id)
                                            ->where('agent_id', $agentId)
                                            ->where('is_active', true)
                                            ->first();

                if ($screenLog) {
                    $screenLog->update([
                        'ended_at' => now(),
                        'duration_minutes' => rounded_time_value($screenLog->started_at->diffInMinutes(now())),
                        'is_active' => false
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Screen sharing ' . $action . ' tracked successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error tracking screen sharing: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Manual meeting setup page.
     */
    public function meetingSetup()
    {
        $now = now();
        $today = $now->toDateString();

        $upcomingMeetings = Meeting::whereDate('date', '>=', $today)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $recentMeetings = Meeting::whereDate('date', '<', $today)
            ->orderByDesc('date')
            ->orderByDesc('start_time')
            ->limit(20)
            ->get();

        $upcomingMeetings = $upcomingMeetings->concat($recentMeetings)->take(50);

        return view('admin.meeting-setup', compact('upcomingMeetings'));
    }

    /**
     * Store or update a manual meeting entry.
     */
    public function storeMeetingSetup(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'meet_link' => ['required', 'url'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        $start = \Carbon\Carbon::parse($data['date'] . ' ' . $data['start_time']);
        $end = \Carbon\Carbon::parse($data['date'] . ' ' . $data['end_time']);

        $meeting = Meeting::updateOrCreate(
            ['date' => $data['date']],
            [
                'meet_link' => $data['meet_link'],
                'title' => $data['title'] ?? 'Team Meeting',
                'description' => $data['description'] ?? null,
                'start_time' => $start,
                'end_time' => $end,
                'is_active' => true,
            ]
        );

        return redirect()->route('admin.meeting-setup')
            ->with('success', "Meeting saved for {$meeting->date->format('M d, Y')}");
    }

    /**
     * Delete a scheduled meeting from the manual setup page.
     */
    public function destroyMeetingSetup(Meeting $meeting)
    {
        $meetingDate = $meeting->date?->format('M d, Y') ?? 'the selected date';

        $meeting->delete();

        return redirect()->route('admin.meeting-setup')
            ->with('success', "Meeting deleted for {$meetingDate}");
    }

    /**
     * Show generate meeting page
     */
    public function showGenerateMeeting()
    {
        // Get today's meeting if it exists
        $todayMeeting = $this->googleMeetService->getTodaysMeeting();

        // Get OAuth settings
        $oauthSettings = \App\Models\OAuthSetting::getGoogleMeetSettings();

        return view('admin.generate-meeting', compact('todayMeeting', 'oauthSettings'));
    }

    /**
     * Generate today's meeting
     */
    public function generateMeeting()
    {
        try {
            $meeting = $this->googleMeetService->createDailyMeet();

            return response()->json([
                'success' => true,
                'message' => 'Meeting generated successfully!',
                'meeting' => $meeting
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate meeting: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get meeting attendance report
     */
    public function getMeetingReport($meeting = null)
    {
        try {
            $meeting = $this->resolveMeeting($meeting) ?? $this->googleMeetService->getTodaysMeeting();

            if (!$meeting) {
                return response()->json([
                    'success' => false,
                    'message' => 'No meeting found'
                ]);
            }

            $stats = $this->googleMeetService->getMeetingStats($meeting->id);
            $attendanceReport = $this->googleMeetService->getAttendanceReport($meeting->id);

            return response()->json([
                'success' => true,
                'meeting' => $meeting,
                'stats' => $stats,
                'attendance_report' => $attendanceReport
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get meeting report: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Export meeting attendance report
     */
    public function exportMeetingReport($meeting = null)
    {
        try {
            $meeting = $this->resolveMeeting($meeting) ?? $this->googleMeetService->getTodaysMeeting();

            if (!$meeting) {
                return redirect()->back()->with('error', 'No meeting found');
            }

            $attendanceReport = $this->googleMeetService->getAttendanceReport($meeting->id);

            // Generate CSV
            $filename = 'meeting_attendance_' . $meeting->date->format('Y-m-d') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($attendanceReport) {
                $file = fopen('php://output', 'w');

                // CSV headers
                fputcsv($file, ['Agent Name', 'Agent Email', 'Join Time', 'Leave Time', 'Duration (Minutes)', 'Status']);

                // CSV data
                foreach ($attendanceReport as $attendance) {
                    fputcsv($file, [
                        $attendance['agent_name'],
                        $attendance['agent_email'],
                        $attendance['join_time'] ? $attendance['join_time']->format('Y-m-d H:i:s') : '',
                        $attendance['leave_time'] ? $attendance['leave_time']->format('Y-m-d H:i:s') : '',
                        $attendance['duration_minutes'] ?? 0,
                        $attendance['status']
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to export report: ' . $e->getMessage());
        }
    }

    public function exportMeetingReportForMeeting(Meeting $meeting)
    {
        return $this->exportMeetingReport($meeting);
    }

    private function resolveMeeting($meeting): ?Meeting
    {
        if ($meeting instanceof Meeting) {
            return $meeting;
        }

        if (blank($meeting)) {
            return null;
        }

        return (new Meeting())->resolveRouteBinding($meeting);
    }

    /**
     * Get comprehensive meeting reports
     */
    private function getMeetingReports()
    {
        try {
            $reports = [];

            // Daily attendance trend (last 7 days)
            $reports['daily_attendance'] = Meeting::with('attendances')
                ->where('date', '>=', now()->subDays(7))
                ->get()
                ->map(function ($meeting) {
                    return [
                        'date' => $meeting->date->format('M d'),
                        'attended' => $meeting->attendances->where('status', 'joined')->count(),
                        'total_agents' => User::where('role_id', 2)->count()
                    ];
                });

            // Weekly summary
            $reports['weekly_summary'] = [
                'total_meetings' => Meeting::where('date', '>=', now()->subDays(7))->count(),
                'total_attendance' => Attendance::where('created_at', '>=', now()->subDays(7))->count(),
                'average_attendance_rate' => $this->calculateAverageAttendanceRate(7),
                'total_work_hours' => Attendance::where('created_at', '>=', now()->subDays(7))->sum('duration_minutes') / 60
            ];

            // Top performing agents
            $reports['top_agents'] = \App\Models\User::where('role_id', 2)
                ->withCount(['attendances' => function ($query) {
                    $query->where('status', 'joined');
                }])
                ->with(['attendances' => function ($query) {
                    $query->where('status', 'joined');
                }])
                ->get()
                ->map(function ($agent) {
                    $totalMinutes = $agent->attendances->sum('duration_minutes');
                    return [
                        'name' => $agent->name,
                        'email' => $agent->email,
                        'attendance_count' => $agent->attendances_count,
                        'total_hours' => round($totalMinutes / 60, 2),
                        'average_duration' => $agent->attendances->avg('duration_minutes') ?: 0
                    ];
                })
                ->sortByDesc('attendance_count')
                ->take(5);

            return $reports;
        } catch (\Exception $e) {
            Log::error('getMeetingReports Error: ' . $e->getMessage());
            return [
                'daily_attendance' => collect(),
                'weekly_summary' => [
                    'total_meetings' => 0,
                    'total_attendance' => 0,
                    'average_attendance_rate' => 0,
                    'total_work_hours' => 0
                ],
                'top_agents' => collect()
            ];
        }
    }

    /**
     * Get weekly statistics
     */
    private function getWeeklyStats()
    {
        try {
            $startOfWeek = now()->startOfWeek();
            $endOfWeek = now()->endOfWeek();

            return [
                'meetings_this_week' => Meeting::whereBetween('date', [$startOfWeek, $endOfWeek])->count(),
                'attendance_this_week' => Attendance::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count(),
                'total_work_hours' => Attendance::whereBetween('created_at', [$startOfWeek, $endOfWeek])->sum('duration_minutes') / 60,
                'average_daily_attendance' => $this->calculateAverageAttendanceRate(7)
            ];
        } catch (\Exception $e) {
            Log::error('getWeeklyStats Error: ' . $e->getMessage());
            return [
                'meetings_this_week' => 0,
                'attendance_this_week' => 0,
                'total_work_hours' => 0,
                'average_daily_attendance' => 0
            ];
        }
    }

    /**
     * Get agent performance data
     */
    private function getAgentPerformance()
    {
        try {
            return \App\Models\User::where('role_id', 2)
                ->with(['attendances' => function ($query) {
                    $query->where('status', 'joined');
                }])
                ->get()
                ->map(function ($agent) {
                    $totalMinutes = $agent->attendances->sum('duration_minutes');
                    $attendanceCount = $agent->attendances->count();

                    return [
                        'id' => $agent->id,
                        'name' => $agent->name,
                        'email' => $agent->email,
                        'attendance_count' => $attendanceCount,
                        'total_hours' => round($totalMinutes / 60, 2),
                        'average_duration' => $agent->attendances->avg('duration_minutes') ?: 0,
                        'last_attended' => $agent->attendances->max('join_time'),
                        'attendance_rate' => $this->calculateAgentAttendanceRate($agent->id)
                    ];
                });
        } catch (\Exception $e) {
            Log::error('getAgentPerformance Error: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get meeting trends
     */
    private function getMeetingTrends()
    {
        try {
            $trends = [];

            // Last 30 days attendance trend
            for ($i = 29; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $meeting = Meeting::whereDate('date', $date)->first();

                if ($meeting) {
                    $attended = $meeting->attendances->where('status', 'joined')->count();
                    $totalAgents = \App\Models\User::where('role_id', 2)->count();

                    $trends[] = [
                        'date' => $date->format('M d'),
                        'attended' => $attended,
                        'rate' => $totalAgents > 0 ? round(($attended / $totalAgents) * 100) : 0
                    ];
                }
            }

            return $trends;
        } catch (\Exception $e) {
            Log::error('getMeetingTrends Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate average attendance rate for given days
     */
    private function calculateAverageAttendanceRate($days)
    {
        $meetings = Meeting::where('date', '>=', now()->subDays($days))->get();
        $totalAgents = \App\Models\User::where('role_id', 2)->count();

        if ($meetings->isEmpty() || $totalAgents == 0) {
            return 0;
        }

        $totalAttendance = 0;
        $totalPossible = $meetings->count() * $totalAgents;

        foreach ($meetings as $meeting) {
            $totalAttendance += $meeting->attendances->where('status', 'joined')->count();
        }

        return $totalPossible > 0 ? round(($totalAttendance / $totalPossible) * 100) : 0;
    }

    /**
     * Calculate agent attendance rate
     */
    private function calculateAgentAttendanceRate($agentId)
    {
        $totalMeetings = Meeting::where('date', '>=', now()->subDays(30))->count();
        $attendedMeetings = Attendance::where('agent_id', $agentId)
            ->where('status', 'joined')
            ->whereHas('meeting', function ($query) {
                $query->where('date', '>=', now()->subDays(30));
            })
            ->count();

        return $totalMeetings > 0 ? round(($attendedMeetings / $totalMeetings) * 100) : 0;
    }
}
