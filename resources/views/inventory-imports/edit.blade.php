<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa phiếu nhập kho - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/delete-modal.js') }}"></script>
</head>
<body>
    <x-sidebar-component />
    
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa phiếu nhập kho</h1>
                <div class="ml-4 px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    Mã phiếu: NK001
                </div>
            </div>
            <a href="{{ url('/inventory-imports/1') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 h-10 px-4 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </header>

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <form action="#" method="POST">
                    <h2 class="text-lg font-semibold text-gray-800 mb-6">Thông tin phiếu nhập kho</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Cột 1 -->
                        <div class="space-y-4">
                            <div>
                                <label for="material_code" class="block text-sm font-medium text-gray-700 mb-1 required">Mã vật tư</label>
                                <input type="text" id="material_code" name="material_code" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="VT001" required>
                            </div>
                            
                            <div>
                                <label for="material_name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên vật tư</label>
                                <input type="text" id="material_name" name="material_name" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="Ốc vít 10mm" required>
                            </div>
                            
                            <div>
                                <label for="unit" class="block text-sm font-medium text-gray-700 mb-1 required">Đơn vị</label>
                                <select id="unit" name="unit" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn đơn vị --</option>
                                    <option value="kg" selected>Kg</option>
                                    <option value="cai">Cái</option>
                                    <option value="met">Mét</option>
                                    <option value="lit">Lít</option>
                                    <option value="bo">Bộ</option>
                                    <option value="thung">Thùng</option>
                                    <option value="hop">Hộp</option>
                                    <option value="tuyp">Tuýp</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-1 required">Phân loại</label>
                                <select id="category" name="category" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn phân loại --</option>
                                    <option value="linh_kien" selected>Linh kiện</option>
                                    <option value="vat_tu">Vật tư</option>
                                    <option value="dien">Điện</option>
                                    <option value="co_khi">Cơ khí</option>
                                    <option value="hoa_chat">Hóa chất</option>
                                    <option value="thiet_bi">Thiết bị</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Cột 2 -->
                        <div class="space-y-4">
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                                <input type="number" id="quantity" name="quantity" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="100" min="1" required>
                            </div>
                            
                            <div>
                                <label for="import_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày nhập kho</label>
                                <input type="date" id="import_date" name="import_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="2024-06-01" required>
                            </div>
                            
                            <div>
                                <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1 required">Nhà cung cấp</label>
                                <select id="supplier_id" name="supplier_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn nhà cung cấp --</option>
                                    <option value="1" selected>Công ty TNHH ABC</option>
                                    <option value="2">Công ty Vật liệu XYZ</option>
                                    <option value="3">Công ty Điện máy MNO</option>
                                    <option value="4">Công ty Hóa chất LKM</option>
                                    <option value="5">Công ty TNHH PQR</option>
                                </select>
                                <div class="mt-2 text-sm">
                                    <a href="{{ url('/suppliers/create') }}" class="text-blue-500 hover:text-blue-700">
                                        <i class="fas fa-plus-circle mr-1"></i>Thêm nhà cung cấp mới
                                    </a>
                                </div>
                            </div>
                            
                          
                        </div>
                    </div>
                    
                    <!-- Ghi chú -->
                    <div class="mt-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea id="notes" name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">Nhập đợt 1</textarea>
                    </div>
                    
                    <div class="mt-6">
                        <h3 class="text-md font-semibold text-gray-800 mb-2">Thông tin bổ sung</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label for="warehouse" class="block text-sm font-medium text-gray-700 mb-1">Kho nhập</label>
                                    <select id="warehouse" name="warehouse" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                        <option value="">-- Chọn kho --</option>
                                        <option value="main" selected>Kho chính</option>
                                        <option value="secondary">Kho phụ</option>
                                        <option value="components">Kho linh kiện</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="order_code" class="block text-sm font-medium text-gray-700 mb-1">Mã đơn hàng</label>
                                    <input type="text" id="order_code" name="order_code" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="ĐH-2024-06-01">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-3">
                        <a href="{{ url('/inventory-imports/1') }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center justify-center transition-colors">
                            Hủy
                        </a>
                        <button type="submit" class="h-10 bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fas fa-save mr-2"></i> Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
            
          
        </main>
    </div>
</body>
</html> 