@extends('layouts.app')

@section('title', 'Tạo phiếu khách yêu cầu bảo trì - SGL')

@section('content')
<div class="container-fluid px-6 py-4">
    <header class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Tạo phiếu khách yêu cầu bảo trì</h1>
                <div class="mt-1 px-3 py-1 inline-block bg-green-100 text-green-800 text-sm rounded-full">
                    Mẫu CMR
                </div>
            </div>
            <a href="{{ route('requests.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </div>
        </header>
        
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

    @if($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">Vui lòng kiểm tra lại thông tin:</p>
                    <ul class="mt-1 text-sm list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
    
    <form action="{{ route('requests.customer-maintenance.store') }}" method="POST" class="bg-white rounded-xl shadow-md p-6">
                @csrf
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin tiếp nhận</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="request_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày tiếp nhận</label>
                    <input type="date" name="request_date" id="request_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('request_date', $request->request_date ? $request->request_date->format('Y-m-d') : date('Y-m-d')) }}">
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin khách hàng</h2>
            
            @if(Auth::guard('web')->check())
            <div class="mb-4">
                <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1">Chọn khách hàng</label>
                <select name="customer_id" id="customer_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Chọn khách hàng --</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id', $request->customer_id) == $customer->id ? 'selected' : '' }}>
                            {{ $customer->company_name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-sm text-gray-500 mt-1">Nếu không chọn khách hàng, vui lòng điền thông tin bên dưới</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên khách hàng/Đơn vị</label>
                    <input type="text" name="customer_name" id="customer_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('customer_name', $request->customer_name) }}">
                </div>
                <div>
                    <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-1 required">Số điện thoại liên hệ</label>
                    <input type="text" name="customer_phone" id="customer_phone" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('customer_phone', $request->customer_phone) }}">
                </div>
                <div>
                    <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="customer_email" id="customer_email" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('customer_email', $request->customer_email) }}">
                </div>
                <div>
                    <label for="customer_address" class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ</label>
                    <input type="text" name="customer_address" id="customer_address" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('customer_address', $request->customer_address) }}">
                </div>
            </div>
            @else
                <input type="hidden" name="customer_id" value="{{ Auth::guard('customer')->user()->customer->id }}">
                <div class="bg-blue-50 rounded-lg p-4 mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tên khách hàng/Đơn vị</label>
                            <p class="text-gray-800">{{ Auth::guard('customer')->user()->customer->company_name ?? Auth::guard('customer')->user()->customer->name }}</p>
                            <input type="hidden" name="customer_name" value="{{ Auth::guard('customer')->user()->customer->company_name ?? Auth::guard('customer')->user()->customer->name }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại liên hệ</label>
                            <p class="text-gray-800">{{ Auth::guard('customer')->user()->customer->phone ?: 'Chưa có thông tin' }}</p>
                            <input type="hidden" name="customer_phone" value="{{ Auth::guard('customer')->user()->customer->phone }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <p class="text-gray-800">{{ Auth::guard('customer')->user()->customer->email ?: 'Chưa có thông tin' }}</p>
                            <input type="hidden" name="customer_email" value="{{ Auth::guard('customer')->user()->customer->email }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ</label>
                            <p class="text-gray-800">{{ Auth::guard('customer')->user()->customer->address ?: 'Chưa có thông tin' }}</p>
                            <input type="hidden" name="customer_address" value="{{ Auth::guard('customer')->user()->customer->address }}">
                        </div>
                    </div>
                </div>
            @endif
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin yêu cầu bảo trì</h2>
            
            @if(Auth::guard('customer')->check() && (isset($customerProjects) || isset($customerRentals)))
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Chọn nguồn thiết bị</label>
                <div class="space-y-2">
                    <label class="inline-flex items-center">
                        <input type="radio" name="item_source" value="project" class="form-radio" onchange="handleItemSourceChange(this)">
                        <span class="ml-2">Từ dự án</span>
                    </label>
                    <label class="inline-flex items-center ml-4">
                        <input type="radio" name="item_source" value="rental" class="form-radio" onchange="handleItemSourceChange(this)">
                        <span class="ml-2">Từ thuê thiết bị</span>
                    </label>
                    <label class="inline-flex items-center ml-4">
                        <input type="radio" name="item_source" value="none" class="form-radio" checked onchange="handleItemSourceChange(this)">
                        <span class="ml-2">Không chọn</span>
                    </label>
                </div>
            </div>
            
            <!-- Tên dự án/thiết bị -->
            <div id="project_name_container" class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tên dự án/thiết bị <span class="text-red-500">*</span>
                </label>
                <input type="text" name="project_name" id="project_name" class="form-input w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            
            <!-- Chọn dự án -->
            <div id="project_selection" class="mb-4 hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">Chọn dự án</label>
                <select name="project_id" class="form-select w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="loadProjectItems(this.value)">
                    <option value="">-- Chọn dự án --</option>
                    @foreach($customerProjects as $project)
                        <option value="{{ $project->id }}" data-code="{{ $project->project_code }}">{{ $project->project_name }} ({{ $project->project_code }})</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Chọn đơn thuê -->
            <div id="rental_selection" class="mb-4 hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">Chọn đơn thuê</label>
                <select name="rental_id" class="form-select w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="loadRentalItems(this.value)">
                    <option value="">-- Chọn đơn thuê --</option>
                    @foreach($customerRentals as $rental)
                        <option value="{{ $rental->id }}" data-code="{{ $rental->rental_code }}">{{ $rental->rental_code }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Danh sách thiết bị -->
            <div id="items_table" class="mb-4 hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">Chọn thiết bị</label>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chọn</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên thiết bị</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số serial</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã dự án/đơn thuê</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mô tả thiết bị</th>
                            </tr>
                        </thead>
                        <tbody id="items_list" class="bg-white divide-y divide-gray-200">
                            <!-- Items will be loaded here -->
                        </tbody>
                    </table>
                </div>
                        </div>
            @endif
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
               
                <div class="md:col-span-2">
                    <label for="maintenance_reason" class="block text-sm font-medium text-gray-700 mb-1 required">Lý do yêu cầu bảo trì</label>
                    <textarea name="maintenance_reason" id="maintenance_reason" rows="3" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('maintenance_reason', $request->maintenance_reason) }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                    <label for="maintenance_details" class="block text-sm font-medium text-gray-700 mb-1">Chi tiết yêu cầu bảo trì</label>
                    <textarea name="maintenance_details" id="maintenance_details" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('maintenance_details', $request->maintenance_details) }}</textarea>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Mức độ ưu tiên</h2>
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center">
                    <input type="radio" name="priority" id="priority_low" value="low" {{ old('priority', $request->priority) == 'low' ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <label for="priority_low" class="ml-2 block text-sm font-medium text-gray-700">Thấp</label>
                        </div>
                        <div class="flex items-center">
                    <input type="radio" name="priority" id="priority_medium" value="medium" {{ old('priority', $request->priority) == 'medium' || !old('priority', $request->priority) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <label for="priority_medium" class="ml-2 block text-sm font-medium text-gray-700">Trung bình</label>
                        </div>
                        <div class="flex items-center">
                    <input type="radio" name="priority" id="priority_high" value="high" {{ old('priority', $request->priority) == 'high' ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <label for="priority_high" class="ml-2 block text-sm font-medium text-gray-700">Cao</label>
                        </div>
                        <div class="flex items-center">
                    <input type="radio" name="priority" id="priority_urgent" value="urgent" {{ old('priority', $request->priority) == 'urgent' ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <label for="priority_urgent" class="ml-2 block text-sm font-medium text-gray-700">Khẩn cấp</label>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Thời gian</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ngày hoàn thành dự kiến</label>
                    <input type="date" name="expected_completion_date" class="form-input w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('expected_completion_date', $request->expected_completion_date) }}">
                        </div>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú bổ sung</label>
            <textarea name="notes" id="notes" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes', $request->notes) }}</textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
            <a href="{{ route('requests.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 flex items-center">
                <i class="fas fa-times mr-2"></i> Hủy bỏ
            </a>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Xử lý chọn khách hàng
        const customerSelect = document.getElementById('customer_id');
        if (customerSelect) {
            customerSelect.addEventListener('change', function() {
                const selectedValue = this.value;
                if (selectedValue) {
                    fetch(`/api/customers/${selectedValue}`)
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('customer_name').value = data.company_name;
                            document.getElementById('customer_phone').value = data.phone || '';
                            document.getElementById('customer_email').value = data.email || '';
                            document.getElementById('customer_address').value = data.address || '';
                        })
                        .catch(error => console.error('Error:', error));
                }
            });
        }
        
        // Xử lý chọn nguồn thiết bị
        const sourceRadios = document.querySelectorAll('input[name="item_source"]');
        const projectSelectContainer = document.getElementById('project_select_container');
        const projectItemsContainer = document.getElementById('project_items_container');
        const rentalSelectContainer = document.getElementById('rental_select_container');
        const rentalItemsContainer = document.getElementById('rental_items_container');
        const projectSelect = document.getElementById('project_id');
        const projectItemSelect = document.getElementById('project_item_id');
        const rentalSelect = document.getElementById('rental_id');
        const rentalItemSelect = document.getElementById('rental_item_id');
        const projectNameInput = document.getElementById('project_name');
        
        // Ẩn/hiện các phần tương ứng khi chọn nguồn thiết bị
        if (sourceRadios) {
            sourceRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'project') {
                        if (projectSelectContainer) projectSelectContainer.classList.remove('hidden');
                        if (projectItemsContainer) projectItemsContainer.classList.add('hidden');
                        if (rentalSelectContainer) rentalSelectContainer.classList.add('hidden');
                        if (rentalItemsContainer) rentalItemsContainer.classList.add('hidden');
                    } else if (this.value === 'rental') {
                        if (projectSelectContainer) projectSelectContainer.classList.add('hidden');
                        if (projectItemsContainer) projectItemsContainer.classList.add('hidden');
                        if (rentalSelectContainer) rentalSelectContainer.classList.remove('hidden');
                        if (rentalItemsContainer) rentalItemsContainer.classList.add('hidden');
                    } else {
                        if (projectSelectContainer) projectSelectContainer.classList.add('hidden');
                        if (projectItemsContainer) projectItemsContainer.classList.add('hidden');
                        if (rentalSelectContainer) rentalSelectContainer.classList.add('hidden');
                        if (rentalItemsContainer) rentalItemsContainer.classList.add('hidden');
                    }
                });
            });
        }
        
        // Lấy thiết bị theo dự án
        if (projectSelect) {
            projectSelect.addEventListener('change', function() {
                const projectId = this.value;
                if (projectId) {
                    fetch(`/api/projects/${projectId}/items`)
                        .then(response => response.json())
                        .then(data => {
                            projectItemSelect.innerHTML = '<option value="">-- Chọn thiết bị --</option>';
                            data.forEach(item => {
                                const option = document.createElement('option');
                                option.value = `${item.type}:${item.id}`;
                                option.textContent = `${item.name} (${item.type === 'product' ? 'Thiết bị' : item.type === 'material' ? 'Vật tư' : 'Hàng hóa'})`;
                                projectItemSelect.appendChild(option);
                            });
                            projectItemsContainer.classList.remove('hidden');
                            
                            // Cập nhật tên dự án
                            projectNameInput.value = this.options[this.selectedIndex].text;
                        })
                        .catch(error => console.error('Error:', error));
                } else {
                    projectItemsContainer.classList.add('hidden');
                }
            });
        }
        
        // Lấy thiết bị theo đơn thuê
        if (rentalSelect) {
            rentalSelect.addEventListener('change', function() {
                const rentalId = this.value;
                if (rentalId) {
                    fetch(`/api/rentals/${rentalId}/items`)
                        .then(response => response.json())
                        .then(data => {
                            rentalItemSelect.innerHTML = '<option value="">-- Chọn thiết bị --</option>';
                            data.forEach(item => {
                                const option = document.createElement('option');
                                option.value = `${item.type}:${item.id}`;
                                option.textContent = `${item.name} (${item.type === 'product' ? 'Thiết bị' : item.type === 'material' ? 'Vật tư' : 'Hàng hóa'})`;
                                rentalItemSelect.appendChild(option);
                            });
                            rentalItemsContainer.classList.remove('hidden');
                            
                            // Cập nhật tên thiết bị thuê
                            projectNameInput.value = 'Thuê thiết bị: ' + this.options[this.selectedIndex].text;
                        })
                        .catch(error => console.error('Error:', error));
                } else {
                    rentalItemsContainer.classList.add('hidden');
                }
            });
        }
        
        // Cập nhật thông tin khi chọn thiết bị từ dự án
        if (projectItemSelect) {
            projectItemSelect.addEventListener('change', function() {
                if (this.value) {
                    projectNameInput.value = projectSelect.options[projectSelect.selectedIndex].text + ' - ' + this.options[this.selectedIndex].text;
                }
            });
        }
        
        // Cập nhật thông tin khi chọn thiết bị từ đơn thuê
        if (rentalItemSelect) {
            rentalItemSelect.addEventListener('change', function() {
                if (this.value) {
                    projectNameInput.value = 'Thuê thiết bị: ' + rentalSelect.options[rentalSelect.selectedIndex].text + ' - ' + this.options[this.selectedIndex].text;
                }
            });
        }
    });

    function handleItemSourceChange(radio) {
        // Reset selections
        document.querySelector('select[name="project_id"]').value = '';
        document.querySelector('select[name="rental_id"]').value = '';
        document.getElementById('items_list').innerHTML = '';
        document.getElementById('project_name').value = '';
        
        // Show/hide project name input
        document.getElementById('project_name_container').style.display = radio.value === 'none' ? 'block' : 'none';
        
        // Hide all sections first
        document.getElementById('project_selection').classList.add('hidden');
        document.getElementById('rental_selection').classList.add('hidden');
        document.getElementById('items_table').classList.add('hidden');
        
        // Show relevant section based on selection
        if (radio.value === 'project') {
            document.getElementById('project_selection').classList.remove('hidden');
        } else if (radio.value === 'rental') {
            document.getElementById('rental_selection').classList.remove('hidden');
        }
    }

    function loadProjectItems(projectId) {
        if (!projectId) {
            document.getElementById('items_table').classList.add('hidden');
            document.getElementById('items_list').innerHTML = '';
            document.getElementById('project_name').value = '';
            return;
        }
        
        // Get project code from selected option
        const projectSelect = document.querySelector('select[name="project_id"]');
        const selectedOption = projectSelect.options[projectSelect.selectedIndex];
        const projectCode = selectedOption.dataset.code;
        
        fetch(`/api/projects/${projectId}/items`)
            .then(response => response.json())
            .then(items => {
                document.getElementById('items_table').classList.remove('hidden');
                document.getElementById('items_list').innerHTML = items.map(item => `
                    <tr>
                        <td class="px-6 py-4">
                            <input type="radio" name="item_id" value="product:${item.id}" class="form-radio" onchange="handleItemSelection(this, '${item.name}', '${projectCode}')">
                        </td>
                        <td class="px-6 py-4">${item.name}</td>
                        <td class="px-6 py-4">${item.serial_number || 'N/A'}</td>
                        <td class="px-6 py-4">${projectCode}</td>
                        <td class="px-6 py-4">${item.description || 'N/A'}</td>
                    </tr>
                `).join('');
            });
    }

    function loadRentalItems(rentalId) {
        if (!rentalId) {
            document.getElementById('items_table').classList.add('hidden');
            document.getElementById('items_list').innerHTML = '';
            document.getElementById('project_name').value = '';
            return;
        }
        
        // Get rental code from selected option
        const rentalSelect = document.querySelector('select[name="rental_id"]');
        const selectedOption = rentalSelect.options[rentalSelect.selectedIndex];
        const rentalCode = selectedOption.dataset.code;
        
        fetch(`/api/rentals/${rentalId}/items`)
            .then(response => response.json())
            .then(items => {
                document.getElementById('items_table').classList.remove('hidden');
                document.getElementById('items_list').innerHTML = items.map(item => `
                    <tr>
                        <td class="px-6 py-4">
                            <input type="radio" name="item_id" value="${item.type}:${item.id}" class="form-radio" onchange="handleItemSelection(this, '${item.name}', '${rentalCode}')">
                        </td>
                        <td class="px-6 py-4">${item.name}</td>
                        <td class="px-6 py-4">${item.serial_number || 'N/A'}</td>
                        <td class="px-6 py-4">${rentalCode}</td>
                        <td class="px-6 py-4">${item.description || 'N/A'}</td>
                    </tr>
                `).join('');
            });
    }

    function handleItemSelection(radio, itemName, code) {
        if (radio.checked) {
            document.getElementById('project_name').value = `${itemName} (${code})`;
        }
        }
    </script>
@endsection 