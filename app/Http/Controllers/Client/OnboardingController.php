<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\CustomizationSetting;
use App\Services\NoticeService;
use App\Services\UserCommunicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    public function __construct(
        protected NoticeService $noticeService,
        protected UserCommunicationService $communicationService,
    ) {
    }

    public function create()
    {
        $user = Auth::user();
        $profile = $user->clientProfile;

        if (!$profile) {
            $profile = ClientProfile::create([
                'user_id' => $user->id,
                'status' => 0,
                'onboarding_visible' => true,
                'onboarding_status' => ClientProfile::ONBOARDING_STATUS_PENDING,
                'service_type' => ClientProfile::SERVICE_TYPE_REGULAR,
            ]);
        }

        if (!$profile->shouldShowOnboardingForm()) {
            return redirect()->route('client.dashboard')->with('info', 'Onboarding details already submitted.');
        }

        $instructions = CustomizationSetting::getValue('onboarding_instructions');
        $guideFile = CustomizationSetting::getValue('onboarding_guide_file');

        return view('client.onboarding', compact('profile', 'instructions', 'guideFile'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $profile = $user->clientProfile;

        if (!$profile || !$profile->shouldShowOnboardingForm()) {
            return redirect()->route('client.dashboard')->with('info', 'Onboarding details are not requested.');
        }

        $data = $request->validate([
            'onboarding_resume_file' => 'required|file|mimes:pdf,doc,docx,csv,xlsx,xls|max:10240',
            'client_signature_camera' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'acknowledge_policies' => 'accepted',

            'full_name' => 'required|string|max:255',
            'dob' => 'nullable|string|max:255',
            'ssn' => 'nullable|string|max:255',
            'address_street' => 'required|string|max:255',
            'address_city' => 'required|string|max:255',
            'address_state' => 'required|string|max:255',
            'address_zip' => 'required|string|max:20',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'sex' => 'required|string|max:100',
            'ethnicity' => 'nullable|string|max:255',
            'is_felon' => 'required|string|in:yes,no',
            'accommodations' => 'nullable|string|max:2000',

            'target_roles' => 'required|string',
            'industries' => 'required|string',
            'certifications' => 'nullable|string',
            'licenses' => 'nullable|string',
            'salary_type' => 'required|string|in:hourly,yearly',
            'salary_amount' => 'required|string|max:255',
            'extra_strengths' => 'nullable|string',
            'extra_experience' => 'nullable|string',
            'soft_skills' => 'nullable|string',
            'tools' => 'nullable|string',
            'languages' => 'nullable|string|max:255',

            'education.institution.*' => 'required|string|max:255',
            'education.degree.*' => 'required|string|max:255',
            'education.enrollment.*' => 'nullable|string|max:255',
            'education.graduation.*' => 'nullable|string|max:255',

            'service_package' => 'required|string|in:2-weeks,3-weeks,4-weeks,5-weeks,6-weeks',

            'onboarding_note' => 'nullable|string|max:2000',
        ]);

        $resumePath = $profile->onboarding_resume_file;

        if ($request->hasFile('onboarding_resume_file')) {
            $resumePath = $request->file('onboarding_resume_file')->store('client-onboarding/resumes', 'public');
        }

        $signaturePath = $profile->client_signature_path;

        if ($request->hasFile('client_signature_camera')) {
            $signaturePath = $request->file('client_signature_camera')->store('client-onboarding/signatures', 'public');
        }

        // Build a consolidated onboarding text payload
        $educationEntries = collect($request->input('education.institution', []))
            ->map(function ($value, $idx) use ($request) {
                $degree = $request->input("education.degree.$idx");
                $enroll = $request->input("education.enrollment.$idx");
                $grad = $request->input("education.graduation.$idx");
                return trim("
Institution: {$value}
Degree: {$degree}
Enrollment: " . ($enroll ?: 'N/A') . "
Graduation: " . ($grad ?: 'N/A') . "
");
            })
            ->filter();

        $onboardingText = implode("\n", [
            "FORM – TOP TEXT CONTENT",
            "To get started and build a resume + cover letter that truly stands out, and to begin applying to remote positions on your behalf, I’ll need a few important details from you.",
            "",
            "SECTION: Personal Information",
            "Full Name: {$data['full_name']}",
            "Date of Birth: " . ($data['dob'] ?? 'N/A'),
            "Social Security Number: " . ($data['ssn'] ?? 'N/A'),
            "Current Address: {$data['address_street']}, {$data['address_city']}, {$data['address_state']} {$data['address_zip']}",
            "Best Contact Number: {$data['phone']}",
            "Email Address: {$data['email']}",
            "Sex: {$data['sex']}",
            "Ethnicity: " . ($data['ethnicity'] ?? 'N/A'),
            "Are you a felon?: " . strtoupper($data['is_felon']),
            "Accommodations: " . ($data['accommodations'] ?? 'N/A'),
            "",
            "SECTION: Career Details",
            "Remote roles: {$data['target_roles']}",
            "Industries: {$data['industries']}",
            "Certifications: " . ($data['certifications'] ?? 'N/A'),
            "Licenses: " . ($data['licenses'] ?? 'N/A'),
            "Desired pay: {$data['salary_type']} - {$data['salary_amount']}",
            "Strengthening details: " . ($data['extra_strengths'] ?? 'N/A'),
            "Additional experience: " . ($data['extra_experience'] ?? 'N/A'),
            "Soft skills: " . ($data['soft_skills'] ?? 'N/A'),
            "Tools/Software: " . ($data['tools'] ?? 'N/A'),
            "Languages: " . ($data['languages'] ?? 'N/A'),
            "",
            "SECTION: Education History",
            $educationEntries->implode("\n"),
            "",
            "SECTION: Service",
            "Selected Package: {$data['service_package']}",
            "",
            "Note to client: Make sure to attach your current resume. If you don’t have one please email caliwfh@outlook.com before you submit the form.",
            "Thank you! We’ll get started right away."
        ]);

        $profile->update([
            'onboarding_resume_file' => $resumePath,
            'onboarding_form_file' => null,
            'onboarding_text' => $onboardingText,
            'onboarding_note' => $data['onboarding_note'] ?? null,
            'onboarding_submitted_at' => now(),
            'onboarding_requested_at' => null,
            'onboarding_visible' => false,
            'onboarding_status' => ClientProfile::ONBOARDING_STATUS_COMPLETED,
            'phone' => $data['phone'],
            'address' => trim("{$data['address_street']}, {$data['address_city']}, {$data['address_state']} {$data['address_zip']}"),
            'service_package' => $data['service_package'],
            'client_signature_path' => $signaturePath,
            'policy_acknowledged_at' => now(),
        ]);

        $this->noticeService->syncOnboardingNotice($user);
        $this->communicationService->notify(
            $user,
            'Onboarding submitted',
            'Your onboarding form was submitted successfully.',
            \App\Models\Notification::TYPE_SUCCESS,
            ['category' => 'onboarding'],
            \App\Models\Notification::PRIORITY_HIGH,
            $profile,
            route('client.dashboard')
        );
        $this->communicationService->sendOnboardingConfirmationEmail($user);

        return redirect()->route('client.dashboard')->with('success', 'Onboarding details submitted successfully.');
    }
}
