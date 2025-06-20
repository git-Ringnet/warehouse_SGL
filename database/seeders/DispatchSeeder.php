<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\Warehouse;
use App\Models\Employee;
use App\Models\Material;
use App\Models\Product;
use App\Models\Good;
use Carbon\Carbon;

class DispatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo nhiều phiếu xuất mẫu với các loại dispatch_detail khác nhau
        $dispatches = [
            // Phiếu xuất tất cả (all)
            [
                'dispatch_code' => 'XK' . date('Ymd') . '-001',
                'dispatch_date' => Carbon::now(),
                'dispatch_type' => 'project',
                'dispatch_detail' => 'all',
                'project_receiver' => 'Dự án IoT Smart Home - Công ty TNHH ABC Tech',
                'warranty_period' => '12 tháng',
                'company_representative_id' => 1,
                'warehouse_id' => 1,
                'dispatch_note' => 'Xuất toàn bộ thiết bị theo đơn đặt hàng số ĐH-2024-001',
                'status' => 'pending',
                'created_by' => 1,
            ],
            // Phiếu xuất theo hợp đồng (contract)
            [
                'dispatch_code' => 'XK' . date('Ymd') . '-002',
                'dispatch_date' => Carbon::now()->subDays(1),
                'dispatch_type' => 'project',
                'dispatch_detail' => 'contract',
                'project_receiver' => 'Dự án Smart City Đà Nẵng - Công ty CP XYZ Solutions',
                'warranty_period' => '24 tháng',
                'company_representative_id' => 2,
                'warehouse_id' => 1,
                'dispatch_note' => 'Xuất thiết bị theo hợp đồng HC-2024-089 - Giai đoạn 1',
                'status' => 'approved',
                'created_by' => 1,
                'approved_by' => 1,
                'approved_at' => Carbon::now()->subHours(2),
            ],
            // Phiếu xuất dự phòng (backup)
            [
                'dispatch_code' => 'XK' . date('Ymd') . '-003',
                'dispatch_date' => Carbon::now()->subDays(2),
                'dispatch_type' => 'rental',
                'dispatch_detail' => 'backup',
                'project_receiver' => 'Dự án Nhà máy thông minh 4.0 - Tập đoàn MNO Corp',
                'warranty_period' => '18 tháng',
                'company_representative_id' => 1,
                'warehouse_id' => 2,
                'dispatch_note' => 'Xuất thiết bị dự phòng để thay thế khi cần thiết',
                'status' => 'completed',
                'created_by' => 1,
                'approved_by' => 1,
                'approved_at' => Carbon::now()->subDays(1),
            ],
            // Thêm phiếu xuất theo hợp đồng khác
            [
                'dispatch_code' => 'XK' . date('Ymd') . '-004',
                'dispatch_date' => Carbon::now()->subDays(3),
                'dispatch_type' => 'project',
                'dispatch_detail' => 'contract',
                'project_receiver' => 'Dự án Tự động hóa sản xuất - Công ty TNHH PQR Manufacturing',
                'warranty_period' => '36 tháng',
                'company_representative_id' => 2,
                'warehouse_id' => 1,
                'dispatch_note' => 'Xuất thiết bị chính theo hợp đồng HC-2024-125',
                'status' => 'approved',
                'created_by' => 1,
                'approved_by' => 1,
                'approved_at' => Carbon::now()->subDays(2),
            ],
            // Thêm phiếu xuất dự phòng khác
            [
                'dispatch_code' => 'XK' . date('Ymd') . '-005',
                'dispatch_date' => Carbon::now()->subDays(4),
                'dispatch_type' => 'warranty',
                'dispatch_detail' => 'backup',
                'project_receiver' => 'Kho dự phòng khu vực miền Nam - Chi nhánh HCM',
                'warranty_period' => '12 tháng',
                'company_representative_id' => 1,
                'warehouse_id' => 2,
                'dispatch_note' => 'Chuyển thiết bị dự phòng về kho chi nhánh',
                'status' => 'completed',
                'created_by' => 1,
                'approved_by' => 1,
                'approved_at' => Carbon::now()->subDays(3),
            ],
        ];

        foreach ($dispatches as $dispatchData) {
            $dispatch = Dispatch::create($dispatchData);

            // Tạo các items cho mỗi dispatch tùy theo loại
            $this->createDispatchItems($dispatch);
        }
    }

    private function createDispatchItems(Dispatch $dispatch)
    {
        // Tạo items khác nhau tùy theo dispatch_detail
        switch ($dispatch->dispatch_detail) {
            case 'all':
                // Xuất tất cả: bao gồm cả thiết bị chính và dự phòng
                $items = [
                    // Thiết bị theo hợp đồng
                    ['type' => 'material', 'id' => 1, 'quantity' => 10, 'category' => 'contract', 'serials' => ['MC001', 'MC002', 'MC003', 'MC004', 'MC005', 'MC006', 'MC007', 'MC008', 'MC009', 'MC010'], 'notes' => 'Bo mạch chủ theo hợp đồng'],
                    ['type' => 'product', 'id' => 1, 'quantity' => 5, 'category' => 'contract', 'serials' => ['SP001', 'SP002', 'SP003', 'SP004', 'SP005'], 'notes' => 'Bộ điều khiển theo hợp đồng'],
                    ['type' => 'good', 'id' => 1, 'quantity' => 8, 'category' => 'contract', 'serials' => ['SN001', 'SN002', 'SN003', 'SN004', 'SN005', 'SN006', 'SN007', 'SN008'], 'notes' => 'Cảm biến theo hợp đồng'],
                    // Thiết bị dự phòng
                    ['type' => 'material', 'id' => 2, 'quantity' => 3, 'category' => 'backup', 'serials' => ['MC101', 'MC102', 'MC103'], 'notes' => 'Bo mạch dự phòng'],
                    ['type' => 'product', 'id' => 2, 'quantity' => 2, 'category' => 'backup', 'serials' => ['SP101', 'SP102'], 'notes' => 'Bộ điều khiển dự phòng'],
                ];
                break;
                
            case 'contract':
                // Xuất theo hợp đồng: chỉ thiết bị chính theo đúng hợp đồng
                $items = [
                    ['type' => 'material', 'id' => 1, 'quantity' => 15, 'category' => 'contract', 'serials' => ['HC001', 'HC002', 'HC003', 'HC004', 'HC005', 'HC006', 'HC007', 'HC008', 'HC009', 'HC010', 'HC011', 'HC012', 'HC013', 'HC014', 'HC015'], 'notes' => 'Bo mạch chủ theo hợp đồng'],
                    ['type' => 'product', 'id' => 1, 'quantity' => 8, 'category' => 'contract', 'serials' => ['HCP001', 'HCP002', 'HCP003', 'HCP004', 'HCP005', 'HCP006', 'HCP007', 'HCP008'], 'notes' => 'Bộ điều khiển theo hợp đồng'],
                    ['type' => 'good', 'id' => 1, 'quantity' => 12, 'category' => 'contract', 'serials' => ['HCG001', 'HCG002', 'HCG003', 'HCG004', 'HCG005', 'HCG006', 'HCG007', 'HCG008', 'HCG009', 'HCG010', 'HCG011', 'HCG012'], 'notes' => 'Cảm biến theo yêu cầu hợp đồng'],
                    ['type' => 'material', 'id' => 3, 'quantity' => 6, 'category' => 'contract', 'serials' => ['HCM001', 'HCM002', 'HCM003', 'HCM004', 'HCM005', 'HCM006'], 'notes' => 'Màn hình hiển thị theo hợp đồng'],
                ];
                break;
                
            case 'backup':
                // Xuất dự phòng: chỉ thiết bị dự phòng
                $items = [
                    ['type' => 'material', 'id' => 2, 'quantity' => 5, 'category' => 'backup', 'serials' => ['BP001', 'BP002', 'BP003', 'BP004', 'BP005'], 'notes' => 'Bo mạch dự phòng'],
                    ['type' => 'product', 'id' => 2, 'quantity' => 3, 'category' => 'backup', 'serials' => ['BPP001', 'BPP002', 'BPP003'], 'notes' => 'Bộ điều khiển dự phòng'],
                    ['type' => 'good', 'id' => 2, 'quantity' => 7, 'category' => 'backup', 'serials' => ['BPG001', 'BPG002', 'BPG003', 'BPG004', 'BPG005', 'BPG006', 'BPG007'], 'notes' => 'Thiết bị dự phòng cho sự cố'],
                    ['type' => 'material', 'id' => 4, 'quantity' => 4, 'category' => 'backup', 'serials' => ['BPM001', 'BPM002', 'BPM003', 'BPM004'], 'notes' => 'Nguồn dự phòng'],
                ];
                break;
        }

        foreach ($items as $item) {
            DispatchItem::create([
                'dispatch_id' => $dispatch->id,
                'item_type' => $item['type'],
                'item_id' => $item['id'],
                'quantity' => $item['quantity'],
                'category' => $item['category'],
                'serial_numbers' => $item['serials'], // Sẽ được cast thành JSON tự động
                'notes' => $item['notes'],
            ]);
        }
    }
}
