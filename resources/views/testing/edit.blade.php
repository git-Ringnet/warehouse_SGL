<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa phiếu kiểm thử - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa phiếu kiểm thử</h1>
                <div class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    QA-24060001
                </div>
                <div class="ml-2 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    Module 4G
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ url('/testing/1') }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
            </div>
        </header>

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <form action="{{ url('/testing/1') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Thông tin cơ bản -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2">Thông tin cơ bản</h2>
                        
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="test_type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại kiểm thử</label>
                                <select id="test_type" name="test_type" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required onchange="toggleTestTypeFields()">
                                    <option value="new_component" selected>Kiểm thử linh kiện đầu vào</option>
                                    <option value="defective">Kiểm thử module bị lỗi</option>
                                    <option value="new_device">Kiểm thử thiết bị mới lắp ráp</option>
                                </select>
                            </div>

                            <div>
                                <label for="device_category" class="block text-sm font-medium text-gray-700 mb-1 required">Loại thiết bị/module</label>
                                <select id="device_category" name="device_category" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="android">Android</option>
                                    <option value="module_4g" selected>Module 4G</option>
                                    <option value="module_power">Module Công suất</option>
                                    <option value="module_iot">Module IoTs</option>
                                    <option value="smartbox">SGL SmartBox</option>
                                    <option value="other">Khác</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="serial_number" class="block text-sm font-medium text-gray-700 mb-1 required">Serial/Mã thiết bị</label>
                                <input type="text" id="serial_number" name="serial_number" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="4G-MOD-2305621" required>
                            </div>

                            <div>
                                <label for="test_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày kiểm thử</label>
                                <input type="date" id="test_date" name="test_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="2024-06-15" required>
                            </div>
                        </div>
                        
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="test_engineer" class="block text-sm font-medium text-gray-700 mb-1 required">Người kiểm thử</label>
                                <select id="test_engineer" name="test_engineer" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="1" selected>Nguyễn Văn A</option>
                                    <option value="2">Trần Văn B</option>
                                    <option value="3">Lê Thị C</option>
                                    <option value="4">Phạm Văn D</option>
                                    <option value="5">Lê Văn E</option>
                                </select>
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1 required">Trạng thái</label>
                                <select id="status" name="status" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="pending">Chờ kiểm thử</option>
                                    <option value="testing">Đang kiểm thử</option>
                                    <option value="completed" selected>Hoàn thành</option>
                                </select>
                            </div>
                        </div>

                        <!-- Thông tin kiểm thử linh kiện đầu vào -->
                        <div id="new_component_fields" class="mt-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="supplier" class="block text-sm font-medium text-gray-700 mb-1">Nhà cung cấp</label>
                                    <input type="text" id="supplier" name="supplier" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="Công ty ABC Electronics">
                                </div>
                                
                                <div>
                                    <label for="batch_number" class="block text-sm font-medium text-gray-700 mb-1">Mã lô</label>
                                    <input type="text" id="batch_number" name="batch_number" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="LOT-2405-01">
                                </div>
                            </div>
                            
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="manufacture_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày sản xuất</label>
                                    <input type="date" id="manufacture_date" name="manufacture_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="2024-05-10">
                                </div>
                                
                                <div>
                                    <label for="arrival_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày nhập kho</label>
                                    <input type="date" id="arrival_date" name="arrival_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="2024-06-05">
                                </div>
                            </div>
                        </div>

                        <!-- Thông tin kiểm thử module lỗi (ẩn mặc định) -->
                        <div id="defective_fields" class="mt-4 hidden">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="defect_source" class="block text-sm font-medium text-gray-700 mb-1">Nguồn gốc lỗi</label>
                                    <select id="defect_source" name="defect_source" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                        <option value="">-- Chọn nguồn gốc lỗi --</option>
                                        <option value="customer_site">Site khách hàng</option>
                                        <option value="internal_test">Phát hiện nội bộ</option>
                                        <option value="production">Lỗi sản xuất</option>
                                        <option value="other">Khác</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="received_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày nhận module lỗi</label>
                                    <input type="date" id="received_date" name="received_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <label for="defect_description" class="block text-sm font-medium text-gray-700 mb-1">Mô tả lỗi</label>
                                <textarea id="defect_description" name="defect_description" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập mô tả chi tiết về lỗi"></textarea>
                            </div>
                        </div>

                        <!-- Thông tin kiểm thử thiết bị mới (ẩn mặc định) -->
                        <div id="new_device_fields" class="mt-4 hidden">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="assembly_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày lắp ráp</label>
                                    <input type="date" id="assembly_date" name="assembly_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                </div>
                                
                                <div>
                                    <label for="software_version" class="block text-sm font-medium text-gray-700 mb-1">Phiên bản phần mềm</label>
                                    <input type="text" id="software_version" name="software_version" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="VD: 1.2.5">
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <label for="device_config" class="block text-sm font-medium text-gray-700 mb-1">Cấu hình thiết bị</label>
                                <textarea id="device_config" name="device_config" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập thông tin cấu hình thiết bị"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Hạng mục kiểm thử và kết quả -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mt-6">Hạng mục kiểm thử và kết quả</h2>
                        
                        <div class="mt-4">
                            <table class="min-w-full divide-y divide-gray-200 border">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hạng mục</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Đánh dấu</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kết quả</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Kiểm tra phần cứng</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <input type="checkbox" id="hardware_test" name="test_items[]" value="hardware_inspection" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <select name="test_results[hardware_inspection]" class="w-full border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                                <option value="pass" selected>Đạt</option>
                                                <option value="fail">Không đạt</option>
                                                <option value="na">Không áp dụng</option>
                                            </select>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            <input type="text" name="test_notes[hardware_inspection]" class="w-full border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="Kiểm tra chân cắm và các kết nối đầy đủ">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Kiểm tra phần mềm</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <input type="checkbox" id="software_test" name="test_items[]" value="software_test" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <select name="test_results[software_test]" class="w-full border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                                <option value="pass" selected>Đạt</option>
                                                <option value="fail">Không đạt</option>
                                                <option value="na">Không áp dụng</option>
                                            </select>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            <input type="text" name="test_notes[software_test]" class="w-full border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="Firmware v3.2.1 hoạt động ổn định">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Kiểm tra kết nối/truyền thông</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <input type="checkbox" id="communication_test" name="test_items[]" value="communication_test" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <select name="test_results[communication_test]" class="w-full border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                                <option value="pass" selected>Đạt</option>
                                                <option value="fail">Không đạt</option>
                                                <option value="na">Không áp dụng</option>
                                            </select>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            <input type="text" name="test_notes[communication_test]" class="w-full border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="Kết nối mạng ổn định, tốc độ đạt yêu cầu">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Kiểm tra chức năng</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <input type="checkbox" id="functionality_test" name="test_items[]" value="functionality_test" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <select name="test_results[functionality_test]" class="w-full border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                                <option value="pass" selected>Đạt</option>
                                                <option value="fail">Không đạt</option>
                                                <option value="na">Không áp dụng</option>
                                            </select>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            <input type="text" name="test_notes[functionality_test]" class="w-full border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="Tất cả chức năng hoạt động theo yêu cầu">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Kiểm tra độ bền (stress test)</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <input type="checkbox" id="stress_test" name="test_items[]" value="stress_test" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <select name="test_results[stress_test]" class="w-full border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                                <option value="pass" selected>Đạt</option>
                                                <option value="fail">Không đạt</option>
                                                <option value="na">Không áp dụng</option>
                                            </select>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            <input type="text" name="test_notes[stress_test]" class="w-full border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="Hoạt động ổn định trong 72 giờ liên tục">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Kiểm tra khả năng tương thích</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <input type="checkbox" id="compatibility_test" name="test_items[]" value="compatibility_test" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <select name="test_results[compatibility_test]" class="w-full border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                                <option value="pass">Đạt</option>
                                                <option value="fail">Không đạt</option>
                                                <option value="na" selected>Không áp dụng</option>
                                            </select>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            <input type="text" name="test_notes[compatibility_test]" class="w-full border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Kết quả kiểm thử -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mt-6">Kết quả kiểm thử</h2>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="test_result" class="block text-sm font-medium text-gray-700 mb-1 required">Kết quả kiểm thử</label>
                                <select id="test_result" name="test_result" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required onchange="toggleAdditionalFields()">
                                    <option value="pass" selected>Đạt</option>
                                    <option value="fail">Không đạt</option>
                                    <option value="repaired">Đã sửa</option>
                                    <option value="unrepairable">Không thể sửa chữa</option>
                                </select>
                            </div>

                            <div id="requires_approval_field" class="hidden">
                                <label for="requires_approval" class="block text-sm font-medium text-gray-700 mb-1">Yêu cầu phê duyệt</label>
                                <select id="requires_approval" name="requires_approval" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                    <option value="0">Không</option>
                                    <option value="1" selected>Có</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label for="conclusion" class="block text-sm font-medium text-gray-700 mb-1 required">Kết luận</label>
                            <textarea id="conclusion" name="conclusion" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>Module hoạt động ổn định và đáp ứng tất cả yêu cầu kỹ thuật.</textarea>
                        </div>
                        
                        <!-- <div class="mt-4">
                            <label for="tester_comment" class="block text-sm font-medium text-gray-700 mb-1">Đánh giá của người kiểm thử</label>
                            <textarea id="tester_comment" name="tester_comment" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">Module 4G đạt chất lượng để đưa vào sản xuất. Tất cả thông số kỹ thuật đều đạt yêu cầu. Đặc biệt kết nối mạng ổn định trong điều kiện tín hiệu yếu.</textarea>
                        </div>
                        
                        <div class="mt-4">
                            <label for="additional_requirements" class="block text-sm font-medium text-gray-700 mb-1">Yêu cầu bổ sung (nếu có)</label>
                            <textarea id="additional_requirements" name="additional_requirements" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">Không có yêu cầu bổ sung.</textarea>
                        </div>
                        
                        <div class="mt-4">
                            <label for="recommendations" class="block text-sm font-medium text-gray-700 mb-1">Đề xuất</label>
                            <textarea id="recommendations" name="recommendations" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">Có thể tiến hành đặt hàng thêm module cùng loại từ nhà cung cấp này cho các đợt sản xuất tiếp theo.</textarea>
                        </div> -->
                    </div>

                    <!-- Thông tin phê duyệt -->
                    <!-- <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mt-6">Thông tin phê duyệt</h2>
                        
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="manager_id" class="block text-sm font-medium text-gray-700 mb-1">Quản lý phê duyệt</label>
                                <select id="manager_id" name="manager_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                    <option value="">-- Chọn quản lý phê duyệt --</option>
                                    <option value="2" selected>Trần Văn B</option>
                                    <option value="6">Hoàng Văn F</option>
                                    <option value="7">Đỗ Thị G</option>
                                </select>
                            </div>

                            <div>
                                <label for="approval_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày phê duyệt</label>
                                <input type="date" id="approval_date" name="approval_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="2024-06-16">
                            </div>
                        </div>
                    </div> -->

                    <!-- Submit buttons -->
                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-3">
                        <a href="{{ url('/testing/1') }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center justify-center transition-colors">
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

    <script>
        // Toggle fields based on test type
        function toggleTestTypeFields() {
            const testType = document.getElementById('test_type').value;
            
            // Hide all specific fields first
            document.getElementById('new_component_fields').classList.add('hidden');
            document.getElementById('defective_fields').classList.add('hidden');
            document.getElementById('new_device_fields').classList.add('hidden');
            
            // Show fields based on test type
            if(testType === 'new_component') {
                document.getElementById('new_component_fields').classList.remove('hidden');
            } else if(testType === 'defective') {
                document.getElementById('defective_fields').classList.remove('hidden');
            } else if(testType === 'new_device') {
                document.getElementById('new_device_fields').classList.remove('hidden');
            }
        }
        
        // Toggle additional fields based on test result
        function toggleAdditionalFields() {
            const testResult = document.getElementById('test_result').value;
            
            if(testResult === 'fail' || testResult === 'unrepairable') {
                document.getElementById('requires_approval_field').classList.remove('hidden');
            } else {
                document.getElementById('requires_approval_field').classList.add('hidden');
            }
        }

        // Hàm toggleDropdown cho sidebar
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