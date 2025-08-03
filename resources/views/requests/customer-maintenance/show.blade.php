@extends('layouts.app')

@section('title', 'Chi tiết phiếu yêu cầu bảo trì - SGL')

@section('content')
<div class="container-fluid px-6 py-4">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Chi tiết phiếu yêu cầu bảo trì</h1>
            <div class="mt-1 flex items-center">
                <span class="text-sm text-gray-500">Mã phiếu: {{ $request->request_code }}</span>
                <span class="mx-2 text-gray-400">|</span>
                <span class="text-sm text-gray-500">Ngày tạo: {{ $request->request_date->format('d/m/Y') }}</span>
                <span class="mx-2 text-gray-400">|</span>
                <span class="text-sm">
                    @switch($request->status)
                        @case('pending')
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Chờ duyệt</span>
                            @break
                        @case('approved')
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Đã duyệt</span>
                            @break
                        @case('rejected')
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Từ chối</span>
                            @break
                        @case('in_progress')
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Đang thực hiện</span>
                            @break
                        @case('completed')
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Hoàn thành</span>
                            @break
                        @case('canceled')
                            <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">Đã hủy</span>
                            @break
                        @default
                            <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">Không xác định</span>
                    @endswitch
                </span>
            </div>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('requests.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
            
            <a href="{{ route('requests.customer-maintenance.preview', $request->id) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-eye mr-2"></i> Xem trước
            </a>
            
            <a href="#" onclick="window.print(); return false;" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-print mr-2"></i> In PDF
            </a>
            
            @if(Auth::guard('customer')->check() && $request->status === 'pending')
                <!-- <a href="{{ route('requests.customer-maintenance.edit', $request->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a> -->
                
                <form action="{{ route('requests.customer-maintenance.destroy', $request->id) }}" method="POST" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors" onclick="return confirm('Bạn có chắc chắn muốn xóa phiếu yêu cầu này?')">
                        <i class="fas fa-trash mr-2"></i> Xóa
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
            <div class="flex">
                <div class="py-1"><i class="fas fa-check-circle text-green-500"></i></div>
                <div class="ml-3">
                    <p class="text-sm">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
            <div class="flex">
                <div class="py-1"><i class="fas fa-exclamation-circle text-red-500"></i></div>
                <div class="ml-3">
                    <p class="text-sm">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Thông tin yêu cầu -->
        <div class="bg-white rounded-xl shadow-md p-6 md:col-span-2">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Thông tin yêu cầu</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Tên dự án/thiết bị</p>
                    <p class="font-medium">{{ $request->project_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Ngày yêu cầu</p>
                    <p class="font-medium">{{ $request->request_date->format('d/m/Y') }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Mức độ ưu tiên</p>
                    <p class="font-medium">
                        @switch($request->priority)
                            @case('low')
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">Thấp</span>
                                @break
                            @case('medium')
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Trung bình</span>
                                @break
                            @case('high')
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Cao</span>
                                @break
                            @case('urgent')
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Khẩn cấp</span>
                                @break
                        @endswitch
                    </p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500">Lý do yêu cầu bảo trì</p>
                    <p class="font-medium whitespace-pre-line">{{ $request->maintenance_reason }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500">Chi tiết bảo trì</p>
                    <p class="font-medium whitespace-pre-line">{{ $request->maintenance_details ?: 'Không có' }}</p>
                </div>
            </div>
        </div>

        <!-- Thông tin khách hàng -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Thông tin khách hàng</h2>
            <div>
                <p class="text-sm text-gray-500">Tên khách hàng</p>
                <p class="font-medium">{{ $request->customer ? $request->customer->company_name : $request->customer_name }}</p>
            </div>
            <div class="mt-3">
                <p class="text-sm text-gray-500">Số điện thoại</p>
                <p class="font-medium">{{ $request->customer_phone ?: 'Không có' }}</p>
            </div>
            <div class="mt-3">
                <p class="text-sm text-gray-500">Email</p>
                <p class="font-medium">{{ $request->customer_email ?: 'Không có' }}</p>
            </div>
            <div class="mt-3">
                <p class="text-sm text-gray-500">Địa chỉ</p>
                <p class="font-medium">{{ $request->customer_address ?: 'Không có' }}</p>
            </div>
        </div>
    </div>

    <!-- Thông tin kiểm duyệt -->
    <div class="bg-white rounded-xl shadow-md p-6 mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Thông tin kiểm duyệt</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Người kiểm duyệt</p>
                <p class="font-medium">{{ $request->approvedByUser ? $request->approvedByUser->name : 'Chưa được duyệt' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Thời gian duyệt</p>
                <p class="font-medium">{{ $request->approved_at ? $request->approved_at->format('d/m/Y H:i:s') : 'Chưa được duyệt' }}</p>
            </div>
            @if($request->status === 'rejected')
            <div class="md:col-span-2">
                <p class="text-sm text-gray-500">Lý do từ chối</p>
                <p class="font-medium text-red-600">{{ $request->rejection_reason }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Ghi chú -->
    <div class="bg-white rounded-xl shadow-md p-6 mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Ghi chú</h2>
        <div class="bg-gray-50 p-4 rounded-lg whitespace-pre-line">
            {{ $request->notes ?: 'Không có ghi chú' }}
        </div>
    </div>

    <!-- Phần xử lý phiếu yêu cầu (chỉ hiển thị cho admin) -->
    @if(Auth::guard('web')->check() && $request->status === 'pending')
    <div class="bg-white rounded-xl shadow-md p-6 mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Xử lý phiếu yêu cầu</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Duyệt phiếu -->
            <div>
                <h3 class="text-md font-medium text-gray-700 mb-3">Duyệt phiếu yêu cầu</h3>
                <form action="{{ route('requests.customer-maintenance.approve', $request->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                        <i class="fas fa-check mr-2"></i> Duyệt phiếu
                    </button>
                </form>
            </div>

            <!-- Từ chối phiếu -->
            <div>
                <h3 class="text-md font-medium text-gray-700 mb-3">Từ chối phiếu yêu cầu</h3>
                <form action="{{ route('requests.customer-maintenance.reject', $request->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-1">Lý do từ chối</label>
                        <textarea name="rejection_reason" id="rejection_reason" rows="3" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                        <i class="fas fa-times mr-2"></i> Từ chối phiếu
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .container-fluid, .container-fluid * {
            visibility: visible;
        }
        .container-fluid {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .flex.space-x-2, .bg-white.rounded-xl.shadow-md.p-6.mt-6:last-child {
            display: none !important;
        }
    }
</style>
@endsection 