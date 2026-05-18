/**
 * Alpine.js Components for Work Automation System
 * Contains all Alpine.js components used throughout the application
 */

// Dashboard Component
window.dashboardComponent = () => ({
    stats: {
        total_work_updates: 0,
        pending_approvals: 0,
        approved_today: 0,
        rejection_rate: 0
    },
    recentActivities: [],
    loading: true,
    error: null,

    async init() {
        await this.loadDashboardData();
        // Refresh data every 30 seconds
        setInterval(() => this.loadDashboardData(), 30000);
    },

    async loadDashboardData() {
        try {
            this.loading = true;
            const [statsData, activitiesData] = await Promise.all([
                window.apiService.getDashboardStats(),
                window.apiService.getRecentActivities(10)
            ]);
            
            this.stats = statsData;
            this.recentActivities = activitiesData;
            this.error = null;
        } catch (error) {
            this.error = 'Failed to load dashboard data';
            console.error('Dashboard error:', error);
        } finally {
            this.loading = false;
        }
    },

    async refreshStats() {
        await this.loadDashboardData();
    },

    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    getStatusBadgeClass(status) {
        const statusClasses = {
            'pending': 'bg-yellow-100 text-yellow-800',
            'approved': 'bg-green-100 text-green-800',
            'rejected': 'bg-red-100 text-red-800',
            'draft': 'bg-gray-100 text-gray-800'
        };
        return statusClasses[status] || 'bg-gray-100 text-gray-800';
    }
});

// Work Update Form Component
window.workUpdateFormComponent = () => ({
    workUpdate: {
        title: '',
        description: '',
        hours_worked: '',
        status: 'draft'
    },
    isEditing: false,
    loading: false,
    errors: {},

    init() {
        // Check if editing existing work update
        const workUpdateId = this.$el.dataset.workUpdateId;
        if (workUpdateId) {
            this.isEditing = true;
            this.loadWorkUpdate(workUpdateId);
        }
    },

    async loadWorkUpdate(id) {
        try {
            this.loading = true;
            const response = await window.axios.get(`/api/work-updates/${id}`);
            this.workUpdate = response.data.data;
        } catch (error) {
            console.error('Failed to load work update:', error);
        } finally {
            this.loading = false;
        }
    },

    async submitForm() {
        try {
            this.loading = true;
            this.errors = {};

            if (this.isEditing) {
                await window.apiService.updateWorkUpdate(this.workUpdate.id, this.workUpdate);
            } else {
                await window.apiService.createWorkUpdate(this.workUpdate);
            }

            // Redirect to work updates list
            window.location.href = '/work-updates';
        } catch (error) {
            if (error.response?.data?.errors) {
                this.errors = error.response.data.errors;
            }
        } finally {
            this.loading = false;
        }
    },

    async submitForApproval() {
        if (this.isEditing && this.workUpdate.id) {
            try {
                await window.apiService.submitWorkUpdate(this.workUpdate.id);
                window.location.href = '/work-updates';
            } catch (error) {
                console.error('Failed to submit for approval:', error);
            }
        }
    },

    hasError(field) {
        return this.errors[field] && this.errors[field].length > 0;
    },

    getError(field) {
        return this.errors[field] ? this.errors[field][0] : '';
    }
});

// Work Updates List Component
window.workUpdatesListComponent = () => ({
    workUpdates: [],
    pagination: {},
    filters: {
        status: '',
        date_from: '',
        date_to: '',
        search: ''
    },
    loading: true,
    currentPage: 1,

    async init() {
        await this.loadWorkUpdates();
    },

    async loadWorkUpdates(page = 1) {
        try {
            this.loading = true;
            const params = {
                page,
                ...this.filters
            };
            
            const response = await window.apiService.getWorkUpdates(params);
            this.workUpdates = response.data;
            this.pagination = response.meta || response.pagination;
            this.currentPage = page;
        } catch (error) {
            console.error('Failed to load work updates:', error);
        } finally {
            this.loading = false;
        }
    },

    async applyFilters() {
        this.currentPage = 1;
        await this.loadWorkUpdates();
    },

    async clearFilters() {
        this.filters = {
            status: '',
            date_from: '',
            date_to: '',
            search: ''
        };
        await this.loadWorkUpdates();
    },

    async deleteWorkUpdate(id) {
        if (confirm('Are you sure you want to delete this work update?')) {
            try {
                await window.apiService.deleteWorkUpdate(id);
                await this.loadWorkUpdates(this.currentPage);
            } catch (error) {
                console.error('Failed to delete work update:', error);
            }
        }
    },

    async approveWorkUpdate(id) {
        const notes = prompt('Add approval notes (optional):');
        try {
            await window.apiService.approveWorkUpdate(id, notes || '');
            await this.loadWorkUpdates(this.currentPage);
        } catch (error) {
            console.error('Failed to approve work update:', error);
        }
    },

    async rejectWorkUpdate(id) {
        const notes = prompt('Add rejection notes (required):');
        if (!notes) {
            alert('Rejection notes are required');
            return;
        }
        
        try {
            await window.apiService.rejectWorkUpdate(id, notes);
            await this.loadWorkUpdates(this.currentPage);
        } catch (error) {
            console.error('Failed to reject work update:', error);
        }
    },

    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },

    getStatusBadgeClass(status) {
        const statusClasses = {
            'pending': 'bg-yellow-100 text-yellow-800',
            'approved': 'bg-green-100 text-green-800',
            'rejected': 'bg-red-100 text-red-800',
            'draft': 'bg-gray-100 text-gray-800'
        };
        return statusClasses[status] || 'bg-gray-100 text-gray-800';
    }
});

// Search Component
window.searchComponent = () => ({
    query: '',
    results: [],
    loading: false,
    showResults: false,

    async search() {
        if (this.query.length < 3) {
            this.results = [];
            this.showResults = false;
            return;
        }

        try {
            this.loading = true;
            const response = await window.apiService.search(this.query);
            this.results = response.data;
            this.showResults = true;
        } catch (error) {
            console.error('Search failed:', error);
            this.results = [];
        } finally {
            this.loading = false;
        }
    },

    hideResults() {
        setTimeout(() => {
            this.showResults = false;
        }, 200);
    },

    selectResult(result) {
        this.query = result.title || result.name;
        this.showResults = false;
        
        // Navigate to the selected item
        if (result.type === 'work_update') {
            window.location.href = `/work-updates/${result.id}`;
        } else if (result.type === 'user') {
            window.location.href = `/users/${result.id}`;
        }
    },

    highlightMatch(text, query) {
        if (!query) return text;
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    }
});

// Notification Component
window.notificationComponent = () => ({
    notifications: [],

    init() {
        // Listen for notification events
        window.addEventListener('show-notification', (event) => {
            this.addNotification(event.detail);
        });
    },

    addNotification({ message, type = 'info', duration = 5000 }) {
        const id = Date.now();
        const notification = {
            id,
            message,
            type,
            visible: true
        };

        this.notifications.push(notification);

        // Auto-remove after duration
        setTimeout(() => {
            this.removeNotification(id);
        }, duration);
    },

    removeNotification(id) {
        const index = this.notifications.findIndex(n => n.id === id);
        if (index > -1) {
            this.notifications[index].visible = false;
            setTimeout(() => {
                this.notifications.splice(index, 1);
            }, 300); // Wait for fade out animation
        }
    },

    getNotificationClasses(type) {
        const baseClasses = 'p-4 rounded-lg shadow-lg transition-all duration-300 transform';
        const typeClasses = {
            'success': 'bg-green-500 text-white',
            'error': 'bg-red-500 text-white',
            'warning': 'bg-yellow-500 text-white',
            'info': 'bg-blue-500 text-white'
        };
        return `${baseClasses} ${typeClasses[type] || typeClasses.info}`;
    }
});

// Mobile Menu Component
window.mobileMenuComponent = () => ({
    isOpen: false,

    toggle() {
        this.isOpen = !this.isOpen;
    },

    close() {
        this.isOpen = false;
    }
});

// Modal Component
window.modalComponent = () => ({
    isOpen: false,
    title: '',
    content: '',

    open(title = '', content = '') {
        this.title = title;
        this.content = content;
        this.isOpen = true;
        document.body.style.overflow = 'hidden';
    },

    close() {
        this.isOpen = false;
        document.body.style.overflow = '';
    },

    closeOnBackdrop(event) {
        if (event.target === event.currentTarget) {
            this.close();
        }
    }
});

// System Settings Component
window.systemSettingsComponent = () => ({
    settings: {},
    loading: true,
    saving: false,
    errors: {},

    async init() {
        await this.loadSettings();
    },

    async loadSettings() {
        try {
            this.loading = true;
            const response = await window.apiService.getSystemSettings();
            this.settings = response.data;
        } catch (error) {
            console.error('Failed to load settings:', error);
        } finally {
            this.loading = false;
        }
    },

    async saveSettings() {
        try {
            this.saving = true;
            this.errors = {};
            await window.apiService.updateSystemSettings(this.settings);
        } catch (error) {
            if (error.response?.data?.errors) {
                this.errors = error.response.data.errors;
            }
        } finally {
            this.saving = false;
        }
    },

    hasError(field) {
        return this.errors[field] && this.errors[field].length > 0;
    },

    getError(field) {
        return this.errors[field] ? this.errors[field][0] : '';
    }
});