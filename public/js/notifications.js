 // Notification System
class NotificationManager {
    constructor() {
        this.notificationCheckInterval = null;
        this.eventListenersInitialized = false;
        this.init();
    }

    init() {
        this.startNotificationChecking();
        this.setupEventListeners();
    }

    // Load notifications
    loadNotifications() {
        fetch('/notifications/latest')
            .then(response => response.json())
            .then(data => {
                this.updateNotificationCount(data.unreadCount);
                this.updateNotificationList(data.notifications);
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
            });
    }

    // Update notification count
    updateNotificationCount(count) {
        const countElement = document.getElementById('notificationCount');
        if (!countElement) return;

        if (count > 0) {
            countElement.textContent = count;
            countElement.classList.remove('hidden');
        } else {
            countElement.classList.add('hidden');
        }
    }

    // Update notification list
    updateNotificationList(notifications) {
        const listElement = document.getElementById('notificationList');
        if (!listElement) return;
        
        if (notifications.length === 0) {
            listElement.innerHTML = `
                <div class="px-4 py-3 text-center text-gray-500">
                    <i class="fas fa-bell-slash mr-2"></i>Không có thông báo mới
                </div>
            `;
            return;
        }

        let html = '';
        notifications.forEach(notification => {
            const isUnread = !notification.is_read;
            const timeAgo = this.getTimeAgo(notification.created_at);
            
            html += `
                <a href="${notification.link || '#'}" 
                   class="flex px-4 py-3 border-b border-gray-100 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors notification-item"
                   data-id="${notification.id}">
                    <div class="flex-shrink-0 mr-3">
                        <div class="h-8 w-8 rounded-full ${this.getNotificationColor(notification.type)} text-white flex items-center justify-center">
                            <i class="${notification.icon}"></i>
                        </div>
                    </div>
                    <div class="flex-grow">
                        <p class="text-sm text-gray-800 dark:text-gray-200 mb-1 font-medium">${notification.title}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">${notification.message}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">${timeAgo}</p>
                    </div>
                    ${isUnread ? '<span class="h-2 w-2 bg-blue-500 rounded-full"></span>' : ''}
                </a>
            `;
        });
        
        listElement.innerHTML = html;

        // Add click handlers for notifications
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function(e) {
                const notificationId = this.dataset.id;
                this.markNotificationAsRead(notificationId);
            }.bind(this));
        });
    }

    // Get notification color based on type
    getNotificationColor(type) {
        switch (type) {
            case 'success':
                return 'bg-green-500';
            case 'warning':
                return 'bg-yellow-500';
            case 'error':
                return 'bg-red-500';
            case 'info':
            default:
                return 'bg-blue-500';
        }
    }

    // Get time ago
    getTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) {
            return 'Vừa xong';
        } else if (diffInSeconds < 3600) {
            const minutes = Math.floor(diffInSeconds / 60);
            return `${minutes} phút trước`;
        } else if (diffInSeconds < 86400) {
            const hours = Math.floor(diffInSeconds / 3600);
            return `${hours} giờ trước`;
        } else {
            const days = Math.floor(diffInSeconds / 86400);
            return `${days} ngày trước`;
        }
    }

    // Mark notification as read
    markNotificationAsRead(notificationId) {
        const csrfToken = this.getCsrfToken();
        
        if (!csrfToken) {
            console.error('CSRF token not found');
            return;
        }
        
        fetch(`/notifications/${notificationId}/mark-read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Reload notifications to update count
                this.loadNotifications();
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }

    // Get CSRF token from various sources
    getCsrfToken() {
        // Try to get from meta tag
        let token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // If not found, try to get from form
        if (!token) {
            token = document.querySelector('input[name="_token"]')?.value;
        }
        
        // If still not found and we have jQuery, try Laravel's $.ajaxSetup
        if (!token && typeof $ !== 'undefined' && $.ajaxSettings && $.ajaxSettings.headers) {
            token = $.ajaxSettings.headers['X-CSRF-TOKEN'];
        }
        
        return token || '';
    }

    // Mark all notifications as read
    markAllAsRead() {
        const csrfToken = this.getCsrfToken();
        
        if (!csrfToken) {
            console.error('CSRF token not found');
            
            // Thêm thông báo lỗi nếu không tìm thấy CSRF token
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Không tìm thấy CSRF token. Vui lòng tải lại trang và thử lại.'
                });
            }
            return;
        }
        
        fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Show success message with SweetAlert2
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
                
                // Reload notifications
                this.loadNotifications();
            }
        })
        .catch(error => {
            console.error('Error marking all notifications as read:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Có lỗi xảy ra khi đánh dấu thông báo: ' + error.message
                });
            }
        });
    }

    // Start notification checking
    startNotificationChecking() {
        // Load notifications immediately
        this.loadNotifications();
        
        // Check for new notifications every 30 seconds
        this.notificationCheckInterval = setInterval(() => {
            this.loadNotifications();
        }, 30000);
    }

    // Stop notification checking
    stopNotificationChecking() {
        if (this.notificationCheckInterval) {
            clearInterval(this.notificationCheckInterval);
        }
    }

    // Setup event listeners
    setupEventListeners() {
        // Prevent multiple event listeners
        if (this.eventListenersInitialized) {
            return;
        }
        
        // Mark all as read button
        const markAllReadBtn = document.getElementById('markAllReadBtn');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.markAllAsRead();
            });
        }

        // Clean up when page is unloaded
        window.addEventListener('beforeunload', () => {
            this.stopNotificationChecking();
        });
        
        this.eventListenersInitialized = true;
    }
}

// Initialize notification manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Ensure we only create one instance
    if (!window.notificationManager) {
        window.notificationManager = new NotificationManager();
    }
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NotificationManager;
}