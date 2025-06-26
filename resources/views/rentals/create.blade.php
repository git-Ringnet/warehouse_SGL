<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo phiếu cho thuê mới - SGL</title>
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
            <h1 class="text-xl font-bold text-gray-800">Tạo phiếu cho thuê</h1>
            <a href="{{ route('rentals.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 h-10 px-4 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </header>
        
        @if(session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif
        @if(session('error'))
            <x-alert type="error" :message="session('error')" />
        @endif
        
        <main class="p-6">
            <form action="{{ route('rentals.store') }}" method="POST" class="bg-white rounded-xl shadow-md p-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Mã phiếu cho thuê -->
                    <div>
                        <label for="rental_code" class="block text-sm font-medium text-gray-700 mb-1">Mã phiếu</label>
                        <input type="text" name="rental_code" id="rental_code" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="RNT-{{ date('ymd') }}{{ rand(100, 999) }}" readonly>
                    </div>

                    <!-- Tên phiếu cho thuê -->
                    <div>
                        <label for="rental_name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên phiếu cho thuê</label>
                        <input type="text" name="rental_name" id="rental_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('rental_name') }}">
                        @error('rental_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Khách hàng -->
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1 required">Khách hàng</label>
                        <select name="customer_id" id="customer_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="getCustomerInfo(this.value)">
                            <option value="">-- Chọn khách hàng --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->company_name }}</option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nhân viên phụ trách -->
                    <div>
                        <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">Nhân viên phụ trách</label>
                        <select name="employee_id" id="employee_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Chọn nhân viên phụ trách --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Thông tin người đại diện -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Người đại diện</label>
                        <div id="customer_info" class="p-3 border border-gray-200 rounded-lg bg-gray-50">
                            <p class="text-sm text-gray-500">Vui lòng chọn khách hàng để hiển thị thông tin</p>
                        </div>
                    </div>

                    <!-- Ngày cho thuê -->
                    <div>
                        <label for="rental_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày cho thuê</label>
                        <input type="date" name="rental_date" id="rental_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('rental_date', date('Y-m-d')) }}">
                        @error('rental_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Ngày hẹn trả -->
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày hẹn trả</label>
                        <input type="date" name="due_date" id="due_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}">
                        @error('due_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Ghi chú -->
                    <div class="col-span-1 md:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea name="notes" id="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <a href="{{ route('rentals.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Hủy
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                        <i class="fas fa-save mr-2"></i> Lưu phiếu cho thuê
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        // Set default dates
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const nextMonth = new Date();
            nextMonth.setMonth(today.getMonth() + 1);

            document.getElementById('rental_date').value = today.toISOString().split('T')[0];
            document.getElementById('due_date').value = nextMonth.toISOString().split('T')[0];
        });

        // Lấy thông tin khách hàng
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
                                <p class="text-sm"><span class="font-semibold">Số điện thoại:</span> ${customer.phone || 'N/A'}</p>
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

        // Kiểm tra nếu đã có khách hàng được chọn từ trước, hiển thị thông tin
        document.addEventListener('DOMContentLoaded', function() {
            const selectedCustomerId = document.getElementById('customer_id').value;
            if (selectedCustomerId) {
                getCustomerInfo(selectedCustomerId);
            }
        });
    </script>
</body>
</html> 