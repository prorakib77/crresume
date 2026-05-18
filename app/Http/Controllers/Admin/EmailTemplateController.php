<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomizationSetting;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\EmailTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class EmailTemplateController extends Controller
{
    public function __construct(
        protected EmailTemplateService $emailTemplateService
    ) {
    }

    public function index(): View
    {
        EmailTemplate::syncDefaults();

        $templates = EmailTemplate::query()
            ->orderBy('sort_order')
            ->orderBy('template_name')
            ->paginate(20);

        $recipientSuggestions = User::query()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'name', 'email', 'role_id']);

        return view('admin.email-templates.index', [
            'templates' => $templates,
            'recipientSuggestions' => $recipientSuggestions,
        ]);
    }

    public function edit(EmailTemplate $emailTemplate): View
    {
        EmailTemplate::syncDefaults();
        CustomizationSetting::syncDefaults();
        $settings = CustomizationSetting::getAllActive();

        return view('admin.email-templates.edit', [
            'template' => $emailTemplate,
            'accentColor' => (string) CustomizationSetting::getValue('accent_color', '#C8A45D'),
            'emailHeaderLogoUrl' => (string) CustomizationSetting::getValue('email_header_logo_url', ''),
            'emailHeaderBgImageUrl' => (string) CustomizationSetting::getValue('email_header_bg_image_url', ''),
            'defaultFromName' => (string) config('mail.from.name', 'Atswfhresumes'),
            'defaultFromEmail' => (string) config('mail.from.address', ''),
            'emailHeaderLogoUploadedUrl' => $this->settingAssetUrl($settings->get('email_header_logo')?->setting_value, $settings->get('email_header_logo')?->updated_at?->timestamp),
            'emailHeaderBgUploadedUrl' => $this->settingAssetUrl($settings->get('email_header_bg_image')?->setting_value, $settings->get('email_header_bg_image')?->updated_at?->timestamp),
        ]);
    }

    public function update(Request $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        $validated = $request->validate([
            'subject_template' => ['required', 'string', 'max:1000'],
            'body_template' => ['required', 'string', 'max:200000'],
            'footer_note' => ['nullable', 'string', 'max:1000'],
            'content_note' => ['nullable', 'string', 'max:2000'],
            'from_name' => ['nullable', 'string', 'max:190'],
            'from_email' => ['nullable', 'email:rfc', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'accent_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'email_header_logo_url' => ['nullable', 'string', 'max:255'],
            'email_header_bg_image_url' => ['nullable', 'string', 'max:255'],
            'email_header_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:4096'],
            'email_header_bg_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:6144'],
        ]);

        $emailTemplate->update([
            'subject_template' => trim((string) $validated['subject_template']),
            'body_template' => trim((string) $validated['body_template']),
            'footer_note' => trim((string) ($validated['footer_note'] ?? '')),
            'content_note' => trim((string) ($validated['content_note'] ?? '')),
            'from_name' => $this->nullableTrimmedValue($validated['from_name'] ?? null),
            'from_email' => $this->nullableTrimmedValue($validated['from_email'] ?? null),
            'is_active' => $request->boolean('is_active'),
        ]);

        CustomizationSetting::setValue(
            'accent_color',
            trim((string) ($validated['accent_color'] ?? '#C8A45D')) ?: '#C8A45D',
            'color',
            'colors',
            'Accent color'
        );

        CustomizationSetting::setValue(
            'email_header_logo_url',
            trim((string) ($validated['email_header_logo_url'] ?? '')),
            'text',
            'email',
            'Email header logo URL'
        );

        CustomizationSetting::setValue(
            'email_header_bg_image_url',
            trim((string) ($validated['email_header_bg_image_url'] ?? '')),
            'text',
            'email',
            'Email header background image URL'
        );

        if ($request->hasFile('email_header_logo')) {
            $logoPath = $request->file('email_header_logo')->store('customization/email-branding', 'public');
            CustomizationSetting::setValue('email_header_logo', $logoPath, 'image', 'email', 'Email header logo');
        }

        if ($request->hasFile('email_header_bg_image')) {
            $backgroundPath = $request->file('email_header_bg_image')->store('customization/email-branding', 'public');
            CustomizationSetting::setValue('email_header_bg_image', $backgroundPath, 'image', 'email', 'Email header background image');
        }

        Artisan::call('view:clear');

        return redirect()
            ->route('admin.email-templates.edit', $emailTemplate)
            ->with('success', 'Email template updated successfully.');
    }

    public function reset(EmailTemplate $emailTemplate): RedirectResponse
    {
        EmailTemplate::resetToDefault($emailTemplate->template_key);
        Artisan::call('view:clear');

        return redirect()
            ->route('admin.email-templates.edit', $emailTemplate)
            ->with('success', 'Email template restored to default.');
    }

    public function sendAllTests(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recipient_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'test_email' => ['nullable', 'email:rfc,dns'],
            'test_name' => ['nullable', 'string', 'max:120'],
        ]);

        if (blank($validated['recipient_user_id'] ?? null) && blank($validated['test_email'] ?? null)) {
            return back()->with('error', 'Select a user email or enter a custom test email.');
        }

        $recipientName = trim((string) ($validated['test_name'] ?? ''));
        $recipientEmail = trim((string) ($validated['test_email'] ?? ''));

        if (!blank($validated['recipient_user_id'] ?? null)) {
            $user = User::query()
                ->whereKey((int) $validated['recipient_user_id'])
                ->first();

            if (!$user || blank($user->email)) {
                return back()->with('error', 'Selected user does not have a valid email address.');
            }

            $recipientEmail = (string) $user->email;
            $recipientName = $recipientName !== '' ? $recipientName : (string) $user->name;
        }

        if ($recipientName === '') {
            $recipientName = 'Test Recipient';
        }

        EmailTemplate::syncDefaults();
        $definitions = EmailTemplate::defaultDefinitions();

        $sentCount = 0;
        $failedKeys = [];

        foreach (array_keys($definitions) as $templateKey) {
            try {
                $this->emailTemplateService->sendTemplate(
                    $templateKey,
                    $recipientEmail,
                    $recipientName,
                    $this->sampleVariablesFor($templateKey, $recipientName, $recipientEmail)
                );
                $sentCount++;
            } catch (\Throwable $exception) {
                report($exception);
                $failedKeys[] = $templateKey;
            }
        }

        Artisan::call('view:clear');

        if (!empty($failedKeys)) {
            return back()->with(
                'warning',
                "Test completed: {$sentCount} template(s) sent, " . count($failedKeys) . ' failed (' . implode(', ', $failedKeys) . ').'
            );
        }

        return back()->with('success', "All {$sentCount} email templates were sent to {$recipientEmail}.");
    }

    private function sampleVariablesFor(string $templateKey, string $recipientName, string $recipientEmail): array
    {
        $now = now();
        $siteName = site_name();

        $common = [
            'site_name' => $siteName,
            'current_date' => $now->format('M d, Y'),
            'current_time' => $now->format('h:i A'),
            'current_datetime' => $now->format('M d, Y h:i A'),
        ];

        return match ($templateKey) {
            EmailTemplate::KEY_CONTACT_FORM_SUBMISSION => $common + [
                'name' => 'Website Visitor',
                'email' => $recipientEmail,
                'phone' => '+1 555 123 4567',
                'subject' => 'General Inquiry',
                'message' => 'This is a test message sent from the email template tester.',
                'submitted_at' => $now->format('M d, Y h:i A'),
            ],
            EmailTemplate::KEY_PAYMENT_REQUEST => $common + [
                'client_name' => $recipientName,
                'payment_reference' => '#362485',
                'amount' => '275.00',
                'status' => 'Pending',
                'note' => 'This is a test payment request template.',
                'login_url' => route('login'),
            ],
            EmailTemplate::KEY_OTP_REQUEST => $common + [
                'client_name' => $recipientName,
                'agent_name' => 'Team',
                'requested_at' => $now->format('M d, Y h:i A'),
                'expires_at' => $now->copy()->addMinutes(10)->format('M d, Y h:i A'),
                'expires_in_minutes' => '10',
                'request_message' => 'Please submit the verification code with the company name by clicking the link below.',
                'submit_url' => route('otp.submit.public', ['otpVerification' => 'ov-test']),
            ],
            EmailTemplate::KEY_OTP_SUBMISSION_NOTIFICATION => $common + [
                'agent_name' => 'Assigned Agent',
                'client_name' => $recipientName,
                'client_email' => $recipientEmail,
                'company_name' => 'Example Company Inc.',
                'otp_code' => '452198',
                'status' => 'Pending Review',
                'submitted_at' => $now->format('M d, Y h:i A'),
                'submissions_url' => route('agent.submissions.index'),
            ],
            EmailTemplate::KEY_SUPPORT_TICKET_MESSAGE => $common + [
                'recipient_name' => $recipientName,
                'sender_name' => 'Support Team',
                'ticket_reference' => 'ST-4584185',
                'ticket_subject' => 'Profile Access Request',
                'ticket_status' => 'Open',
                'message' => 'This is a test support message preview.',
                'posted_at' => $now->format('M d, Y h:i A'),
                'portal_url' => route('admin.support-tickets.index'),
            ],
            EmailTemplate::KEY_DAILY_MEETING_NOTIFICATION => $common + [
                'agent_name' => $recipientName,
                'meeting_date' => $now->copy()->addDay()->format('l, F j, Y'),
                'meeting_date_short' => $now->copy()->addDay()->format('M d, Y'),
                'meeting_time' => '09:00 AM - 10:00 AM',
                'meeting_duration' => '1 Hour',
                'meeting_title' => 'Daily Agent Standup',
                'meeting_link' => 'https://meet.google.com/test-demo-link',
            ],
            EmailTemplate::KEY_DAILY_WORK_UPDATE => $common + [
                'client_name' => $recipientName,
                'report_date' => $now->format('M d, Y'),
                'updates_count' => '3',
                'updates_list' => '<ul style="margin:0;padding-left:18px;"><li>Product Manager - ABC Corp</li><li>Business Analyst - Delta LLC</li><li>Operations Lead - Nova Inc.</li></ul>',
            ],
            EmailTemplate::KEY_CLIENT_SUBMISSION_NOTIFICATION => $common + [
                'agent_name' => 'Assigned Agent',
                'client_name' => $recipientName,
                'client_email' => $recipientEmail,
                'company_name' => 'Example Company Inc.',
                'otp_code' => '452198',
                'status' => 'Pending',
                'submitted_at' => $now->format('M d, Y h:i A'),
                'submissions_url' => route('agent.submissions.index'),
            ],
            EmailTemplate::KEY_CLIENT_WELCOME => $common + [
                'user_name' => $recipientName,
                'user_email' => $recipientEmail,
                'dashboard_url' => route('client.dashboard'),
                'onboarding_url' => route('client.onboarding.create'),
            ],
            EmailTemplate::KEY_ONBOARDING_SUBMISSION_CONFIRMATION => $common + [
                'user_name' => $recipientName,
                'dashboard_url' => route('client.dashboard'),
                'support_tickets_url' => route('client.support-tickets.create'),
            ],
            EmailTemplate::KEY_EMAIL_VERIFICATION => $common + [
                'user_name' => $recipientName,
                'user_email' => $recipientEmail,
                'verification_url' => route('verification.notice'),
                'verification_expires_at' => $now->copy()->addMinutes(60)->format('M d, Y h:i A'),
            ],
            EmailTemplate::KEY_PASSWORD_RESET => $common + [
                'user_name' => $recipientName,
                'user_email' => $recipientEmail,
                'reset_url' => route('password.request'),
                'reset_expires_minutes' => '60',
            ],
            EmailTemplate::KEY_PASSWORD_RESET_CONFIRMATION => $common + [
                'user_name' => $recipientName,
                'user_email' => $recipientEmail,
                'changed_at' => $now->format('M d, Y h:i A'),
                'ip_address' => request()->ip() ?: '127.0.0.1',
                'login_url' => route('login'),
            ],
            default => $common,
        };
    }

    private function settingAssetUrl(?string $path, ?int $updatedAtTimestamp): ?string
    {
        $trimmedPath = ltrim(trim((string) $path), '/');
        if ($trimmedPath === '') {
            return null;
        }

        $url = storage_public_url($trimmedPath);
        $version = $updatedAtTimestamp ?: time();

        return "{$url}?v={$version}";
    }

    private function nullableTrimmedValue(?string $value): ?string
    {
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
