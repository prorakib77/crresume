@php
    $pageOrientation = $pageOrientation ?? 'portrait';
    $pdfAccent = \App\Models\CustomizationSetting::getValue('pdf_accent_color', '#9A7B3F');
    $pdfHeading = \App\Models\CustomizationSetting::getValue('pdf_heading_color', '#111827');
    $pdfBody = \App\Models\CustomizationSetting::getValue('pdf_body_text_color', '#374151');
    $pdfMuted = \App\Models\CustomizationSetting::getValue('pdf_muted_text_color', '#6B7280');
    $pdfBorder = \App\Models\CustomizationSetting::getValue('pdf_border_color', '#DDE4EE');
    $pdfPanelBackground = \App\Models\CustomizationSetting::getValue('pdf_panel_background', '#F8FAFC');
    $pdfTableHeaderBackground = \App\Models\CustomizationSetting::getValue('pdf_table_header_background', '#111827');
    $pdfTableHeaderText = \App\Models\CustomizationSetting::getValue('pdf_table_header_text_color', '#FFFFFF');
    $pdfTableAltBackground = \App\Models\CustomizationSetting::getValue('pdf_table_row_alt_background', '#FBFCFE');
    $pdfBrandMarkBackground = \App\Models\CustomizationSetting::getValue('pdf_brand_mark_background', '#111827');
    $pdfBrandMarkText = \App\Models\CustomizationSetting::getValue('pdf_brand_mark_text_color', '#FFFFFF');
@endphp
<style>
    @page {
        size: A4 {{ $pageOrientation }};
        margin: 120px 32px 60px;
    }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
        line-height: 1.5;
        color: {{ $pdfBody }};
    }

    * {
        box-sizing: border-box;
    }

    a {
        color: {{ $pdfAccent }};
    }

    .pdf-header {
        position: fixed;
        top: -95px;
        left: 0;
        right: 0;
        height: 82px;
        padding-bottom: 12px;
        border-bottom: 1px solid {{ $pdfBorder }};
    }

    .pdf-footer {
        position: fixed;
        bottom: -40px;
        left: 0;
        right: 0;
        height: 28px;
        padding-top: 8px;
        border-top: 1px solid {{ $pdfBorder }};
        font-size: 9px;
        color: {{ $pdfMuted }};
    }

    .header-table,
    .footer-table,
    .metrics-table,
    .two-column-table,
    .detail-table,
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .brand-logo {
        max-width: 160px;
        max-height: 46px;
        width: auto;
        height: auto;
    }

    .brand-mark {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: {{ $pdfBrandMarkBackground }};
        color: {{ $pdfBrandMarkText }};
        text-align: center;
        line-height: 48px;
        font-size: 18px;
        font-weight: bold;
    }

    .header-kicker {
        margin: 0 0 4px;
        font-size: 9px;
        font-weight: bold;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: {{ $pdfAccent }};
    }

    .header-title {
        margin: 0 0 4px;
        font-size: 19px;
        font-weight: bold;
        color: {{ $pdfHeading }};
    }

    .header-subtitle {
        margin: 0;
        font-size: 10px;
        color: {{ $pdfMuted }};
    }

    .header-meta {
        text-align: right;
        font-size: 10px;
        color: {{ $pdfMuted }};
    }

    .header-meta strong {
        color: {{ $pdfHeading }};
    }

    .footer-table td:last-child {
        text-align: right;
    }

    .section {
        margin-bottom: 18px;
    }

    .intro-card,
    .panel,
    .summary-card,
    .note-box,
    .empty-state {
        background: {{ $pdfPanelBackground }};
        border: 1px solid {{ $pdfBorder }};
        border-radius: 10px;
    }

    .intro-card,
    .panel,
    .note-box,
    .empty-state {
        padding: 14px 16px;
    }

    .panel-title {
        margin: 0 0 8px;
        font-size: 14px;
        font-weight: bold;
        color: {{ $pdfHeading }};
    }

    .panel-copy,
    .paragraph {
        margin: 0;
        color: {{ $pdfBody }};
    }

    .metrics-table td {
        width: 25%;
        padding-right: 10px;
        vertical-align: top;
    }

    .metrics-table td:last-child {
        padding-right: 0;
    }

    .summary-card {
        padding: 12px 10px;
        text-align: center;
    }

    .summary-value {
        margin: 0 0 4px;
        font-size: 18px;
        font-weight: bold;
        color: {{ $pdfHeading }};
    }

    .summary-label {
        margin: 0;
        font-size: 9px;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: {{ $pdfMuted }};
    }

    .detail-table td {
        padding: 7px 0;
        vertical-align: top;
        border-bottom: 1px solid {{ $pdfBorder }};
    }

    .detail-table tr:last-child td {
        border-bottom: none;
    }

    .detail-label {
        width: 34%;
        padding-right: 12px;
        font-weight: bold;
        color: {{ $pdfHeading }};
    }

    .detail-value {
        color: {{ $pdfBody }};
    }

    .data-table thead {
        display: table-header-group;
    }

    .data-table tr {
        page-break-inside: avoid;
    }

    .data-table th {
        padding: 10px 9px;
        border: 1px solid {{ $pdfTableHeaderBackground }};
        background: {{ $pdfTableHeaderBackground }};
        color: {{ $pdfTableHeaderText }};
        text-align: left;
        font-size: 9px;
        font-weight: bold;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .data-table td {
        padding: 10px 9px;
        border: 1px solid {{ $pdfBorder }};
        vertical-align: top;
        background: #ffffff;
    }

    .data-table tbody tr:nth-child(even) td {
        background: {{ $pdfTableAltBackground }};
    }

    .table-title {
        margin: 0 0 3px;
        font-size: 11px;
        font-weight: bold;
        color: {{ $pdfHeading }};
    }

    .table-copy,
    .muted,
    .link-line,
    .note-inline {
        margin: 0 0 4px;
        font-size: 10px;
        color: {{ $pdfBody }};
    }

    .muted:last-child,
    .link-line:last-child,
    .note-inline:last-child {
        margin-bottom: 0;
    }

    .label {
        font-weight: bold;
        color: {{ $pdfHeading }};
    }

    .word-break {
        word-break: break-all;
        overflow-wrap: anywhere;
    }

    .status-stack {
        margin: 0;
    }

    .status-badge {
        display: inline-block;
        margin: 0 4px 4px 0;
        padding: 3px 7px;
        border-radius: 999px;
        border: 1px solid transparent;
        font-size: 9px;
        font-weight: bold;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .status-applied,
    .status-submitted {
        background: #e8f1ff;
        border-color: #bfd3ff;
        color: #1d4ed8;
    }

    .status-interview {
        background: #fef3c7;
        border-color: #fcd34d;
        color: #92400e;
    }

    .status-hired,
    .status-approved {
        background: #dcfce7;
        border-color: #86efac;
        color: #166534;
    }

    .status-rejected {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #b91c1c;
    }

    .status-incomplete,
    .status-draft {
        background: #f3f4f6;
        border-color: #d1d5db;
        color: #4b5563;
    }

    .section-block {
        margin-bottom: 14px;
        page-break-inside: avoid;
    }

    .section-heading {
        margin: 0 0 10px;
        font-size: 13px;
        font-weight: bold;
        color: {{ $pdfHeading }};
    }

    .paragraph-list {
        margin: 0;
        padding-left: 16px;
        color: {{ $pdfBody }};
    }

    .paragraph-list li {
        margin-bottom: 4px;
    }

    .paragraph-list li:last-child {
        margin-bottom: 0;
    }

    .empty-state {
        text-align: center;
        color: {{ $pdfMuted }};
    }

    .empty-state strong {
        display: block;
        margin-bottom: 4px;
        font-size: 13px;
        color: {{ $pdfHeading }};
    }
</style>
