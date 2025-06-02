<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Công ty TNHH Thiết bị điện tử ABC',
                'phone' => '0912345678',
                'email' => 'info@abcelectronics.com',
                'address' => '123 Lê Lợi, Quận 1, TP.HCM',
                'notes' => 'Nhà cung cấp thiết bị điện tử chính',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Công ty CP Thiết bị công nghiệp XYZ',
                'phone' => '0987654321',
                'email' => 'sales@xyztechnology.com',
                'address' => '456 Nguyễn Huệ, Quận 3, TP.HCM',
                'notes' => 'Cung cấp linh kiện công nghiệp và thiết bị đo lường',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Công ty TNHH Phần cứng DEF',
                'phone' => '0901234567',
                'email' => 'contact@defhardware.com',
                'address' => '789 Lý Tự Trọng, Quận 5, TP.HCM',
                'notes' => 'Chuyên cung cấp linh kiện máy tính và phụ kiện',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Công ty CP Thiết bị mạng GHI',
                'phone' => '0919876543',
                'email' => 'info@ghinetworks.com',
                'address' => '101 Nguyễn Thái Học, Quận 10, TP.HCM',
                'notes' => 'Cung cấp thiết bị mạng, router và switch',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tập đoàn Kỹ thuật số JKL',
                'phone' => '0909876543',
                'email' => 'support@jkldigital.com',
                'address' => '222 Trần Hưng Đạo, Hà Nội',
                'notes' => 'Phân phối thiết bị kỹ thuật số và giải pháp IoT',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Công ty TNHH An ninh mạng MNO',
                'phone' => '0978123456',
                'email' => 'security@mnocorp.com',
                'address' => '333 Lê Duẩn, Đà Nẵng',
                'notes' => 'Cung cấp giải pháp bảo mật và an ninh mạng',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Công ty CP Phần mềm PQR',
                'phone' => '0912987654',
                'email' => 'info@pqrsoftware.com',
                'address' => '444 Hai Bà Trưng, TP.HCM',
                'notes' => 'Phát triển và phân phối phần mềm doanh nghiệp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tập đoàn Máy tính STU',
                'phone' => '0909123456',
                'email' => 'sales@stucomputers.com',
                'address' => '555 Điện Biên Phủ, Hà Nội',
                'notes' => 'Nhập khẩu và phân phối máy tính, laptop',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Công ty TNHH Vượt Trội',
                'phone' => '0978654321',
                'email' => 'contact@vuottroi.com',
                'address' => '666 Nguyễn Đình Chính, TP.HCM',
                'notes' => 'Cung cấp giải pháp quản lý doanh nghiệp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Công ty CP Công nghệ Tiến Phát',
                'phone' => '0988776655',
                'email' => 'info@tienphat.com',
                'address' => '777 Cách Mạng Tháng 8, TP.HCM',
                'notes' => 'Nhà phân phối thiết bị công nghệ cao',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::create($supplierData);
        }
    }
}
