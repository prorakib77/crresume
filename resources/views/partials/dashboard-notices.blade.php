@if(isset($dashboardNotices) && $dashboardNotices->count())
    <section class="dashboard-notice-stack" id="dashboard-notice-stack" data-dashboard-notice-user="{{ auth()->id() }}">
        @foreach($dashboardNotices as $notice)
            <article
                class="dashboard-notice-card {{ $notice->source_type === \App\Models\Notice::SOURCE_ONBOARDING_REQUEST ? 'dashboard-notice-card-onboarding' : '' }}"
                data-dashboard-notice="{{ $notice->id }}"
                data-dashboard-notice-source="{{ $notice->source_type }}"
                style="--dashboard-notice-bg: {{ $notice->background_color }}; --dashboard-notice-text: {{ $notice->resolved_text_color }};"
            >
                <div class="dashboard-notice-card-top">
                    <div class="dashboard-notice-card-main">
                        <span class="dashboard-notice-card-icon">
                            <i class="{{ $notice->resolved_icon_class }}"></i>
                        </span>
                        <div>
                            <h3 class="dashboard-notice-card-title">{{ $notice->title }}</h3>
                            <p class="dashboard-notice-card-copy">{{ $notice->content }}</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="dashboard-notice-close"
                        aria-label="Dismiss notice"
                        onclick="dismissDashboardNotice({{ $notice->id }})"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="dashboard-notice-card-footer">
                    <span class="dashboard-notice-card-time">{{ $notice->created_at?->diffForHumans() }}</span>
                    @if($notice->action_url)
                        <a href="{{ $notice->action_url }}" class="btn btn-sm btn-black">
                            <i class="fas fa-arrow-right me-2"></i>{{ $notice->resolved_action_label }}
                        </a>
                    @endif
                </div>
            </article>
        @endforeach
    </section>

    @push('styles')
    <style>
        .dashboard-notice-stack {
            display: grid;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .dashboard-notice-card {
            position: relative;
            border: 1px solid rgba(15, 15, 15, 0.08);
            border-radius: 1.45rem;
            background: var(--dashboard-notice-bg, #fff7e0);
            color: var(--dashboard-notice-text, #111111);
            box-shadow: 0 16px 34px rgba(15, 15, 15, 0.06);
            padding: 0.92rem 1rem;
        }

        .dashboard-notice-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
        }

        .dashboard-notice-card-main {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 0.9rem;
            align-items: flex-start;
            min-width: 0;
        }

        .dashboard-notice-card-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.7rem;
            height: 2.7rem;
            border-radius: 1rem;
            background: rgba(17, 17, 17, 0.12);
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        .dashboard-notice-card-title {
            margin: 0;
            font-family: var(--display-font-family, 'Poppins'), sans-serif;
            font-size: 1.02rem;
            font-weight: 700;
            line-height: 1.4;
        }

        .dashboard-notice-card-copy {
            margin: 0.4rem 0 0;
            color: inherit;
            opacity: 0.88;
            font-size: 0.86rem;
            line-height: 1.55;
        }

        .dashboard-notice-close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.1rem;
            height: 2.1rem;
            border: 1px solid rgba(17, 17, 17, 0.12);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.42);
            color: inherit;
            flex-shrink: 0;
        }

        .dashboard-notice-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 0.8rem;
            padding-top: 0.75rem;
            border-top: 1px solid rgba(17, 17, 17, 0.08);
        }

        .dashboard-notice-card-time {
            font-size: 0.78rem;
            font-weight: 600;
            opacity: 0.78;
        }

        .dashboard-notice-card-onboarding {
            border-color: rgba(26, 168, 214, 0.28);
            background: linear-gradient(140deg, #f2fcff 0%, #e4f8ff 100%);
            color: #0f3b4c;
            box-shadow: 0 12px 26px rgba(26, 168, 214, 0.12);
        }

        .dashboard-notice-card-onboarding .dashboard-notice-card-main {
            gap: 0.75rem;
        }

        .dashboard-notice-card-onboarding .dashboard-notice-card-icon {
            width: 2.35rem;
            height: 2.35rem;
            border-radius: 0.82rem;
            background: rgba(26, 168, 214, 0.16);
            color: #0b5f7c;
            font-size: 0.84rem;
        }

        .dashboard-notice-card-onboarding .dashboard-notice-card-title {
            font-size: 0.94rem;
        }

        .dashboard-notice-card-onboarding .dashboard-notice-card-copy {
            font-size: 0.8rem;
            line-height: 1.45;
            margin-top: 0.25rem;
        }

        .dashboard-notice-card-onboarding .dashboard-notice-close {
            border-color: rgba(11, 95, 124, 0.2);
            background: rgba(255, 255, 255, 0.62);
        }

        .dashboard-notice-card-onboarding .dashboard-notice-card-footer {
            margin-top: 0.65rem;
            padding-top: 0.6rem;
        }

        @media (max-width: 640px) {
            .dashboard-notice-card {
                padding-right: 4.15rem;
            }

            .dashboard-notice-card-top,
            .dashboard-notice-card-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .dashboard-notice-close {
                position: absolute;
                top: 0.9rem;
                right: 0.95rem;
                align-self: auto;
                z-index: 1;
            }

            .dashboard-notice-card-footer .btn {
                align-self: flex-end;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        function getDashboardNoticeStorageKey(noticeId) {
            const userId = document.getElementById('dashboard-notice-stack')?.dataset.dashboardNoticeUser || 'guest';

            return `dashboard_notice_hidden_until:${userId}:${noticeId}`;
        }

        function pruneDashboardNoticeState(noticeId) {
            try {
                const key = getDashboardNoticeStorageKey(noticeId);
                const hiddenUntil = Number(window.localStorage.getItem(key) || 0);

                if (!hiddenUntil) {
                    return false;
                }

                if (hiddenUntil > Date.now()) {
                    return true;
                }

                window.localStorage.removeItem(key);
            } catch (error) {
                console.error('Dashboard notice storage error:', error);
            }

            return false;
        }

        function cleanupDashboardNoticeStack() {
            const stack = document.getElementById('dashboard-notice-stack');

            if (stack && !stack.querySelector('[data-dashboard-notice]')) {
                stack.remove();
            }
        }

        function hydrateDashboardNotices() {
            document.querySelectorAll('[data-dashboard-notice]').forEach(card => {
                const noticeId = card.dataset.dashboardNotice;

                if (pruneDashboardNoticeState(noticeId)) {
                    card.remove();
                }
            });

            cleanupDashboardNoticeStack();
        }

        function dismissDashboardNotice(noticeId) {
            try {
                window.localStorage.setItem(
                    getDashboardNoticeStorageKey(noticeId),
                    String(Date.now() + (12 * 60 * 60 * 1000))
                );
            } catch (error) {
                console.error('Dashboard notice storage error:', error);
            }

            const card = document.querySelector(`[data-dashboard-notice="${noticeId}"]`);

            if (card) {
                card.remove();
            }

            cleanupDashboardNoticeStack();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', hydrateDashboardNotices, { once: true });
        } else {
            hydrateDashboardNotices();
        }
    </script>
    @endpush
@endif
