<x-app-layout>
    <x-slot name="title">Notifications</x-slot>
    <x-slot name="pageTitle">Notifications</x-slot>
    <x-slot name="pageSubtitle">Stay updated with system notifications</x-slot>

    @php
        $totalNotifications = $notifications->count();
        $readCount = $notifications->filter(fn($notification) => $notification->isRead())->count();
    @endphp

    <style>
        .notification-center-shell {
            display: grid;
            gap: 1rem;
        }

        .notification-center-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.85rem;
            flex-wrap: wrap;
            padding: 1rem 1.1rem;
            border: 1px solid rgba(200, 164, 93, 0.22);
            border-radius: 1.2rem;
            background: linear-gradient(135deg, rgba(255, 253, 250, 0.98), rgba(245, 239, 229, 0.95));
        }

        .notification-center-title {
            margin: 0;
            color: #111111;
            font-size: 1.05rem;
            font-weight: 700;
        }

        .notification-center-subtitle {
            margin: 0.25rem 0 0;
            color: #6f6555;
            font-size: 0.82rem;
        }

        .notification-center-stat-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .notification-center-stat {
            border: 1px solid rgba(15, 15, 15, 0.06);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.95);
            padding: 0.75rem 0.85rem;
        }

        .notification-center-stat-label {
            color: #8b7350;
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .notification-center-stat-value {
            margin-top: 0.28rem;
            color: #111111;
            font-size: 1.1rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .notification-center-list {
            display: grid;
            gap: 0.75rem;
        }

        .notification-center-item {
            border: 1px solid rgba(15, 15, 15, 0.08);
            border-radius: 1.2rem;
            background: rgba(255, 255, 255, 0.96);
            padding: 0.95rem 1rem;
            box-shadow: 0 10px 24px rgba(15, 15, 15, 0.05);
        }

        .notification-center-item.is-unread {
            border-color: rgba(200, 164, 93, 0.34);
            background: linear-gradient(180deg, #fff8ea 0%, #f9f1dd 100%);
        }

        .notification-center-item-main {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            gap: 0.8rem;
            align-items: start;
        }

        .notification-center-item-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.6rem;
            height: 2.6rem;
            border-radius: 0.95rem;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        .notification-center-item-icon.info {
            background: rgba(200, 164, 93, 0.14);
            color: #9b7431;
        }

        .notification-center-item-icon.success {
            background: rgba(34, 197, 94, 0.14);
            color: #15803d;
        }

        .notification-center-item-icon.warning {
            background: rgba(245, 158, 11, 0.14);
            color: #b45309;
        }

        .notification-center-item-icon.error {
            background: rgba(239, 68, 68, 0.14);
            color: #b91c1c;
        }

        .notification-center-item-title {
            margin: 0;
            color: #17120d;
            font-size: 0.96rem;
            font-weight: 700;
            line-height: 1.35;
        }

        .notification-center-item-message {
            margin: 0.35rem 0 0;
            color: #6b6458;
            font-size: 0.84rem;
            line-height: 1.58;
        }

        .notification-center-item-meta {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            margin-top: 0.5rem;
            color: #8d846f;
            font-size: 0.74rem;
            font-weight: 600;
        }

        .notification-center-item-meta-dot {
            width: 0.2rem;
            height: 0.2rem;
            border-radius: 999px;
            background: rgba(141, 132, 111, 0.7);
        }

        .notification-center-item-actions {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            justify-content: flex-end;
        }

        .notification-center-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2rem;
            padding: 0.4rem 0.7rem;
            border: 1px solid rgba(15, 15, 15, 0.1);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.9);
            color: #17120d;
            font-size: 0.72rem;
            font-weight: 700;
            text-decoration: none;
            transition: transform 0.16s ease, border-color 0.16s ease, background-color 0.16s ease;
        }

        .notification-center-action:hover {
            transform: scale(1.02);
            border-color: rgba(200, 164, 93, 0.34);
            background: #fcf8ef;
            text-decoration: none;
        }

        .notification-center-action.is-danger {
            color: #b91c1c;
            border-color: rgba(185, 28, 28, 0.22);
            background: rgba(254, 242, 242, 0.85);
        }

        .notification-center-empty {
            border: 1px solid rgba(15, 15, 15, 0.08);
            border-radius: 1.2rem;
            background: rgba(255, 255, 255, 0.96);
            padding: 2rem 1.2rem;
            text-align: center;
        }

        @media (max-width: 768px) {
            .notification-center-stat-grid {
                gap: 0.45rem;
            }

            .notification-center-stat {
                padding: 0.58rem 0.52rem;
            }

            .notification-center-stat-label {
                font-size: 0.56rem;
                letter-spacing: 0.08em;
            }

            .notification-center-stat-value {
                font-size: 0.92rem;
            }

            .notification-center-item-main {
                grid-template-columns: auto minmax(0, 1fr);
            }

            .notification-center-item-actions {
                grid-column: 1 / -1;
                justify-content: flex-start;
                margin-top: 0.3rem;
            }
        }
    </style>

    <section class="notification-center-shell">
        <div class="notification-center-toolbar">
            <div>
                <h2 class="notification-center-title">
                    <i class="fas fa-bell me-2"></i>Notification Center
                </h2>
                <p class="notification-center-subtitle">Track alerts, approvals, and account updates in one place.</p>
            </div>

            @if($unreadCount > 0)
                <button class="btn btn-black btn-sm" onclick="markAllAsRead()">
                    <i class="fas fa-check-double me-1"></i>Mark All as Read
                </button>
            @endif
        </div>

        <div class="notification-center-stat-grid">
            <article class="notification-center-stat">
                <div class="notification-center-stat-label">Total</div>
                <div class="notification-center-stat-value">{{ $totalNotifications }}</div>
            </article>
            <article class="notification-center-stat">
                <div class="notification-center-stat-label">Unread</div>
                <div class="notification-center-stat-value">{{ $unreadCount }}</div>
            </article>
            <article class="notification-center-stat">
                <div class="notification-center-stat-label">Read</div>
                <div class="notification-center-stat-value">{{ $readCount }}</div>
            </article>
        </div>

        @if($notifications->count() > 0)
            <div class="notification-center-list">
                @foreach($notifications as $notification)
                    @php
                        $tone = match($notification->type) {
                            'success' => 'success',
                            'warning' => 'warning',
                            'error' => 'error',
                            default => 'info',
                        };

                        $icon = match($notification->type) {
                            'success' => 'fa-check-circle',
                            'warning' => 'fa-exclamation-triangle',
                            'error' => 'fa-times-circle',
                            default => 'fa-info-circle',
                        };
                    @endphp

                    <article class="notification-center-item {{ $notification->isUnread() ? 'is-unread' : '' }}" data-notification-id="{{ $notification->id }}">
                        <div class="notification-center-item-main">
                            <span class="notification-center-item-icon {{ $tone }}">
                                <i class="fas {{ $icon }}"></i>
                            </span>

                            <div>
                                <h3 class="notification-center-item-title">
                                    {{ $notification->title }}
                                    @if($notification->isUnread())
                                        <span class="badge bg-dark ms-2">New</span>
                                    @endif
                                </h3>
                                <p class="notification-center-item-message">{{ $notification->message }}</p>
                                <div class="notification-center-item-meta">
                                    <span>{{ strtoupper($notification->type ?? 'info') }}</span>
                                    <span class="notification-center-item-meta-dot"></span>
                                    <span>{{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                            </div>

                            <div class="notification-center-item-actions">
                                @if($notification->isUnread())
                                    <button type="button" class="notification-center-action" onclick="markAsRead({{ $notification->id }})">
                                        <i class="fas fa-check me-1"></i>Read
                                    </button>
                                @endif

                                @if($notification->resolved_action_url)
                                    <a class="notification-center-action" href="{{ $notification->resolved_action_url }}">
                                        <i class="fas fa-arrow-up-right-from-square me-1"></i>Open
                                    </a>
                                @endif

                                <button type="button" class="notification-center-action is-danger" onclick="deleteNotification({{ $notification->id }})">
                                    <i class="fas fa-trash me-1"></i>Delete
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="notification-center-empty">
                <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                <h5 class="text-muted mb-1">No Notifications</h5>
                <p class="text-muted mb-0">You are all caught up.</p>
            </div>
        @endif
    </section>

    @push('scripts')
    <script>
        function getNotificationsPageElements() {
            return {
                headerUnreadBadge: document.getElementById('unread-count'),
                localUnreadStat: document.querySelector('.notification-center-stat-grid .notification-center-stat:nth-child(2) .notification-center-stat-value'),
            };
        }

        function removeNotificationItem(notificationId) {
            const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notificationItem) {
                notificationItem.remove();
            }
        }

        function syncPageUnreadCount(count) {
            const { headerUnreadBadge, localUnreadStat } = getNotificationsPageElements();

            if (localUnreadStat) {
                localUnreadStat.textContent = String(count);
            }

            if (headerUnreadBadge) {
                headerUnreadBadge.textContent = String(count);
                headerUnreadBadge.classList.toggle('d-none', count <= 0);
            }
        }

        function markAsRead(notificationId) {
            fetch(`/notifications/${notificationId}/mark-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    return;
                }

                const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notificationItem) {
                    notificationItem.classList.remove('is-unread');
                    notificationItem.querySelectorAll('.badge').forEach(badge => badge.remove());
                    notificationItem.querySelectorAll('button').forEach(button => {
                        if (button.textContent.toLowerCase().includes('read')) {
                            button.remove();
                        }
                    });
                }

                updateUnreadCount();
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function markAllAsRead() {
            fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    return;
                }

                document.querySelectorAll('.notification-center-item').forEach(item => {
                    item.classList.remove('is-unread');
                    item.querySelectorAll('.badge').forEach(badge => badge.remove());
                    item.querySelectorAll('button').forEach(button => {
                        if (button.textContent.toLowerCase().includes('read')) {
                            button.remove();
                        }
                    });
                });

                const markAllButton = document.querySelector('.notification-center-toolbar .btn.btn-black.btn-sm');
                if (markAllButton) {
                    markAllButton.remove();
                }

                syncPageUnreadCount(0);

                if (typeof loadNotifications === 'function') {
                    loadNotifications();
                }
                if (typeof loadRecentNotifications === 'function') {
                    loadRecentNotifications();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function deleteNotification(notificationId) {
            if (!confirm('Delete this notification?')) {
                return;
            }

            fetch(`/notifications/${notificationId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    return;
                }

                removeNotificationItem(notificationId);
                updateUnreadCount();
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function updateUnreadCount() {
            fetch('/notifications/unread-count')
                .then(response => response.json())
                .then(data => {
                    syncPageUnreadCount(data.count ?? 0);

                    if (typeof loadNotifications === 'function') {
                        loadNotifications();
                    }

                    if (typeof loadRecentNotifications === 'function') {
                        loadRecentNotifications();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        setInterval(updateUnreadCount, 30000);
    </script>
    @endpush
</x-app-layout>
