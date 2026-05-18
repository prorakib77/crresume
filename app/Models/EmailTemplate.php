<?php

namespace App\Models;

use App\Models\Concerns\HasSlugRouteKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class EmailTemplate extends Model
{
    use HasFactory;
    use HasSlugRouteKey;

    public const KEY_CONTACT_FORM_SUBMISSION = 'contact_form_submission';
    public const KEY_PAYMENT_REQUEST = 'payment_request';
    public const KEY_OTP_REQUEST = 'otp_request';
    public const KEY_OTP_SUBMISSION_NOTIFICATION = 'otp_submission_notification';
    public const KEY_SUPPORT_TICKET_MESSAGE = 'support_ticket_message';
    public const KEY_DAILY_MEETING_NOTIFICATION = 'daily_meeting_notification';
    public const KEY_DAILY_WORK_UPDATE = 'daily_work_update';
    public const KEY_CLIENT_SUBMISSION_NOTIFICATION = 'client_submission_notification';
    public const KEY_CLIENT_WELCOME = 'client_welcome';
    public const KEY_ONBOARDING_SUBMISSION_CONFIRMATION = 'onboarding_submission_confirmation';
    public const KEY_EMAIL_VERIFICATION = 'email_verification';
    public const KEY_PASSWORD_RESET = 'password_reset';
    public const KEY_PASSWORD_RESET_CONFIRMATION = 'password_reset_confirmation';

    protected $fillable = [
        'template_key',
        'template_name',
        'description',
        'subject_template',
        'body_template',
        'footer_note',
        'content_note',
        'from_name',
        'from_email',
        'available_variables',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public static function defaultDefinitions(): array
    {
        $defaultFooterNote = 'This is an automated email.';
        $defaultDailyWorkUpdateContentNote = 'This is an automated message and this inbox is not monitored. Please do not reply to this email.';

        $dailyWorkUpdateTemplate = <<<'HTML'
<div style="border:1px solid #111111;border-radius:10px;overflow:hidden;background:#ffffff;">
    <div style="padding:14px 16px;border-bottom:1px solid #111111;">
        <p style="margin:0;font-size:10px;letter-spacing:0.1em;text-transform:uppercase;color:#6b7280;font-weight:700;">Daily Work Update</p>
        <p style="margin:8px 0 0;font-size:48px;line-height:1.05;font-weight:700;color:#111111;">Hello {{client_name}},</p>
        <p style="margin:8px 0 0;font-size:16px;line-height:1.4;color:#4b5563;">{{report_date}}</p>
    </div>
    <div style="padding:14px 16px;">
        <p style="margin:0 0 12px;font-size:14px;line-height:1.7;color:#1f2937;">Here are the latest actions completed for you. Each entry below contains the essential details for a quick review.</p>
        <div>
            {{updates_list}}
        </div>
    </div>
    <div style="padding:10px 16px;border-top:1px solid #111111;background:#f8fafc;">
        <p style="margin:0;font-size:11px;line-height:1.5;color:#4b5563;">{{content_note}}</p>
    </div>
</div>
HTML;

        $paymentRequestTemplate = <<<'HTML'
<div style="border:1px solid #111111;border-radius:10px;overflow:hidden;background:#ffffff;">
    <div style="padding:14px 16px;border-bottom:1px solid #111111;">
        <p style="margin:0;font-size:10px;letter-spacing:0.1em;text-transform:uppercase;color:#6b7280;font-weight:700;">Payment Request</p>
        <p style="margin:8px 0 0;font-size:34px;line-height:1.1;font-weight:700;color:#111111;">Hello {{client_name}},</p>
        <p style="margin:8px 0 0;font-size:14px;line-height:1.6;color:#4b5563;">A new payment request has been added to your account.</p>
    </div>
    <div style="padding:14px 16px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;border:1px solid #111111;border-radius:8px;overflow:hidden;background:#ffffff;">
            <tr>
                <td style="padding:10px 12px;border-bottom:1px solid #111111;font-size:12px;line-height:1.5;color:#6b7280;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">Payment ID</td>
                <td style="padding:10px 12px;border-bottom:1px solid #111111;font-size:14px;line-height:1.5;color:#111111;font-weight:700;text-align:right;">{{payment_reference}}</td>
            </tr>
            <tr>
                <td style="padding:10px 12px;border-bottom:1px solid #111111;font-size:12px;line-height:1.5;color:#6b7280;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">Status</td>
                <td style="padding:10px 12px;border-bottom:1px solid #111111;font-size:14px;line-height:1.5;color:#111111;font-weight:700;text-align:right;">{{status}}</td>
            </tr>
            <tr>
                <td style="padding:10px 12px;font-size:12px;line-height:1.5;color:#6b7280;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">Amount Due</td>
                <td style="padding:10px 12px;font-size:20px;line-height:1.3;color:#111111;font-weight:800;text-align:right;">${{amount}}</td>
            </tr>
        </table>

        <div style="margin-top:10px;border:1px solid #111111;border-radius:8px;padding:10px 12px;background:#ffffff;">
            <p style="margin:0 0 4px;font-size:10px;line-height:1.4;color:#6b7280;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">Note</p>
            <p style="margin:0;font-size:13px;line-height:1.7;color:#111111;">{{note}}</p>
        </div>

        <a href="{{login_url}}" style="display:inline-block;margin-top:12px;background:#111111;color:#ffffff !important;text-decoration:none;padding:10px 16px;border-radius:7px;border:1px solid #111111;font-size:13px;line-height:1.2;font-weight:700;">Open Dashboard</a>
    </div>
    <div style="padding:10px 16px;border-top:1px solid #111111;background:#f8fafc;">
        <p style="margin:0;font-size:11px;line-height:1.6;color:#4b5563;">Review the request and mark it as paid after payment is completed.</p>
    </div>
</div>
HTML;

        $otpRequestTemplate = <<<'HTML'
<div style="border:1px solid #111111;border-radius:10px;overflow:hidden;background:#ffffff;">
    <div style="padding:14px 16px;border-bottom:1px solid #111111;">
        <p style="margin:0;font-size:10px;letter-spacing:0.1em;text-transform:uppercase;color:#6b7280;font-weight:700;">Verification Code Request</p>
        <p style="margin:8px 0 0;font-size:32px;line-height:1.1;font-weight:700;color:#111111;">Hello {{client_name}},</p>
        <p style="margin:8px 0 0;font-size:14px;line-height:1.7;color:#111111;">We request that you provide us with the verification code that was sent to your email, as we need it to successfully complete the application.</p>
    </div>
    <div style="padding:14px 16px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;border:1px solid #111111;border-radius:8px;overflow:hidden;background:#ffffff;">
            <tr>
                <td style="padding:10px 12px;border-bottom:1px solid #111111;font-size:12px;line-height:1.5;color:#6b7280;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">Requested On</td>
                <td style="padding:10px 12px;border-bottom:1px solid #111111;font-size:14px;line-height:1.5;color:#111111;font-weight:700;text-align:right;">{{requested_at}}</td>
            </tr>
            <tr>
                <td style="padding:10px 12px;border-bottom:1px solid #111111;font-size:12px;line-height:1.5;color:#6b7280;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">Expires At</td>
                <td style="padding:10px 12px;border-bottom:1px solid #111111;font-size:14px;line-height:1.5;color:#111111;font-weight:700;text-align:right;">{{expires_at}}</td>
            </tr>
            <tr>
                <td style="padding:10px 12px;font-size:12px;line-height:1.5;color:#6b7280;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">Validity</td>
                <td style="padding:10px 12px;font-size:14px;line-height:1.5;color:#111111;font-weight:700;text-align:right;">{{expires_in_minutes}} minutes</td>
            </tr>
        </table>
        <p style="margin:12px 0 0;font-size:14px;line-height:1.7;color:#111111;">{{request_message}}</p>
        <a href="{{submit_url}}" style="display:inline-block;margin-top:12px;background:#111111;color:#ffffff !important;text-decoration:none;padding:10px 16px;border-radius:7px;border:1px solid #111111;font-size:13px;line-height:1.2;font-weight:700;">Submit Verification Code</a>
    </div>
    <div style="padding:10px 16px;border-top:1px solid #111111;background:#f8fafc;">
        <p style="margin:0;font-size:11px;line-height:1.6;color:#4b5563;">For security reasons, this verification code expires in {{expires_in_minutes}} minutes.</p>
    </div>
</div>
HTML;

        $otpSubmissionNotificationTemplate = <<<'HTML'
<div style="border:1px solid #111111;border-radius:10px;overflow:hidden;background:#ffffff;">
    <div style="padding:14px 16px;border-bottom:1px solid #111111;">
        <p style="margin:0;font-size:10px;letter-spacing:0.1em;text-transform:uppercase;color:#6b7280;font-weight:700;">Verification Submission</p>
        <p style="margin:8px 0 0;font-size:30px;line-height:1.1;font-weight:700;color:#111111;">Your client submitted a verification code.</p>
    </div>
    <div style="padding:14px 16px;">
        <p style="margin:0 0 10px;font-size:14px;line-height:1.7;color:#111111;">Hello {{agent_name}},</p>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;border:1px solid #111111;border-radius:8px;overflow:hidden;background:#ffffff;">
            <tr>
                <td style="padding:10px 12px;border-bottom:1px solid #111111;font-size:12px;line-height:1.5;color:#6b7280;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">Client</td>
                <td style="padding:10px 12px;border-bottom:1px solid #111111;font-size:14px;line-height:1.5;color:#111111;font-weight:700;text-align:right;">{{client_name}} ({{client_email}})</td>
            </tr>
            <tr>
                <td style="padding:10px 12px;border-bottom:1px solid #111111;font-size:12px;line-height:1.5;color:#6b7280;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">Company</td>
                <td style="padding:10px 12px;border-bottom:1px solid #111111;font-size:14px;line-height:1.5;color:#111111;font-weight:700;text-align:right;">{{company_name}}</td>
            </tr>
            <tr>
                <td style="padding:10px 12px;border-bottom:1px solid #111111;font-size:12px;line-height:1.5;color:#6b7280;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">Verification Code</td>
                <td style="padding:10px 12px;border-bottom:1px solid #111111;font-size:15px;line-height:1.5;color:#111111;font-weight:800;text-align:right;">{{otp_code}}</td>
            </tr>
            <tr>
                <td style="padding:10px 12px;border-bottom:1px solid #111111;font-size:12px;line-height:1.5;color:#6b7280;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">Status</td>
                <td style="padding:10px 12px;border-bottom:1px solid #111111;font-size:14px;line-height:1.5;color:#111111;font-weight:700;text-align:right;">{{status}}</td>
            </tr>
            <tr>
                <td style="padding:10px 12px;font-size:12px;line-height:1.5;color:#6b7280;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">Submitted</td>
                <td style="padding:10px 12px;font-size:14px;line-height:1.5;color:#111111;font-weight:700;text-align:right;">{{submitted_at}}</td>
            </tr>
        </table>
        <a href="{{submissions_url}}" style="display:inline-block;margin-top:12px;background:#111111;color:#ffffff !important;text-decoration:none;padding:10px 16px;border-radius:7px;border:1px solid #111111;font-size:13px;line-height:1.2;font-weight:700;">Open Submissions</a>
    </div>
    <div style="padding:10px 16px;border-top:1px solid #111111;background:#f8fafc;">
        <p style="margin:0;font-size:11px;line-height:1.6;color:#4b5563;">Review this submission from your dashboard.</p>
    </div>
</div>
HTML;

        return [
            self::KEY_CONTACT_FORM_SUBMISSION => [
                'name' => 'Contact Form Submission',
                'description' => 'Email sent to admin when someone submits the website contact form.',
                'subject' => 'Contact Form: {{subject}}',
                'body' => '<h2>New Contact Form Submission</h2><p><strong>Name:</strong> {{name}}</p><p><strong>Email:</strong> {{email}}</p><p><strong>Phone:</strong> {{phone}}</p><p><strong>Subject:</strong> {{subject}}</p><p><strong>Submitted:</strong> {{submitted_at}}</p><hr><p>{{message}}</p>',
                'variables' => ['name', 'email', 'phone', 'subject', 'message', 'submitted_at', 'site_name'],
                'footer_note' => $defaultFooterNote,
                'sort_order' => 10,
            ],
            self::KEY_PAYMENT_REQUEST => [
                'name' => 'Payment Request',
                'description' => 'Email sent to client when admin creates a payment request.',
                'subject' => 'Payment Request {{payment_reference}} - ${{amount}}',
                'body' => $paymentRequestTemplate,
                'variables' => ['client_name', 'payment_reference', 'amount', 'status', 'note', 'login_url', 'site_name'],
                'footer_note' => $defaultFooterNote,
                'sort_order' => 20,
            ],
            self::KEY_OTP_REQUEST => [
                'name' => 'OTP Request',
                'description' => 'Email sent to client when agent requests OTP/company details.',
                'subject' => 'Verification Code Request - Action Required',
                'body' => $otpRequestTemplate,
                'variables' => ['client_name', 'agent_name', 'requested_at', 'expires_at', 'expires_in_minutes', 'request_message', 'submit_url', 'site_name'],
                'footer_note' => $defaultFooterNote,
                'sort_order' => 30,
            ],
            self::KEY_OTP_SUBMISSION_NOTIFICATION => [
                'name' => 'OTP Submission Notification',
                'description' => 'Email sent to agent when client submits OTP/company details.',
                'subject' => 'Your client submitted a verification code - {{company_name}}',
                'body' => $otpSubmissionNotificationTemplate,
                'variables' => ['agent_name', 'client_name', 'client_email', 'company_name', 'otp_code', 'status', 'submitted_at', 'submissions_url', 'site_name'],
                'footer_note' => $defaultFooterNote,
                'sort_order' => 40,
            ],
            self::KEY_SUPPORT_TICKET_MESSAGE => [
                'name' => 'Support Ticket Message',
                'description' => 'Email sent when a support ticket gets a new message.',
                'subject' => 'Support Ticket {{ticket_reference}} - {{ticket_subject}}',
                'body' => '<h2>Support Ticket Update</h2><p>Hi {{recipient_name}},</p><p><strong>{{sender_name}}</strong> sent a new message on your support ticket.</p><p><strong>Ticket:</strong> {{ticket_reference}} - {{ticket_subject}}</p><p><strong>Status:</strong> {{ticket_status}}</p><hr><p>{{message}}</p><p><strong>Posted:</strong> {{posted_at}}</p><p><a href="{{portal_url}}">View Ticket</a></p>',
                'variables' => ['recipient_name', 'sender_name', 'ticket_reference', 'ticket_subject', 'ticket_status', 'message', 'posted_at', 'portal_url', 'site_name'],
                'footer_note' => $defaultFooterNote,
                'sort_order' => 50,
            ],
            self::KEY_DAILY_MEETING_NOTIFICATION => [
                'name' => 'Daily Meeting Notification',
                'description' => 'Email sent to agents with daily meeting details.',
                'subject' => 'Daily Agent Meeting - {{meeting_date_short}}',
                'body' => '<h2>Daily Agent Meeting</h2><p>Hello {{agent_name}},</p><p><strong>Date:</strong> {{meeting_date}}</p><p><strong>Time:</strong> {{meeting_time}}</p><p><strong>Duration:</strong> {{meeting_duration}}</p><p><strong>Title:</strong> {{meeting_title}}</p><p><a href="{{meeting_link}}">Join Meeting</a></p>',
                'variables' => ['agent_name', 'meeting_date', 'meeting_date_short', 'meeting_time', 'meeting_duration', 'meeting_title', 'meeting_link', 'site_name'],
                'footer_note' => $defaultFooterNote,
                'sort_order' => 60,
            ],
            self::KEY_DAILY_WORK_UPDATE => [
                'name' => 'Daily Work Update',
                'description' => 'Email sent to clients with day-by-day work updates.',
                'subject' => 'Daily Work Update - {{report_date}}',
                'body' => $dailyWorkUpdateTemplate,
                'variables' => ['client_name', 'report_date', 'updates_count', 'updates_list', 'content_note', 'site_name'],
                'footer_note' => $defaultFooterNote,
                'content_note' => $defaultDailyWorkUpdateContentNote,
                'sort_order' => 70,
            ],
            self::KEY_CLIENT_SUBMISSION_NOTIFICATION => [
                'name' => 'Client Submission Notification',
                'description' => 'Email sent to agent when client submits company/OTP form.',
                'subject' => 'New Client Submission - {{company_name}}',
                'body' => '<h2>New Client Submission</h2><p>Hello {{agent_name}},</p><p><strong>Client:</strong> {{client_name}} ({{client_email}})</p><p><strong>Company:</strong> {{company_name}}</p><p><strong>OTP:</strong> {{otp_code}}</p><p><strong>Status:</strong> {{status}}</p><p><strong>Submitted:</strong> {{submitted_at}}</p><p><a href="{{submissions_url}}">Open Submissions</a></p>',
                'variables' => ['agent_name', 'client_name', 'client_email', 'company_name', 'otp_code', 'status', 'submitted_at', 'submissions_url', 'site_name'],
                'footer_note' => $defaultFooterNote,
                'sort_order' => 80,
            ],
            self::KEY_CLIENT_WELCOME => [
                'name' => 'Client Welcome',
                'description' => 'Welcome email sent after a new client account is created.',
                'subject' => 'Welcome to {{site_name}}, {{user_name}}',
                'body' => '<p>Hello {{user_name}},</p><p>Welcome to {{site_name}}. Your client portal account has been created successfully, and we are pleased to have you with us.</p><p>You can sign in any time using <strong>{{user_email}}</strong> to review your dashboard and account activity.</p><p>If you subscribed to our Full Service package, your next step is to complete the onboarding form from your dashboard. This helps our team review your information, prepare your materials, and move your service forward properly.</p><p>Please have your current resume and any relevant details ready before you begin.</p><p><a href="{{onboarding_url}}" class="mail-cta">Open Onboarding Form</a></p><p>If you purchased a different service, please follow the instructions provided with your purchase and keep an eye on your email for any additional next steps from our team.</p><p>Thank you for choosing {{site_name}}. We appreciate the opportunity to support you.</p>',
                'variables' => ['user_name', 'user_email', 'onboarding_url', 'dashboard_url', 'site_name'],
                'footer_note' => $defaultFooterNote,
                'sort_order' => 85,
            ],
            self::KEY_ONBOARDING_SUBMISSION_CONFIRMATION => [
                'name' => 'Onboarding Submission Confirmation',
                'description' => 'Confirmation email sent after a client successfully submits the onboarding form.',
                'subject' => 'We received your onboarding form',
                'body' => '<p>Hello {{user_name}},</p><p>Thank you for submitting your onboarding form. We have successfully received your onboarding details, resume, and signature.</p><p>Our team will review the information you provided and continue with the next steps in your service process.</p><p>If you have any questions or need help at any point, please create a support ticket from your dashboard for the fastest assistance.</p><p><a href="{{support_tickets_url}}" class="mail-cta">Create Support Ticket</a></p><p>You can also return to your dashboard at any time to check notices, updates, and service activity.</p><p>We appreciate your prompt submission and look forward to supporting you.</p>',
                'variables' => ['user_name', 'dashboard_url', 'support_tickets_url', 'site_name'],
                'footer_note' => $defaultFooterNote,
                'sort_order' => 86,
            ],
            self::KEY_EMAIL_VERIFICATION => [
                'name' => 'Email Verification',
                'description' => 'Verification email sent when user account needs email verification.',
                'subject' => 'Verify Your Email Address',
                'body' => '<h2>Email Verification Required</h2><p>Hello {{user_name}},</p><p>Please verify your email by clicking the button below.</p><p><a href="{{verification_url}}">Verify Email Address</a></p><p><strong>Expires:</strong> {{verification_expires_at}}</p>',
                'variables' => ['user_name', 'user_email', 'verification_url', 'verification_expires_at', 'site_name'],
                'footer_note' => $defaultFooterNote,
                'sort_order' => 90,
            ],
            self::KEY_PASSWORD_RESET => [
                'name' => 'Password Reset Link',
                'description' => 'Password reset email sent when user requests reset link.',
                'subject' => 'Reset Your Password',
                'body' => '<h2>Password Reset Request</h2><p>Hello {{user_name}},</p><p>Click below to reset your password.</p><p><a href="{{reset_url}}">Reset Password</a></p><p><strong>Expires in:</strong> {{reset_expires_minutes}} minutes</p>',
                'variables' => ['user_name', 'user_email', 'reset_url', 'reset_expires_minutes', 'site_name'],
                'footer_note' => $defaultFooterNote,
                'sort_order' => 100,
            ],
            self::KEY_PASSWORD_RESET_CONFIRMATION => [
                'name' => 'Password Reset Confirmation',
                'description' => 'Confirmation email sent after password reset succeeds.',
                'subject' => 'Password Reset Successful',
                'body' => '<p>Hello {{user_name}},</p><p>Your password has been changed successfully.</p><p><strong>Changed at:</strong> {{changed_at}}</p><p><strong>IP:</strong> {{ip_address}}</p><p><a href="{{login_url}}" style="display:inline-block;background:#111111;color:#ffffff !important;text-decoration:none;padding:11px 16px;border-radius:8px;font-weight:600;">Sign In</a></p>',
                'variables' => ['user_name', 'user_email', 'changed_at', 'ip_address', 'login_url', 'site_name'],
                'footer_note' => $defaultFooterNote,
                'sort_order' => 110,
            ],
        ];
    }

    public static function syncDefaults(): void
    {
        if (!Schema::hasTable('email_templates')) {
            return;
        }

        foreach (static::defaultDefinitions() as $key => $definition) {
            $template = static::firstOrNew(['template_key' => $key]);
            $isNew = !$template->exists;

            $template->template_name = $definition['name'];
            $template->description = $definition['description'] ?? null;
            $template->available_variables = json_encode($definition['variables'] ?? [], JSON_UNESCAPED_SLASHES);
            $template->sort_order = (int) ($definition['sort_order'] ?? 0);

            if ($isNew) {
                $template->subject_template = $definition['subject'] ?? '';
                $template->body_template = $definition['body'] ?? '';
                $template->footer_note = $definition['footer_note'] ?? null;
                $template->content_note = $definition['content_note'] ?? null;
                $template->from_name = $definition['from_name'] ?? null;
                $template->from_email = $definition['from_email'] ?? null;
                $template->is_active = true;
            } elseif ($key === self::KEY_PAYMENT_REQUEST) {
                $currentBody = (string) $template->body_template;

                $usesLegacyPaymentTemplate =
                    str_contains($currentBody, '<h2>Payment Requested</h2>')
                    || str_contains($currentBody, 'Our team sent you a payment request.')
                    || str_contains($currentBody, '<strong>Payment ID:</strong>')
                    || str_contains($currentBody, 'Go to Dashboard');

                if ($usesLegacyPaymentTemplate) {
                    $template->subject_template = $definition['subject'] ?? '';
                    $template->body_template = $definition['body'] ?? '';
                }
            } elseif ($key === self::KEY_OTP_REQUEST) {
                $currentBody = (string) $template->body_template;

                $usesLegacyOtpTemplate =
                    str_contains($currentBody, '<h2>Verification Code Request</h2>')
                    || str_contains($currentBody, 'requested your OTP/company information')
                    || str_contains($currentBody, 'Submit Company Information')
                    || str_contains($currentBody, 'Please submit your verification details.')
                    || str_contains($currentBody, 'Please submit your company information and OTP')
                    || (
                        str_contains($currentBody, 'Please submit the verification code with the company name by clicking the link below.')
                        && str_contains($currentBody, '{{request_message}}')
                    );

                if ($usesLegacyOtpTemplate) {
                    $template->subject_template = $definition['subject'] ?? '';
                    $template->body_template = $definition['body'] ?? '';
                }
            } elseif ($key === self::KEY_OTP_SUBMISSION_NOTIFICATION) {
                $currentBody = (string) $template->body_template;

                $usesLegacyOtpSubmissionTemplate =
                    str_contains($currentBody, '<h2>New OTP Submission Received</h2>')
                    || str_contains($currentBody, 'A client submitted verification details.')
                    || str_contains($currentBody, 'View Submissions')
                    || str_contains($currentBody, '<strong>OTP:</strong>');

                if ($usesLegacyOtpSubmissionTemplate) {
                    $template->subject_template = $definition['subject'] ?? '';
                    $template->body_template = $definition['body'] ?? '';
                }
            } elseif ($key === self::KEY_PASSWORD_RESET_CONFIRMATION) {
                $currentBody = (string) $template->body_template;

                $usesLegacyHeading = str_contains($currentBody, '<h2>Password Reset Successful</h2>');
                $usesLegacyButton = str_contains($currentBody, 'href="{{login_url}}"') && !str_contains($currentBody, 'background:#111111');

                if ($usesLegacyHeading || $usesLegacyButton) {
                    $template->subject_template = $definition['subject'] ?? '';
                    $template->body_template = $definition['body'] ?? '';
                }
            } elseif ($key === self::KEY_DAILY_WORK_UPDATE) {
                $currentBody = (string) $template->body_template;

                $usesLegacyDailyTemplate =
                    str_contains($currentBody, '<h2>Daily Work Update</h2>')
                    || str_contains($currentBody, 'Today we submitted <strong>{{updates_count}}</strong> updates.')
                    || str_contains($currentBody, 'Daily Summary')
                    || str_contains($currentBody, 'Here is your daily work update for <strong>{{report_date}}</strong>.')
                    || str_contains($currentBody, 'class="wu-card"')
                    || str_contains($currentBody, '<title>Daily Work Update</title>');

                if ($usesLegacyDailyTemplate) {
                    $template->subject_template = $definition['subject'] ?? '';
                    $template->body_template = $definition['body'] ?? '';
                } elseif (
                    str_contains($currentBody, 'This is an automated message and this inbox is not monitored. Please do not reply to this email.')
                    && !str_contains($currentBody, '{{content_note}}')
                ) {
                    $template->body_template = str_replace(
                        'This is an automated message and this inbox is not monitored. Please do not reply to this email.',
                        '{{content_note}}',
                        $currentBody
                    );
                }
            }

            $template->save();
        }
    }

    public static function defaultSubject(string $key): string
    {
        return (string) (static::defaultDefinitions()[$key]['subject'] ?? '');
    }

    public static function defaultBody(string $key): string
    {
        return (string) (static::defaultDefinitions()[$key]['body'] ?? '');
    }

    public static function defaultFooterNote(string $key): ?string
    {
        return static::defaultDefinitions()[$key]['footer_note'] ?? null;
    }

    public static function defaultContentNote(string $key): ?string
    {
        return static::defaultDefinitions()[$key]['content_note'] ?? null;
    }

    public static function resetToDefault(string $key): ?self
    {
        $definition = static::defaultDefinitions()[$key] ?? null;
        if (!$definition) {
            return null;
        }

        $template = static::query()->where('template_key', $key)->first();

        if (!$template) {
            return null;
        }

        $template->update([
            'subject_template' => (string) ($definition['subject'] ?? ''),
            'body_template' => (string) ($definition['body'] ?? ''),
            'footer_note' => $definition['footer_note'] ?? null,
            'content_note' => $definition['content_note'] ?? null,
            'from_name' => $definition['from_name'] ?? null,
            'from_email' => $definition['from_email'] ?? null,
            'is_active' => true,
        ]);

        return $template->fresh();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getVariablesAttribute(): array
    {
        $decoded = json_decode((string) $this->available_variables, true);
        if (!is_array($decoded)) {
            return [];
        }

        return array_values(array_filter(array_map(static fn ($item) => trim((string) $item), $decoded)));
    }

    protected function routeKeyPrefix(): string
    {
        return 'eml';
    }

    protected function routeKeySourceColumn(): ?string
    {
        return 'template_name';
    }
}
