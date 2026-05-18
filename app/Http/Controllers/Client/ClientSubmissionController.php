<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\ClientSubmission;
use App\Models\AgentClientAssignment;
use App\Services\NotificationService;
use App\Services\EmailTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientSubmissionController extends Controller
{
    protected $notificationService;
    protected EmailTemplateService $emailTemplateService;

    public function __construct(NotificationService $notificationService, EmailTemplateService $emailTemplateService)
    {
        $this->notificationService = $notificationService;
        $this->emailTemplateService = $emailTemplateService;
    }

    /**
     * Display the client submission form
     */
    public function index()
    {
        $user = Auth::user();

        // Get client's submissions
        $submissions = ClientSubmission::with(['agent'])
            ->where('client_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('client.submissions.index', compact('submissions'));
    }

    /**
     * Show the form for creating a new submission
     */
    public function create()
    {
        $user = Auth::user();

        // Check if client has an assigned agent
        $assignment = AgentClientAssignment::where('client_id', $user->id)
            ->where('is_active', true)
            ->with('agent')
            ->first();

        if (!$assignment) {
            return redirect()->route('client.dashboard')
                ->with('error', 'Your service has not been started. Please contact support.');
        }

        return view('client.submissions.create', compact('assignment'));
    }

    /**
     * Store a newly created submission
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validate the request
        $request->validate([
            'company_name' => 'required|string|max:255',
            'otp' => 'required|string|max:50',
        ], [
            'company_name.required' => 'Company name is required.',
            'otp.required' => 'OTP is required.',
        ]);

        // Get client's assigned agent
        $assignment = AgentClientAssignment::where('client_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (!$assignment) {
            return back()->with('error', 'Your service has not been started. Please contact support.')
                        ->withInput();
        }

        try {
            DB::transaction(function () use ($request, $user, $assignment) {
                // Create the submission
                $submission = ClientSubmission::create([
                    'client_id' => $user->id,
                    'agent_id' => $assignment->agent_id,
                    'company_name' => $request->company_name,
                    'otp' => $request->otp,
                    'status' => ClientSubmission::STATUS_PENDING,
                ]);

                // Send email notification to agent
                $this->sendSubmissionNotificationToAgent($submission);

                // Send in-app notification to agent
                $agent = $assignment->agent;
                $this->notificationService->notify(
                    $agent,
                    'New Client Submission',
                    "Client {$user->name} has submitted a new company and OTP: {$request->company_name}",
                    \App\Models\Notification::TYPE_INFO,
                    ['submission_id' => $submission->id, 'client_id' => $user->id],
                    \App\Models\Notification::PRIORITY_HIGH,
                    null,
                    route('agent.submissions.show', $submission)
                );

                Log::info('Client submission created successfully', [
                    'submission_id' => $submission->id,
                    'client_id' => $user->id,
                    'agent_id' => $assignment->agent_id,
                    'company_name' => $request->company_name,
                ]);
            });

            return redirect()->route('client.submissions.index')
                            ->with('success', 'Your submission has been sent to your assigned agent successfully!');

        } catch (\Exception $e) {
            Log::error('Client submission failed', [
                'client_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Failed to submit your request. Please try again.')
                        ->withInput();
        }
    }

    /**
     * Display the specified submission
     */
    public function show(ClientSubmission $submission)
    {
        $user = Auth::user();

        // Ensure the client can only view their own submissions
        if ($submission->client_id !== $user->id) {
            abort(403, 'Unauthorized access to submission.');
        }

        $submission->load(['agent']);

        return view('client.submissions.show', compact('submission'));
    }

    /**
     * Send email notification to agent about new submission
     */
    private function sendSubmissionNotificationToAgent(ClientSubmission $submission)
    {
        try {
            $agent = $submission->agent;
            $client = $submission->client;

            $emailContent = $this->prepareSubmissionEmail($submission);

            $this->emailTemplateService->sendTemplate(
                EmailTemplate::KEY_CLIENT_SUBMISSION_NOTIFICATION,
                (string) $agent->email,
                (string) $agent->name,
                [
                    'agent_name' => $agent->name,
                    'client_name' => $client->name,
                    'client_email' => $client->email,
                    'company_name' => $submission->company_name,
                    'otp_code' => $submission->otp,
                    'status' => strtoupper((string) $submission->status),
                    'submitted_at' => optional($submission->created_at)->format('M j, Y \\a\\t g:i A'),
                    'submissions_url' => route('agent.submissions.show', $submission),
                ],
                [
                    'subject_fallback' => "New Client Submission - {$submission->company_name}",
                    'body_fallback' => $emailContent,
                ]
            );

            Log::info('Client submission email sent to agent', [
                'submission_id' => $submission->id,
                'agent_email' => $agent->email,
                'client_name' => $client->name,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send submission email to agent', [
                'submission_id' => $submission->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Prepare HTML email content for submission notification
     */
    private function prepareSubmissionEmail(ClientSubmission $submission)
    {
        $client = $submission->client;
        $agent = $submission->agent;

        return "
        <div style='font-family: Inter, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif; max-width: 600px; margin: 0 auto; background: #E5E7EB; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #111827 0%, #374151 100%); color: white; padding: 40px 30px; text-align: center; border-radius: 12px 12px 0 0; position: relative;'>
                <div style='position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url(\"data:image/svg+xml,<svg xmlns=\\\"http://www.w3.org/2000/svg\\\" viewBox=\\\"0 0 100 100\\\"><defs><pattern id=\\\"grain\\\" width=\\\"100\\\" height=\\\"100\\\" patternUnits=\\\"userSpaceOnUse\\\"><circle cx=\\\"50\\\" cy=\\\"50\\\" r=\\\"1\\\" fill=\\\"%23ffffff\\\" opacity=\\\"0.05\\\"/></pattern></defs><rect width=\\\"100\\\" height=\\\"100\\\" fill=\\\"url(%23grain)\\\"/></svg>\"); opacity: 0.1; border-radius: 12px 12px 0 0;'></div>
                <h1 style='margin: 0; font-size: 28px; font-weight: 700; font-family: Playfair Display, serif; position: relative; z-index: 1;'>📝 New Client Submission</h1>
                <p style='margin: 8px 0 0 0; opacity: 0.9; font-size: 16px; position: relative; z-index: 1;'>" . now()->format('l, F j, Y \a\t g:i A') . "</p>
                <p style='margin: 8px 0 0 0; opacity: 0.9; font-size: 14px; position: relative; z-index: 1;'><strong>Atswfhresumes</strong> - Professional Resume Services</p>
            </div>

            <div style='padding: 40px 30px; background: white;'>
                <p style='margin: 0 0 20px 0; color: #111827; font-size: 18px; font-weight: 500;'>
                    Hello <strong>{$agent->name}</strong>,
                </p>
                <div style='background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); padding: 25px; border-radius: 12px; margin: 25px 0; border-left: 4px solid #dcb9bd;'>
                    <p style='margin: 0; color: #111827;'>
                        Your assigned client <strong>{$client->name}</strong> has submitted a new company and OTP.
                    </p>
                </div>
            </div>

            <div style='padding: 0 30px 30px 30px; background: white;'>
                <div style='border: 1px solid #E5E7EB; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); background: white;'>
                    <div style='background: linear-gradient(135deg, #111827 0%, #374151 100%); color: white; padding: 20px; font-weight: 600;'>
                        <h3 style='margin: 0; font-size: 18px; font-weight: 600;'>📋 Submission Details</h3>
                    </div>
                    <div style='padding: 25px;'>
                        <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;'>
                            <div style='background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #E5E7EB;'>
                                <strong style='color: #374151; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;'>👤 Client Name</strong><br>
                                <span style='color: #111827; font-weight: 500; font-size: 16px;'>{$client->name}</span>
                            </div>
                            <div style='background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #E5E7EB;'>
                                <strong style='color: #374151; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;'>📧 Client Email</strong><br>
                                <span style='color: #111827; font-weight: 500; font-size: 16px;'>{$client->email}</span>
                            </div>
                        </div>
                        <div style='background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #E5E7EB; margin-bottom: 20px;'>
                            <strong style='color: #374151; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;'>🏢 Company Name</strong><br>
                            <span style='color: #111827; font-weight: 600; font-size: 18px;'>{$submission->company_name}</span>
                        </div>
                        <div style='background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #E5E7EB; margin-bottom: 20px;'>
                            <strong style='color: #374151; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;'>🔐 OTP Code</strong><br>
                            <span style='color: #111827; font-size: 24px; font-weight: bold; background: linear-gradient(135deg, #111827 0%, #374151 100%); color: white; padding: 12px 20px; border-radius: 8px; display: inline-block; margin-top: 8px;'>{$submission->otp}</span>
                        </div>
                        <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px;'>
                            <div style='background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #E5E7EB;'>
                                <strong style='color: #374151; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;'>📅 Submission Date</strong><br>
                                <span style='color: #111827; font-weight: 500;'>{$submission->created_at->format('M j, Y \a\t g:i A')}</span>
                            </div>
                            <div style='background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #E5E7EB;'>
                                <strong style='color: #374151; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;'>📊 Status</strong><br>
                                <span style='background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;'>PENDING</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div style='background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); padding: 30px; text-align: center; border-top: 1px solid #E5E7EB; border-radius: 0 0 12px 12px;'>
                <p style='margin: 0 0 10px 0; color: #374151; font-size: 14px; font-weight: 500;'>
                    Please log in to your agent dashboard to process this submission.
                </p>
                <p style='margin: 0 0 10px 0; color: #111827; font-size: 14px; font-weight: 600;'>
                    <strong>Atswfhresumes</strong> - Professional Resume Services
                </p>
                <p style='margin: 0; color: #6b7280; font-size: 12px;'>
                    This is an automated message. Please do not reply to this email.
                </p>
            </div>
        </div>";
    }
}
