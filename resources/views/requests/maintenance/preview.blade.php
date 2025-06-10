<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xem trước phiếu bảo trì dự án - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8fafc;
            padding: 20px;
        }
        .report-container {
            background-color: white;
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .report-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
        }
        .company-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        .section-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: #1a365d;
            text-transform: uppercase;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #e5e7eb;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            font-weight: 500;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }
        .signature-box {
            text-align: center;
            width: 30%;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            margin-bottom: 10px;
        }
        @media print {
            body {
                background-color: white;
                padding: 0;
                margin: 0;
            }
            .report-container {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print bg-blue-600 text-white py-4 px-6 mb-4 flex justify-between items-center">
        <h1 class="text-xl font-bold">Xem trước phiếu bảo trì dự án</h1>
        <div class="flex space-x-2">
            <button onclick="window.print()" class="bg-white text-blue-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-print mr-2"></i> In phiếu
            </button>
            <button onclick="exportToExcel()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-file-excel mr-2"></i> Xuất Excel
            </button>
            <a href="{{ url('/requests/maintenance/'.$id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="report-container">
        <div class="report-header">
            <div class="flex justify-between items-center mb-4">
                <img src="{{ asset('images/logo.png') }}" alt="Logo công ty" height="60" class="h-16">
                <div class="text-right">
                    <h2 class="text-xl font-bold text-blue-800">CÔNG TY SGL TECH</h2>
                    <p class="text-sm text-gray-600">Địa chỉ: 123 Đường ABC, Quận 1, TP.HCM</p>
                    <p class="text-sm text-gray-600">Điện thoại: (028) 3456 7890</p>
                </div>
            </div>
            <h1 class="text-2xl font-bold text-center text-gray-800 mt-6">PHIẾU BẢO TRÌ DỰ ÁN</h1>
            <p class="text-center text-gray-600">Mã phiếu: MAINT-{{ str_pad($id, 4, '0', STR_PAD_LEFT) }}</p>
        </div>

        <div class="company-info">
            <div>
                <p class="text-sm"><strong>Ngày lập phiếu:</strong> 20/06/2024</p>
                <p class="text-sm"><strong>Kỹ thuật đề xuất:</strong> Trần Minh Trí</p>
            </div>
            <div class="text-right">
                <p class="text-sm"><strong>Trạng thái:</strong> Đang xử lý</p>
                <p class="text-sm"><strong>Loại bảo trì:</strong> Định kỳ</p>
            </div>
        </div>

        <div class="section">
            <h2 class="section-title">THÔNG TIN DỰ ÁN</h2>
            <table class="w-full">
                <tr>
                    <td class="w-1/4 bg-gray-50"><strong>Tên dự án:</strong></td>
                    <td>Hệ thống giám sát Tân Thành</td>
                </tr>
                <tr>
                    <td class="bg-gray-50"><strong>Đối tác:</strong></td>
                    <td>Viễn Thông A</td>
                </tr>
                <tr>
                    <td class="bg-gray-50"><strong>Địa chỉ dự án:</strong></td>
                    <td>456 Đường Lê Hồng Phong, Phường Tân Thành, Quận Tân Phú, TP.HCM</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2 class="section-title">THÔNG TIN KHÁCH HÀNG</h2>
            <table class="w-full">
                <tr>
                    <td class="w-1/4 bg-gray-50"><strong>Tên khách hàng:</strong></td>
                    <td>Lê Quang Hưng</td>
                </tr>
                <tr>
                    <td class="bg-gray-50"><strong>Số điện thoại:</strong></td>
                    <td>0987654321</td>
                </tr>
                <tr>
                    <td class="bg-gray-50"><strong>Email:</strong></td>
                    <td>hung.le@vienthonga.com</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2 class="section-title">THÔNG TIN BẢO TRÌ</h2>
            <table class="w-full">
                <tr>
                    <td class="w-1/4 bg-gray-50"><strong>Ngày bảo trì dự kiến:</strong></td>
                    <td>05/07/2024</td>
                </tr>
                <tr>
                    <td class="bg-gray-50"><strong>Lý do bảo trì:</strong></td>
                    <td>Bảo trì định kỳ 6 tháng theo hợp đồng. Kiểm tra các thiết bị camera, hệ thống lưu trữ và nâng cấp phần mềm quản lý.</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2 class="section-title">VẬT TƯ CẦN THIẾT</h2>
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="w-1/12">STT</th>
                        <th class="w-7/12">Tên vật tư</th>
                        <th class="w-2/12">Số lượng</th>
                        <th class="w-2/12">Đơn vị</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Ổ cứng lưu trữ 4TB</td>
                        <td>2</td>
                        <td>Cái</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Bộ nguồn dự phòng</td>
                        <td>1</td>
                        <td>Bộ</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2 class="section-title">NHÂN SỰ THỰC HIỆN</h2>
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="w-1/12">STT</th>
                        <th>Họ và tên</th>
                        <th class="w-3/12">Vị trí</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Nguyễn Văn An</td>
                        <td>Kỹ thuật viên</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Phạm Thị Bình</td>
                        <td>Kỹ thuật viên</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2 class="section-title">GHI CHÚ</h2>
            <p class="text-gray-700 text-sm">Bảo trì định kỳ đã được thông báo cho khách hàng trước 2 tuần. Cần chuẩn bị đầy đủ vật tư và liên hệ xác nhận lịch trước khi thực hiện.</p>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <p><strong>Người lập phiếu</strong></p>
                <div class="signature-line"></div>
                <p>Trần Minh Trí</p>
            </div>
            <div class="signature-box">
                <p><strong>Phụ trách kỹ thuật</strong></p>
                <div class="signature-line"></div>
                <p></p>
            </div>
            <div class="signature-box">
                <p><strong>Người phê duyệt</strong></p>
                <div class="signature-line"></div>
                <p></p>
            </div>
        </div>
    </div>

    <script>
        function exportToExcel() {
            alert('Đang xuất file Excel...');
            // Thực tế sẽ gọi API hoặc sử dụng thư viện để xuất Excel
        }
    </script>
</body>
</html> 