<div class="notice-center-shell">
    <section class="notice-center-hero">
        <div>
            <p class="notice-center-eyebrow">Important Updates</p>
            <h2 class="notice-center-title">{{ $pageHeading ?? 'Notices' }}</h2>
            <p class="notice-center-copy">{{ $pageCopy ?? 'Important account and workflow notices appear here.' }}</p>
        </div>
        <div class="notice-center-count">
            <span class="notice-center-count-value">{{ $noticeCount }}</span>
            <span class="notice-center-count-label">Active</span>
        </div>
    </section>

    <section class="card">
        <div class="card-body p-0">
            @forelse($notices as $notice)
                <article
                    class="notice-center-item"
                    data-notice-id="{{ $notice->id }}"
                    style="--notice-bg: {{ $notice->background_color }}; --notice-text: {{ $notice->resolved_text_color }};"
                >
                    <div class="notice-center-item-panel">
                        <div class="notice-center-item-top">
                            <div class="notice-center-item-main">
                                <span class="notice-center-item-icon">
                                    <i class="{{ $notice->resolved_icon_class }}"></i>
                                </span>
                                <div>
                                    <h3 class="notice-center-item-title">{{ $notice->title }}</h3>
                                    <p class="notice-center-item-message">{{ $notice->content }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="notice-center-item-footer">
                            <div class="notice-center-item-meta">
                                <span>{{ $notice->created_at?->diffForHumans() }}</span>
                                @if($notice->recipient)
                                    <span>For {{ $notice->recipient->name }}</span>
                                @elseif($notice->audience === \App\Models\Notice::AUDIENCE_BOTH)
                                    <span>Agents & Clients</span>
                                @elseif($notice->audience === \App\Models\Notice::AUDIENCE_AGENT)
                                    <span>Agents</span>
                                @else
                                    <span>Clients</span>
                                @endif
                            </div>

                            @if($notice->resolved_action_url)
                                <a href="{{ $notice->resolved_action_url }}" class="btn btn-sm btn-black">
                                    <i class="fas fa-arrow-right me-2"></i>{{ $notice->resolved_action_label }}
                                </a>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="notice-center-empty">
                    <div class="notice-center-empty-icon">
                        <i class="fas fa-bell-slash"></i>
                    </div>
                    <h3>No notices right now</h3>
                    <p>{{ $emptyCopy ?? 'Important notices will appear here when they are available.' }}</p>
                </div>
            @endforelse
        </div>
    </section>

    @if($notices->hasPages())
        <div class="work-updates-pagination">
            {{ $notices->links() }}
        </div>
    @endif
</div>

@push('styles')
<style>
    .notice-center-shell {
        display: grid;
        gap: 1.5rem;
    }

    .notice-center-hero {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.3rem 1.35rem;
        border: 1px solid rgba(15, 15, 15, 0.08);
        border-radius: 1.7rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(250, 247, 239, 0.98));
        box-shadow: 0 18px 44px rgba(15, 15, 15, 0.06);
    }

    .notice-center-eyebrow {
        margin: 0;
        color: #9b7431;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.22em;
        text-transform: uppercase;
    }

    .notice-center-title {
        margin: 0.35rem 0 0;
        color: #111111;
        font-family: var(--display-font-family, 'Poppins'), sans-serif;
        font-size: clamp(1.35rem, 2vw, 1.95rem);
        font-weight: 700;
        letter-spacing: -0.03em;
    }

    .notice-center-copy {
        margin: 0.65rem 0 0;
        color: #756d61;
        font-size: 0.9rem;
        line-height: 1.7;
        max-width: 40rem;
    }

    .notice-center-count {
        display: grid;
        gap: 0.2rem;
        min-width: 7rem;
        padding: 0.9rem 1rem;
        border: 1px solid rgba(15, 15, 15, 0.08);
        border-radius: 1.15rem;
        background: linear-gradient(180deg, #191919 0%, #0d0d0d 100%);
        text-align: center;
    }

    .notice-center-count-value {
        color: #f4d58f;
        font-size: 1.3rem;
        font-weight: 700;
        line-height: 1;
    }

    .notice-center-count-label {
        color: #d2c4a5;
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .notice-center-item {
        padding: 1rem 1.1rem;
        border-bottom: 1px solid rgba(15, 15, 15, 0.06);
    }

    .notice-center-item:last-child {
        border-bottom: 0;
    }

    .notice-center-item-panel {
        border: 1px solid rgba(15, 15, 15, 0.06);
        border-radius: 1.35rem;
        background: var(--notice-bg, #fff7e0);
        color: var(--notice-text, #111111);
        box-shadow: 0 12px 28px rgba(15, 15, 15, 0.04);
        padding: 1rem;
    }

    .notice-center-item-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }

    .notice-center-item-main {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 0.9rem;
        min-width: 0;
    }

    .notice-center-item-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 3rem;
        height: 3rem;
        border-radius: 1rem;
        background: rgba(17, 17, 17, 0.12);
        font-size: 1rem;
        flex-shrink: 0;
    }

    .notice-center-item-title {
        margin: 0;
        font-family: var(--display-font-family, 'Poppins'), sans-serif;
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.4;
    }

    .notice-center-item-message {
        margin: 0.45rem 0 0;
        color: inherit;
        opacity: 0.88;
        font-size: 0.9rem;
        line-height: 1.7;
    }
    .notice-center-item-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-top: 0.95rem;
        padding-top: 0.85rem;
        border-top: 1px solid rgba(17, 17, 17, 0.08);
    }

    .notice-center-item-meta {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        flex-wrap: wrap;
        color: inherit;
        opacity: 0.8;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .notice-center-empty {
        display: grid;
        justify-items: center;
        gap: 0.55rem;
        padding: 3rem 1.5rem;
        text-align: center;
    }

    .notice-center-empty-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 4rem;
        height: 4rem;
        border-radius: 1.25rem;
        background: linear-gradient(180deg, #191919 0%, #0d0d0d 100%);
        color: #f4d58f;
    }

    .notice-center-empty h3 {
        margin: 0;
        color: #111111;
        font-size: 1.1rem;
        font-weight: 700;
    }

    .notice-center-empty p {
        margin: 0;
        color: #756d61;
        font-size: 0.9rem;
        line-height: 1.65;
        max-width: 24rem;
    }

    @media (max-width: 768px) {
        .notice-center-hero,
        .notice-center-item-top,
        .notice-center-item-footer {
            flex-direction: column;
            align-items: stretch;
        }

        .notice-center-item-main {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
