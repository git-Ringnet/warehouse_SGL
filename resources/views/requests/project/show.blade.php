@extends('layouts.app')

@section('title', 'Chi tiết phiếu đề xuất triển khai dự án - SGL')

@section('content')
<div class="container-fluid px-6 py-4">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Chi tiết phiếu đề xuất triển khai dự án</h1>
            <div class="mt-1 flex items-center">
                <span class="text-sm text-gray-500">Mã phiếu: {{ $projectRequest->request_code }}</span>
                <span class="mx-2 text-gray-400">|</span>
                <span class="text-sm text-gray-500">Ngày tạo: {{ $projectRequest->request_date->format('d/m/Y') }}</span>
                <span class="mx-2 text-gray-400">|</span>
                <span class="text-sm">
                    @switch($projectRequest->status)
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
            
            <a href="{{ route('requests.project.preview', $projectRequest->id) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-eye mr-2"></i> Xem trước
            </a>
            
            <a href="#" onclick="window.print(); return false;" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-print mr-2"></i> In PDF
            </a>
            
            <form action="{{ route('requests.project.store') }}" method="POST" class="inline-block">
                @csrf
                <input type="hidden" name="copy_from" value="{{ $projectRequest->id }}">
                <button type="submit" class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-copy mr-2"></i> Sao chép
                </button>
            </form>
            
            @if($projectRequest->status === 'pending')
                <a href="{{ route('requests.project.edit', $projectRequest->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
                <form action="{{ route('requests.project.destroy', $projectRequest->id) }}" method="POST" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors" onclick="return confirm('Bạn có chắc chắn muốn xóa phiếu đề xuất này?')">
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
        <!-- Thông tin đề xuất -->
        <div class="bg-white rounded-xl shadow-md p-6 md:col-span-2">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Thông tin đề xuất</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Ngày đề xuất</p>
                    <p class="font-medium">{{ \Carbon\Carbon::parse($projectRequest->request_date)->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Trạng thái</p>
                    <p>
                        @if($projectRequest->status == 'pending')
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">Chờ duyệt</span>
                        @elseif($projectRequest->status == 'approved')
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Đã duyệt</span>
                        @elseif($projectRequest->status == 'rejected')
                            <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Từ chối</span>
                        @elseif($projectRequest->status == 'in_progress')
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Đang thực hiện</span>
                        @elseif($projectRequest->status == 'completed')
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Hoàn thành</span>
                        @elseif($projectRequest->status == 'canceled')
                            <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full">Đã hủy</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Nhân viên đề xuất</p>
                    <p class="font-medium">{{ $projectRequest->proposer->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Nhân viên thực hiện</p>
                    <p class="font-medium">{{ $projectRequest->implementer->name ?? 'Chưa phân công' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Phương thức xử lý</p>
                    <p class="font-medium">
                        @if($projectRequest->approval_method == 'production')
                            <span class="inline-flex items-center">
                                <i class="fas fa-tools mr-1 text-blue-600"></i> Sản xuất lắp ráp
                            </span>
                        @elseif($projectRequest->approval_method == 'warehouse')
                            <span class="inline-flex items-center">
                                <i class="fas fa-warehouse mr-1 text-green-600"></i> Xuất kho
                            </span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Tên dự án</p>
                    <p class="font-medium">{{ $projectRequest->project_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Địa chỉ dự án</p>
                    <p class="font-medium">{{ $projectRequest->project_address }}</p>
                </div>
            </div>

            @if($projectRequest->status == 'approved' || $projectRequest->status == 'in_progress' || $projectRequest->status == 'completed')
                <div class="mt-4">
                    @if($projectRequest->approval_method == 'production')
                        <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="flex items-center">
                                <div class="bg-blue-100 p-2 rounded-full">
                                    <i class="fas fa-tools text-blue-600"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-md font-medium text-blue-800">Phiếu lắp ráp</h3>
                                    @php
                                        $assembly = \App\Models\Assembly::where('notes', 'like', '%phiếu đề xuất dự án #' . $projectRequest->id . '%')->first();
                                    @endphp
                                    
                                    @if(isset($assembly) && $assembly)
                                        <p class="text-sm text-blue-600">Phiếu lắp ráp <a href="{{ route('assemblies.show', $assembly->id) }}" class="font-semibold hover:underline">{{ $assembly->code }}</a> đã được tạo tự động.</p>
                                        
                                        @if($assembly->products && $assembly->products->count() > 0)
                                            <div class="mt-2">
                                                <p class="text-sm font-medium text-blue-700">Sản phẩm trong phiếu lắp ráp:</p>
                                                <ul class="mt-1 list-disc list-inside text-sm text-blue-600 ml-2">
                                                    @foreach($assembly->products as $assemblyProduct)
                                                        <li>{{ $assemblyProduct->product->name }} ({{ $assemblyProduct->product->code }}) - Số lượng: {{ $assemblyProduct->quantity }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                        @else
                                        <p class="text-sm text-blue-600">Nhân viên thực hiện đã nhận được thông báo để tạo phiếu lắp ráp từ phiếu đề xuất này. Vui lòng truy cập vào mục Quản lý lắp ráp để tạo phiếu lắp ráp mới.</p>
                        @endif
                                </div>
                            </div>
                        </div>
                    @elseif($projectRequest->approval_method == 'warehouse')
                        <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                            <div class="flex items-center">
                                <div class="bg-green-100 p-2 rounded-full">
                                    <i class="fas fa-warehouse text-green-600"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-md font-medium text-green-800">Phiếu xuất kho</h3>
                                    <p class="text-sm text-green-600">Nhân viên thực hiện đã nhận được thông báo để tạo phiếu xuất kho từ phiếu đề xuất này. Vui lòng truy cập vào mục Quản lý kho để tạo phiếu xuất kho mới.</p>
                                </div>
                            </div>
                </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Thông tin khách hàng -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Thông tin khách hàng</h2>
            <div>
                <p class="text-sm text-gray-500">Đối tác</p>
                <p class="font-medium">{{ $projectRequest->customer ? $projectRequest->customer->company_name : $projectRequest->customer_name }}</p>
            </div>
            <div class="mt-3">
                <p class="text-sm text-gray-500">Tên người liên hệ</p>
                <p class="font-medium">{{ $projectRequest->customer_name }}</p>
            </div>
            <div class="mt-3">
                <p class="text-sm text-gray-500">Số điện thoại</p>
                <p class="font-medium">{{ $projectRequest->customer_phone }}</p>
            </div>
            <div class="mt-3">
                <p class="text-sm text-gray-500">Email</p>
                <p class="font-medium">{{ $projectRequest->customer_email ?: 'Không có' }}</p>
            </div>
            <div class="mt-3">
                <p class="text-sm text-gray-500">Địa chỉ</p>
                <p class="font-medium">{{ $projectRequest->customer_address }}</p>
            </div>
        </div>
    </div>

    <!-- Danh sách thiết bị và vật tư -->
    <div class="bg-white rounded-xl shadow-md p-6 mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Danh sách sản phẩm đề xuất</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STT</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên sản phẩm</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php $index = 1; @endphp
                    
                    @forelse($projectRequest->items as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $index++ }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($item->item_type == 'equipment')
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Thiết bị</span>
                                @elseif($item->item_type == 'material')
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Vật tư</span>
                                @elseif($item->item_type == 'good')
                                    <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">Hàng hóa</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">Không có sản phẩm nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Ghi chú -->
    <div class="bg-white rounded-xl shadow-md p-6 mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Ghi chú</h2>
        <div class="bg-gray-50 p-4 rounded-lg whitespace-pre-line">
            {{ $projectRequest->notes ?: 'Không có ghi chú' }}
        </div>
    </div>

    <!-- Phần xử lý phiếu đề xuất -->
    <div class="bg-white rounded-xl shadow-md p-6 mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Xử lý phiếu đề xuất</h2>
        
        @if($projectRequest->status === 'pending')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Duyệt phiếu -->
                <div>
                    <h3 class="text-md font-medium text-gray-700 mb-3">Duyệt phiếu đề xuất</h3>
                    <form action="{{ route('requests.project.approve', $projectRequest->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="implementer_id" value="{{ $projectRequest->proposer_id }}">
                        <p class="mb-4 text-gray-700">Người thực hiện: <span class="font-medium">{{ $projectRequest->proposer ? $projectRequest->proposer->name : 'Không có' }}</span></p>
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-check mr-2"></i> Duyệt phiếu
                        </button>
                    </form>
                </div>

                <!-- Từ chối phiếu -->
                <div>
                    <h3 class="text-md font-medium text-gray-700 mb-3">Từ chối phiếu đề xuất</h3>
                    <form action="{{ route('requests.project.reject', $projectRequest->id) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="reject_reason" class="block text-sm font-medium text-gray-700 mb-1">Lý do từ chối</label>
                            <textarea name="reject_reason" id="reject_reason" rows="3" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            @error('reject_reason')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-times mr-2"></i> Từ chối phiếu
                        </button>
                    </form>
                </div>
            </div>
        @elseif($projectRequest->status === 'approved' || $projectRequest->status === 'in_progress')
            <div>
                <h3 class="text-md font-medium text-gray-700 mb-3">Cập nhật trạng thái</h3>
                <form action="{{ route('requests.project.status', $projectRequest->id) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái mới</label>
                            <select name="status" id="status" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="in_progress" {{ $projectRequest->status === 'in_progress' ? 'selected' : '' }}>Đang thực hiện</option>
                                <option value="completed">Hoàn thành</option>
                                <option value="canceled">Đã hủy</option>
                            </select>
                            @error('status')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="status_note" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                            <textarea name="status_note" id="status_note" rows="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            @error('status_note')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                        <i class="fas fa-save mr-2"></i> Cập nhật trạng thái
                    </button>
                </form>
            </div>
        @endif
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
        .flex.space-x-2, .bg-white.rounded-xl.shadow-md.p-6.mt-6:last-child {
            display: none !important;
        }
    }
</style>
@endsection 