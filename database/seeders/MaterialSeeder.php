<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $materials = [
            [
                'code' => 'VT001',
                'name' => 'Ốc vít 10mm',
                'category' => 'Linh kiện',
                'unit' => 'Kg',
                'notes' => 'Ốc vít thông dụng',
                'created_at' => now(),
                'updated_at' => now(),
                'status' => 'active',
                'is_hidden' => false,
            ],
            [
                'code' => 'VT002',
                'name' => 'Ống nhựa PVC 20mm',
                'category' => 'Vật tư',
                'unit' => 'Mét',
                'notes' => 'Ống nhựa chất lượng cao',
                'created_at' => now(),
                'updated_at' => now(),
                'status' => 'active',
                'is_hidden' => false,
            ],
            [
                'code' => 'VT003',
                'name' => 'Dây điện 2.5mm',
                'category' => 'Điện',
                'unit' => 'Mét',
                'notes' => 'Dây điện chất lượng cao',
                'created_at' => now(),
                'updated_at' => now(),
                'status' => 'active',
                'is_hidden' => false,
            ],
            [
                'code' => 'VT004',
                'name' => 'Bóng đèn LED 10W',
                'category' => 'Điện',
                'unit' => 'Cái',
                'notes' => 'Tiết kiệm năng lượng',
                'created_at' => now(),
                'updated_at' => now(),
                'status' => 'active',
                'is_hidden' => false,
            ],
            [
                'code' => 'VT005',
                'name' => 'Keo dán 2 thành phần',
                'category' => 'Hóa chất',
                'unit' => 'Tuýp',
                'notes' => 'Keo dán đa năng',
                'created_at' => now(),
                'updated_at' => now(),
                'status' => 'active',
                'is_hidden' => false,
            ],
        ];

        foreach ($materials as $materialData) {
            Material::create($materialData);
        }
    }
} 