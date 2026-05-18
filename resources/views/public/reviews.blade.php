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
    <title>{{ $siteName }} - Reviews</title>

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

        .review-card {
            width: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(200, 164, 93, 0.24);
            border-radius: 1.25rem;
            background: #ffffff;
            overflow: hidden;
            box-shadow: 0 16px 38px rgba(17, 17, 17, 0.06);
        }

        .review-media-button {
            position: relative;
            width: 100%;
            border: 0;
            padding: 0;
            margin: 0;
            aspect-ratio: 16 / 10;
            cursor: zoom-in;
            background: #f2e8d4;
            overflow: hidden;
        }

        .review-media-button img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.26s ease;
        }

        .review-media-button:hover img {
            transform: scale(1.03);
        }

        .review-media-fallback {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9b7431;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            background: linear-gradient(135deg, #f7ecda, #fff8ea);
        }

        .review-card-body {
            display: grid;
            gap: 0.85rem;
            padding: 0.9rem;
            flex: 1;
        }

        .review-card-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.65rem;
        }

        .review-customer {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 0;
        }

        .review-country {
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e5d6b7;
            background: #fff6e5;
            color: #8b6728;
            font-size: 0.57rem;
            font-weight: 800;
            letter-spacing: 0.06em;
        }

        .review-name {
            color: #171717;
            font-size: 0.9rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .review-verified-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid #d6eadf;
            background: #eef8f2;
            color: #157347;
            font-size: 0.62rem;
            font-weight: 700;
            gap: 0.34rem;
            padding: 0.3rem 0.58rem;
            white-space: nowrap;
        }

        .review-card-headline {
            margin: 0;
            color: #111111;
            font-size: 1.02rem;
            font-weight: 700;
            line-height: 1.4;
        }

        .review-card-text {
            margin: 0;
            color: #3f3f46;
            font-size: 0.83rem;
            line-height: 1.78;
        }

        .review-product-box {
            margin-top: 0.15rem;
            border: 1px solid #ece4d5;
            background: #faf7f2;
            border-radius: 0.95rem;
            padding: 0.64rem 0.72rem;
        }

        .review-product-note {
            margin: 0;
            color: #71717a;
            font-size: 0.56rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .review-product-link {
            margin-top: 0.25rem;
            display: inline-block;
            color: #111111;
            font-size: 0.78rem;
            font-weight: 700;
            text-decoration: underline;
            text-decoration-color: #c8a45d;
            text-underline-offset: 0.18rem;
            text-decoration-thickness: 2px;
        }

        .review-image-modal {
            position: fixed;
            inset: 0;
            background: rgba(12, 12, 12, 0.82);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1400;
            padding: 1rem;
        }

        .review-image-modal.is-open {
            display: flex;
        }

        .review-image-modal-dialog {
            width: min(96vw, 1080px);
            max-height: 92vh;
            border-radius: 1rem;
            background: #ffffff;
            overflow: hidden;
            box-shadow: 0 36px 80px rgba(0, 0, 0, 0.35);
            position: relative;
        }

        .review-image-modal-close {
            position: absolute;
            top: 0.65rem;
            right: 0.65rem;
            width: 2rem;
            height: 2rem;
            border: 0;
            border-radius: 999px;
            background: rgba(17, 17, 17, 0.88);
            color: #ffffff;
            font-size: 0.72rem;
            z-index: 2;
        }

        .review-image-modal-media {
            width: 100%;
            max-height: 78vh;
            display: block;
            object-fit: contain;
            background: #f8f8f8;
        }

        .review-image-modal-caption {
            border-top: 1px solid #efefef;
            padding: 0.65rem 0.9rem;
            color: #3f3f46;
            font-size: 0.82rem;
            font-weight: 600;
            text-align: center;
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

            .review-card-body {
                padding: 0.78rem;
                gap: 0.72rem;
            }

            .review-name {
                font-size: 0.82rem;
            }

            .review-card-headline {
                font-size: 0.92rem;
            }

            .review-card-text {
                font-size: 0.74rem;
                line-height: 1.64;
            }

            .review-image-modal-dialog {
                width: 100%;
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
        <div class="absolute left-[-8%] top-[-14%] h-80 w-80 rounded-full bg-[#c8a45d]/20 blur-3xl"></div>
        <div class="absolute right-[-10%] top-1/4 h-72 w-72 rounded-full bg-black/10 blur-3xl"></div>
    </div>

    <div class="relative">
        <x-public-header
            :site-name="$siteName"
            :site-logo="$siteLogo"
            active="reviews"
            :primary-label="$primaryLabel"
            :primary-link="$primaryLink"
            :secondary-label="$secondaryLabel"
            :secondary-link="$secondaryLink"
        />

        <main class="mx-auto w-full max-w-7xl px-4 pb-16 pt-8 sm:px-6 lg:px-8">
            <section class="pt-2 text-center">
                <p class="mt-4 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#9b7431]">Reviews</p>
                <h1 class="theme-display mt-2 text-3xl text-stone-950 sm:text-4xl" style="font-weight: 600">Customer Reviews</h1>
                <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-stone-600">Be our next wfh success story.</p>
            </section>

            @if($reviews->isEmpty())
                <section class="mt-6 rounded-[1.5rem] border border-dashed border-[#d8c6a1] bg-[#fffaf1] p-8 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full border border-[#e6d2ad] bg-white text-[#9b7431]">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h2 class="theme-display text-2xl text-stone-900">No reviews available yet</h2>
                    <p class="mx-auto mt-2 max-w-xl text-sm leading-7 text-stone-600">Please check back soon for new client feedback.</p>
                </section>
            @else
                <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($reviews as $review)
                        @php($imageUrl = $review->image_source_url)
                        <article class="review-card">
                            <button type="button" class="review-media-button" data-review-image="{{ $imageUrl }}" data-review-caption="{{ $review->customer_name }}">
                                @if($imageUrl)
                                    <img src="{{ $imageUrl }}" alt="Review image for {{ $review->customer_name }}" loading="lazy">
                                @else
                                    <span class="review-media-fallback">Review Image</span>
                                @endif
                            </button>

                            <div class="review-card-body">
                                <div class="review-card-meta">
                                    <div class="review-customer">
                                        <span class="review-country">
                                            {{ $review->country_label ?: 'US' }}
                                        </span>
                                        <span class="review-name">{{ $review->customer_name }}</span>
                                    </div>

                                    @if($review->is_verified)
                                        <span class="review-verified-pill">
                                            <i class="fas fa-check-circle"></i>
                                            Verified customer
                                        </span>
                                    @endif
                                </div>

                                <h3 class="review-card-headline">{{ $review->headline }}</h3>
                                <p class="review-card-text">{{ $review->review_text }}</p>

                                @if($review->product_name)
                                    <div class="review-product-box">
                                        <p class="review-product-note">{{ $review->customer_name }} uses...</p>
                                        @if($review->product_link)
                                            <a href="{{ $review->product_link }}" target="_blank" rel="noopener noreferrer" class="review-product-link">
                                                {{ $review->product_name }}
                                            </a>
                                        @else
                                            <span class="review-product-link">{{ $review->product_name }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </section>

                <div class="mt-6">
                    {{ $reviews->links() }}
                </div>
            @endif

            <x-site-footer />
        </main>
    </div>

    <div class="review-image-modal" data-review-image-modal aria-hidden="true">
        <div class="review-image-modal-dialog" role="dialog" aria-modal="true" aria-label="Review image preview">
            <button type="button" class="review-image-modal-close" data-review-image-close aria-label="Close image preview">
                <i class="fas fa-times"></i>
            </button>
            <img src="" alt="Review preview" class="review-image-modal-media" data-review-image-target>
            <div class="review-image-modal-caption" data-review-image-caption></div>
        </div>
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

            const modal = document.querySelector('[data-review-image-modal]');
            const target = modal?.querySelector('[data-review-image-target]');
            const caption = modal?.querySelector('[data-review-image-caption]');
            const closeButton = modal?.querySelector('[data-review-image-close]');

            if (!modal || !target) {
                return;
            }

            const closeModal = function () {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
                target.src = '';
                if (caption) {
                    caption.textContent = '';
                }
            };

            document.addEventListener('click', function (event) {
                const trigger = event.target.closest('[data-review-image]');
                if (!trigger) {
                    return;
                }

                const src = trigger.getAttribute('data-review-image');
                if (!src) {
                    return;
                }

                const text = trigger.getAttribute('data-review-caption') || 'Review preview';
                target.src = src;
                target.alt = text;
                if (caption) {
                    caption.textContent = text;
                }

                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            });

            closeButton?.addEventListener('click', closeModal);

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeModal();
                }
            });
        });
    </script>
</body>
</html>
