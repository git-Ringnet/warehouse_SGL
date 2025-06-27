<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Thông báo - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center mb-8">Test Hệ thống Thông báo</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Test tạo thông báo -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Tạo thông báo test</h2>
                <div class="space-y-4">
                    <button onclick="createTestNotification('success')" class="w-full bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600">
                        <i class="fas fa-check-circle mr-2"></i>Thông báo thành công
                    </button>
                    <button onclick="createTestNotification('warning')" class="w-full bg-yellow-500 text-white py-2 px-4 rounded hover:bg-yellow-600">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Thông báo cảnh báo
                    </button>
                    <button onclick="createTestNotification('error')" class="w-full bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600">
                        <i class="fas fa-times-circle mr-2"></i>Thông báo lỗi
                    </button>
                    <button onclick="createTestNotification('info')" class="w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
                        <i class="fas fa-info-circle mr-2"></i>Thông báo thông tin
                    </button>
                </div>
            </div>

            <!-- Test phiếu lắp ráp -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Test phiếu lắp ráp</h2>
                <div class="space-y-4">
                    <button onclick="createAssemblyNotification()" class="w-full bg-purple-500 text-white py-2 px-4 rounded hover:bg-purple-600">
                        <i class="fas fa-tools mr-2"></i>Tạo thông báo phiếu lắp ráp
                    </button>
                    <button onclick="createTestingNotification()" class="w-full bg-indigo-500 text-white py-2 px-4 rounded hover:bg-indigo-600">
                        <i class="fas fa-vial mr-2"></i>Tạo thông báo phiếu kiểm thử
                    </button>
                    <button onclick="createDispatchNotification()" class="w-full bg-teal-500 text-white py-2 px-4 rounded hover:bg-teal-600">
                        <i class="fas fa-truck mr-2"></i>Tạo thông báo phiếu xuất kho
                    </button>
                </div>
            </div>

            <!-- Test dự án -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Test dự án</h2>
                <div class="space-y-4">
                    <button onclick="createProjectNotification()" class="w-full bg-orange-500 text-white py-2 px-4 rounded hover:bg-orange-600">
                        <i class="fas fa-project-diagram mr-2"></i>Tạo thông báo dự án mới
                    </button>
                    <button onclick="createProjectExpiryNotification()" class="w-full bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600">
                        <i class="fas fa-clock mr-2"></i>Tạo thông báo hết hạn bảo hành
                    </button>
                </div>
            </div>

            <!-- Xem thông báo -->
            <div class="bg-white p-6 rounded-lg shadow-md md:col-span-2">
                <h2 class="text-xl font-semibold mb-4">Xem thông báo hiện tại</h2>
                <div class="flex space-x-4 mb-4">
                    <button onclick="loadNotifications()" class="bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600">
                        <i class="fas fa-sync-alt mr-2"></i>Làm mới
                    </button>
                    <button onclick="markAllAsRead()" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
                        <i class="fas fa-check-double mr-2"></i>Đánh dấu tất cả đã đọc
                    </button>
                </div>
                <div id="notificationList" class="border rounded-lg p-4 min-h-64">
                    <div class="text-center text-gray-500">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>Đang tải thông báo...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Load notifications on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadNotifications();
        });

        // Create test notification
        function createTestNotification(type) {
            const messages = {
                success: 'Đây là thông báo thành công test',
                warning: 'Đây là thông báo cảnh báo test',
                error: 'Đây là thông báo lỗi test',
                info: 'Đây là thông báo thông tin test'
            };

            const titles = {
                success: 'Thành công',
                warning: 'Cảnh báo',
                error: 'Lỗi',
                info: 'Thông tin'
            };

            fetch('/notifications/create-test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    title: titles[type],
                    message: messages[type],
                    type: type
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: 'Đã tạo thông báo test',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadNotifications();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: data.message || 'Có lỗi xảy ra'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Có lỗi xảy ra khi tạo thông báo'
                });
            });
        }

        // Create assembly notification
        function createAssemblyNotification() {
            fetch('/notifications/create-assembly-test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: 'Đã tạo thông báo phiếu lắp ráp test',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadNotifications();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Có lỗi xảy ra'
                });
            });
        }

        // Create testing notification
        function createTestingNotification() {
            fetch('/notifications/create-testing-test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: 'Đã tạo thông báo phiếu kiểm thử test',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadNotifications();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Có lỗi xảy ra'
                });
            });
        }

        // Create dispatch notification
        function createDispatchNotification() {
            fetch('/notifications/create-dispatch-test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: 'Đã tạo thông báo phiếu xuất kho test',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadNotifications();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Có lỗi xảy ra'
                });
            });
        }

        // Create project notification
        function createProjectNotification() {
            fetch('/notifications/create-project-test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: 'Đã tạo thông báo dự án test',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadNotifications();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Có lỗi xảy ra'
                });
            });
        }

        // Create project expiry notification
        function createProjectExpiryNotification() {
            fetch('/notifications/create-project-expiry-test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: 'Đã tạo thông báo hết hạn bảo hành test',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadNotifications();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Có lỗi xảy ra'
                });
            });
        }

        // Load notifications
        function loadNotifications() {
            const container = document.getElementById('notificationList');
            container.innerHTML = `
                <div class="text-center text-gray-500">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <p>Đang tải thông báo...</p>
                </div>
            `;

            fetch('/notifications/latest')
                .then(response => response.json())
                .then(data => {
                    if (data.notifications.length === 0) {
                        container.innerHTML = `
                            <div class="text-center text-gray-500">
                                <i class="fas fa-bell-slash text-4xl mb-2"></i>
                                <p>Không có thông báo nào</p>
                            </div>
                        `;
                        return;
                    }

                    let html = '';
                    data.notifications.forEach(notification => {
                        const isUnread = !notification.is_read;
                        const timeAgo = getTimeAgo(notification.created_at);
                        
                        html += `
                            <div class="border-b border-gray-200 p-3 ${isUnread ? 'bg-blue-50' : ''}">
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="h-8 w-8 rounded-full ${getNotificationColor(notification.type)} text-white flex items-center justify-center">
                                            <i class="${notification.icon}"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">${notification.title}</p>
                                        <p class="text-sm text-gray-600">${notification.message}</p>
                                        <p class="text-xs text-gray-500 mt-1">${timeAgo}</p>
                                    </div>
                                    ${isUnread ? '<span class="h-2 w-2 bg-blue-500 rounded-full"></span>' : ''}
                                </div>
                            </div>
                        `;
                    });
                    
                    container.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    container.innerHTML = `
                        <div class="text-center text-red-500">
                            <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                            <p>Có lỗi xảy ra khi tải thông báo</p>
                        </div>
                    `;
                });
        }

        // Mark all as read
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
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadNotifications();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Có lỗi xảy ra'
                });
            });
        }

        // Helper functions
        function getNotificationColor(type) {
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

        function getTimeAgo(dateString) {
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
    </script>
</body>
</html> 