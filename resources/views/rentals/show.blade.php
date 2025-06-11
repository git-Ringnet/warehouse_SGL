<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết phiếu cho thuê - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/delete-modal.js') }}"></script>
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
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Chi tiết phiếu cho thuê</h1>
                <div class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    {{ $rental->rental_code }}
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('rentals.index') }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
                <a href="{{ route('rentals.edit', $rental->id) }}" class="h-10 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
                <button id="deleteButton" 
                    data-id="{{ $rental->id }}" 
                    data-name="{{ $rental->rental_name }}"
                    class="h-10 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-trash-alt mr-2"></i> Xóa
                </button>
            </div>
        </header>

        <main class="p-6">
            @if(session('success'))
                <x-alert type="success" :message="session('success')" />
            @endif
            @if(session('error'))
                <x-alert type="error" :message="session('error')" />
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Thông tin phiếu cho thuê -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">Thông tin phiếu cho thuê</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6">
                            <div>
                                <p class="text-sm text-gray-500">Mã phiếu</p>
                                <p class="text-base text-gray-800 font-medium">{{ $rental->rental_code }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Tên phiếu</p>
                                <p class="text-base text-gray-800 font-medium">{{ $rental->rental_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Khách hàng</p>
                                <p class="text-base text-gray-800 font-medium">{{ $rental->customer->company_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Người đại diện</p>
                                <p class="text-base text-gray-800 font-medium">{{ $rental->customer->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Ngày cho thuê</p>
                                <p class="text-base text-gray-800 font-medium">{{ \Carbon\Carbon::parse($rental->rental_date)->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Ngày hẹn trả</p>
                                <p class="text-base text-gray-800 font-medium">{{ \Carbon\Carbon::parse($rental->due_date)->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email liên hệ</p>
                                <p class="text-base text-gray-800 font-medium">{{ $rental->customer->email ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Số điện thoại</p>
                                <p class="text-base text-gray-800 font-medium">{{ $rental->customer->phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <p class="text-sm text-gray-500">Ghi chú</p>
                            <p class="text-base text-gray-800 mt-1">
                                {{ $rental->notes ?? 'Không có ghi chú' }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Thiết bị hàng hóa theo hợp đồng -->
                    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                        <div class="flex justify-between items-center border-b border-gray-200 pb-2 mb-4">
                            <h2 class="text-lg font-semibold text-gray-800">Thiết bị hàng hóa theo hợp đồng</h2>
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
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Thông tin chi tiết</th>
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Mock data - Thay thế bằng dữ liệu thực tế từ database -->
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 px-4 border-b">1</td>
                                        <td class="py-2 px-4 border-b">RENT001</td>
                                        <td class="py-2 px-4 border-b">Máy chiếu Epson</td>
                                        <td class="py-2 px-4 border-b">EPS2023001</td>
                                        <td class="py-2 px-4 border-b">
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Chưa thay đổi</span>
                                        </td>
                                        <td class="py-2 px-4 border-b">
                                            <a href="#" class="text-blue-500 hover:text-blue-700">
                                                <i class="fas fa-info-circle mr-1"></i> Xem chi tiết
                                            </a>
                                        </td>
                                        <td class="py-2 px-4 border-b">
                                            <div class="flex space-x-2">
                                                <button type="button" onclick="openModal('warranty-modal', 1, 'RENT001')" class="text-blue-500 hover:text-blue-700">
                                                    <i class="fas fa-tools mr-1"></i> Bảo hành/Thay thế
                                                </button>
                                                <button type="button" onclick="openModal('return-modal', 1, 'RENT001')" class="text-red-500 hover:text-red-700">
                                                    <i class="fas fa-undo-alt mr-1"></i> Thu hồi
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 px-4 border-b">2</td>
                                        <td class="py-2 px-4 border-b">RENT002</td>
                                        <td class="py-2 px-4 border-b">Laptop Dell XPS</td>
                                        <td class="py-2 px-4 border-b">DELL2023078</td>
                                        <td class="py-2 px-4 border-b">
                                            <button onclick="openModal('history-modal', 2)" class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs hover:bg-yellow-200">
                                                Đã thay đổi <i class="fas fa-history ml-1"></i>
                                            </button>
                                        </td>
                                        <td class="py-2 px-4 border-b">
                                            <a href="#" class="text-blue-500 hover:text-blue-700">
                                                <i class="fas fa-info-circle mr-1"></i> Xem chi tiết
                                            </a>
                                        </td>
                                        <td class="py-2 px-4 border-b">
                                            <div class="flex space-x-2">
                                                <button type="button" onclick="openModal('warranty-modal', 2, 'RENT002')" class="text-blue-500 hover:text-blue-700">
                                                    <i class="fas fa-tools mr-1"></i> Bảo hành/Thay thế
                                                </button>
                                                <button type="button" onclick="openModal('return-modal', 2, 'RENT002')" class="text-red-500 hover:text-red-700">
                                                    <i class="fas fa-undo-alt mr-1"></i> Thu hồi
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Thiết bị dự phòng/bảo hành -->
                    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                        <div class="flex justify-between items-center border-b border-gray-200 pb-2 mb-4">
                            <h2 class="text-lg font-semibold text-gray-800">Thiết bị dự phòng/bảo hành</h2>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">STT</th>
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Mã thiết bị</th>
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tên thiết bị</th>
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Serial</th>
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Loại thiết bị</th>
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Trạng thái</th>
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Thông tin chi tiết</th>
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Mock data - Thay thế bằng dữ liệu thực tế từ database -->
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 px-4 border-b">1</td>
                                        <td class="py-2 px-4 border-b">RENT003</td>
                                        <td class="py-2 px-4 border-b">Bộ âm thanh hội nghị</td>
                                        <td class="py-2 px-4 border-b">AUDIO2023045</td>
                                        <td class="py-2 px-4 border-b">Thiết bị dự phòng</td>
                                        <td class="py-2 px-4 border-b">
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Chưa thay đổi</span>
                                        </td>
                                        <td class="py-2 px-4 border-b">
                                            <a href="#" class="text-blue-500 hover:text-blue-700">
                                                <i class="fas fa-info-circle mr-1"></i> Xem chi tiết
                                            </a>
                                        </td>
                                        <td class="py-2 px-4 border-b">
                                            <button type="button" onclick="openModal('return-modal', 3, 'RENT003')" class="text-red-500 hover:text-red-700">
                                                <i class="fas fa-undo-alt mr-1"></i> Thu hồi
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 px-4 border-b">2</td>
                                        <td class="py-2 px-4 border-b">RENT004</td>
                                        <td class="py-2 px-4 border-b">Camera hội nghị Logitech</td>
                                        <td class="py-2 px-4 border-b">LOG2023056</td>
                                        <td class="py-2 px-4 border-b">Thiết bị đã thay thế</td>
                                        <td class="py-2 px-4 border-b">
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Đã được thay đổi</span>
                                        </td>
                                        <td class="py-2 px-4 border-b">
                                            <a href="#" class="text-blue-500 hover:text-blue-700">
                                                <i class="fas fa-info-circle mr-1"></i> Xem chi tiết
                                            </a>
                                        </td>
                                        <td class="py-2 px-4 border-b">
                                            <button type="button" onclick="openModal('return-modal', 4, 'RENT004')" class="text-red-500 hover:text-red-700">
                                                <i class="fas fa-undo-alt mr-1"></i> Thu hồi
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 px-4 border-b">3</td>
                                        <td class="py-2 px-4 border-b">RENT005</td>
                                        <td class="py-2 px-4 border-b">Máy chiếu Sony Portable</td>
                                        <td class="py-2 px-4 border-b">SONY2023099</td>
                                        <td class="py-2 px-4 border-b">Thiết bị bảo hành</td>
                                        <td class="py-2 px-4 border-b">
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Chưa thay đổi</span>
                                        </td>
                                        <td class="py-2 px-4 border-b">
                                            <a href="#" class="text-blue-500 hover:text-blue-700">
                                                <i class="fas fa-info-circle mr-1"></i> Xem chi tiết
                                            </a>
                                        </td>
                                        <td class="py-2 px-4 border-b">
                                            <button type="button" onclick="openModal('return-modal', 5, 'RENT005')" class="text-red-500 hover:text-red-700">
                                                <i class="fas fa-undo-alt mr-1"></i> Thu hồi
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Lịch sử gia hạn -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex justify-between items-center border-b border-gray-200 pb-2 mb-4">
                            <h2 class="text-lg font-semibold text-gray-800">Lịch sử gia hạn</h2>
                            <button onclick="openExtendModal()" class="text-blue-500 hover:text-blue-600 text-sm font-medium">
                                <i class="fas fa-clock mr-1"></i> Gia hạn phiếu thuê
                            </button>
                        </div>
                        
                        @if(strpos($rental->notes ?? '', 'Gia hạn') !== false)
                            <div class="space-y-3">
                                @php
                                    $notes = explode("\n", $rental->notes);
                                    $extensionNotes = array_filter($notes, function($note) {
                                        return strpos($note, 'Gia hạn') !== false;
                                    });
                                @endphp
                                
                                @foreach($extensionNotes as $note)
                                    <div class="p-3 bg-gray-50 rounded-lg">
                                        <p class="text-sm text-gray-800">{{ $note }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 text-center py-4">Chưa có lịch sử gia hạn</p>
                        @endif
                    </div>
                </div>
                
                <!-- Sidebar thông tin -->
                <div class="lg:col-span-1">
                    <!-- Thông tin trạng thái -->
                    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">Tình trạng phiếu thuê</h2>
                        
                        @php
                            $daysRemaining = $rental->daysRemaining();
                        @endphp
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500">Thời gian còn lại</p>
                                @if($rental->isOverdue())
                                    <p class="text-xl font-semibold text-red-600">Quá hạn {{ abs(intval($daysRemaining)) }} ngày</p>
                                @else
                                    <p class="text-xl font-semibold text-gray-800">{{ intval($daysRemaining) }} ngày</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Ngày hẹn trả</p>
                                <p class="text-xl font-semibold {{ $rental->isOverdue() ? 'text-red-600' : 'text-gray-800' }}">
                                    {{ \Carbon\Carbon::parse($rental->due_date)->format('d/m/Y') }}
                                </p>
                            </div>
                            <div class="mb-4">
                                <p class="text-sm font-medium text-gray-500 mb-1">Nhân viên phụ trách</p>
                                <p class="text-base text-gray-900">{{ $rental->employee ? $rental->employee->name : 'Chưa phân công' }}</p>
                            </div>
                            <div class="mb-4">
                                <p class="text-sm font-medium text-gray-500 mb-1">Ngày tạo</p>
                                <p class="text-base text-gray-900">{{ \Carbon\Carbon::parse($rental->created_at)->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="mt-2">
                                <button onclick="openExtendModal()" class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center justify-center transition-colors">
                                    <i class="fas fa-clock mr-2"></i> Gia hạn phiếu thuê
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Hành động -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">Hành động</h2>
                        
                        <div class="space-y-3">
                            <button onclick="confirmDelete()" class="w-full px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg flex items-center justify-center transition-colors">
                                <i class="fas fa-trash mr-2"></i> Xóa phiếu
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal gia hạn phiếu thuê -->
    <div id="extendModal" class="modal-overlay">
        <div class="modal p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Gia hạn phiếu thuê</h3>
            
            <form action="{{ route('rentals.extend', $rental->id) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="extend_days" class="block text-sm font-medium text-gray-700 mb-1">Số ngày gia hạn</label>
                    <input type="number" name="extend_days" id="extend_days" min="1" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="7">
                </div>
                <div class="mb-4">
                    <label for="extend_notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                    <textarea name="extend_notes" id="extend_notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeExtendModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Hủy
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                        <i class="fas fa-check mr-2"></i> Xác nhận gia hạn
                    </button>
                </div>
            </form>
        </div>
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

    <!-- Modal Bảo hành/Thay thế -->
    <div id="warranty-modal" class="modal-overlay">
        <div class="modal max-w-lg w-full">
            <div class="p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Bảo hành/Thay thế thiết bị</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal('warranty-modal')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="warranty-form" action="#" method="POST">
                    @csrf
                    <input type="hidden" id="warranty-equipment-id" name="equipment_id">
                    
                    <p class="mb-4">Bạn đang thực hiện bảo hành/thay thế cho thiết bị <span id="warranty-equipment-code" class="font-semibold"></span></p>
                    
                    <div class="mb-4">
                        <label for="replacement-device" class="block text-sm font-medium text-gray-700 mb-1">Chọn thiết bị thay thế</label>
                        <select id="replacement-device" name="replacement_device_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">-- Chọn thiết bị --</option>
                            <option value="1">RENT003 - Bộ âm thanh hội nghị - Thiết bị dự phòng</option>
                            <option value="2">RENT005 - Máy chiếu Sony Portable - Thiết bị bảo hành</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="warranty-reason" class="block text-sm font-medium text-gray-700 mb-1">Lý do bảo hành/thay thế</label>
                        <textarea id="warranty-reason" name="reason" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-5">
                        <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300" onclick="closeModal('warranty-modal')">
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

    <!-- Modal Lịch sử thay đổi -->
    <div id="history-modal" class="modal-overlay">
        <div class="modal max-w-lg w-full">
            <div class="p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Lịch sử thay đổi thiết bị</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal('history-modal')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <h4 class="text-md font-medium text-gray-800 mb-2">Thông tin thiết bị gốc</h4>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p><span class="font-medium">Mã thiết bị gốc:</span> RENT007</p>
                        <p><span class="font-medium">Tên thiết bị:</span> Laptop Dell XPS (cũ)</p>
                        <p><span class="font-medium">Serial:</span> DELL2023045</p>
                        <p><span class="font-medium">Lý do thay thế:</span> Hỏng ổ cứng</p>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h4 class="text-md font-medium text-gray-800 mb-2">Lịch sử thay đổi</h4>
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="py-2 px-3 text-left text-xs font-medium text-gray-700">Ngày thay đổi</th>
                                    <th class="py-2 px-3 text-left text-xs font-medium text-gray-700">Người thực hiện</th>
                                    <th class="py-2 px-3 text-left text-xs font-medium text-gray-700">Thiết bị thay thế</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="py-2 px-3 border-t">08/06/2024 10:15</td>
                                    <td class="py-2 px-3 border-t">Nguyễn Thị B</td>
                                    <td class="py-2 px-3 border-t">RENT002 - Laptop Dell XPS</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300" onclick="closeModal('history-modal')">
                        Đóng
                    </button>
                </div>
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
        // Delete functionality setup
        document.addEventListener('DOMContentLoaded', function() {
            initDeleteModal();
            
            // Attach click event to delete button
            document.getElementById('deleteButton').addEventListener('click', function() {
                // Get the rental name from a data attribute to avoid JS issues
                const rentalName = this.getAttribute('data-name');
                const rentalId = this.getAttribute('data-id');
                openDeleteModal(rentalId, rentalName);
            });
        });

        // Override deleteCustomer function from delete-modal.js
        function deleteCustomer(id) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('rentals.index') }}/" + id;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = "{{ csrf_token() }}";
            
            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(method);
            document.body.appendChild(form);
            form.submit();
        }
        
        // Mở modal gia hạn phiếu thuê
        function openExtendModal() {
            document.getElementById('extendModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        
        // Đóng modal gia hạn phiếu thuê
        function closeExtendModal() {
            document.getElementById('extendModal').classList.remove('show');
            document.body.style.overflow = '';
        }
        
        // Xác nhận xóa phiếu
        function confirmDelete() {
            if (confirm('Bạn có chắc chắn muốn xóa phiếu cho thuê này không?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('rentals.destroy', $rental->id) }}";
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = "{{ csrf_token() }}";
                
                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';
                
                form.appendChild(csrfToken);
                form.appendChild(method);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Mở modal
        function openModal(modalId, equipmentId, equipmentCode) {
            document.getElementById(modalId).classList.add('show');
            
            if (modalId === 'convert-modal') {
                document.getElementById('convert-equipment-id').value = equipmentId;
                document.getElementById('convert-equipment-code').textContent = equipmentCode;
            } else if (modalId === 'return-modal') {
                document.getElementById('return-equipment-id').value = equipmentId;
                document.getElementById('return-equipment-code').textContent = equipmentCode;
            } else if (modalId === 'warranty-modal') {
                document.getElementById('warranty-equipment-id').value = equipmentId;
                document.getElementById('warranty-equipment-code').textContent = equipmentCode;
            } else if (modalId === 'history-modal') {
                // Handle history modal opening
            }
        }
        
        // Đóng modal
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }
    </script>
</body>
</html>