<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý kiểm thử - SGL</title>
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
        <header class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">Quản lý kiểm thử</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <div class="flex gap-2 w-full md:w-auto">
                    <input type="text" placeholder="Tìm kiếm mã phiếu, thiết bị..." class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full md:w-64 h-10" />
                    <select class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 h-10">
                        <option value="">Loại kiểm thử</option>
                        <option value="material">Vật tư/Hàng hóa</option>
                        <option value="finished_product">Thiết bị thành phẩm</option>
                    </select>
                </div>
                <a href="{{ url('/testing/create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full md:w-auto justify-center h-10">
                    <i class="fas fa-plus-circle mr-2"></i> Tạo phiếu kiểm thử
                </a>
            </div>
        </header>
        <main class="p-6">
            <!-- Tab Navigation -->
            <div class="mb-6 border-b border-gray-200">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                    <li class="mr-2">
                        <a href="{{ url('/testing') }}" class="inline-flex items-center p-4 border-b-2 {{ request()->query('type') ? 'border-transparent hover:border-gray-300 text-gray-500 hover:text-gray-600' : 'border-blue-500 text-blue-600' }} rounded-t-lg group">
                            <i class="fas fa-clipboard-check mr-2"></i> Tất cả phiếu kiểm thử
                        </a>
                    </li>
                    <li class="mr-2">
                        <a href="{{ url('/testing?type=material') }}" class="inline-flex items-center p-4 border-b-2 {{ request()->query('type') == 'material' ? 'border-blue-500 text-blue-600' : 'border-transparent hover:border-gray-300 text-gray-500 hover:text-gray-600' }} rounded-t-lg group">
                            <i class="fas fa-microchip mr-2"></i> Vật tư/Hàng hóa
                        </a>
                    </li>
                    <li class="mr-2">
                        <a href="{{ url('/testing?type=finished_product') }}" class="inline-flex items-center p-4 border-b-2 {{ request()->query('type') == 'finished_product' ? 'border-blue-500 text-blue-600' : 'border-transparent hover:border-gray-300 text-gray-500 hover:text-gray-600' }} rounded-t-lg group">
                            <i class="fas fa-box mr-2"></i> Thiết bị thành phẩm
                        </a>
                    </li>
                </ul>
            </div>

            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã phiếu</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Loại kiểm thử</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Vật tư/Hàng hóa</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Serial/Mã</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Người kiểm thử</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày kiểm thử</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kết quả</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <!-- Phiếu kiểm thử 1: Vật tư/Hàng hóa -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">QA-24060001</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Vật tư/Hàng hóa</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Module 4G</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">4G-MOD-2305621</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Nguyễn Văn A</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">15/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Hoàn thành</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <button onclick="showResultDetails('qa_1')" class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs hover:bg-green-200">90% Đạt</button>
                                <div id="qa_1" class="hidden absolute bg-white shadow-lg border rounded-lg p-3 z-50 mt-2 text-xs">
                                    <p>Số lượng đạt: 18/20</p>
                                    <p>Số lượng không đạt: 2/20</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/testing/1') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/testing/1/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openUpdateInventory(1, 'QA-24060001')" class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-100 hover:bg-purple-500 transition-colors group" title="Cập nhật về kho">
                                    <i class="fas fa-warehouse text-purple-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Phiếu kiểm thử 2: Vật tư/Hàng hóa đang chờ -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">QA-24060002</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Vật tư/Hàng hóa</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Module Công suất</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">PWR-2405102</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Trần Văn B</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">12/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">Chờ xử lý</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">Chưa có</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/testing/2') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="approveTest(2)" class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 hover:bg-green-500 transition-colors group" title="Duyệt">
                                    <i class="fas fa-check text-green-500 group-hover:text-white"></i>
                                </button>
                                <button onclick="rejectTest(2)" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Từ chối">
                                    <i class="fas fa-times text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Phiếu kiểm thử 3: Thiết bị thành phẩm -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">QA-24060003</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs">Thiết bị thành phẩm</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SGL SmartBox</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SB-2406057</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Lê Thị C</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">14/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Đang thực hiện</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">Chưa có</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/testing/3') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/testing/3/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="receiveTest(3)" class="w-8 h-8 flex items-center justify-center rounded-full bg-teal-100 hover:bg-teal-500 transition-colors group" title="Tiếp nhận">
                                    <i class="fas fa-clipboard-check text-teal-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Phiếu kiểm thử 4: Vật tư/Hàng hóa đã hủy -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">QA-24060004</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Vật tư/Hàng hóa</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Android Box</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">AND-2406015</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Phạm Văn D</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">13/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Đã hủy</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">Không có</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/testing/4') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- Phiếu kiểm thử 5: Vật tư/Hàng hóa hoàn thành -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">QA-24050005</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Vật tư/Hàng hóa</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Module IoTs</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">IOT-2405089</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Lê Văn E</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">30/05/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Hoàn thành</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <button onclick="showResultDetails('qa_5')" class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs hover:bg-green-200">100% Đạt</button>
                                <div id="qa_5" class="hidden absolute bg-white shadow-lg border rounded-lg p-3 z-50 mt-2 text-xs">
                                    <p>Số lượng đạt: 15/15</p>
                                    <p>Số lượng không đạt: 0/15</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/testing/5') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openUpdateInventory(5, 'QA-24050005')" class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-100 hover:bg-purple-500 transition-colors group" title="Cập nhật về kho">
                                    <i class="fas fa-warehouse text-purple-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-500">Hiển thị 1-5 của 25 phiếu kiểm thử</div>
                <div class="flex space-x-1">
                <button class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <a href="#" class="px-3 py-1 rounded border border-blue-500 bg-blue-500 text-white">1</a>
                    <a href="#" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-100">2</a>
                    <a href="#" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-100">3</a>
                    <a href="#" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-100">4</a>
                    <a href="#" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-100">5</a>
                    <button class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- Modals -->
    <!-- Update Inventory Modal -->
    <div id="updateInventoryModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex justify-between items-center px-6 py-4 border-b">
                <h3 class="text-lg font-medium text-gray-800">Cập nhật vào kho</h3>
                <button onclick="closeUpdateInventoryModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="updateInventoryForm" class="px-6 py-4">
                <input type="hidden" id="test_id" name="test_id">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mã phiếu kiểm thử</label>
                    <div id="test_code" class="border border-gray-300 rounded-lg px-3 py-2 bg-gray-50"></div>
                </div>
                <div class="mb-4">
                    <label for="good_warehouse" class="block text-sm font-medium text-gray-700 mb-1 required">Kho lưu thiết bị Đạt</label>
                    <select id="good_warehouse" name="good_warehouse" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                        <option value="">-- Chọn kho --</option>
                        <option value="warehouse_a">Kho A - Thiết bị hoàn chỉnh</option>
                        <option value="warehouse_b">Kho B - Linh kiện đạt</option>
                        <option value="warehouse_c">Kho C - Hàng tiêu chuẩn</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="defective_warehouse" class="block text-sm font-medium text-gray-700 mb-1 required">Kho lưu thiết bị Không đạt</label>
                    <select id="defective_warehouse" name="defective_warehouse" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                        <option value="">-- Chọn kho --</option>
                        <option value="warehouse_d">Kho D - Linh kiện lỗi</option>
                        <option value="warehouse_e">Kho E - Hàng chờ trả NCC</option>
                        <option value="warehouse_f">Kho F - Đợi xử lý</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="inventory_note" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                    <textarea id="inventory_note" name="inventory_note" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập ghi chú nếu có"></textarea>
                </div>
                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeUpdateInventoryModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Hủy
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                        <i class="fas fa-save mr-2"></i> Xác nhận
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Approve Modal -->
    <div id="approveModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex justify-between items-center px-6 py-4 border-b">
                <h3 class="text-lg font-medium text-gray-800">Duyệt phiếu kiểm thử</h3>
                <button onclick="closeApproveModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="approveForm" class="px-6 py-4">
                <input type="hidden" id="approve_test_id" name="approve_test_id">
                <div class="mb-4">
                    <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1 required">Giao cho người phụ trách</label>
                    <select id="assigned_to" name="assigned_to" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                        <option value="">-- Chọn người phụ trách --</option>
                        <option value="1">Nguyễn Văn A</option>
                        <option value="2">Trần Văn B</option>
                        <option value="3">Lê Thị C</option>
                        <option value="4">Phạm Văn D</option>
                        <option value="5">Lê Văn E</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="approve_note" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                    <textarea id="approve_note" name="approve_note" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập ghi chú nếu có"></textarea>
                </div>
                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeApproveModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Hủy
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                        <i class="fas fa-check mr-2"></i> Xác nhận
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex justify-between items-center px-6 py-4 border-b">
                <h3 class="text-lg font-medium text-gray-800">Từ chối phiếu kiểm thử</h3>
                <button onclick="closeRejectModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="rejectForm" class="px-6 py-4">
                <input type="hidden" id="reject_test_id" name="reject_test_id">
                <div class="mb-4">
                    <label for="reject_reason" class="block text-sm font-medium text-gray-700 mb-1 required">Lý do từ chối</label>
                    <textarea id="reject_reason" name="reject_reason" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập lý do từ chối phiếu kiểm thử" required></textarea>
                </div>
                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeRejectModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Hủy
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                        <i class="fas fa-times mr-2"></i> Xác nhận từ chối
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Show test result details
        function showResultDetails(elementId) {
            const resultElement = document.getElementById(elementId);
            const isHidden = resultElement.classList.contains('hidden');
            
            // Hide all other results first
            document.querySelectorAll('[id^="qa_"]').forEach(el => {
                el.classList.add('hidden');
            });
            
            // Toggle current result
            if (isHidden) {
                resultElement.classList.remove('hidden');
            } else {
                resultElement.classList.add('hidden');
            }
        }

        // Click outside to hide result details
        document.addEventListener('click', (e) => {
            if (!e.target.closest('button[onclick^="showResultDetails"]')) {
                document.querySelectorAll('[id^="qa_"]').forEach(el => {
                    el.classList.add('hidden');
                });
            }
        });

        // Update Inventory Modal
        function openUpdateInventory(id, testCode) {
            document.getElementById('test_id').value = id;
            document.getElementById('test_code').textContent = testCode;
            document.getElementById('updateInventoryModal').classList.remove('hidden');
        }

        function closeUpdateInventoryModal() {
            document.getElementById('updateInventoryModal').classList.add('hidden');
        }

        // Approve Modal
        function approveTest(id) {
            document.getElementById('approve_test_id').value = id;
            document.getElementById('approveModal').classList.remove('hidden');
        }

        function closeApproveModal() {
            document.getElementById('approveModal').classList.add('hidden');
        }

        // Reject Modal
        function rejectTest(id) {
            document.getElementById('reject_test_id').value = id;
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }

        // Receive Test
        function receiveTest(id) {
            // In a real application, this would be an API call
            alert('Đã tiếp nhận phiếu kiểm thử #' + id + ' thành công');
        }

        // Form submission handlers
        document.getElementById('updateInventoryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('test_id').value;
            const goodWarehouse = document.getElementById('good_warehouse').value;
            const defectiveWarehouse = document.getElementById('defective_warehouse').value;
            
            // In a real application, this would be an API call
            alert(`Đã cập nhật kho cho phiếu #${id}:\n- Kho thiết bị đạt: ${goodWarehouse}\n- Kho thiết bị lỗi: ${defectiveWarehouse}`);
            closeUpdateInventoryModal();
        });

        document.getElementById('approveForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('approve_test_id').value;
            const assignedTo = document.getElementById('assigned_to').value;
            
            // In a real application, this would be an API call
            alert(`Đã duyệt phiếu #${id} và giao cho người phụ trách: ${assignedTo}`);
            closeApproveModal();
        });

        document.getElementById('rejectForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('reject_test_id').value;
            const reason = document.getElementById('reject_reason').value;
            
            // In a real application, this would be an API call
            alert(`Đã từ chối phiếu #${id}. Lý do: ${reason}`);
            closeRejectModal();
        });
    </script>
</body>
</html> 