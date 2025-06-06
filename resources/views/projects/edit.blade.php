<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa dự án - SGL</title>
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
            background: #f8fafc;
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
        @if(session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif
        @if(session('error'))
            <x-alert type="error" :message="session('error')" />
        @endif
        
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa dự án</h1>
                <div class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    PRJ-2406001
                </div>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('projects.show', $project->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 h-10 px-4 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-eye mr-2"></i> Xem chi tiết
                </a>
                <a href="{{ route('projects.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 h-10 px-4 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
            </div>
        </header>

        <main class="p-6">
            <form action="{{ route('projects.update', $project->id) }}" method="POST" class="bg-white rounded-xl shadow-md p-6">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Mã dự án -->
                    <div>
                        <label for="project_code" class="block text-sm font-medium text-gray-700 mb-1">Mã dự án</label>
                        <input type="text" name="project_code" id="project_code" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('project_code', $project->project_code) }}">
                    </div>

                    <!-- Tên dự án -->
                    <div>
                        <label for="project_name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên dự án</label>
                        <input type="text" name="project_name" id="project_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('project_name', $project->project_name) }}">
                    </div>

                    <!-- Khách hàng -->
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1 required">Khách hàng</label>
                        <select name="customer_id" id="customer_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="getCustomerInfo(this.value)">
                            <option value="">-- Chọn khách hàng --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', $project->customer_id) == $customer->id ? 'selected' : '' }}>{{ $customer->company_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Thông tin người đại diện -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Người đại diện</label>
                        <div id="customer_info" class="p-3 border border-gray-200 rounded-lg bg-gray-50">
                            <p class="text-sm text-gray-500">Đang tải thông tin...</p>
                        </div>
                    </div>

                    <!-- Ngày bắt đầu -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày bắt đầu</label>
                        <input type="date" name="start_date" id="start_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('start_date', $project->start_date) }}">
                    </div>

                    <!-- Ngày kết thúc dự kiến -->
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày kết thúc dự kiến</label>
                        <input type="date" name="end_date" id="end_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('end_date', $project->end_date) }}">
                    </div>

                    <!-- Thời gian bảo hành (tháng) -->
                    <div>
                        <label for="warranty_period" class="block text-sm font-medium text-gray-700 mb-1 required">Thời gian bảo hành (tháng)</label>
                        <input type="number" name="warranty_period" id="warranty_period" required min="1" max="36" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('warranty_period', $project->warranty_period) }}">
                    </div>

                    <!-- Mô tả -->
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                        <textarea name="description" id="description" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $project->description) }}</textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('projects.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Hủy
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                        <i class="fas fa-save mr-2"></i> Lưu thay đổi
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        function getCustomerInfo(customerId) {
            if (!customerId) {
                document.getElementById('customer_info').innerHTML = '<p class="text-sm text-gray-500">Vui lòng chọn khách hàng để hiển thị thông tin</p>';
                return;
            }
            
            fetch(`/api/customers/${customerId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const customer = data.data;
                        let html = `
                            <div class="space-y-1">
                                <p class="text-sm"><span class="font-semibold">Tên:</span> ${customer.name}</p>
                                <p class="text-sm"><span class="font-semibold">Số điện thoại:</span> ${customer.phone}</p>
                                <p class="text-sm"><span class="font-semibold">Email:</span> ${customer.email || 'N/A'}</p>
                                <p class="text-sm"><span class="font-semibold">Địa chỉ:</span> ${customer.address || 'N/A'}</p>
                            </div>
                        `;
                        document.getElementById('customer_info').innerHTML = html;
                    } else {
                        document.getElementById('customer_info').innerHTML = '<p class="text-sm text-red-500">Không thể lấy thông tin khách hàng</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('customer_info').innerHTML = '<p class="text-sm text-red-500">Đã xảy ra lỗi khi lấy thông tin khách hàng</p>';
                });
        }

        // Tải thông tin khách hàng khi trang được tải
        document.addEventListener('DOMContentLoaded', function() {
            const selectedCustomerId = document.getElementById('customer_id').value;
            if (selectedCustomerId) {
                getCustomerInfo(selectedCustomerId);
            }
        });
    </script>
</body>
</html> 