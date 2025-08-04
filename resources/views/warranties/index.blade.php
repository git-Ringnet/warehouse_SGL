<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý bảo hành điện tử - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>

<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header
            class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">Quản lý bảo hành điện tử</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <form method="GET" action="{{ route('warranties.index') }}" class="flex gap-2 w-full md:w-auto">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Tìm kiếm thiết bị, khách hàng..."
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full md:w-64" />
                    <select name="status" onchange="this.form.submit()"
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Còn hiệu lực
                        </option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Hết hạn</option>
                    </select>
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </header>   

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                STT</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Mã bảo hành</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Khách hàng</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Dự án</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Ngày kích hoạt</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Ngày hết hạn</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Trạng thái</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($warranties as $index => $warranty)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ ($warranties->currentPage() - 1) * $warranties->perPage() + $index + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $warranty->warranty_code }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <div>
                                        <div>
                                            @php
                                                $projectName = $warranty->project_name;
                                                $customerName = 'N/A';
                                                $projectDisplay = $projectName;
                                                
                                                // Tách tên khách hàng từ trong ngoặc đơn
                                                if (preg_match('/\((.*?)\)$/', $projectName, $matches)) {
                                                    $customerName = trim($matches[1]);
                                                    $projectDisplay = trim(str_replace('(' . $matches[1] . ')', '', $projectName));
                                                }
                                            @endphp
                                            {{ $customerName }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <div>
                                        <div>{{ $projectDisplay }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $warranty->warranty_start_date ? $warranty->warranty_start_date->format('d/m/Y') : '--/--/----' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $warranty->warranty_end_date ? $warranty->warranty_end_date->format('d/m/Y') : '--/--/----' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'active' => 'bg-green-100 text-green-800',
                                            'expired' => 'bg-red-100 text-red-800',
                                            'claimed' => 'bg-yellow-100 text-yellow-800',
                                            'void' => 'bg-gray-100 text-gray-800',
                                        ];
                                    @endphp
                                    <span
                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$warranty->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $warranty->status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                    <a href="{{ route('warranties.show', $warranty) }}">
                                        <button
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                            title="Xem chi tiết">
                                            <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                        </button>
                                    </a>

                                    <button onclick="showQRCode('{{ $warranty->warranty_code }}')"
                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-100 hover:bg-purple-500 transition-colors group"
                                        title="Xem QR Code">
                                        <i class="fas fa-qrcode text-purple-500 group-hover:text-white"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-clipboard-list text-4xl mb-4 text-gray-300"></i>
                                    <p class="text-lg">Không có dữ liệu bảo hành</p>
                                    <p class="text-sm">Chưa có bản ghi bảo hành nào trong hệ thống</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($warranties->hasPages())
                <div
                    class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-4 rounded-lg shadow-sm">
                    <div class="flex flex-1 justify-between sm:hidden">
                        @if ($warranties->onFirstPage())
                            <span
                                class="relative inline-flex items-center rounded-md border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400 cursor-not-allowed">Trang
                                trước</span>
                        @else
                            <a href="{{ $warranties->previousPageUrl() }}"
                                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Trang
                                trước</a>
                        @endif

                        @if ($warranties->hasMorePages())
                            <a href="{{ $warranties->nextPageUrl() }}"
                                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Trang
                                sau</a>
                        @else
                            <span
                                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400 cursor-not-allowed">Trang
                                sau</span>
                        @endif
                    </div>
                    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Hiển thị <span class="font-medium">{{ $warranties->firstItem() }}</span> đến <span
                                    class="font-medium">{{ $warranties->lastItem() }}</span> của
                                <span class="font-medium">{{ $warranties->total() }}</span> kết quả
                            </p>
                        </div>
                        <div>
                            {{ $warranties->appends(request()->query())->links('pagination::tailwind') }}
                        </div>
                    </div>
                </div>
            @endif
        </main>
    </div>

    <!-- QR Code Modal -->
    <div id="qr-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">QR Code bảo hành</h3>
                <button type="button" onclick="closeQrModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="text-center mb-4">
                <div id="qr-code-container" class="flex justify-center mb-3">
                    <!-- QR code will be generated here -->
                </div>
                <p class="text-sm text-gray-600">Quét mã QR này để kiểm tra thông tin bảo hành</p>
                <p id="warranty-code-text" class="text-sm font-medium text-gray-800 mt-2"></p>
            </div>
            <div class="flex justify-between space-x-3">
                <button type="button" onclick="downloadQR()"
                    class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors flex-1">
                    <i class="fas fa-download mr-2"></i> Tải xuống
                </button>
                <button type="button" onclick="printQR()"
                    class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors flex-1">
                    <i class="fas fa-print mr-2"></i> In mã QR
                </button>
            </div>
        </div>
    </div>

    <script>
        // Show QR Code Modal
        function showQRCode(warrantyCode) {
            const modal = document.getElementById('qr-modal');
            const container = document.getElementById('qr-code-container');
            const codeText = document.getElementById('warranty-code-text');

            // Generate QR code with full URL
            const warrantyUrl = `${window.location.origin}/warranty/check/${warrantyCode}`;
            const qrUrl =
                `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(warrantyUrl)}`;
            container.innerHTML = `<img src="${qrUrl}" alt="QR Code" class="mx-auto border rounded" style="max-width: 250px;">`;
            codeText.textContent = `Mã bảo hành: ${warrantyCode}`;

            modal.classList.remove('hidden');
        }

        // Close QR Code Modal
        function closeQrModal() {
            document.getElementById('qr-modal').classList.add('hidden');
        }

        // Download QR Code
        async function downloadQR() {
            const img = document.querySelector('#qr-code-container img');
            const warrantyCodeElement = document.getElementById('warranty-code-text');
            
            if (img && warrantyCodeElement) {
                try {
                    // Get warranty code from text
                    const warrantyCode = warrantyCodeElement.textContent.replace('Mã bảo hành: ', '');
                    
                    // Fetch QR image directly from API
                    const response = await fetch(img.src);
                    const blob = await response.blob();
                    
                    // Create download link
                    const url = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `QR_Code_${warrantyCode}.png`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);
                } catch (error) {
                    console.error('Lỗi khi tải QR code:', error);
                    alert('Có lỗi xảy ra khi tải QR code. Vui lòng thử lại.');
                }
            }
        }

        // Print QR Code
        function printQR() {
            const img = document.querySelector('#qr-code-container img');
            const warrantyCode = document.getElementById('warranty-code-text').textContent;

            if (img) {
                try {
                    const printWindow = window.open('', '_blank');
                    printWindow.document.write(`
                        <html>
                            <head>
                                <title>In QR Code Bảo hành</title>
                                <style>
                                    body { 
                                        text-align: center; 
                                        font-family: Arial, sans-serif; 
                                        margin: 50px;
                                        padding: 20px;
                                    }
                                    .qr-container { 
                                        margin: 30px 0; 
                                        display: flex;
                                        justify-content: center;
                                        align-items: center;
                                    }
                                    h1 { 
                                        color: #333; 
                                        margin-bottom: 20px;
                                        font-size: 24px;
                                    }
                                    p { 
                                        color: #666; 
                                        margin: 10px 0;
                                        font-size: 14px;
                                    }
                                    img {
                                        max-width: 300px;
                                        height: auto;
                                        border: 2px solid #ddd;
                                        border-radius: 8px;
                                    }
                                </style>
                            </head>
                            <body>
                                <h1>QR Code Bảo hành</h1>
                                <div class="qr-container">
                                    <img src="${img.src}" alt="QR Code" onload="window.print();">
                                </div>
                                <p><strong>${warrantyCode}</strong></p>
                                <p>Quét mã QR để kiểm tra thông tin bảo hành</p>
                            </body>
                        </html>
                    `);
                    printWindow.document.close();
                } catch (error) {
                    console.error('Lỗi khi in QR code:', error);
                    alert('Có lỗi xảy ra khi in QR code. Vui lòng thử lại.');
                }
            }
        }

        // Update Warranty Status


        // Close modal when clicking outside
        document.getElementById('qr-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeQrModal();
            }
        });
    </script>
</body>

</html>
