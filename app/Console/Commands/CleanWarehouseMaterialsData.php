<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WarehouseMaterial;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class CleanWarehouseMaterialsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warehouse:clean-materials 
                            {warehouse_id : ID của kho cần xóa dữ liệu}
                            {--force : Xóa không cần xác nhận}
                            {--backup : Tạo backup trước khi xóa}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xóa tất cả dữ liệu vật tư/hàng hóa dư trong một kho cụ thể';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $warehouseId = $this->argument('warehouse_id');
        
        // Kiểm tra kho có tồn tại không
        $warehouse = Warehouse::find($warehouseId);
        if (!$warehouse) {
            $this->error("Không tìm thấy kho với ID: {$warehouseId}");
            return Command::FAILURE;
        }

        $this->info("=== XÓA DỮ LIỆU DƯ TRONG KHO ===");
        $this->info("Kho: {$warehouse->name} (ID: {$warehouseId})");
        $this->newLine();

        // Lấy danh sách records cần xóa
        $records = WarehouseMaterial::where('warehouse_id', $warehouseId)->get();
        
        if ($records->isEmpty()) {
            $this->warn("Không có dữ liệu nào trong kho này.");
            return Command::SUCCESS;
        }

        // Hiển thị thống kê
        $materialCount = $records->where('item_type', 'material')->count();
        $productCount = $records->where('item_type', 'product')->count();
        $goodCount = $records->where('item_type', 'good')->count();
        $totalQuantity = $records->sum('quantity');

        $this->table(
            ['Loại', 'Số records', 'Tổng số lượng'],
            [
                ['Vật tư (material)', $materialCount, $records->where('item_type', 'material')->sum('quantity')],
                ['Thành phẩm (product)', $productCount, $records->where('item_type', 'product')->sum('quantity')],
                ['Hàng hóa (good)', $goodCount, $records->where('item_type', 'good')->sum('quantity')],
                ['TỔNG', $records->count(), $totalQuantity],
            ]
        );

        // Hiển thị chi tiết
        $this->newLine();
        $this->info("Chi tiết dữ liệu sẽ bị xóa:");
        
        $detailData = $records->map(function ($record) {
            return [
                $record->id,
                $record->item_type,
                $record->material_id,
                $record->quantity,
                $record->serial_number ?? '-',
                $record->location ?? '-',
            ];
        })->toArray();

        $this->table(
            ['ID', 'Loại', 'Material ID', 'Số lượng', 'Serial', 'Vị trí'],
            $detailData
        );

        // Xác nhận xóa
        if (!$this->option('force')) {
            if (!$this->confirm("Bạn có chắc chắn muốn xóa {$records->count()} records này?")) {
                $this->info("Đã hủy thao tác.");
                return Command::SUCCESS;
            }
        }

        // Backup nếu có option
        if ($this->option('backup')) {
            $this->info("Đang tạo backup...");
            $backupData = $records->toArray();
            $backupFile = storage_path("app/backup_warehouse_{$warehouseId}_" . date('Y-m-d_His') . ".json");
            file_put_contents($backupFile, json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->info("Đã lưu backup tại: {$backupFile}");
        }

        // Thực hiện xóa
        DB::beginTransaction();
        try {
            $deletedCount = WarehouseMaterial::where('warehouse_id', $warehouseId)->delete();
            
            DB::commit();
            
            $this->newLine();
            $this->info("✓ Đã xóa thành công {$deletedCount} records trong kho '{$warehouse->name}'");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Lỗi khi xóa dữ liệu: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
