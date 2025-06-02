<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Nguyễn Văn B',
                'phone' => '0912345678',
                'email' => 'nguyenb@gmail.com',
                'address' => '123 Lê Lợi, Hà Nội',
                'notes' => 'Khách hàng thân thiết',
                'created_at' => '2024-05-01 00:00:00',
                'updated_at' => '2024-05-01 00:00:00',
            ],
            [
                'name' => 'Trần Thị C',
                'phone' => '0987654321',
                'email' => 'tranthic@gmail.com',
                'address' => '456 Trần Hưng Đạo, TP.HCM',
                'notes' => 'Khách hàng doanh nghiệp',
                'created_at' => '2024-04-15 00:00:00',
                'updated_at' => '2024-04-15 00:00:00',
            ],
            [
                'name' => 'Lê Văn D',
                'phone' => '0901234567',
                'email' => 'levand@gmail.com',
                'address' => '789 Nguyễn Trãi, Đà Nẵng',
                'notes' => 'Khách hàng mới',
                'created_at' => '2024-03-20 00:00:00',
                'updated_at' => '2024-03-20 00:00:00',
            ],
            [
                'name' => 'Vũ Thị M',
                'phone' => '0911111111',
                'email' => 'vum@gmail.com',
                'address' => '11 Lê Lai, Hà Nội',
                'notes' => '',
                'created_at' => '2023-07-01 00:00:00',
                'updated_at' => '2023-07-01 00:00:00',
            ],
            [
                'name' => 'Phạm Thị X',
                'phone' => '0912345679',
                'email' => 'phamx@gmail.com',
                'address' => '23 Nguyễn Huệ, Huế',
                'notes' => 'Khách hàng VIP',
                'created_at' => '2023-07-12 00:00:00',
                'updated_at' => '2023-07-12 00:00:00',
            ],
            [
                'name' => 'Ngô Văn Y',
                'phone' => '0987654322',
                'email' => 'ngoy@gmail.com',
                'address' => '45 Lê Lợi, Hải Phòng',
                'notes' => '',
                'created_at' => '2023-07-13 00:00:00',
                'updated_at' => '2023-07-13 00:00:00',
            ],
            [
                'name' => 'Lê Thị Z',
                'phone' => '0901234568',
                'email' => 'lez@gmail.com',
                'address' => '67 Nguyễn Văn Cừ, Cần Thơ',
                'notes' => 'Khách hàng định kỳ',
                'created_at' => '2023-07-14 00:00:00',
                'updated_at' => '2023-07-14 00:00:00',
            ],
            [
                'name' => 'Trịnh Văn A1',
                'phone' => '0912345680',
                'email' => 'trinha1@gmail.com',
                'address' => '89 Lê Duẩn, Đắk Lắk',
                'notes' => '',
                'created_at' => '2023-07-15 00:00:00',
                'updated_at' => '2023-07-15 00:00:00',
            ],
            [
                'name' => 'Nguyễn Thị B2',
                'phone' => '0987654323',
                'email' => 'nguyenthib2@gmail.com',
                'address' => '12 Nguyễn Trãi, Nam Định',
                'notes' => '',
                'created_at' => '2023-07-16 00:00:00',
                'updated_at' => '2023-07-16 00:00:00',
            ],
            [
                'name' => 'Lê Văn C3',
                'phone' => '0901234569',
                'email' => 'levanc3@gmail.com',
                'address' => '34 Lê Lợi, Quảng Ngãi',
                'notes' => '',
                'created_at' => '2023-07-17 00:00:00',
                'updated_at' => '2023-07-17 00:00:00',
            ],
            [
                'name' => 'Phạm Thị D4',
                'phone' => '0912345670',
                'email' => 'phamd4@gmail.com',
                'address' => '56 Nguyễn Văn Cừ, Cần Thơ',
                'notes' => '',
                'created_at' => '2023-07-18 00:00:00',
                'updated_at' => '2023-07-18 00:00:00',
            ],
            [
                'name' => 'Ngô Văn E5',
                'phone' => '0987654324',
                'email' => 'ngovane5@gmail.com',
                'address' => '78 Phan Đình Phùng, Quảng Ninh',
                'notes' => '',
                'created_at' => '2023-07-19 00:00:00',
                'updated_at' => '2023-07-19 00:00:00',
            ],
        ];

        // Chèn dữ liệu vào bảng customers
        foreach ($customers as $customerData) {
            Customer::create($customerData);
        }
    }
}
