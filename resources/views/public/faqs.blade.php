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
    <title>{{ $siteName }} - FAQs</title>

    @if($siteFavicon)
        <link rel="icon" type="image/x-icon" href="{{ $siteFavicon }}">
        <link rel="shortcut icon" type="image/x-icon" href="{{ $siteFavicon }}">
    @endif

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite('resources/css/app.css')
    <x-dynamic-styles />
    <style>
        .welcome-header-wrap {
            position: sticky;
            top: 0;
            z-index: 70;
            padding: 0;
        }

        .welcome-visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .welcome-header {
            margin: 0;
            width: 100%;
            padding: 0;
            background: transparent;
            border-radius: 0;
            border: 1px solid rgba(200, 164, 93, 0.28);
            border-left: 0;
            border-right: 0;
            backdrop-filter: blur(8px);
            box-shadow: 0 18px 44px rgba(17, 17, 17, 0.08);
        }

        .welcome-header-inner {
            margin: 0 auto;
            max-width: 80rem;
            display: grid;
            grid-template-columns: auto 1fr;
            align-items: center;
            gap: 1rem;
            padding: 0.62rem 0.72rem;
        }

        .welcome-header-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            min-height: 3.3rem;
        }

        .welcome-header-logo {
            width: auto;
            max-height: 3.1rem;
        }

        .welcome-header-fallback {
            display: inline-flex;
            width: 3rem;
            height: 3rem;
            align-items: center;
            justify-content: center;
            border-radius: 0.85rem;
            border: 1px solid #d8c6a1;
            background: #fbf5e8;
            color: #b68c3a;
        }

        .welcome-header-panel {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            gap: 0.8rem;
            min-width: 0;
        }

        .welcome-header-menu {
            display: flex;
            align-items: center;
            justify-self: center;
            gap: 0.4rem;
        }

        .welcome-header-menu-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.25rem;
            border-radius: 999px;
            padding: 0.5rem 0.9rem;
            border: 1px solid transparent;
            color: #111111;
            background: transparent;
            font-size: 0.58rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            text-decoration: none;
            transition: transform 0.18s ease, background-color 0.18s ease, color 0.18s ease, border-color 0.18s ease;
        }

        .welcome-header-menu-link:hover {
            transform: scale(1.02);
            border-color: #d8c6a1;
            background: #fffaf1;
        }

        .welcome-header-menu-link.active {
            border-color: transparent;
            background: transparent;
            color: #9b7431;
        }

        .welcome-header-actions {
            display: flex;
            justify-self: end;
            gap: 0.6rem;
        }

        .welcome-header-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.36rem;
            border-radius: 999px;
            padding: 0.56rem 1rem;
            border: 1px solid #111111;
            color: #ffffff !important;
            background: #111111;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            text-decoration: none;
            transition: transform 0.18s ease, background-color 0.18s ease, color 0.18s ease, border-color 0.18s ease;
        }

        .welcome-header-btn:hover {
            transform: scale(1.02);
            background: #000000;
        }

        .welcome-header-btn.secondary {
            border-color: #d8c6a1;
            background: #fffaf1;
            color: #1f1f1f !important;
        }

        .welcome-header-btn.secondary:hover {
            background: #f8edd8;
        }

        .welcome-header-toggle {
            display: none;
            width: 2.25rem;
            height: 2.25rem;
            align-items: center;
            justify-content: center;
            border-radius: 0.72rem;
            border: 1px solid #dfcfaf;
            background: #fffaf1;
            color: #111111;
            font-size: 0.82rem;
        }

        .faq-hero {
            margin: 0 auto;
            max-width: 48rem;
            text-align: center;
        }

        .faq-list {
            margin-top: 1.5rem;
            display: grid;
            gap: 1rem;
            margin-left: auto;
            margin-right: auto;
            max-width: 64rem;
        }

        .faq-item {
            border: 1px solid #e7dcc5;
            border-radius: 1.35rem;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 18px 40px rgba(17, 17, 17, 0.05);
            overflow: hidden;
        }

        .faq-item[open] {
            border-color: #d8c6a1;
            box-shadow: 0 24px 52px rgba(17, 17, 17, 0.08);
        }

        .faq-summary {
            list-style: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.2rem 1.25rem;
            cursor: pointer;
        }

        .faq-summary::-webkit-details-marker {
            display: none;
        }

        .faq-summary-main {
            display: flex;
            align-items: flex-start;
            gap: 0.95rem;
            min-width: 0;
            align-items: center;
        }

        .faq-order {
            display: inline-flex;
            width: 2.3rem;
            height: 2.3rem;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid #e2d2af;
            background: #fbf5e8;
            color: #9b7431;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.06em;
        }

        .faq-question {
            margin: 0;
            color: #111111;
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.5;
        }

        .faq-toggle {
            display: inline-flex;
            width: 2.1rem;
            height: 2.1rem;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid #e2d2af;
            background: #fffaf1;
            color: #9b7431;
            font-size: 0.78rem;
            position: relative;
            transition: background-color 0.18s ease, color 0.18s ease, border-color 0.18s ease;
        }

        .faq-toggle i {
            position: absolute;
            transition: opacity 0.18s ease, transform 0.18s ease;
        }

        .faq-toggle-minus {
            opacity: 0;
            transform: scale(0.8);
        }

        .faq-item[open] .faq-toggle {
            background: #111111;
            border-color: #111111;
            color: #ffffff;
        }

        .faq-item[open] .faq-toggle-plus {
            opacity: 0;
            transform: scale(0.8);
        }

        .faq-item[open] .faq-toggle-minus {
            opacity: 1;
            transform: scale(1);
        }

        .faq-answer {
            border-top: 1px solid #efe5d2;
            padding: 0 1.25rem 1.2rem 4.5rem;
            color: #49443a;
            font-size: 0.88rem;
            line-height: 1.9;
        }

        .faq-answer p {
            margin: 0;
        }

        @media (max-width: 767px) {
            .welcome-header-wrap {
                padding: 0;
            }

            .welcome-header-inner {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                padding: 0.54rem 0.58rem;
            }

            .welcome-header-logo {
                max-height: 2.6rem;
            }

            .welcome-header-fallback {
                width: 2.55rem;
                height: 2.55rem;
            }

            .welcome-header-toggle {
                display: inline-flex;
            }

            .welcome-header-panel {
                width: 100%;
                display: none;
                grid-template-columns: 1fr;
                align-items: stretch;
                gap: 0.5rem;
                border-top: 1px solid #eee5d3;
                margin-top: 0.28rem;
                padding-top: 0.62rem;
            }

            .welcome-header-panel.is-open {
                display: grid;
            }

            .welcome-header-menu {
                width: 100%;
                justify-self: stretch;
                flex-direction: column;
                align-items: stretch;
                gap: 0.4rem;
            }

            .welcome-header-menu-link {
                width: 100%;
                min-height: 2.2rem;
                font-size: 0.56rem;
                letter-spacing: 0.18em;
            }

            .welcome-header-actions {
                width: 100%;
                justify-self: stretch;
                display: flex;
                flex-direction: column;
                gap: 0.45rem;
            }

            .welcome-header-btn {
                width: 100%;
                min-height: 2.32rem;
                font-size: 0.58rem;
                letter-spacing: 0.18em;
            }

            .faq-summary {
                padding: 1rem;
            }

            .faq-question {
                font-size: 0.9rem;
            }

            .faq-answer {
                padding: 0 1rem 1rem 1rem;
                font-size: 0.8rem;
                line-height: 1.8;
            }
        }

        @media (min-width: 768px) {
            .welcome-header-panel {
                display: grid !important;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-[#f6f3ec] text-stone-900 antialiased">
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute left-[-10%] top-[-14%] h-80 w-80 rounded-full bg-[#c8a45d]/20 blur-3xl"></div>
        <div class="absolute right-[-10%] top-1/4 h-72 w-72 rounded-full bg-black/10 blur-3xl"></div>
    </div>

    <div class="relative">
        <x-public-header
            :site-name="$siteName"
            :site-logo="$siteLogo"
            active="faqs"
            :primary-label="$primaryLabel"
            :primary-link="$primaryLink"
            :secondary-label="$secondaryLabel"
            :secondary-link="$secondaryLink"
        />

        <main class="mx-auto w-full max-w-7xl px-4 pb-16 pt-8 sm:px-6 lg:px-8">
            <section class="faq-hero">
                <p class="mt-4 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#9b7431]">FAQs</p>
                <h1 class="theme-display mt-2 text-3xl text-stone-950 sm:text-4xl" style="font-weight: 600">Frequently Asked Questions</h1>
                <p class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-stone-600">Browse the answers to the most common client questions about onboarding, communication, payments, support, and daily work updates.</p>
            </section>

            @if($faqs->isEmpty())
                <section class="mt-6 rounded-[1.5rem] border border-dashed border-[#d8c6a1] bg-[#fffaf1] p-8 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full border border-[#e6d2ad] bg-white text-[#9b7431]">
                        <i class="fas fa-circle-question"></i>
                    </div>
                    <h2 class="theme-display text-2xl text-stone-900">No FAQs available yet</h2>
                    <p class="mx-auto mt-2 max-w-xl text-sm leading-7 text-stone-600">Please check back soon for answers to common questions.</p>
                </section>
            @else
                <section class="faq-list">
                    @foreach($faqs as $faq)
                        <details class="faq-item" @if($loop->first) open @endif>
                            <summary class="faq-summary">
                                <div class="faq-summary-main">
                                    <span class="faq-order">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <p class="faq-question">{{ $faq->question }}</p>
                                </div>
                                <span class="faq-toggle">
                                    <i class="fas fa-plus faq-toggle-plus" aria-hidden="true"></i>
                                    <i class="fas fa-minus faq-toggle-minus" aria-hidden="true"></i>
                                </span>
                            </summary>
                            <div class="faq-answer">
                                <p>{!! nl2br(e($faq->answer)) !!}</p>
                            </div>
                        </details>
                    @endforeach
                </section>
            @endif

            <x-site-footer />
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const navToggle = document.querySelector('[data-welcome-nav-toggle]');
            const navMenu = document.querySelector('[data-welcome-nav]');
            const navIcon = document.querySelector('[data-welcome-nav-icon]');

            if (navToggle && navMenu) {
                const setMenuOpen = function (isOpen) {
                    navMenu.classList.toggle('is-open', isOpen);
                    navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

                    if (navIcon) {
                        navIcon.classList.toggle('fa-bars', !isOpen);
                        navIcon.classList.toggle('fa-times', isOpen);
                    }
                };

                navToggle.addEventListener('click', function () {
                    setMenuOpen(!navMenu.classList.contains('is-open'));
                });

                navMenu.querySelectorAll('a').forEach(function (link) {
                    link.addEventListener('click', function () {
                        if (window.innerWidth < 768) {
                            setMenuOpen(false);
                        }
                    });
                });

                window.addEventListener('resize', function () {
                    if (window.innerWidth >= 768) {
                        setMenuOpen(false);
                    }
                });
            }
        });
    </script>
</body>
</html>
