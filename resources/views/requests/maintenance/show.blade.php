@extends('layouts.app')

@section('title', 'Chi tiết phiếu bảo trì dự án - SGL')

@section('content')
<div class="container-fluid px-6 py-4">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Chi tiết phiếu bảo trì dự án</h1>
            <div class="mt-1 flex items-center">
                <span class="text-sm text-gray-500">Mã phiếu: {{ $maintenanceRequest->request_code }}</span>
                <span class="mx-2 text-gray-400">|</span>
                <span class="text-sm text-gray-500">Ngày tạo: {{ $maintenanceRequest->request_date->format('d/m/Y') }}</span>
                <span class="mx-2 text-gray-400">|</span>
                <span class="text-sm">
                    @switch($maintenanceRequest->status)
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
            
            <a href="{{ route('requests.maintenance.preview', $maintenanceRequest->id) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-eye mr-2"></i> Xem trước
            </a>
            
            <a href="#" onclick="window.print(); return false;" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-print mr-2"></i> In PDF
            </a>
            
            <form action="{{ route('requests.maintenance.store') }}" method="POST" class="inline-block">
                @csrf
                <input type="hidden" name="copy_from" value="{{ $maintenanceRequest->id }}">
                <button type="submit" class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-copy mr-2"></i> Sao chép
                </button>
            </form>
            
            @if($maintenanceRequest->status === 'pending')
                <a href="{{ route('requests.maintenance.edit', $maintenanceRequest->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
                <form action="{{ route('requests.maintenance.destroy', $maintenanceRequest->id) }}" method="POST" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors" onclick="return confirm('Bạn có chắc chắn muốn xóa phiếu bảo trì này?')">
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
                    <p class="text-sm text-gray-500">Kỹ thuật viên</p>
                    <p class="font-medium">{{ $maintenanceRequest->proposer ? $maintenanceRequest->proposer->name : 'Không có' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Ngày đề xuất</p>
                    <p class="font-medium">{{ $maintenanceRequest->request_date->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Tên dự án</p>
                    <p class="font-medium">{{ $maintenanceRequest->project_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Loại bảo trì</p>
                    <p class="font-medium">
                        @if($maintenanceRequest->maintenance_type === 'regular')
                            Định kỳ
                        @elseif($maintenanceRequest->maintenance_type === 'emergency')
                            Khẩn cấp
                        @elseif($maintenanceRequest->maintenance_type === 'preventive')
                            Phòng ngừa
                        @else
                            Không xác định
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Thông tin khách hàng -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Thông tin khách hàng</h2>
            <div>
                <p class="text-sm text-gray-500">Đối tác</p>
                <p class="font-medium">{{ $maintenanceRequest->customer ? $maintenanceRequest->customer->company_name : $maintenanceRequest->customer_name }}</p>
            </div>
            <div class="mt-3">
                <p class="text-sm text-gray-500">Tên người liên hệ</p>
                <p class="font-medium">{{ $maintenanceRequest->customer_name }}</p>
            </div>
            <div class="mt-3">
                <p class="text-sm text-gray-500">Số điện thoại</p>
                <p class="font-medium">{{ $maintenanceRequest->customer_phone }}</p>
            </div>
            <div class="mt-3">
                <p class="text-sm text-gray-500">Email</p>
                <p class="font-medium">{{ $maintenanceRequest->customer_email ?: 'Không có' }}</p>
            </div>
            <div class="mt-3">
                <p class="text-sm text-gray-500">Địa chỉ</p>
                <p class="font-medium">{{ $maintenanceRequest->customer_address }}</p>
            </div>
        </div>
    </div>

    <!-- Thông tin bảo trì -->
    <div class="bg-white rounded-xl shadow-md p-6 mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Thông tin bảo trì</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Ngày bảo trì dự kiến</p>
                <p class="font-medium">{{ $maintenanceRequest->maintenance_date->format('d/m/Y') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Lý do bảo trì</p>
                <p class="font-medium">{{ $maintenanceRequest->maintenance_reason }}</p>
            </div>
        </div>
    </div>

    <!-- Thông tin bảo hành -->
    @if($maintenanceRequest->warranty)
    <div class="bg-white rounded-xl shadow-md p-6 mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Thông tin dự án bảo hành</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Mã bảo hành</p>
                <p class="font-medium">{{ $maintenanceRequest->warranty->warranty_code }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Loại bảo hành</p>
                <p class="font-medium">{{ $maintenanceRequest->warranty->type ?? 'Tiêu chuẩn' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Thời gian bảo hành</p>
                <p class="font-medium">{{ $maintenanceRequest->warranty->period ?? '12' }} tháng</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Ngày hết hạn</p>
                <p class="font-medium">{{ $maintenanceRequest->warranty->warranty_end_date ? date('d/m/Y', strtotime($maintenanceRequest->warranty->warranty_end_date)) : 'Không có' }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Danh sách thành phẩm -->
    <div class="bg-white rounded-xl shadow-md p-6 mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-3">Thành phẩm cần bảo trì</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Mã thành phẩm
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tên thành phẩm
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Số lượng
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Đơn vị
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Mô tả
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($maintenanceRequest->products as $product)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $product->product_code }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $product->product_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $product->quantity }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $product->unit }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $product->description }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

   

    <!-- Ghi chú -->
    <div class="bg-white rounded-xl shadow-md p-6 mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Ghi chú</h2>
        <div class="bg-gray-50 p-4 rounded-lg whitespace-pre-line">
            {{ $maintenanceRequest->notes ?: 'Không có ghi chú' }}
        </div>
    </div>

    <!-- Phần xử lý phiếu đề xuất -->
    <div class="bg-white rounded-xl shadow-md p-6 mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Xử lý phiếu bảo trì</h2>
        
        @if($maintenanceRequest->status === 'pending')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Duyệt phiếu -->
                <div>
                    <h3 class="text-md font-medium text-gray-700 mb-3">Duyệt phiếu bảo trì</h3>
                    <form action="{{ route('requests.maintenance.approve', $maintenanceRequest->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="implementer_id" value="{{ $maintenanceRequest->proposer_id }}">
                        <p class="mb-4 text-gray-700">Kỹ thuật viên: <span class="font-medium">{{ $maintenanceRequest->proposer ? $maintenanceRequest->proposer->name : 'Không có' }}</span></p>
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-check mr-2"></i> Duyệt phiếu
                        </button>
                    </form>
                </div>

                <!-- Từ chối phiếu -->
                <div>
                    <h3 class="text-md font-medium text-gray-700 mb-3">Từ chối phiếu bảo trì</h3>
                    <form action="{{ route('requests.maintenance.reject', $maintenanceRequest->id) }}" method="POST">
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
        @elseif($maintenanceRequest->status === 'approved' || $maintenanceRequest->status === 'in_progress')
            <div>
                <h3 class="text-md font-medium text-gray-700 mb-3">Cập nhật trạng thái</h3>
                <form action="{{ route('requests.maintenance.status', $maintenanceRequest->id) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái mới</label>
                            <select name="status" id="status" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="in_progress" {{ $maintenanceRequest->status === 'in_progress' ? 'selected' : '' }}>Đang thực hiện</option>
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
                
                <!-- Hiển thị thông tin phiếu sửa chữa đã tạo -->
                @if($maintenanceRequest->repairs && $maintenanceRequest->repairs->count() > 0)
                    <div class="mt-6 border-t border-gray-200 pt-4">
                        <h3 class="text-md font-medium text-gray-700 mb-3">Phiếu sửa chữa & bảo trì đã tạo</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã phiếu</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày tạo</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($maintenanceRequest->repairs as $repair)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $repair->repair_code }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $repair->repair_type_label }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $repair->repair_date->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $repair->status_color }}-100 text-{{ $repair->status_color }}-800">
                                                    {{ $repair->status_label }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('repairs.show', $repair->id) }}" class="text-blue-600 hover:text-blue-900">
                                                    <i class="fas fa-eye"></i> Xem
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
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
            padding: 20px;
            background: white;
        }
        .flex.space-x-2, 
        .bg-white.rounded-xl.shadow-md.p-6.mt-6:last-child,
        .print-controls {
            display: none !important;
        }

        /* Excel-like styling for print */
        .bg-white {
            box-shadow: none !important;
            border: 1px solid #d1d5db !important;
            margin-bottom: 20px !important;
        }
        
        h1 {
            font-size: 24px !important;
            text-align: center !important;
            color: #1e40af !important;
            margin-bottom: 10px !important;
        }
        
        h2 {
            font-size: 16px !important;
            background-color: #e5e7eb !important;
            padding: 5px 10px !important;
            margin-bottom: 10px !important;
        }
        
        table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-bottom: 10px !important;
        }
        
        th, td {
            border: 1px solid #d1d5db !important;
            padding: 8px !important;
            text-align: left !important;
        }
        
        th {
            background-color: #e5e7eb !important;
            font-weight: 500 !important;
        }

        /* Signature section */
        .signature-section {
            margin-top: 50px !important;
            display: flex !important;
            justify-content: space-between !important;
            page-break-inside: avoid !important;
        }
        
        .signature-box {
            flex: 1 !important;
            text-align: center !important;
            padding: 0 20px !important;
        }
        
        .signature-title {
            font-weight: bold !important;
            margin-bottom: 60px !important;
        }
        
        .signature-name {
            font-weight: 500 !important;
        }
    }
</style>

<!-- Add signature section before the last div -->
<div class="signature-section" style="display: none;">
    <div class="signature-box">
        <div class="signature-title">Kỹ thuật viên</div>
        <div class="signature-name">{{ $maintenanceRequest->proposer ? $maintenanceRequest->proposer->name : '' }}</div>
    </div>
    <div class="signature-box">
        <div class="signature-title">Người duyệt</div>
        <div class="signature-name"></div>
    </div>
</div>

@section('scripts')
<script>
    function exportToExcel() {
        // Trong thực tế, sẽ cần một thư viện như SheetJS để xuất Excel đúng định dạng
        alert('Đang tải xuống file Excel...');
        // window.location.href = '/api/requests/maintenance/{{ $maintenanceRequest->id }}/export-excel';
    }
</script>
@endsection
@endsection 