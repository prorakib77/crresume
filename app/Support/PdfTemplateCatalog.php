<?php

namespace App\Support;

use Illuminate\Support\Str;

class PdfTemplateCatalog
{
    public static function all(): array
    {
        return [
            'admin-work-updates' => static::template(
                key: 'admin-work-updates',
                name: 'Admin Work Updates PDF',
                description: 'Static copy for the admin work updates export.',
                tokens: ['{record_count}', '{generated_date}'],
                prefix: 'pdf_admin_work_updates_',
                suffixes: [
                    'tag',
                    'title',
                    'subtitle',
                    'intro_title',
                    'intro_text',
                    'metric_total_updates_label',
                    'metric_applied_label',
                    'metric_interviews_label',
                    'metric_hired_label',
                    'detail_included_records_label',
                    'detail_report_scope_label',
                    'detail_report_scope_value',
                    'detail_application_coverage_label',
                    'detail_application_coverage_value',
                    'register_title',
                    'table_date_label',
                    'table_assignment_label',
                    'table_position_label',
                    'table_method_label',
                    'table_status_label',
                    'table_references_label',
                    'prefix_submitted',
                    'assignment_agent_label',
                    'assignment_client_label',
                    'unknown_agent_text',
                    'unknown_client_text',
                    'untitled_position_text',
                    'company_missing_text',
                    'reference_job_label',
                    'reference_success_label',
                    'reference_note_label',
                    'no_references_text',
                    'empty_title',
                    'empty_text',
                    'footer_note',
                ],
                textareaRows: [
                    'subtitle' => 2,
                    'intro_text' => 4,
                    'no_references_text' => 2,
                    'empty_text' => 3,
                    'footer_note' => 2,
                ],
            ),
            'agent-work-updates' => static::template(
                key: 'agent-work-updates',
                name: 'Agent Work Updates PDF',
                description: 'Static copy for the agent work updates export.',
                tokens: ['{agent_name}', '{record_count}', '{generated_date}'],
                prefix: 'pdf_agent_work_updates_',
                suffixes: [
                    'tag',
                    'title',
                    'subtitle',
                    'intro_title',
                    'intro_text',
                    'metric_total_updates_label',
                    'metric_applied_label',
                    'metric_interviews_label',
                    'metric_hired_label',
                    'detail_agent_label',
                    'detail_included_records_label',
                    'detail_report_scope_label',
                    'detail_report_scope_value',
                    'register_title',
                    'table_date_label',
                    'table_client_label',
                    'table_position_label',
                    'table_method_label',
                    'table_status_label',
                    'table_references_label',
                    'prefix_submitted',
                    'unknown_client_text',
                    'no_email_text',
                    'untitled_position_text',
                    'company_missing_text',
                    'reference_job_label',
                    'reference_success_label',
                    'reference_note_label',
                    'no_references_text',
                    'empty_title',
                    'empty_text',
                    'footer_note',
                ],
                textareaRows: [
                    'subtitle' => 2,
                    'intro_text' => 4,
                    'no_references_text' => 2,
                    'empty_text' => 3,
                    'footer_note' => 2,
                ],
            ),
            'client-work-updates' => static::template(
                key: 'client-work-updates',
                name: 'Client Work Updates PDF',
                description: 'Static copy for the client work updates export.',
                tokens: ['{client_name}', '{client_email}', '{record_count}', '{generated_date}'],
                prefix: 'pdf_client_work_updates_',
                suffixes: [
                    'tag',
                    'title',
                    'subtitle',
                    'intro_title',
                    'intro_text',
                    'metric_total_updates_label',
                    'metric_applied_label',
                    'metric_interviews_label',
                    'metric_hired_label',
                    'detail_client_label',
                    'detail_email_label',
                    'detail_included_records_label',
                    'register_title',
                    'table_date_label',
                    'table_submitted_by_label',
                    'table_position_label',
                    'table_method_label',
                    'table_progress_label',
                    'table_references_label',
                    'prefix_added',
                    'assigned_agent_text',
                    'no_email_text',
                    'untitled_position_text',
                    'company_missing_text',
                    'reference_job_label',
                    'reference_success_label',
                    'reference_note_label',
                    'no_references_text',
                    'empty_title',
                    'empty_text',
                    'footer_note',
                ],
                textareaRows: [
                    'subtitle' => 2,
                    'intro_text' => 4,
                    'no_references_text' => 2,
                    'empty_text' => 3,
                    'footer_note' => 2,
                ],
            ),
            'onboarding' => static::template(
                key: 'onboarding',
                name: 'Onboarding PDF',
                description: 'Static copy for the client onboarding submission export.',
                tokens: ['{client_name}', '{client_email}', '{package}', '{submitted_at}', '{generated_date}'],
                prefix: 'pdf_onboarding_',
                suffixes: [
                    'tag',
                    'title',
                    'subtitle',
                    'intro_title',
                    'intro_text',
                    'metric_sections_label',
                    'metric_data_fields_label',
                    'metric_notes_label',
                    'metric_files_label',
                    'detail_client_label',
                    'detail_email_label',
                    'detail_package_label',
                    'detail_submitted_label',
                    'detail_resume_file_label',
                    'detail_form_file_label',
                    'status_received_text',
                    'status_not_uploaded_text',
                    'register_title',
                    'section_prefix',
                    'section_fields_suffix',
                    'section_notes_title',
                    'client_note_title',
                    'empty_title',
                    'empty_text',
                    'footer_note',
                    'fallback_section_title',
                    'default_package_text',
                ],
                textareaRows: [
                    'subtitle' => 2,
                    'intro_text' => 4,
                    'empty_text' => 3,
                    'footer_note' => 2,
                ],
            ),
        ];
    }

    public static function ordered(): array
    {
        return array_values(static::all());
    }

    public static function find(string $key): ?array
    {
        return static::all()[$key] ?? null;
    }

    private static function template(
        string $key,
        string $name,
        string $description,
        array $tokens,
        string $prefix,
        array $suffixes,
        array $textareaRows = [],
    ): array {
        return [
            'key' => $key,
            'name' => $name,
            'description' => $description,
            'tokens' => $tokens,
            'fields' => static::makeFields($prefix, $suffixes, $textareaRows),
        ];
    }

    private static function makeFields(string $prefix, array $suffixes, array $textareaRows = []): array
    {
        return array_map(function (string $suffix) use ($prefix, $textareaRows) {
            $isTextarea = array_key_exists($suffix, $textareaRows);

            return [
                'key' => $prefix . $suffix,
                'suffix' => $suffix,
                'label' => static::labelForSuffix($suffix),
                'type' => $isTextarea ? 'textarea' : 'text',
                'rows' => $textareaRows[$suffix] ?? null,
                'max' => $isTextarea ? 5000 : 500,
            ];
        }, $suffixes);
    }

    private static function labelForSuffix(string $suffix): string
    {
        return match (true) {
            $suffix === 'tag' => 'Header Tag',
            $suffix === 'title' => 'Document Title',
            $suffix === 'subtitle' => 'Document Subtitle',
            $suffix === 'intro_title' => 'Intro Title',
            $suffix === 'intro_text' => 'Intro Text',
            $suffix === 'register_title' => 'Register Title',
            $suffix === 'footer_note' => 'Footer Note',
            $suffix === 'empty_title' => 'Empty State Title',
            $suffix === 'empty_text' => 'Empty State Text',
            str_starts_with($suffix, 'metric_') && str_ends_with($suffix, '_label') => 'Metric Label: ' . static::titleSegment(Str::before(Str::after($suffix, 'metric_'), '_label')),
            str_starts_with($suffix, 'detail_') && str_ends_with($suffix, '_label') => 'Detail Label: ' . static::titleSegment(Str::before(Str::after($suffix, 'detail_'), '_label')),
            str_starts_with($suffix, 'detail_') && str_ends_with($suffix, '_value') => 'Detail Value: ' . static::titleSegment(Str::before(Str::after($suffix, 'detail_'), '_value')),
            str_starts_with($suffix, 'table_') && str_ends_with($suffix, '_label') => 'Table Header: ' . static::titleSegment(Str::before(Str::after($suffix, 'table_'), '_label')),
            str_starts_with($suffix, 'reference_') && str_ends_with($suffix, '_label') => 'Reference Label: ' . static::titleSegment(Str::before(Str::after($suffix, 'reference_'), '_label')),
            str_starts_with($suffix, 'assignment_') && str_ends_with($suffix, '_label') => 'Assignment Label: ' . static::titleSegment(Str::before(Str::after($suffix, 'assignment_'), '_label')),
            str_starts_with($suffix, 'prefix_') => 'Row Prefix: ' . static::titleSegment(Str::after($suffix, 'prefix_')),
            str_starts_with($suffix, 'status_') && str_ends_with($suffix, '_text') => 'Status Text: ' . static::titleSegment(Str::before(Str::after($suffix, 'status_'), '_text')),
            in_array($suffix, ['no_references_text', 'no_email_text', 'unknown_agent_text', 'unknown_client_text', 'assigned_agent_text', 'untitled_position_text', 'company_missing_text', 'fallback_section_title', 'default_package_text'], true)
                => 'Fallback Text: ' . static::titleSegment(Str::replaceLast('_text', '', $suffix)),
            $suffix === 'section_prefix' => 'Section Prefix',
            $suffix === 'section_fields_suffix' => 'Section Count Suffix',
            $suffix === 'section_notes_title' => 'Section Notes Title',
            $suffix === 'client_note_title' => 'Client Note Title',
            default => static::titleSegment($suffix),
        };
    }

    private static function titleSegment(string $value): string
    {
        return (string) Str::of($value)
            ->replace('_', ' ')
            ->replace('Otp', 'OTP')
            ->replace('Pdf', 'PDF')
            ->title();
    }
}
