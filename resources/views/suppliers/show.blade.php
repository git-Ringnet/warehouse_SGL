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
    <script src="{{ asset('js/delete-modal.js') }}"></script>
</head>
<body>
    <x-sidebar-component />
    
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Chi tiết nhà cung cấp</h1>
                <div class="ml-4 px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    ID: #{{ $supplier->id }}
                </div>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('suppliers.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
                <a href="{{ route('suppliers.edit', $supplier->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
                <button id="deleteButton" 
                    data-id="{{ $supplier->id }}" 
                    data-name="{{ $supplier->name }}"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-trash-alt mr-2"></i> Xóa
                </button>
            </div>
        </header>

        @if(session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif

        @if(session('error'))
            <x-alert type="error" :message="session('error')" />
        @endif

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Thông tin cơ bản</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500 font-medium">Tên nhà cung cấp</p>
                                <p class="text-base text-gray-800 font-semibold">{{ $supplier->name }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500 font-medium">Người đại diện</p>
                                <p class="text-base text-gray-800 font-semibold">{{ $supplier->representative ?? 'Chưa cập nhật' }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500 font-medium">Số điện thoại</p>
                                <p class="text-base text-gray-800 font-semibold">{{ $supplier->phone }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500 font-medium">Email</p>
                                <p class="text-base text-gray-800 font-semibold">{{ $supplier->email ?? 'Chưa cập nhật' }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500 font-medium">Địa chỉ</p>
                                <p class="text-base text-gray-800 font-semibold">{{ $supplier->address ?? 'Chưa cập nhật' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Thống kê</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500 font-medium">Tổng số lượng đã nhập</p>
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl font-bold text-blue-600">{{ $materials->count() + $goods->count() }}</span>
                                    <span class="text-sm text-gray-500">vật tư/hàng hóa</span>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <p class="text-sm text-gray-500 font-medium">Vật tư</p>
                                <div class="flex items-center gap-2">
                                    <span class="text-xl font-bold text-blue-600">{{ $materials->count() }}</span>
                                    <span class="text-sm text-gray-500">vật tư</span>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <p class="text-sm text-gray-500 font-medium">Hàng hóa</p>
                                <div class="flex items-center gap-2">
                                    <span class="text-xl font-bold text-blue-600">{{ $goods->count() }}</span>
                                    <span class="text-sm text-gray-500">hàng hóa</span>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <p class="text-sm text-gray-500 font-medium">Ngày tạo</p>
                                <p class="text-base text-gray-800 font-semibold">{{ $supplier->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</p>
                            </div>
                            
                            <div class="mt-4">
                                <p class="text-sm text-gray-500 font-medium">Cập nhật lần cuối</p>
                                <p class="text-base text-gray-800 font-semibold">{{ $supplier->updated_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Ghi chú</h3>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                        <p class="text-base text-gray-900">{{ $supplier->notes ?? 'Không có ghi chú' }}</p>
                    </div>
                </div>
            </div>

            <!-- Danh sách vật tư của nhà cung cấp -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 mt-6 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Vật tư liên quan</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã vật tư</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên vật tư</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Danh mục</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Đơn vị</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($materials as $material)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $material->code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $material->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $material->category }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $material->unit }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <a href="{{ route('materials.show', $material->id) }}" class="text-blue-500 hover:text-blue-700">Xem chi tiết</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Không có vật tư nào từ nhà cung cấp này</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Danh sách hàng hóa của nhà cung cấp -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 mt-6 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Hàng hóa liên quan</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã hàng hóa</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên hàng hóa</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Danh mục</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Đơn vị</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($goods as $good)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $good->code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $good->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $good->category }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $good->unit }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <a href="{{ route('goods.show', $good->id) }}" class="text-blue-500 hover:text-blue-700">Xem chi tiết</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Không có hàng hóa nào từ nhà cung cấp này</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Delete functionality setup
        document.addEventListener('DOMContentLoaded', function() {
            initDeleteModal();
            
            // Attach click event to delete button
            document.getElementById('deleteButton').addEventListener('click', function() {
                // Get the supplier name from a data attribute to avoid JS issues
                const supplierName = this.getAttribute('data-name');
                const supplierId = this.getAttribute('data-id');
                openDeleteModal(supplierId, supplierName);
            });
        });

        // Override deleteCustomer function from delete-modal.js
        function deleteCustomer(id) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('suppliers.index') }}/" + id;
            
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