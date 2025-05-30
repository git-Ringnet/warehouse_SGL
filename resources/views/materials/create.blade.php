<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm vật tư mới - SGL</title>
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
                <a href="{{ asset('materials.index') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Thêm vật tư mới</h1>
            </div>
        </header>

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mx-auto">
                <form action="#" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Mã vật tư -->
                        <div class="col-span-1">
                            <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Mã vật tư <span class="text-red-500">*</span></label>
                            <input type="text" id="code" name="code" placeholder="VT-XXXX" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Tên vật tư -->
                        <div class="col-span-1">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Tên vật tư <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Loại vật tư -->
                        <div class="col-span-1">
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Loại vật tư <span class="text-red-500">*</span></label>
                            <select id="category" name="category" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Chọn loại vật tư</option>
                                <option value="Linh kiện điện tử">Linh kiện điện tử</option>
                                <option value="Linh kiện định vị">Linh kiện định vị</option>
                                <option value="Vi điều khiển">Vi điều khiển</option>
                                <option value="Cảm biến">Cảm biến</option>
                                <option value="Phụ kiện">Phụ kiện</option>
                                <option value="Nguồn điện">Nguồn điện</option>
                                <option value="Linh kiện viễn thông">Linh kiện viễn thông</option>
                                <option value="Phụ kiện viễn thông">Phụ kiện viễn thông</option>
                            </select>
                        </div>

                        <!-- Đơn vị tính -->
                        <div class="col-span-1">
                            <label for="unit" class="block text-sm font-medium text-gray-700 mb-1">Đơn vị<span class="text-red-500">*</span></label>
                            <select id="unit" name="unit" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Chọn đơn vị</option>
                                <option value="Cái">Cái</option>
                                <option value="Bộ">Bộ</option>
                                <option value="Chiếc">Chiếc</option>
                                <option value="Mét">Mét</option>
                                <option value="Cuộn">Cuộn</option>
                                <option value="Kg">Kg</option>
                            </select>
                        </div>

                        <!-- Nhà cung cấp -->
                        <div class="col-span-1">
                            <label for="supplier" class="block text-sm font-medium text-gray-700 mb-1">Nhà cung cấp</label>
                            <select id="supplier" name="supplier" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Chọn nhà cung cấp</option>
                                <option value="1">Công ty TNHH Điện tử ABC</option>
                                <option value="2">Công ty CP Thiết bị XYZ</option>
                                <option value="3">Công ty TNHH Linh kiện DEF</option>
                            </select>
                        </div>

                        <!-- Kho -->
                        <div class="col-span-1">
                            <label for="warehouse" class="block text-sm font-medium text-gray-700 mb-1">Kho <span class="text-red-500">*</span></label>
                            <select id="warehouse" name="warehouse" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Chọn kho</option>
                                <option value="1">Kho chính</option>
                                <option value="2">Kho phụ</option>
                                <option value="3">Kho linh kiện</option>
                            </select>
                        </div>

                        <!-- Vị trí trong kho -->
                        <div class="col-span-1">
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Vị trí trong kho</label>
                            <input type="text" id="location" name="location" placeholder="VD: Kệ A, Ngăn 3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Trạng thái -->
                        <div class="col-span-1">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái <span class="text-red-500">*</span></label>
                            <select id="status" name="status" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="new">Mới</option>
                                <option value="used">Cũ</option>
                                <option value="damaged">Hư hỏng</option>
                            </select>
                        </div>

                        <!-- Hình ảnh -->
                        <div class="col-span-2">
                            <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Hình ảnh</label>
                            <div class="flex items-center space-x-4">
                                <div class="w-32 h-32 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center bg-gray-50">
                                    <div class="text-center">
                                        <i class="fas fa-image text-gray-400 text-2xl mb-1"></i>
                                        <p class="text-xs text-gray-500">Chưa có ảnh</p>
                                    </div>
                                </div>
                                <div>
                                    <label for="image" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg cursor-pointer inline-flex items-center transition-colors">
                                        <i class="fas fa-upload mr-2"></i> Tải ảnh lên
                                        <input type="file" id="image" name="image" accept="image/*" class="hidden">
                                    </label>
                                    <p class="text-xs text-gray-500 mt-1">Hỗ trợ: JPG, PNG, GIF (tối đa 2MB)</p>
                                </div>
                            </div>
                        </div>

                        <!-- Mô tả -->
                        <div class="col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                            <textarea id="description" name="description" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <!-- Ghi chú -->
                        <div class="col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                            <textarea id="notes" name="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end space-x-3">
                        <a href="{{ asset('materials.index') }}" class="bg-gray-200 text-gray-700 px-5 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                            Hủy
                        </a>
                        <button type="submit" class="bg-blue-500 text-white px-5 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                            <i class="fas fa-save mr-2"></i> Lưu vật tư
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Hiển thị hình ảnh khi chọn file
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewContainer = document.querySelector('.w-32.h-32');
                    previewContainer.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover rounded-lg">`;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>

</html> 