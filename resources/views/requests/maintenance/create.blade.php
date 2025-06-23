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
                    <label for="proposer_id" class="block text-sm font-medium text-gray-700 mb-1 required">Kỹ thuật đề xuất</label>
                    <select name="proposer_id" id="proposer_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Chọn nhân viên --</option>
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
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin dự án bảo trì</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="project_name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên dự án</label>
                    <input type="text" name="project_name" id="project_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('project_name') }}">
                </div>
                <div>
                    <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1 required">Đối tác</label>
                    <select name="customer_id" id="customer_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Chọn đối tác --</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->company_name }}
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
        
        <div class="mb-6 border-b border-gray-200 pb-4">
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-lg font-semibold text-gray-800">Thành phẩm cần bảo trì</h2>
                <button type="button" id="add_product" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                    <i class="fas fa-plus-circle mr-1"></i> Thêm thành phẩm
                </button>
            </div>
            
            <div id="product_container">
                <div class="product-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3">
                    <div class="md:col-span-3">
                        <label for="product_id_0" class="block text-sm font-medium text-gray-700 mb-1 required">Thành phẩm</label>
                        <select name="products[0][id]" id="product_id_0" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Chọn thành phẩm --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-1">
                        <label for="product_quantity_0" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                        <input type="number" name="products[0][quantity]" id="product_quantity_0" required min="1" value="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-1 flex items-end">
                        <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group invisible">
                            <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                        </button>
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
        
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Nhân sự thực hiện</h2>
            <div id="staff_container">
                <div class="staff-row grid grid-cols-1 md:grid-cols-4 gap-4 mb-3">
                    <div class="md:col-span-3">
                        <label for="staff_name_0" class="block text-sm font-medium text-gray-700 mb-1 required">Tên nhân viên</label>
                        <select name="staff[0][id]" id="staff_name_0" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Chọn nhân viên --</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-1 flex items-end">
                        <button type="button" id="add_staff" class="h-10 px-4 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors flex items-center justify-center">
                            <i class="fas fa-plus mr-2"></i> Thêm
                        </button>
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
    // Thêm thành phẩm
    let productCount = 1;
    document.getElementById('add_product').addEventListener('click', function() {
        const container = document.getElementById('product_container');
        const newRow = document.createElement('div');
        newRow.className = 'product-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3';
        
        // Lấy danh sách thành phẩm từ select đầu tiên
        const firstSelect = document.getElementById('product_id_0');
        let optionsHtml = '<option value="">-- Chọn thành phẩm --</option>';
        
        Array.from(firstSelect.options).forEach(option => {
            optionsHtml += `<option value="${option.value}">${option.text}</option>`;
        });
        
        newRow.innerHTML = `
            <div class="md:col-span-3">
                <label for="product_id_${productCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Thành phẩm</label>
                <select name="products[${productCount}][id]" id="product_id_${productCount}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    ${optionsHtml}
                </select>
            </div>
            <div class="md:col-span-1">
                <label for="product_quantity_${productCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                <input type="number" name="products[${productCount}][quantity]" id="product_quantity_${productCount}" required min="1" value="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="md:col-span-1 flex items-end">
                <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                </button>
            </div>
        `;
        container.appendChild(newRow);
        productCount++;
        
        addRemoveRowEventListeners();
    });
    
    // Thêm nhân viên
    let staffCount = 1;
    document.getElementById('add_staff').addEventListener('click', function() {
        const container = document.getElementById('staff_container');
        const newRow = document.createElement('div');
        newRow.className = 'staff-row grid grid-cols-1 md:grid-cols-4 gap-4 mb-3';
        newRow.innerHTML = `
            <div class="md:col-span-3">
                <label for="staff_name_${staffCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Tên nhân viên</label>
                <select name="staff[${staffCount}][id]" id="staff_name_${staffCount}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Chọn nhân viên --</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-1 flex items-end">
                <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                </button>
            </div>
        `;
        container.appendChild(newRow);
        staffCount++;
        
        addRemoveRowEventListeners();
    });
    
    // Xử lý sự kiện nút xóa
    function addRemoveRowEventListeners() {
        document.querySelectorAll('.remove-row').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.product-row, .staff-row').remove();
            });
        });
    }

    // Auto-fill thông tin khách hàng khi chọn đối tác
    document.getElementById('customer_id').addEventListener('change', function() {
        const customerId = this.value;
        if (!customerId) return;
        
        // Gọi API để lấy thông tin khách hàng
        fetch(`/api/customers/${customerId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('customer_name').value = data.name || '';
                document.getElementById('customer_phone').value = data.phone || '';
                document.getElementById('customer_email').value = data.email || '';
                document.getElementById('customer_address').value = data.address || '';
            })
            .catch(error => console.error('Lỗi khi lấy thông tin khách hàng:', error));
    });
</script>
@endsection 