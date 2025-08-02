@extends('layouts.app')

@section('title', 'Tạo mới phiếu đề xuất triển khai dự án - SGL')

@section('content')
<div class="container-fluid px-6 py-4">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Tạo mới phiếu đề xuất triển khai dự án</h1>
            <div class="mt-1">
                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">Mẫu REQ-PRJ</span>
                </div>
            </div>
            <div class="flex space-x-2">
            <a href="{{ route('requests.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-times mr-2"></i> Hủy
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
        
    <form action="{{ route('requests.project.store') }}" method="POST" class="bg-white rounded-xl shadow-md p-6">
                @csrf
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin đề xuất</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="request_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày đề xuất</label>
                    <input type="date" name="request_date" id="request_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('request_date', date('Y-m-d')) }}">
                </div>
                <div>
                    <label for="proposer_id" class="block text-sm font-medium text-gray-700 mb-1 required" id="proposer_label">Nhân viên đề xuất</label>
                    <select name="proposer_id" id="proposer_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Chọn nhân viên --</option>
                        @foreach($employees ?? [] as $employee)
                            <option value="{{ $employee->id }}" {{ (old('proposer_id') == $employee->id || (isset($currentEmployee) && $currentEmployee->id == $employee->id)) ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                        </div>
                        <div>
                    <label for="implementer_id" class="block text-sm font-medium text-gray-700 mb-1" id="implementer_label">Nhân viên thực hiện</label>
                    <select name="implementer_id" id="implementer_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Chọn nhân viên --</option>
                        @foreach($employees ?? [] as $employee)
                            <option value="{{ $employee->id }}" {{ old('implementer_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin dự án</h2>
                    <div class="mb-3 p-3 bg-blue-50 rounded-lg border border-blue-200 text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-1"></i> 
                        <strong>Lưu ý:</strong> Chỉ hiển thị các dự án và phiếu cho thuê còn hiệu lực bảo hành. 
                        Các dự án/phiếu cho thuê đã hết hạn bảo hành sẽ không được hiển thị trong danh sách này.
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="project_id" class="block text-sm font-medium text-gray-700 mb-1 required">Dự án / Phiếu cho thuê</label>
                            <select name="project_id" id="project_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Chọn dự án / phiếu cho thuê --</option>
                                <optgroup label="Dự án">
                                @foreach($projects as $project)
                                        <option value="project_{{ $project->id }}" 
                                                data-type="project"
                                            data-customer-id="{{ $project->customer->id }}"
                                            data-customer-name="{{ $project->customer->name }}"
                                            data-customer-phone="{{ $project->customer->phone }}"
                                            data-customer-email="{{ $project->customer->email }}" 
                                            data-customer-address="{{ $project->customer->address }}"
                                                data-project-name="{{ $project->project_name }}"
                                                data-project-address="{{ $project->project_address ?? '' }}"
                                                {{ old('project_id') == 'project_' . $project->id ? 'selected' : '' }}>
                                        {{ $project->project_name }} ({{ $project->project_code }})
                                    </option>
                                @endforeach
                                </optgroup>
                                <optgroup label="Phiếu cho thuê">
                                    @foreach($rentals as $rental)
                                        <option value="rental_{{ $rental->id }}" 
                                                data-type="rental"
                                                data-customer-id="{{ $rental->customer->id }}"
                                                data-customer-name="{{ $rental->customer->name }}"
                                                data-customer-phone="{{ $rental->customer->phone }}"
                                                data-customer-email="{{ $rental->customer->email }}" 
                                                data-customer-address="{{ $rental->customer->address }}"
                                                data-project-name="{{ $rental->rental_name }}"
                                                data-project-address="{{ $rental->rental_address ?? '' }}"
                                                {{ old('project_id') == 'rental_' . $rental->id ? 'selected' : '' }}>
                                            {{ $rental->rental_name }} ({{ $rental->rental_code }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                            <input type="hidden" name="project_name" id="project_name">
                            <input type="hidden" name="project_type" id="project_type">
                        </div>
                        <div>
                    <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1">Đối tác</label>
                    <select name="customer_id" id="customer_id" readonly disabled class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 cursor-not-allowed">
                        <option value="">-- Đối tác sẽ được tự động điền --</option>
                    </select>
                        </div>
                        <div class="md:col-span-2">
                            <label for="project_address" class="block text-sm font-medium text-gray-700 mb-1 required">Địa chỉ dự án</label>
                    <input type="text" name="project_address" id="project_address" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('project_address') }}">
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
                            <!-- Thêm các trường ẩn để lưu dữ liệu -->
                            <input type="hidden" name="customer_name" id="customer_name">
                            <input type="hidden" name="customer_phone" id="customer_phone">
                            <input type="hidden" name="customer_email" id="customer_email">
                            <input type="hidden" name="customer_address" id="customer_address">
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Phương thức xử lý khi duyệt</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center">
                        <input type="radio" name="approval_method" id="production" value="production" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" {{ old('approval_method', 'production') == 'production' ? 'checked' : '' }}>
                                <label for="production" class="ml-2 block text-sm font-medium text-gray-700">Sản xuất lắp ráp</label>
                            </div>
                            <div class="flex items-center">
                        <input type="radio" name="approval_method" id="warehouse" value="warehouse" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" {{ old('approval_method') == 'warehouse' ? 'checked' : '' }}>
                                <label for="warehouse" class="ml-2 block text-sm font-medium text-gray-700">Xuất kho</label>
                            </div>
                        </div>
                        <div class="col-span-2 mt-2">
                            <div id="production_info" class="p-3 bg-blue-50 rounded-lg border border-blue-200 text-sm text-blue-700 {{ old('approval_method', 'production') == 'production' ? 'block' : 'hidden' }}">
                                <i class="fas fa-info-circle mr-1"></i> Khi chọn <strong>Sản xuất lắp ráp</strong>, hệ thống sẽ gửi thông báo đến nhân viên thực hiện để tạo phiếu lắp ráp sau khi phiếu được duyệt.
                            </div>
                            <div id="warehouse_info" class="p-3 bg-green-50 rounded-lg border border-green-200 text-sm text-green-700 {{ old('approval_method') == 'warehouse' ? 'block' : 'hidden' }}">
                                <i class="fas fa-info-circle mr-1"></i> Khi chọn <strong>Xuất kho</strong>, hệ thống sẽ gửi thông báo đến nhân viên thực hiện để tạo phiếu xuất kho sau khi phiếu được duyệt.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Danh mục đề xuất</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="flex items-center" id="equipment_radio">
                    <input type="radio" name="item_type" id="equipment_type" value="equipment" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" {{ old('item_type', 'equipment') == 'equipment' ? 'checked' : '' }}>
                    <label for="equipment_type" class="ml-2 block text-sm font-medium text-gray-700">Thành phẩm</label>
                </div>
                <div class="flex items-center" id="material_radio" style="display:none;">
                    <input type="radio" name="item_type" id="material_type" value="material" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" {{ old('item_type') == 'material' ? 'checked' : '' }}>
                    <label for="material_type" class="ml-2 block text-sm font-medium text-gray-700">Vật tư</label>
                </div>
                <div class="flex items-center" id="good_radio" style="display:none;">
                    <input type="radio" name="item_type" id="good_type" value="good" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" {{ old('item_type') == 'good' ? 'checked' : '' }}>
                    <label for="good_type" class="ml-2 block text-sm font-medium text-gray-700">Hàng hóa</label>
                </div>
            </div>
            
            <div id="equipment_section" class="item-section">
                    <div class="flex justify-between items-center mb-3">
                    <h3 class="text-md font-medium text-gray-800">Thiết bị đề xuất</h3>
                        <button type="button" id="add_equipment" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                            <i class="fas fa-plus-circle mr-1"></i> Thêm thiết bị
                        </button>
                    </div>
                    
                    <div id="equipment_container">
                        <div class="equipment-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3">
                            <div class="md:col-span-3">
                            <label for="equipment_id_0" class="block text-sm font-medium text-gray-700 mb-1 required">Thiết bị</label>
                            <select name="equipment[0][id]" id="equipment_id_0" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field equipment-select">
                                <option value="">-- Chọn thiết bị --</option>
                                @foreach($equipments ?? [] as $equipment)
                                    <option value="{{ $equipment->id }}" {{ old('equipment.0.id') == $equipment->id ? 'selected' : '' }}>
                                        {{ $equipment->name }} ({{ $equipment->code }})
                                    </option>
                                @endforeach
                            </select>
                            </div>
                            <div class="md:col-span-1">
                                <label for="equipment_quantity_0" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                            <input type="number" name="equipment[0][quantity]" id="equipment_quantity_0" required min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field equipment-quantity" value="{{ old('equipment.0.quantity', 1) }}">
                            </div>
                            <div class="md:col-span-1 flex items-end">
                                <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group invisible">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
            <div id="material_section" class="item-section hidden">
                    <div class="flex justify-between items-center mb-3">
                    <h3 class="text-md font-medium text-gray-800">Vật tư đề xuất</h3>
                        <button type="button" id="add_material" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                            <i class="fas fa-plus-circle mr-1"></i> Thêm vật tư
                        </button>
                    </div>
                    
                    <div id="material_container">
                        <div class="material-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3">
                            <div class="md:col-span-3">
                            <label for="material_id_0" class="block text-sm font-medium text-gray-700 mb-1 required">Vật tư</label>
                            <select name="material[0][id]" id="material_id_0" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field">
                                <option value="">-- Chọn vật tư --</option>
                                @foreach($materials ?? [] as $material)
                                    <option value="{{ $material->id }}" {{ old('material.0.id') == $material->id ? 'selected' : '' }}>
                                        {{ $material->name }} ({{ $material->code }})
                                    </option>
                                @endforeach
                            </select>
                            </div>
                            <div class="md:col-span-1">
                                <label for="material_quantity_0" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                            <input type="number" name="material[0][quantity]" id="material_quantity_0" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field" value="{{ old('material.0.quantity', 1) }}">
                            </div>
                            <div class="md:col-span-1 flex items-end">
                                <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group invisible">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="good_section" class="item-section hidden">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-md font-medium text-gray-800">Hàng hóa đề xuất</h3>
                    <button type="button" id="add_good" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                        <i class="fas fa-plus-circle mr-1"></i> Thêm hàng hóa
                    </button>
                </div>
                
                <div id="good_container">
                    <div class="good-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3">
                        <div class="md:col-span-3">
                            <label for="good_id_0" class="block text-sm font-medium text-gray-700 mb-1 required">Hàng hóa</label>
                            <select name="good[0][id]" id="good_id_0" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field">
                                <option value="">-- Chọn hàng hóa --</option>
                                @foreach($goods ?? [] as $good)
                                    <option value="{{ $good->id }}" {{ old('good.0.id') == $good->id ? 'selected' : '' }}>
                                        {{ $good->name }} ({{ $good->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-1">
                            <label for="good_quantity_0" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                            <input type="number" name="good[0][quantity]" id="good_quantity_0" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field" value="{{ old('good.0.quantity', 1) }}">
                        </div>
                        <div class="md:col-span-1 flex items-end">
                            <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group invisible">
                                <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                            </button>
                        </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
            <textarea name="notes" id="notes" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center">
                        <i class="fas fa-save mr-2"></i> Tạo phiếu
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

@section('scripts')
    <script>
    // Xử lý hiển thị section theo loại item được chọn
    document.querySelectorAll('input[name="item_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            // Ẩn tất cả các section
            document.querySelectorAll('.item-section').forEach(section => {
                section.classList.add('hidden');
                
                // Tắt required cho các trường trong section ẩn
                section.querySelectorAll('[required]').forEach(field => {
                    field.removeAttribute('required');
                });
            });
            
            // Hiển thị section tương ứng
            const selectedType = this.value;
            const selectedSection = document.getElementById(selectedType + '_section');
            selectedSection.classList.remove('hidden');
            
            // Bật required cho các trường trong section hiển thị
            selectedSection.querySelectorAll('.required-field').forEach(field => {
                field.setAttribute('required', 'required');
            });
        });
    });
    
    // Xử lý khi chọn dự án
    document.getElementById('project_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const projectType = selectedOption.getAttribute('data-type');
        const projectId = selectedOption.getAttribute('data-project-id'); // Lấy ID của dự án/phiếu cho thuê
        const customerId = selectedOption.getAttribute('data-customer-id');
        const customerName = selectedOption.getAttribute('data-customer-name');
        const customerPhone = selectedOption.getAttribute('data-customer-phone');
        const customerEmail = selectedOption.getAttribute('data-customer-email');
        const customerAddress = selectedOption.getAttribute('data-customer-address');
        const projectName = selectedOption.getAttribute('data-project-name');
        const projectAddress = selectedOption.getAttribute('data-project-address');
        
        // Cập nhật select box khách hàng (disabled)
        const customerSelect = document.getElementById('customer_id');
        customerSelect.innerHTML = `<option value="${customerId}" selected>${customerName}</option>`;
        
        // Cập nhật tên dự án/phiếu cho thuê
        document.getElementById('project_name').value = projectName;
        document.getElementById('project_type').value = projectType;
        
        // Cập nhật các trường thông tin khách hàng
        document.getElementById('customer_name').value = customerName;
        document.getElementById('customer_phone').value = customerPhone;
        document.getElementById('customer_email').value = customerEmail;
        document.getElementById('customer_address').value = customerAddress;
        
        // Hiển thị thông tin trong div
        document.getElementById('customer_name_display').textContent = customerName;
        document.getElementById('customer_phone_display').textContent = customerPhone;
        document.getElementById('customer_email_display').textContent = customerEmail;
        document.getElementById('customer_address_display').textContent = customerAddress;
        
        // Hiển thị div thông tin
        document.getElementById('customer_details').classList.remove('hidden');
    });
    
    // Xử lý khi chọn khách hàng trực tiếp
    document.getElementById('customer_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            // Lấy thông tin từ data attributes của option được chọn
            const name = selectedOption.getAttribute('data-name');
            const phone = selectedOption.getAttribute('data-phone');
            const email = selectedOption.getAttribute('data-email');
            const address = selectedOption.getAttribute('data-address');
            
            // Hiển thị thông tin
            document.getElementById('customer_name_display').textContent = name || 'N/A';
            document.getElementById('customer_phone_display').textContent = phone || 'N/A';
            document.getElementById('customer_email_display').textContent = email || 'N/A';
            document.getElementById('customer_address_display').textContent = address || 'N/A';
            
            // Cập nhật giá trị cho các trường ẩn
            document.getElementById('customer_name').value = name || '';
            document.getElementById('customer_phone').value = phone || '';
            document.getElementById('customer_email').value = email || '';
            document.getElementById('customer_address').value = address || '';
            
            // Hiển thị div thông tin
            document.getElementById('customer_details').classList.remove('hidden');
        } else {
            // Ẩn div thông tin nếu không có đối tác nào được chọn
            document.getElementById('customer_details').classList.add('hidden');
        }
    });
    
    // Kiểm tra nếu đã có dự án được chọn khi tải trang
    document.addEventListener('DOMContentLoaded', function() {
        const projectSelect = document.getElementById('project_id');
        if (projectSelect.value) {
            // Kích hoạt sự kiện change để hiển thị thông tin
            const event = new Event('change');
            projectSelect.dispatchEvent(event);
        }
    });
    
    // Xử lý hiển thị thông tin phương thức xử lý
    document.querySelectorAll('input[name="approval_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            // Ẩn tất cả các thông tin
            document.getElementById('production_info').classList.add('hidden');
            document.getElementById('warehouse_info').classList.add('hidden');
            
            // Hiển thị thông tin tương ứng
            if (this.value === 'production') {
                document.getElementById('production_info').classList.remove('hidden');
                // Chỉ hiển thị radio "Thành phẩm" khi chọn "Sản xuất lắp ráp"
                document.getElementById('equipment_radio').style.display = 'flex';
                document.getElementById('material_radio').style.display = 'none';
                document.getElementById('good_radio').style.display = 'none';
                // Tự động chọn "Thành phẩm"
                document.getElementById('equipment_type').checked = true;
                // Kích hoạt sự kiện change để hiển thị section thành phẩm
                document.getElementById('equipment_type').dispatchEvent(new Event('change'));
            } else if (this.value === 'warehouse') {
                document.getElementById('warehouse_info').classList.remove('hidden');
                // Hiển thị đầy đủ 3 radio khi chọn "Xuất kho"
                document.getElementById('equipment_radio').style.display = 'flex';
                document.getElementById('material_radio').style.display = 'flex';
                document.getElementById('good_radio').style.display = 'flex';
            }
        });
    });
    
    // Khởi tạo trạng thái ban đầu
    document.addEventListener('DOMContentLoaded', function() {
        const productionRadio = document.getElementById('production');
        const warehouseRadio = document.getElementById('warehouse');
        
        if (productionRadio.checked) {
            // Nếu mặc định chọn "Sản xuất lắp ráp"
            document.getElementById('equipment_radio').style.display = 'flex';
            document.getElementById('material_radio').style.display = 'none';
            document.getElementById('good_radio').style.display = 'none';
            document.getElementById('equipment_type').checked = true;
        } else if (warehouseRadio.checked) {
            // Nếu mặc định chọn "Xuất kho"
            document.getElementById('equipment_radio').style.display = 'flex';
            document.getElementById('material_radio').style.display = 'flex';
            document.getElementById('good_radio').style.display = 'flex';
        }
    });
    
    // Kiểm tra nếu đã có đối tác được chọn khi tải trang
    document.addEventListener('DOMContentLoaded', function() {
        const customerSelect = document.getElementById('customer_id');
        if (customerSelect.value) {
            // Kích hoạt sự kiện change để hiển thị thông tin
            const event = new Event('change');
            customerSelect.dispatchEvent(event);
        }
        
        // Hiển thị section mặc định khi tải trang
        const selectedType = document.querySelector('input[name="item_type"]:checked').value;
        const selectedSection = document.getElementById(selectedType + '_section');
        selectedSection.classList.remove('hidden');
        
        // Tắt required cho các trường trong section ẩn
        document.querySelectorAll('.item-section.hidden').forEach(section => {
            section.querySelectorAll('[required]').forEach(field => {
                field.removeAttribute('required');
            });
        });
        
        // Đánh dấu các trường cần required
        document.querySelectorAll('.item-section').forEach(section => {
            section.querySelectorAll('select, input').forEach(field => {
                if (field.hasAttribute('required')) {
                    field.classList.add('required-field');
                }
            });
        });
    });

    // Debug form submission
    document.querySelector('form').addEventListener('submit', function(e) {
        // Kiểm tra trước khi submit
        const selectedType = document.querySelector('input[name="item_type"]:checked').value;
        const selectedSection = document.getElementById(selectedType + '_section');
        const requiredFields = selectedSection.querySelectorAll('[required]');
        
        let hasError = false;
        requiredFields.forEach(field => {
            if (!field.value) {
                hasError = true;
                field.classList.add('border-red-500');
                
                // Hiển thị thông báo lỗi
                const errorMsg = document.createElement('p');
                errorMsg.className = 'text-red-500 text-sm mt-1';
                errorMsg.textContent = 'Trường này là bắt buộc';
                
                // Xóa thông báo lỗi cũ nếu có
                const existingError = field.parentNode.querySelector('.text-red-500');
                if (existingError) {
                    existingError.remove();
                }
                
                field.parentNode.appendChild(errorMsg);
            }
        });
        
        if (hasError) {
            e.preventDefault();
            alert('Vui lòng điền đầy đủ thông tin trong form');
        }
    });
    
        // Thêm thiết bị
        let equipmentCount = 1;
        document.getElementById('add_equipment').addEventListener('click', function() {
            const container = document.getElementById('equipment_container');
            const newRow = document.createElement('div');
            newRow.className = 'equipment-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3';
        
        // Lấy danh sách thiết bị từ select đầu tiên
        const firstSelect = document.getElementById('equipment_id_0');
        let optionsHtml = '';
        
        Array.from(firstSelect.options).forEach(option => {
            optionsHtml += `<option value="${option.value}">${option.text}</option>`;
        });
        
            newRow.innerHTML = `
                <div class="md:col-span-3">
                <label for="equipment_id_${equipmentCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Thiết bị</label>
                <select name="equipment[${equipmentCount}][id]" id="equipment_id_${equipmentCount}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field">
                    ${optionsHtml}
                </select>
                </div>
                <div class="md:col-span-1">
                    <label for="equipment_quantity_${equipmentCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                <input type="number" name="equipment[${equipmentCount}][quantity]" id="equipment_quantity_${equipmentCount}" min="1" value="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field">
                </div>
                <div class="md:col-span-1 flex items-end">
                    <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                        <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            equipmentCount++;
        
        // Nếu section này đang hiển thị, thêm required cho các trường mới
        if (!document.getElementById('equipment_section').classList.contains('hidden')) {
            newRow.querySelectorAll('.required-field').forEach(field => {
                field.setAttribute('required', 'required');
            });
        }
            
            addRemoveRowEventListeners();
        });
        
        // Thêm vật tư
        let materialCount = 1;
        document.getElementById('add_material').addEventListener('click', function() {
            const container = document.getElementById('material_container');
            const newRow = document.createElement('div');
            newRow.className = 'material-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3';
        
        // Lấy danh sách vật tư từ select đầu tiên
        const firstSelect = document.getElementById('material_id_0');
        let optionsHtml = '';
        
        Array.from(firstSelect.options).forEach(option => {
            optionsHtml += `<option value="${option.value}">${option.text}</option>`;
        });
        
            newRow.innerHTML = `
                <div class="md:col-span-3">
                <label for="material_id_${materialCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Vật tư</label>
                <select name="material[${materialCount}][id]" id="material_id_${materialCount}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field">
                    ${optionsHtml}
                </select>
                </div>
                <div class="md:col-span-1">
                    <label for="material_quantity_${materialCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                <input type="number" name="material[${materialCount}][quantity]" id="material_quantity_${materialCount}" min="1" value="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field">
                </div>
                <div class="md:col-span-1 flex items-end">
                    <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                        <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            materialCount++;
        
        // Nếu section này đang hiển thị, thêm required cho các trường mới
        if (!document.getElementById('material_section').classList.contains('hidden')) {
            newRow.querySelectorAll('.required-field').forEach(field => {
                field.setAttribute('required', 'required');
            });
        }
            
            addRemoveRowEventListeners();
        });
        
    // Thêm hàng hóa
    let goodCount = 1;
    document.getElementById('add_good').addEventListener('click', function() {
        const container = document.getElementById('good_container');
        const newRow = document.createElement('div');
        newRow.className = 'good-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3';
        
        // Lấy danh sách hàng hóa từ select đầu tiên
        const firstSelect = document.getElementById('good_id_0');
        let optionsHtml = '';
        
        Array.from(firstSelect.options).forEach(option => {
            optionsHtml += `<option value="${option.value}">${option.text}</option>`;
        });
        
        newRow.innerHTML = `
            <div class="md:col-span-3">
                <label for="good_id_${goodCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Hàng hóa</label>
                <select name="good[${goodCount}][id]" id="good_id_${goodCount}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field">
                    ${optionsHtml}
                </select>
            </div>
            <div class="md:col-span-1">
                <label for="good_quantity_${goodCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                <input type="number" name="good[${goodCount}][quantity]" id="good_quantity_${goodCount}" min="1" value="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field">
            </div>
            <div class="md:col-span-1 flex items-end">
                <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                </button>
            </div>
        `;
        container.appendChild(newRow);
        goodCount++;
        
        // Nếu section này đang hiển thị, thêm required cho các trường mới
        if (!document.getElementById('good_section').classList.contains('hidden')) {
            newRow.querySelectorAll('.required-field').forEach(field => {
                field.setAttribute('required', 'required');
            });
        }
        
        addRemoveRowEventListeners();
    });
    
    // Xóa hàng
        function addRemoveRowEventListeners() {
            document.querySelectorAll('.remove-row').forEach(button => {
                button.addEventListener('click', function() {
                this.closest('.equipment-row, .material-row, .good-row').remove();
                });
            });
        }

    // Xử lý khi chọn thiết bị
    function handleEquipmentChange(selectElement) {
        const equipmentId = selectElement.value;
        const equipmentRow = selectElement.closest('.equipment-row');
        const quantityInput = equipmentRow.querySelector('.equipment-quantity');
        
        if (equipmentId) {
            // Lấy danh sách vật tư của thiết bị
            fetch(`/assemblies/product-materials/${equipmentId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.materials && data.materials.length > 0) {
                        // Lấy số lượng thiết bị
                        const equipmentQuantity = parseInt(quantityInput.value) || 1;
                        
                        // Thêm các vật tư vào danh sách
                        data.materials.forEach(material => {
                            // Tính số lượng vật tư dựa trên số lượng thiết bị
                            const materialQuantity = material.quantity * equipmentQuantity;
                            
                            // Tìm vật tư trong danh sách hiện tại
                            const existingMaterial = document.querySelector(`select[name^="material["] option[value="${material.id}"]:checked`);
                            
                            if (!existingMaterial) {
                                // Thêm vật tư mới
                                const addMaterialBtn = document.getElementById('add_material');
                                addMaterialBtn.click();
                                
                                // Lấy row vật tư vừa thêm
                                const materialRows = document.querySelectorAll('.material-row');
                                const lastMaterialRow = materialRows[materialRows.length - 1];
                                
                                // Chọn vật tư và cập nhật số lượng
                                const materialSelect = lastMaterialRow.querySelector('select');
                                const materialQuantityInput = lastMaterialRow.querySelector('input[type="number"]');
                                
                                materialSelect.value = material.id;
                                materialQuantityInput.value = materialQuantity;
                            } else {
                                // Cập nhật số lượng vật tư hiện có
                                const materialRow = existingMaterial.closest('.material-row');
                                const materialQuantityInput = materialRow.querySelector('input[type="number"]');
                                materialQuantityInput.value = parseInt(materialQuantityInput.value) + materialQuantity;
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading materials:', error);
                });
        }
    }
    
    // Thêm sự kiện change cho thiết bị
    document.addEventListener('change', function(e) {
        if (e.target.matches('.equipment-select')) {
            handleEquipmentChange(e.target);
        }
    });
    
    // Thêm sự kiện change cho số lượng thiết bị
    document.addEventListener('change', function(e) {
        if (e.target.matches('.equipment-quantity')) {
            const equipmentRow = e.target.closest('.equipment-row');
            const equipmentSelect = equipmentRow.querySelector('.equipment-select');
            handleEquipmentChange(equipmentSelect);
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        function updateLabels() {
            var prod = document.getElementById('production');
            var proposerLabel = document.getElementById('proposer_label');
            var implementerLabel = document.getElementById('implementer_label');
            
            if (prod.checked) {
                proposerLabel.innerHTML = 'Người phụ trách lắp ráp <span class="text-danger">*</span>';
                implementerLabel.innerHTML = 'Người tiếp nhận kiểm thử <span class="text-danger">*</span>';
                document.getElementById('implementer_id').setAttribute('required', 'required');
            } else {
                proposerLabel.innerHTML = 'Người tạo phiếu <span class="text-danger">*</span>';
                implementerLabel.innerHTML = 'Người nhận phiếu';
                document.getElementById('implementer_id').removeAttribute('required');
            }
        }
        
        document.getElementById('production').addEventListener('change', updateLabels);
        document.getElementById('warehouse').addEventListener('change', updateLabels);
        updateLabels();
    });

    // Kiểm tra tồn kho khi chọn item
    function checkStock(itemType, itemId, selectElement) {
        if (!itemId) return;
        
        fetch(`/api/check-stock/${itemType}/${itemId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.has_stock) {
                        // Hiển thị thông tin tồn kho
                        const stockInfo = data.warehouses.map(w => 
                            `${w.warehouse_name}: ${w.quantity}`
                        ).join(', ');
                        
                        // Tạo thông báo thành công
                        const successMsg = `✅ Đã chọn thành công: ${data.item_name} (${data.item_code})\n📦 Tổng tồn kho: ${data.total_stock}\n🏢 Kho: ${stockInfo}`;
                        
                        // Hiển thị thông báo
                        showNotification(successMsg, 'success');
                        
                        // Thêm class thành công cho select
                        selectElement.classList.add('border-green-500');
                        selectElement.classList.remove('border-red-500');
                    } else {
                        // Thông báo không đủ tồn kho
                        const errorMsg = `❌ Không đủ tồn kho cho: ${data.item_name} (${data.item_code})\n📦 Tổng tồn kho: ${data.total_stock}`;
                        
                        showNotification(errorMsg, 'error');
                        
                        // Reset select và thêm class lỗi
                        selectElement.value = '';
                        selectElement.classList.add('border-red-500');
                        selectElement.classList.remove('border-green-500');
                    }
                } else {
                    showNotification('❌ Lỗi khi kiểm tra tồn kho', 'error');
                }
            })
            .catch(error => {
                console.error('Error checking stock:', error);
                showNotification('❌ Lỗi khi kiểm tra tồn kho', 'error');
            });
    }
    
    // Hiển thị thông báo
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Tự động ẩn sau 5 giây
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
    
    // Thêm event listener cho các select items
    document.addEventListener('change', function(e) {
        if (e.target.matches('select[name*="[id]"]')) {
            const itemType = getItemTypeFromSelect(e.target);
            const itemId = e.target.value;
            
            if (itemId) {
                checkStock(itemType, itemId, e.target);
            }
        }
    });
    
    // Lấy item type từ select
    function getItemTypeFromSelect(selectElement) {
        const name = selectElement.name;
        if (name.includes('equipment')) return 'product';
        if (name.includes('material')) return 'material';
        if (name.includes('good')) return 'good';
        return 'product';
    }
    </script>
@endsection
@endsection 