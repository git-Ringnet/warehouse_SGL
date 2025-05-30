<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu đề xuất nhập thêm linh kiện - SGL</title>
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
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Phiếu đề xuất nhập thêm linh kiện</h1>
                <div class="ml-4 px-3 py-1 bg-orange-100 text-orange-800 text-sm rounded-full">
                    Mẫu REQ-CMP
                </div>
            </div>
            <a href="{{ url('/requests') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </header>
        
        <main class="p-6">
            <form action="{{ url('/requests/components') }}" method="POST" class="bg-white rounded-xl shadow-md p-6">
                @csrf
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin đề xuất</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="request_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày đề xuất</label>
                            <input type="date" name="request_date" id="request_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ date('Y-m-d') }}">
                        </div>
                        <div>
                            <label for="technician" class="block text-sm font-medium text-gray-700 mb-1 required">Kỹ thuật đề xuất</label>
                            <input type="text" name="technician" id="technician" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="request_type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại yêu cầu</label>
                            <select name="request_type" id="request_type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Chọn loại yêu cầu --</option>
                                <option value="project">Cho dự án mới</option>
                                <option value="replacement">Thay thế hỏng hóc</option>
                                <option value="stock">Bổ sung tồn kho</option>
                                <option value="repair">Sửa chữa thiết bị</option>
                            </select>
                        </div>
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 mb-1 required">Mức độ ưu tiên</label>
                            <select name="priority" id="priority" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Chọn mức độ ưu tiên --</option>
                                <option value="low">Thấp</option>
                                <option value="medium">Trung bình</option>
                                <option value="high">Cao</option>
                                <option value="urgent">Khẩn cấp</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Lý do đề xuất</h2>
                    <textarea name="reason" id="reason" rows="3" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-lg font-semibold text-gray-800">Danh sách linh kiện</h2>
                        <button type="button" id="add_component" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                            <i class="fas fa-plus-circle mr-1"></i> Thêm linh kiện
                        </button>
                    </div>
                    
                    <div id="component_container" class="mb-3">
                        <div class="component-row grid grid-cols-1 md:grid-cols-12 gap-4 mb-3 pb-3 border-b border-gray-100">
                            <div class="md:col-span-3">
                                <label for="component_name_0" class="block text-sm font-medium text-gray-700 mb-1 required">Tên linh kiện</label>
                                <input type="text" name="components[0][name]" id="component_name_0" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="md:col-span-2">
                                <label for="component_model_0" class="block text-sm font-medium text-gray-700 mb-1 required">Model</label>
                                <input type="text" name="components[0][model]" id="component_model_0" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="md:col-span-2">
                                <label for="component_brand_0" class="block text-sm font-medium text-gray-700 mb-1">Hãng sản xuất</label>
                                <input type="text" name="components[0][brand]" id="component_brand_0" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="md:col-span-1">
                                <label for="component_quantity_0" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                                <input type="number" name="components[0][quantity]" id="component_quantity_0" required min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="md:col-span-2">
                                <label for="component_unit_0" class="block text-sm font-medium text-gray-700 mb-1">Đơn vị tính</label>
                                <input type="text" name="components[0][unit]" id="component_unit_0" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Cái, Bộ, Thùng...">
                            </div>
                            <div class="md:col-span-2 flex items-end">
                                <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group invisible">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin dự án (nếu có)</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="project_name" class="block text-sm font-medium text-gray-700 mb-1">Tên dự án</label>
                            <input type="text" name="project_name" id="project_name" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="partner" class="block text-sm font-medium text-gray-700 mb-1">Đối tác</label>
                            <input type="text" name="partner" id="partner" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thời gian cần nhập</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="expected_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày dự kiến cần nhập</label>
                            <input type="date" name="expected_date" id="expected_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú thêm</label>
                    <textarea name="notes" id="notes" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                
                <div class="flex justify-between">
                    <div>
                        <label for="file_attachments" class="block text-sm font-medium text-gray-700 mb-2">File đính kèm (nếu có)</label>
                        <input type="file" name="file_attachments[]" id="file_attachments" multiple class="block w-full text-sm text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-md file:border-0
                            file:text-sm file:font-medium
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100
                        " />
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="button" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 flex items-center">
                            <i class="fas fa-print mr-2"></i> Xem trước
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center">
                            <i class="fas fa-save mr-2"></i> Lưu phiếu
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <script>
        // Thêm linh kiện
        let componentCount = 1;
        document.getElementById('add_component').addEventListener('click', function() {
            const container = document.getElementById('component_container');
            const newRow = document.createElement('div');
            newRow.className = 'component-row grid grid-cols-1 md:grid-cols-12 gap-4 mb-3 pb-3 border-b border-gray-100';
            newRow.innerHTML = `
                <div class="md:col-span-3">
                    <label for="component_name_${componentCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Tên linh kiện</label>
                    <input type="text" name="components[${componentCount}][name]" id="component_name_${componentCount}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label for="component_model_${componentCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Model</label>
                    <input type="text" name="components[${componentCount}][model]" id="component_model_${componentCount}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label for="component_brand_${componentCount}" class="block text-sm font-medium text-gray-700 mb-1">Hãng sản xuất</label>
                    <input type="text" name="components[${componentCount}][brand]" id="component_brand_${componentCount}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-1">
                    <label for="component_quantity_${componentCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                    <input type="number" name="components[${componentCount}][quantity]" id="component_quantity_${componentCount}" required min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label for="component_unit_${componentCount}" class="block text-sm font-medium text-gray-700 mb-1">Đơn vị tính</label>
                    <input type="text" name="components[${componentCount}][unit]" id="component_unit_${componentCount}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Cái, Bộ, Thùng...">
                </div>
                <div class="md:col-span-2 flex items-end">
                    <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                        <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            componentCount++;
            
            addRemoveRowEventListeners();
        });
        
        // Xóa dòng
        function addRemoveRowEventListeners() {
            document.querySelectorAll('.remove-row').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.component-row').remove();
                });
            });
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            addRemoveRowEventListeners();
            
            // Thiết lập ngày dự kiến ban đầu 
            const today = new Date();
            const nextWeek = new Date();
            nextWeek.setDate(today.getDate() + 7);
            
            document.getElementById('expected_date').value = nextWeek.toISOString().split('T')[0];
        });
    </script>
</body>
</html> 