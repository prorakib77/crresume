@php
    $primaryEnabled = (string) ($settings->get('public_header_register_enabled')?->setting_value ?? $settings->get('welcome_primary_enabled')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('public_header_register_enabled', '1')) === '1';
    $secondaryEnabled = (string) ($settings->get('welcome_secondary_enabled')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_secondary_enabled', '1')) === '1';
    $primaryLabel = $primaryEnabled ? ($settings->get('public_header_register_label')?->setting_value ?? $settings->get('welcome_primary_label')?->setting_value ?? 'Secure Your Spot') : '';
    $primaryLink = $settings->get('public_header_register_link')?->setting_value ?? $settings->get('welcome_primary_link')?->setting_value ?? route('register');
    $secondaryLabel = $secondaryEnabled ? ($settings->get('welcome_secondary_label')?->setting_value ?? 'Login') : '';
    $secondaryLink = $settings->get('welcome_secondary_link')?->setting_value ?? route('login');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $siteName }} - {{ $policyPage['title'] }}</title>

    @if($siteFavicon)
        <link rel="icon" type="image/x-icon" href="{{ $siteFavicon }}">
        <link rel="shortcut icon" type="image/x-icon" href="{{ $siteFavicon }}">
    @endif

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite('resources/css/app.css')
    <x-dynamic-styles />
    <style>
        .policy-page-shell {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(200, 164, 93, 0.16), transparent 28%),
                radial-gradient(circle at right 24%, rgba(17, 17, 17, 0.06), transparent 18%),
                #f6f3ec;
            color: #1c1917;
        }

        .policy-page-main {
            width: min(100%, 1180px);
            margin: 0 auto;
            padding: 1.75rem 1rem 4rem;
        }

        .policy-hero {
            padding: 1.5rem;
            border: 1px solid #e7dcc5;
            border-radius: 2rem;
            background: rgba(255, 255, 255, 0.94);
            box-shadow: 0 24px 64px rgba(17, 17, 17, 0.06);
        }

        .policy-title {
            font-size: clamp(2.2rem, 5vw, 4rem);
            line-height: 0.96;
            color: #111111;
        }

        .policy-subtitle {
            max-width: 52rem;
            margin-top: 1rem;
            color: #57534e;
            font-size: 1rem;
            line-height: 1.9;
        }

        .policy-meta {
            margin-top: 1.15rem;
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            color: #78716c;
            font-size: 0.88rem;
        }

        .policy-content-card {
            margin-top: 1.4rem;
            padding: 1.75rem;
            border: 1px solid #e7dcc5;
            border-radius: 2rem;
            background: rgba(255, 255, 255, 0.97);
            box-shadow: 0 24px 64px rgba(17, 17, 17, 0.06);
        }

        .policy-richtext {
            color: #292524;
            font-size: 1rem;
            line-height: 1.9;
        }

        .policy-richtext > *:first-child {
            margin-top: 0;
        }

        .policy-richtext > *:last-child {
            margin-bottom: 0;
        }

        .policy-richtext h1,
        .policy-richtext h2,
        .policy-richtext h3 {
            margin: 1.7rem 0 0.8rem;
            color: #111111;
            line-height: 1.2;
        }

        .policy-richtext h1 {
            font-size: 2rem;
        }

        .policy-richtext h2 {
            font-size: 1.45rem;
        }

        .policy-richtext h3 {
            font-size: 1.18rem;
        }

        .policy-richtext p,
        .policy-richtext ul,
        .policy-richtext ol,
        .policy-richtext blockquote {
            margin: 0.9rem 0;
        }

        .policy-richtext ul,
        .policy-richtext ol {
            padding-left: 1.35rem;
        }

        .policy-richtext li + li {
            margin-top: 0.38rem;
        }

        .policy-richtext a {
            color: #9b7431;
            font-weight: 700;
            text-decoration: underline;
            text-underline-offset: 0.16rem;
        }

        .policy-richtext strong {
            color: #111111;
            font-weight: 800;
        }

        .policy-richtext blockquote {
            padding: 1rem 1.15rem;
            border-left: 4px solid #c8a45d;
            border-radius: 0 1rem 1rem 0;
            background: #fff8ed;
            color: #44403c;
        }

        .policy-richtext .ql-align-center {
            text-align: center;
        }

        .policy-richtext .ql-align-right {
            text-align: right;
        }

        .policy-richtext .ql-align-justify {
            text-align: justify;
        }

        .policy-richtext .ql-indent-1 {
            padding-left: 2rem;
        }

        .policy-richtext .ql-indent-2 {
            padding-left: 4rem;
        }

        .policy-richtext .ql-indent-3 {
            padding-left: 6rem;
        }

        @media (max-width: 767px) {
            .policy-page-main {
                padding: 1.1rem 1rem 3rem;
            }

            .policy-hero,
            .policy-content-card {
                padding: 1.2rem;
                border-radius: 1.45rem;
            }

            .policy-richtext {
                font-size: 0.96rem;
                line-height: 1.82;
            }

            .policy-richtext .ql-indent-1,
            .policy-richtext .ql-indent-2,
            .policy-richtext .ql-indent-3 {
                padding-left: 1.2rem;
            }
        }
    </style>
</head>
<body class="policy-page-shell">
    <x-public-header
        :site-name="$siteName"
        :site-logo="$siteLogo"
        active=""
        :primary-label="$primaryLabel"
        :primary-link="$primaryLink"
        :secondary-label="$secondaryLabel"
        :secondary-link="$secondaryLink"
    />

    <main class="policy-page-main">
        <section class="policy-hero">
            <h1 class="policy-title theme-display">{{ $policyPage['title'] }}</h1>
            <p class="policy-subtitle">{{ $policyPage['subtitle'] }}</p>
            @if(!blank(trim((string) ($policyPage['meta_text'] ?? ''))))
                <p class="policy-meta">
                    <i class="fas fa-clock"></i>
                    <span>{{ $policyPage['meta_text'] }}</span>
                </p>
            @endif
        </section>

        <section class="policy-content-card">
            <article class="policy-richtext">
                {!! $policyPage['content'] !!}
            </article>
        </section>

        <x-site-footer />
    </main>
</body>
</html>
