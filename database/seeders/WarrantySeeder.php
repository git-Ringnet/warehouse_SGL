<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Warranty;
use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\Material;
use App\Models\Product;
use App\Models\Good;
use App\Models\User;
use Carbon\Carbon;

class WarrantySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy các dispatch items đã có
        $dispatchItems = DispatchItem::with('dispatch')->get();
        
        if ($dispatchItems->isEmpty()) {
            $this->command->info('Không có dispatch items nào. Vui lòng chạy DispatchSeeder trước.');
            return;
        }

        // Mẫu dữ liệu khách hàng cho bảo hành điện tử
        $customers = [
            [
                'name' => 'Công ty TNHH Công nghệ ABC',
                'phone' => '0901234567',
                'email' => 'contact@abc-tech.com',
                'address' => '123 Nguyễn Văn Cừ, Quận 1, TP.HCM',
            ],
            [
                'name' => 'Công ty CP Điện tử XYZ',
                'phone' => '0987654321',
                'email' => 'info@xyz-electronics.com',
                'address' => '456 Lê Văn Việt, Quận 9, TP.HCM',
            ],
            [
                'name' => 'Tập đoàn Công nghiệp MNO',
                'phone' => '0912345678',
                'email' => 'support@mno-group.com',
                'address' => '789 Hoàng Diệu, Quận 4, TP.HCM',
            ],
            [
                'name' => 'Công ty TNHH Tự động hóa DEF',
                'phone' => '0934567890',
                'email' => 'sales@def-automation.com',
                'address' => '321 Nguyễn Thị Minh Khai, Quận 3, TP.HCM',
            ],
            [
                'name' => 'Viện Nghiên cứu IoT GHI',
                'phone' => '0965432109',
                'email' => 'research@ghi-iot.edu.vn',
                'address' => '654 Võ Văn Tần, Quận 3, TP.HCM',
            ],
        ];

        // Mẫu dự án điện tử
        $projects = [
            'Hệ thống IoT nhà thông minh',
            'Dự án Smart Factory 4.0',
            'Hệ thống giám sát môi trường',
            'Dự án City Management System',
            'Hệ thống tự động hóa văn phòng',
            'Dự án Smart Agriculture',
            'Hệ thống quản lý năng lượng',
            'Dự án Digital Transformation',
        ];

        // Điều khoản bảo hành mẫu
        $warrantyTerms = [
            'standard' => 'Bảo hành miễn phí trong thời gian quy định. Bao gồm sửa chữa, thay thế linh kiện lỗi do nhà sản xuất. Không bao gồm hư hỏng do thiên tai, sử dụng sai mục đích.',
            'extended' => 'Bảo hành mở rộng với dịch vụ hỗ trợ 24/7. Bao gồm bảo trì định kỳ, cập nhật firmware, thay thế linh kiện. Hỗ trợ từ xa qua internet.',
            'premium' => 'Bảo hành cao cấp với cam kết thời gian phản hồi 4 giờ. Bao gồm tất cả dịch vụ bảo hành mở rộng cộng với đào tạo nhân viên, tư vấn kỹ thuật.',
        ];

        $userId = User::first()->id ?? 1;
        $warrantyCount = 0;

        foreach ($dispatchItems as $dispatchItem) {
            // Tạo 1-2 warranty cho mỗi dispatch item
            $warrantyPerItem = rand(1, 2);
            
            for ($i = 0; $i < $warrantyPerItem; $i++) {
                $customer = $customers[array_rand($customers)];
                $project = $projects[array_rand($projects)];
                $warrantyType = ['standard', 'extended', 'premium'][rand(0, 2)];
                $warrantyPeriod = $warrantyType === 'standard' ? 12 : ($warrantyType === 'extended' ? 18 : 24);
                
                // Ngày bắt đầu bảo hành (từ ngày dispatch hoặc ngày kích hoạt)
                $startDate = $dispatchItem->dispatch->dispatch_date->copy()->addDays(rand(0, 30));
                $endDate = $startDate->copy()->addMonths($warrantyPeriod);
                
                // Trạng thái bảo hành
                $status = 'active';
                if ($endDate < now()) {
                    $status = 'expired';
                } elseif ($endDate->diffInDays(now()) < 30) {
                    $status = rand(0, 1) ? 'active' : 'claimed';
                }

                // Số serial ngẫu nhiên cho thiết bị điện tử
                $serialNumber = 'SN' . strtoupper(substr(md5(uniqid()), 0, 8));

                $warranty = Warranty::create([
                    'warranty_code' => Warranty::generateWarrantyCode(),
                    'dispatch_id' => $dispatchItem->dispatch_id,
                    'dispatch_item_id' => $dispatchItem->id,
                    'item_type' => $dispatchItem->item_type,
                    'item_id' => $dispatchItem->item_id,
                    'serial_number' => $serialNumber,
                    'customer_name' => $customer['name'],
                    'customer_phone' => $customer['phone'],
                    'customer_email' => $customer['email'],
                    'customer_address' => $customer['address'],
                    'project_name' => $project,
                    'purchase_date' => $dispatchItem->dispatch->dispatch_date,
                    'warranty_start_date' => $startDate,
                    'warranty_end_date' => $endDate,
                    'warranty_period_months' => $warrantyPeriod,
                    'warranty_type' => $warrantyType,
                    'status' => $status,
                    'warranty_terms' => $warrantyTerms[$warrantyType],
                    'notes' => $this->generateRandomNotes(),
                    'created_by' => $userId,
                    'activated_at' => $startDate,
                ]);

                // Tạo QR code cho warranty
                $warranty->generateQRCode();
                
                $warrantyCount++;
            }
        }

        $this->command->info("Đã tạo {$warrantyCount} bản ghi bảo hành điện tử.");
    }

    /**
     * Tạo ghi chú ngẫu nhiên cho bảo hành
     */
    private function generateRandomNotes()
    {
        $notes = [
            'Thiết bị được kiểm tra và đóng gói cẩn thận trước khi giao.',
            'Khách hàng đã được hướng dẫn sử dụng và bảo trì thiết bị.',
            'Sản phẩm đạt tiêu chuẩn chất lượng cao, đã qua kiểm định.',
            'Đã cung cấp đầy đủ tài liệu hướng dẫn và CD driver.',
            'Thiết bị có chức năng tự chẩn đoán lỗi tích hợp.',
            'Hỗ trợ cập nhật firmware từ xa qua internet.',
            'Sản phẩm tương thích với các tiêu chuẩn quốc tế.',
            'Đã thực hiện test đầy đủ trước khi xuất hàng.',
        ];

        return $notes[array_rand($notes)];
    }
} 