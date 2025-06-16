<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa phiếu kiểm thử - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>
<body>
    <x-sidebar-component />
    
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa phiếu kiểm thử</h1>
                <div class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    {{ $testing->test_code }}
                </div>
                <div class="ml-2 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    {{ $testing->test_type_text }}
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('testing.show', $testing->id) }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
            </div>
        </header>

        <main class="p-6">
            @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded" role="alert">
                <p>{{ session('success') }}</p>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">
                <p>{{ session('error') }}</p>
            </div>
            @endif

            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <form action="{{ route('testing.update', $testing->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Thông tin cơ bản -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2">Thông tin cơ bản</h2>
                        
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="test_type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại kiểm thử</label>
                                <select id="test_type" name="test_type" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required onchange="toggleTestTypeFields()" {{ $testing->status != 'pending' ? 'disabled' : '' }}>
                                    <option value="material" {{ $testing->test_type == 'material' ? 'selected' : '' }}>Kiểm thử Vật tư/Hàng hóa</option>
                                    <option value="finished_product" {{ $testing->test_type == 'finished_product' ? 'selected' : '' }}>Kiểm thử Thiết bị thành phẩm</option>
                                </select>
                            </div>

                            <div>
                                <label for="tester_id" class="block text-sm font-medium text-gray-700 mb-1 required">Người tạo phiếu</label>
                                <select id="tester_id" name="tester_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ $testing->tester_id == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1 required">Người phụ trách</label>
                                <select id="assigned_to" name="assigned_to" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ $testing->assigned_to == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label for="receiver_id" class="block text-sm font-medium text-gray-700 mb-1 required">Người tiếp nhận kiểm thử</label>
                                <select id="receiver_id" name="receiver_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ $testing->receiver_id == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="test_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày kiểm thử</label>
                                <input type="date" id="test_date" name="test_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ $testing->test_date->format('Y-m-d') }}" required>
                            </div>
                        </div>
                        
                        <!-- Bảng tổng hợp vật tư đã thêm -->
                        <div class="mt-6">
                            <h3 class="text-md font-medium text-gray-800 mb-3">Tổng hợp vật tư, hàng hoá đã thêm</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white border border-gray-200">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">STT</th>
                                            <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">LOẠI</th>
                                            <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">MÃ - TÊN SẢN PHẨM</th>
                                            <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">NHÀ CUNG CẤP</th>
                                            <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">MÃ LÔ</th>
                                            <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">SERIAL</th>
                                            <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">ĐƠN VỊ</th>
                                            <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">SỐ LƯỢNG</th>
                                        </tr>
                                    </thead>
                                    <tbody id="items-summary-table">
                                        @forelse($testing->items as $index => $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-2 px-3 border-b border-gray-200">{{ $index + 1 }}</td>
                                            <td class="py-2 px-3 border-b border-gray-200">
                                                @if($item->item_type == 'material')
                                                    Vật tư
                                                @elseif($item->item_type == 'product')
                                                    Thành phẩm
                                                @elseif($item->item_type == 'finished_product')
                                                    Hàng hóa
                                                @endif
                                            </td>
                                            <td class="py-2 px-3 border-b border-gray-200">
                                                @if($item->item_type == 'material' && $item->material)
                                                    {{ $item->material->code }} - {{ $item->material->name }}
                                                @elseif($item->item_type == 'product' && $item->product)
                                                    {{ $item->product->code }} - {{ $item->product->name }}
                                                @elseif($item->item_type == 'finished_product' && $item->good)
                                                    {{ $item->good->code }} - {{ $item->good->name }}
                                                @endif
                                            </td>
                                            <td class="py-2 px-3 border-b border-gray-200">
                                                {{ $item->supplier ? $item->supplier->name : 'N/A' }}
                                            </td>
                                            <td class="py-2 px-3 border-b border-gray-200">{{ $item->batch_number ?: 'N/A' }}</td>
                                            <td class="py-2 px-3 border-b border-gray-200">{{ $item->serial_number ?: 'N/A' }}</td>
                                            <td class="py-2 px-3 border-b border-gray-200">
                                                @if($item->item_type == 'material' && $item->material)
                                                    {{ $item->material->unit }}
                                                @elseif($item->item_type == 'product' && $item->product)
                                                    {{ $item->product->unit }}
                                                @elseif($item->item_type == 'finished_product' && $item->good)
                                                    {{ $item->good->unit }}
                                                @endif
                                            </td>
                                            <td class="py-2 px-3 border-b border-gray-200">{{ $item->quantity }}</td>
                                        </tr>
                                        @empty
                                        <tr class="text-gray-500 text-center">
                                            <td colspan="8" class="py-4">Chưa có vật tư/hàng hóa nào được thêm</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Thông tin chi tiết các mục kiểm thử -->
                        <div class="mt-6">
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="text-md font-medium text-gray-800">Hạng mục kiểm thử</h3>
                                <button type="button" onclick="addTestItem()" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm flex items-center">
                                    <i class="fas fa-plus mr-1"></i> Thêm hạng mục
                                </button>
                            </div>
                            
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div id="test_items_container" class="space-y-3">
                                    @forelse($testing->details as $detail)
                                        <div class="test-item flex items-center gap-4">
                                            <input type="text" name="test_item_names[]" class="h-10 border border-gray-300 rounded px-3 py-2 flex-grow focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ $detail->test_item_name }}" placeholder="Nhập hạng mục kiểm thử">
                                            <select name="test_results[]" class="h-10 border border-gray-300 rounded px-3 py-2 w-32 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                                <option value="pending" {{ $detail->result == 'pending' ? 'selected' : '' }}>Chưa có</option>
                                                <option value="pass" {{ $detail->result == 'pass' ? 'selected' : '' }}>Đạt</option>
                                                <option value="fail" {{ $detail->result == 'fail' ? 'selected' : '' }}>Không đạt</option>
                                            </select>
                                            <input type="text" name="test_notes[]" class="h-10 border border-gray-300 rounded px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ $detail->notes }}" placeholder="Ghi chú">
                                            <button type="button" onclick="removeTestItem(this)" class="px-3 py-1 bg-red-100 text-red-500 rounded hover:bg-red-200">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    @empty
                                        <div class="test-item flex items-center gap-4">
                                            <input type="text" name="test_item_names[]" class="h-10 border border-gray-300 rounded px-3 py-2 flex-grow focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập hạng mục kiểm thử">
                                            <select name="test_results[]" class="h-10 border border-gray-300 rounded px-3 py-2 w-32 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                                <option value="pending">Chưa có</option>
                                                <option value="pass">Đạt</option>
                                                <option value="fail">Không đạt</option>
                                            </select>
                                            <input type="text" name="test_notes[]" class="h-10 border border-gray-300 rounded px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Ghi chú">
                                            <button type="button" onclick="removeTestItem(this)" class="px-3 py-1 bg-red-100 text-red-500 rounded hover:bg-red-200">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Kết quả kiểm thử -->
                        <div class="mt-6">
                            <h3 class="text-md font-medium text-gray-800 mb-3">Kết quả kiểm thử</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="pass_quantity" class="block text-sm font-medium text-gray-700 mb-1">Số lượng đạt</label>
                                    <input type="number" id="pass_quantity" name="pass_quantity" min="0" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ $testing->pass_quantity }}">
                                </div>
                                
                                <div>
                                    <label for="fail_quantity" class="block text-sm font-medium text-gray-700 mb-1">Số lượng không đạt</label>
                                    <input type="number" id="fail_quantity" name="fail_quantity" min="0" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ $testing->fail_quantity }}">
                                </div>
                        </div>
                        
                        <div class="mt-4">
                                <label for="fail_reasons" class="block text-sm font-medium text-gray-700 mb-1">Lý do không đạt</label>
                                <textarea id="fail_reasons" name="fail_reasons" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">{{ $testing->fail_reasons }}</textarea>
                            </div>

                            <div class="mt-4">
                                <label for="conclusion" class="block text-sm font-medium text-gray-700 mb-1">Kết luận</label>
                                <textarea id="conclusion" name="conclusion" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">{{ $testing->conclusion }}</textarea>
                            </div>
                        </div>

                        <!-- Ghi chú -->
                        <div class="mt-4">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                            <textarea id="notes" name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập ghi chú bổ sung nếu có">{{ $testing->notes }}</textarea>
                        </div>
                    </div>

                    <!-- Submit buttons -->
                    <div class="flex justify-end space-x-2">
                        <a href="{{ route('testing.show', $testing->id) }}" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">Hủy</a>
                        <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function addTestItem() {
            const container = document.getElementById('test_items_container');
            const newItem = document.createElement('div');
            newItem.className = 'test-item flex items-center gap-4';
            newItem.innerHTML = `
                <input type="text" name="test_item_names[]" class="h-10 border border-gray-300 rounded px-3 py-2 flex-grow focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập hạng mục kiểm thử">
                <select name="test_results[]" class="h-10 border border-gray-300 rounded px-3 py-2 w-32 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="pending">Chưa có</option>
                        <option value="pass">Đạt</option>
                        <option value="fail">Không đạt</option>
                    </select>
                <input type="text" name="test_notes[]" class="h-10 border border-gray-300 rounded px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Ghi chú">
                <button type="button" onclick="removeTestItem(this)" class="px-3 py-1 bg-red-100 text-red-500 rounded hover:bg-red-200">
                        <i class="fas fa-trash"></i>
                    </button>
            `;
            container.appendChild(newItem);
            
            // Thêm event listener cho select kết quả mới
            const newSelect = newItem.querySelector('select[name="test_results[]"]');
            if (newSelect) {
                newSelect.addEventListener('change', updateTestResults);
            }
        }
        
        function removeTestItem(button) {
            const container = document.getElementById('test_items_container');
            const item = button.closest('.test-item');
            
            // Don't remove if it's the last one
            if (container.children.length > 1) {
                container.removeChild(item);
                
                // Cập nhật lại kết quả sau khi xóa
                updateTestResults();
            }
        }
        
        function toggleTestTypeFields() {
            const testType = document.getElementById('test_type').value;
            const materialFields = document.getElementById('material_fields');
            const finishedProductFields = document.getElementById('finished_product_fields');
            
            if (testType === 'material') {
                if (materialFields) materialFields.classList.remove('hidden');
                if (finishedProductFields) finishedProductFields.classList.add('hidden');
            } else if (testType === 'finished_product') {
                if (materialFields) materialFields.classList.add('hidden');
                if (finishedProductFields) finishedProductFields.classList.remove('hidden');
            }
        }
        
        // Hàm cập nhật danh sách sản phẩm theo loại
        function updateItemOptions(selectElement, index) {
            const itemType = selectElement.value;
            const itemNameSelect = document.getElementById('item_name_' + index);
            const supplierContainer = selectElement.closest('.grid').querySelector(`select[name="items[${index}][supplier_id]"]`).closest('div');
            const supplierSelect = selectElement.closest('.grid').querySelector(`select[name="items[${index}][supplier_id]"]`);
            const serialInput = selectElement.closest('.item-row').querySelector(`input[name="items[${index}][serial_number]"]`);
            
            // Xóa container serial cũ nếu có
            const oldSerialContainer = selectElement.closest('.item-row').querySelector('.serial-container');
            if (oldSerialContainer) {
                oldSerialContainer.remove();
            }
            
            // Clear existing options
            itemNameSelect.innerHTML = '<option value="">-- Chọn --</option>';
            
            // Hiển thị/ẩn trường nhà cung cấp dựa vào loại item
            if (itemType === 'product') {
                supplierContainer.style.display = 'none';
            } else {
                supplierContainer.style.display = 'block';
            }
            
            if (!itemType) return;
            
            // Fetch items based on type
            fetch(`/api/testing/materials-by-type?type=${itemType}&search=`)
                .then(response => response.json())
                .then(items => {
                    // Xóa tất cả options cũ
                    itemNameSelect.innerHTML = '<option value="">-- Chọn --</option>';
                    
                    items.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.name;
                        option.dataset.code = item.code || '';
                        itemNameSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching items:', error));
                
            // Thêm event listener cho việc chọn item
            itemNameSelect.onchange = function() {
                const selectedItemId = this.value;
                if (selectedItemId) {
                    console.log(`Fetching details for ${itemType} with ID ${selectedItemId}`);
                    
                    // Lấy thông tin nhà cung cấp của item
                    fetch(`/api/items/${itemType}/${selectedItemId}`)
                        .then(response => response.json())
                        .then(data => {
                            console.log('Item details:', data);
                            if (data.supplier_id) {
                                // Tìm option có value là supplier_id và chọn nó
                                const supplierOption = supplierSelect.querySelector(`option[value="${data.supplier_id}"]`);
                                if (supplierOption) {
                                    supplierSelect.value = data.supplier_id;
                                    // Thêm thông báo đã tự động chọn nhà cung cấp
                                    const notification = document.createElement('div');
                                    notification.className = 'text-sm text-green-600 mt-1';
                                    notification.textContent = `Đã tự động chọn: ${data.supplier_name || supplierOption.textContent}`;
                                    const existingNotification = supplierSelect.parentNode.querySelector('.text-green-600');
                                    if (existingNotification) {
                                        existingNotification.remove();
                                    }
                                    supplierSelect.parentNode.appendChild(notification);
                                    
                                    // Tự động ẩn thông báo sau 3 giây
                                    setTimeout(() => {
                                        if (notification.parentNode) {
                                            notification.remove();
                                        }
                                    }, 3000);
                                }
                            }
                        })
                        .catch(error => console.error('Error fetching item details:', error));
                        
                    // Lấy danh sách serial có sẵn
                    fetch(`/api/testing/serial-numbers?type=${itemType}&id=${selectedItemId}`)
                        .then(response => response.json())
                        .then(serials => {
                            console.log('Serials:', serials);
                            
                            // Xóa container serial cũ nếu có
                            const oldSerialContainer = selectElement.closest('.item-row').querySelector('.serial-container');
                            if (oldSerialContainer) {
                                oldSerialContainer.remove();
                            }
                            
                            // Hiển thị danh sách serial nếu có
                            if (serials && serials.length > 0) {
                                // Tạo container cho danh sách serial
                                const serialContainerDiv = document.createElement('div');
                                serialContainerDiv.className = 'serial-container bg-gray-50 border border-gray-200 rounded-lg p-3 mb-3';
                                
                                // Tạo label
                                const label = document.createElement('label');
                                label.className = 'block text-sm font-medium text-gray-700 mb-2';
                                label.textContent = 'Chọn serial có sẵn:';
                                serialContainerDiv.appendChild(label);
                                
                                // Tạo select
                                const serialSelect = document.createElement('select');
                                serialSelect.className = 'w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white mb-2';
                                
                                // Thêm option mặc định
                                const defaultOption = document.createElement('option');
                                defaultOption.value = '';
                                defaultOption.textContent = '-- Chọn serial --';
                                serialSelect.appendChild(defaultOption);
                                
                                // Thêm các options từ danh sách serial
                                serials.forEach(serial => {
                                    const option = document.createElement('option');
                                    option.value = serial;
                                    option.textContent = serial;
                                    serialSelect.appendChild(option);
                                });
                                
                                // Thêm event listener cho select
                                serialSelect.onchange = function() {
                                    serialInput.value = this.value;
                                };
                                
                                serialContainerDiv.appendChild(serialSelect);
                                
                                // Thêm container vào trước input serial
                                const serialInputContainer = serialInput.parentNode;
                                serialInputContainer.insertBefore(serialContainerDiv, serialInput);
                            }
                        })
                        .catch(error => console.error('Error fetching serials:', error));
                }
            };
        }
        
        // Initialize fields based on selected test type
        document.addEventListener('DOMContentLoaded', function() {
            toggleTestTypeFields();
            
            // Khởi tạo cho các item hiện có
            document.querySelectorAll('.item-type').forEach((itemTypeSelect, index) => {
                // Khởi tạo hiển thị/ẩn trường nhà cung cấp
                const supplierContainer = itemTypeSelect.closest('.grid').querySelector(`select[name="items[${index}][supplier_id]"]`).closest('div');
                if (itemTypeSelect.value === 'product') {
                    supplierContainer.style.display = 'none';
                }
                
                itemTypeSelect.addEventListener('change', function() {
                    updateItemOptions(this, index);
                });
                
                // Nếu đã có giá trị, kích hoạt cập nhật
                if (itemTypeSelect.value) {
                    updateItemOptions(itemTypeSelect, index);
                }
                
                // Thêm sự kiện cho select tên sản phẩm
                const itemNameSelect = document.getElementById('item_name_' + index);
                if (itemNameSelect) {
                    itemNameSelect.onchange = function() {
                        const itemType = itemTypeSelect.value;
                        const selectedItemId = this.value;
                        const supplierSelect = this.closest('.grid').querySelector(`select[name="items[${index}][supplier_id]"]`);
                        const serialInput = this.closest('.item-row').querySelector(`input[name="items[${index}][serial_number]"]`);
                        
                        if (selectedItemId) {
                            console.log(`Fetching details for ${itemType} with ID ${selectedItemId}`);
                            
                            // Lấy thông tin nhà cung cấp của item
                            fetch(`/api/items/${itemType}/${selectedItemId}`)
                                .then(response => response.json())
                                .then(data => {
                                    console.log('Item details:', data);
                                    if (data.supplier_id) {
                                        // Tìm option có value là supplier_id và chọn nó
                                        const supplierOption = supplierSelect.querySelector(`option[value="${data.supplier_id}"]`);
                                        if (supplierOption) {
                                            supplierSelect.value = data.supplier_id;
                                            // Thêm thông báo đã tự động chọn nhà cung cấp
                                            const notification = document.createElement('div');
                                            notification.className = 'text-sm text-green-600 mt-1';
                                            notification.textContent = `Đã tự động chọn: ${data.supplier_name || supplierOption.textContent}`;
                                            const existingNotification = supplierSelect.parentNode.querySelector('.text-green-600');
                                            if (existingNotification) {
                                                existingNotification.remove();
                                            }
                                            supplierSelect.parentNode.appendChild(notification);
                                            
                                            // Tự động ẩn thông báo sau 3 giây
                                            setTimeout(() => {
                                                if (notification.parentNode) {
                                                    notification.remove();
                                                }
                                            }, 3000);
                                        }
                                    }
                                })
                                .catch(error => console.error('Error fetching item details:', error));
                                
                            // Lấy danh sách serial có sẵn
                            fetch(`/api/testing/serial-numbers?type=${itemType}&id=${selectedItemId}`)
                                .then(response => response.json())
                                .then(serials => {
                                    console.log('Serials:', serials);
                                    
                                    // Xóa container serial cũ nếu có
                                    const oldSerialContainer = this.closest('.item-row').querySelector('.serial-container');
                                    if (oldSerialContainer) {
                                        oldSerialContainer.remove();
                                    }
                                    
                                    // Hiển thị danh sách serial nếu có
                                    if (serials && serials.length > 0) {
                                        // Tạo container cho danh sách serial
                                        const serialContainerDiv = document.createElement('div');
                                        serialContainerDiv.className = 'serial-container bg-gray-50 border border-gray-200 rounded-lg p-3 mb-3';
                                        
                                        // Tạo label
                                        const label = document.createElement('label');
                                        label.className = 'block text-sm font-medium text-gray-700 mb-2';
                                        label.textContent = 'Chọn serial có sẵn:';
                                        serialContainerDiv.appendChild(label);
                                        
                                        // Tạo select
                                        const serialSelect = document.createElement('select');
                                        serialSelect.className = 'w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white mb-2';
                                        
                                        // Thêm option mặc định
                                        const defaultOption = document.createElement('option');
                                        defaultOption.value = '';
                                        defaultOption.textContent = '-- Chọn serial --';
                                        serialSelect.appendChild(defaultOption);
                                        
                                        // Thêm các options từ danh sách serial
                                        serials.forEach(serial => {
                                            const option = document.createElement('option');
                                            option.value = serial;
                                            option.textContent = serial;
                                            serialSelect.appendChild(option);
                                        });
                                        
                                        // Thêm event listener cho select
                                        serialSelect.onchange = function() {
                                            serialInput.value = this.value;
                                        };
                                        
                                        serialContainerDiv.appendChild(serialSelect);
                                        
                                        // Thêm container vào trước input serial
                                        const serialInputContainer = serialInput.parentNode;
                                        serialInputContainer.insertBefore(serialContainerDiv, serialInput);
                                    }
                                })
                                .catch(error => console.error('Error fetching serials:', error));
                        }
                    };
                }
            });
        });

        // Tự động tính toán số lượng đạt/không đạt dựa trên kết quả kiểm thử
        function updateTestResults() {
            const testResultSelects = document.querySelectorAll('select[name="test_results[]"]');
            const passQuantityInput = document.getElementById('pass_quantity');
            const failQuantityInput = document.getElementById('fail_quantity');
            const failReasonsTextarea = document.getElementById('fail_reasons');
            
            let passCount = 0;
            let failCount = 0;
            let failReasons = [];
            
            // Đếm số lượng đạt/không đạt
            testResultSelects.forEach((select, index) => {
                if (select.value === 'pass') {
                    passCount++;
                } else if (select.value === 'fail') {
                    failCount++;
                    
                    // Lấy tên hạng mục kiểm thử và ghi chú
                    const testItemName = document.querySelectorAll('input[name="test_item_names[]"]')[index].value;
                    const testNote = document.querySelectorAll('input[name="test_notes[]"]')[index].value;
                    
                    if (testItemName) {
                        failReasons.push(`${testItemName}${testNote ? ': ' + testNote : ''}`);
                    }
                }
            });
            
            // Cập nhật giá trị
            if (passQuantityInput) passQuantityInput.value = passCount;
            if (failQuantityInput) failQuantityInput.value = failCount;
            
            // Cập nhật lý do không đạt
            if (failReasonsTextarea && failReasons.length > 0) {
                failReasonsTextarea.value = failReasons.join('\n');
            }
            
            // Tính phần trăm
            const total = passCount + failCount;
            const passPercent = total > 0 ? Math.round((passCount / total) * 100) : 0;
            const failPercent = total > 0 ? Math.round((failCount / total) * 100) : 0;
            
            // Tự động cập nhật kết luận
            const conclusionTextarea = document.getElementById('conclusion');
            if (conclusionTextarea && total > 0) {
                let conclusion = '';
                
                if (passPercent >= 80) {
                    conclusion = `Kết quả kiểm thử đạt yêu cầu với ${passPercent}% hạng mục đạt tiêu chuẩn.`;
                } else if (passPercent >= 50) {
                    conclusion = `Kết quả kiểm thử đạt mức trung bình với ${passPercent}% hạng mục đạt tiêu chuẩn. Cần cải thiện các hạng mục không đạt.`;
                } else {
                    conclusion = `Kết quả kiểm thử không đạt yêu cầu với chỉ ${passPercent}% hạng mục đạt tiêu chuẩn. Cần kiểm tra lại toàn bộ.`;
                }
                
                if (failReasons.length > 0) {
                    conclusion += ` Các hạng mục cần khắc phục: ${failReasons.join(', ')}.`;
                }
                
                conclusionTextarea.value = conclusion;
            }
        }
        
        // Thêm event listener cho các select kết quả kiểm thử
        document.addEventListener('DOMContentLoaded', function() {
            const testResultSelects = document.querySelectorAll('select[name="test_results[]"]');
            testResultSelects.forEach(select => {
                select.addEventListener('change', updateTestResults);
            });
            
            // Thêm event listener cho nút thêm hạng mục kiểm thử
            const addTestItemButton = document.querySelector('button[onclick="addTestItem()"]');
            if (addTestItemButton) {
                const originalAddTestItem = window.addTestItem;
                window.addTestItem = function() {
                    originalAddTestItem();
                    
                    // Thêm event listener cho select kết quả mới
                    const newTestResultSelect = document.querySelector('#test_items_container .test-item:last-child select');
                    if (newTestResultSelect) {
                        newTestResultSelect.addEventListener('change', updateTestResults);
                    }
                };
            }
            
            // Chạy lần đầu để cập nhật giá trị ban đầu
            updateTestResults();
        });
    </script>
</body>
</html> 
