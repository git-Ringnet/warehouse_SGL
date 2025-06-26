<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa phiếu khách yêu cầu bảo trì - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
        .sidebar {
            background: linear-gradient(180deg, #1a365d 0%, #0f2942 100%);
            transition: all 0.3s ease;
        }
        .content-area {
            margin-left: 256px;
            min-height: 100vh;
           
            transition: margin-left 0.3s ease;
        }
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                z-index: 1000;
                height: 100vh;
                width: 70px;
            }
            .content-area {
                margin-left: 0 !important;
            }
        }
        .required:after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
    <x-sidebar-component />
    
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa phiếu khách yêu cầu bảo trì</h1>
                <div class="ml-4 px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                    Mẫu REQ-CUST-MAINT
                </div>
                <div class="ml-2 px-3 py-1 bg-yellow-100 text-yellow-800 text-sm rounded-full">
                    ID: {{ $id }}
                </div>
            </div>
            <div class="flex space-x-2">
                <a href="{{ url('/requests/customer-maintenance/'.$id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-eye mr-2"></i> Xem phiếu
                </a>
                <a href="{{ url('/requests') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-times mr-2"></i> Hủy
                </a>
            </div>
        </header>
        
        <main class="p-6">
            <form action="{{ url('/requests/customer-maintenance/'.$id) }}" method="POST" class="bg-white rounded-xl shadow-md p-6">
                @csrf
                @method('PATCH')
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin tiếp nhận</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="request_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày tiếp nhận</label>
                            <input type="date" name="request_date" id="request_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('request_date', $request->request_date ? $request->request_date->format('Y-m-d') : date('Y-m-d')) }}">
                        </div>
                        <div>
                            <label for="receiver" class="block text-sm font-medium text-gray-700 mb-1 required">Người tiếp nhận</label>
                            <input type="text" name="receiver" id="receiver" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Nguyễn Văn A">
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
                        <input type="hidden" name="customer_id" value="{{ $request->customer_id }}">
                        <div class="bg-blue-50 rounded-lg p-4 mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên khách hàng/Đơn vị</label>
                                    <p class="text-gray-800">{{ $request->customer ? $request->customer->company_name : $request->customer_name }}</p>
                                    <input type="hidden" name="customer_name" value="{{ $request->customer_name }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại liên hệ</label>
                                    <p class="text-gray-800">{{ $request->customer_phone ?: 'Chưa có thông tin' }}</p>
                                    <input type="hidden" name="customer_phone" value="{{ $request->customer_phone }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                    <p class="text-gray-800">{{ $request->customer_email ?: 'Chưa có thông tin' }}</p>
                                    <input type="hidden" name="customer_email" value="{{ $request->customer_email }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ</label>
                                    <p class="text-gray-800">{{ $request->customer_address ?: 'Chưa có thông tin' }}</p>
                                    <input type="hidden" name="customer_address" value="{{ $request->customer_address }}">
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin thiết bị cần bảo trì</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="device_name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên thiết bị</label>
                            <input type="text" name="device_name" id="device_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Máy chủ Dell PowerEdge R740">
                        </div>
                        <div>
                            <label for="device_model" class="block text-sm font-medium text-gray-700 mb-1">Model/Serial</label>
                            <input type="text" name="device_model" id="device_model" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="SN: ABC12345XYZ">
                        </div>
                        <div class="md:col-span-2">
                            <label for="issue_description" class="block text-sm font-medium text-gray-700 mb-1 required">Mô tả sự cố</label>
                            <textarea name="issue_description" id="issue_description" rows="3" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">Máy chủ gặp lỗi không thể khởi động, đèn báo hiệu lỗi ổ cứng sáng liên tục. Cần kiểm tra và thay thế các linh kiện bị hỏng.</textarea>
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
                            <input type="radio" name="priority" id="priority_medium" value="medium" {{ old('priority', $request->priority) == 'medium' ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
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
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thời gian và chi phí</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="expected_completion_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày hoàn thành dự kiến</label>
                            <input type="date" name="expected_completion_date" id="expected_completion_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('expected_completion_date', $request->expected_completion_date ? $request->expected_completion_date->format('Y-m-d') : '') }}">
                        </div>
                        <div>
                            <label for="estimated_cost" class="block text-sm font-medium text-gray-700 mb-1">Chi phí dự kiến (VNĐ)</label>
                            <input type="number" name="estimated_cost" id="estimated_cost" min="0" step="1000" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('estimated_cost', $request->estimated_cost) }}">
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-lg font-semibold text-gray-800">Yêu cầu vật tư (nếu có)</h2>
                        <button type="button" id="add_material" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                            <i class="fas fa-plus-circle mr-1"></i> Thêm vật tư
                        </button>
                    </div>
                    
                    <div id="material_container">
                        <div class="material-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3">
                            <div class="md:col-span-3">
                                <label for="material_name_0" class="block text-sm font-medium text-gray-700 mb-1">Tên vật tư</label>
                                <input type="text" name="material[0][name]" id="material_name_0" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Ổ cứng SSD 1TB">
                            </div>
                            <div class="md:col-span-1">
                                <label for="material_quantity_0" class="block text-sm font-medium text-gray-700 mb-1">Số lượng</label>
                                <input type="number" name="material[0][quantity]" id="material_quantity_0" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="2">
                            </div>
                            <div class="md:col-span-1 flex items-end">
                                <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </div>
                        </div>
                        <div class="material-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3">
                            <div class="md:col-span-3">
                                <label for="material_name_1" class="block text-sm font-medium text-gray-700 mb-1">Tên vật tư</label>
                                <input type="text" name="material[1][name]" id="material_name_1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Cáp SATA">
                            </div>
                            <div class="md:col-span-1">
                                <label for="material_quantity_1" class="block text-sm font-medium text-gray-700 mb-1">Số lượng</label>
                                <input type="number" name="material[1][quantity]" id="material_quantity_1" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="4">
                            </div>
                            <div class="md:col-span-1 flex items-end">
                                <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Trạng thái phiếu</h2>
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center">
                            <input type="radio" name="status" id="status_pending" value="pending" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <label for="status_pending" class="ml-2 block text-sm font-medium text-gray-700">Chờ xử lý</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" name="status" id="status_processing" value="processing" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" checked>
                            <label for="status_processing" class="ml-2 block text-sm font-medium text-gray-700">Đang xử lý</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" name="status" id="status_completed" value="completed" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <label for="status_completed" class="ml-2 block text-sm font-medium text-gray-700">Hoàn thành</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" name="status" id="status_cancelled" value="cancelled" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <label for="status_cancelled" class="ml-2 block text-sm font-medium text-gray-700">Hủy bỏ</label>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú bổ sung</label>
                    <textarea name="notes" id="notes" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes', $request->notes) }}</textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="copyForm()" class="px-4 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 flex items-center">
                        <i class="fas fa-copy mr-2"></i> Sao chép phiếu
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center">
                        <i class="fas fa-save mr-2"></i> Cập nhật phiếu
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        // Thêm vật tư
        let materialCount = 2; // Bắt đầu từ 2 vì đã có 2 mẫu có sẵn
        document.getElementById('add_material').addEventListener('click', function() {
            const container = document.getElementById('material_container');
            const newRow = document.createElement('div');
            newRow.className = 'material-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3';
            newRow.innerHTML = `
                <div class="md:col-span-3">
                    <label for="material_name_${materialCount}" class="block text-sm font-medium text-gray-700 mb-1">Tên vật tư</label>
                    <input type="text" name="material[${materialCount}][name]" id="material_name_${materialCount}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-1">
                    <label for="material_quantity_${materialCount}" class="block text-sm font-medium text-gray-700 mb-1">Số lượng</label>
                    <input type="number" name="material[${materialCount}][quantity]" id="material_quantity_${materialCount}" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-1 flex items-end">
                    <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                        <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            materialCount++;
            
            addRemoveRowEventListeners();
        });
        
        // Xóa dòng
        function addRemoveRowEventListeners() {
            document.querySelectorAll('.remove-row').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.material-row').remove();
                });
            });
        }
        
        // Sao chép phiếu
        function copyForm() {
            alert('Đã sao chép phiếu này thành một phiếu mới!');
            // Thực tế sẽ lưu dữ liệu form hiện tại và chuyển hướng đến trang tạo phiếu mới với dữ liệu đã được điền sẵn
        }

        // Khởi tạo các event listeners khi trang tải xong
        document.addEventListener('DOMContentLoaded', function() {
            addRemoveRowEventListeners();
        });
    </script>
</body>
</html> 