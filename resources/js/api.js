/**
 * API Service for Work Automation System
 * Handles all API interactions with proper error handling and authentication
 */

class ApiService {
    constructor() {
        this.baseURL = '/api';
        this.setupAxiosInterceptors();
    }

    setupAxiosInterceptors() {
        // Request interceptor for adding auth token
        window.axios.interceptors.request.use(
            config => {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (token) {
                    config.headers['X-CSRF-TOKEN'] = token;
                }
                return config;
            },
            error => Promise.reject(error)
        );

        // Response interceptor for handling common errors
        window.axios.interceptors.response.use(
            response => response,
            error => {
                if (error.response?.status === 401) {
                    // Handle unauthorized access
                    window.location.href = '/login';
                } else if (error.response?.status === 403) {
                    // Handle forbidden access
                    this.showErrorNotification('You do not have permission to perform this action');
                } else if (error.response?.status >= 500) {
                    // Handle server errors
                    this.showErrorNotification('Server error occurred. Please try again later.');
                }
                return Promise.reject(error);
            }
        );
    }

    // Work Updates API
    async getWorkUpdates(params = {}) {
        try {
            const response = await window.axios.get(`${this.baseURL}/work-updates`, { params });
            return response.data;
        } catch (error) {
            throw this.handleError(error);
        }
    }

    async createWorkUpdate(data) {
        try {
            const response = await window.axios.post(`${this.baseURL}/work-updates`, data);
            this.showSuccessNotification('Work update created successfully');
            return response.data;
        } catch (error) {
            throw this.handleError(error);
        }
    }

    async updateWorkUpdate(id, data) {
        try {
            const response = await window.axios.put(`${this.baseURL}/work-updates/${id}`, data);
            this.showSuccessNotification('Work update updated successfully');
            return response.data;
        } catch (error) {
            throw this.handleError(error);
        }
    }

    async deleteWorkUpdate(id) {
        try {
            await window.axios.delete(`${this.baseURL}/work-updates/${id}`);
            this.showSuccessNotification('Work update deleted successfully');
            return true;
        } catch (error) {
            throw this.handleError(error);
        }
    }

    async submitWorkUpdate(id) {
        try {
            const response = await window.axios.post(`${this.baseURL}/work-updates/${id}/submit`);
            this.showSuccessNotification('Work update submitted for approval');
            return response.data;
        } catch (error) {
            throw this.handleError(error);
        }
    }

    async approveWorkUpdate(id, notes = '') {
        try {
            const response = await window.axios.post(`${this.baseURL}/work-updates/${id}/approve`, { notes });
            this.showSuccessNotification('Work update approved');
            return response.data;
        } catch (error) {
            throw this.handleError(error);
        }
    }

    async rejectWorkUpdate(id, notes = '') {
        try {
            const response = await window.axios.post(`${this.baseURL}/work-updates/${id}/reject`, { notes });
            this.showSuccessNotification('Work update rejected');
            return response.data;
        } catch (error) {
            throw this.handleError(error);
        }
    }

    // Work Update Batches API
    async getWorkUpdateBatches(params = {}) {
        try {
            const response = await window.axios.get(`${this.baseURL}/work-update-batches`, { params });
            return response.data;
        } catch (error) {
            throw this.handleError(error);
        }
    }

    async submitDailyBatch(date) {
        try {
            const response = await window.axios.post(`${this.baseURL}/work-update-batches/submit-daily`, { date });
            this.showSuccessNotification('Daily batch submitted successfully');
            return response.data;
        } catch (error) {
            throw this.handleError(error);
        }
    }

    // Search API
    async search(query, filters = {}) {
        try {
            const params = { query, ...filters };
            const response = await window.axios.get(`${this.baseURL}/search`, { params });
            return response.data;
        } catch (error) {
            throw this.handleError(error);
        }
    }

    // Dashboard Stats API
    async getDashboardStats() {
        try {
            const response = await window.axios.get(`${this.baseURL}/dashboard/stats`);
            return response.data;
        } catch (error) {
            throw this.handleError(error);
        }
    }

    async getRecentActivities(limit = 10) {
        try {
            const response = await window.axios.get(`${this.baseURL}/dashboard/recent-activities`, { 
                params: { limit } 
            });
            return response.data;
        } catch (error) {
            throw this.handleError(error);
        }
    }

    // Users API
    async getUsers(params = {}) {
        try {
            const response = await window.axios.get(`${this.baseURL}/users`, { params });
            return response.data;
        } catch (error) {
            throw this.handleError(error);
        }
    }

    async updateUser(id, data) {
        try {
            const response = await window.axios.put(`${this.baseURL}/users/${id}`, data);
            this.showSuccessNotification('User updated successfully');
            return response.data;
        } catch (error) {
            throw this.handleError(error);
        }
    }

    // System Settings API
    async getSystemSettings() {
        try {
            const response = await window.axios.get(`${this.baseURL}/system-settings`);
            return response.data;
        } catch (error) {
            throw this.handleError(error);
        }
    }

    async updateSystemSettings(data) {
        try {
            const response = await window.axios.put(`${this.baseURL}/system-settings`, data);
            this.showSuccessNotification('System settings updated successfully');
            return response.data;
        } catch (error) {
            throw this.handleError(error);
        }
    }

    // Error handling
    handleError(error) {
        console.error('API Error:', error);
        
        if (error.response?.data?.message) {
            this.showErrorNotification(error.response.data.message);
        } else if (error.message) {
            this.showErrorNotification(error.message);
        } else {
            this.showErrorNotification('An unexpected error occurred');
        }
        
        return error;
    }

    // Notification helpers
    showSuccessNotification(message) {
        this.showNotification(message, 'success');
    }

    showErrorNotification(message) {
        this.showNotification(message, 'error');
    }

    showNotification(message, type = 'info') {
        // Dispatch custom event for notification system
        window.dispatchEvent(new CustomEvent('show-notification', {
            detail: { message, type }
        }));
    }
}

// Export as global instance
window.apiService = new ApiService();

export default ApiService;