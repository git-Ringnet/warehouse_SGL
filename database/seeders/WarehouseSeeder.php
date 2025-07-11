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
                'manager' => 1,
                'description' => 'Kho chính trung tâm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'WH002',
                'name' => 'Kho phụ',
                'address' => '456 Nguyễn Huệ, Quận 3, TP.HCM',
                'manager' => 2,
                'description' => 'Kho phụ gần trung tâm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'WH003',
                'name' => 'Kho linh kiện',
                'address' => '789 Lý Tự Trọng, Quận 5, TP.HCM',
                'manager' => 2,
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