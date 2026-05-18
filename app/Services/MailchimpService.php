<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailchimpService
{
    protected $apiKey;
    protected $serverPrefix;
    protected $listId;

    public function __construct()
    {
        $this->apiKey = config('services.mailchimp.api_key');
        $this->serverPrefix = config('services.mailchimp.server_prefix');
        $this->listId = config('services.mailchimp.list_id');
    }

    /**
     * Send daily work update email to client using Laravel Mail
     */
    public function sendDailyWorkUpdate(User $client, array $workUpdates, $date)
    {
        try {
            // Prepare email content
            $emailContent = $this->prepareWorkUpdateEmail($client, $workUpdates, $date);
            $updatesListHtml = $this->buildUpdatesListHtml($workUpdates);

            app(EmailTemplateService::class)->sendTemplate(
                EmailTemplate::KEY_DAILY_WORK_UPDATE,
                (string) $client->email,
                (string) $client->name,
                [
                    'client_name' => $client->name,
                    'report_date' => $date->format('M j, Y'),
                    'updates_count' => count($workUpdates),
                    'updates_list' => $updatesListHtml,
                ],
                [
                    'subject_fallback' => "Daily Work Update - {$date->format('M j, Y')}",
                    'body_fallback' => $emailContent,
                ]
            );

            Log::info('Daily work update email sent successfully', [
                'client_id' => $client->id,
                'client_email' => $client->email,
                'updates_count' => count($workUpdates),
                'method' => 'Laravel Mail'
            ]);
            return true;

        } catch (\Exception $e) {
            Log::error('Exception in MailchimpService::sendDailyWorkUpdate', [
                'client_id' => $client->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Ensure client is subscribed to the Mailchimp list
     */
    private function ensureClientSubscribed(User $client)
    {
        try {
            // Check if client is already subscribed
            $response = Http::withBasicAuth('anystring', $this->apiKey)
                ->get("https://{$this->serverPrefix}.api.mailchimp.com/3.0/lists/{$this->listId}/members/" . md5(strtolower($client->email)));

            if ($response->successful()) {
                // Client is already subscribed
                return true;
            }

            // If not subscribed, add them to the list
            $subscribeResponse = Http::withBasicAuth('anystring', $this->apiKey)
                ->post("https://{$this->serverPrefix}.api.mailchimp.com/3.0/lists/{$this->listId}/members", [
                    'email_address' => $client->email,
                    'status' => 'subscribed',
                    'merge_fields' => [
                        'FNAME' => $client->name,
                        'LNAME' => ''
                    ],
                    'tags' => ['client', 'work-updates']
                ]);

            if ($subscribeResponse->successful()) {
                Log::info('Client subscribed to Mailchimp list', [
                    'client_id' => $client->id,
                    'client_email' => $client->email
                ]);
                return true;
            } else {
                Log::warning('Failed to subscribe client to Mailchimp list', [
                    'client_id' => $client->id,
                    'client_email' => $client->email,
                    'response' => $subscribeResponse->json()
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error('Exception in ensureClientSubscribed', [
                'client_id' => $client->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Prepare HTML content for work update email
     */
    private function prepareWorkUpdateEmail(User $client, array $workUpdates, $date)
    {
        $reportDate = e($date->format('M j, Y'));
        $safeClientName = e((string) $client->name);
        $updatesList = $this->buildUpdatesListHtml($workUpdates);

        return "
            <p>Hello {$safeClientName},</p>
            <p>Here is your daily work update for <strong>{$reportDate}</strong>.</p>
            {$updatesList}
            <p style='margin-top:14px;'>Each item includes links and notes for quick review.</p>
        ";
    }

    private function buildUpdatesListHtml(array $workUpdates): string
    {
        if ($workUpdates === []) {
            return '
                <div style="border:1px solid #111111;border-radius:8px;padding:12px;background:#ffffff;">
                    <p style="margin:0;font-size:13px;line-height:1.5;color:#111111;font-weight:600;">No updates were submitted for this day.</p>
                    <p style="margin:4px 0 0;font-size:12px;line-height:1.6;color:#6b7280;">Your next report will be sent automatically when new entries are available.</p>
                </div>
            ';
        }

        $items = [];

        foreach ($workUpdates as $update) {
            $jobTitle = e((string) ($update['job_title'] ?? 'Text'));
            $company = e((string) ($update['company'] ?? 'Text'));
            $statusKey = strtolower(trim((string) ($update['application_status'] ?? 'applied')));
            $status = e($this->formatStatusLabel($statusKey));
            $appliedDate = e($this->formatAppliedDateValue($update['applied_date'] ?? null));
            $method = e($this->formatStatusLabel((string) ($update['applied_method'] ?? 'web')));
            $statusDotColor = e($this->getStatusDotColor($statusKey));
            $note = trim((string) ($update['note'] ?? ''));
            $jobLink = trim((string) ($update['job_link'] ?? ''));
            $successLink = trim((string) ($update['job_success_link'] ?? ''));

            $linksHtml = '';
            if ($jobLink !== '') {
                $safeJobLink = e($jobLink);
                $linksHtml .= "<a href=\"{$safeJobLink}\" target=\"_blank\" rel=\"noopener\" style=\"display:inline-block;background:#111111;color:#ffffff !important;text-decoration:none;border:1px solid #111111;border-radius:6px;padding:8px 12px;font-size:12px;line-height:1.2;font-weight:700;margin:0 8px 8px 0;\">View Job Posting</a>";
            }
            if ($successLink !== '') {
                $safeSuccessLink = e($successLink);
                $linksHtml .= "<a href=\"{$safeSuccessLink}\" target=\"_blank\" rel=\"noopener\" style=\"display:inline-block;background:#111111;color:#ffffff !important;text-decoration:none;border:1px solid #111111;border-radius:6px;padding:8px 12px;font-size:12px;line-height:1.2;font-weight:700;margin:0 8px 8px 0;\">Open Success Link</a>";
            }

            $noteHtml = '';
            if ($note !== '') {
                $safeNote = nl2br(e($note));
                $noteHtml = "
                    <div style=\"margin-top:8px;border:1px solid #111111;border-radius:6px;padding:8px 10px;background:#ffffff;\">
                        <span style=\"display:block;margin-bottom:4px;font-size:10px;letter-spacing:0.08em;text-transform:uppercase;color:#6b7280;font-weight:700;\">Note</span>
                        <p style=\"margin:0;font-size:13px;line-height:1.6;color:#111111;\">{$safeNote}</p>
                    </div>
                ";
            }

            $linksBlockHtml = $linksHtml !== ''
                ? "<div style=\"margin-top:10px;font-size:0;\">{$linksHtml}</div>"
                : '';

            $items[] = "
                <div style=\"margin:0 0 10px;border:1px solid #111111;border-radius:8px;padding:10px;background:#ffffff;\">
                    <table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"border-collapse:collapse;\">
                        <tr>
                            <td valign=\"top\" style=\"padding:0 8px 0 0;\">
                                <p style=\"margin:0;font-size:22px;line-height:1.2;color:#111111;font-weight:700;\">{$jobTitle}</p>
                                <p style=\"margin:4px 0 0;font-size:13px;line-height:1.5;color:#6b7280;\">{$company}</p>
                            </td>
                            <td valign=\"top\" align=\"right\" style=\"white-space:nowrap;\">
                                <span style=\"display:inline-block;padding:5px 12px;border:1px solid #111111;border-radius:999px;background:#ffffff;font-size:11px;line-height:1.2;font-weight:700;color:#111111;text-transform:capitalize;\">{$status}</span>
                                <span style=\"display:inline-block;width:8px;height:8px;border-radius:999px;border:1px solid #111111;background:{$statusDotColor};margin-left:5px;vertical-align:middle;\"></span>
                            </td>
                        </tr>
                    </table>
                    <table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"border-collapse:collapse;margin-top:10px;\">
                        <tr>
                            <td valign=\"top\" style=\"padding:0 8px 0 0;width:50%;\">
                                <span style=\"display:block;margin-bottom:4px;font-size:10px;letter-spacing:0.08em;text-transform:uppercase;color:#6b7280;font-weight:700;\">Applied Date</span>
                                <div style=\"font-size:13px;line-height:1.4;color:#111111;font-weight:700;word-break:break-word;\">{$appliedDate}</div>
                            </td>
                            <td valign=\"top\" style=\"padding:0;width:50%;\">
                                <span style=\"display:block;margin-bottom:4px;font-size:10px;letter-spacing:0.08em;text-transform:uppercase;color:#6b7280;font-weight:700;\">Method</span>
                                <div style=\"font-size:13px;line-height:1.4;color:#111111;font-weight:700;word-break:break-word;\">{$method}</div>
                            </td>
                        </tr>
                    </table>
                    {$linksBlockHtml}
                    {$noteHtml}
                </div>
            ";
        }

        return implode('', $items);
    }

    /**
     * Get status indicator color
     */
    private function getStatusDotColor(string $status): string
    {
        return match($status) {
            'applied' => '#1d4ed8',
            'interview' => '#f59e0b',
            'hired' => '#16a34a',
            'rejected' => '#dc2626',
            default => '#6b7280',
        };
    }

    private function formatStatusLabel(string $value): string
    {
        $normalized = str_replace(['-', '_'], ' ', strtolower(trim($value)));
        if ($normalized === '') {
            return '-';
        }

        return (string) collect(explode(' ', $normalized))
            ->filter()
            ->map(static fn (string $part) => ucfirst($part))
            ->implode(' ');
    }

    /**
     * Prepare text content for work update email
     */
    private function prepareTextContent(User $client, array $workUpdates, $date)
    {
        $text = "Daily Work Update - {$date->format('M j, Y')}\n\n";
        $text .= "Hello {$client->name},\n\n";
        $text .= "Here's your daily work update with " . count($workUpdates) . " new job applications:\n\n";

        foreach ($workUpdates as $index => $update) {
            $text .= ($index + 1) . ". {$update['job_title']} - {$update['company']}\n";
            $text .= "   Status: " . ucfirst($update['application_status']) . "\n";
            $text .= "   Applied Date: " . $this->formatAppliedDateValue($update['applied_date'] ?? null) . "\n";
            $text .= "   Method: " . ucfirst($update['applied_method']) . "\n";

            if (!empty($update['job_link'])) {
                $text .= "   Job Link: {$update['job_link']}\n";
            }

            if (!empty($update['note'])) {
                $text .= "   Note: {$update['note']}\n";
            }

            $text .= "\n";
        }

        $text .= "Thank you for using W-Automation!\n";
        $text .= "This is an automated message. Please do not reply to this email.";

        return $text;
    }

    private function formatAppliedDateValue(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        $normalized = trim((string) ($value ?? ''));

        if ($normalized === '') {
            return '-';
        }

        try {
            return Carbon::parse($normalized)->format('Y-m-d');
        } catch (\Throwable) {
            return (string) preg_replace('/\s+00:00:00$/', '', $normalized);
        }
    }

    /**
     * Test Mailchimp connection
     */
    public function testConnection()
    {
        try {
            $response = Http::withBasicAuth('anystring', $this->apiKey)
                ->get("https://{$this->serverPrefix}.api.mailchimp.com/3.0/ping");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Mailchimp connection test failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get account information
     */
    public function getAccountInfo()
    {
        try {
            $response = Http::withBasicAuth('anystring', $this->apiKey)
                ->get("https://{$this->serverPrefix}.api.mailchimp.com/3.0/");

            if ($response->successful()) {
                return $response->json();
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get Mailchimp account info', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
