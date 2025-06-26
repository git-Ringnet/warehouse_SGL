<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ChangeLog;

class ChangeLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $changeLogs = [
            [
                'time_changed' => '2023-12-15 08:30:12',
                'item_code' => 'VT001',
                'item_name' => 'Bo mạch điều khiển chính v2.1',
                'change_type' => 'lap_rap',
                'document_code' => 'LR001',
                'quantity' => 5,
                'description' => 'Sử dụng vật tư cho phiếu lắp ráp thiết bị IoT Smart City theo hợp đồng HD001/2024. Vật tư đã được kiểm tra chất lượng và đáp ứng yêu cầu kỹ thuật.',
                'performed_by' => 'Nguyễn Văn A',
                'notes' => 'Đã kiểm tra chất lượng - Vật tư đạt tiêu chuẩn ISO 9001 và đã qua kiểm định chất lượng đầu vào.',
                'detailed_info' => [
                    'ma_phieu_lap_rap' => 'LR001/2023',
                    'san_pham_dich' => 'Thiết bị IoT Smart City',
                    'kho_xuat' => 'Kho vật tư chính',
                    'vi_tri' => 'Kệ A-02, Ngăn 15',
                    'thoi_gian_hoan_thanh' => '08:45:30',
                    'trang_thai' => 'Đã hoàn thành'
                ]
            ],
            [
                'time_changed' => '2023-12-15 10:15:45',
                'item_code' => 'SP002',
                'item_name' => 'Cảm biến nhiệt độ độ chính xác cao',
                'change_type' => 'xuat_kho',
                'document_code' => 'XK002',
                'quantity' => 10,
                'description' => 'Xuất kho giao cho khách hàng ABC',
                'performed_by' => 'Trần Thị B',
                'notes' => 'Giao hàng theo hợp đồng HD001',
                'detailed_info' => [
                    'khach_hang' => 'Công ty ABC',
                    'dia_chi_giao' => 'Số 123, Đường XYZ, Hà Nội',
                    'nguoi_nhan' => 'Lê Văn C',
                    'so_dien_thoai' => '0123456789'
                ]
            ],
            [
                'time_changed' => '2023-12-16 14:30:00',
                'item_code' => 'DEV001',
                'item_name' => 'Bộ điều khiển chính - SN001122',
                'change_type' => 'sua_chua',
                'document_code' => 'SC003',
                'quantity' => 1,
                'description' => 'Bảo trì định kỳ và thay thế linh kiện',
                'performed_by' => 'Lê Văn C',
                'notes' => 'Thiết bị hoạt động bình thường',
                'detailed_info' => [
                    'loai_sua_chua' => 'Bảo trì định kỳ',
                    'linh_kien_thay_the' => ['Tụ điện C1', 'Điện trở R5'],
                    'thoi_gian_sua_chua' => '2 giờ',
                    'ket_qua_kiem_tra' => 'Đạt yêu cầu'
                ]
            ],
            [
                'time_changed' => '2023-12-17 09:22:30',
                'item_code' => 'SP003',
                'item_name' => 'Màn hình hiển thị TFT 7 inch',
                'change_type' => 'thu_hoi',
                'document_code' => 'TH004',
                'quantity' => 3,
                'description' => 'Thu hồi sản phẩm lỗi từ khách hàng',
                'performed_by' => 'Phạm Thị D',
                'notes' => 'Lỗi màn hình hiển thị',
                'detailed_info' => [
                    'ly_do_thu_hoi' => 'Lỗi hiển thị không ổn định',
                    'khach_hang' => 'Công ty DEF',
                    'ngay_ban' => '2023-11-15',
                    'tinh_trang' => 'Cần sửa chữa'
                ]
            ],
            [
                'time_changed' => '2023-12-18 11:45:15',
                'item_code' => 'VT025',
                'item_name' => 'Dây cáp nguồn 12V',
                'change_type' => 'nhap_kho',
                'document_code' => 'NK005',
                'quantity' => 50,
                'description' => 'Nhập kho vật tư mới từ nhà cung cấp',
                'performed_by' => 'Hoàng Văn E',
                'notes' => 'Đã kiểm tra chất lượng',
                'detailed_info' => [
                    'nha_cung_cap' => 'Công ty Điện tử GHI',
                    'so_hoa_don' => 'HD2023001',
                    'gia_nhap' => 25000,
                    'chat_luong' => 'Đạt tiêu chuẩn'
                ]
            ],
            [
                'time_changed' => '2023-12-19 15:20:00',
                'item_code' => 'HH010',
                'item_name' => 'Hộp đựng thiết bị nhựa ABS',
                'change_type' => 'chuyen_kho',
                'document_code' => 'CK006',
                'quantity' => 25,
                'description' => 'Chuyển từ kho Hà Nội sang kho Hồ Chí Minh',
                'performed_by' => 'Vũ Thị F',
                'notes' => 'Phân phối theo kế hoạch',
                'detailed_info' => [
                    'kho_xuat' => 'Kho Hà Nội',
                    'kho_nhap' => 'Kho Hồ Chí Minh',
                    'van_chuyen_boi' => 'Xe tải 5 tấn',
                    'thoi_gian_du_kien' => '2 ngày'
                ]
            ]
        ];

        foreach ($changeLogs as $log) {
            ChangeLog::create($log);
        }
    }
}
