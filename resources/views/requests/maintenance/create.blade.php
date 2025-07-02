@extends('layouts.app')

@section('title', 'Tạo phiếu bảo trì dự án - SGL')

@section('content')
<div class="container-fluid px-6 py-4">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Tạo phiếu bảo trì dự án</h1>
            <div class="mt-1 flex items-center">
                <span class="text-sm text-gray-500">Mẫu REQ-MAINT</span>
            </div>
        </div>
        <div>
            <a href="{{ route('requests.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
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

    <form action="{{ route('requests.maintenance.store') }}" method="POST" class="bg-white rounded-xl shadow-md p-6">
        @csrf
        
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin đề xuất</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="request_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày đề xuất</label>
                    <input type="date" name="request_date" id="request_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('request_date', date('Y-m-d')) }}">
                </div>
                <div>
                    <label for="proposer_id" class="block text-sm font-medium text-gray-700 mb-1 required">Kỹ thuật viên</label>
                    <select name="proposer_id" id="proposer_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Chọn kỹ thuật viên --</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('proposer_id', Auth::id()) == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
        </div>
            </div>
        </div>
        
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin dự án</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="warranty_id" class="block text-sm font-medium text-gray-700 mb-1 required">Dự án bảo hành</label>
                    <select name="warranty_id" id="warranty_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Chọn dự án --</option>
                        @forelse($warranties as $warranty)
                            <option value="{{ $warranty->id }}" 
                                    data-project-id="{{ $warranty->dispatch->project->id ?? '' }}"
                                    data-project-name="{{ $warranty->dispatch->project->project_name ?? $warranty->project_name }}"
                                    data-customer-id="{{ $warranty->dispatch->project->customer->id ?? '' }}"
                                    data-customer-name="{{ $warranty->dispatch->project->customer->company_name ?? $warranty->customer_name }}"
                                    data-customer-phone="{{ $warranty->dispatch->project->customer->phone ?? $warranty->customer_phone }}"
                                    data-customer-email="{{ $warranty->dispatch->project->customer->email ?? $warranty->customer_email }}" 
                                    data-customer-address="{{ $warranty->dispatch->project->customer->address ?? $warranty->customer_address }}"
                                    data-warranty-type="{{ $warranty->warranty_type ?? 'Tiêu chuẩn' }}"
                                    data-warranty-period="{{ $warranty->warranty_period_months ?? '12' }}"
                                    data-warranty-end="{{ $warranty->warranty_end_date }}"
                                    {{ old('warranty_id') == $warranty->id ? 'selected' : '' }}>
                                {{ $warranty->project_name }} - {{ $warranty->warranty_code }} (Hết hạn: {{ \Carbon\Carbon::parse($warranty->warranty_end_date)->format('d/m/Y') }})
                            </option>
                        @empty
                            <option value="" disabled>Không có dự án bảo hành nào đang hoạt động</option>
                        @endforelse
                    </select>
                    @if($warranties->isEmpty())
                        <p class="mt-1 text-sm text-red-600">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            Không tìm thấy dự án bảo hành nào đang hoạt động trong hệ thống.
                        </p>
                    @endif
                </div>
                <div>
                    <label for="project_name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên dự án</label>
                    <input type="text" name="project_name" id="project_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('project_name') }}">
                </div>
                <div id="customer_details" class="md:col-span-2 border border-gray-200 rounded-lg p-4 bg-gray-50 hidden">
                    <h3 class="text-md font-medium text-gray-800 mb-2">Thông tin đối tác</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm text-gray-500">Tên người liên hệ:</span>
                            <p id="customer_name_display" class="font-medium text-gray-700"></p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Số điện thoại:</span>
                            <p id="customer_phone_display" class="font-medium text-gray-700"></p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Email:</span>
                            <p id="customer_email_display" class="font-medium text-gray-700"></p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Địa chỉ:</span>
                            <p id="customer_address_display" class="font-medium text-gray-700"></p>
                        </div>
                    </div>
                    
                    <h3 class="text-md font-medium text-gray-800 mt-4 mb-2">Thông tin bảo hành</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-sm text-gray-500">Loại bảo hành:</span>
                            <p id="warranty_type_display" class="font-medium text-gray-700"></p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Thời gian bảo hành:</span>
                            <p id="warranty_period_display" class="font-medium text-gray-700"></p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Ngày hết hạn:</span>
                            <p id="warranty_end_display" class="font-medium text-gray-700"></p>
                        </div>
                    </div>
                    
                    <!-- Thêm các trường ẩn để lưu dữ liệu -->
                    <input type="hidden" name="customer_id" id="customer_id">
                    <input type="hidden" name="customer_name" id="customer_name">
                    <input type="hidden" name="customer_phone" id="customer_phone">
                    <input type="hidden" name="customer_email" id="customer_email">
                    <input type="hidden" name="customer_address" id="customer_address">
                </div>
            </div>
        </div>
        
        <!-- Danh sách thiết bị trong bảo hành -->
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Thiết bị bảo trì</h2>
            <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 mb-4">
                <p class="text-sm">
                    <i class="fas fa-info-circle mr-1"></i> Các thiết bị trong dự án bảo hành sẽ được tự động thêm vào phiếu bảo trì.
                </p>
            </div>
            <div id="warranty_items_container" class="hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên thiết bị</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã serial</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại</th>
                            </tr>
                        </thead>
                        <tbody id="warranty_items_list" class="bg-white divide-y divide-gray-200">
                            <!-- Danh sách thiết bị sẽ được thêm vào đây -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin bảo trì</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="maintenance_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày bảo trì dự kiến</label>
                    <input type="date" name="maintenance_date" id="maintenance_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('maintenance_date') }}">
                </div>
                <div>
                    <label for="maintenance_type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại bảo trì</label>
                    <select name="maintenance_type" id="maintenance_type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Chọn loại bảo trì --</option>
                        <option value="regular" {{ old('maintenance_type') == 'regular' ? 'selected' : '' }}>Định kỳ</option>
                        <option value="emergency" {{ old('maintenance_type') == 'emergency' ? 'selected' : '' }}>Khẩn cấp</option>
                        <option value="preventive" {{ old('maintenance_type') == 'preventive' ? 'selected' : '' }}>Phòng ngừa</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label for="maintenance_reason" class="block text-sm font-medium text-gray-700 mb-1 required">Lý do bảo trì</label>
                    <textarea name="maintenance_reason" id="maintenance_reason" rows="3" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('maintenance_reason') }}</textarea>
                </div>
            </div>
        </div>
        
        <div class="mb-6">
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
            <textarea name="notes" id="notes" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
        </div>
        
        <div class="flex justify-end space-x-3">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center">
                <i class="fas fa-save mr-2"></i> Lưu phiếu
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

<script>
    // Xử lý khi chọn dự án bảo hành
    document.getElementById('warranty_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (!this.value) {
            // Reset form nếu không chọn dự án
            document.getElementById('customer_details').classList.add('hidden');
            document.getElementById('warranty_items_container').classList.add('hidden');
            return;
        }

        // Lấy thông tin từ data attributes
        const projectName = selectedOption.getAttribute('data-project-name');
        const customerName = selectedOption.getAttribute('data-customer-name');
        const customerPhone = selectedOption.getAttribute('data-customer-phone');
        const customerEmail = selectedOption.getAttribute('data-customer-email');
        const customerAddress = selectedOption.getAttribute('data-customer-address');
        const customerId = selectedOption.getAttribute('data-customer-id');
        const warrantyType = selectedOption.getAttribute('data-warranty-type') || 'Tiêu chuẩn';
        const warrantyPeriod = selectedOption.getAttribute('data-warranty-period') || '12';
        const warrantyEnd = selectedOption.getAttribute('data-warranty-end');
    
        // Cập nhật các trường dự án
        document.getElementById('project_name').value = projectName || '';

        // Cập nhật các trường thông tin khách hàng
        document.getElementById('customer_name').value = customerName || '';
        document.getElementById('customer_phone').value = customerPhone || '';
        document.getElementById('customer_email').value = customerEmail || '';
        document.getElementById('customer_address').value = customerAddress || '';
        
        // Cập nhật customer_id nếu có select box
        const customerIdField = document.getElementById('customer_id');
        if (customerIdField && customerId) {
            customerIdField.value = customerId;
        }
            
        // Hiển thị thông tin trong div
        document.getElementById('customer_name_display').textContent = customerName || 'Không có thông tin';
        document.getElementById('customer_phone_display').textContent = customerPhone || 'Không có thông tin';
        document.getElementById('customer_email_display').textContent = customerEmail || 'Không có thông tin';
        document.getElementById('customer_address_display').textContent = customerAddress || 'Không có thông tin';
            
        // Hiển thị thông tin bảo hành
        document.getElementById('warranty_type_display').textContent = warrantyType;
        document.getElementById('warranty_period_display').textContent = warrantyPeriod + ' tháng';
        document.getElementById('warranty_end_display').textContent = warrantyEnd ? new Date(warrantyEnd).toLocaleDateString('vi-VN') : 'Không có thông tin';
            
            // Hiển thị div thông tin
        document.getElementById('customer_details').classList.remove('hidden');

        // Lấy danh sách thiết bị của dự án
        fetch(`/api/warranty/${this.value}/items`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Xóa các hàng hiện tại trong bảng thiết bị
                    const tbody = document.querySelector('#warranty_items_list');
                    tbody.innerHTML = '';

                    // Hiển thị container thiết bị
                    document.getElementById('warranty_items_container').classList.remove('hidden');

                    // Thêm các thiết bị mới vào bảng
                    if (data.items.length > 0) {
                        data.items.forEach(item => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td class="px-6 py-4">${item.name}</td>
                                <td class="px-6 py-4">${item.serial_number || 'Không có'}</td>
                                <td class="px-6 py-4">${item.type}</td>
                            `;
                            tbody.appendChild(row);
                        });
                    } else {
                        // Hiển thị thông báo không có thiết bị
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                Không tìm thấy thiết bị nào trong dự án bảo hành này
                            </td>
                        `;
                        tbody.appendChild(row);
                    }
        } else {
                    console.error('Error fetching warranty items:', data.message);
        }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });
    
    // Kiểm tra nếu đã có dự án được chọn khi tải trang
    document.addEventListener('DOMContentLoaded', function() {
        const warrantySelect = document.getElementById('warranty_id');
        if (warrantySelect.value) {
            // Kích hoạt sự kiện change để hiển thị thông tin
            const event = new Event('change');
            warrantySelect.dispatchEvent(event);
        }
    });
</script>
@endsection 