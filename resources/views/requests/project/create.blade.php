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
                    <label for="proposer_id" class="block text-sm font-medium text-gray-700 mb-1 required">Nhân viên đề xuất</label>
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
                    <label for="implementer_id" class="block text-sm font-medium text-gray-700 mb-1">Nhân viên thực hiện</label>
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="project_name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên dự án</label>
                    <input type="text" name="project_name" id="project_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('project_name') }}">
                        </div>
                        <div>
                    <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1 required">Đối tác</label>
                    <select name="customer_id" id="customer_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Chọn đối tác --</option>
                        @foreach($customers ?? [] as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->company_name }} ({{ $customer->name }})
                            </option>
                        @endforeach
                    </select>
                        </div>
                        <div class="md:col-span-2">
                            <label for="project_address" class="block text-sm font-medium text-gray-700 mb-1 required">Địa chỉ dự án</label>
                    <input type="text" name="project_address" id="project_address" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('project_address') }}">
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
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Danh mục đề xuất</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="flex items-center">
                    <input type="radio" name="item_type" id="equipment_type" value="equipment" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" {{ old('item_type', 'equipment') == 'equipment' ? 'checked' : '' }}>
                    <label for="equipment_type" class="ml-2 block text-sm font-medium text-gray-700">Thiết bị</label>
                </div>
                <div class="flex items-center">
                    <input type="radio" name="item_type" id="material_type" value="material" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" {{ old('item_type') == 'material' ? 'checked' : '' }}>
                    <label for="material_type" class="ml-2 block text-sm font-medium text-gray-700">Vật tư</label>
                </div>
                <div class="flex items-center">
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
                            <select name="equipment[0][id]" id="equipment_id_0" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field">
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
                            <input type="number" name="equipment[0][quantity]" id="equipment_quantity_0" required min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 required-field" value="{{ old('equipment.0.quantity', 1) }}">
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
                                </button>
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
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin liên hệ khách hàng</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên khách hàng</label>
                    <input type="text" name="customer_name" id="customer_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('customer_name') }}">
                        </div>
                        <div>
                            <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-1 required">Số điện thoại</label>
                    <input type="text" name="customer_phone" id="customer_phone" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('customer_phone') }}">
                        </div>
                        <div>
                            <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="customer_email" id="customer_email" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('customer_email') }}">
                        </div>
                        <div class="md:col-span-3">
                            <label for="customer_address" class="block text-sm font-medium text-gray-700 mb-1 required">Địa chỉ</label>
                    <input type="text" name="customer_address" id="customer_address" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('customer_address') }}">
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
    
    // Hiển thị section mặc định khi tải trang
    document.addEventListener('DOMContentLoaded', function() {
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
    </script>
@endsection
@endsection 