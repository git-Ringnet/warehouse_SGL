<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết nhà cung cấp - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chi tiết nhà cung cấp</h1>
               
            </div>
            <div class="flex space-x-3">
                <a href="{{ url('/suppliers/1/edit') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Sửa
                </a>
             
                <a href="{{ url('/suppliers') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
            </div>
        </header>

        <main class="p-6">
            <div class="mb-6">
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">Công ty ABC</h2>
                                <p class="text-gray-600 mt-1">Nhà cung cấp từ 01/05/2024</p>
                            </div>
                            <div class="mt-4 md:mt-0 flex flex-col items-start md:items-end">
                                <div class="flex items-center">
                                    <i class="fas fa-phone-alt text-gray-400 mr-2"></i>
                                    <span class="text-gray-800 font-medium">0987654321</span>
                                </div>
                                <div class="flex items-center mt-1">
                                    <i class="fas fa-envelope text-gray-400 mr-2"></i>
                                    <span class="text-gray-800">contact@abc.com</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin chi tiết -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Thông tin nhà cung cấp</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6 mb-6">
                    <div>
                        <p class="text-sm text-gray-500">Tên nhà cung cấp</p>
                        <p class="text-gray-900 font-medium">Công ty ABC</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Số điện thoại</p>
                        <p class="text-gray-900 font-medium">0987654321</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="text-gray-900">contact@abc.com</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Địa chỉ</p>
                        <p class="text-gray-900">123 Lê Lợi, Hà Nội</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Ngày tạo</p>
                        <p class="text-gray-900">01/05/2024</p>
                    </div>
                </div>
                
                <div class="border-t border-gray-200 pt-4">
                    <p class="text-sm text-gray-500">Ghi chú</p>
                    <p class="text-gray-900 mt-1">Nhà cung cấp vật liệu xây dựng chính, thường có hàng sẵn và giao hàng nhanh.</p>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Dropdown Menus
        function toggleDropdown(id) {
            const dropdown = document.getElementById(id);
            const allDropdowns = document.querySelectorAll('.dropdown-content');
            
            // Close all other dropdowns
            allDropdowns.forEach(d => {
                if (d.id !== id) {
                    d.classList.remove('show');
                }
            });
            
            // Toggle current dropdown
            dropdown.classList.toggle('show');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                    dropdown.classList.remove('show');
                });
            }
        });
    </script>
</body>
</html> 