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
            <div class="bg-white rounded-xl shadow-md p-6">
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

        // Prevent dropdown from closing when clicking inside
        document.querySelectorAll('.dropdown-content').forEach(dropdown => {
            dropdown.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        });
    </script>
</body>
</html> 