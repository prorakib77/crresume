@php
    $primaryEnabled = (string) ($settings->get('public_header_register_enabled')?->setting_value ?? $settings->get('welcome_primary_enabled')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('public_header_register_enabled', '1')) === '1';
    $secondaryEnabled = (string) ($settings->get('welcome_secondary_enabled')?->setting_value ?? \App\Models\CustomizationSetting::defaultValue('welcome_secondary_enabled', '1')) === '1';
    $primaryLabel = $primaryEnabled ? ($settings->get('public_header_register_label')?->setting_value ?? $settings->get('welcome_primary_label')?->setting_value ?? 'Secure Your Spot') : '';
    $primaryLink = $settings->get('public_header_register_link')?->setting_value ?? $settings->get('welcome_primary_link')?->setting_value ?? route('register');
    $secondaryLabel = $secondaryEnabled ? ($settings->get('welcome_secondary_label')?->setting_value ?? 'Login') : '';
    $secondaryLink = $settings->get('welcome_secondary_link')?->setting_value ?? route('login');
    $guidePrimaryLink = $guide['primary_link'] ?: route('register');
    $guideSecondaryLink = $guide['secondary_link'] ?: route('contact.page');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $siteName }} - Guide</title>

    @if($siteFavicon)
        <link rel="icon" type="image/x-icon" href="{{ $siteFavicon }}">
        <link rel="shortcut icon" type="image/x-icon" href="{{ $siteFavicon }}">
    @endif

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite('resources/css/app.css')
    <x-dynamic-styles />
    <style>
        .guide-page {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(200, 164, 93, 0.14), transparent 28%),
                radial-gradient(circle at right 20%, rgba(17, 17, 17, 0.05), transparent 18%),
                #f6f3ec;
            color: #1c1917;
        }

        .guide-page * {
            box-sizing: border-box;
        }

        .guide-page-main {
            width: min(100%, 1180px);
            margin: 0 auto;
            padding: 2rem 1rem 4rem;
        }

        .guide-page p,
        .guide-page h1,
        .guide-page h2,
        .guide-page h3 {
            margin: 0;
        }

        .guide-surface {
            border: 1px solid #e5d8c3;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 24px 64px rgba(17, 17, 17, 0.07);
        }

        .guide-hero {
            display: grid;
            gap: 1.5rem;
            padding: 1.6rem;
            border-radius: 2rem;
        }

        .guide-hero-main {
            display: grid;
            gap: 1.15rem;
            align-content: start;
        }

        .guide-badge {
            display: inline-flex;
            width: fit-content;
            align-items: center;
            gap: 0.55rem;
            padding: 0.72rem 1rem;
            border-radius: 999px;
            border: 1px solid #dbc79d;
            background: #fff7e8;
            color: #9b7431;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .guide-title {
            max-width: 12ch;
            font-size: clamp(2.5rem, 5vw, 4.5rem);
            font-weight: 600;
            line-height: 85px;
            color: #111111;
        }
        @media (max-width: 900px) {
            .guide-title {
                line-height: 45px;
            }
        }

        .guide-text {
            max-width: 42rem;
            color: #57534e;
            font-size: 1rem;
            font-weight: 600;
            line-height: 2.1;
        }

        .guide-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.9rem;
            align-items: stretch;
        }

        .guide-button {
            display: inline-flex;
            flex: 0 1 auto;
            max-width: 100%;
            min-height: 3rem;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            padding: 0.85rem 1.2rem;
            border-radius: 999px;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            line-height: 1.35;
            text-align: center;
            text-transform: uppercase;
            text-decoration: none;
            white-space: normal;
            overflow-wrap: anywhere;
            transition: transform 0.18s ease, background-color 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .guide-button i {
            flex-shrink: 0;
            font-size: 0.8rem;
        }

        .guide-button:hover {
            transform: translateY(-2px);
        }

        .guide-button-primary {
            border: 1px solid #c8a45d;
            background: #c8a45d;
            color: #171510;
            box-shadow: 0 12px 28px rgba(200, 164, 93, 0.22);
        }

        .guide-button-primary:hover {
            background: #d0ae67;
            color: #171510;
        }

        .guide-button-secondary {
            border: 1px solid #dcc9a5;
            background: #ffffff;
            color: #171510;
            box-shadow: 0 10px 22px rgba(17, 17, 17, 0.05);
        }

        .guide-button-secondary:hover {
            border-color: #c8a45d;
            background: #fff8ed;
            color: #111111;
        }

        .guide-highlights {
            display: grid;
            gap: 0.85rem;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .guide-highlight-card {
            display: grid;
            gap: 0.55rem;
            padding: 1rem 1.05rem;
            border-radius: 1.2rem;
            border: 1px solid #ece2cf;
            background: #fffdfa;
            box-shadow: 0 10px 22px rgba(17, 17, 17, 0.04);
        }

        .guide-highlight-label {
            color: #9b7431;
            font-size: 0.64rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .guide-highlight-text {
            color: #1c1917;
            font-size: 0.96rem;
            font-weight: 700;
            line-height: 1.5;
        }

        .guide-hero-side {
            display: grid;
            gap: 1rem;
            align-content: start;
            padding: 0.15rem;
        }

        .guide-side-card {
            display: grid;
            gap: 0.8rem;
            padding: 1.35rem;
            border-radius: 1.5rem;
            border: 1px solid #e7d9bf;
            background: linear-gradient(180deg, #fffdf8 0%, #fff7eb 100%);
        }

        .guide-side-card--soft {
            background: #ffffff;
        }

        .guide-card-label {
            color: #9b7431;
            font-size: 0.64rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .guide-card-title {
            color: #111111;
            font-size: 1.6rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .guide-card-text {
            color: #57534e;
            font-size: 0.96rem;
            line-height: 1.9;
        }

        .guide-section {
            margin-top: 2rem;
        }

        .guide-section-head {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 1.4rem;
        }

        .guide-section-copy {
            display: grid;
            gap: 0.7rem;
            max-width: 42rem;
        }

        .guide-section-label {
            color: #9b7431;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .guide-section-title {
            color: #111111;
            font-size: clamp(2rem, 3.5vw, 3rem);
            font-weight: 700;
            line-height: 1.05;
        }

        .guide-section-text {
            color: #57534e;
            font-size: 1rem;
            line-height: 1.9;
        }

        .guide-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.7rem 1rem;
            border-radius: 999px;
            border: 1px solid #e2d4bc;
            background: rgba(255, 255, 255, 0.8);
            color: #57534e;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            line-height: 1.35;
            text-transform: uppercase;
            text-align: center;
        }

        .guide-step-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .guide-step-card {
            display: grid;
            gap: 1rem;
            height: 100%;
            padding: 1.4rem;
            border-radius: 1.6rem;
            border: 1px solid #e4d7c1;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 20px 48px rgba(17, 17, 17, 0.05);
            position: relative;
            overflow: hidden;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .guide-step-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 28px 64px rgba(17, 17, 17, 0.08);
        }

        .guide-step-card::before {
            content: "";
            position: absolute;
            inset: 0 auto auto 0;
            width: 100%;
            height: 0.25rem;
            background: linear-gradient(90deg, #dbc08a 0%, #b98f45 55%, rgba(17, 17, 17, 0.7) 100%);
        }

        .guide-step-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
        }

        .guide-step-copy {
            display: grid;
            gap: 0.65rem;
            min-width: 0;
        }

        .guide-step-number {
            display: inline-flex;
            width: 3rem;
            height: 3rem;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid #e2d2af;
            background: #fbf5e8;
            color: #9b7431;
            font-size: 0.88rem;
            font-weight: 800;
            letter-spacing: 0.08em;
        }

        .guide-step-label {
            color: #9b7431;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .guide-step-title {
            color: #111111;
            font-size: 1.65rem;
            font-weight: 700;
            line-height: 1.18;
        }

        .guide-step-text {
            color: #57534e;
            font-size: 0.96rem;
            line-height: 1.9;
        }

        .guide-support-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .guide-support-card {
            display: grid;
            gap: 0.9rem;
            padding: 1.35rem;
            border-radius: 1.5rem;
            border: 1px solid #e4d7c1;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 18px 40px rgba(17, 17, 17, 0.04);
        }

        .guide-support-label {
            color: #9b7431;
            font-size: 0.64rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .guide-support-text {
            color: #57534e;
            font-size: 0.96rem;
            line-height: 1.9;
        }

        .guide-cta {
            display: grid;
            gap: 1.5rem;
            align-items: center;
            margin-top: 2rem;
            padding: 1.6rem;
            border-radius: 2rem;
            border: 1px solid #e2d4bc;
            background: linear-gradient(135deg, #fffaf1 0%, #f7eddc 100%);
            box-shadow: 0 26px 60px rgba(17, 17, 17, 0.07);
        }

        .guide-cta-copy {
            display: grid;
            gap: 0.8rem;
            align-content: start;
        }

        .guide-cta-title {
            color: #111111;
            font-size: clamp(2rem, 3.2vw, 2.8rem);
            font-weight: 700;
            line-height: 1.08;
        }

        .guide-cta-text {
            max-width: 42rem;
            color: #57534e;
            font-size: 1rem;
            line-height: 1.9;
        }

        @media (min-width: 900px) {
            .guide-hero {
                grid-template-columns: minmax(0, 1.12fr) minmax(18rem, 0.88fr);
                gap: 1.75rem;
                padding: 2rem;
            }

            .guide-cta {
                grid-template-columns: minmax(0, 1fr) auto;
            }
        }

        @media (max-width: 899px) {
            .guide-highlights,
            .guide-step-grid,
            .guide-support-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .guide-page-main {
                padding: 1.4rem 0.9rem 3rem;
            }

            .guide-hero,
            .guide-cta {
                padding: 1.15rem;
                border-radius: 1.5rem;
            }

            .guide-side-card,
            .guide-step-card,
            .guide-support-card {
                padding: 1.1rem;
                border-radius: 1.25rem;
            }

            .guide-actions {
                display: grid;
                gap: 0.75rem;
            }

            .guide-button {
                width: 100%;
                padding: 0.88rem 1rem;
                font-size: 0.62rem;
                letter-spacing: 0.12em;
            }

            .guide-text {
                font-size: 0.92rem;
                line-height: 1.85;
            }

            .guide-chip {
                width: 100%;
            }

            .guide-step-top {
                gap: 0.85rem;
            }

            .guide-step-title {
                font-size: 1.4rem;
            }

            .guide-card-title {
                font-size: 1.35rem;
            }
        }
    </style>
</head>
<body class="guide-page">
    <div class="relative">
        <x-public-header
            :site-name="$siteName"
            :site-logo="$siteLogo"
            active="guide"
            :primary-label="$primaryLabel"
            :primary-link="$primaryLink"
            :secondary-label="$secondaryLabel"
            :secondary-link="$secondaryLink"
        />

        <main class="guide-page-main">
            <section class="guide-surface guide-hero">
                <div class="guide-hero-main">
                    <span class="guide-badge">
                        <i class="fas fa-compass"></i>
                        {{ $guide['badge'] }}
                    </span>

                    <h1 class="guide-title theme-display" style="">{{ $guide['title'] }}</h1>
                    <p class="guide-text">{!! nl2br(e($guide['subtitle'])) !!}</p>

                    <div class="guide-actions">
                        <a href="{{ $guidePrimaryLink }}" class="guide-button guide-button-primary" style="color: #fff; background: #000;">
                            <i class="fas fa-arrow-right"></i>
                            <span>{{ $guide['primary_label'] }}</span>
                        </a>
                        <a href="{{ $guideSecondaryLink }}" class="guide-button guide-button-secondary">
                            <i class="fas fa-envelope"></i>
                            <span>{{ $guide['secondary_label'] }}</span>
                        </a>
                    </div>

                    <div class="guide-highlights">
                        <article class="guide-highlight-card">
                            <p class="guide-highlight-label">Step 1</p>
                            <p class="guide-highlight-text">Create your account</p>
                        </article>
                        <article class="guide-highlight-card">
                            <p class="guide-highlight-label">Step 2</p>
                            <p class="guide-highlight-text">Complete onboarding</p>
                        </article>
                        <article class="guide-highlight-card">
                            <p class="guide-highlight-label">Step 3+</p>
                            <p class="guide-highlight-text">Stay active in your dashboard</p>
                        </article>
                    </div>
                </div>

                <aside class="guide-hero-side">
                    <article class="guide-side-card">
                        <p class="guide-card-label">What to expect</p>
                        <h2 class="guide-card-title theme-display">{{ $guide['intro_title'] }}</h2>
                        <p class="guide-card-text">{!! nl2br(e($guide['intro_text'])) !!}</p>
                    </article>

                    <article class="guide-side-card guide-side-card--soft">
                        <p class="guide-card-label">Client habit</p>
                        <h2 class="guide-card-title theme-display">{{ $guide['support_title'] }}</h2>
                        <p class="guide-card-text">{!! nl2br(e($guide['support_text'])) !!}</p>
                    </article>
                </aside>
            </section>

            <section class="guide-section">
                <div class="guide-section-head">
                    <div class="guide-section-copy">
                        <p class="guide-section-label">Step-by-step workflow</p>
                        <h2 class="guide-section-title theme-display">A clear process from first login to final delivery.</h2>
                        <p class="guide-section-text">This page helps clients know what to do next at every stage of the service, with each step controlled from the admin dashboard.</p>
                    </div>

                    <div class="guide-chip">6 focused steps</div>
                </div>

                <div class="guide-step-grid">
                    @foreach($steps as $step)
                        <article class="guide-step-card">
                            <div class="guide-step-top">
                                <div class="guide-step-copy">
                                    <p class="guide-step-label">{{ $step['eyebrow'] }}</p>
                                    <h3 class="guide-step-title theme-display">{{ $step['title'] }}</h3>
                                </div>

                                <span class="guide-step-number">{{ str_pad((string) $step['number'], 2, '0', STR_PAD_LEFT) }}</span>
                            </div>

                            <p class="guide-step-text">{!! nl2br(e($step['body'])) !!}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="guide-section">
                <div class="guide-support-grid">
                    <article class="guide-support-card">
                        <p class="guide-support-label">Portal access</p>
                        <p class="guide-support-text">Use your account as the single place for onboarding, updates, notices, and next actions.</p>
                    </article>
                    <article class="guide-support-card">
                        <p class="guide-support-label">Faster turnaround</p>
                        <p class="guide-support-text">Quick replies to requests, OTP prompts, and approvals help your workflow move without delays.</p>
                    </article>
                    <article class="guide-support-card">
                        <p class="guide-support-label">Organized support</p>
                        <p class="guide-support-text">Keep payments, messages, and service records inside the website so nothing gets lost.</p>
                    </article>
                </div>
            </section>

            <section class="guide-cta">
                <div class="guide-cta-copy">
                    <p class="guide-section-label">Next step</p>
                    <h2 class="guide-cta-title theme-display">Use the portal consistently and keep every client action in one place.</h2>
                    <p class="guide-cta-text">The strongest client experience usually comes from accurate onboarding, regular dashboard checks, and fast replies whenever your team requests something.</p>
                </div>

                <div class="guide-actions">
                    <a href="{{ $guidePrimaryLink }}" class="guide-button guide-button-primary" style="color: #fff; background: #000;">
                        <span>{{ $guide['primary_label'] }}</span>
                    </a>
                    <a href="{{ $guideSecondaryLink }}" class="guide-button guide-button-secondary">
                        <i class="fas fa-envelope"></i>
                        <span>{{ $guide['secondary_label'] }}</span>
                    </a>
                </div>
            </section>

            <x-site-footer />
        </main>
    </div>
</body>
</html>
