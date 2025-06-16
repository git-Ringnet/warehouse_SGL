<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Testing;
use App\Models\TestingItem;
use App\Models\TestingDetail;
use App\Models\Employee;
use App\Models\Material;
use App\Models\Product;
use App\Models\Good;
use App\Models\Supplier;
use App\Models\Warehouse;
use Carbon\Carbon;

class TestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample employees if none exist
        if (Employee::count() == 0) {
            $employees = [
                ['name' => 'Nguyễn Văn A', 'email' => 'nva@example.com', 'phone' => '0901234567', 'position' => 'QA Engineer', 'active' => true],
                ['name' => 'Trần Văn B', 'email' => 'tvb@example.com', 'phone' => '0901234568', 'position' => 'QA Manager', 'active' => true],
                ['name' => 'Lê Thị C', 'email' => 'ltc@example.com', 'phone' => '0901234569', 'position' => 'QA Tester', 'active' => true],
            ];
            
            foreach ($employees as $employee) {
                Employee::create($employee);
            }
        }
        
        // Create sample suppliers if none exist
        if (Supplier::count() == 0) {
            $suppliers = [
                ['name' => 'ABC Electronics', 'email' => 'contact@abc.com', 'phone' => '0901111222', 'address' => 'Hà Nội'],
                ['name' => 'Tech Solutions', 'email' => 'info@techsolutions.com', 'phone' => '0902222333', 'address' => 'TP. Hồ Chí Minh'],
                ['name' => 'VN Components', 'email' => 'sales@vncomponents.com', 'phone' => '0903333444', 'address' => 'Đà Nẵng'],
            ];
            
            foreach ($suppliers as $supplier) {
                Supplier::create($supplier);
            }
        }
        
        // Create sample warehouses if none exist
        if (Warehouse::count() == 0) {
            $warehouses = [
                ['name' => 'Kho A - Thiết bị hoàn chỉnh', 'address' => 'TP. Hồ Chí Minh', 'status' => 'active'],
                ['name' => 'Kho B - Linh kiện đạt', 'address' => 'TP. Hồ Chí Minh', 'status' => 'active'],
                ['name' => 'Kho C - Linh kiện lỗi', 'address' => 'TP. Hồ Chí Minh', 'status' => 'active'],
            ];
            
            foreach ($warehouses as $warehouse) {
                Warehouse::create($warehouse);
            }
        }
        
        // Create sample materials if none exist
        if (Material::count() == 0) {
            $materials = [
                ['name' => 'Module 4G', 'code' => 'MAT-4G', 'description' => 'Module kết nối 4G', 'unit' => 'Cái', 'hidden' => false],
                ['name' => 'Module Công suất', 'code' => 'MAT-PWR', 'description' => 'Module công suất', 'unit' => 'Cái', 'hidden' => false],
                ['name' => 'Module IoTs', 'code' => 'MAT-IOT', 'description' => 'Module IoT', 'unit' => 'Cái', 'hidden' => false],
            ];
            
            foreach ($materials as $material) {
                Material::create($material);
            }
        }
        
        // Create sample products if none exist
        if (Product::count() == 0) {
            $products = [
                ['name' => 'Bộ điều khiển SGL-500', 'code' => 'PRD-SGL500', 'description' => 'Bộ điều khiển SGL-500', 'unit' => 'Bộ', 'hidden' => false],
                ['name' => 'Thiết bị đo nhiệt độ', 'code' => 'PRD-TEMP', 'description' => 'Thiết bị đo nhiệt độ', 'unit' => 'Cái', 'hidden' => false],
            ];
            
            foreach ($products as $product) {
                Product::create($product);
            }
        }
        
        // Create sample goods if none exist
        if (Good::count() == 0) {
            $goods = [
                ['name' => 'SGL SmartBox', 'code' => 'GD-SMARTBOX', 'notes' => 'Thiết bị SmartBox', 'unit' => 'Bộ', 'status' => 'active', 'category' => 'Thiết bị'],
                ['name' => 'Bộ thu phát SGL-4G-Premium', 'code' => 'GD-SGL4GP', 'notes' => 'Bộ thu phát 4G Premium', 'unit' => 'Bộ', 'status' => 'active', 'category' => 'Thiết bị'],
            ];
            
            foreach ($goods as $good) {
                Good::create($good);
            }
        }
        
        // Create testing records
        $testings = [
            [
                'test_code' => 'QA-240601',
                'test_type' => 'material',
                'tester_id' => 1,
                'test_date' => Carbon::now()->subDays(5),
                'status' => 'completed',
                'notes' => 'Kiểm tra module 4G',
                'conclusion' => 'Module 4G đạt chất lượng để đưa vào sản xuất. Đa số thông số kỹ thuật đều đạt yêu cầu. Cần loại bỏ 2 module bị lỗi anten.',
                'pass_quantity' => 18,
                'fail_quantity' => 2,
                'fail_reasons' => '2 module có vấn đề về kết nối anten, cần kiểm tra lại mạch RF.',
                'approved_by' => 2,
                'approved_at' => Carbon::now()->subDays(6),
                'received_by' => 3,
                'received_at' => Carbon::now()->subDays(6),
                'is_inventory_updated' => true,
                'success_warehouse_id' => 1,
                'fail_warehouse_id' => 3,
            ],
            [
                'test_code' => 'QA-240602',
                'test_type' => 'material',
                'tester_id' => 3,
                'test_date' => Carbon::now()->subDays(3),
                'status' => 'pending',
                'notes' => 'Kiểm tra module công suất',
            ],
            [
                'test_code' => 'QA-240603',
                'test_type' => 'finished_product',
                'tester_id' => 2,
                'test_date' => Carbon::now()->subDays(2),
                'status' => 'in_progress',
                'notes' => 'Kiểm tra thiết bị SmartBox',
                'approved_by' => 1,
                'approved_at' => Carbon::now()->subDays(1),
            ],
            [
                'test_code' => 'QA-240604',
                'test_type' => 'material',
                'tester_id' => 1,
                'test_date' => Carbon::now()->subDays(1),
                'status' => 'cancelled',
                'notes' => 'Kiểm tra module IoT',
            ],
            [
                'test_code' => 'QA-240605',
                'test_type' => 'material',
                'tester_id' => 3,
                'test_date' => Carbon::now(),
                'status' => 'completed',
                'notes' => 'Kiểm tra module IoT',
                'conclusion' => 'Module IoT hoạt động tốt, đạt 100% yêu cầu kỹ thuật.',
                'pass_quantity' => 15,
                'fail_quantity' => 0,
                'approved_by' => 2,
                'approved_at' => Carbon::now()->subHours(12),
                'received_by' => 1,
                'received_at' => Carbon::now()->subHours(10),
                'is_inventory_updated' => false,
            ],
        ];
        
        foreach ($testings as $testingData) {
            $testing = Testing::create($testingData);
            
            // Add testing items
            if ($testing->test_type == 'material') {
                $materialId = $testing->test_code == 'QA-240601' ? 1 : ($testing->test_code == 'QA-240602' ? 2 : 3);
                $supplierId = $materialId;
                $serialNumber = $testing->test_code == 'QA-240601' ? '4G-MOD-2305621' : ($testing->test_code == 'QA-240602' ? 'PWR-2405102' : 'IOT-2405089');
                
                TestingItem::create([
                    'testing_id' => $testing->id,
                    'item_type' => 'material',
                    'material_id' => $materialId,
                    'supplier_id' => $supplierId,
                    'serial_number' => $serialNumber,
                    'batch_number' => 'LOT-' . date('ym') . '-01',
                    'quantity' => $testing->pass_quantity + $testing->fail_quantity,
                    'result' => $testing->status == 'completed' ? 'pass' : 'pending',
                ]);
            } else {
                $goodId = 1;
                $serialNumber = 'SB-2406057';
                
                TestingItem::create([
                    'testing_id' => $testing->id,
                    'item_type' => 'finished_product',
                    'good_id' => $goodId,
                    'supplier_id' => 1,
                    'serial_number' => $serialNumber,
                    'quantity' => 1,
                    'result' => $testing->status == 'completed' ? 'pass' : 'pending',
                ]);
            }
            
            // Add testing details
            $testItems = [
                'Kiểm tra phần cứng',
                'Kiểm tra phần mềm',
                'Kiểm tra kết nối',
                'Kiểm tra hiệu năng',
            ];
            
            foreach ($testItems as $testItem) {
                TestingDetail::create([
                    'testing_id' => $testing->id,
                    'test_item_name' => $testItem,
                    'result' => $testing->status == 'completed' ? 'pass' : 'pending',
                    'notes' => $testing->status == 'completed' ? 'Đạt yêu cầu' : null,
                ]);
            }
        }
    }
} 