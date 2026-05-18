@php
    $notificationsRoute = route('notifications.index');
@endphp

<!-- Floating Notification Card -->
<div class="floating-notification-card" id="notificationCard" style="display: none;">
    <div class="notification-header">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold">
                <i class="fas fa-bell me-2"></i>
                Notifications
                <span class="badge bg-danger ms-2" id="headerUnreadCount" style="display: none;">0</span>
            </h6>
            <button class="btn-close btn-close-sm" onclick="closeNotificationCard()"></button>
        </div>
    </div>

    <div class="notification-list" id="notificationList">
        <div class="text-center text-muted py-3">
            <div class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            Loading notifications...
        </div>
    </div>

    <div class="notification-footer">
        <a href="{{ $notificationsRoute }}" class="btn btn-black btn-sm w-100">
            <i class="fas fa-list me-2"></i>View All Notifications
        </a>
    </div>
</div>

<!-- Notification Toggle Button -->
<button class="notification-toggle-btn" id="notificationToggle" onclick="toggleNotificationCard()">
    <i class="fas fa-bell"></i>
    <span class="notification-count" id="toggleUnreadCount" style="display: none;">0</span>
</button>

<style>
.floating-notification-card {
    position: fixed;
    top: 80px;
    right: 20px;
    width: 350px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    border: 1px solid #e5e7eb;
    z-index: 1050;
    max-height: 500px;
    overflow: hidden;
    animation: slideInRight 0.3s ease-out;
}

.notification-header {
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
    background: #f8f9fa;
    border-radius: 12px 12px 0 0;
}

.notification-list {
    max-height: 300px;
    overflow-y: auto;
}

.notification-item {
    padding: 12px 20px;
    border-bottom: 1px solid #f1f3f4;
    cursor: pointer;
    transition: background-color 0.2s;
    position: relative;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #f0f9ff;
    border-left: 3px solid #3b82f6;
}

.notification-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    flex-shrink: 0;
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-weight: 600;
    font-size: 14px;
    color: #1f2937;
    margin-bottom: 4px;
    line-height: 1.3;
}

.notification-message {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 4px;
    line-height: 1.4;
}

.notification-time {
    font-size: 11px;
    color: #9ca3af;
}

.unread-dot {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 8px;
    height: 8px;
    background-color: #3b82f6;
    border-radius: 50%;
}

.notification-footer {
    padding: 12px 20px;
    border-top: 1px solid #e5e7eb;
    background: #f8f9fa;
    border-radius: 0 0 12px 12px;
}

.notification-toggle-btn {
    position: fixed;
    top: 80px;
    right: 20px;
    width: 50px;
    height: 50px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 50%;
    box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
    cursor: pointer;
    z-index: 1040;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: transform 0.3s ease, background-color 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease, color 0.3s ease;
}

.notification-toggle-btn:hover {
    transform: scale(1.05);
}

.notification-count {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ef4444;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 10px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .floating-notification-card {
        width: calc(100vw - 40px);
        right: 20px;
        left: 20px;
    }
}
</style>

<script>
function toggleNotificationCard() {
    const card = document.getElementById('notificationCard');
    const toggle = document.getElementById('notificationToggle');

    if (card.style.display === 'none') {
        card.style.display = 'block';
        toggle.style.display = 'none';
        // Load notifications when opening the card
        loadNotifications();
    } else {
        card.style.display = 'none';
        toggle.style.display = 'flex';
    }
}

function closeNotificationCard() {
    const card = document.getElementById('notificationCard');
    const toggle = document.getElementById('notificationToggle');

    card.style.display = 'none';
    toggle.style.display = 'flex';
}

function loadNotifications() {
    fetch('/notifications?limit=3')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('notificationList');

            if (data.notifications && data.notifications.length > 0) {
                container.innerHTML = data.notifications.map(notification => `
                    <div class="notification-item ${notification.read_at ? '' : 'unread'}"
                         onclick="markNotificationAsRead(${notification.id})">
                        <div class="d-flex align-items-start">
                            <div class="notification-icon">
                                <i class="fas fa-${getNotificationIcon(notification.type)} ${getNotificationColor(notification.type)}"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">${notification.title}</div>
                                <div class="notification-message">${truncateText(notification.message, 50)}</div>
                                <div class="notification-time">${formatTime(notification.created_at)}</div>
                            </div>
                            ${!notification.read_at ? '<div class="unread-dot"></div>' : ''}
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = `
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-bell-slash fa-2x mb-2"></i>
                        <div>No notifications</div>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            document.getElementById('notificationList').innerHTML = `
                <div class="text-center text-muted py-3">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <div>Error loading notifications</div>
                </div>
            `;
        });
}

function markNotificationAsRead(notificationId) {
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
            // Update UI to show as read
            const notificationItem = event.currentTarget;
            notificationItem.classList.remove('unread');
            const dot = notificationItem.querySelector('.unread-dot');
            if (dot) dot.remove();

            // Update unread count
            updateUnreadCount();
        }
    })
    .catch(error => console.error('Error marking notification as read:', error));
}

function updateUnreadCount() {
    fetch('/notifications/unread-count')
        .then(response => response.json())
        .then(data => {
            const toggleBadge = document.getElementById('toggleUnreadCount');
            const headerBadge = document.getElementById('headerUnreadCount');

            if (data.count > 0) {
                if (toggleBadge) {
                    toggleBadge.textContent = data.count;
                    toggleBadge.style.display = 'flex';
                }
                if (headerBadge) {
                    headerBadge.textContent = data.count;
                    headerBadge.style.display = 'inline';
                }
            } else {
                if (toggleBadge) toggleBadge.style.display = 'none';
                if (headerBadge) headerBadge.style.display = 'none';
            }
        })
        .catch(error => console.error('Error updating unread count:', error));
}

// Helper functions
function getNotificationIcon(type) {
    const icons = {
        'success': 'check-circle',
        'warning': 'exclamation-triangle',
        'error': 'times-circle',
        'info': 'info-circle',
        'work_update': 'document-text',
        'approval': 'check-circle',
        'rejection': 'times-circle',
        'system': 'cog',
        'otp_request': 'key',
        'otp_submission': 'check'
    };
    return icons[type] || 'info-circle';
}

function getNotificationColor(type) {
    const colors = {
        'success': 'text-success',
        'warning': 'text-warning',
        'error': 'text-danger',
        'info': 'text-primary',
        'work_update': 'text-primary',
        'approval': 'text-success',
        'rejection': 'text-danger',
        'system': 'text-secondary',
        'otp_request': 'text-info',
        'otp_submission': 'text-success'
    };
    return colors[type] || 'text-primary';
}

function truncateText(text, maxLength) {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

function formatTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);

    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
    if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`;

    return date.toLocaleDateString();
}

// Close notification card when clicking outside
document.addEventListener('click', function(event) {
    const card = document.getElementById('notificationCard');
    const toggle = document.getElementById('notificationToggle');

    if (!card.contains(event.target) && !toggle.contains(event.target)) {
        card.style.display = 'none';
        toggle.style.display = 'flex';
    }
});

// Load unread count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateUnreadCount();

    // Refresh unread count every 30 seconds
    setInterval(updateUnreadCount, 30000);
});
</script>
