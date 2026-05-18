@php
    $generatedAt = now();
    $pageOrientation = 'landscape';
    $notAvailableText = trim((string) \App\Models\CustomizationSetting::getValue('pdf_not_available_text', 'N/A')) ?: 'N/A';
    $replacements = [
        '{client_name}' => $user->name,
        '{client_email}' => $user->email,
        '{record_count}' => (string) $workUpdates->count(),
        '{generated_date}' => $generatedAt->format('F j, Y'),
    ];
    $pdfText = static function (string $key, string $default) use ($replacements): string {
        $value = trim((string) \App\Models\CustomizationSetting::getValue($key, $default));

        if ($value === '') {
            $value = $default;
        }

        return strtr($value, $replacements);
    };
    $documentTitle = $pdfText('pdf_client_work_updates_title', 'Client Work Updates Report');
    $documentSubtitle = $pdfText('pdf_client_work_updates_subtitle', 'Approved work updates for {client_name}.');
    $documentTag = $pdfText('pdf_client_work_updates_tag', 'Client Export');
    $footerNote = $pdfText('pdf_client_work_updates_footer_note', 'Client report for {client_name}.');
    $summaryMetrics = [
        ['label' => $pdfText('pdf_client_work_updates_metric_total_updates_label', 'Total Updates'), 'value' => $workUpdates->count()],
        ['label' => $pdfText('pdf_client_work_updates_metric_applied_label', 'Applied'), 'value' => $workUpdates->where('application_status', \App\Models\WorkUpdate::APPLICATION_STATUS_APPLIED)->count()],
        ['label' => $pdfText('pdf_client_work_updates_metric_interviews_label', 'Interviews'), 'value' => $workUpdates->where('application_status', \App\Models\WorkUpdate::APPLICATION_STATUS_INTERVIEW)->count()],
        ['label' => $pdfText('pdf_client_work_updates_metric_hired_label', 'Hired'), 'value' => $workUpdates->where('application_status', \App\Models\WorkUpdate::APPLICATION_STATUS_HIRED)->count()],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $documentTitle }}</title>
    @include('pdf.partials.theme')
</head>
<body>
    @include('pdf.partials.page-chrome', ['documentTag' => $documentTag, 'footerNote' => $footerNote])

    <div class="section intro-card">
        <table class="two-column-table">
            <tr>
                <td style="width: 64%; padding-right: 16px; vertical-align: top;">
                    <h2 class="panel-title">{{ $pdfText('pdf_client_work_updates_intro_title', 'Client Summary') }}</h2>
                    <p class="panel-copy">{{ $pdfText('pdf_client_work_updates_intro_text', 'This report contains the approved work updates currently available in the client portal, including job details, handling agent, application progress, and any saved references.') }}</p>
                </td>
                <td style="width: 36%; vertical-align: top;">
                    <table class="detail-table">
                        <tr>
                            <td class="detail-label">{{ $pdfText('pdf_client_work_updates_detail_client_label', 'Client') }}</td>
                            <td class="detail-value">{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <td class="detail-label">{{ $pdfText('pdf_client_work_updates_detail_email_label', 'Email') }}</td>
                            <td class="detail-value">{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <td class="detail-label">{{ $pdfText('pdf_client_work_updates_detail_included_records_label', 'Included Records') }}</td>
                            <td class="detail-value">{{ $workUpdates->count() }}</td>
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
        <h2 class="panel-title">{{ $pdfText('pdf_client_work_updates_register_title', 'Approved Work Updates') }}</h2>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 12%;">{{ $pdfText('pdf_client_work_updates_table_date_label', 'Date') }}</th>
                    <th style="width: 16%;">{{ $pdfText('pdf_client_work_updates_table_submitted_by_label', 'Submitted By') }}</th>
                    <th style="width: 21%;">{{ $pdfText('pdf_client_work_updates_table_position_label', 'Position') }}</th>
                    <th style="width: 12%;">{{ $pdfText('pdf_client_work_updates_table_method_label', 'Method') }}</th>
                    <th style="width: 14%;">{{ $pdfText('pdf_client_work_updates_table_progress_label', 'Progress') }}</th>
                    <th style="width: 25%;">{{ $pdfText('pdf_client_work_updates_table_references_label', 'References') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($workUpdates as $update)
                    <tr>
                        <td>
                            <p class="table-title">{{ ($update->applied_date ?? $update->created_at)?->format('M j, Y') ?? $notAvailableText }}</p>
                            <p class="muted">{{ $pdfText('pdf_client_work_updates_prefix_added', 'Added') }} {{ $update->created_at?->format('M j, Y g:i A') ?? $notAvailableText }}</p>
                        </td>
                        <td>
                            <p class="table-title">{{ $update->agent?->name ?? $pdfText('pdf_client_work_updates_assigned_agent_text', 'Assigned Agent') }}</p>
                            <p class="muted">{{ $update->agent?->email ?? $pdfText('pdf_client_work_updates_no_email_text', 'No email on file') }}</p>
                        </td>
                        <td>
                            <p class="table-title">{{ $update->job_title ?: $pdfText('pdf_client_work_updates_untitled_position_text', 'Untitled Position') }}</p>
                            <p class="muted">{{ $update->company ?: $pdfText('pdf_client_work_updates_company_missing_text', 'Company not provided') }}</p>
                        </td>
                        <td>
                            <p class="table-copy">{{ $update->getAppliedMethodLabel() }}</p>
                        </td>
                        <td>
                            <div class="status-stack">
                                <span class="status-badge status-{{ $update->application_status ?: 'draft' }}">{{ $update->getApplicationStatusLabel() }}</span>
                                <span class="status-badge status-{{ $update->status ?: 'approved' }}">{{ $update->getStatusLabel() }}</span>
                            </div>
                        </td>
                        <td>
                            @if($update->job_link)
                                <p class="link-line"><span class="label">{{ $pdfText('pdf_client_work_updates_reference_job_label', 'Job:') }}</span> <span class="word-break">{{ $update->job_link }}</span></p>
                            @endif

                            @if($update->job_success_link)
                                <p class="link-line"><span class="label">{{ $pdfText('pdf_client_work_updates_reference_success_label', 'Success:') }}</span> <span class="word-break">{{ $update->job_success_link }}</span></p>
                            @endif

                            @if($update->note)
                                <p class="note-inline"><span class="label">{{ $pdfText('pdf_client_work_updates_reference_note_label', 'Note:') }}</span> {{ $update->note }}</p>
                            @endif

                            @if(!$update->job_link && !$update->job_success_link && !$update->note)
                                <p class="muted">{{ $pdfText('pdf_client_work_updates_no_references_text', 'No reference links or notes recorded.') }}</p>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <strong>{{ $pdfText('pdf_client_work_updates_empty_title', 'No work updates found') }}</strong>
                                {{ $pdfText('pdf_client_work_updates_empty_text', 'No approved work updates were available for this client when the PDF was generated.') }}
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
