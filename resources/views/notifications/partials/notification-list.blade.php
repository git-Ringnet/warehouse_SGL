@if($notifications->count() > 0)
    @foreach($notifications as $notification)
        <div class="notification-item border-b border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors {{ !$notification->is_read ? 'unread bg-blue-50 dark:bg-blue-900/20' : '' }}" 
             data-id="{{ $notification->id }}" 
             data-type="{{ $notification->type }}" 
             data-icon="{{ $notification->icon }}" 
             data-link="{{ $notification->link }}">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <div class="h-10 w-10 rounded-full {{ getNotificationColor($notification->type) }} text-white flex items-center justify-center">
                        <i class="{{ $notification->icon }}"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <p class="notification-title text-sm font-medium text-gray-900 dark:text-white">{{ $notification->title }}</p>
                        <div class="flex items-center space-x-2">
                            @if(!$notification->is_read)
                                <span class="h-2 w-2 bg-blue-500 rounded-full"></span>
                            @endif
                            <button onclick="deleteNotification({{ $notification->id }})" class="text-gray-400 hover:text-red-500">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </div>
                    <p class="notification-message text-sm text-gray-600 dark:text-gray-300 mt-1">{{ $notification->message }}</p>
                    <p class="notification-time text-xs text-gray-500 dark:text-gray-400 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @if($notification->link)
                <a href="{{ $notification->link }}" class="block mt-2 text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    Xem chi tiết <i class="fas fa-arrow-right ml-1"></i>
                </a>
            @endif
        </div>
    @endforeach

    <!-- Pagination -->
    @if($notifications->hasPages())
        <div class="pagination px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            {{ $notifications->links() }}
        </div>
    @endif
@else
    <div class="p-8 text-center">
        <i class="fas fa-bell-slash text-4xl text-gray-400 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-600 mb-2">Không có thông báo</h3>
        <p class="text-gray-500">Bạn chưa có thông báo nào.</p>
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