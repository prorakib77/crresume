@php
    $generatedAt = now();
    $pageOrientation = 'portrait';
    $notAvailableText = trim((string) \App\Models\CustomizationSetting::getValue('pdf_not_available_text', 'N/A')) ?: 'N/A';
    $defaultPackageText = trim((string) \App\Models\CustomizationSetting::getValue('pdf_onboarding_default_package_text', 'Not provided')) ?: 'Not provided';
    $fallbackSectionTitle = trim((string) \App\Models\CustomizationSetting::getValue('pdf_onboarding_fallback_section_title', 'Additional Details')) ?: 'Additional Details';
    $sections = [];
    $currentSection = 'Submission Overview';
    $sections[$currentSection] = ['items' => [], 'paragraphs' => []];
    $summaryFields = [
        'Full Name' => $client->name,
        'Email Address' => $client->email,
        'Selected Package' => $defaultPackageText,
    ];

    foreach (preg_split("/\r\n|\r|\n/", (string) $profile->onboarding_text) as $rawLine) {
        $line = trim($rawLine);

        if ($line === '') {
            continue;
        }

        if (str_starts_with($line, 'SECTION:')) {
            $currentSection = trim(substr($line, 8)) ?: $fallbackSectionTitle;
            $sections[$currentSection] = $sections[$currentSection] ?? ['items' => [], 'paragraphs' => []];
            continue;
        }

        if (str_contains($line, ':')) {
            [$label, $value] = array_pad(explode(':', $line, 2), 2, '');
            $label = trim($label);
            $value = trim($value);

            if ($value === '') {
                $sections[$currentSection]['paragraphs'][] = $line;
                continue;
            }

            $sections[$currentSection]['items'][] = [
                'label' => $label,
                'value' => $value,
            ];

            if (array_key_exists($label, $summaryFields)) {
                $summaryFields[$label] = $value;
            }

            continue;
        }

        $sections[$currentSection]['paragraphs'][] = $line;
    }

    $sections = array_filter(
        $sections,
        static fn (array $section): bool => !empty($section['items']) || !empty($section['paragraphs'])
    );

    $overviewSection = $sections['Submission Overview'] ?? ['items' => [], 'paragraphs' => []];
    $displaySections = $sections;
    unset($displaySections['Submission Overview']);

    $totalFields = 0;
    $totalParagraphs = 0;

    foreach ($sections as $section) {
        $totalFields += count($section['items']);
        $totalParagraphs += count($section['paragraphs']);
    }

    $attachedFilesCount = collect([
        $profile->onboarding_resume_file,
        $profile->onboarding_form_file,
    ])->filter()->count();

    $replacements = [
        '{client_name}' => $client->name,
        '{client_email}' => $client->email,
        '{package}' => $summaryFields['Selected Package'],
        '{submitted_at}' => $profile->onboarding_submitted_at?->format('M j, Y g:i A') ?? $notAvailableText,
        '{generated_date}' => $generatedAt->format('F j, Y'),
    ];
    $pdfText = static function (string $key, string $default) use ($replacements): string {
        $value = trim((string) \App\Models\CustomizationSetting::getValue($key, $default));

        if ($value === '') {
            $value = $default;
        }

        return strtr($value, $replacements);
    };

    $documentTitle = $pdfText('pdf_onboarding_title', 'Client Onboarding Submission');
    $documentSubtitle = $pdfText('pdf_onboarding_subtitle', 'Structured intake report for {client_name}.');
    $documentTag = $pdfText('pdf_onboarding_tag', 'Onboarding Record');
    $footerNote = $pdfText('pdf_onboarding_footer_note', 'Onboarding submission for {client_name}.');
    $summaryMetrics = [
        ['label' => $pdfText('pdf_onboarding_metric_sections_label', 'Sections'), 'value' => count($displaySections)],
        ['label' => $pdfText('pdf_onboarding_metric_data_fields_label', 'Data Fields'), 'value' => $totalFields],
        ['label' => $pdfText('pdf_onboarding_metric_notes_label', 'Notes'), 'value' => $totalParagraphs + ($profile->onboarding_note ? 1 : 0)],
        ['label' => $pdfText('pdf_onboarding_metric_files_label', 'Files'), 'value' => $attachedFilesCount],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $documentTitle }}</title>
    @include('pdf.partials.theme')
    <style>
        .onboarding-section-block + .onboarding-section-block {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid {{ \App\Models\CustomizationSetting::getValue('pdf_border_color', '#DDE4EE') }};
        }

        .onboarding-section-kicker {
            margin-bottom: 5px;
        }

        .onboarding-section-meta {
            display: inline-block;
            padding: 4px 8px;
            border: 1px solid {{ \App\Models\CustomizationSetting::getValue('pdf_border_color', '#DDE4EE') }};
            border-radius: 999px;
            background: {{ \App\Models\CustomizationSetting::getValue('pdf_panel_background', '#F8FAFC') }};
            color: {{ \App\Models\CustomizationSetting::getValue('pdf_muted_text_color', '#6B7280') }};
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .onboarding-overview-copy {
            margin-top: 10px;
        }

        .onboarding-note-box {
            margin-top: 10px;
            padding: 10px 12px;
            border: 1px solid {{ \App\Models\CustomizationSetting::getValue('pdf_border_color', '#DDE4EE') }};
            border-radius: 10px;
            background: {{ \App\Models\CustomizationSetting::getValue('pdf_table_row_alt_background', '#FBFCFE') }};
        }

        .onboarding-note-title {
            margin: 0 0 6px;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: {{ \App\Models\CustomizationSetting::getValue('pdf_muted_text_color', '#6B7280') }};
        }

        .onboarding-note-box .paragraph-list {
            margin-top: 0;
        }

        .onboarding-empty-copy {
            margin-top: 6px;
        }
    </style>
</head>
<body>
    @include('pdf.partials.page-chrome', ['documentTag' => $documentTag, 'footerNote' => $footerNote])

    <div class="section intro-card">
        <table class="two-column-table">
            <tr>
                <td style="width: 60%; padding-right: 16px; vertical-align: top;">
                    <h2 class="panel-title">{{ $pdfText('pdf_onboarding_intro_title', 'Submission Overview') }}</h2>
                    <p class="panel-copy">{{ $pdfText('pdf_onboarding_intro_text', 'This report captures the onboarding details submitted by the client in a structured format, matching the internal review style used across the workspace export reports.') }}</p>

                    @if(!empty($overviewSection['paragraphs']))
                        <ul class="paragraph-list onboarding-overview-copy">
                            @foreach($overviewSection['paragraphs'] as $paragraph)
                                <li>{{ $paragraph }}</li>
                            @endforeach
                        </ul>
                    @endif

                    @if(!empty($overviewSection['items']))
                        <table class="detail-table" style="margin-top: 10px;">
                            @foreach($overviewSection['items'] as $item)
                                <tr>
                                    <td class="detail-label">{{ $item['label'] }}:</td>
                                    <td class="detail-value">{{ $item['value'] }}</td>
                                </tr>
                            @endforeach
                        </table>
                    @endif
                </td>
                <td style="width: 40%; vertical-align: top;">
                    <table class="detail-table">
                        <tr>
                            <td class="detail-label">{{ $pdfText('pdf_onboarding_detail_client_label', 'Client') }}</td>
                            <td class="detail-value">{{ $summaryFields['Full Name'] }}</td>
                        </tr>
                        <tr>
                            <td class="detail-label">{{ $pdfText('pdf_onboarding_detail_email_label', 'Email') }}</td>
                            <td class="detail-value">{{ $summaryFields['Email Address'] }}</td>
                        </tr>
                        <tr>
                            <td class="detail-label">{{ $pdfText('pdf_onboarding_detail_package_label', 'Package') }}</td>
                            <td class="detail-value">{{ $summaryFields['Selected Package'] }}</td>
                        </tr>
                        <tr>
                            <td class="detail-label">{{ $pdfText('pdf_onboarding_detail_submitted_label', 'Submitted') }}</td>
                            <td class="detail-value">{{ $profile->onboarding_submitted_at?->format('M j, Y g:i A') ?? $notAvailableText }}</td>
                        </tr>
                        <tr>
                            <td class="detail-label">{{ $pdfText('pdf_onboarding_detail_resume_file_label', 'Resume File') }}</td>
                            <td class="detail-value">{{ $profile->onboarding_resume_file ? $pdfText('pdf_onboarding_status_received_text', 'Received') : $pdfText('pdf_onboarding_status_not_uploaded_text', 'Not uploaded') }}</td>
                        </tr>
                        <tr>
                            <td class="detail-label">{{ $pdfText('pdf_onboarding_detail_form_file_label', 'Form File') }}</td>
                            <td class="detail-value">{{ $profile->onboarding_form_file ? $pdfText('pdf_onboarding_status_received_text', 'Received') : $pdfText('pdf_onboarding_status_not_uploaded_text', 'Not uploaded') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table class="metrics-table">
            <tr>
                @foreach($summaryMetrics as $metric)
                    <td>
                        <div class="summary-card">
                            <p class="summary-value">{{ $metric['value'] }}</p>
                            <p class="summary-label">{{ $metric['label'] }}</p>
                        </div>
                    </td>
                @endforeach
            </tr>
        </table>
    </div>

    <div class="section panel">
        <h2 class="panel-title">{{ $pdfText('pdf_onboarding_register_title', 'Onboarding Register') }}</h2>

        @if(!empty($displaySections))
            @foreach($displaySections as $sectionTitle => $section)
                <div class="onboarding-section-block">
                    <table class="two-column-table">
                        <tr>
                            <td style="vertical-align: top;">
                                <p class="header-kicker onboarding-section-kicker">{{ $pdfText('pdf_onboarding_section_prefix', 'Section') }} {{ $loop->iteration }}</p>
                                <h3 class="section-heading" style="margin-bottom: 0;">{{ $sectionTitle }}</h3>
                            </td>
                            <td style="width: 120px; text-align: right; vertical-align: top;">
                                <span class="onboarding-section-meta">{{ count($section['items']) }} {{ $pdfText('pdf_onboarding_section_fields_suffix', 'fields') }}</span>
                            </td>
                        </tr>
                    </table>

                    @if(!empty($section['items']))
                        <table class="detail-table" style="margin-top: 10px;">
                            @foreach($section['items'] as $item)
                                <tr>
                                    <td class="detail-label">{{ $item['label'] }}:</td>
                                    <td class="detail-value">{{ $item['value'] }}</td>
                                </tr>
                            @endforeach
                        </table>
                    @endif

                    @if(!empty($section['paragraphs']))
                        <div class="onboarding-note-box">
                            <p class="onboarding-note-title">{{ $pdfText('pdf_onboarding_section_notes_title', 'Section Notes') }}</p>
                            <ul class="paragraph-list">
                                @foreach($section['paragraphs'] as $paragraph)
                                    <li>{{ $paragraph }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <div class="empty-state">
                <strong>{{ $pdfText('pdf_onboarding_empty_title', 'No onboarding sections found') }}</strong>
                <p class="onboarding-empty-copy">{{ $pdfText('pdf_onboarding_empty_text', 'This submission did not include any structured onboarding sections.') }}</p>
            </div>
        @endif
    </div>

    @if($profile->onboarding_note)
        <div class="section note-box">
            <h2 class="panel-title">{{ $pdfText('pdf_onboarding_client_note_title', 'Client Note') }}</h2>
            <p class="paragraph">{{ $profile->onboarding_note }}</p>
        </div>
    @endif
</body>
</html>
