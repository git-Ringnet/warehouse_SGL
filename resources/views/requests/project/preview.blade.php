@extends('layouts.app')

@section('title', 'Xem trước phiếu đề xuất triển khai dự án - ' . $projectRequest->request_code)

@section('content')
<div class="container-fluid px-6 py-4">
    <div class="print-controls mb-4 flex justify-between items-center">
        <div class="flex space-x-2">
            <button onclick="window.print();" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center">
                <i class="fas fa-print mr-2"></i> In phiếu
            </button>
            <button onclick="exportToExcel();" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 flex items-center">
                <i class="fas fa-file-excel mr-2"></i> Xuất Excel
            </button>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('requests.project.show', $projectRequest->id) }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                Quay lại
            </a>
        </div>
    </div>
    
    <div class="excel-sheet" id="excel-content">
        <div class="excel-header">
            <div class="excel-title">PHIẾU ĐỀ XUẤT TRIỂN KHAI DỰ ÁN</div>
            <div class="excel-subtitle">Mã phiếu: {{ $projectRequest->request_code }} | Ngày tạo: {{ $projectRequest->request_date->format('d/m/Y') }}</div>
        </div>
        
        <div class="excel-section">
            <div class="excel-section-title">1. THÔNG TIN ĐỀ XUẤT</div>
            <div class="excel-row">
                <div class="excel-cell excel-cell-header">Ngày đề xuất:</div>
                <div class="excel-cell">{{ $projectRequest->request_date->format('d/m/Y') }}</div>
                <div class="excel-cell excel-cell-header">Kỹ thuật đề xuất:</div>
                <div class="excel-cell">{{ $projectRequest->proposer ? $projectRequest->proposer->name : 'Không có' }}</div>
            </div>
        </div>
        
        <div class="excel-section">
            <div class="excel-section-title">2. THÔNG TIN DỰ ÁN</div>
            <div class="excel-row">
                <div class="excel-cell excel-cell-header">Tên dự án:</div>
                <div class="excel-cell">{{ $projectRequest->project_name }}</div>
                <div class="excel-cell excel-cell-header">Đối tác:</div>
                <div class="excel-cell">{{ $projectRequest->customer ? $projectRequest->customer->company_name : $projectRequest->customer_name }}</div>
            </div>
            <div class="excel-row">
                <div class="excel-cell excel-cell-header">Địa chỉ dự án:</div>
                <div class="excel-cell" colspan="3">{{ $projectRequest->project_address }}</div>
            </div>
        </div>
        
        <div class="excel-section">
            <div class="excel-section-title">3. SẢN PHẨM ĐỀ XUẤT</div>
            <table class="excel-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">STT</th>
                        <th>Tên sản phẩm</th>
                        <th style="width: 100px;">Số lượng</th>
                        <th style="width: 100px;">Loại</th>
                    </tr>
                </thead>
                <tbody>
                    @php $index = 1; @endphp
                    @forelse($projectRequest->items as $item)
                        <tr>
                            <td style="text-align: center;">{{ $index++ }}</td>
                            <td>{{ $item->name }}</td>
                            <td style="text-align: center;">{{ $item->quantity }}</td>
                            <td style="text-align: center;">
                                @if($item->item_type == 'equipment')
                                    Thiết bị
                                @elseif($item->item_type == 'material')
                                    Vật tư
                                @elseif($item->item_type == 'good')
                                    Hàng hóa
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center;">Không có sản phẩm nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="excel-section">
            <div class="excel-section-title">4. THÔNG TIN LIÊN HỆ KHÁCH HÀNG</div>
            <div class="excel-row">
                <div class="excel-cell excel-cell-header">Tên khách hàng:</div>
                <div class="excel-cell">{{ $projectRequest->customer_name }}</div>
                <div class="excel-cell excel-cell-header">Số điện thoại:</div>
                <div class="excel-cell">{{ $projectRequest->customer_phone }}</div>
            </div>
            <div class="excel-row">
                <div class="excel-cell excel-cell-header">Email:</div>
                <div class="excel-cell">{{ $projectRequest->customer_email ?: 'Không có' }}</div>
                <div class="excel-cell excel-cell-header">Địa chỉ:</div>
                <div class="excel-cell">{{ $projectRequest->customer_address }}</div>
            </div>
        </div>
        
        <div class="excel-section">
            <div class="excel-section-title">5. GHI CHÚ</div>
            <div style="border: 1px solid #d1d5db; padding: 10px;">
                {!! nl2br(e($projectRequest->notes)) ?: 'Không có ghi chú' !!}
            </div>
        </div>
        
        <div class="excel-footer">
            <div class="excel-signature">
                <div class="excel-signature-title">Người đề xuất</div>
                <div class="excel-signature-name">{{ $projectRequest->proposer ? $projectRequest->proposer->name : '' }}</div>
            </div>
            <div class="excel-signature">
                <div class="excel-signature-title">Người thực hiện</div>
                <div class="excel-signature-name">{{ $projectRequest->implementer ? $projectRequest->implementer->name : '' }}</div>
            </div>
           
        </div>
    </div>
</div>

    <style>
        .excel-sheet {
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
            padding: 20px;
        }
        .excel-header {
            border-bottom: 2px solid #d1d5db;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .excel-title {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
            text-align: center;
            margin-bottom: 10px;
        }
        .excel-subtitle {
            font-size: 14px;
            color: #6b7280;
            text-align: center;
        }
        .excel-table {
            width: 100%;
            border-collapse: collapse;
        }
        .excel-table th, .excel-table td {
            border: 1px solid #d1d5db;
            padding: 8px;
        }
        .excel-table th {
            background-color: #e5e7eb;
            font-weight: 500;
            text-align: center;
        }
        .excel-table td {
            vertical-align: top;
        }
        .excel-section {
            margin-bottom: 20px;
        }
        .excel-section-title {
            font-weight: bold;
            margin-bottom: 10px;
            background-color: #e5e7eb;
            padding: 5px 10px;
        }
        .excel-row {
            display: flex;
            border-bottom: 1px solid #d1d5db;
        }
        .excel-row:last-child {
            border-bottom: none;
        }
        .excel-cell {
            padding: 8px;
            flex: 1;
            border-right: 1px solid #d1d5db;
        }
        .excel-cell:last-child {
            border-right: none;
        }
        .excel-cell-header {
            font-weight: 500;
            background-color: #f3f4f6;
        }
        .excel-footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .excel-signature {
            flex: 1;
            text-align: center;
            padding: 0 20px;
        }
        .excel-signature-title {
            font-weight: bold;
            margin-bottom: 60px;
        }
        .excel-signature-name {
            font-weight: 500;
        }
        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
            }
            .excel-sheet {
                box-shadow: none;
                border: none;
                max-width: 100%;
            }
            .print-controls {
                display: none;
            }
            .excel-footer {
                margin-top: 30px;
            }
        }
    </style>

@section('scripts')
    <script>
        function exportToExcel() {
            // Trong thực tế, sẽ cần một thư viện như SheetJS để xuất Excel đúng định dạng
            // Đây chỉ là mô phỏng hành động xuất Excel
            alert('Đang tải xuống file Excel...');
            
            // Đường dẫn thực tế đến API xuất file Excel
        // window.location.href = '/api/requests/project/{{ $projectRequest->id }}/export-excel';
        }
    </script>
@endsection
@endsection 