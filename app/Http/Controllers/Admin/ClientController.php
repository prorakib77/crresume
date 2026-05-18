<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AgentClientAssignment;
use App\Models\ClientProfile;
use App\Models\ClientSubmission;
use App\Services\NoticeService;
use App\Services\UserCommunicationService;
use Illuminate\Support\Facades\Storage;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function __construct(
        protected NoticeService $noticeService,
        protected UserCommunicationService $communicationService,
    ) {
    }

    /**
     * Display a listing of clients
     */
    public function index(Request $request)
    {
        return view('admin.clients.index');
    }

    /**
     * Show client details
     */
    public function show(User $client)
    {
        $this->ensureClient($client);

        $assignment = AgentClientAssignment::where('client_id', $client->id)
            ->with('agent')
            ->newestFirst()
            ->first();

        $submissions = ClientSubmission::where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $client->loadMissing('clientProfile');

        return view('admin.clients.show', compact('client', 'assignment', 'submissions'));
    }

    /**
     * Request onboarding details again from client.
     */
    public function requestOnboarding(User $client)
    {
        $this->ensureClient($client);

        $profile = $client->clientProfile;
        if (!$profile) {
            $profile = $client->clientProfile()->create([
                'status' => 0,
                'onboarding_status' => ClientProfile::ONBOARDING_STATUS_PENDING,
                'service_type' => ClientProfile::SERVICE_TYPE_REGULAR,
            ]);
        }

        $profile->update([
            'onboarding_visible' => true,
            'onboarding_status' => ClientProfile::ONBOARDING_STATUS_REQUESTED_AGAIN,
            'onboarding_requested_at' => now(),
        ]);

        $this->noticeService->syncOnboardingNotice($client, request()->user());
        $this->communicationService->notify(
            $client,
            'Onboarding requested again',
            'Admin requested your onboarding details again. Please review and submit the onboarding form.',
            \App\Models\Notification::TYPE_WARNING,
            ['category' => 'onboarding'],
            \App\Models\Notification::PRIORITY_HIGH,
            $profile,
            route('client.onboarding.create')
        );
        $this->communicationService->sendStructuredEmail(
            $client,
            'Onboarding Update Required',
            'Your onboarding information needs attention.',
            [
                'An admin requested your onboarding details again.',
                'Please review your information, upload the required files, and submit the onboarding form from your dashboard.',
            ],
            route('client.onboarding.create'),
            'Open Onboarding'
        );

        return back()->with('success', 'Onboarding details requested from client.');
    }

    public function updateDetails(Request $request, User $client)
    {
        $this->ensureClient($client);

        $data = $request->validate([
            'onboarding_status' => ['required', Rule::in([
                ClientProfile::ONBOARDING_STATUS_PENDING,
                ClientProfile::ONBOARDING_STATUS_COMPLETED,
                ClientProfile::ONBOARDING_STATUS_REQUESTED_AGAIN,
            ])],
            'estimated_resume_completion_date' => ['nullable', 'date'],
            'estimated_cover_letter_completion_date' => ['nullable', 'date'],
            'estimated_application_start_date' => ['nullable', 'date'],
            'service_start_date' => ['nullable', 'date'],
            'service_package' => ['nullable', Rule::in(['2-weeks', '3-weeks', '4-weeks', '5-weeks', '6-weeks'])],
            'service_type' => ['required', Rule::in([
                ClientProfile::SERVICE_TYPE_REGULAR,
                ClientProfile::SERVICE_TYPE_VIP,
            ])],
        ]);

        $profile = $client->clientProfile ?: $client->clientProfile()->create([
            'status' => 0,
            'onboarding_status' => ClientProfile::ONBOARDING_STATUS_PENDING,
            'service_type' => ClientProfile::SERVICE_TYPE_REGULAR,
            'onboarding_visible' => true,
        ]);

        $originalValues = [
            'onboarding_status' => $profile->resolvedOnboardingStatus(),
            'estimated_resume_completion_date' => optional($profile->estimated_resume_completion_date)->toDateString(),
            'estimated_cover_letter_completion_date' => optional($profile->estimated_cover_letter_completion_date)->toDateString(),
            'estimated_application_start_date' => optional($profile->estimated_application_start_date)->toDateString(),
            'service_start_date' => optional($profile->service_start_date)->toDateString(),
            'service_package' => $profile->service_package,
            'service_type' => $profile->service_type ?: ClientProfile::SERVICE_TYPE_REGULAR,
        ];

        $onboardingStatus = $data['onboarding_status'];
        $requiresOnboarding = $onboardingStatus !== ClientProfile::ONBOARDING_STATUS_COMPLETED;

        $profile->fill([
            'onboarding_status' => $onboardingStatus,
            'onboarding_visible' => $requiresOnboarding,
            'onboarding_requested_at' => $requiresOnboarding ? now() : null,
            'onboarding_submitted_at' => $onboardingStatus === ClientProfile::ONBOARDING_STATUS_COMPLETED
                ? ($profile->onboarding_submitted_at ?? now())
                : $profile->onboarding_submitted_at,
            'estimated_resume_completion_date' => $data['estimated_resume_completion_date'] ?? null,
            'estimated_cover_letter_completion_date' => $data['estimated_cover_letter_completion_date'] ?? null,
            'estimated_application_start_date' => $data['estimated_application_start_date'] ?? null,
            'service_start_date' => $data['service_start_date'] ?? null,
            'service_package' => $data['service_package'] ?: null,
            'service_type' => $data['service_type'],
        ]);
        $profile->save();

        $this->noticeService->syncOnboardingNotice($client, $request->user());

        $updatedValues = [
            'onboarding_status' => $profile->resolvedOnboardingStatus(),
            'estimated_resume_completion_date' => optional($profile->estimated_resume_completion_date)->toDateString(),
            'estimated_cover_letter_completion_date' => optional($profile->estimated_cover_letter_completion_date)->toDateString(),
            'estimated_application_start_date' => optional($profile->estimated_application_start_date)->toDateString(),
            'service_start_date' => optional($profile->service_start_date)->toDateString(),
            'service_package' => $profile->service_package,
            'service_type' => $profile->service_type,
        ];

        $changeLines = [];

        if ($originalValues['onboarding_status'] !== $updatedValues['onboarding_status']) {
            $changeLines[] = 'Onboarding status: ' . $profile->onboardingStatusLabel();
        }

        if ($originalValues['service_start_date'] !== $updatedValues['service_start_date']) {
            $changeLines[] = 'Service start date: ' . ($profile->service_start_date?->format('M j, Y') ?? 'Not set');
        }

        if ($originalValues['estimated_resume_completion_date'] !== $updatedValues['estimated_resume_completion_date']) {
            $changeLines[] = 'Resume completion date: ' . ($profile->estimated_resume_completion_date?->format('M j, Y') ?? 'Not set');
        }

        if ($originalValues['estimated_cover_letter_completion_date'] !== $updatedValues['estimated_cover_letter_completion_date']) {
            $changeLines[] = 'Cover letter completion date: ' . ($profile->estimated_cover_letter_completion_date?->format('M j, Y') ?? 'Not set');
        }

        if ($originalValues['estimated_application_start_date'] !== $updatedValues['estimated_application_start_date']) {
            $changeLines[] = 'Application start date: ' . ($profile->estimated_application_start_date?->format('M j, Y') ?? 'Not set');
        }

        if ($originalValues['service_package'] !== $updatedValues['service_package']) {
            $changeLines[] = 'Service package: ' . ($profile->service_package ? str_replace('-', ' ', $profile->service_package) : 'Not set');
        }

        if ($originalValues['service_type'] !== $updatedValues['service_type']) {
            $changeLines[] = 'Service type: ' . $profile->serviceTypeLabel();
        }

        if ($changeLines === []) {
            $changeLines[] = 'Your client account settings were reviewed by admin.';
        }

        $actionUrl = $profile->shouldShowOnboardingForm()
            ? route('client.onboarding.create')
            : route('client.dashboard');
        $actionLabel = $profile->shouldShowOnboardingForm() ? 'Open Onboarding' : 'Open Dashboard';

        $this->communicationService->notify(
            $client,
            'Client profile updated',
            'Admin updated your onboarding or service details.',
            \App\Models\Notification::TYPE_INFO,
            ['category' => 'client_profile'],
            \App\Models\Notification::PRIORITY_HIGH,
            $profile,
            $actionUrl
        );
        $this->communicationService->sendStructuredEmail(
            $client,
            'Your Client Settings Were Updated',
            'Admin updated details on your account.',
            $changeLines,
            $actionUrl,
            $actionLabel
        );

        return back()->with('success', 'Client details updated successfully.');
    }

    public function sendCustomEmail(Request $request, User $client)
    {
        $this->ensureClient($client);

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:190'],
            'body' => ['required', 'string', 'max:12000'],
        ]);

        $sender = $request->user();

        $this->communicationService->sendCustomEmail(
            $client,
            trim((string) $data['subject']),
            trim((string) $data['body']),
            false,
            $sender?->email,
            $sender?->name
        );
        $this->communicationService->notify(
            $client,
            'New message from admin',
            'Admin sent you a new email message: ' . trim((string) $data['subject']),
            \App\Models\Notification::TYPE_INFO,
            ['category' => 'custom_email'],
            \App\Models\Notification::PRIORITY_NORMAL,
            null,
            route('client.dashboard')
        );

        return back()->with('success', 'Custom email sent successfully.');
    }

    /**
     * Download onboarding text as PDF.
     */
    public function downloadOnboardingText(User $client)
    {
        $this->ensureClient($client);

        if (!$client->clientProfile?->onboarding_text) {
            abort(404);
        }

        $pdf = PDF::loadView('admin.clients.onboarding-pdf', [
            'client' => $client,
            'profile' => $client->clientProfile,
        ]);

        return $pdf->download("onboarding-{$client->id}.pdf");
    }

    /**
     * Download onboarding files.
     */
    public function downloadOnboardingFile(User $client, string $type)
    {
        $this->ensureClient($client);

        $profile = $client->clientProfile;
        if (!$profile) {
            abort(404);
        }

        $path = match ($type) {
            'resume' => $profile->onboarding_resume_file,
            'form' => $profile->onboarding_form_file,
            default => null,
        };

        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->download($path);
    }

    protected function ensureClient(User $client): void
    {
        if (!$client->hasRole('client')) {
            abort(404);
        }
    }
}
