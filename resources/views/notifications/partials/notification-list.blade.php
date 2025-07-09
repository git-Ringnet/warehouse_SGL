@if($notifications->count() > 0)
    @foreach($notifications as $notification)
        <div class="notification-item border-b border-gray-200 dark:border-gray-700 p-5 {{ !$notification->is_read ? 'unread bg-blue-50 dark:bg-blue-900/20' : '' }}" 
             data-id="{{ $notification->id }}" 
             data-type="{{ $notification->type }}" 
             data-icon="{{ $notification->icon }}" 
             data-link="{{ $notification->link }}">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-full {{ getNotificationColor($notification->type) }} text-white flex items-center justify-center shadow-md">
                        <i class="{{ $notification->icon }}"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <p class="notification-title text-base font-medium text-gray-900 dark:text-white">{{ $notification->title }}</p>
                        <div class="flex items-center space-x-3">
                            @if(!$notification->is_read)
                                <span class="h-3 w-3 bg-blue-500 rounded-full shadow-sm"></span>
                            @endif
                            <button onclick="deleteNotification({{ $notification->id }})" class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <p class="notification-message text-sm text-gray-600 dark:text-gray-300 mt-2 leading-relaxed">{{ $notification->message }}</p>
                    <div class="flex items-center justify-between mt-3">
                        <p class="notification-time text-xs text-gray-500 dark:text-gray-400">
                            <i class="far fa-clock mr-1"></i> {{ $notification->created_at->diffForHumans() }}
                        </p>
                        @if($notification->link)
                            <a href="{{ $notification->link }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                Xem chi tiết <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Pagination -->
    @if($notifications->hasPages())
        <div class="pagination px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $notifications->links() }}
        </div>
    @endif
@else
    <div class="py-16 px-8 text-center">
        <div class="bg-gray-100 dark:bg-gray-800 rounded-full h-20 w-20 mx-auto flex items-center justify-center mb-4">
            <i class="fas fa-bell-slash text-4xl text-gray-400"></i>
        </div>
        <h3 class="text-xl font-medium text-gray-700 dark:text-gray-300 mb-2">Không có thông báo</h3>
        <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">Hiện tại bạn chưa có thông báo nào. Khi có thông báo mới, chúng sẽ xuất hiện ở đây.</p>
    </div>
@endif

@php
function getNotificationColor($type) {
    switch ($type) {
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
@endphp 