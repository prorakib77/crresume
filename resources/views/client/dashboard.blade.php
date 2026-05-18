@php
    use Illuminate\Support\Facades\Storage;
    use App\Models\PaymentRequest;
@endphp

<x-app-layout>
    <x-slot name="title">Client Dashboard</x-slot>
    <x-slot name="pageTitle">Dashboard</x-slot>
    <x-slot name="pageSubtitle">Welcome back, {{ auth()->user()->name }}</x-slot>

    @php
        $clientSalesPopup = $clientSalesPopup ?? null;
        $showClientSalesPopup = $clientSalesPopup !== null;
        $serviceDaysRemaining = $assignment?->getDaysRemaining();
        $serviceCompleted = $assignment?->isServiceCompleted() ?? false;
        $clientSalesPopupImage = $showClientSalesPopup ? $clientSalesPopup->image_source_url : null;
        $clientSalesPopupOpenNewTab = $showClientSalesPopup
            ? \Illuminate\Support\Str::startsWith(strtolower((string) $clientSalesPopup->cta_link), ['http://', 'https://'])
            : false;
        $clientSalesPopupDelay = $showClientSalesPopup ? max(0, min(15, (int) $clientSalesPopup->show_delay)) : 0;
        $timelineItems = [
            [
                'label' => 'Resume',
                'icon' => 'fa-file-lines',
                'date' => $profile?->estimated_resume_completion_date,
            ],
            [
                'label' => 'Onboarding',
                'icon' => 'fa-clipboard-check',
                'date' => $profile?->estimated_cover_letter_completion_date,
            ],
            [
                'label' => 'Job Apply',
                'icon' => 'fa-briefcase',
                'date' => $profile?->estimated_application_start_date,
            ],
        ];
        $timelineItems = collect($timelineItems)
            ->filter(fn (array $item) => filled($item['date']))
            ->map(function (array $item) {
                $item['date'] = \Illuminate\Support\Carbon::parse($item['date']);

                return $item;
            })
            ->filter(fn (array $item) => $item['date']->copy()->endOfDay()->greaterThanOrEqualTo(now()))
            ->values()
            ->all();
    @endphp

    @if($showClientSalesPopup)
        <style>
            .client-sales-popup-overlay {
                position: fixed;
                inset: 0;
                z-index: 1500;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 1rem;
                background: rgba(10, 10, 10, 0.62);
                backdrop-filter: blur(2px);
            }

            .client-sales-popup-overlay.is-open {
                display: flex;
            }

            .client-sales-popup-dialog {
                width: min(96vw, 900px);
                border-radius: 1.4rem;
                overflow: hidden;
                border: 1px solid color-mix(in srgb, var(--client-popup-accent, #c8a45d) 44%, transparent);
                background: linear-gradient(130deg, #ffffff 0%, #f8f3e9 48%, #ffffff 100%);
                box-shadow: 0 36px 90px rgba(0, 0, 0, 0.28);
                color: var(--client-popup-text, #111111);
                position: relative;
            }

            .client-sales-popup-close {
                position: absolute;
                top: 0.85rem;
                right: 0.85rem;
                width: 2.2rem;
                height: 2.2rem;
                border: 1px solid rgba(15, 15, 15, 0.14);
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.95);
                color: #111111;
                font-size: 0.78rem;
                z-index: 2;
            }

            .client-sales-popup-grid {
                display: grid;
                grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr);
                min-height: 360px;
            }

            .client-sales-popup-media {
                position: relative;
                background: color-mix(in srgb, var(--client-popup-bg, #111111) 80%, #ffffff 20%);
            }

            .client-sales-popup-media::after {
                content: "";
                position: absolute;
                inset: 0;
                background: linear-gradient(180deg, rgba(0, 0, 0, 0.08) 0%, rgba(0, 0, 0, 0.58) 100%);
            }

            .client-sales-popup-media img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }

            .client-sales-popup-media-fallback {
                width: 100%;
                height: 100%;
                display: grid;
                align-content: center;
                justify-items: center;
                gap: 0.35rem;
                padding: 1rem;
                color: #fff6de;
                text-align: center;
            }

            .client-sales-popup-media-fallback strong {
                font-size: 0.82rem;
                letter-spacing: 0.16em;
                text-transform: uppercase;
            }

            .client-sales-popup-media-fallback span {
                font-size: 0.75rem;
                opacity: 0.9;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .client-sales-popup-content {
                display: grid;
                align-content: center;
                gap: 0.9rem;
                padding: 1.5rem 1.35rem 1.35rem;
            }

            .client-sales-popup-badge {
                display: inline-flex;
                width: fit-content;
                border-radius: 999px;
                border: 1px solid color-mix(in srgb, var(--client-popup-accent, #c8a45d) 60%, transparent);
                background: color-mix(in srgb, var(--client-popup-accent, #c8a45d) 16%, transparent);
                color: #725011;
                font-size: 0.58rem;
                font-weight: 800;
                letter-spacing: 0.18em;
                text-transform: uppercase;
                padding: 0.33rem 0.62rem;
            }

            .client-sales-popup-title {
                margin: 0;
                color: #111111;
                font-family: var(--display-font-family, 'Poppins'), sans-serif;
                font-size: 1.32rem;
                font-weight: 700;
                line-height: 1.22;
            }

            .client-sales-popup-copy {
                margin: 0;
                color: #514b43;
                font-size: 0.84rem;
                line-height: 1.7;
            }

            .client-sales-popup-price {
                display: inline-flex;
                width: fit-content;
                border-radius: 0.82rem;
                border: 1px solid rgba(15, 15, 15, 0.12);
                background: #fff;
                color: #111111;
                font-size: 1rem;
                font-weight: 700;
                padding: 0.46rem 0.72rem;
            }

            .client-sales-popup-target {
                margin: 0;
                color: #7c7368;
                font-size: 0.72rem;
                font-weight: 700;
                letter-spacing: 0.12em;
                text-transform: uppercase;
            }

            .client-sales-popup-actions {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                margin-top: 0.2rem;
            }

            .client-sales-popup-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 2.45rem;
                border-radius: 999px;
                border: 1px solid var(--client-popup-accent, #c8a45d);
                background: var(--client-popup-accent, #c8a45d);
                color: #111111 !important;
                font-size: 0.64rem;
                font-weight: 800;
                letter-spacing: 0.18em;
                text-transform: uppercase;
                text-decoration: none;
                padding: 0.58rem 1.1rem;
                transition: transform 0.18s ease, filter 0.18s ease;
            }

            .client-sales-popup-btn:hover {
                color: #111111;
                transform: scale(1.02);
                filter: brightness(0.95);
            }

            @media (max-width: 767px) {
                .client-sales-popup-dialog {
                    width: 100%;
                }

                .client-sales-popup-grid {
                    grid-template-columns: 1fr;
                    min-height: auto;
                }

                .client-sales-popup-media {
                    min-height: 190px;
                }

                .client-sales-popup-content {
                    padding: 1.05rem 1rem 1rem;
                    gap: 0.72rem;
                }

                .client-sales-popup-title {
                    font-size: 1.03rem;
                }

                .client-sales-popup-copy {
                    font-size: 0.76rem;
                    line-height: 1.58;
                }

                .client-sales-popup-price {
                    font-size: 0.9rem;
                }

                .client-sales-popup-btn {
                    width: 100%;
                    min-height: 2.32rem;
                    font-size: 0.58rem;
                }
            }
        </style>

        <div
            id="client-sales-popup"
            class="client-sales-popup-overlay"
            data-client-popup
            data-client-popup-id="{{ $clientSalesPopup->id }}"
            data-client-popup-delay="{{ $clientSalesPopupDelay }}"
            aria-hidden="true"
        >
            <div
                class="client-sales-popup-dialog"
                role="dialog"
                aria-modal="true"
                aria-label="Client sales popup"
                style="--client-popup-bg: {{ $clientSalesPopup->bg_color ?: '#111111' }}; --client-popup-text: {{ $clientSalesPopup->text_color ?: '#FFFFFF' }}; --client-popup-accent: {{ $clientSalesPopup->accent_color ?: '#C8A45D' }};"
            >
                <button type="button" class="client-sales-popup-close" data-client-popup-close aria-label="Close popup">
                    <i class="fas fa-times"></i>
                </button>

                <div class="client-sales-popup-grid">
                    <div class="client-sales-popup-media">
                        @if($clientSalesPopupImage)
                            <img src="{{ $clientSalesPopupImage }}" alt="{{ $clientSalesPopup->title }}" loading="lazy">
                        @else
                            <div class="client-sales-popup-media-fallback">
                                <strong>{{ $clientSalesPopup->badge_text ?: 'Exclusive Offer' }}</strong>
                                <span>{{ site_name() }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="client-sales-popup-content">
                        @if($clientSalesPopup->badge_text)
                            <span class="client-sales-popup-badge">{{ $clientSalesPopup->badge_text }}</span>
                        @endif

                        <h2 class="client-sales-popup-title">{{ $clientSalesPopup->title }}</h2>

                        @if($clientSalesPopup->message)
                            <p class="client-sales-popup-copy">{{ $clientSalesPopup->message }}</p>
                        @endif

                        @if($clientSalesPopup->price_text)
                            <div class="client-sales-popup-price">{{ $clientSalesPopup->price_text }}</div>
                        @endif

                        <p class="client-sales-popup-target">
                            @if($clientSalesPopup->target_type === \App\Models\ClientSalesPopup::TARGET_SPECIFIC)
                                Personalized Client Offer
                            @else
                                Recurring Client Offer
                            @endif
                        </p>

                        <div class="client-sales-popup-actions">
                            <a
                                href="{{ $clientSalesPopup->cta_link ?: '#' }}"
                                class="client-sales-popup-btn"
                                @if($clientSalesPopupOpenNewTab) target="_blank" rel="noopener noreferrer" @endif
                            >
                                {{ $clientSalesPopup->cta_label ?: 'Book Now' }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const popup = document.querySelector('[data-client-popup]');
                if (!popup) {
                    return;
                }

                const popupId = popup.getAttribute('data-client-popup-id');
                const storageKey = popupId ? `client_dashboard_popup_closed_${popupId}` : '';
                const closeButton = popup.querySelector('[data-client-popup-close]');
                const delaySeconds = Number(popup.getAttribute('data-client-popup-delay') || 0);
                const delayMs = Number.isFinite(delaySeconds) ? Math.max(0, delaySeconds * 1000) : 0;

                if (storageKey && window.sessionStorage.getItem(storageKey) === '1') {
                    return;
                }

                const openPopup = function () {
                    popup.classList.add('is-open');
                    popup.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                };

                const closePopup = function () {
                    popup.classList.remove('is-open');
                    popup.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';

                    if (storageKey) {
                        window.sessionStorage.setItem(storageKey, '1');
                    }
                };

                closeButton?.addEventListener('click', closePopup);

                popup.addEventListener('click', function (event) {
                    if (event.target === popup) {
                        closePopup();
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && popup.classList.contains('is-open')) {
                        closePopup();
                    }
                });

                window.addEventListener('load', function () {
                    window.setTimeout(openPopup, delayMs);
                }, { once: true });
            });
        </script>
    @endif

    <style>
        .client-timeline-countdown-shell {
            margin: 0 0 1rem;
        }

        .client-timeline-countdown-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.65rem;
        }

        .client-timeline-item {
            min-width: 0;
            border: 1px solid rgba(17, 17, 17, 0.1);
            border-radius: 0.95rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8f4eb 100%);
            padding: 0.62rem 0.7rem;
            box-shadow: 0 8px 22px rgba(17, 17, 17, 0.04);
        }

        .client-timeline-item.is-passed {
            border-color: rgba(185, 28, 28, 0.2);
            background: linear-gradient(180deg, #ffffff 0%, #fff2f2 100%);
        }

        .client-timeline-head {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            min-width: 0;
        }

        .client-timeline-icon {
            width: 1.65rem;
            height: 1.65rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #111111;
            color: #ffffff;
            font-size: 0.7rem;
            flex-shrink: 0;
        }

        .client-timeline-text {
            min-width: 0;
        }

        .client-timeline-title {
            margin: 0;
            color: #111111;
            font-size: 0.74rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .client-timeline-date {
            margin: 0.12rem 0 0;
            color: #6d655a;
            font-size: 0.66rem;
            line-height: 1.2;
        }

        .client-timeline-count {
            margin-top: 0.5rem;
            color: #111111;
            font-size: 0.72rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .client-timeline-item.is-passed .client-timeline-count {
            color: #b91c1c;
        }

        @media (max-width: 767px) {
            .client-timeline-countdown-grid {
                gap: 0.45rem;
            }

            .client-timeline-item {
                padding: 0.52rem 0.5rem;
                border-radius: 0.8rem;
            }

            .client-timeline-icon {
                width: 1.5rem;
                height: 1.5rem;
                font-size: 0.64rem;
            }

            .client-timeline-title {
                font-size: 0.66rem;
            }

            .client-timeline-date {
                font-size: 0.58rem;
            }

            .client-timeline-count {
                font-size: 0.64rem;
                margin-top: 0.35rem;
            }
        }
    </style>

    @if(!empty($timelineItems))
        <section class="client-timeline-countdown-shell" aria-label="Estimated timeline countdown">
            <div class="client-timeline-countdown-grid">
                @foreach($timelineItems as $item)
                    @php
                        $targetDate = $item['date']->copy()->endOfDay();
                        $stateClass = '';
                        $countText = 'Due today';
                        $now = now();
                        $secondsRemaining = $now->diffInSeconds($targetDate, false);

                        if ($secondsRemaining > 0) {
                            $daysLeft = (int) floor($secondsRemaining / 86400);
                            $hoursLeft = (int) floor(($secondsRemaining % 86400) / 3600);
                            $countText = $daysLeft > 0
                                ? "{$daysLeft}d {$hoursLeft}h left"
                                : "{$hoursLeft}h left";
                        } elseif (!$now->isSameDay($targetDate)) {
                            $stateClass = 'is-passed';
                            $daysPast = abs((int) $now->copy()->startOfDay()->diffInDays($targetDate->copy()->startOfDay(), false));
                            $countText = "Passed {$daysPast}d ago";
                        }
                    @endphp

                    <article class="client-timeline-item {{ $stateClass }}">
                        <div class="client-timeline-head">
                            <span class="client-timeline-icon">
                                <i class="fas {{ $item['icon'] }}"></i>
                            </span>
                            <div class="client-timeline-text">
                                <p class="client-timeline-title">{{ $item['label'] }}</p>
                                <p class="client-timeline-date">{{ $item['date']->format('M j, Y') }}</p>
                            </div>
                        </div>
                        <div class="client-timeline-count">{{ $countText }}</div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Payment Requests Banner -->
    @if(isset($paymentRequests) && $paymentRequests->count())
        <style>
            .payment-fixed-alert {
                position: relative;
                width: 100%;
                max-width: none;
                margin: 0 0 1.5rem;
            }

            .payment-notice-panel {
                --paypal-navy: #003087;
                --paypal-blue: #0070ba;
                --paypal-cyan: #009cde;
                --paypal-gold: #ffc439;
                --paypal-gold-deep: #ffb900;
                --paypal-ink: #0d1b3d;
                border: 0;
                border-radius: 1.7rem;
                background:
                    radial-gradient(circle at top right, rgba(255, 255, 255, 0.28), transparent 30%),
                    radial-gradient(circle at bottom left, rgba(255, 255, 255, 0.18), transparent 28%),
                    linear-gradient(135deg, var(--paypal-navy) 0%, #00579f 42%, var(--paypal-cyan) 100%);
                box-shadow: 0 24px 54px rgba(0, 48, 135, 0.22);
                overflow: hidden;
            }

            .payment-notice-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1.1rem;
                padding: 1.15rem 1.3rem 1rem;
                border-bottom: 1px solid rgba(255, 255, 255, 0.16);
            }

            .payment-notice-header-main {
                display: flex;
                align-items: center;
                gap: 0.9rem;
                min-width: 0;
            }

            .payment-notice-brand-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 3.1rem;
                height: 3.1rem;
                flex: 0 0 auto;
                border-radius: 1rem;
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.24), rgba(255, 255, 255, 0.1));
                color: #ffffff;
                font-size: 1.15rem;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.22);
            }

            .payment-notice-copy {
                display: grid;
                gap: 0.15rem;
                min-width: 0;
            }

            .payment-notice-eyebrow {
                margin: 0;
                color: rgba(255, 255, 255, 0.8);
                font-size: 0.72rem;
                font-weight: 800;
                letter-spacing: 0.16em;
                text-transform: uppercase;
            }

            .payment-notice-subtitle {
                margin: 0;
                color: rgba(255, 255, 255, 0.82);
                font-size: 0.83rem;
                line-height: 1.45;
            }

            .payment-notice-title {
                margin: 0;
                color: #ffffff;
                font-family: var(--display-font-family, 'Poppins'), sans-serif;
                font-size: clamp(1.02rem, 1.4vw, 1.28rem);
                font-weight: 800;
                letter-spacing: -0.025em;
            }

            .payment-notice-summary {
                display: grid;
                justify-items: end;
                gap: 0.15rem;
                flex: 0 0 auto;
                padding: 0.72rem 0.9rem;
                border: 1px solid rgba(255, 255, 255, 0.18);
                border-radius: 1.1rem;
                background: rgba(255, 255, 255, 0.12);
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.14);
            }

            .payment-notice-summary-count {
                color: #ffffff;
                font-size: 1.18rem;
                font-weight: 800;
                line-height: 1;
            }

            .payment-notice-summary-label {
                color: rgba(255, 255, 255, 0.82);
                font-size: 0.74rem;
                font-weight: 700;
                letter-spacing: 0.06em;
                text-transform: uppercase;
            }

            .payment-notice-body {
                display: grid;
                gap: 1rem;
                padding: 1rem 1.25rem 1.25rem;
                max-height: min(26rem, calc(100vh - 16rem));
                overflow-y: auto;
                overscroll-behavior-y: auto;
                scrollbar-gutter: stable;
                -webkit-overflow-scrolling: touch;
            }

            .payment-notice-body::-webkit-scrollbar {
                width: 0.45rem;
            }

            .payment-notice-body::-webkit-scrollbar-thumb {
                border-radius: 999px;
                background: rgba(155, 116, 49, 0.28);
            }

            .payment-notice-body::-webkit-scrollbar-track {
                background: transparent;
            }

            .payment-notice-item {
                border: 1px solid rgba(255, 255, 255, 0.34);
                border-radius: 1.3rem;
                background:
                    linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(247, 250, 255, 0.96));
                box-shadow: 0 18px 38px rgba(7, 28, 86, 0.1);
                padding: 1.05rem;
            }

            .payment-notice-item-top {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                align-items: start;
                gap: 1rem;
            }

            .payment-notice-meta {
                display: grid;
                gap: 0.42rem;
            }

            .payment-notice-reference {
                margin: 0;
                color: var(--paypal-blue);
                font-size: 0.7rem;
                font-weight: 800;
                letter-spacing: 0.15em;
                text-transform: uppercase;
            }

            .payment-notice-amount {
                margin: 0;
                color: #111111;
                font-size: clamp(1.4rem, 3vw, 1.85rem);
                font-weight: 800;
                line-height: 1.1;
            }

            .payment-notice-date {
                color: #65718c;
                font-size: 0.79rem;
                font-weight: 600;
            }

            .payment-notice-note {
                margin-top: 0.8rem;
                padding: 0.82rem 0.9rem;
                border-radius: 1rem;
                background: linear-gradient(135deg, rgba(0, 112, 186, 0.08), rgba(0, 156, 222, 0.06));
                color: #22304f;
                font-size: 0.88rem;
                line-height: 1.65;
            }

            .payment-proof-preview {
                display: inline-flex;
                align-items: center;
                gap: 0.45rem;
                margin-top: 0.7rem;
                padding: 0.52rem 0.86rem;
                border: 1px solid rgba(0, 112, 186, 0.16);
                border-radius: 999px;
                background: rgba(0, 112, 186, 0.06);
                color: var(--paypal-blue);
                font-size: 0.76rem;
                font-weight: 700;
                text-decoration: none;
            }

            .payment-proof-preview:hover {
                color: var(--paypal-navy);
            }

            .payment-proof-upload-input {
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

            .payment-proof-dropzone {
                display: grid;
                justify-items: center;
                gap: 0.45rem;
                padding: 1.35rem 1rem;
                border: 1.5px dashed rgba(200, 164, 93, 0.42);
                border-radius: 1.2rem;
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 244, 236, 0.98));
                text-align: center;
                cursor: pointer;
                transition: border-color 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease;
            }

            .payment-proof-dropzone:hover,
            .payment-proof-dropzone.is-active {
                border-color: rgba(155, 116, 49, 0.8);
                box-shadow: 0 16px 32px rgba(15, 15, 15, 0.08);
            }

            .payment-proof-dropzone.has-file {
                border-style: solid;
                background: linear-gradient(180deg, rgba(255, 251, 239, 0.98), rgba(255, 255, 255, 0.98));
            }

            .payment-proof-dropzone-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 3.2rem;
                height: 3.2rem;
                border-radius: 1rem;
                background: #111111;
                color: #f2d18b;
                font-size: 1.15rem;
                box-shadow: 0 14px 30px rgba(15, 15, 15, 0.14);
            }

            .payment-proof-dropzone-title {
                color: #111111;
                font-size: 0.96rem;
                font-weight: 700;
                line-height: 1.45;
                word-break: break-word;
            }

            .payment-proof-dropzone-copy {
                color: #6f6555;
                font-size: 0.84rem;
                line-height: 1.5;
            }

            .payment-proof-dropzone-meta {
                color: #8b7350;
                font-size: 0.76rem;
                font-weight: 700;
                letter-spacing: 0.05em;
                text-transform: uppercase;
            }

            .payment-rejection-panel {
                display: grid;
                gap: 0.45rem;
                margin-top: 0.75rem;
                padding: 0.8rem 0.9rem;
                border: 1px solid rgba(185, 28, 28, 0.12);
                border-radius: 1rem;
                background: linear-gradient(180deg, rgba(255, 248, 248, 0.95), rgba(255, 255, 255, 0.98));
            }

            .payment-rejection-title {
                display: inline-flex;
                align-items: center;
                gap: 0.45rem;
                color: #b91c1c;
                font-size: 0.86rem;
                font-weight: 700;
            }

            .payment-rejection-copy {
                color: #3f3a34;
                font-size: 0.82rem;
                line-height: 1.55;
            }

            .payment-rejection-date {
                color: #857e73;
                font-size: 0.74rem;
            }

            .payment-notice-item-footer {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                align-items: end;
                justify-content: space-between;
                gap: 1rem;
                margin-top: 0.95rem;
                padding-top: 0.95rem;
                border-top: 1px solid rgba(13, 27, 61, 0.08);
            }

            .payment-status-detail {
                color: #52617f;
                font-size: 0.82rem;
                line-height: 1.6;
            }

            .payment-notice-actions {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                flex-wrap: wrap;
                gap: 0.7rem;
            }

            .payment-status-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 2rem;
                padding: 0.42rem 0.86rem;
                border-radius: 999px;
                font-size: 0.74rem;
                font-weight: 800;
                letter-spacing: 0.03em;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2);
            }

            .payment-notice-item .payment-status-badge.bg-warning {
                background: #fff2cf !important;
                color: #9a6700 !important;
            }

            .payment-notice-item .payment-status-badge.bg-info,
            .payment-notice-item .payment-status-badge.text-dark {
                background: #dff3ff !important;
                color: #045b8f !important;
            }

            .payment-notice-item .payment-status-badge.bg-success {
                background: #dff8ea !important;
                color: #117547 !important;
            }

            .payment-action-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.62rem;
                min-height: 3rem;
                padding: 0.78rem 1.2rem;
                border: 0;
                border-radius: 999px;
                font-size: 0.9rem;
                font-weight: 800;
                letter-spacing: 0.01em;
                text-decoration: none;
                white-space: nowrap;
                transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
            }

            .payment-action-btn:hover,
            .payment-action-btn:focus-visible {
                transform: translateY(-1px);
                text-decoration: none;
            }

            .payment-action-btn-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2rem;
                height: 2rem;
                border-radius: 999px;
                flex: 0 0 auto;
                font-size: 0.86rem;
            }

            .payment-pay-btn {
                background: linear-gradient(135deg, var(--paypal-gold) 0%, var(--paypal-gold-deep) 100%);
                color: #1a1f36 !important;
                box-shadow: 0 16px 32px rgba(255, 196, 57, 0.32);
            }

            .payment-pay-btn .payment-action-btn-icon {
                background: rgba(255, 255, 255, 0.45);
                color: var(--paypal-navy);
            }

            .payment-paid-btn {
                background: linear-gradient(135deg, var(--paypal-navy) 0%, #0b4ea2 100%);
                color: #ffffff !important;
                box-shadow: 0 16px 34px rgba(0, 48, 135, 0.28);
            }

            .payment-paid-btn .payment-action-btn-icon {
                background: rgba(255, 255, 255, 0.18);
                color: #ffffff;
            }

            .payment-review-chip {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 2.4rem;
                padding: 0.62rem 0.92rem;
                border-radius: 999px;
                background: #dff3ff;
                color: #045b8f;
                font-size: 0.8rem;
                font-weight: 800;
                letter-spacing: 0.02em;
            }

            .payment-proof-modal-shell {
                position: fixed;
                inset: 0;
                z-index: 1200;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1rem;
                width: 100vw;
                min-height: 100dvh;
            }

            .payment-proof-modal {
                width: min(100%, 34rem);
                max-height: calc(100dvh - 2rem);
                border: 1px solid rgba(15, 15, 15, 0.08);
                border-radius: 1.5rem;
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(250, 247, 239, 0.98));
                box-shadow: 0 28px 64px rgba(15, 15, 15, 0.18);
                overflow: hidden;
            }

            .payment-proof-modal-header,
            .payment-proof-modal-copy,
            .payment-proof-form {
                padding-left: 1.2rem;
                padding-right: 1.2rem;
            }

            .payment-proof-modal-header {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 1rem;
                padding-top: 1.2rem;
            }

            .payment-proof-modal-copy {
                padding-top: 0.35rem;
                padding-bottom: 0.95rem;
            }

            .payment-proof-form {
                padding-bottom: 1.2rem;
                overflow-y: auto;
            }

            .payment-proof-actions {
                display: flex;
                justify-content: flex-end;
                gap: 0.75rem;
                margin-top: 1rem;
            }

            @media (max-width: 900px) {
                .payment-notice-header {
                    flex-direction: column;
                    align-items: stretch;
                }

                .payment-notice-item-footer {
                    grid-template-columns: 1fr;
                }

                .payment-notice-summary {
                    justify-items: start;
                }
            }

            @media (max-width: 640px) {
                .payment-fixed-alert {
                    max-width: 100%;
                    margin-bottom: 1.35rem;
                }

                .payment-notice-header,
                .payment-notice-actions {
                    flex-direction: column;
                    align-items: stretch;
                }

                .payment-notice-header {
                    padding: 1rem;
                }

                .payment-notice-body {
                    padding: 0.95rem;
                    max-height: none;
                    overflow: visible;
                }

                .payment-notice-header-main {
                    align-items: flex-start;
                }

                .payment-notice-brand-icon {
                    width: 2.8rem;
                    height: 2.8rem;
                }

                .payment-notice-summary {
                    width: 100%;
                    justify-items: start;
                }

                .payment-notice-item {
                    padding: 0.92rem;
                }

                .payment-notice-item-top,
                .payment-notice-item-footer {
                    grid-template-columns: 1fr;
                }

                .payment-action-btn,
                .payment-review-chip {
                    width: 100%;
                }

                .payment-notice-actions {
                    width: 100%;
                    justify-content: stretch;
                }

                .payment-proof-actions {
                    flex-direction: column-reverse;
                }

                .payment-proof-actions > * {
                    width: 100%;
                }
            }
        </style>
        <div
            class="payment-fixed-alert"
            x-data="{
                markPaidModalOpen: false,
                markPaidAction: '',
                markPaidRequestId: '',
                markPaidReference: '',
                markPaidAmount: '',
                paymentProofName: '',
                paymentProofDragActive: false,
                init() {
                    this.$watch('markPaidModalOpen', value => {
                        document.body.classList.toggle('modal-open', value);
                    });

                    if (this.$refs.markPaidAutoOpen) {
                        this.$nextTick(() => this.$refs.markPaidAutoOpen.click());
                    }
                },
                openMarkPaidModal(trigger) {
                    this.markPaidModalOpen = true;
                    this.markPaidRequestId = trigger.dataset.requestId;
                    this.markPaidAction = trigger.dataset.action;
                    this.markPaidReference = trigger.dataset.reference;
                    this.markPaidAmount = trigger.dataset.amount;
                    this.paymentProofName = '';
                    this.paymentProofDragActive = false;

                    if (this.$refs.paymentProofInput) {
                        this.$refs.paymentProofInput.value = '';
                    }
                },
                closeMarkPaidModal() {
                    this.markPaidModalOpen = false;
                    this.paymentProofDragActive = false;
                    this.paymentProofName = '';

                    if (this.$refs.paymentProofInput) {
                        this.$refs.paymentProofInput.value = '';
                    }
                },
                handlePaymentProofChange(event) {
                    this.paymentProofName = event.target.files && event.target.files[0]
                        ? event.target.files[0].name
                        : '';
                },
                handlePaymentProofDrop(event) {
                    this.paymentProofDragActive = false;

                    const files = event.dataTransfer?.files;

                    if (!files || !files.length) {
                        return;
                    }

                    const transfer = new DataTransfer();

                    Array.from(files).forEach(file => transfer.items.add(file));

                    this.$refs.paymentProofInput.files = transfer.files;
                    this.paymentProofName = transfer.files[0] ? transfer.files[0].name : '';
                },
                handlePaymentNoticeWheel(event) {
                    const scrollBody = this.$refs.paymentNoticeBody;

                    if (!scrollBody) {
                        return;
                    }

                    const maxScrollTop = scrollBody.scrollHeight - scrollBody.clientHeight;

                    if (maxScrollTop <= 0) {
                        return;
                    }

                    const wheelInsideBody = event.target.closest('.payment-notice-body');

                    if (!wheelInsideBody) {
                        scrollBody.scrollTop += event.deltaY;
                        event.preventDefault();
                        return;
                    }

                    const nextScrollTop = scrollBody.scrollTop + event.deltaY;

                    if (nextScrollTop <= 0 || nextScrollTop >= maxScrollTop) {
                        return;
                    }
                }
            }"
            @keydown.escape.window="closeMarkPaidModal()"
        >
            <section class="payment-notice-panel" aria-label="Payment Notice" @wheel="handlePaymentNoticeWheel($event)">
                <div class="payment-notice-header">
                    <div class="payment-notice-header-main">
                        <div class="payment-notice-brand-icon" aria-hidden="true">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="payment-notice-copy">
                            <p class="payment-notice-eyebrow">Secure Payments</p>
                            <h2 class="payment-notice-title">Payments</h2>
                            <p class="payment-notice-subtitle">Review payment requests, pay securely, mark as paid, and upload proof from any device.</p>
                        </div>
                    </div>
                    <div class="payment-notice-summary" aria-label="Payment request summary">
                        <span class="payment-notice-summary-count">{{ $paymentRequests->count() }}</span>
                        <span class="payment-notice-summary-label">
                            {{ \Illuminate\Support\Str::plural('Open Request', $paymentRequests->count()) }}
                        </span>
                    </div>
                </div>

                <div class="payment-notice-body" x-ref="paymentNoticeBody">
                @foreach($paymentRequests as $due)
                    <article class="payment-notice-item">
                        <div class="payment-notice-item-top">
                            <div class="payment-notice-meta">
                                <p class="payment-notice-reference">{{ $due->display_reference }}</p>
                                <h3 class="payment-notice-amount">${{ number_format($due->amount, 2) }}</h3>
                                <div class="payment-notice-date">Requested {{ $due->created_at?->diffForHumans() ?? 'recently' }}</div>
                            </div>
                            <span class="badge payment-status-badge {{ $due->getStatusBadgeClass() }}">
                                {{ $due->status === PaymentRequest::STATUS_CLIENT_MARKED ? 'You Marked Paid' : $due->getDisplayStatusLabel() }}
                            </span>
                        </div>
                        @if($due->note)
                            <div class="payment-notice-note">{{ $due->note }}</div>
                        @endif
                        @if($due->hasPaymentProof())
                            <a href="{{ $due->payment_proof_url }}" target="_blank" rel="noopener" class="payment-proof-preview">
                                <i class="fas fa-image"></i>
                                <span>View submitted proof</span>
                            </a>
                        @endif
                        @if($due->isRejected())
                            <div class="payment-rejection-panel">
                                <div class="payment-rejection-title">
                                    <i class="fas fa-circle-exclamation"></i>
                                    <span>Payment rejected</span>
                                </div>
                                <div class="payment-rejection-copy">{{ $due->rejection_reason }}</div>
                                @if($due->rejected_at)
                                    <div class="payment-rejection-date">Rejected {{ $due->rejected_at->diffForHumans() }}</div>
                                @endif
                            </div>
                        @endif

                        <div class="payment-notice-item-footer">
                            <div class="payment-status-detail">
                                @if($due->status === PaymentRequest::STATUS_CLIENT_MARKED)
                                    Waiting for admin confirmation.
                                    @if($due->payment_proof_uploaded_at)
                                        <span class="d-block">Proof uploaded {{ $due->payment_proof_uploaded_at->diffForHumans() }}.</span>
                                    @endif
                                @elseif($due->isRejected())
                                    Upload a fresh screenshot after correcting the issue above.
                                @else
                                    After completing the payment, mark as paid and upload a clear payment screenshot when the transfer is complete.
                                @endif
                            </div>

                            <div class="payment-notice-actions">
                                @if($due->payment_link)
                                    <a
                                        href="{{ $due->payment_link }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="payment-action-btn payment-pay-btn"
                                    >
                                        <span class="payment-action-btn-icon">
                                            <i class="fas fa-credit-card"></i>
                                        </span>
                                        <span>Pay Now</span>
                                    </a>
                                @endif

                                @if($due->isRejected())
                                    <button
                                        type="button"
                                        class="payment-action-btn payment-paid-btn"
                                        data-payment-request-trigger="{{ $due->id }}"
                                        data-request-id="{{ $due->id }}"
                                        data-action="{{ route('client.payment-requests.mark-paid', $due) }}"
                                        data-reference="{{ $due->display_reference }}"
                                        data-amount="${{ number_format($due->amount, 2) }}"
                                        @if((string) old('payment_request_id') === (string) $due->id) x-ref="markPaidAutoOpen" @endif
                                        @click="openMarkPaidModal($el)"
                                    >
                                        <span class="payment-action-btn-icon">
                                            <i class="fas fa-rotate-right"></i>
                                        </span>
                                        <span>Mark as Paid Again</span>
                                    </button>
                                @elseif($due->status === PaymentRequest::STATUS_PENDING)
                                    <button
                                        type="button"
                                        class="payment-action-btn payment-paid-btn"
                                        data-payment-request-trigger="{{ $due->id }}"
                                        data-request-id="{{ $due->id }}"
                                        data-action="{{ route('client.payment-requests.mark-paid', $due) }}"
                                        data-reference="{{ $due->display_reference }}"
                                        data-amount="${{ number_format($due->amount, 2) }}"
                                        @if((string) old('payment_request_id') === (string) $due->id) x-ref="markPaidAutoOpen" @endif
                                        @click="openMarkPaidModal($el)"
                                    >
                                        <span class="payment-action-btn-icon">
                                            <i class="fas fa-check"></i>
                                        </span>
                                        <span>Mark as Paid</span>
                                    </button>
                                @elseif($due->status === PaymentRequest::STATUS_CLIENT_MARKED)
                                    <span class="payment-review-chip">Submitted for Review</span>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
                </div>
            </section>

            <template x-teleport="body">
                <div
                    x-cloak
                    x-show="markPaidModalOpen"
                    x-transition.opacity
                    class="work-update-filter-backdrop"
                    @click="closeMarkPaidModal()"
                ></div>
            </template>

            <template x-teleport="body">
                <div
                    x-cloak
                    x-show="markPaidModalOpen"
                    x-transition.opacity
                    class="payment-proof-modal-shell"
                >
                    <div class="payment-proof-modal" @click.outside="closeMarkPaidModal()">
                        <div class="payment-proof-modal-header">
                            <div>
                                <p class="work-updates-eyebrow">Payment Proof</p>
                                <h3 class="theme-display mb-0 text-2xl text-stone-950">Upload payment screenshot</h3>
                            </div>
                            <button type="button" class="work-update-filter-close" @click="closeMarkPaidModal()" aria-label="Close payment proof form">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="payment-proof-modal-copy">
                            <p class="mb-1 text-sm text-stone-700">
                                Submit the payment proof for <strong x-text="markPaidReference"></strong>.
                            </p>
                            <p class="mb-0 text-sm text-stone-500">
                                Upload a clear screenshot for <span x-text="markPaidAmount"></span>. The Team will use this image to verify your payment.
                            </p>
                        </div>

                        <form method="POST" :action="markPaidAction" enctype="multipart/form-data" class="payment-proof-form">
                            @csrf
                            <input type="hidden" name="payment_request_id" :value="markPaidRequestId">

                            <label for="payment_proof" class="form-label fw-semibold">Payment screenshot</label>
                            <input
                                x-ref="paymentProofInput"
                                id="payment_proof"
                                type="file"
                                name="payment_proof"
                                class="payment-proof-upload-input @error('payment_proof') is-invalid @enderror"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                required
                                @change="handlePaymentProofChange($event)"
                            >
                            <label
                                for="payment_proof"
                                class="payment-proof-dropzone"
                                :class="{ 'is-active': paymentProofDragActive, 'has-file': paymentProofName }"
                                @dragover.prevent="paymentProofDragActive = true"
                                @dragleave.prevent="paymentProofDragActive = false"
                                @drop.prevent="handlePaymentProofDrop($event)"
                            >
                                <span class="payment-proof-dropzone-icon">
                                    <i class="fas fa-cloud-arrow-up"></i>
                                </span>
                                <span class="payment-proof-dropzone-title" x-text="paymentProofName || 'Drag & drop your payment screenshot'"></span>
                                <span class="payment-proof-dropzone-copy">or click here to choose a file</span>
                                <span class="payment-proof-dropzone-meta">JPG, PNG, WEBP up to 5MB</span>
                            </label>
                            @error('payment_proof')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <div class="payment-proof-actions">
                                <button type="button" class="btn btn-white" @click="closeMarkPaidModal()">Cancel</button>
                                <button type="submit" class="btn btn-black">
                                    <i class="fas fa-upload me-2"></i>Submit Proof
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </template>
        </div>
    @endif

    @include('partials.dashboard-notices')

    <div class="client-dashboard-stats-shell mb-4">
        <div class="row client-dashboard-stats-row">
            <div class="col-lg-3 col-md-6 col-sm-6 col-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-black me-2 me-md-3">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stats-number">{{ $stats['total_updates'] }}</div>
                        <div class="stats-label">Total Updates</div>
                    </div>
                </div>
            </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6 col-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-black me-2 me-md-3">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stats-number">{{ $stats['this_month'] }}</div>
                        <div class="stats-label">This Month</div>
                    </div>
                </div>
            </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6 col-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-black me-2 me-md-3">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stats-number">{{ $stats['last_update'] ? $stats['last_update']->created_at->diffForHumans() : 'Never' }}</div>
                        <div class="stats-label">Last Update</div>
                    </div>
                </div>
            </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6 col-6 mb-3">
            <div class="stats-card {{
                $serviceCompleted
                    ? 'bg-info'
                    : (
                        $serviceDaysRemaining !== null && $serviceDaysRemaining < 0
                            ? 'bg-danger'
                            : ($serviceDaysRemaining !== null && $serviceDaysRemaining <= 3 ? 'bg-warning' : 'bg-success')
                    )
            }}">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-black me-2 me-md-3">
                        <i class="fas fa-{{ $serviceCompleted ? 'flag-checkered' : ($serviceDaysRemaining !== null && $serviceDaysRemaining < 0 ? 'times-circle' : 'check-circle') }}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stats-number">
                            @if($serviceCompleted)
                                Completed
                            @elseif($assignment && $assignment->service_end_date)
                                @if($serviceDaysRemaining < 0)
                                    Expired
                                @elseif($serviceDaysRemaining === 0)
                                    Expires Today
                                @else
                                    {{ $serviceDaysRemaining }} Days
                                @endif
                            @else
                                Active
                            @endif
                        </div>
                        @if($serviceDaysRemaining !== null && $serviceDaysRemaining >= 0)
                            <div class="stats-label">Service ends in</div>
                        @endif
                    </div>
                </div>
            </div>
            </div>
        </div>

        <div class="client-dashboard-stats-actions">
            <a href="{{ route('client.work-updates.index') }}" class="btn btn-black w-100 client-dashboard-work-updates-btn">
                <i class="fas fa-clipboard-list me-2"></i>Work Updates
            </a>
        </div>
    </div>

    @if(($recentWorkUpdates ?? collect())->isNotEmpty())
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="card-title mb-1">
                                <i class="fas fa-briefcase me-2"></i>Recent Work Updates
                            </h5>
                            <p class="text-muted mb-0">Update the result when an application moves to interview, hired, or rejected.</p>
                        </div>
                        <a href="{{ route('client.work-updates.index') }}" class="btn btn-white btn-sm">
                            <i class="fas fa-list me-2"></i>View All
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($recentWorkUpdates as $update)
                                @php
                                    $statusBadgeClass = match($update->application_status) {
                                        \App\Models\WorkUpdate::APPLICATION_STATUS_HIRED => 'bg-success',
                                        \App\Models\WorkUpdate::APPLICATION_STATUS_INTERVIEW => 'bg-info text-dark',
                                        \App\Models\WorkUpdate::APPLICATION_STATUS_REJECTED => 'bg-danger',
                                        \App\Models\WorkUpdate::APPLICATION_STATUS_APPLIED => 'bg-dark',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <div class="col-xl-6">
                                    <div class="border rounded-3 p-3 h-100">
                                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                            <div class="min-w-0">
                                                <h6 class="mb-1">{{ $update->job_title }}</h6>
                                                <p class="text-muted mb-0">{{ $update->company }}</p>
                                            </div>
                                            <span class="badge {{ $statusBadgeClass }}">{{ $update->getApplicationStatusLabel() }}</span>
                                        </div>

                                        <div class="row g-2 small text-muted mb-3">
                                            <div class="col-sm-6">
                                                <strong class="d-block text-dark">Applied Via</strong>
                                                {{ $update->getAppliedMethodLabel() }}
                                            </div>
                                            <div class="col-sm-6">
                                                <strong class="d-block text-dark">Applied Date</strong>
                                                {{ ($update->applied_date ?? $update->created_at)?->format('M j, Y') }}
                                            </div>
                                        </div>

                                        @if($update->note)
                                            <p class="small text-muted mb-3">{{ \Illuminate\Support\Str::limit($update->note, 120) }}</p>
                                        @endif

                                        <div class="d-flex flex-column flex-sm-row gap-2">
                                            <a href="{{ route('client.work-updates.edit', $update->id) }}" class="btn btn-black btn-sm">
                                                <i class="fas fa-pen-to-square me-2"></i>Update Status
                                            </a>
                                            @if($update->job_link)
                                                <a href="{{ $update->job_link }}" target="_blank" class="btn btn-white btn-sm">
                                                    <i class="fas fa-arrow-up-right-from-square me-2"></i>Job Link
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Service Information -->
    @if($assignment || $profile)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Service Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Service Start Date</label>
                                    <p class="mb-0">{{ $profile?->service_start_date?->format('M j, Y') ?? $assignment?->assigned_date?->format('M j, Y') ?? 'Not Set' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Service End Date</label>
                                    <p class="mb-0">
                                        @if($assignment?->isServiceCompleted())
                                            {{ $assignment->service_end_date?->format('M j, Y') ?? 'N/A' }}
                                            <span class="badge bg-info text-dark ms-2">
                                                Service Completed
                                            </span>
                                            @if($assignment?->service_completed_at)
                                                <span class="d-block text-muted mt-1">Completed {{ $assignment->service_completed_at->diffForHumans() }}</span>
                                            @endif
                                        @elseif($assignment?->service_end_date)
                                            {{ $assignment->service_end_date->format('M j, Y') }}
                                            @php $daysRemaining = $assignment->getDaysRemaining(); @endphp
                                            @if($daysRemaining !== null)
                                                <span class="badge {{ $daysRemaining < 0 ? 'bg-danger' : ($daysRemaining <= 3 ? 'bg-warning' : 'bg-success') }} ms-2">
                                                    @if($daysRemaining < 0)
                                                        Expired {{ abs($daysRemaining) }} days ago
                                                    @elseif($daysRemaining === 0)
                                                        Expires Today
                                                    @else
                                                        {{ $daysRemaining }} days remaining
                                                    @endif
                                                </span>
                                            @endif
                                        @elseif($assignment)
                                            <span class="text-success">No Expiration Date</span>
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Service Type</label>
                                    <p class="mb-0">
                                        <span class="badge {{ ($profile?->service_type ?? \App\Models\ClientProfile::SERVICE_TYPE_REGULAR) === \App\Models\ClientProfile::SERVICE_TYPE_VIP ? 'bg-dark text-white' : 'bg-info text-dark' }}">
                                            {{ $profile?->serviceTypeLabel() ?? 'Regular' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Service Package</label>
                                    <p class="mb-0">
                                        @if($profile?->service_package)
                                            <span class="badge bg-primary">{{ ucwords(str_replace('-', ' ', $profile->service_package)) }}</span>
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <p class="mb-0">
                                        @if($assignment?->isServiceCompleted())
                                            <span class="badge bg-info text-dark">Service Completed</span>
                                        @elseif(!$assignment)
                                            <span class="badge bg-secondary">Awaiting assignment</span>
                                        @else
                                            @php $daysRemaining = $assignment->getDaysRemaining(); @endphp
                                            @if($daysRemaining !== null && $daysRemaining < 0)
                                            <span class="badge bg-danger">Expired</span>
                                            @elseif($daysRemaining !== null && $daysRemaining <= 3)
                                            <span class="badge bg-warning">Ending Soon</span>
                                            @else
                                            <span class="badge bg-success">Active</span>
                                            @endif
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        @if($assignment?->notes)
                            <div class="mt-3">
                                <label class="form-label fw-bold">Notes</label>
                                <p class="mb-0">{{ $assignment->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Assignment Files Section -->
    @if($assignment)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-alt me-2"></i>My Resume And Cover Letters
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($assignment->resume_file)
                                <div class="col-lg-6 col-md-12 mb-3">
                                    <div class="card border">
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                                            <h6 class="card-title">Resume</h6>
                                            <button type="button"
                                                    class="btn btn-black js-download-file"
                                                    data-url="{{ storage_public_url($assignment->resume_file) }}"
                                                    data-filename="{{ basename($assignment->resume_file) }}">
                                                <i class="fas fa-download me-2"></i>Download Resume
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @php
                                $coverLetters = $assignment->cover_letters;
                                if ($coverLetters && !is_array($coverLetters)) {
                                    $decoded = json_decode($coverLetters, true);
                                    $coverLetters = is_array($decoded) ? $decoded : [];
                                }
                            @endphp

                            @if($coverLetters && count($coverLetters) > 0)
                                @foreach($coverLetters as $index => $coverLetter)
                                    <div class="col-lg-6 col-md-12 mb-3">
                                        <div class="card border">
                                            <div class="card-body text-center">
                                                <i class="fas fa-file-text fa-3x text-primary mb-3"></i>
                                                <h6 class="card-title">Cover Letter {{ $index + 1 }}</h6>
                                                <button type="button"
                                                        class="btn btn-black js-download-file"
                                                        data-url="{{ storage_public_url($coverLetter) }}"
                                                        data-filename="{{ basename($coverLetter) }}">
                                                    <i class="fas fa-download me-2"></i>Download Cover Letter
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            @if(!$assignment->resume_file && (!$assignment->cover_letters || count($assignment->cover_letters) == 0))
                                <div class="col-12">
                                    <div class="text-center py-4">
                                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No Files Available</h5>
                                        <p class="text-muted">No files have been uploaded yet.</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="{{ route('client.submissions.create') }}" class="btn btn-create btn-lg w-100">
                                <i class="fas fa-plus me-2"></i>Submit verification code
                            </a>
                            <small class="text-muted d-block mt-2">Submit verification code for application processing</small>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('client.submissions.index') }}" class="btn btn-border-black btn-lg w-100">
                                <i class="fas fa-list me-2"></i>View My Submissions
                            </a>
                            <small class="text-muted d-block mt-2">View and track your submission history</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media (max-width: 768px) {
            .card-body {
                padding: 1rem;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn-group .btn {
                margin-bottom: 0.25rem;
            }

        .stats-card {
            margin-bottom: 1rem;
        }

        .client-dashboard-stats-shell {
            display: flex;
            flex-direction: column;
        }

        .client-dashboard-stats-row {
            margin-bottom: 0;
        }

        .client-dashboard-stats-actions {
            display: flex;
            justify-content: stretch;
            margin-top: -0.1rem;
        }

        .client-dashboard-work-updates-btn {
            width: 100%;
        }

        .client-dashboard-work-updates-btn:hover,
        .client-dashboard-work-updates-btn:active {
            transform: none !important;
        }

        .stats-card .d-flex {
            flex-direction: column;
            text-align: center;
        }

            .stats-icon {
                margin: 0 auto 0.5rem auto !important;
                width: 50px;
                height: 50px;
            }

            .stats-number {
                font-size: 1.5rem;
            }

            .stats-label {
                font-size: 0.8rem;
            }

            .table-responsive {
                font-size: 0.8rem;
            }

            .btn {
                font-size: 0.8rem;
                padding: 0.5rem 0.75rem;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .page-subtitle {
                font-size: 1rem;
            }

            .alert {
                font-size: 0.9rem;
                padding: 0.75rem;
            }
        }

        @media (max-width: 576px) {
            .client-dashboard-stats-actions {
                margin-top: 0;
            }

            .stats-card {
                padding: 1rem;
            }

            .stats-icon {
                width: 40px;
                height: 40px;
                font-size: 0.9rem;
            }

            .stats-number {
                font-size: 1.25rem;
            }

            .stats-label {
                font-size: 0.75rem;
            }

            .table-responsive {
                font-size: 0.75rem;
            }

            .btn {
                font-size: 0.75rem;
                padding: 0.4rem 0.6rem;
            }

            .page-title {
                font-size: 1.25rem;
            }

            .page-subtitle {
                font-size: 0.9rem;
            }

            .alert {
                font-size: 0.8rem;
                padding: 0.6rem;
            }

            .card-body {
                padding: 0.75rem;
            }
        }

        /* Service Status Card Styles */
        .stats-card.bg-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            color: white;
        }

        .stats-card.bg-success {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            color: white;
        }

        .stats-card.bg-danger {
            background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
            color: white;
        }

        .stats-card.bg-info {
            background: linear-gradient(135deg, #0ea5e9 0%, #38bdf8 100%);
            color: white;
        }

        .stats-card.bg-warning .stats-number,
        .stats-card.bg-warning .stats-label,
        .stats-card.bg-success .stats-number,
        .stats-card.bg-success .stats-label,
        .stats-card.bg-danger .stats-number,
        .stats-card.bg-danger .stats-label,
        .stats-card.bg-info .stats-number,
        .stats-card.bg-info .stats-label {
            color: #ffffff !important;
        }

        .stats-icon.bg-warning {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .stats-icon.bg-success {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .stats-icon.bg-danger {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .stats-icon.bg-info {
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* Alert Styles */
        .alert {
            border-radius: 8px;
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .alert-danger {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            color: #dc2626;
        }

        .alert-warning {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            color: #d97706;
        }

        :root {
            --accent: #2563eb;
            --accent-soft: #e8f0ff;
        }

        .update-day-card {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
        }

        .update-count-badge {
            background: var(--accent);
            color: #fff;
            font-weight: 700;
            padding: 0.45rem 0.85rem;
        }

        .update-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1rem;
            background: #fff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        }

        .update-title {
            font-weight: 700;
            color: #0f172a;
        }

        .update-company {
            font-size: 0.9rem;
        }

        .update-meta {
            font-size: 0.88rem;
            color: #475569;
            display: grid;
            gap: 0.2rem;
            margin-top: 0.5rem;
        }

        .status-pill {
            border-radius: 999px;
            padding: 0.35rem 0.75rem;
            font-weight: 700;
            font-size: 0.8rem;
            white-space: nowrap;
        }

        .status-applied { background: #e5edff; color: #1d4ed8; }
        .status-interview { background: #fef3c7; color: #b45309; }
        .status-hired { background: #ecfdf3; color: #15803d; }
        .status-rejected { background: #fef2f2; color: #b91c1c; }
        .status-default { background: #e2e8f0; color: #1f2937; }

        .btn-accent {
            background: var(--accent-soft);
            border: 1px solid var(--accent);
            color: #0b1324;
            font-weight: 600;
        }

        .btn-accent:hover {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }

        .update-note {
            background: #f8fafc;
            border: 1px dashed #e2e8f0;
            border-radius: 10px;
            padding: 0.75rem;
        }
    </style>
</x-app-layout>
