@extends('layouts.app')

@section('title', 'Thông báo')

@section('content')
<div class="w-full max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Thông báo</h1>
        <div class="flex space-x-2">
            <button id="markAllReadBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm transition-all">
                <i class="fas fa-check-double mr-2"></i>Đánh dấu tất cả đã đọc
            </button>
            <button id="refreshBtn" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm transition-all">
                <i class="fas fa-sync-alt mr-2"></i>Làm mới
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 w-full">
        <div id="notificationContainer" class="w-full">
            @include('notifications.partials.notification-list')
        </div>
    </div>
</div>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chỉ thiết lập các sự kiện nếu chưa được khởi tạo bởi notifications.js
    if (!window.notificationManager) {
        // Mark notification as read
        function markNotificationAsRead(notificationId) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            fetch(`{{ route('notifications.mark-read', '') }}/${notificationId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
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
                    // Remove unread styling
                    const notification = document.querySelector(`[data-id="${notificationId}"]`);
                    if (notification) {
                        notification.classList.remove('unread', 'bg-blue-50', 'dark:bg-blue-900/20');
                        const unreadDot = notification.querySelector('.bg-blue-500.rounded-full');
                        if (unreadDot) {
                            unreadDot.remove();
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
            });
        }

        // Delete notification
        window.deleteNotification = function(notificationId) {
            Swal.fire({
                title: 'Xác nhận xóa',
                text: 'Bạn có chắc chắn muốn xóa thông báo này?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    
                    fetch(`{{ route('notifications.destroy', '') }}/${notificationId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
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
                            Swal.fire({
                                icon: 'success',
                                title: 'Thành công!',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            
                            // Reload page
                            location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting notification:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi!',
                            text: 'Có lỗi xảy ra khi xóa thông báo'
                        });
                    });
                }
            });
        };

        // Mark all notifications as read
        const markAllReadBtn = document.getElementById('markAllReadBtn');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function() {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                fetch('{{ route("notifications.mark-all-read") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
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
                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        // Reload page
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error marking all notifications as read:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: 'Có lỗi xảy ra khi đánh dấu thông báo'
                    });
                });
            });
        }

        // Refresh button
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function() {
                location.reload();
            });
        }

        // Add click handlers for notifications
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function(e) {
                if (!e.target.closest('button') && !e.target.closest('a')) {
                    const notificationId = this.dataset.id;
                    markNotificationAsRead(notificationId);
                }
            });
        });
    }
});
</script>

<style>
.notification-item.unread {
    border-left: 4px solid #3b82f6;
}

.notification-item {
    transition: all 0.2s ease;
}

.notification-item:hover {
    cursor: pointer;
    background-color: #f3f4f6;
}

.dark .notification-item:hover {
    background-color: #2d3748;
}

.notification-item button {
    transition: transform 0.2s ease;
}

.notification-item button:hover {
    transform: scale(1.1);
}
</style>
@endsection 