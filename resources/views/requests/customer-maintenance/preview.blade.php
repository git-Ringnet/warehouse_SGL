@extends('layouts.app')

@section('title', 'Xem trước phiếu yêu cầu bảo trì - SGL')

@section('content')
<div class="container-fluid px-6 py-4">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Xem trước phiếu yêu cầu bảo trì</h1>
            <div class="mt-1 flex items-center">
                <span class="text-sm text-gray-500">Mã phiếu: {{ $request->request_code }}</span>
                <span class="mx-2 text-gray-400">|</span>
                <span class="text-sm text-gray-500">Ngày tạo: {{ $request->request_date->format('d/m/Y') }}</span>
            </div>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('requests.customer-maintenance.show', $request->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
            
            <a href="#" onclick="window.print(); return false;" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-print mr-2"></i> In PDF
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">PHIẾU YÊU CẦU BẢO TRÌ</h2>
            <p class="text-gray-600">Mã phiếu: {{ $request->request_code }}</p>
        </div>

        <!-- Thông tin khách hàng -->
        <div class="grid grid-cols-2 gap-6 mb-8">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Thông tin khách hàng</h3>
                <table class="w-full">
                    <tr>
                        <td class="py-2 text-gray-600">Tên khách hàng:</td>
                        <td class="py-2 font-medium">{{ $request->customer ? $request->customer->company_name : $request->customer_name }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Số điện thoại:</td>
                        <td class="py-2 font-medium">{{ $request->customer_phone }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Email:</td>
                        <td class="py-2 font-medium">{{ $request->customer_email ?: 'Không có' }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Địa chỉ:</td>
                        <td class="py-2 font-medium">{{ $request->customer_address ?: 'Không có' }}</td>
                    </tr>
                </table>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Thông tin yêu cầu</h3>
                <table class="w-full">
                    <tr>
                        <td class="py-2 text-gray-600">Ngày yêu cầu:</td>
                        <td class="py-2 font-medium">{{ $request->request_date->format('d/m/Y') }}</td>
                    </tr>

                    <tr>
                        <td class="py-2 text-gray-600">Mức độ ưu tiên:</td>
                        <td class="py-2">
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
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2 text-gray-600">Trạng thái:</td>
                        <td class="py-2">
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
                            @endswitch
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Chi tiết yêu cầu -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Chi tiết yêu cầu bảo trì</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="mb-4">
                    <p class="text-gray-600 mb-2">Tên dự án/thiết bị:</p>
                    <p class="font-medium">{{ $request->project_name }}</p>
                </div>
                <div class="mb-4">
                    <p class="text-gray-600 mb-2">Lý do yêu cầu bảo trì:</p>
                    <p class="font-medium whitespace-pre-line">{{ $request->maintenance_reason }}</p>
                </div>
                @if($request->maintenance_details)
                <div class="mb-4">
                    <p class="text-gray-600 mb-2">Chi tiết bảo trì:</p>
                    <p class="font-medium whitespace-pre-line">{{ $request->maintenance_details }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Ghi chú -->
        @if($request->notes)
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Ghi chú bổ sung</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="whitespace-pre-line">{{ $request->notes }}</p>
            </div>
        </div>
        @endif

        <!-- Chữ ký -->
        <div class="grid grid-cols-2 gap-6 mt-12">
            <div class="text-center">
                <p class="font-medium mb-20">Người yêu cầu</p>
                <p>{{ $request->customer ? $request->customer->company_name : $request->customer_name }}</p>
            </div>
            <div class="text-center">
                <p class="font-medium mb-20">Người duyệt</p>
                <p>{{ $request->approvedByUser ? $request->approvedByUser->name : '.........................' }}</p>
            </div>
        </div>
    </div>
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
        .flex.space-x-2 {
            display: none !important;
        }
    }
</style>
@endsection 