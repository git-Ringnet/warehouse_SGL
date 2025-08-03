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
                    <input type="date" name="request_date" id="request_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('request_date', date('Y-m-d')) }}" readonly>
                    <p class="text-xs text-gray-500 mt-1">Tự động tạo theo thời điểm hiện tại</p>
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
                    <label for="project_type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại dự án</label>
                    <select name="project_type" id="project_type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Chọn loại --</option>
                        <option value="project" {{ old('project_type') == 'project' ? 'selected' : '' }}>Dự án</option>
                        <option value="rental" {{ old('project_type') == 'rental' ? 'selected' : '' }}>Phiếu cho thuê</option>
                    </select>
                </div>
                <div>
                    <label for="project_id" class="block text-sm font-medium text-gray-700 mb-1 required">Dự án / Phiếu cho thuê</label>
                    <select name="project_id" id="project_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                        <option value="">-- Vui lòng chọn loại trước --</option>
                    </select>
                </div>
                <div>
                    <label for="project_name" class="block text-sm font-medium text-gray-700 mb-1">Tên dự án</label>
                    <input type="text" name="project_name" id="project_name" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 focus:outline-none" value="{{ old('project_name') }}">
                </div>
                
                <!-- Dữ liệu cho dropdown dự án -->
                <script type="application/json" id="projects-data">
                    @json($projects)
                </script>
                
                <!-- Dữ liệu cho dropdown phiếu cho thuê -->
                <script type="application/json" id="rentals-data">
                    @json($rentals)
                </script>
                <div id="customer_details" class="md:col-span-2 border border-gray-200 rounded-lg p-4 bg-gray-50 hidden">
                    <h3 class="text-md font-medium text-gray-800 mb-2">Thông tin đối tác</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm text-gray-500">Loại:</span>
                            <p id="project_type_display" class="font-medium text-gray-700"></p>
                        </div>
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
                            <p id="warranty_type_display" class="font-medium text-gray-700">Tiêu chuẩn</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Thời gian bảo hành:</span>
                            <p id="warranty_period_display" class="font-medium text-gray-700">12 tháng</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Ngày hết hạn:</span>
                            <p id="warranty_end_display" class="font-medium text-gray-700">-</p>
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
        
        <!-- Thiết bị bảo trì -->
        <div class="mb-6 border-b border-gray-200 pb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Thiết bị bảo trì</h2>
            <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 mb-4">
                <p class="text-sm">
                    <i class="fas fa-info-circle mr-1"></i> Chọn các thiết bị cần bảo trì từ danh sách dưới đây.
                </p>
            </div>
            <div id="device_selection_error" class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4 hidden">
                <p class="text-sm">
                    <i class="fas fa-exclamation-circle mr-1"></i> Vui lòng chọn ít nhất một thiết bị để bảo trì.
                </p>
            </div>
            <div id="warranty_items_container" class="hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" id="select_all_devices" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã thiết bị</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên thiết bị</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serial thiết bị</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
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
                        <option value="maintenance" {{ old('maintenance_type') == 'maintenance' ? 'selected' : '' }}>Bảo trì định kỳ</option>
                        <option value="repair" {{ old('maintenance_type') == 'repair' ? 'selected' : '' }}>Sửa chữa lỗi</option>
                        <option value="replacement" {{ old('maintenance_type') == 'replacement' ? 'selected' : '' }}>Thay thế linh kiện</option>
                        <option value="upgrade" {{ old('maintenance_type') == 'upgrade' ? 'selected' : '' }}>Nâng cấp</option>
                        <option value="other" {{ old('maintenance_type') == 'other' ? 'selected' : '' }}>Khác</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="mb-6">
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
            <textarea name="notes" id="notes" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
        </div>
        
        <div class="flex justify-end space-x-3">
            <button type="submit" id="submit-btn" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center">
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
    // Xử lý khi chọn loại dự án
    document.getElementById('project_type').addEventListener('change', function() {
        const projectSelect = document.getElementById('project_id');
        const projectType = this.value;
        
        // Reset dropdown dự án
        projectSelect.innerHTML = '<option value="">-- Vui lòng chọn loại trước --</option>';
        projectSelect.disabled = true;
        
        if (!projectType) {
            return;
        }
        
        // Enable dropdown dự án
        projectSelect.disabled = false;
        
        // Lấy dữ liệu từ JSON
        let data = [];
        if (projectType === 'project') {
            data = JSON.parse(document.getElementById('projects-data').textContent);
            projectSelect.innerHTML = '<option value="">-- Chọn dự án --</option>';
        } else if (projectType === 'rental') {
            data = JSON.parse(document.getElementById('rentals-data').textContent);
            projectSelect.innerHTML = '<option value="">-- Chọn phiếu cho thuê --</option>';
        }
        
        // Thêm options vào dropdown
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            
            if (projectType === 'project') {
                // Sử dụng ngày hết hạn bảo hành từ database
                const warrantyEndDate = new Date(item.warranty_end_date);
                const today = new Date();
                const isExpired = warrantyEndDate < today;
                
                const statusText = isExpired ? ' (Đã quá hạn)' : '';
                const statusClass = isExpired ? 'text-red-600' : 'text-green-600';
                
                option.textContent = `${item.project_code} - ${item.project_name} (${item.customer?.company_name || 'N/A'}) - Hết hạn: ${warrantyEndDate.toLocaleDateString('vi-VN')}${statusText}`;
                option.className = statusClass;
                option.setAttribute('data-project-name', item.project_name);
                option.setAttribute('data-customer-id', item.customer?.id || '');
                option.setAttribute('data-customer-name', item.customer?.company_name || '');
                option.setAttribute('data-customer-phone', item.customer?.phone || '');
                option.setAttribute('data-customer-email', item.customer?.email || '');
                option.setAttribute('data-customer-address', item.customer?.address || '');
                option.setAttribute('data-project-type', 'project');
                option.setAttribute('data-warranty-end', item.warranty_end_date);
                option.setAttribute('data-is-expired', isExpired);
                
                // Disable option nếu đã quá hạn
                if (isExpired) {
                    option.disabled = true;
                }
            } else {
                const dueDate = new Date(item.due_date);
                const today = new Date();
                const isExpired = dueDate < today;
                
                const statusText = isExpired ? ' (Đã quá hạn)' : '';
                const statusClass = isExpired ? 'text-red-600' : 'text-green-600';
                
                option.textContent = `${item.rental_code} - ${item.rental_name} (${item.customer?.company_name || 'N/A'}) - Hạn trả: ${dueDate.toLocaleDateString('vi-VN')}${statusText}`;
                option.className = statusClass;
                option.setAttribute('data-project-name', item.rental_name);
                option.setAttribute('data-customer-id', item.customer?.id || '');
                option.setAttribute('data-customer-name', item.customer?.company_name || '');
                option.setAttribute('data-customer-phone', item.customer?.phone || '');
                option.setAttribute('data-customer-email', item.customer?.email || '');
                option.setAttribute('data-customer-address', item.customer?.address || '');
                option.setAttribute('data-project-type', 'rental');
                option.setAttribute('data-warranty-end', item.due_date);
                option.setAttribute('data-is-expired', isExpired);
                
                // Disable option nếu đã quá hạn
                if (isExpired) {
                    option.disabled = true;
                }
            }
            
            projectSelect.appendChild(option);
        });
    });
    
    // Xử lý khi chọn dự án/phiếu cho thuê
    document.getElementById('project_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (!selectedOption.value) {
            document.getElementById('customer_details').classList.add('hidden');
            return;
        }
        
        // Kiểm tra xem có phải option đã quá hạn không
        const isExpired = selectedOption.getAttribute('data-is-expired') === 'true';
        if (isExpired) {
            alert('Không thể chọn dự án/phiếu cho thuê đã quá hạn!');
            this.value = '';
            document.getElementById('customer_details').classList.add('hidden');
            return;
        }

        // Lấy thông tin từ data attributes
        const projectName = selectedOption.getAttribute('data-project-name');
        const customerName = selectedOption.getAttribute('data-customer-name');
        const customerPhone = selectedOption.getAttribute('data-customer-phone');
        const customerEmail = selectedOption.getAttribute('data-customer-email');
        const customerAddress = selectedOption.getAttribute('data-customer-address');
        const customerId = selectedOption.getAttribute('data-customer-id');
        const projectType = selectedOption.getAttribute('data-project-type');
        const warrantyEnd = selectedOption.getAttribute('data-warranty-end');
    
        // Xác định loại dự án
        const projectTypeLabel = projectType === 'rental' ? 'Phiếu cho thuê' : 'Dự án';
    
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
        document.getElementById('project_type_display').textContent = projectTypeLabel || 'Không có thông tin';
        document.getElementById('customer_name_display').textContent = customerName || 'Không có thông tin';
        document.getElementById('customer_phone_display').textContent = customerPhone || 'Không có thông tin';
        document.getElementById('customer_email_display').textContent = customerEmail || 'Không có thông tin';
        document.getElementById('customer_address_display').textContent = customerAddress || 'Không có thông tin';
            
        // Hiển thị ngày hết hạn bảo hành
        if (warrantyEnd) {
            const warrantyEndDate = new Date(warrantyEnd);
            document.getElementById('warranty_end_display').textContent = warrantyEndDate.toLocaleDateString('vi-VN');
        } else {
            document.getElementById('warranty_end_display').textContent = 'Không có thông tin';
        }
            
            // Hiển thị div thông tin
        document.getElementById('customer_details').classList.remove('hidden');

        // Gọi API để lấy thiết bị
        loadDevices(projectType, this.value);
    });
    
    // Hàm lấy thiết bị từ API
    function loadDevices(projectType, projectId) {
        if (!projectType || !projectId) {
            document.getElementById('warranty_items_container').classList.add('hidden');
            return;
        }
        
        console.log('=== LOAD DEVICES DEBUG ===');
        console.log('Project Type:', projectType);
        console.log('Project ID:', projectId);
        
        document.getElementById('warranty_items_container').classList.remove('hidden');
        document.getElementById('warranty_items_list').innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Đang tải thiết bị...</td></tr>';
        
        const requestData = {
            project_type: projectType,
            project_id: projectId
        };
        
        console.log('Request Data:', requestData);
        
        fetch('{{ route("requests.maintenance.api.devices") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            console.log('Response Status:', response.status);
            return response.json();
        })
            .then(data => {
            console.log('Response Data:', data);
            
            if (data.error) {
                console.error('API Error:', data.error);
                document.getElementById('warranty_items_list').innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">' + data.error + '</td></tr>';
                return;
            }
            
            if (data.devices && data.devices.length > 0) {
                console.log('Devices found:', data.devices.length);
                data.devices.forEach((device, index) => {
                    console.log(`Device ${index + 1}:`, device);
                });
                displayDevices(data.devices);
            } else {
                console.log('No devices found');
                document.getElementById('warranty_items_list').innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Không có thiết bị nào</td></tr>';
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            document.getElementById('warranty_items_list').innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Có lỗi xảy ra khi tải thiết bị</td></tr>';
        });
    }
    
    // Hàm hiển thị thiết bị trong bảng
    function displayDevices(devices) {
        const tbody = document.getElementById('warranty_items_list');
                    tbody.innerHTML = '';

        devices.forEach(device => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" class="device-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="${device.id}">
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${device.code}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${device.name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${device.serial_number}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${device.type}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">Yêu cầu bảo trì</td>
                            `;
                            tbody.appendChild(row);
                        });
        
        // Reset select all checkbox
        document.getElementById('select_all_devices').checked = false;
    }

    // Xử lý checkbox "Chọn tất cả"
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select_all_devices');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const deviceCheckboxes = document.querySelectorAll('.device-checkbox');
                deviceCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                // Ẩn thông báo lỗi nếu có thiết bị được chọn
                if (this.checked) {
                    document.getElementById('device_selection_error').classList.add('hidden');
                }
            });
        }

        // Xử lý khi chọn từng thiết bị
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('device-checkbox')) {
                const selectedDevices = document.querySelectorAll('.device-checkbox:checked');
                if (selectedDevices.length > 0) {
                    document.getElementById('device_selection_error').classList.add('hidden');
                }
            }
    });
    
    // Kiểm tra nếu đã có dự án được chọn khi tải trang
        const projectTypeSelect = document.getElementById('project_type');
        if (projectTypeSelect.value) {
            // Kích hoạt sự kiện change để hiển thị thông tin
            const event = new Event('change');
            projectTypeSelect.dispatchEvent(event);
        }
    });

    // Validation form trước khi submit
    document.getElementById('submit-btn').addEventListener('click', function(e) {
        const projectId = document.getElementById('project_id').value;
        if (projectId) {
            const selectedDevices = document.querySelectorAll('.device-checkbox:checked');
            if (selectedDevices.length === 0) {
                e.preventDefault();
                document.getElementById('device_selection_error').classList.remove('hidden');
                document.getElementById('warranty_items_container').scrollIntoView({ behavior: 'smooth' });
                return false;
            } else {
                document.getElementById('device_selection_error').classList.add('hidden');
                
                // Tạo hidden input để gửi dữ liệu thiết bị đã chọn
                const selectedDeviceIds = Array.from(selectedDevices).map(checkbox => checkbox.value);
                
                // Xóa hidden input cũ nếu có
                const oldHiddenInput = document.getElementById('selected_devices');
                if (oldHiddenInput) {
                    oldHiddenInput.remove();
                }
                
                // Tạo hidden input mới
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'selected_devices';
                hiddenInput.id = 'selected_devices';
                hiddenInput.value = JSON.stringify(selectedDeviceIds);
                
                // Thêm vào form
                document.querySelector('form').appendChild(hiddenInput);
                
                console.log('Selected devices:', selectedDeviceIds);
            }
        }
    });
</script>
@endsection 