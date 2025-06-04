<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = [
            [
                'code' => 'WH001',
                'name' => 'Kho chính',
                'address' => '123 Lê Lợi, Quận 1, TP.HCM',
                'manager' => 'Nguyễn Văn A',
                'phone' => '0912345678',
                'email' => 'khochinh@sgl.com',
                'description' => 'Kho chính trung tâm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'WH002',
                'name' => 'Kho phụ',
                'address' => '456 Nguyễn Huệ, Quận 3, TP.HCM',
                'manager' => 'Trần Thị B',
                'phone' => '0987654321',
                'email' => 'khophu@sgl.com',
                'description' => 'Kho phụ gần trung tâm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'WH003',
                'name' => 'Kho linh kiện',
                'address' => '789 Lý Tự Trọng, Quận 5, TP.HCM',
                'manager' => 'Lê Văn C',
                'phone' => '0901234567',
                'email' => 'kholinhkien@sgl.com',
                'description' => 'Kho chuyên lưu trữ linh kiện điện tử',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($warehouses as $warehouseData) {
            Warehouse::create($warehouseData);
        }
    }
} 