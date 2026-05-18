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
    <title>{{ $siteName }} - Contact</title>

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
        <div class="absolute left-[-12%] top-[-14%] h-80 w-80 rounded-full bg-[#c8a45d]/20 blur-3xl"></div>
        <div class="absolute right-[-8%] top-1/4 h-72 w-72 rounded-full bg-black/10 blur-3xl"></div>
    </div>

    <div class="relative">
        <x-public-header
            :site-name="$siteName"
            :site-logo="$siteLogo"
            active="contact"
            :primary-label="$primaryLabel"
            :primary-link="$primaryLink"
            :secondary-label="$secondaryLabel"
            :secondary-link="$secondaryLink"
        />

        <main class="mx-auto w-full max-w-7xl px-4 pb-16 pt-8 sm:px-6 lg:px-8">
            <section class="mx-auto max-w-3xl text-center">
                <p class="mt-4 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#9b7431]">Contact</p>
                <h1 class="theme-display mt-2 text-3xl font-black text-stone-950 sm:text-4xl" style="font-weight: 600">Send Us a Message</h1>
                <p class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-stone-600">Please use the form to send your questions directly to our team. We will review your queries and respond as soon as possible.</p>

                <div class="mt-6 flex justify-center" style="margin-bottom: 30px">
                    <div class="w-full max-w-md rounded-xl border border-[#ece4d5] bg-transparent px-4 py-3 text-center">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-stone-500">Response Window</p>
                        <p class="mt-1 text-sm font-semibold text-stone-900">Usually within 24 hours</p>
                        <p class="mt-1 text-sm font-light italic text-stone-700">Please note that it may take 1 to 3 business days to receive a response.</p>
                    </div>
                </div>
            </section>

            <section class="mx-auto mt-8 w-full max-w-4xl rounded-[1.5rem] border border-[#e7dcc5] bg-white/95 p-6 shadow-[0_24px_58px_rgba(17,17,17,0.06)]">
                    @if(session('success'))
                        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            <p class="font-semibold">Please fix the errors below:</p>
                            <ul class="mt-2 list-disc ps-4">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.submit') }}" class="grid gap-4 md:grid-cols-2">
                        @csrf

                        <div>
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control" maxlength="120" required>
                        </div>

                        <div>
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" maxlength="190" required>
                        </div>

                        <div>
                            <label for="phone" class="form-label">Phone (Optional)</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="form-control" maxlength="40">
                        </div>

                        <div>
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" id="subject" name="subject" value="{{ old('subject') }}" class="form-control" maxlength="190" required>
                        </div>

                        <div class="md:col-span-2">
                            <label for="message" class="form-label">Message</label>
                            <textarea id="message" name="message" rows="7" maxlength="5000" class="form-control" required>{{ old('message') }}</textarea>
                        </div>

                        <div class="md:col-span-2 text-center">
                            <button type="submit" class="btn btn-black w-full sm:w-auto">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </div>
                    </form>
            </section>

            <x-site-footer />
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const navToggle = document.querySelector('[data-welcome-nav-toggle]');
            const navMenu = document.querySelector('[data-welcome-nav]');
            const navIcon = document.querySelector('[data-welcome-nav-icon]');

            if (!navToggle || !navMenu) {
                return;
            }

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
        });
    </script>
</body>
</html>
