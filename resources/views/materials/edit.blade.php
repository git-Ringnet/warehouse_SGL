<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa vật tư - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa vật tư</h1>
                <div class="ml-4 px-2 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                    ID: VT-0002
                </div>
            </div>
            <a href="{{ asset('materials/show') }}"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </header>

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <form action="#" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Thông tin vật tư</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700 mb-1 required">Mã
                                    vật tư <span class="text-red-500">*</span></label>
                                <input type="text" id="code" name="code" value="VT-0002" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên
                                    vật tư <span class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" value="Module GPS NEO-6M" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-1 required">Loại
                                    vật tư <span class="text-red-500">*</span></label>
                                <select id="category" name="category" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Chọn loại vật tư</option>
                                    <option value="Linh kiện điện tử">Linh kiện điện tử</option>
                                    <option value="Linh kiện định vị" selected>Linh kiện định vị</option>
                                    <option value="Vi điều khiển">Vi điều khiển</option>
                                    <option value="Cảm biến">Cảm biến</option>
                                    <option value="Phụ kiện">Phụ kiện</option>
                                    <option value="Nguồn điện">Nguồn điện</option>
                                    <option value="Linh kiện viễn thông">Linh kiện viễn thông</option>
                                    <option value="Phụ kiện viễn thông">Phụ kiện viễn thông</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label for="supplier" class="block text-sm font-medium text-gray-700 mb-1">Nhà cung
                                    cấp</label>
                                <select id="supplier" name="supplier"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Chọn nhà cung cấp</option>
                                    <option value="1">Công ty TNHH Điện tử ABC</option>
                                    <option value="2">Công ty CP Thiết bị XYZ</option>
                                    <option value="3" selected>Công ty TNHH Linh kiện DEF</option>
                                </select>
                            </div>

                            <div>
                                <label for="status"
                                    class="block text-sm font-medium text-gray-700 mb-1 required">Trạng thái <span
                                        class="text-red-500">*</span></label>
                                <select id="status" name="status" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="new" selected>Mới</option>
                                    <option value="used">Cũ</option>
                                    <option value="damaged">Hư hỏng</option>
                                </select>
                            </div>
                            <div>
                                <label for="unit" class="block text-sm font-medium text-gray-700 mb-1 required">Đơn
                                    vị<span class="text-red-500">*</span></label>
                                <select id="unit" name="unit" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Chọn đơn vị</option>
                                    <option value="Cái" selected>Cái</option>
                                    <option value="Bộ">Bộ</option>
                                    <option value="Chiếc">Chiếc</option>
                                    <option value="Mét">Mét</option>
                                    <option value="Cuộn">Cuộn</option>
                                    <option value="Kg">Kg</option>
                                </select>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                            <textarea id="notes" name="notes" rows="3"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">Cần kiểm tra trạng thái hoạt động trước khi sử dụng. Nên tránh để gần các nguồn nhiễu điện từ để đảm bảo chất lượng tín hiệu.</textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ asset('materials/show') }}"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                            Hủy
                        </a>
                        <button type="button"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                            <i class="fas fa-save mr-2"></i> Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Hiển thị xác nhận khi rời khỏi trang có thay đổi chưa lưu
        const form = document.querySelector('form');
        const originalFormState = form.innerHTML;

        window.addEventListener('beforeunload', function(e) {
            if (form.innerHTML !== originalFormState) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
</body>

</html>
