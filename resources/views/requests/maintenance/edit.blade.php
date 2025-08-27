@extends('layouts.app')

@section('title', 'Chỉnh sửa phiếu bảo trì dự án - SGL')

@section('content')
<div class="container-fluid px-6 py-4">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Chỉnh sửa phiếu bảo trì dự án</h1>
            <div class="mt-1 flex items-center">
                <span class="text-sm text-gray-500">Mã phiếu: {{ $maintenanceRequest->request_code }}</span>
                <span class="ml-4 px-2 py-1 text-xs rounded-full {{ $maintenanceRequest->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $maintenanceRequest->status == 'pending' ? 'Chờ duyệt' : 'Đã duyệt' }}
                </span>
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

    @if($maintenanceRequest->status !== 'pending')
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 rounded">
            <div class="flex">
                <div class="py-1"><i class="fas fa-exclamation-triangle text-yellow-500"></i></div>
                <div class="ml-3">
                    <p class="font-medium">Không thể chỉnh sửa!</p>
                    <p class="text-sm">Phiếu bảo trì này đã được duyệt và không thể chỉnh sửa.</p>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('requests.maintenance.update', $maintenanceRequest->id) }}" method="POST" class="bg-white rounded-xl shadow-md p-6" {{ $maintenanceRequest->status !== 'pending' ? 'style="pointer-events: none; opacity: 0.6;"' : '' }}>
        @csrf
        @method('PATCH')
        
        <!-- Thông tin đề xuất -->
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin đề xuất</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="request_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày đề xuất</label>
                    <input type="date" name="request_date" id="request_date" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 focus:outline-none" value="{{ old('request_date', $maintenanceRequest->request_date->format('Y-m-d')) }}">
                    <p class="text-xs text-gray-500 mt-1">Tự động tạo theo thời điểm hiện tại</p>
                </div>
                <div>
                    <label for="proposer_id" class="block text-sm font-medium text-gray-700 mb-1">Kỹ thuật viên</label>
                    <input type="text" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100" value="{{ $maintenanceRequest->proposer ? $maintenanceRequest->proposer->name : 'Không có' }}">
                    <input type="hidden" name="proposer_id" value="{{ $maintenanceRequest->proposer_id }}">
                </div>
            </div>
        </div>
        
        <!-- Thông tin dự án -->
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin dự án</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="project_type" class="block text-sm font-medium text-gray-700 mb-1">Loại dự án</label>
                    <select name="project_type" id="project_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="project" {{ old('project_type', $maintenanceRequest->project_type) == 'project' ? 'selected' : '' }}>Dự án</option>
                        <option value="rental" {{ old('project_type', $maintenanceRequest->project_type) == 'rental' ? 'selected' : '' }}>Phiếu cho thuê</option>
                    </select>
                </div>
                <div>
                    <label for="project_id" class="block text-sm font-medium text-gray-700 mb-1">Dự án / Phiếu cho thuê</label>
                    <select name="project_id" id="project_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Chọn dự án / phiếu cho thuê</option>
                    </select>
                </div>
                <div>
                    <label for="project_name" class="block text-sm font-medium text-gray-700 mb-1">Tên dự án</label>
                    <input type="text" name="project_name" id="project_name" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 focus:outline-none" value="{{ old('project_name', $maintenanceRequest->project_name) }}">
                </div>
                
                <!-- Dữ liệu cho dropdown dự án -->
                <script type="application/json" id="projects-data">
                    @json($projects)
                </script>
                
                <!-- Dữ liệu cho dropdown phiếu cho thuê -->
                <script type="application/json" id="rentals-data">
                    @json($rentals)
                </script>
            </div>
        </div>
        
        <!-- Thông tin đối tác -->
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin đối tác</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Loại:</label>
                    <input type="text" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100" value="{{ $maintenanceRequest->project_type == 'project' ? 'Dự án' : 'Phiếu cho thuê' }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên người liên hệ:</label>
                    <input type="text" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100" value="{{ $maintenanceRequest->customer_name }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại:</label>
                    <input type="text" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100" value="{{ $maintenanceRequest->customer_phone }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email:</label>
                    <input type="text" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100" value="{{ $maintenanceRequest->customer_email }}">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ:</label>
                    <input type="text" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100" value="{{ $maintenanceRequest->customer_address }}">
                </div>
            </div>
        </div>
        
        <!-- Thông tin bảo hành -->
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin bảo hành</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Loại bảo hành:</label>
                    <input type="text" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100" value="Tiêu chuẩn">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Thời gian bảo hành:</label>
                    <input type="text" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100" value="12 tháng">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ngày hết hạn:</label>
                    <input type="text" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100" value="{{ $maintenanceRequest->maintenance_date->addMonths(12)->format('d/m/Y') }}">
                </div>
            </div>
        </div>
        
        <!-- Thiết bị bảo trì -->
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Thiết bị bảo trì</h2>
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Chọn các thiết bị cần bảo trì từ danh sách dưới đây.
                        </p>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="select-all" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">MÃ THIẾT BỊ</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TÊN THIẾT BỊ</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SERIAL THIẾT BỊ</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">LOẠI</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody id="devices-table-body" class="bg-white divide-y divide-gray-200">
                        <!-- Thiết bị sẽ được load động -->
                    </tbody>
                </table>
            </div>
            <div id="no-devices-message" class="text-center py-8 text-gray-500" style="display: none;">
                <i class="fas fa-box-open text-4xl mb-2"></i>
                <p>Không có thiết bị nào để hiển thị</p>
            </div>
            <div id="devices-error" class="text-center py-8 text-red-500" style="display: none;">
                <i class="fas fa-exclamation-triangle text-4xl mb-2"></i>
                <p>Có lỗi xảy ra khi tải danh sách thiết bị</p>
            </div>
        </div>
        
        <!-- Thông tin bảo trì -->
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
            </div>
        </div>
        
        <!-- Ghi chú -->
        <div class="mb-6">
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
            <textarea name="notes" id="notes" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes', $maintenanceRequest->notes) }}</textarea>
        </div>
        
        <!-- Hidden input cho selected devices -->
        <input type="hidden" name="selected_devices" id="selected_devices" value="">
        
        <div class="flex justify-end space-x-3">
            @if($maintenanceRequest->status == 'pending')
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center">
                    <i class="fas fa-save mr-2"></i> Lưu thay đổi
                </button>
            @else
                <button type="button" disabled class="px-4 py-2 bg-gray-400 text-gray-600 rounded-lg cursor-not-allowed flex items-center">
                    <i class="fas fa-save mr-2"></i> Không thể chỉnh sửa
                </button>
            @endif
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
    const projectTypeSelect = document.getElementById('project_type');
    const projectIdSelect = document.getElementById('project_id');
    const projectNameInput = document.getElementById('project_name');
    const devicesTableBody = document.getElementById('devices-table-body');
    const noDevicesMessage = document.getElementById('no-devices-message');
    const devicesError = document.getElementById('devices-error');
    const selectAllCheckbox = document.getElementById('select-all');
    const selectedDevicesInput = document.getElementById('selected_devices');
    
    // Dữ liệu ban đầu
    const initialProjectType = '{{ $maintenanceRequest->project_type ?? "" }}';
    const initialProjectId = '{{ $maintenanceRequest->project_id ?? "" }}';
    const initialProjectName = '{{ $maintenanceRequest->project_name }}';
    const initialSelectedDevices = {!! json_encode($maintenanceRequest->products->pluck('id')->toArray()) !!};
    const initialProducts = {!! json_encode($maintenanceRequest->products) !!};
    
    // Log debug
    console.log('=== EDIT PAGE DEBUG ===');
    console.log('Initial Project Type:', initialProjectType);
    console.log('Initial Project ID:', initialProjectId);
    console.log('Initial Project Name:', initialProjectName);
    console.log('Initial Selected Devices:', initialSelectedDevices);
    console.log('Maintenance Request Products:', {!! json_encode($maintenanceRequest->products) !!});
    
    // Khởi tạo form
    projectTypeSelect.value = initialProjectType;
    projectIdSelect.value = initialProjectId;
    projectNameInput.value = initialProjectName;
    
    console.log('=== FORM INITIALIZATION ===');
    console.log('Project Type Select Value:', projectTypeSelect.value);
    console.log('Project ID Select Value:', projectIdSelect.value);
    console.log('Project Name Input Value:', projectNameInput.value);
    
    // Load projects/rentals dựa trên loại dự án
    function loadProjects() {
        const projectType = projectTypeSelect.value;
        projectIdSelect.innerHTML = '<option value="">Chọn dự án / phiếu cho thuê</option>';
        
        if (projectType === 'project') {
            // Load projects từ JSON data
            const data = JSON.parse(document.getElementById('projects-data').textContent);
            projectIdSelect.innerHTML = '<option value="">-- Chọn dự án --</option>';
            
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                
                // Sử dụng ngày hết hạn bảo hành từ database
                const warrantyEndDate = new Date(item.warranty_end_date);
                const today = new Date();
                const isExpired = warrantyEndDate < today;
                
                const statusText = isExpired ? ' (Đã quá hạn)' : '';
                const statusClass = isExpired ? 'text-red-600' : 'text-green-600';
                
                option.textContent = `${item.project_code} - ${item.project_name} (${(item.customer && item.customer.company_name) || 'N/A'}) - Hết hạn: ${warrantyEndDate.toLocaleDateString('vi-VN')}${statusText}`;
                option.className = statusClass;
                option.setAttribute('data-project-name', item.project_name);
                option.setAttribute('data-customer-id', (item.customer && item.customer.id) || '');
                option.setAttribute('data-customer-name', (item.customer && item.customer.company_name) || '');
                option.setAttribute('data-customer-phone', (item.customer && item.customer.phone) || '');
                option.setAttribute('data-customer-email', (item.customer && item.customer.email) || '');
                option.setAttribute('data-customer-address', (item.customer && item.customer.address) || '');
                option.setAttribute('data-project-type', 'project');
                option.setAttribute('data-warranty-end', item.warranty_end_date);
                option.setAttribute('data-is-expired', isExpired);
                
                // Disable option nếu đã quá hạn
                if (isExpired) {
                    option.disabled = true;
                }
                // Ưu tiên khớp theo tên dự án đã lưu; nếu không khớp được thì mới dùng id
                const matchByName = initialProjectName && item.project_name && item.project_name.trim().toLowerCase() === initialProjectName.trim().toLowerCase();
                const matchById = initialProjectId && String(item.id) === String(initialProjectId);
                if (matchByName || (!matchByName && matchById)) {
                    option.selected = true;
                }
                projectIdSelect.appendChild(option);
            });
        } else if (projectType === 'rental') {
            // Load rentals từ JSON data
            const data = JSON.parse(document.getElementById('rentals-data').textContent);
            projectIdSelect.innerHTML = '<option value="">-- Chọn phiếu cho thuê --</option>';
            
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                
                const dueDate = new Date(item.due_date);
                const today = new Date();
                const isExpired = dueDate < today;
                
                const statusText = isExpired ? ' (Đã quá hạn)' : '';
                const statusClass = isExpired ? 'text-red-600' : 'text-green-600';
                
                option.textContent = `${item.rental_code} - ${item.rental_name} (${(item.customer && item.customer.company_name) || 'N/A'}) - Hạn trả: ${dueDate.toLocaleDateString('vi-VN')}${statusText}`;
                option.className = statusClass;
                option.setAttribute('data-project-name', item.rental_name);
                option.setAttribute('data-customer-id', (item.customer && item.customer.id) || '');
                option.setAttribute('data-customer-name', (item.customer && item.customer.company_name) || '');
                option.setAttribute('data-customer-phone', (item.customer && item.customer.phone) || '');
                option.setAttribute('data-customer-email', (item.customer && item.customer.email) || '');
                option.setAttribute('data-customer-address', (item.customer && item.customer.address) || '');
                option.setAttribute('data-project-type', 'rental');
                option.setAttribute('data-warranty-end', item.due_date);
                option.setAttribute('data-is-expired', isExpired);
                
                // Disable option nếu đã quá hạn
                if (isExpired) {
                    option.disabled = true;
                }
                // Ưu tiên khớp theo tên phiếu cho thuê đã lưu; nếu không khớp được thì mới dùng id
                const matchByName = initialProjectName && item.rental_name && item.rental_name.trim().toLowerCase() === initialProjectName.trim().toLowerCase();
                const matchById = initialProjectId && String(item.id) === String(initialProjectId);
                if (matchByName || (!matchByName && matchById)) {
                    option.selected = true;
                }
                projectIdSelect.appendChild(option);
            });
        }
    }
    
    // Load devices khi chọn project/rental
    function loadDevices() {
        const projectType = projectTypeSelect.value;
        const projectId = projectIdSelect.value;
        
        console.log('=== LOAD DEVICES DEBUG ===');
        console.log('Project Type:', projectType);
        console.log('Project ID:', projectId);
        
        if (!projectId) {
            devicesTableBody.innerHTML = '';
            noDevicesMessage.style.display = 'block';
            devicesError.style.display = 'none';
            return;
        }
        
        // Hiển thị loading
        devicesTableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải thiết bị...</td></tr>';
        noDevicesMessage.style.display = 'none';
        devicesError.style.display = 'none';
        
        fetch('/requests/maintenance/api/devices', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                project_type: projectType,
                project_id: projectId
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Devices loaded:', data);
            devicesTableBody.innerHTML = '';
            
            if (data.devices && data.devices.length > 0) {
                data.devices.forEach(device => {
                    const row = document.createElement('tr');
                    // So sánh theo product_code, product_name và serial_number
                    const isSelected = initialSelectedDevices.some(selectedId => {
                        const selectedProduct = initialProducts.find(p => p.id == selectedId);
                        return selectedProduct && 
                               selectedProduct.product_code === device.code && 
                               selectedProduct.product_name === device.name &&
                               (selectedProduct.serial_number || 'N/A') === (device.serial_number || 'N/A');
                    });
                    
                    console.log('Device:', device);
                    console.log('Is Selected:', isSelected);
                    
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" class="device-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                                   value="${device.id}" ${isSelected ? 'checked' : ''}>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${device.code}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${device.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${device.serial_number}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${device.type}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-medium">Yêu cầu bảo trì</td>
                    `;
                    devicesTableBody.appendChild(row);
                });
                updateSelectedDevices();
            } else {
                noDevicesMessage.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error loading devices:', error);
            devicesError.style.display = 'block';
            devicesTableBody.innerHTML = '';
        });
    }
    
    // Hiển thị thiết bị đã có sẵn
    function showExistingDevices() {
        console.log('=== SHOW EXISTING DEVICES ===');
        console.log('Showing existing devices:', initialProducts);
        devicesTableBody.innerHTML = '';
        
        if (initialProducts && initialProducts.length > 0) {
            initialProducts.forEach(product => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox" class="device-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                               value="${product.id}" checked>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${product.product_code}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${product.product_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.serial_number || 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.type || 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-medium">Yêu cầu bảo trì</td>
                `;
                devicesTableBody.appendChild(row);
            });
            updateSelectedDevices();
        } else {
            noDevicesMessage.style.display = 'block';
        }
    }
    
    // Cập nhật selected devices
    function updateSelectedDevices() {
        const checkboxes = document.querySelectorAll('.device-checkbox:checked');
        const selectedIds = Array.from(checkboxes).map(cb => cb.value);
        selectedDevicesInput.value = JSON.stringify(selectedIds);
        
        console.log('=== UPDATE SELECTED DEVICES DEBUG ===');
        console.log('Selected checkboxes count:', checkboxes.length);
        console.log('Selected IDs:', selectedIds);
        console.log('Selected devices JSON:', selectedDevicesInput.value);
        
        // Cập nhật select all checkbox
        const allCheckboxes = document.querySelectorAll('.device-checkbox');
        selectAllCheckbox.checked = allCheckboxes.length > 0 && allCheckboxes.length === checkboxes.length;
        selectAllCheckbox.indeterminate = checkboxes.length > 0 && checkboxes.length < allCheckboxes.length;
    }
    
    // Event listeners
    projectTypeSelect.addEventListener('change', function() {
        projectIdSelect.innerHTML = '<option value="">Chọn dự án / phiếu cho thuê</option>';
        projectNameInput.value = '';
        devicesTableBody.innerHTML = '';
        noDevicesMessage.style.display = 'none';
        devicesError.style.display = 'none';
        loadProjects();
    });
    
    projectIdSelect.addEventListener('change', function() {
        const selectedOption = projectIdSelect.options[projectIdSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const textParts = selectedOption.textContent.split(' - ');
            projectNameInput.value = textParts.length > 1 ? textParts[1].split(' (')[0] : '';
            loadDevices();
        } else {
            projectNameInput.value = '';
            devicesTableBody.innerHTML = '';
            noDevicesMessage.style.display = 'block';
        }
    });
    
    // Select all checkbox
    selectAllCheckbox.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.device-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateSelectedDevices();
    });
    
    // Individual device checkboxes
    devicesTableBody.addEventListener('change', function(e) {
        if (e.target.classList.contains('device-checkbox')) {
            updateSelectedDevices();
        }
    });
    
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        console.log('=== FORM SUBMISSION DEBUG ===');
        console.log('Selected devices input value:', selectedDevicesInput.value);
        
        const selectedDevices = JSON.parse(selectedDevicesInput.value || '[]');
        console.log('Parsed selected devices:', selectedDevices);
        console.log('Selected devices count:', selectedDevices.length);
        
        if (selectedDevices.length === 0) {
            e.preventDefault();
            alert('Vui lòng chọn ít nhất một thiết bị để bảo trì.');
            return false;
        }
        
        console.log('Form will be submitted with selected devices:', selectedDevices);
    });
    
    // Khởi tạo ban đầu
    loadProjects();
    // Nếu đã có project_id thì load thiết bị luôn (giống create)
    if (initialProjectId) {
        loadDevices();
    }
});
</script>
@endsection 