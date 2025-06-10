<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết dự án - SGL</title>
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
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 50;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .modal {
            background-color: white;
            border-radius: 0.5rem;
            max-width: 500px;
            width: 90%;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }
        
        .modal-overlay.show .modal {
            transform: scale(1);
        }
    </style>
</head>
<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
      
        
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
                <h1 class="text-xl font-bold text-gray-800">Chi tiết dự án</h1>
            <div class="flex space-x-2">
                <a href="{{ route('projects.edit', $project->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white h-10 px-4 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
                <form action="{{ route('projects.destroy', $project->id) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa dự án này?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white h-10 px-4 rounded-lg flex items-center transition-colors">
                        <i class="fas fa-trash-alt mr-2"></i> Xóa
                    </button>
                </form>
                <a href="{{ route('projects.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 h-10 px-4 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
            </div>
        </header>
        @if(session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif
        @if(session('error'))
            <x-alert type="error" :message="session('error')" />
        @endif
        <main class="p-6">
                <!-- Thông tin dự án -->
                    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-project-diagram mr-2 text-blue-500"></i> Thông tin dự án
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Mã dự án</p>
                            <p class="text-base font-medium text-gray-900">{{ $project->project_code }}</p>
                            </div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Tên dự án</p>
                            <p class="text-base text-gray-900">{{ $project->project_name }}</p>
                        </div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Ngày bắt đầu</p>
                            <p class="text-base text-gray-900">{{ \Carbon\Carbon::parse($project->start_date)->format('d/m/Y') }}</p>
                        </div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Thời gian bảo hành</p>
                            <p class="text-base text-gray-900">{{ $project->warranty_period }} tháng</p>
                        </div>
                    </div>
                                <div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Công ty khách hàng</p>
                            <p class="text-base text-gray-900">{{ $project->customer->company_name }}</p>
                                </div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Người đại diện</p>
                            <p class="text-base text-gray-900">{{ $project->customer->name }}</p>
                            </div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Ngày kết thúc dự kiến</p>
                            <p class="text-base text-gray-900">{{ \Carbon\Carbon::parse($project->end_date)->format('d/m/Y') }}</p>
                        </div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Ngày tạo</p>
                            <p class="text-base text-gray-900">{{ \Carbon\Carbon::parse($project->created_at)->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    <div class="col-span-1 md:col-span-2">
                        <p class="text-sm font-medium text-gray-500 mb-1">Mô tả</p>
                        <p class="text-base text-gray-900 whitespace-pre-line">{{ $project->description ?? 'Không có mô tả' }}</p>
                        </div>
                    </div>
                </div>
                
            <!-- Thông tin khách hàng -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-user-tie mr-2 text-blue-500"></i> Thông tin người đại diện
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Họ và tên</p>
                            <p class="text-base text-gray-900">{{ $project->customer->name }}</p>
                        </div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Số điện thoại</p>
                            <p class="text-base text-gray-900">{{ $project->customer->phone }}</p>
                        </div>
                    </div>
                            <div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Email</p>
                            <p class="text-base text-gray-900">{{ $project->customer->email ?? 'N/A' }}</p>
                            </div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Địa chỉ</p>
                            <p class="text-base text-gray-900">{{ $project->customer->address ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thiết bị hàng hóa theo hợp đồng -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-box mr-2 text-blue-500"></i> Thiết bị hàng hóa theo hợp đồng
                    </h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">STT</th>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Mã thiết bị</th>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tên thiết bị</th>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Serial</th>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Trạng thái</th>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Mock data - Thay thế bằng dữ liệu thực tế từ database -->
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-4 border-b">1</td>
                                <td class="py-2 px-4 border-b">TB001</td>
                                <td class="py-2 px-4 border-b">Máy tính để bàn Dell</td>
                                <td class="py-2 px-4 border-b">DELL2023001</td>
                                <td class="py-2 px-4 border-b"><span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Đang hoạt động</span></td>
                                <td class="py-2 px-4 border-b">
                                    <div class="flex space-x-2">
                                        <button type="button" onclick="openModal('convert-modal', 1, 'TB001')" class="text-blue-500 hover:text-blue-700">
                                            <i class="fas fa-exchange-alt mr-1"></i> Chuyển dự phòng
                                        </button>
                                        <button type="button" onclick="openModal('return-modal', 1, 'TB001')" class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-undo-alt mr-1"></i> Thu hồi
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-4 border-b">2</td>
                                <td class="py-2 px-4 border-b">TB002</td>
                                <td class="py-2 px-4 border-b">Máy in HP LaserJet</td>
                                <td class="py-2 px-4 border-b">HP2023045</td>
                                <td class="py-2 px-4 border-b"><span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Đang hoạt động</span></td>
                                <td class="py-2 px-4 border-b">
                                    <div class="flex space-x-2">
                                        <button type="button" onclick="openModal('convert-modal', 2, 'TB002')" class="text-blue-500 hover:text-blue-700">
                                            <i class="fas fa-exchange-alt mr-1"></i> Chuyển dự phòng
                                        </button>
                                        <button type="button" onclick="openModal('return-modal', 2, 'TB002')" class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-undo-alt mr-1"></i> Thu hồi
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Thiết bị dự phòng -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-tools mr-2 text-blue-500"></i> Thiết bị dự phòng
                    </h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">STT</th>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Mã thiết bị</th>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tên thiết bị</th>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Serial</th>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Trạng thái</th>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Mock data - Thay thế bằng dữ liệu thực tế từ database -->
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-4 border-b">1</td>
                                <td class="py-2 px-4 border-b">TB003</td>
                                <td class="py-2 px-4 border-b">Laptop Lenovo ThinkPad</td>
                                <td class="py-2 px-4 border-b">LEN2023056</td>
                                <td class="py-2 px-4 border-b"><span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Dự phòng</span></td>
                                <td class="py-2 px-4 border-b">
                                    <button type="button" onclick="openModal('return-modal', 3, 'TB003')" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-undo-alt mr-1"></i> Thu hồi
                                    </button>
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-4 border-b">2</td>
                                <td class="py-2 px-4 border-b">TB004</td>
                                <td class="py-2 px-4 border-b">Màn hình Dell UltraSharp</td>
                                <td class="py-2 px-4 border-b">DEL2023078</td>
                                <td class="py-2 px-4 border-b"><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Đã được thay đổi</span></td>
                                <td class="py-2 px-4 border-b">
                                    <button type="button" onclick="openModal('return-modal', 4, 'TB004')" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-undo-alt mr-1"></i> Thu hồi
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Chuyển sang dự phòng -->
    <div id="convert-modal" class="modal-overlay">
        <div class="modal max-w-md w-full">
            <div class="p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Chuyển thiết bị sang dự phòng</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal('convert-modal')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="convert-form" action="#" method="POST">
                    @csrf
                    <input type="hidden" id="convert-equipment-id" name="equipment_id">
                    
                    <p class="mb-4">Bạn muốn chuyển thiết bị <span id="convert-equipment-code" class="font-semibold"></span> sang trạng thái dự phòng?</p>
                    
                    <div class="mb-4">
                        <label for="convert-reason" class="block text-sm font-medium text-gray-700 mb-1">Lý do</label>
                        <textarea id="convert-reason" name="reason" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-5">
                        <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300" onclick="closeModal('convert-modal')">
                            Hủy bỏ
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            Xác nhận
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Thu hồi thiết bị -->
    <div id="return-modal" class="modal-overlay">
        <div class="modal max-w-md w-full">
            <div class="p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Thu hồi thiết bị</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal('return-modal')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="return-form" action="#" method="POST">
                    @csrf
                    <input type="hidden" id="return-equipment-id" name="equipment_id">
                    
                    <p class="mb-4">Bạn muốn thu hồi thiết bị <span id="return-equipment-code" class="font-semibold"></span> trả về kho?</p>
                    
                    <div class="mb-4">
                        <label for="warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Chọn kho</label>
                        <select id="warehouse_id" name="warehouse_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">-- Chọn kho --</option>
                            <option value="1">Kho Hà Nội</option>
                            <option value="2">Kho Hồ Chí Minh</option>
                            <option value="3">Kho Đà Nẵng</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="return-reason" class="block text-sm font-medium text-gray-700 mb-1">Lý do thu hồi</label>
                        <textarea id="return-reason" name="reason" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label for="condition" class="block text-sm font-medium text-gray-700 mb-1">Tình trạng thiết bị</label>
                        <select id="condition" name="condition" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="good">Hoạt động tốt</option>
                            <option value="damaged">Hư hỏng nhẹ</option>
                            <option value="broken">Hư hỏng nặng</option>
                        </select>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-5">
                        <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300" onclick="closeModal('return-modal')">
                            Hủy bỏ
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            Xác nhận
                        </button>
                    </div>
                </form>
            </div>
        </div>
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

        // Prevent dropdown from closing when clicking inside
        document.querySelectorAll('.dropdown-content').forEach(dropdown => {
            dropdown.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        });

        // Mở modal
        function openModal(modalId, equipmentId, equipmentCode) {
            document.getElementById(modalId).classList.add('show');
            
            if (modalId === 'convert-modal') {
                document.getElementById('convert-equipment-id').value = equipmentId;
                document.getElementById('convert-equipment-code').textContent = equipmentCode;
            } else if (modalId === 'return-modal') {
                document.getElementById('return-equipment-id').value = equipmentId;
                document.getElementById('return-equipment-code').textContent = equipmentCode;
            }
        }
        
        // Đóng modal
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }
    </script>
</body>
</html> 