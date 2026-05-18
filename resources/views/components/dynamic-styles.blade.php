@php
    $settings = \App\Models\CustomizationSetting::getAllActive();
    $themeDefaults = collect(\App\Models\CustomizationSetting::defaultDefinitions())
        ->mapWithKeys(fn ($definition, $key) => [$key => $definition['setting_value']])
        ->all();

    $legacyFallbacks = [
        'primary_color' => ['#3b82f6', '#2563eb', '#1d4ed8'],
        'secondary_color' => ['#6b7280', '#4b5563'],
        'accent_color' => ['#10b981', '#059669'],
        'background_color' => ['#ffffff', '#f8fafc'],
        'text_color' => ['#1f2937'],
        'header_color' => ['#1f2937'],
        'sidebar_color' => ['#f8fafc'],
        'button_color' => ['#3b82f6', '#2563eb'],
        'link_color' => ['#3b82f6', '#2563eb'],
        'border_color' => ['#e5e7eb'],
        'success_color' => ['#10b981', '#059669'],
        'warning_color' => ['#f59e0b', '#d97706'],
        'error_color' => ['#ef4444', '#dc2626'],
        'info_color' => ['#3b82f6', '#2563eb', '#1d4ed8'],
        'danger_color' => ['#ef4444', '#dc2626'],
        'primary_dark' => ['#1d4ed8'],
        'secondary_dark' => ['#4b5563'],
        'success_dark' => ['#059669'],
        'warning_dark' => ['#d97706'],
        'error_dark' => ['#dc2626'],
        'info_dark' => ['#1d4ed8'],
        'danger_dark' => ['#dc2626'],
        'font_family' => ['Poppins'],
        'display_font' => ['Cormorant Garamond'],
    ];

    $resolveSetting = function (string $key) use ($settings, $themeDefaults, $legacyFallbacks) {
        $value = trim((string) ($settings->get($key)?->setting_value ?? ''));

        if ($value === '') {
            return $themeDefaults[$key] ?? '';
        }

        $normalized = strtolower($value);
        $legacyValues = array_map('strtolower', $legacyFallbacks[$key] ?? []);

        if (in_array($normalized, $legacyValues, true)) {
            return $themeDefaults[$key] ?? $value;
        }

        return $value;
    };

    $fontStack = function (string $font, bool $display = false): string {
        $font = trim($font);

        if ($font === '') {
            $font = $display ? 'Poppins' : 'Inter';
        }

        $fallback = str_contains($font, 'Serif') || str_contains($font, 'Playfair')
            ? 'serif'
            : 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';

        return "'" . addslashes($font) . "', {$fallback}";
    };

    $selectedBodyFont = $resolveSetting('font_family');
    $selectedDisplayFont = $resolveSetting('display_font');
    $fontHref = \App\Models\CustomizationSetting::googleFontHref([$selectedBodyFont, $selectedDisplayFont]);
    $siteFavicon = site_favicon();
@endphp

@if($fontHref)
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ $fontHref }}" rel="stylesheet">
@endif

@if($siteFavicon)
    <link rel="icon" type="image/x-icon" href="{{ $siteFavicon }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ $siteFavicon }}">
@endif

<style>
:root {
    --primary-color: {{ $resolveSetting('primary_color') }};
    --secondary-color: {{ $resolveSetting('secondary_color') }};
    --accent-color: {{ $resolveSetting('accent_color') }};
    --background-color: {{ $resolveSetting('background_color') }};
    --text-color: {{ $resolveSetting('text_color') }};
    --header-color: {{ $resolveSetting('header_color') }};
    --sidebar-color: {{ $resolveSetting('sidebar_color') }};
    --button-color: {{ $resolveSetting('button_color') }};
    --link-color: {{ $resolveSetting('link_color') }};
    --border-color: {{ $resolveSetting('border_color') }};
    --success-color: {{ $resolveSetting('success_color') }};
    --warning-color: {{ $resolveSetting('warning_color') }};
    --error-color: {{ $resolveSetting('error_color') }};
    --info-color: {{ $resolveSetting('info_color') }};
    --danger-color: {{ $resolveSetting('danger_color') }};
    --primary-dark: {{ $resolveSetting('primary_dark') }};
    --secondary-dark: {{ $resolveSetting('secondary_dark') }};
    --success-dark: {{ $resolveSetting('success_dark') }};
    --warning-dark: {{ $resolveSetting('warning_dark') }};
    --error-dark: {{ $resolveSetting('error_dark') }};
    --info-dark: {{ $resolveSetting('info_dark') }};
    --danger-dark: {{ $resolveSetting('danger_dark') }};
    --font-family: {!! $fontStack($selectedBodyFont) !!};
    --display-font: {!! $fontStack($selectedDisplayFont, true) !!};
    --font-size: {{ $resolveSetting('font_size') }};
    --border-radius: {{ $resolveSetting('border_radius') }};
    --box-shadow: {{ $resolveSetting('box_shadow') }};
    --animation-speed: {{ $resolveSetting('animation_speed') }};
    --sidebar-width: {{ $resolveSetting('sidebar_width') }};
    --header-height: {{ $resolveSetting('header_height') }};
}

body {
    font-family: var(--font-family);
    font-size: var(--font-size);
    background-color: var(--background-color);
    color: var(--text-color);
}

.theme-display,
.page-title,
.card-title,
.modal-title,
.sidebar-brand {
    font-family: var(--display-font);
}

a:not(.btn):not(.nav-link):not(.sidebar-brand) {
    color: var(--link-color);
}

button,
.btn,
[role="button"],
input,
select,
textarea,
a,
.dropdown-item,
.dropdown-item-text,
.form-control,
.form-select,
.form-control-color,
.work-update-filter-button,
.work-update-filter-close,
.searchable-toggle,
.sidebar-toggle,
.sidebar-close-button,
.btn-close {
    transition:
        color var(--animation-speed) ease,
        background-color var(--animation-speed) ease,
        border-color var(--animation-speed) ease,
        box-shadow var(--animation-speed) ease,
        opacity var(--animation-speed) ease,
        transform var(--animation-speed) ease;
}

@if($settings->get('custom_css')?->setting_value)
{!! $settings->get('custom_css')->setting_value !!}
@endif
</style>

@if($settings->get('custom_js')?->setting_value)
<script>
{!! $settings->get('custom_js')->setting_value !!}
</script>
@endif
