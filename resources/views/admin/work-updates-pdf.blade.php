@php
    $generatedAt = now();
    $pageOrientation = 'landscape';
    $notAvailableText = trim((string) \App\Models\CustomizationSetting::getValue('pdf_not_available_text', 'N/A')) ?: 'N/A';
    $replacements = [
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
    $documentTitle = $pdfText('pdf_admin_work_updates_title', 'Admin Work Updates Report');
    $documentSubtitle = $pdfText('pdf_admin_work_updates_subtitle', 'Filtered application activity across agents and clients.');
    $documentTag = $pdfText('pdf_admin_work_updates_tag', 'Workspace Report');
    $footerNote = $pdfText('pdf_admin_work_updates_footer_note', 'Admin work updates export for {record_count} records.');
    $summaryMetrics = [
        ['label' => $pdfText('pdf_admin_work_updates_metric_total_updates_label', 'Total Updates'), 'value' => $workUpdates->count()],
        ['label' => $pdfText('pdf_admin_work_updates_metric_applied_label', 'Applied'), 'value' => $workUpdates->where('application_status', \App\Models\WorkUpdate::APPLICATION_STATUS_APPLIED)->count()],
        ['label' => $pdfText('pdf_admin_work_updates_metric_interviews_label', 'Interviews'), 'value' => $workUpdates->where('application_status', \App\Models\WorkUpdate::APPLICATION_STATUS_INTERVIEW)->count()],
        ['label' => $pdfText('pdf_admin_work_updates_metric_hired_label', 'Hired'), 'value' => $workUpdates->where('application_status', \App\Models\WorkUpdate::APPLICATION_STATUS_HIRED)->count()],
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
                    <h2 class="panel-title">{{ $pdfText('pdf_admin_work_updates_intro_title', 'Operational Overview') }}</h2>
                    <p class="panel-copy">{{ $pdfText('pdf_admin_work_updates_intro_text', 'This export captures the filtered work updates visible to administrators, including assignment ownership, application progress, supporting links, and recorded notes.') }}</p>
                </td>
                <td style="width: 36%; vertical-align: top;">
                    <table class="detail-table">
                        <tr>
                            <td class="detail-label">{{ $pdfText('pdf_admin_work_updates_detail_included_records_label', 'Included Records') }}</td>
                            <td class="detail-value">{{ $workUpdates->count() }}</td>
                        </tr>
                        <tr>
                            <td class="detail-label">{{ $pdfText('pdf_admin_work_updates_detail_report_scope_label', 'Report Scope') }}</td>
                            <td class="detail-value">{{ $pdfText('pdf_admin_work_updates_detail_report_scope_value', 'Admin workspace export') }}</td>
                        </tr>
                        <tr>
                            <td class="detail-label">{{ $pdfText('pdf_admin_work_updates_detail_application_coverage_label', 'Application Coverage') }}</td>
                            <td class="detail-value">{{ $pdfText('pdf_admin_work_updates_detail_application_coverage_value', 'All filtered agents and clients') }}</td>
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
        <h2 class="panel-title">{{ $pdfText('pdf_admin_work_updates_register_title', 'Work Update Register') }}</h2>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 12%;">{{ $pdfText('pdf_admin_work_updates_table_date_label', 'Date') }}</th>
                    <th style="width: 18%;">{{ $pdfText('pdf_admin_work_updates_table_assignment_label', 'Assignment') }}</th>
                    <th style="width: 19%;">{{ $pdfText('pdf_admin_work_updates_table_position_label', 'Position') }}</th>
                    <th style="width: 12%;">{{ $pdfText('pdf_admin_work_updates_table_method_label', 'Method') }}</th>
                    <th style="width: 14%;">{{ $pdfText('pdf_admin_work_updates_table_status_label', 'Status') }}</th>
                    <th style="width: 25%;">{{ $pdfText('pdf_admin_work_updates_table_references_label', 'References') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($workUpdates as $update)
                    <tr>
                        <td>
                            <p class="table-title">{{ ($update->applied_date ?? $update->created_at)?->format('M j, Y') ?? $notAvailableText }}</p>
                            <p class="muted">{{ $pdfText('pdf_admin_work_updates_prefix_submitted', 'Submitted') }} {{ $update->created_at?->format('M j, Y g:i A') ?? $notAvailableText }}</p>
                        </td>
                        <td>
                            <p class="table-copy"><span class="label">{{ $pdfText('pdf_admin_work_updates_assignment_agent_label', 'Agent:') }}</span> {{ $update->agent?->name ?? $pdfText('pdf_admin_work_updates_unknown_agent_text', 'Unknown Agent') }}</p>
                            <p class="muted"><span class="label">{{ $pdfText('pdf_admin_work_updates_assignment_client_label', 'Client:') }}</span> {{ $update->client?->name ?? $pdfText('pdf_admin_work_updates_unknown_client_text', 'Unknown Client') }}</p>
                        </td>
                        <td>
                            <p class="table-title">{{ $update->job_title ?: $pdfText('pdf_admin_work_updates_untitled_position_text', 'Untitled Position') }}</p>
                            <p class="muted">{{ $update->company ?: $pdfText('pdf_admin_work_updates_company_missing_text', 'Company not provided') }}</p>
                        </td>
                        <td>
                            <p class="table-copy">{{ $update->getAppliedMethodLabel() }}</p>
                        </td>
                        <td>
                            <div class="status-stack">
                                <span class="status-badge status-{{ $update->application_status ?: 'draft' }}">{{ $update->getApplicationStatusLabel() }}</span>
                                <span class="status-badge status-{{ $update->status ?: 'draft' }}">{{ $update->getStatusLabel() }}</span>
                            </div>
                        </td>
                        <td>
                            @if($update->job_link)
                                <p class="link-line"><span class="label">{{ $pdfText('pdf_admin_work_updates_reference_job_label', 'Job:') }}</span> <span class="word-break">{{ $update->job_link }}</span></p>
                            @endif

                            @if($update->job_success_link)
                                <p class="link-line"><span class="label">{{ $pdfText('pdf_admin_work_updates_reference_success_label', 'Success:') }}</span> <span class="word-break">{{ $update->job_success_link }}</span></p>
                            @endif

                            @if($update->note)
                                <p class="note-inline"><span class="label">{{ $pdfText('pdf_admin_work_updates_reference_note_label', 'Note:') }}</span> {{ $update->note }}</p>
                            @endif

                            @if(!$update->job_link && !$update->job_success_link && !$update->note)
                                <p class="muted">{{ $pdfText('pdf_admin_work_updates_no_references_text', 'No reference links or notes recorded.') }}</p>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <strong>{{ $pdfText('pdf_admin_work_updates_empty_title', 'No work updates found') }}</strong>
                                {{ $pdfText('pdf_admin_work_updates_empty_text', 'No filtered work updates were available when this PDF was generated.') }}
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
