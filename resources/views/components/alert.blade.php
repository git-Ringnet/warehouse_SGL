@props(['type' => 'success', 'message'])

@php
    $colors = [
        'success' => 'bg-green-100 border-green-500 text-green-700',
        'error' => 'bg-red-100 border-red-500 text-red-700',
        'warning' => 'bg-yellow-100 border-yellow-500 text-yellow-700',
        'info' => 'bg-blue-100 border-blue-500 text-blue-700',
    ];
    
    $icons = [
        'success' => 'fas fa-check-circle',
        'error' => 'fas fa-exclamation-circle',
        'warning' => 'fas fa-exclamation-triangle',
        'info' => 'fas fa-info-circle',
    ];
    
    $alertId = 'alert-' . uniqid();
@endphp

<div id="{{ $alertId }}" class="{{ $colors[$type] }} border-l-4 p-4 mb-4 mx-6 mt-4 relative transition-all duration-300 ease-in-out">
    <button type="button" onclick="closeAlert('{{ $alertId }}')" class="absolute right-2 top-1/2 transform -translate-y-1/2 hover:bg-opacity-20 hover:bg-gray-500 rounded-full p-1">
        <i class="fas fa-times"></i>
    </button>
    <div class="pr-6 flex items-center">
        <i class="{{ $icons[$type] }} mr-2"></i>
        {!! $message !!}
    </div>
</div>

<script>
    // Tự động ẩn thông báo sau 5 giây
    setTimeout(function() {
        fadeOutAlert('{{ $alertId }}');
    }, 5000);

    // Hàm đóng thông báo với hiệu ứng mờ dần
    function fadeOutAlert(id) {
        const alert = document.getElementById(id);
        if (alert) {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 300);
        }
    }

    // Hàm đóng thông báo khi click nút X
    function closeAlert(id) {
        fadeOutAlert(id);
    }
</script> 