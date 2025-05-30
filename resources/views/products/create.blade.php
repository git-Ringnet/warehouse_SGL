<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm thành phẩm mới - SGL</title>
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
                <a href="{{ asset('products') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Thêm thành phẩm mới</h1>
            </div>
        </header>

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mx-auto">
                <form action="#" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700 mb-1 required">Mã sản phẩm <span class="text-red-500">*</span></label>
                                <input type="text" id="code" name="code" placeholder="SP-XXXX" required 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label for="serial" class="block text-sm font-medium text-gray-700 mb-1 required">Serial thành phẩm <span class="text-red-500">*</span></label>
                                <input type="text" id="serial" name="serial" placeholder="SER-XXXX" required 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên sản phẩm <span class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" placeholder="Nhập tên sản phẩm" required 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700 mb-1 required">Vị trí hiện tại <span class="text-red-500">*</span></label>
                                <select id="location" name="location" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Chọn vị trí</option>
                                    <option value="Kho Hà Nội">Kho Hà Nội</option>
                                    <option value="Kho Hồ Chí Minh">Kho Hồ Chí Minh</option>
                                    <option value="Kho Đà Nẵng">Kho Đà Nẵng</option>
                                    <option value="Dự án ABC">Dự án ABC</option>
                                    <option value="Dự án XYZ">Dự án XYZ</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại sản phẩm <span class="text-red-500">*</span></label>
                                <select id="type" name="type" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="Mới" selected>Mới</option>
                                    <option value="Bảo hành">Bảo hành</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Mô tả sản phẩm</label>
                            <textarea id="description" name="description" rows="3" placeholder="Nhập mô tả chi tiết về sản phẩm"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end space-x-3">
                        <a href="{{ asset('products') }}" class="bg-gray-200 text-gray-700 px-5 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                            Hủy
                        </a>
                        <button type="submit" class="bg-blue-500 text-white px-5 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                            <i class="fas fa-save mr-2"></i> Lưu sản phẩm
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const materialSelect = document.getElementById('material_select');
            const materialQuantity = document.getElementById('material_quantity');
            const addMaterialBtn = document.getElementById('add_material');
            const materialsTable = document.getElementById('materials_table');
            
            // Danh sách vật tư để hiển thị tên
            const materials = {
                'VT-0001': 'Vỏ thiết bị Radio SPA',
                'VT-0002': 'Module GPS NEO-6M',
                'VT-0003': 'Bo mạch điều khiển',
                'VT-0004': 'Ăng-ten thu phát',
                'VT-0005': 'Pin Lithium 2000mAh'
            };
            
            // Tạo mã serial ngẫu nhiên
            function generateSerial(prefix) {
                return prefix + '-' + Math.floor(Math.random() * 1000000).toString().padStart(6, '0');
            }
            
            // Thêm vật tư vào bảng
            addMaterialBtn.addEventListener('click', function() {
                const materialId = materialSelect.value;
                const quantity = materialQuantity.value;
                
                if (!materialId) {
                    alert('Vui lòng chọn vật tư');
                    return;
                }
                
                // Kiểm tra vật tư đã tồn tại chưa
                const existingRow = document.querySelector(`tr[data-material-id="${materialId}"]`);
                if (existingRow) {
                    alert('Vật tư này đã được thêm vào danh sách');
                    return;
                }
                
                const row = document.createElement('tr');
                row.setAttribute('data-material-id', materialId);
                row.classList.add('hover:bg-gray-50');
                
                // Tạo serial ngẫu nhiên cho vật tư
                const serial = generateSerial('SER-VT');
                
                row.innerHTML = `
                    <td class="py-2 text-sm text-gray-700">${materialId}</td>
                    <td class="py-2 text-sm text-gray-700">
                        <input type="text" name="material_serial[]" value="${serial}" class="w-32 border border-gray-300 rounded px-2 py-1">
                    </td>
                    <td class="py-2 text-sm text-gray-700">${materials[materialId]}</td>
                    <td class="py-2 text-sm text-gray-700">
                        <input type="number" name="material_qty[]" value="${quantity}" min="1" class="w-16 border border-gray-300 rounded px-2 py-1">
                        <input type="hidden" name="material_id[]" value="${materialId}">
                    </td>
                    <td class="py-2 text-sm text-gray-700">
                        <button type="button" class="text-red-500 hover:text-red-700 remove-material">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                `;
                
                materialsTable.appendChild(row);
                
                // Reset form
                materialSelect.value = '';
                materialQuantity.value = 1;
            });
            
            // Xóa vật tư khỏi bảng
            materialsTable.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-material') || e.target.parentElement.classList.contains('remove-material')) {
                    const row = e.target.closest('tr');
                    row.remove();
                }
            });
        });
    </script>
</body>

</html> 