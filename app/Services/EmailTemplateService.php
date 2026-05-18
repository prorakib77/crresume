<?php

namespace App\Services;

use App\Mail\DynamicTemplateMail;
use App\Models\CustomizationSetting;
use App\Models\EmailTemplate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class EmailTemplateService
{
    public function render(
        string $templateKey,
        array $variables = [],
        ?string $fallbackSubject = null,
        ?string $fallbackBody = null
    ): array {
        $this->syncDefaultsIfReady();

        $template = $this->getTemplate($templateKey);
        if ($template && !$template->is_active) {
            $template = null;
        }

        $subjectTemplate = trim((string) ($template?->subject_template ?? ''));
        if ($subjectTemplate === '') {
            $subjectTemplate = $fallbackSubject ?: EmailTemplate::defaultSubject($templateKey);
        }

        $bodyTemplate = trim((string) ($template?->body_template ?? ''));
        if ($bodyTemplate === '') {
            $bodyTemplate = $fallbackBody ?: EmailTemplate::defaultBody($templateKey);
        }

        $context = $this->normalizeContext($variables);
        $context['content_note'] = $context['content_note'] ?? $this->resolveContentNote($template, $templateKey);

        $compiledSubject = $this->compile($subjectTemplate, $context);
        $compiledBody = $this->compile($bodyTemplate, $context);

        return [
            'subject' => $compiledSubject,
            'body' => $this->formatBody($compiledBody, $compiledSubject, (string) $context['site_name'], $templateKey, $template),
        ];
    }

    public function sendTemplate(
        string $templateKey,
        string $toEmail,
        ?string $toName,
        array $variables = [],
        array $options = []
    ): void {
        $template = $this->getTemplate($templateKey);
        if ($template && !$template->is_active) {
            $template = null;
        }

        $rendered = $this->render(
            $templateKey,
            $variables,
            $options['subject_fallback'] ?? null,
            $options['body_fallback'] ?? null
        );

        $fromEmail = array_key_exists('from_email', $options)
            ? $options['from_email']
            : ($template?->from_email ?: config('mail.from.address'));

        $fromName = array_key_exists('from_name', $options)
            ? $options['from_name']
            : ($template?->from_name ?: config('mail.from.name', 'Atswfhresumes'));

        $mailable = new DynamicTemplateMail(
            $rendered['subject'],
            $rendered['body'],
            $fromEmail,
            $fromName,
            $options['reply_to_email'] ?? null,
            $options['reply_to_name'] ?? null
        );

        Mail::to($toEmail, $toName)->send($mailable);
    }

    public function getTemplate(string $templateKey): ?EmailTemplate
    {
        $this->syncDefaultsIfReady();

        if (!Schema::hasTable('email_templates')) {
            return null;
        }

        return EmailTemplate::query()
            ->where('template_key', $templateKey)
            ->first();
    }

    private function syncDefaultsIfReady(): void
    {
        if (!Schema::hasTable('email_templates')) {
            return;
        }

        EmailTemplate::syncDefaults();
    }

    private function normalizeContext(array $variables): array
    {
        $context = [];

        foreach ($variables as $key => $value) {
            if (is_array($value)) {
                $context[$key] = implode(', ', array_map(static fn ($item) => (string) $item, $value));
                continue;
            }

            if ($value instanceof Carbon) {
                $context[$key] = $value->format('M d, Y h:i A');
                continue;
            }

            if ($value instanceof \DateTimeInterface) {
                $context[$key] = Carbon::instance($value)->format('M d, Y h:i A');
                continue;
            }

            if (is_null($value)) {
                $context[$key] = '';
                continue;
            }

            if (is_bool($value)) {
                $context[$key] = $value ? 'Yes' : 'No';
                continue;
            }

            $context[$key] = (string) $value;
        }

        $context['site_name'] = $context['site_name'] ?? site_name();
        $context['current_date'] = $context['current_date'] ?? now()->format('M d, Y');
        $context['current_time'] = $context['current_time'] ?? now()->format('h:i A');
        $context['current_datetime'] = $context['current_datetime'] ?? now()->format('M d, Y h:i A');

        return $context;
    }

    private function compile(string $template, array $context): string
    {
        $replacements = [];
        foreach ($context as $key => $value) {
            $replacements['{{' . $key . '}}'] = $value;
        }

        $compiled = strtr($template, $replacements);

        return (string) preg_replace('/\{\{\s*[a-zA-Z0-9_\-]+\s*\}\}/', '', $compiled);
    }

    private function formatBody(string $body, string $subject, string $siteName, string $templateKey, ?EmailTemplate $template): string
    {
        $trimmed = $this->stripLetterSpacing(trim($body));
        if ($trimmed === '') {
            return '';
        }

        if (preg_match('/<\s*(?:!doctype|html|body)\b/i', $trimmed) === 1) {
            return $trimmed;
        }

        $trimmed = (string) preg_replace('/^\s*<h[1-4][^>]*>.*?<\/h[1-4]>\s*/is', '', $trimmed, 1);
        $trimmed = $this->decorateCallToActionLinks($trimmed);

        $accentColor = $this->sanitizeHexColor(
            (string) CustomizationSetting::getValue('accent_color', '#C8A45D'),
            '#C8A45D'
        );

        $emailLogoUrl = (string) (CustomizationSetting::getAssetUrl('email_header_logo', '') ?: '');

        if ($emailLogoUrl === '') {
            $emailLogoUrl = trim((string) CustomizationSetting::getValue('email_header_logo_url', ''));
            if ($emailLogoUrl !== '' && !preg_match('~^(?:https?:)?//|^data:~i', $emailLogoUrl)) {
                $emailLogoUrl = url('/' . ltrim($emailLogoUrl, '/'));
            }
        }

        if ($emailLogoUrl === '') {
            $emailLogoUrl = (string) (site_logo('') ?: '');
        }

        $emailHeaderBackgroundUrl = (string) (CustomizationSetting::getAssetUrl('email_header_bg_image', '') ?: '');
        if ($emailHeaderBackgroundUrl === '') {
            $emailHeaderBackgroundUrl = trim((string) CustomizationSetting::getValue('email_header_bg_image_url', ''));

            if ($emailHeaderBackgroundUrl !== '' && !preg_match('~^(?:https?:)?//|^data:~i', $emailHeaderBackgroundUrl)) {
                $emailHeaderBackgroundUrl = url('/' . ltrim($emailHeaderBackgroundUrl, '/'));
            }
        }

        $emailFooterNote = $this->resolveFooterNote($template, $templateKey);

        $safeSiteName = htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8');
        $safeSubject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
        $safeAccentColor = htmlspecialchars($accentColor, ENT_QUOTES, 'UTF-8');
        $safeFooterNote = htmlspecialchars($emailFooterNote, ENT_QUOTES, 'UTF-8');

        $headerStyle = 'background-color:#111111;';
        if ($emailHeaderBackgroundUrl !== '') {
            $safeHeaderBackgroundUrl = htmlspecialchars($emailHeaderBackgroundUrl, ENT_QUOTES, 'UTF-8');
            $headerStyle .= "background-image:linear-gradient(135deg, rgba(0, 0, 0, 0.78), rgba(0, 0, 0, 0.68)), url('{$safeHeaderBackgroundUrl}');";
            $headerStyle .= 'background-size:cover;background-position:center;background-repeat:no-repeat;';
        } else {
            $headerStyle .= 'background-image:radial-gradient(circle at 20% 20%, rgba(255,255,255,0.12), transparent 42%),radial-gradient(circle at 80% 30%, rgba(255,255,255,0.08), transparent 38%),linear-gradient(135deg,#1b1b1b,#000000);';
        }

        $logoMarkup = '';
        if ($emailLogoUrl !== '') {
            $safeLogoUrl = htmlspecialchars($emailLogoUrl, ENT_QUOTES, 'UTF-8');
            $logoMarkup = "<img src=\"{$safeLogoUrl}\" alt=\"{$safeSiteName}\" class=\"mail-banner-logo\">";
        } else {
            $logoMarkup = "<div class=\"mail-banner-brand\">{$safeSiteName}</div>";
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$safeSubject}</title>
    <style>
        :root {
            --mail-bg: transparent;
            --mail-card: #ffffff;
            --mail-text: #111111;
            --mail-muted: #6a6a73;
            --mail-accent: {$safeAccentColor};
            --mail-line: #d8d9de;
        }

        * {
            box-sizing: border-box;
            letter-spacing: normal !important;
        }

        body {
            margin: 0;
            padding: 0;
            background: transparent;
            color: var(--mail-text);
            font-family: Poppins, "Segoe UI", Arial, sans-serif;
        }

        .mail-shell {
            width: 100%;
            margin: 0 auto;
            min-height: 100vh;
            background: transparent;
        }

        .mail-banner {
            min-height: 148px;
            width: 100%;
            padding: 26px 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .mail-banner-logo {
            max-width: min(240px, 72vw);
            max-height: 78px;
            width: auto;
            height: auto;
            display: block;
            margin-left: auto;
            margin-right: auto;
            object-fit: contain;
            filter: drop-shadow(0 8px 20px rgba(0,0,0,0.45));
        }

        .mail-banner-brand {
            color: #ffffff;
            font-size: 30px;
            line-height: 1.1;
            letter-spacing: 0.02em;
            font-weight: 700;
            text-align: center;
        }

        .mail-content-wrap {
            width: 100%;
            max-width: 760px;
            margin: 0 auto;
            padding: 22px 16px 8px;
        }

        .mail-card {
            background: var(--mail-card);
            border: 1px solid var(--mail-line);
            border-radius: 0;
            padding: 28px 24px 16px;
        }

        .mail-title {
            margin: 0 0 18px;
            color: var(--mail-text);
            font-size: 40px;
            line-height: 1.2;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .mail-body {
            font-size: 14px;
            line-height: 1.7;
            color: var(--mail-text);
        }

        .mail-body p { margin: 0 0 12px; }

        .mail-body a {
            display: inline;
            margin: 0;
            padding: 0;
            background: transparent;
            color: var(--mail-accent);
            text-decoration: underline;
            font-weight: 600;
            border-radius: 0;
            line-height: inherit;
        }

        .mail-body a:hover {
            background: transparent;
            color: var(--mail-accent);
            text-decoration: underline;
        }

        .mail-body a.mail-cta {
            display: inline-block;
            margin-top: 10px;
            background: #111111;
            color: #ffffff !important;
            text-decoration: none;
            font-weight: 600;
            padding: 11px 18px;
            border-radius: 6px;
            line-height: 1.2;
        }

        .mail-body a.mail-cta:hover {
            background: #000000;
            color: #ffffff !important;
            text-decoration: none;
        }

        .mail-body a[href^="mailto:"],
        .mail-body a[href^="tel:"],
        .mail-body a[x-apple-data-detectors],
        .mail-body u + #body a {
            display: inline !important;
            margin: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            color: var(--mail-text) !important;
            text-decoration: underline !important;
            border-radius: 0 !important;
            line-height: inherit !important;
            font-weight: inherit !important;
        }

        .mail-body strong,
        .mail-body .highlight,
        .mail-body .accent {
            color: var(--mail-accent);
        }

        .mail-body h1,
        .mail-body h2,
        .mail-body h3,
        .mail-body h4 {
            margin: 0 0 14px;
            color: var(--mail-text);
            line-height: 1.35;
        }

        .mail-foot {
            width: 100%;
            max-width: 760px;
            margin: 0 auto;
            padding: 14px 16px 34px;
            font-size: 12px;
            color: var(--mail-muted);
            text-align: center;
        }

        .mail-foot-inner {
            border-top: 1px solid rgba(17, 17, 17, 0.12);
            padding-top: 12px;
        }

        @media (max-width: 640px) {
            .mail-banner {
                min-height: 124px;
                padding: 20px 16px;
            }

            .mail-banner-logo {
                max-height: 62px;
                max-width: min(210px, 76vw);
            }

            .mail-content-wrap {
                padding: 14px 10px 6px;
            }

            .mail-card {
                padding: 20px 14px 10px;
            }

            .mail-title {
                font-size: 29px;
                margin-bottom: 14px;
            }

            .mail-foot {
                padding: 10px 10px 24px;
            }
        }
    </style>
</head>
<body>
    <div class="mail-shell">
        <div class="mail-banner" style="{$headerStyle}">
            {$logoMarkup}
        </div>
        <div class="mail-content-wrap">
            <div class="mail-card">
                <h1 class="mail-title">{$safeSubject}</h1>
                <div class="mail-body">
                    {$trimmed}
                </div>
            </div>
        </div>
        <div class="mail-foot">
            <div class="mail-foot-inner">{$safeFooterNote}</div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function sanitizeHexColor(string $color, string $fallback): string
    {
        return preg_match('/^#[0-9A-Fa-f]{6}$/', $color) === 1 ? strtoupper($color) : $fallback;
    }

    private function stripLetterSpacing(string $html): string
    {
        return (string) preg_replace('/letter-spacing\s*:\s*[^;"}]+;?/i', '', $html);
    }

    private function decorateCallToActionLinks(string $html): string
    {
        $decorated = preg_replace_callback('/<a\b([^>]*)>/i', function (array $matches): string {
            $attributes = $matches[1] ?? '';

            if (preg_match('/\bhref\s*=\s*([\'"])(.*?)\1/i', $attributes, $hrefMatch)) {
                $href = strtolower(trim((string) ($hrefMatch[2] ?? '')));

                if (str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
                    return '<a' . $attributes . '>';
                }
            }

            if (preg_match('/\bclass\s*=\s*([\'"])(.*?)\1/i', $attributes, $classMatch, PREG_OFFSET_CAPTURE)) {
                $existingClasses = trim((string) ($classMatch[2][0] ?? ''));

                if (preg_match('/\bmail-cta\b/i', $existingClasses)) {
                    return '<a' . $attributes . '>';
                }

                $fullMatch = $classMatch[0][0];
                $fullOffset = $classMatch[0][1];
                $quote = $classMatch[1][0];
                $replacement = 'class=' . $quote . trim($existingClasses . ' mail-cta') . $quote;

                $attributes = substr_replace($attributes, $replacement, $fullOffset, strlen($fullMatch));

                return '<a' . $attributes . '>';
            }

            return '<a class="mail-cta"' . $attributes . '>';
        }, $html);

        return $decorated ?? $html;
    }

    private function resolveFooterNote(?EmailTemplate $template, string $templateKey): string
    {
        if ($template && $template->footer_note !== null) {
            return trim((string) $template->footer_note);
        }

        $defaultFooterNote = EmailTemplate::defaultFooterNote($templateKey);
        if ($defaultFooterNote !== null) {
            return trim((string) $defaultFooterNote);
        }

        return trim((string) CustomizationSetting::getValue('email_footer_note', 'This is an automated email.'));
    }

    private function resolveContentNote(?EmailTemplate $template, string $templateKey): string
    {
        if ($template && $template->content_note !== null) {
            return trim((string) $template->content_note);
        }

        return trim((string) (EmailTemplate::defaultContentNote($templateKey) ?? ''));
    }
}
