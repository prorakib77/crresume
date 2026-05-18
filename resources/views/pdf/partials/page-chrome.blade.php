@php
    $resolvedBrandName = trim((string) \App\Models\CustomizationSetting::getValue('pdf_brand_name', ''));
    $siteTitle = $resolvedBrandName !== '' ? $resolvedBrandName : site_name();
    $generatedAt = $generatedAt ?? now();
    $documentTitle = $documentTitle ?? 'Document';
    $documentSubtitle = $documentSubtitle ?? null;
    $documentTag = $documentTag ?? 'Professional Export';
    $footerNote = trim((string) ($footerNote ?? \App\Models\CustomizationSetting::getValue('pdf_footer_note', 'Confidential workspace export.')));
    $generatedLabel = trim((string) \App\Models\CustomizationSetting::getValue('pdf_generated_label', 'Generated')) ?: 'Generated';
    $logoDataUri = null;
    $logoImageSrc = null;

    $resolveStoredAssetDataUri = static function (?string $storedPath): ?string {
        if (!is_string($storedPath) || trim($storedPath) === '') {
            return null;
        }

        $absolutePath = storage_path('app/public/' . ltrim(str_replace('\\', '/', $storedPath), '/'));

        if (!is_file($absolutePath) || !is_readable($absolutePath)) {
            return null;
        }

        $contents = file_get_contents($absolutePath);
        $mimeType = function_exists('mime_content_type')
            ? mime_content_type($absolutePath)
            : 'image/png';

        if ($contents === false) {
            return null;
        }

        return 'data:' . ($mimeType ?: 'image/png') . ';base64,' . base64_encode($contents);
    };

    $pdfLogoPath = \App\Models\CustomizationSetting::getStoredValue('pdf_logo');
    $siteLogoPath = \App\Models\CustomizationSetting::getStoredValue('site_logo');
    $pdfLogoUrl = trim((string) \App\Models\CustomizationSetting::getValue('pdf_logo_url', ''));

    $logoDataUri = $resolveStoredAssetDataUri($pdfLogoPath);

    if ($logoDataUri) {
        $logoImageSrc = $logoDataUri;
    } elseif ($pdfLogoUrl !== '') {
        $logoImageSrc = $pdfLogoUrl;
    } else {
        $logoImageSrc = $resolveStoredAssetDataUri($siteLogoPath);
    }

    $footerLeftText = $footerNote !== ''
        ? $siteTitle . ' | ' . $footerNote
        : $siteTitle . ' | ' . $documentTitle;
@endphp

<div class="pdf-header">
    <table class="header-table">
        <tr>
            <td style="width: 56px; vertical-align: top;">
                @if($logoImageSrc)
                    <img src="{{ $logoImageSrc }}" alt="{{ $siteTitle }} logo" class="brand-logo">
                @else
                    <div class="brand-mark">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($siteTitle, 0, 1)) }}</div>
                @endif
            </td>
            <td style="padding-left: 12px; vertical-align: top;">
                <p class="header-kicker">{{ $documentTag }}</p>
                <h1 class="header-title">{{ $documentTitle }}</h1>
                @if($documentSubtitle)
                    <p class="header-subtitle">{{ $documentSubtitle }}</p>
                @endif
            </td>
            <td class="header-meta" style="width: 190px; vertical-align: top;">
                <strong>{{ $siteTitle }}</strong><br>
                {{ $generatedAt->format('F j, Y') }}<br>
                {{ $generatedAt->format('g:i A') }}
            </td>
        </tr>
    </table>
</div>

<div class="pdf-footer">
    <table class="footer-table">
        <tr>
            <td>{{ $footerLeftText }}</td>
            <td>{{ $generatedLabel }} {{ $generatedAt->format('M j, Y g:i A') }}</td>
        </tr>
    </table>
</div>
