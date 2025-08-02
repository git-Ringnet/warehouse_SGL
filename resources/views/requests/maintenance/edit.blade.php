@extends('layouts.app')

@section('title', 'Chỉnh sửa phiếu bảo trì dự án - SGL')

@section('content')
<div class="container-fluid px-6 py-4">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Chỉnh sửa phiếu bảo trì dự án</h1>
            <div class="mt-1 flex items-center">
                <span class="text-sm text-gray-500">Mã phiếu: {{ $maintenanceRequest->request_code }}</span>
            </div>
        </div>
        <div>
            <a href="{{ route('requests.maintenance.show', $maintenanceRequest->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
            <div class="flex">
                <div class="py-1"><i class="fas fa-exclamation-circle text-red-500"></i></div>
                <div class="ml-3">
                    <p class="font-medium">Vui lòng kiểm tra lại các thông tin sau:</p>
                    <ul class="mt-1 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('requests.maintenance.update', $maintenanceRequest->id) }}" method="POST" class="bg-white rounded-xl shadow-md p-6">
        @csrf
        @method('PATCH')
        
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin đề xuất</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="request_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày đề xuất</label>
                    <input type="date" name="request_date" id="request_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('request_date', $maintenanceRequest->request_date->format('Y-m-d')) }}">
                </div>
                <div>
                    <label for="proposer_id" class="block text-sm font-medium text-gray-700 mb-1">Kỹ thuật đề xuất</label>
                    <input type="text" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100" value="{{ $maintenanceRequest->proposer ? $maintenanceRequest->proposer->name : 'Không có' }}">
                    <input type="hidden" name="proposer_id" value="{{ $maintenanceRequest->proposer_id }}">
                </div>
            </div>
        </div>
        
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin dự án bảo trì</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="project_name" class="block text-sm font-medium text-gray-700 mb-1">Tên dự án</label>
                    <input type="text" name="project_name" id="project_name" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 focus:outline-none" value="{{ old('project_name', $maintenanceRequest->project_name) }}">
                </div>
                <div>
                    <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1">Đối tác</label>
                    <input type="text" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100" value="{{ $maintenanceRequest->customer ? $maintenanceRequest->customer->company_name : $maintenanceRequest->customer_name }}">
                    <input type="hidden" name="customer_id" value="{{ $maintenanceRequest->customer_id }}">
                </div>
                <div class="md:col-span-2">
                    <label for="project_address" class="block text-sm font-medium text-gray-700 mb-1 required">Địa chỉ dự án</label>
                    <input type="text" name="project_address" id="project_address" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('project_address', $maintenanceRequest->project_address) }}">
                </div>
            </div>
        </div>
        
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin bảo trì</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="maintenance_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày bảo trì dự kiến</label>
                    <input type="date" name="maintenance_date" id="maintenance_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('maintenance_date', $maintenanceRequest->maintenance_date->format('Y-m-d')) }}">
                </div>
                <div>
                    <label for="maintenance_type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại bảo trì</label>
                    <select name="maintenance_type" id="maintenance_type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="maintenance" {{ old('maintenance_type', $maintenanceRequest->maintenance_type) == 'maintenance' ? 'selected' : '' }}>Bảo trì định kỳ</option>
                        <option value="repair" {{ old('maintenance_type', $maintenanceRequest->maintenance_type) == 'repair' ? 'selected' : '' }}>Sửa chữa lỗi</option>
                        <option value="replacement" {{ old('maintenance_type', $maintenanceRequest->maintenance_type) == 'replacement' ? 'selected' : '' }}>Thay thế linh kiện</option>
                        <option value="upgrade" {{ old('maintenance_type', $maintenanceRequest->maintenance_type) == 'upgrade' ? 'selected' : '' }}>Nâng cấp</option>
                        <option value="other" {{ old('maintenance_type', $maintenanceRequest->maintenance_type) == 'other' ? 'selected' : '' }}>Khác</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label for="maintenance_reason" class="block text-sm font-medium text-gray-700 mb-1 required">Lý do bảo trì</label>
                    <textarea name="maintenance_reason" id="maintenance_reason" rows="3" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('maintenance_reason', $maintenanceRequest->maintenance_reason) }}</textarea>
                </div>
            </div>
        </div>
        
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Thành phẩm cần bảo trì</h2>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500 mb-3 italic">Không thể chỉnh sửa danh sách thành phẩm. Nếu cần thay đổi, vui lòng tạo phiếu bảo trì mới.</p>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STT</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã thành phẩm</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên thành phẩm</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Đơn vị</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mô tả</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php $index = 1; @endphp
                            @forelse($maintenanceRequest->products as $product)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $index++ }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->product_code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->product_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->quantity }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->unit }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $product->description }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">Không có thành phẩm nào</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin liên hệ khách hàng</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên khách hàng</label>
                    <input type="text" name="customer_name" id="customer_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('customer_name', $maintenanceRequest->customer_name) }}">
                </div>
                <div>
                    <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-1 required">Số điện thoại</label>
                    <input type="text" name="customer_phone" id="customer_phone" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('customer_phone', $maintenanceRequest->customer_phone) }}">
                </div>
                <div>
                    <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="customer_email" id="customer_email" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('customer_email', $maintenanceRequest->customer_email) }}">
                </div>
                <div class="md:col-span-3">
                    <label for="customer_address" class="block text-sm font-medium text-gray-700 mb-1 required">Địa chỉ</label>
                    <input type="text" name="customer_address" id="customer_address" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('customer_address', $maintenanceRequest->customer_address) }}">
                </div>
            </div>
        </div>
        
        <div class="mb-6">
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
            <textarea name="notes" id="notes" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes', $maintenanceRequest->notes) }}</textarea>
        </div>
        
        <div class="flex justify-end space-x-3">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center">
                <i class="fas fa-save mr-2"></i> Lưu thay đổi
            </button>
        </div>
    </form>
</div>

<style>
    .required:after {
        content: " *";
        color: red;
    }
</style>
@endsection 