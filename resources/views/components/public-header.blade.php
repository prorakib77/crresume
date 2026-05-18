@props([
    'siteName',
    'siteLogo' => null,
    'active' => 'home',
    'primaryLabel' => 'Secure Your Spot',
    'primaryLink' => '/register',
    'secondaryLabel' => 'Login',
    'secondaryLink' => '/login',
    'showAnnouncement' => false,
    'announcementText' => '',
    'announcementItems' => [],
    'announcementLink' => '',
    'announcementBgColor' => '#111111',
    'announcementTextColor' => '#F7F2E8',
    'announcementSpeed' => 20,
])

@php
    $announcementText = trim((string) $announcementText);
    $announcementLink = trim((string) $announcementLink);
    $announcementItems = collect(is_array($announcementItems) ? $announcementItems : [])
        ->map(fn ($item) => trim((string) $item))
        ->filter()
        ->values();

    if ($announcementItems->isEmpty() && $announcementText !== '') {
        $announcementItems = collect(preg_split('/\r\n|\r|\n/', $announcementText))
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values();

        if ($announcementItems->isEmpty()) {
            $announcementItems = collect([$announcementText]);
        }
    }

    $showAnnouncement = (bool) $showAnnouncement && $announcementItems->isNotEmpty();
    $announcementDuration = max(6, min(60, (int) $announcementSpeed));
    $announcementOpenNewTab = $announcementLink !== ''
        && \Illuminate\Support\Str::startsWith(strtolower($announcementLink), ['http://', 'https://']);

    $navItems = [
        ['key' => 'home', 'label' => 'Home', 'href' => url('/')],
        ['key' => 'reviews', 'label' => 'Reviews', 'href' => route('reviews.page')],
        ['key' => 'faqs', 'label' => 'FAQs', 'href' => route('faqs.page')],
        ['key' => 'guide', 'label' => 'Guide', 'href' => route('guide.page')],
        ['key' => 'contact', 'label' => 'Contact', 'href' => route('contact.page')],
    ];

    $isAuthenticated = auth()->check();
    $hasPrimaryAction = trim((string) $primaryLabel) !== '' && trim((string) $primaryLink) !== '';
    $hasSecondaryAction = trim((string) $secondaryLabel) !== '' && trim((string) $secondaryLink) !== '';
    $mobileActionLabel = $isAuthenticated ? 'Dashboard' : ($hasSecondaryAction ? $secondaryLabel : ($hasPrimaryAction ? $primaryLabel : ''));
    $mobileActionLink = $isAuthenticated ? url('/dashboard') : ($hasSecondaryAction ? $secondaryLink : ($hasPrimaryAction ? $primaryLink : ''));
    $showMobileAction = $mobileActionLabel !== '' && $mobileActionLink !== '';
    $showDesktopPrimary = !$isAuthenticated && $hasPrimaryAction;
    $showDesktopSecondary = !$isAuthenticated && $hasSecondaryAction;
    $showMobilePrimary = !$isAuthenticated && $hasPrimaryAction;
    $navId = 'publicHeaderNav';
@endphp

@once
    <style>
        @keyframes public-header-announcement-scroll {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-33.3333%);
            }
        }

        .public-header-root {
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .public-header-announcement-track {
            display: flex;
            width: max-content;
            min-width: 100%;
            animation: public-header-announcement-scroll var(--public-announcement-duration, 20s) linear infinite;
            will-change: transform;
        }

        .public-header-announcement-group {
            display: inline-flex;
            min-width: 100vw;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            padding: 0.55rem 1rem;
            color: var(--public-announcement-text, #f7f2e8);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .public-header-announcement-item {
            color: var(--public-announcement-text, #f7f2e8) !important;
            text-decoration: none;
            transition: opacity 0.18s ease;
        }

        .public-header-announcement-item:hover {
            opacity: 0.85;
            color: var(--public-announcement-text, #f7f2e8) !important;
        }

        .public-header-announcement-bullet {
            color: var(--public-announcement-text, #f7f2e8) !important;
            opacity: 0.72;
            font-size: 0.92em;
        }

        .public-header-shell {
            border-top: 1px solid rgba(216, 198, 161, 0.35);
            border-bottom: 1px solid rgba(216, 198, 161, 0.35);
            background: rgba(246, 243, 236, 0.92);
            backdrop-filter: blur(14px);
            box-shadow: 0 14px 36px rgba(17, 17, 17, 0.06);
        }

        .public-header-mobile-bar {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 4.35rem;
            gap: 0.75rem;
        }

        .public-header-mobile-brand {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            max-width: 8.5rem;
            text-decoration: none;
        }

        .public-header-desktop {
            display: none;
            align-items: center;
            gap: 1.25rem;
            min-height: 4.6rem;
        }

        .public-header-brand {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 3.2rem;
            text-decoration: none;
        }

        .public-header-logo {
            width: auto;
            max-height: 3rem;
            display: block;
        }

        .public-header-logo-fallback {
            display: inline-flex;
            width: 2.9rem;
            height: 2.9rem;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            border: 1px solid #d8c6a1;
            background: #fbf5e8;
            color: #b68c3a;
        }

        .public-header-toggle {
            display: inline-flex;
            width: 2.75rem;
            height: 2.75rem;
            align-items: center;
            justify-content: center;
            border-radius: 0.95rem;
            border: 1px solid #dfcfaf;
            background: #fffaf1;
            color: #111111;
            font-size: 0.9rem;
            transition: transform 0.18s ease, background-color 0.18s ease, border-color 0.18s ease;
        }

        .public-header-toggle:hover {
            transform: translateY(-1px);
            background: #fff5df;
            border-color: #d8c6a1;
        }

        .public-header-mobile-action {
            display: inline-flex;
            min-height: 2.45rem;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid #111111;
            background: #111111;
            padding: 0.6rem 0.95rem;
            color: #ffffff;
            font-size: 0.64rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            text-decoration: none;
            white-space: nowrap;
            transition: transform 0.18s ease, background-color 0.18s ease, border-color 0.18s ease;
        }

        .public-header-mobile-action:hover {
            transform: translateY(-1px);
            background: #000000;
            border-color: #000000;
            color: #ffffff;
        }

        .public-header-mobile-panel {
            display: none;
            padding: 0 0 1rem;
        }

        .public-header-mobile-panel.is-open {
            display: block;
        }

        .public-header-mobile-panel-inner {
            border-top: 1px solid #eee4cf;
            padding-top: 1rem;
        }

        .public-header-mobile-nav {
            display: grid;
            gap: 0.55rem;
        }

        .public-header-mobile-link,
        .public-header-desktop-link {
            text-decoration: none;
            transition: transform 0.18s ease, background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease;
        }

        .public-header-mobile-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.85rem;
            border-radius: 1rem;
            border: 1px solid transparent;
            background: transparent;
            padding: 0.9rem 1rem;
            color: #1f1f1f;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .public-header-mobile-link:hover {
            border-color: #e3d1ac;
            background: #fff9ef;
            color: #111111;
        }

        .public-header-mobile-link.is-active {
            border-color: #e3d1ac;
            background: #fff7e7;
            color: #9b7431;
        }

        .public-header-mobile-cta {
            margin-top: 0.85rem;
            display: inline-flex;
            width: 100%;
            min-height: 2.9rem;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid #d8c6a1;
            background: transparent;
            padding: 0.8rem 1rem;
            color: #111111;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            text-decoration: none;
            transition: transform 0.18s ease, background-color 0.18s ease;
        }

        .public-header-mobile-cta:hover {
            transform: translateY(-1px);
            background: #fffaf1;
            color: #111111;
        }

        .public-header-desktop-nav {
            display: flex;
            flex: 1;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
        }

        .public-header-desktop-link {
            display: inline-flex;
            min-height: 2.55rem;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid transparent;
            padding: 0.62rem 1rem;
            color: #111111;
            font-size: 0.65rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
        }

        .public-header-desktop-link:hover {
            transform: translateY(-1px);
            border-color: #d8c6a1;
            background: #fffaf1;
            color: #111111;
        }

        .public-header-desktop-link.is-active {
            color: #9b7431;
        }

        .public-header-desktop-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.65rem;
        }

        .public-header-btn {
            display: inline-flex;
            min-height: 2.7rem;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0.65rem 1.1rem;
            font-size: 0.66rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            text-decoration: none;
            transition: transform 0.18s ease, background-color 0.18s ease, border-color 0.18s ease;
            white-space: nowrap;
        }

        .public-header-btn:hover {
            transform: translateY(-1px);
        }

        .public-header-btn-dark {
            border: 1px solid #111111;
            background: #111111;
            color: #ffffff;
        }

        .public-header-btn-dark:hover {
            background: #000000;
            color: #ffffff;
        }

        .public-header-btn-transparent {
            border: 1px solid #d8c6a1;
            background: transparent;
            color: #111111;
        }

        .public-header-btn-transparent:hover {
            background: #fffaf1;
            color: #111111;
        }

        @media (min-width: 768px) {
            .public-header-mobile-bar,
            .public-header-mobile-panel {
                display: none !important;
            }

            .public-header-desktop {
                display: flex;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .public-header-announcement-track {
                animation: none;
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-public-header]').forEach(function (headerRoot) {
                if (headerRoot.dataset.bound === 'true') {
                    return;
                }

                headerRoot.dataset.bound = 'true';

                const navToggle = headerRoot.querySelector('[data-public-nav-toggle]');
                const navPanel = headerRoot.querySelector('[data-public-nav-panel]');
                const navIcon = headerRoot.querySelector('[data-public-nav-icon]');

                if (!navToggle || !navPanel) {
                    return;
                }

                const setMenuOpen = function (isOpen) {
                    navPanel.classList.toggle('is-open', isOpen);
                    navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

                    if (navIcon) {
                        navIcon.classList.toggle('fa-bars', !isOpen);
                        navIcon.classList.toggle('fa-times', isOpen);
                    }
                };

                navToggle.addEventListener('click', function () {
                    setMenuOpen(!navPanel.classList.contains('is-open'));
                });

                navPanel.querySelectorAll('a').forEach(function (link) {
                    link.addEventListener('click', function () {
                        if (window.innerWidth < 768) {
                            setMenuOpen(false);
                        }
                    });
                });

                document.addEventListener('click', function (event) {
                    if (window.innerWidth >= 768) {
                        return;
                    }

                    if (!headerRoot.contains(event.target)) {
                        setMenuOpen(false);
                    }
                });

                window.addEventListener('resize', function () {
                    if (window.innerWidth >= 768) {
                        setMenuOpen(false);
                    }
                });
            });
        });
    </script>
@endonce

<header class="public-header-root" data-public-header>
    @if($showAnnouncement)
        <div
            class="overflow-hidden border-b border-[#d8c6a1]/40"
            style="background: {{ $announcementBgColor }}; color: {{ $announcementTextColor }}; --public-announcement-text: {{ $announcementTextColor }}; --public-announcement-duration: {{ $announcementDuration }}s;"
        >
            <div class="public-header-announcement-track" aria-label="Announcement">
                @for($i = 0; $i < 3; $i++)
                    <span class="public-header-announcement-group">
                        @foreach($announcementItems as $announcementItem)
                            @if($announcementLink !== '')
                                <a
                                    href="{{ $announcementLink }}"
                                    class="public-header-announcement-item"
                                    @if($announcementOpenNewTab) target="_blank" rel="noopener noreferrer" @endif
                                >{{ $announcementItem }}</a>
                            @else
                                <span class="public-header-announcement-item">{{ $announcementItem }}</span>
                            @endif

                            @if(!$loop->last)
                                <span class="public-header-announcement-bullet" aria-hidden="true">&bull;</span>
                            @endif
                        @endforeach
                    </span>
                @endfor
            </div>
        </div>
    @endif

    <div class="public-header-shell">
        <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">
            <div class="public-header-mobile-bar">
                <button
                    type="button"
                    class="public-header-toggle"
                    data-public-nav-toggle
                    aria-controls="{{ $navId }}"
                    aria-expanded="false"
                    aria-label="Toggle menu"
                >
                    <i class="fas fa-bars" data-public-nav-icon></i>
                </button>

                <a href="{{ url('/') }}" class="public-header-brand public-header-mobile-brand" aria-label="{{ $siteName }}">
                    @if($siteLogo)
                        <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="public-header-logo">
                    @else
                        <span class="public-header-logo-fallback">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5 stroke-current" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 19h16M5 19V9.6c0-.4.2-.7.5-.9l5.7-4.3c.5-.4 1.2-.4 1.7 0l5.6 4.3c.3.2.5.5.5.9V19M9 19v-5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v5" />
                            </svg>
                        </span>
                    @endif
                    <span class="sr-only">{{ $siteName }}</span>
                </a>

                @if($showMobileAction)
                    <a href="{{ $mobileActionLink }}" class="public-header-mobile-action" style="color: #fff !important;">{{ $mobileActionLabel }}</a>
                @else
                    <span aria-hidden="true" class="block w-[2.75rem]"></span>
                @endif
            </div>

            <div id="{{ $navId }}" class="public-header-mobile-panel" data-public-nav-panel>
                <div class="public-header-mobile-panel-inner">
                    <nav class="public-header-mobile-nav" aria-label="Mobile primary navigation">
                        @foreach($navItems as $item)
                            <a href="{{ $item['href'] }}" class="public-header-mobile-link {{ $active === $item['key'] ? 'is-active' : '' }}">
                                <span>{{ $item['label'] }}</span>
                                <i class="fas fa-arrow-right text-[11px]"></i>
                            </a>
                        @endforeach
                    </nav>

                    @if($showMobilePrimary)
                        <a href="{{ $primaryLink }}" class="public-header-mobile-cta">{{ $primaryLabel }}</a>
                    @endif
                </div>
            </div>

            <div class="public-header-desktop">
                <a href="{{ url('/') }}" class="public-header-brand" aria-label="{{ $siteName }}">
                    @if($siteLogo)
                        <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="public-header-logo">
                    @else
                        <span class="public-header-logo-fallback">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5 stroke-current" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 19h16M5 19V9.6c0-.4.2-.7.5-.9l5.7-4.3c.5-.4 1.2-.4 1.7 0l5.6 4.3c.3.2.5.5.5.9V19M9 19v-5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v5" />
                            </svg>
                        </span>
                    @endif
                    <span class="sr-only">{{ $siteName }}</span>
                </a>

                <nav class="public-header-desktop-nav" aria-label="Primary navigation">
                    @foreach($navItems as $item)
                        <a href="{{ $item['href'] }}" class="public-header-desktop-link {{ $active === $item['key'] ? 'is-active' : '' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="public-header-desktop-actions">
                    @if($isAuthenticated)
                        <a href="{{ url('/dashboard') }}" class="public-header-btn public-header-btn-dark text-white" style="color: #fff !important;">Dashboard</a>
                    @else
                        @if($showDesktopPrimary)
                            <a href="{{ $primaryLink }}" class="public-header-btn public-header-btn-transparent">{{ $primaryLabel }}</a>
                        @endif
                        @if($showDesktopSecondary)
                            <a href="{{ $secondaryLink }}" class="public-header-btn public-header-btn-dark text-white" style="color: #fff !important;">{{ $secondaryLabel }}</a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</header>
