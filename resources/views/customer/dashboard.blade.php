@extends('layouts.app')

@section('title', 'Trang chủ khách hàng - SGL')

@section('content')
<div class="container-fluid px-6 py-4">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Trang chủ khách hàng</h1>
            <div class="mt-1 text-sm text-gray-500">
                Xin chào, {{ $customer->company_name ?? $customer->name }}
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Phiếu yêu cầu bảo trì -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Yêu cầu bảo trì</h2>
                <a href="{{ route('customer-maintenance.create') }}" class="text-blue-500 hover:text-blue-600">
                    <i class="fas fa-plus"></i>
                </a>
            </div>
            <p class="text-sm text-gray-600 mb-4">
                Tạo phiếu yêu cầu bảo trì cho thiết bị của bạn
            </p>
            <a href="{{ route('customer-maintenance.create') }}" class="inline-flex items-center text-sm text-blue-500 hover:text-blue-600">
                Tạo phiếu mới
                <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <!-- Thông tin tài khoản -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Thông tin tài khoản</h2>
                <a href="{{ route('profile') }}" class="text-blue-500 hover:text-blue-600">
                    <i class="fas fa-user"></i>
                </a>
            </div>
            <div class="space-y-2">
                <p class="text-sm text-gray-600">
                    <span class="font-medium">Tên công ty:</span> {{ $customer->company_name }}
                </p>
                <p class="text-sm text-gray-600">
                    <span class="font-medium">Email:</span> {{ $customer->email }}
                </p>
                <p class="text-sm text-gray-600">
                    <span class="font-medium">Số điện thoại:</span> {{ $customer->phone }}
                </p>
            </div>
            <a href="{{ route('profile') }}" class="inline-flex items-center mt-4 text-sm text-blue-500 hover:text-blue-600">
                Xem chi tiết
                <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <!-- Danh sách phiếu yêu cầu bảo trì đã tạo -->
    <div class="mt-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Phiếu yêu cầu bảo trì của bạn</h2>
        
        @php
            $maintenanceRequests = \App\Models\CustomerMaintenanceRequest::where('customer_id', $customer->id)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        @endphp
        
        @if($maintenanceRequests->count() > 0)
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã phiếu</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên dự án</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày tạo</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($maintenanceRequests as $request)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $request->request_code }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $request->project_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $request->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($request->status == 'pending')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Chờ duyệt
                                            </span>
                                        @elseif($request->status == 'approved')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Đã duyệt
                                            </span>
                                        @elseif($request->status == 'rejected')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Từ chối
                                            </span>
                                        @elseif($request->status == 'in_progress')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Đang xử lý
                                            </span>
                                        @elseif($request->status == 'completed')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Hoàn thành
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ $request->status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <a href="{{ route('customer-maintenance.show', $request->id) }}" class="text-blue-600 hover:text-blue-900">Xem chi tiết</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-md p-6 text-center">
                <p class="text-gray-500">Bạn chưa tạo phiếu yêu cầu bảo trì nào.</p>
                <a href="{{ route('customer-maintenance.create') }}" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i> Tạo phiếu mới
                </a>
            </div>
        @endif
    </div>
</div>
@endsection 