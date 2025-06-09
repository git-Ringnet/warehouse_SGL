<?php

namespace Database\Seeders;

use App\Models\Assembly;
use App\Models\AssemblyMaterial;
use App\Models\Material;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AssemblySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all products
        $products = Product::all();
        if ($products->isEmpty()) {
            $this->command->info('Không có sản phẩm để tạo phiếu lắp ráp. Vui lòng chạy ProductSeeder trước.');
            return;
        }
        
        // Get all materials
        $materials = Material::all();
        if ($materials->isEmpty()) {
            $this->command->info('Không có nguyên vật liệu để tạo phiếu lắp ráp. Vui lòng chạy MaterialSeeder trước.');
            return;
        }
        
        // Get all warehouses
        $warehouses = Warehouse::all();
        if ($warehouses->isEmpty()) {
            $this->command->info('Không có kho để tạo phiếu lắp ráp. Vui lòng chạy WarehouseSeeder trước.');
            return;
        }

        // Tên người được giao nhiệm vụ lắp ráp
        $assignees = [
            'Nguyễn Văn A', 
            'Trần Thị B', 
            'Lê Văn C', 
            'Phạm Thị D', 
            'Hoàng Văn E',
            'Đặng Thị F',
            'Vũ Văn G',
            'Bùi Thị H',
            'Đỗ Văn I',
            'Ngô Thị K'
        ];

        // Các trạng thái của phiếu lắp ráp
        $statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
        
        // Tạo 50 phiếu lắp ráp
        for ($i = 1; $i <= 50; $i++) {
            // Chọn ngẫu nhiên product, warehouse và người được giao nhiệm vụ
            $product = $products->random();
            $sourceWarehouse = $warehouses->random();
            $targetWarehouse = $warehouses->random();
            
            // Đảm bảo kho nguồn và kho đích khác nhau
            while ($targetWarehouse->id === $sourceWarehouse->id) {
                $targetWarehouse = $warehouses->random();
            }
            
            // Chọn ngày lắp ráp trong khoảng 90 ngày gần đây
            $date = now()->subDays(rand(0, 90));
            
            // Xác định trạng thái dựa trên ngày
            // Nếu ngày trong quá khứ xa, có khả năng cao đã hoàn thành
            $daysDiff = now()->diffInDays($date);
            $status = null;
            
            if ($daysDiff > 60) {
                // 90% completed, 10% cancelled cho đơn cũ
                $status = (rand(1, 10) <= 9) ? 'completed' : 'cancelled';
            } elseif ($daysDiff > 30) {
                // 60% completed, 30% in_progress, 10% cancelled
                $rand = rand(1, 10);
                $status = ($rand <= 6) ? 'completed' : (($rand <= 9) ? 'in_progress' : 'cancelled');
            } elseif ($daysDiff > 7) {
                // 30% completed, 50% in_progress, 15% pending, 5% cancelled
                $rand = rand(1, 20);
                $status = ($rand <= 6) ? 'completed' : 
                         (($rand <= 16) ? 'in_progress' : 
                         (($rand <= 19) ? 'pending' : 'cancelled'));
            } else {
                // 10% completed, 30% in_progress, 60% pending
                $rand = rand(1, 10);
                $status = ($rand <= 1) ? 'completed' : 
                         (($rand <= 4) ? 'in_progress' : 'pending');
            }
            
            // Tạo số lượng lắp ráp từ 1-5
            $quantity = rand(1, 5);
            
            // Tạo serial numbers nếu đã hoàn thành
            $serials = null;
            if ($status === 'completed') {
                $serials = [];
                $baseSerial = strtoupper(Str::random(3)) . '-' . rand(1000, 9999);
                for ($j = 1; $j <= $quantity; $j++) {
                    $serials[] = $baseSerial . '-' . str_pad($j, 2, '0', STR_PAD_LEFT);
                }
                $serials = implode(', ', $serials);
            }
            
            // Tạo ghi chú ngẫu nhiên
            $noteTemplates = [
                'Lắp ráp theo yêu cầu của khách hàng.',
                'Sản phẩm cần giao gấp.',
                'Kiểm tra kỹ chất lượng trước khi bàn giao.',
                'Cần thêm linh kiện từ nhà cung cấp.',
                'Sản phẩm dùng cho dự án %s.',
                'Ưu tiên hoàn thành trong tuần này.',
                null, // Một số phiếu không có ghi chú
            ];
            
            $note = $noteTemplates[array_rand($noteTemplates)];
            if (strpos($note, '%s') !== false) {
                $projects = ['Tech Solutions', 'Smart Office', 'Digital Transformation', 'Cloud Migration', 'IoT Development'];
                $note = sprintf($note, $projects[array_rand($projects)]);
            }
            
            // Tạo mã phiếu lắp ráp
            $code = 'ASM-' . date('Ymd', strtotime($date)) . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);
            
            // Tạo phiếu lắp ráp
            $assembly = Assembly::create([
                'code' => $code,
                'date' => $date,
                'product_id' => $product->id,
                'warehouse_id' => $sourceWarehouse->id,
                'target_warehouse_id' => $targetWarehouse->id,
                'assigned_to' => $assignees[array_rand($assignees)],
                'status' => $status,
                'notes' => $note,
                'quantity' => $quantity,
                'product_serials' => $serials,
            ]);
            
            // Tạo các nguyên vật liệu cần cho lắp ráp
            $materialCount = rand(3, 8); // Mỗi sản phẩm cần 3-8 loại nguyên vật liệu
            $usedMaterials = [];
            
            for ($j = 0; $j < $materialCount; $j++) {
                // Lấy ngẫu nhiên một nguyên vật liệu chưa được sử dụng
                $material = null;
                do {
                    $material = $materials->random();
                } while (in_array($material->id, $usedMaterials));
                
                $usedMaterials[] = $material->id;
                
                // Xác định số lượng cần cho mỗi sản phẩm (1-5)
                $materialQuantity = rand(1, 5) * $quantity;
                
                // Tạo ghi chú cho nguyên vật liệu (50% có ghi chú)
                $materialNote = null;
                if (rand(0, 1)) {
                    $materialNoteTemplates = [
                        'Kiểm tra chất lượng trước khi sử dụng.',
                        'Lấy từ lô hàng mới nhất.',
                        'Sử dụng cẩn thận, dễ vỡ.',
                        'Cần bảo quản nhiệt độ phòng.',
                        'Linh kiện nhạy cảm với tĩnh điện.'
                    ];
                    $materialNote = $materialNoteTemplates[array_rand($materialNoteTemplates)];
                }
                
                // Tạo serial cho nguyên vật liệu (chỉ với một số nguyên vật liệu)
                $materialSerial = null;
                if (rand(0, 2) === 0) { // 33% có serial
                    $materialSerial = strtoupper(Str::random(2)) . '-' . rand(100000, 999999);
                }
                
                // Thêm nguyên vật liệu vào phiếu lắp ráp
                AssemblyMaterial::create([
                    'assembly_id' => $assembly->id,
                    'material_id' => $material->id,
                    'quantity' => $materialQuantity,
                    'serial' => $materialSerial,
                    'note' => $materialNote,
                ]);
            }
        }
    }
} 