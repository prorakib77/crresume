@php
    $slotHeaderText = isset($header) ? trim(strip_tags((string) $header)) : '';
    $sectionTitle = trim((string) $__env->yieldContent('title'));
    $sectionPageTitle = trim((string) ($__env->yieldContent('pageTitle') ?: $__env->yieldContent('page-title')));
    $sectionPageSubtitle = $__env->yieldContent('pageSubtitle') ?: $__env->yieldContent('page-subtitle');

    $resolvedPageTitle = trim((string) ($pageTitle ?? $sectionPageTitle ?: $slotHeaderText ?: 'Dashboard'));
    $resolvedPageSubtitle = $pageSubtitle ?? $sectionPageSubtitle;

    if (blank(trim(strip_tags((string) $resolvedPageSubtitle)))) {
        $resolvedPageSubtitle = auth()->check() ? 'Welcome back, ' . e(auth()->user()->name) : '';
    }

    $siteName = site_name();
    $siteLogoSetting = \App\Models\CustomizationSetting::getAllActive()->get('site_logo');
    $siteLogo = $siteLogoSetting?->setting_value
        ? storage_public_url($siteLogoSetting->setting_value, false) . '?v=' . ($siteLogoSetting->updated_at?->timestamp ?? time())
        : site_logo();
    $siteFaviconSetting = \App\Models\CustomizationSetting::getAllActive()->get('site_favicon');
    $siteFavicon = $siteFaviconSetting?->setting_value
        ? storage_public_url($siteFaviconSetting->setting_value, false) . '?v=' . ($siteFaviconSetting->updated_at?->timestamp ?? time())
        : site_favicon();
    $documentTitle = trim((string) ($title ?? $sectionTitle ?: $resolvedPageTitle ?: $siteName));
    $notificationsRoute = route('notifications.index');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $siteName }} - {{ $documentTitle }}</title>

    @if($siteFavicon)
        <link rel="icon" type="image/x-icon" href="{{ $siteFavicon }}">
        <link rel="shortcut icon" type="image/x-icon" href="{{ $siteFavicon }}">
    @endif

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <x-dynamic-styles />
    @stack('styles')
    <style>
        .top-navbar-actions .notification-menu-shell .notification-toggle {
            position: relative !important;
            overflow: visible !important;
        }

        .top-navbar-actions .notification-menu-shell .notification-unread-badge {
            position: absolute !important;
            top: -0.16rem !important;
            right: -0.1rem !important;
            left: auto !important;
            transform: none !important;
            z-index: 7 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 1.18rem !important;
            height: 1.18rem !important;
            padding: 0 0.24rem !important;
            border-radius: 999px !important;
            border: 2px solid #fffdfa !important;
            background: linear-gradient(180deg, #ef4444 0%, #dc2626 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 8px 16px rgba(17, 17, 17, 0.16) !important;
            box-sizing: border-box !important;
            font-size: 0.62rem !important;
            font-weight: 800 !important;
            line-height: 1 !important;
            letter-spacing: 0 !important;
            white-space: nowrap !important;
            pointer-events: none !important;
        }

        @media (max-width: 768px) {
            .top-navbar-actions .notification-menu-shell .notification-unread-badge {
                top: -0.1rem !important;
                right: -0.04rem !important;
                min-width: 1.24rem !important;
                height: 0.96rem !important;
                padding: 0 0.16rem !important;
                font-size: 0.5rem !important;
            }
        }

        @media (max-width: 480px) {
            .top-navbar-actions .notification-menu-shell .notification-unread-badge {
                top: -0.06rem !important;
                right: -0.02rem !important;
                min-width: 1.14rem !important;
                height: 0.88rem !important;
                padding: 0 0.13rem !important;
                font-size: 0.46rem !important;
            }
        }

        .sidebar-brand-logo {
            display: block;
            flex-shrink: 0;
            width: auto;
            max-width: 100%;
            max-height: 2.5rem;
            object-fit: contain;
        }
    </style>
</head>
<body class="app-body">
    @if(session()->has('impersonating'))
        <div class="impersonation-banner">
            <span><i class="fas fa-user-secret me-2"></i>You are impersonating {{ Auth::user()->name }}</span>
            <a href="{{ route('admin.stop-impersonating') }}" class="btn btn-sm btn-warning">
                <i class="fas fa-times me-1"></i>Stop Impersonating
            </a>
        </div>
    @endif

    @auth
        <div class="app-overlay" onclick="closeSidebar()"></div>

        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="{{ url('/') }}" class="sidebar-brand">
                    @if($siteLogo)
                        <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="sidebar-brand-logo">
                    @else
                        <i class="fas fa-home"></i>
                    @endif
                </a>

                <button type="button" class="sidebar-close-button d-lg-none" onclick="closeSidebar()" aria-label="Close sidebar">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <nav class="sidebar-nav">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                        <li class="nav-item">
                            <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                                <i class="fas fa-users"></i>
                                <span>Users</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.agents.index') }}" class="nav-link {{ request()->routeIs('admin.agents*') ? 'active' : '' }}">
                                <i class="fas fa-user-tie"></i>
                                <span>Agents</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.clients.index') }}" class="nav-link {{ request()->routeIs('admin.clients*') ? 'active' : '' }}">
                                <i class="fas fa-user-friends"></i>
                                <span>Clients</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.assignments') }}" class="nav-link {{ request()->routeIs('admin.assignments*') ? 'active' : '' }}">
                                <i class="fas fa-link"></i>
                                <span>Assignments</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <details class="nav-dropdown" @if(request()->routeIs('admin.products*') || request()->routeIs('admin.reviews*') || request()->routeIs('admin.faqs*') || request()->routeIs('admin.sale-countdowns*') || request()->routeIs('admin.client-sales-popups*')) open @endif>
                                <summary class="nav-link nav-dropdown-toggle {{ request()->routeIs('admin.products*') || request()->routeIs('admin.reviews*') || request()->routeIs('admin.faqs*') || request()->routeIs('admin.sale-countdowns*') || request()->routeIs('admin.client-sales-popups*') ? 'active' : '' }}">
                                    <span class="nav-dropdown-label">
                                        <i class="fas fa-box-open"></i>
                                        <span>Products</span>
                                    </span>
                                    <i class="fas fa-chevron-down nav-dropdown-caret"></i>
                                </summary>
                                <div class="nav-dropdown-panel">
                                    <a href="{{ route('admin.products.index', ['type' => 'full_service']) }}" class="nav-link nav-sublink {{ request()->routeIs('admin.products*') ? 'active' : '' }}">
                                        <i class="fas fa-crown"></i>
                                        <span>Full Service</span>
                                    </a>
                                    <a href="{{ route('admin.reviews.index') }}" class="nav-link nav-sublink {{ request()->routeIs('admin.reviews*') ? 'active' : '' }}">
                                        <i class="fas fa-comments"></i>
                                        <span>Reviews</span>
                                    </a>
                                    <a href="{{ route('admin.faqs.index') }}" class="nav-link nav-sublink {{ request()->routeIs('admin.faqs*') ? 'active' : '' }}">
                                        <i class="fas fa-circle-question"></i>
                                        <span>FAQs</span>
                                    </a>
                                    <a href="{{ route('admin.sale-countdowns.index') }}" class="nav-link nav-sublink {{ request()->routeIs('admin.sale-countdowns*') ? 'active' : '' }}">
                                        <i class="fas fa-hourglass-half"></i>
                                        <span>Countdown Sales</span>
                                    </a>
                                    <a href="{{ route('admin.client-sales-popups.index') }}" class="nav-link nav-sublink {{ request()->routeIs('admin.client-sales-popups*') ? 'active' : '' }}">
                                        <i class="fas fa-user-tag"></i>
                                        <span>Client Popups</span>
                                    </a>
                                </div>
                            </details>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.work-updates') }}" class="nav-link {{ request()->routeIs('admin.work-updates*') ? 'active' : '' }}">
                                <i class="fas fa-clipboard-list"></i>
                                <span>Work Updates</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.support-tickets.index') }}" class="nav-link {{ request()->routeIs('admin.support-tickets*') ? 'active' : '' }}">
                                <i class="fas fa-life-ring"></i>
                                <span>Support Tickets</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.payment-requests.index') }}" class="nav-link {{ request()->routeIs('admin.payment-requests*') ? 'active' : '' }}">
                                <i class="fas fa-dollar-sign"></i>
                                <span>Payment Requests</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.meeting-reports') }}" class="nav-link {{ request()->routeIs('admin.meeting-reports*') ? 'active' : '' }}">
                                <i class="fas fa-chart-line"></i>
                                <span>Meeting Reports</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.meeting-dashboard') }}" class="nav-link {{ request()->routeIs('admin.meeting-dashboard*') ? 'active' : '' }}">
                                <i class="fas fa-video"></i>
                                <span>Meeting Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.meeting-setup') }}" class="nav-link {{ request()->routeIs('admin.meeting-setup*') ? 'active' : '' }}">
                                <i class="fas fa-link"></i>
                                <span>Meeting Setup</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.settings') }}" class="nav-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                                <i class="fas fa-cog"></i>
                                <span>Settings</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.email-templates.index') }}" class="nav-link {{ request()->routeIs('admin.email-templates*') ? 'active' : '' }}">
                                <i class="fas fa-envelope-open-text"></i>
                                <span>Email Templates</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.user-email.index') }}" class="nav-link {{ request()->routeIs('admin.user-email.*') ? 'active' : '' }}">
                                <i class="fas fa-paper-plane"></i>
                                <span>Custom Email</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <details class="nav-dropdown" @if(request()->routeIs('admin.customization*') || request()->routeIs('admin.pdf-templates*')) open @endif>
                                <summary class="nav-link nav-dropdown-toggle {{ request()->routeIs('admin.customization*') || request()->routeIs('admin.pdf-templates*') ? 'active' : '' }}">
                                    <span class="nav-dropdown-label">
                                        <i class="fas fa-palette"></i>
                                        <span>Customization</span>
                                    </span>
                                    <i class="fas fa-chevron-down nav-dropdown-caret"></i>
                                </summary>
                                <div class="nav-dropdown-panel">
                                    <a href="{{ route('admin.customization.section', ['section' => 'identity']) }}" class="nav-link nav-sublink {{ request()->route('section') === 'identity' ? 'active' : '' }}">
                                        <i class="fas fa-fingerprint"></i>
                                        <span>Identity</span>
                                    </a>
                                    <a href="{{ route('admin.customization.section', ['section' => 'footer']) }}" class="nav-link nav-sublink {{ request()->route('section') === 'footer' ? 'active' : '' }}">
                                        <i class="fas fa-shoe-prints"></i>
                                        <span>Footer</span>
                                    </a>
                                    <a href="{{ route('admin.customization.section', ['section' => 'theme']) }}" class="nav-link nav-sublink {{ request()->route('section') === 'theme' ? 'active' : '' }}">
                                        <i class="fas fa-swatchbook"></i>
                                        <span>Theme Colors</span>
                                    </a>
                                    <a href="{{ route('admin.customization.section', ['section' => 'layout']) }}" class="nav-link nav-sublink {{ request()->route('section') === 'layout' ? 'active' : '' }}">
                                        <i class="fas fa-text-height"></i>
                                        <span>Layout & Fonts</span>
                                    </a>
                                    <a href="{{ route('admin.customization.section', ['section' => 'welcome']) }}" class="nav-link nav-sublink {{ request()->route('section') === 'welcome' ? 'active' : '' }}">
                                        <i class="fas fa-home"></i>
                                        <span>Welcome Content</span>
                                    </a>
                                    <a href="{{ route('admin.customization.section', ['section' => 'announcement']) }}" class="nav-link nav-sublink {{ request()->route('section') === 'announcement' ? 'active' : '' }}">
                                        <i class="fas fa-bullhorn"></i>
                                        <span>Announcement</span>
                                    </a>
                                    <a href="{{ route('admin.customization.section', ['section' => 'popup']) }}" class="nav-link nav-sublink {{ request()->route('section') === 'popup' ? 'active' : '' }}">
                                        <i class="fas fa-window-maximize"></i>
                                        <span>Popup</span>
                                    </a>
                                    <a href="{{ route('admin.customization.section', ['section' => 'email']) }}" class="nav-link nav-sublink {{ request()->route('section') === 'email' ? 'active' : '' }}">
                                        <i class="fas fa-envelope-open-text"></i>
                                        <span>Email</span>
                                    </a>
                                    <a href="{{ route('admin.customization.section', ['section' => 'pdf']) }}" class="nav-link nav-sublink {{ request()->route('section') === 'pdf' ? 'active' : '' }}">
                                        <i class="fas fa-file-pdf"></i>
                                        <span>PDF Customizer</span>
                                    </a>
                                    <a href="{{ route('admin.pdf-templates.index') }}" class="nav-link nav-sublink {{ request()->routeIs('admin.pdf-templates*') ? 'active' : '' }}">
                                        <i class="fas fa-file-lines"></i>
                                        <span>PDF Templates</span>
                                    </a>
                                    <a href="{{ route('admin.customization.section', ['section' => 'onboarding']) }}" class="nav-link nav-sublink {{ request()->route('section') === 'onboarding' ? 'active' : '' }}">
                                        <i class="fas fa-file-lines"></i>
                                        <span>Onboarding</span>
                                    </a>
                                    <a href="{{ route('admin.customization.section', ['section' => 'client-guide']) }}" class="nav-link nav-sublink {{ request()->route('section') === 'client-guide' ? 'active' : '' }}">
                                        <i class="fas fa-route"></i>
                                        <span>Client Guide</span>
                                    </a>
                                    <a href="{{ route('admin.customization.section', ['section' => 'policies']) }}" class="nav-link nav-sublink {{ request()->route('section') === 'policies' ? 'active' : '' }}">
                                        <i class="fas fa-scale-balanced"></i>
                                        <span>Policies</span>
                                    </a>
                                    <a href="{{ route('admin.customization.section', ['section' => 'code']) }}" class="nav-link nav-sublink {{ request()->route('section') === 'code' ? 'active' : '' }}">
                                        <i class="fas fa-code"></i>
                                        <span>Custom Code</span>
                                    </a>
                                </div>
                            </details>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.notices.index') }}" class="nav-link {{ request()->routeIs('admin.notices*') ? 'active' : '' }}">
                                <i class="fas fa-bullhorn"></i>
                                <span>Notices</span>
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->isAgent())
                        <li class="nav-item">
                            <a href="{{ route('agent.checkin.index') }}" class="nav-link {{ request()->routeIs('agent.checkin*') ? 'active' : '' }}">
                                <i class="fas fa-clock"></i>
                                <span>Check In/Out</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('agent.clients.index') }}" class="nav-link {{ request()->routeIs('agent.clients*') ? 'active' : '' }}">
                                <i class="fas fa-user-friends"></i>
                                <span>My Clients</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('agent.work-updates.create') }}" class="nav-link {{ request()->routeIs('agent.work-updates.create') ? 'active' : '' }}">
                                <i class="fas fa-plus"></i>
                                <span>Submit Update</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('agent.work-updates.index') }}" class="nav-link {{ request()->routeIs('agent.work-updates.index') ? 'active' : '' }}">
                                <i class="fas fa-list"></i>
                                <span>Work History</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('agent.work-updates.drafts') }}" class="nav-link {{ request()->routeIs('agent.work-updates.drafts*') ? 'active' : '' }}">
                                <i class="fas fa-save"></i>
                                <span>Drafts</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('agent.submissions.index') }}" class="nav-link {{ request()->routeIs('agent.submissions.*') ? 'active' : '' }}">
                                <i class="fas fa-key"></i>
                                <span>OTP Codes</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('agent.support-tickets.index') }}" class="nav-link {{ request()->routeIs('agent.support-tickets*') ? 'active' : '' }}">
                                <i class="fas fa-life-ring"></i>
                                <span>Support Tickets</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('agent.notices.index') }}" class="nav-link {{ request()->routeIs('agent.notices*') ? 'active' : '' }}">
                                <i class="fas fa-bullhorn"></i>
                                <span>Notices</span>
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->isClient())
                        <li class="nav-item">
                            <a href="{{ route('client.work-updates.index') }}" class="nav-link {{ request()->routeIs('client.work-updates.*') ? 'active' : '' }}">
                                <i class="fas fa-briefcase"></i>
                                <span>Work Updates</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('client.otp-requests.index') }}" class="nav-link {{ request()->routeIs('client.otp-requests.*') ? 'active' : '' }}">
                                <i class="fas fa-key"></i>
                                <span>Verification Codes</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('client.notices.index') }}" class="nav-link {{ request()->routeIs('client.notices.*') ? 'active' : '' }}">
                                <i class="fas fa-bullhorn"></i>
                                <span>Notices</span>
                            </a>
                        </li>
                        @php($clientProfile = auth()->user()->clientProfile)
                        @if($clientProfile && $clientProfile->shouldShowOnboardingForm())
                            <li class="nav-item">
                                <a href="{{ route('client.onboarding.create') }}" class="nav-link {{ request()->routeIs('client.onboarding.*') ? 'active' : '' }}">
                                    <i class="fas fa-file-alt"></i>
                                    <span>Submit Onboarding</span>
                                </a>
                            </li>
                        @endif
                        <li class="nav-item">
                            <a href="{{ route('client.support-tickets.index') }}" class="nav-link {{ request()->routeIs('client.support-tickets*') ? 'active' : '' }}">
                                <i class="fas fa-life-ring"></i>
                                <span>Support Tickets</span>
                            </a>
                        </li>
                    @endif

                    <li class="nav-item">
                        <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                            <i class="fas fa-bell"></i>
                            <span>Notifications</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
    @endauth

    <div class="main-content" @guest style="margin-left:0" @endguest>
        <audio id="notification-sound" preload="auto">
            <source src="https://actions.google.com/sounds/v1/alarms/beep_short.ogg" type="audio/ogg">
        </audio>

        <header class="top-navbar">
            <div class="top-navbar-main d-flex align-items-center gap-3">
                @auth
                    <button type="button" class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
                        <i class="fas fa-bars" data-sidebar-toggle-icon></i>
                    </button>
                @endauth

                <div class="page-heading">
                    <h1 class="page-title">{{ $resolvedPageTitle }}</h1>
                    @if(!blank(trim(strip_tags((string) $resolvedPageSubtitle))))
                        <div class="page-subtitle">{!! $resolvedPageSubtitle !!}</div>
                    @endif
                </div>
            </div>

            @auth
                <div class="top-navbar-actions d-flex align-items-center gap-3">
                    <div class="dropdown notification-menu-shell">
                        <button type="button" class="position-relative header-icon-button notification-toggle" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" aria-label="Open notifications">
                            <i class="fas fa-bell"></i>
                            <span class="notification-unread-badge d-none" id="unread-count">0</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown">
                            <div
                                class="notification-dropdown-hero"
                                style="margin: 0.35rem 0.35rem 0; border-radius: 1.25rem 1.25rem 1rem 1rem; border: 1px solid rgba(255,255,255,0.08); overflow: hidden; box-shadow: 0 16px 28px rgba(15,15,15,0.14);"
                            >
                                <div class="notification-dropdown-hero-copy">
                                    <div class="notification-dropdown-overline">Inbox</div>
                                    <div class="notification-dropdown-title-row">
                                        <h3 class="notification-dropdown-title">Notifications</h3>
                                        <span class="notification-summary-pill d-none" id="notifications-unread-summary">0 unread</span>
                                    </div>
                                    <p class="notification-dropdown-subtitle">Recent alerts, approvals, and updates from your workspace.</p>
                                </div>
                                <div class="notification-dropdown-actions">
                                    <button type="button" class="notification-header-action d-none" id="notifications-mark-all" onclick="markAllNotificationsAsRead()">
                                        <i class="fas fa-check-double"></i>
                                        <span>Mark all read</span>
                                    </button>
                                    <a href="{{ $notificationsRoute }}" class="notification-header-link">
                                        <span>Open Inbox</span>
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                            <div id="notifications-list" class="notification-list">
                                <div class="notification-empty-state is-loading">
                                    <div class="notification-empty-icon">
                                        <i class="fas fa-bell"></i>
                                    </div>
                                    <div class="notification-empty-title">Loading notifications</div>
                                    <div class="notification-empty-text">Pulling in the latest activity for your account.</div>
                                </div>
                            </div>
                            <div class="notification-dropdown-footer">
                                <a href="{{ $notificationsRoute }}" class="notification-footer-link">
                                    <span>View all notifications</span>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="dropdown">
                        <button type="button" class="user-avatar" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <div class="dropdown-header">
                                <div>{{ auth()->user()->name }}</div>
                                <div class="small text-muted">{{ auth()->user()->email }}</div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="fas fa-user me-2"></i>Profile
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endauth
        </header>

        <main class="content-area">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @hasSection('content')
                @yield('content')
            @else
                {{ $slot ?? '' }}
            @endif
        </main>

        @stack('floating-ui')

        <x-site-footer />
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        const sidebarCollapseStorageKey = 'dashboard.sidebar.collapsed';

        @auth
        (function initializeSuspensionAwareFetch() {
            if (typeof window.fetch !== 'function' || window.__suspensionAwareFetchInitialized) {
                return;
            }

            const originalFetch = window.fetch.bind(window);
            const fallbackRedirectUrl = @json(route('account.suspended'));

            window.fetch = async function (...args) {
                const response = await originalFetch(...args);

                if (response.status === 423) {
                    let redirectUrl = fallbackRedirectUrl;
                    const contentType = response.headers.get('content-type') || '';

                    if (contentType.includes('application/json')) {
                        try {
                            const payload = await response.clone().json();
                            redirectUrl = payload.redirect_to || redirectUrl;
                        } catch (error) {
                            redirectUrl = fallbackRedirectUrl;
                        }
                    }

                    if (window.location.href !== redirectUrl) {
                        window.location.assign(redirectUrl);
                    }
                }

                return response;
            };

            window.__suspensionAwareFetchInitialized = true;
        })();
        @endauth

        function isDesktopSidebarViewport() {
            return window.innerWidth > 1024;
        }

        function updateSidebarToggleIcon() {
            const icon = document.querySelector('[data-sidebar-toggle-icon]');
            if (!icon) {
                return;
            }

            icon.className = 'fas';

            if (isDesktopSidebarViewport()) {
                icon.classList.add(document.body.classList.contains('sidebar-collapsed') ? 'fa-angles-right' : 'fa-angles-left');
                return;
            }

            icon.classList.add(document.body.classList.contains('sidebar-open') ? 'fa-times' : 'fa-bars');
        }

        function setSidebarCollapseState(isCollapsed, persist = true) {
            if (!isDesktopSidebarViewport()) {
                document.body.classList.remove('sidebar-collapsed');
                updateSidebarToggleIcon();
                return;
            }

            document.body.classList.toggle('sidebar-collapsed', isCollapsed);

            if (persist) {
                try {
                    window.localStorage.setItem(sidebarCollapseStorageKey, isCollapsed ? '1' : '0');
                } catch (error) {
                    console.warn('Unable to persist sidebar state.', error);
                }
            }

            updateSidebarToggleIcon();
        }

        function setSidebarState(isOpen) {
            if (isDesktopSidebarViewport()) {
                document.body.classList.remove('sidebar-open');
                updateSidebarToggleIcon();
                return;
            }

            document.body.classList.toggle('sidebar-open', isOpen);
            updateSidebarToggleIcon();
        }

        function applySidebarViewportState() {
            if (isDesktopSidebarViewport()) {
                document.body.classList.remove('sidebar-open');

                let shouldCollapse = false;

                try {
                    shouldCollapse = window.localStorage.getItem(sidebarCollapseStorageKey) === '1';
                } catch (error) {
                    shouldCollapse = false;
                }

                document.body.classList.toggle('sidebar-collapsed', shouldCollapse);
            } else {
                document.body.classList.remove('sidebar-collapsed');
                document.body.classList.remove('sidebar-open');
            }

            updateSidebarToggleIcon();
        }

        function initializeSidebarLinkTitles() {
            document.querySelectorAll('.sidebar .nav-link').forEach(function (link) {
                const labelNode =
                    link.querySelector('.nav-dropdown-label > span:last-child') ||
                    link.querySelector('span');

                const label = (labelNode?.textContent || link.textContent || '').trim();

                if (label !== '') {
                    link.setAttribute('title', label);
                }
            });
        }

        function initializeCollapsedSidebarDropdowns() {
            document.querySelectorAll('.sidebar .nav-dropdown > summary').forEach(function (summary) {
                summary.addEventListener('click', function (event) {
                    if (!isDesktopSidebarViewport() || !document.body.classList.contains('sidebar-collapsed')) {
                        return;
                    }

                    event.preventDefault();

                    const dropdown = summary.parentElement;
                    setSidebarCollapseState(false);

                    window.requestAnimationFrame(function () {
                        if (dropdown) {
                            dropdown.open = true;
                        }
                    });
                });
            });
        }

        function toggleSidebar() {
            if (isDesktopSidebarViewport()) {
                setSidebarCollapseState(!document.body.classList.contains('sidebar-collapsed'));
                return;
            }

            setSidebarState(!document.body.classList.contains('sidebar-open'));
        }

        function closeSidebar() {
            setSidebarState(false);
        }

        window.addEventListener('resize', function () {
            applySidebarViewportState();
            refreshNotificationBadgeDisplay();
        });

        document.addEventListener('DOMContentLoaded', function () {
            initializeSidebarLinkTitles();
            initializeCollapsedSidebarDropdowns();
            applySidebarViewportState();
        });

        window.setTimeout(function () {
            document.querySelectorAll('.alert').forEach(function (alertElement) {
                if (alertElement.closest('.payment-fixed-alert')) {
                    return;
                }

                if (window.bootstrap && window.bootstrap.Alert) {
                    new window.bootstrap.Alert(alertElement).close();
                }
            });
        }, 5000);

        let lastSeenNotificationId = null;
        let notificationPermissionRequested = false;

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function getNotificationMeta(type) {
            switch (type) {
                case 'success':
                    return { icon: 'fa-check-circle', tone: 'success', label: 'Success' };
                case 'warning':
                    return { icon: 'fa-exclamation-triangle', tone: 'warning', label: 'Warning' };
                case 'error':
                    return { icon: 'fa-times-circle', tone: 'error', label: 'Error' };
                default:
                    return { icon: 'fa-info-circle', tone: 'info', label: 'Info' };
            }
        }

        function formatNotificationTime(value) {
            const date = new Date(value);
            const diff = Date.now() - date.getTime();
            const minutes = Math.round(diff / 60000);

            if (minutes < 1) {
                return 'Just now';
            }

            if (minutes < 60) {
                return `${minutes} min ago`;
            }

            const hours = Math.round(minutes / 60);
            if (hours < 24) {
                return `${hours} hr ago`;
            }

            const days = Math.round(hours / 24);
            if (days < 7) {
                return `${days} day${days === 1 ? '' : 's'} ago`;
            }

            return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
        }

        function renderNotificationEmptyState(title, message, icon = 'fa-bell-slash') {
            return `
                <div class="notification-empty-state">
                    <div class="notification-empty-icon">
                        <i class="fas ${icon}"></i>
                    </div>
                    <div class="notification-empty-title">${escapeHtml(title)}</div>
                    <div class="notification-empty-text">${escapeHtml(message)}</div>
                </div>
            `;
        }

        function formatNotificationBadgeCount(count) {
            const numericCount = Number(count) || 0;

            if (numericCount <= 0) {
                return '0';
            }

            if (!window.matchMedia('(max-width: 768px)').matches) {
                return String(numericCount);
            }

            if (numericCount <= 99) {
                return String(numericCount);
            }

            return '99+';
        }

        function refreshNotificationBadgeDisplay() {
            const unreadBadge = document.getElementById('unread-count');
            const notificationToggle = document.querySelector('.notification-toggle');

            if (!unreadBadge) {
                return;
            }

            const numericCount = Number(unreadBadge.dataset.count || unreadBadge.textContent || 0) || 0;
            unreadBadge.textContent = formatNotificationBadgeCount(numericCount);
            unreadBadge.title = numericCount > 0 ? `${numericCount} unread notifications` : '';
            unreadBadge.setAttribute('aria-label', numericCount > 0 ? `${numericCount} unread notifications` : 'No unread notifications');

            if (notificationToggle) {
                notificationToggle.setAttribute(
                    'aria-label',
                    numericCount > 0 ? `Open notifications (${numericCount} unread)` : 'Open notifications'
                );
            }
        }

        function loadNotifications() {
            const unreadBadge = document.getElementById('unread-count');
            const unreadSummary = document.getElementById('notifications-unread-summary');
            const markAllButton = document.getElementById('notifications-mark-all');
            const notificationToggle = document.querySelector('.notification-toggle');
            if (!unreadBadge) return;

            fetch('/notifications/unread-count')
                .then(response => response.json())
                .then(data => {
                    const notificationCount = Number(data.count) || 0;

                    unreadBadge.dataset.count = notificationCount;
                    unreadBadge.classList.toggle('d-none', notificationCount <= 0);
                    notificationToggle?.classList.toggle('has-unread', notificationCount > 0);
                    refreshNotificationBadgeDisplay();

                    if (unreadSummary) {
                        unreadSummary.textContent = `${notificationCount} unread`;
                        unreadSummary.classList.toggle('d-none', notificationCount <= 0);
                    }

                    if (markAllButton) {
                        markAllButton.classList.toggle('d-none', notificationCount <= 0);
                    }
                })
                .catch(error => console.error('Error loading notifications:', error));
        }

        function loadRecentNotifications() {
            const container = document.getElementById('notifications-list');
            if (!container) return;

            fetch('/notifications?limit=5')
                .then(response => response.json())
                .then(data => {
                    if (!data.notifications || data.notifications.length === 0) {
                        container.innerHTML = renderNotificationEmptyState(
                            'No notifications yet',
                            'You are all caught up. New activity will show here when it arrives.'
                        );
                        return;
                    }

                    const newestId = data.notifications[0]?.id || null;
                    if (newestId && lastSeenNotificationId && newestId > lastSeenNotificationId) {
                        triggerBrowserNotification(data.notifications[0]);
                    }
                    lastSeenNotificationId = newestId;

                    container.innerHTML = data.notifications.map(notification => {
                        const meta = getNotificationMeta(notification.type);
                        const url = notification.resolved_action_url || notification.action_url || '#';
                        const title = escapeHtml(notification.title);
                        const message = escapeHtml(notification.message);
                        const time = escapeHtml(formatNotificationTime(notification.created_at));
                        const badge = notification.read_at
                            ? ''
                            : '<span class="notification-entry-badge">New</span>';

                        return `
                            <button type="button"
                                class="notification-entry ${notification.read_at ? '' : 'unread-notification'}"
                                onclick="handleNotificationClick(${notification.id}, ${JSON.stringify(url)})">
                                <span class="notification-entry-icon ${meta.tone}">
                                    <i class="fas ${meta.icon}"></i>
                                </span>
                                <span class="notification-entry-body">
                                    <span class="notification-entry-topline">
                                        <span class="notification-entry-title">${title}</span>
                                        ${badge}
                                    </span>
                                    <span class="notification-entry-message">${message}</span>
                                    <span class="notification-entry-meta">
                                        <span class="notification-entry-type">${meta.label}</span>
                                        <span class="notification-entry-separator"></span>
                                        <span class="notification-entry-time">${time}</span>
                                    </span>
                                </span>
                                <span class="notification-entry-arrow">
                                    <i class="fas fa-chevron-right"></i>
                                </span>
                                ${notification.read_at ? '' : '<span class="unread-dot"></span>'}
                            </button>
                        `;
                    }).join('');
                })
                .catch(error => {
                    console.error('Error loading recent notifications:', error);
                    container.innerHTML = renderNotificationEmptyState(
                        'Unable to load notifications',
                        'Please try again in a moment.',
                        'fa-circle-exclamation'
                    );
                });
        }

        function markAllNotificationsAsRead() {
            fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                    loadRecentNotifications();
                }
            })
            .catch(error => console.error('Error marking all notifications as read:', error));
        }

        function markNotificationAsRead(notificationId, callback) {
            fetch(`/notifications/${notificationId}/mark-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                    loadRecentNotifications();
                    if (typeof callback === 'function') callback();
                }
            })
            .catch(error => console.error('Error marking notification as read:', error));
        }

        function handleNotificationClick(id, url) {
            markNotificationAsRead(id, function () {
                if (url && url !== '#') {
                    window.location.href = url;
                }
            });
        }

        function requestBrowserNotificationPermission() {
            if (notificationPermissionRequested || !('Notification' in window)) return;
            notificationPermissionRequested = true;

            Notification.requestPermission().then(permission => {
                if (permission !== 'granted') {
                    console.warn('Browser notifications not enabled by user.');
                }
            });
        }

        function playNotificationSound() {
            const audio = document.getElementById('notification-sound');
            if (audio) {
                audio.currentTime = 0;
                audio.play().catch(() => {});
            }
        }

        function triggerBrowserNotification(notification) {
            if (!('Notification' in window)) {
                playNotificationSound();
                return;
            }

            if (Notification.permission === 'granted') {
                const browserNotification = new Notification(notification.title || 'New Notification', {
                    body: notification.message || '',
                    tag: 'app-notification',
                });

                browserNotification.onclick = () => {
                    const target = notification.resolved_action_url || notification.action_url || window.location.href;
                    window.open(target, '_self');
                };

                playNotificationSound();
            } else if (Notification.permission !== 'denied') {
                requestBrowserNotificationPermission();
            }
        }

        async function downloadFile(url, filename) {
            try {
                const response = await fetch(url, { credentials: 'same-origin' });
                if (!response.ok) throw new Error('Download failed');

                const blob = await response.blob();
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = filename || 'download';
                document.body.appendChild(link);
                link.click();
                link.remove();
            } catch (error) {
                console.error('Download error', error);
                alert('Unable to download file. Please try again.');
            }
        }

        document.addEventListener('click', function (event) {
            const downloadTrigger = event.target.closest('.js-download-file');
            if (!downloadTrigger) return;

            event.preventDefault();
            downloadFile(downloadTrigger.getAttribute('data-url'), downloadTrigger.getAttribute('data-filename'));
        });

        if (document.getElementById('notifications-list')) {
            requestBrowserNotificationPermission();
            loadNotifications();
            loadRecentNotifications();
            window.setInterval(loadNotifications, 30000);
            window.setInterval(loadRecentNotifications, 30000);
        }
    </script>

    @livewireScriptConfig
    @stack('scripts')
</body>
</html>
