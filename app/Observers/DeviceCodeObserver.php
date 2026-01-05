<?php

namespace App\Observers;

use App\Models\DeviceCode;
use App\Models\MaintenanceRequestProduct;
use App\Models\RepairItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DeviceCodeObserver
{
    /**
     * Flag để tránh vòng lặp đệ quy khi đồng bộ serial
     */
    private static bool $syncing = false;

    /**
     * Handle the DeviceCode "updated" event.
     * Đồng bộ serial khi DeviceCode được cập nhật
     */
    public function updated(DeviceCode $deviceCode)
    {
        // Tránh vòng lặp đệ quy
        if (self::$syncing) {
            return;
        }

        // Chỉ xử lý khi serial_main thay đổi
        if (!$deviceCode->isDirty('serial_main')) {
            return;
        }

        $oldSerial = $deviceCode->old_serial;
        $newSerial = $deviceCode->serial_main;

        // Nếu không có old_serial hoặc serial không thay đổi, bỏ qua
        if (empty($oldSerial) || $oldSerial === $newSerial) {
            return;
        }

        Log::info('DeviceCodeObserver: Serial changed', [
            'device_code_id' => $deviceCode->id,
            'dispatch_id' => $deviceCode->dispatch_id,
            'old_serial' => $oldSerial,
            'new_serial' => $newSerial,
        ]);

        $this->syncSerialToRelatedRecords($deviceCode, $oldSerial, $newSerial);
    }

    /**
     * Handle the DeviceCode "created" event.
     * Đồng bộ serial khi DeviceCode được tạo mới với serial khác old_serial
     */
    public function created(DeviceCode $deviceCode)
    {
        // Tránh vòng lặp đệ quy
        if (self::$syncing) {
            return;
        }

        $oldSerial = $deviceCode->old_serial;
        $newSerial = $deviceCode->serial_main;

        // Nếu không có old_serial hoặc serial giống nhau, bỏ qua
        if (empty($oldSerial) || $oldSerial === $newSerial) {
            return;
        }

        Log::info('DeviceCodeObserver: New DeviceCode with different serial', [
            'device_code_id' => $deviceCode->id,
            'dispatch_id' => $deviceCode->dispatch_id,
            'old_serial' => $oldSerial,
            'new_serial' => $newSerial,
        ]);

        $this->syncSerialToRelatedRecords($deviceCode, $oldSerial, $newSerial);
    }

    /**
     * Đồng bộ serial đến các bản ghi liên quan
     */
    private function syncSerialToRelatedRecords(DeviceCode $deviceCode, string $oldSerial, string $newSerial)
    {
        // Đặt flag để tránh vòng lặp đệ quy
        self::$syncing = true;

        try {
            DB::beginTransaction();

            // 1. Cập nhật MaintenanceRequestProduct
            $this->updateMaintenanceRequestProducts($deviceCode, $oldSerial, $newSerial);

            // 2. Cập nhật RepairItem
            $this->updateRepairItems($deviceCode, $oldSerial, $newSerial);

            // 3. Cập nhật Warranty
            $this->updateWarranties($deviceCode, $oldSerial, $newSerial);

            // KHÔNG cập nhật DispatchItem.serial_numbers để giữ nguyên serial gốc đã xuất
            // Việc lấy serial mới sẽ được xử lý qua DeviceCode khi cần

            DB::commit();

            Log::info('DeviceCodeObserver: Serial sync completed successfully', [
                'old_serial' => $oldSerial,
                'new_serial' => $newSerial,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('DeviceCodeObserver: Error syncing serial', [
                'error' => $e->getMessage(),
                'old_serial' => $oldSerial,
                'new_serial' => $newSerial,
            ]);
        } finally {
            // Reset flag
            self::$syncing = false;
        }
    }

    /**
     * Cập nhật serial trong MaintenanceRequestProduct
     */
    private function updateMaintenanceRequestProducts(DeviceCode $deviceCode, string $oldSerial, string $newSerial)
    {
        // Tìm các MaintenanceRequestProduct có serial_number khớp với old_serial
        // và thuộc về cùng product_id
        $updated = MaintenanceRequestProduct::where('serial_number', $oldSerial)
            ->where('product_id', $deviceCode->product_id)
            ->update(['serial_number' => $newSerial]);

        if ($updated > 0) {
            Log::info('DeviceCodeObserver: Updated MaintenanceRequestProduct', [
                'count' => $updated,
                'old_serial' => $oldSerial,
                'new_serial' => $newSerial,
            ]);
        }
    }

    /**
     * Cập nhật serial trong RepairItem
     */
    private function updateRepairItems(DeviceCode $deviceCode, string $oldSerial, string $newSerial)
    {
        // Tìm các RepairItem có device_serial khớp với old_serial
        // và thuộc về cùng device_code (product code)
        $product = null;
        if ($deviceCode->item_type === 'product') {
            $product = \App\Models\Product::find($deviceCode->product_id);
        } elseif ($deviceCode->item_type === 'good') {
            $product = \App\Models\Good::find($deviceCode->product_id);
        }

        if ($product) {
            $updated = RepairItem::where('device_serial', $oldSerial)
                ->where('device_code', $product->code)
                ->update(['device_serial' => $newSerial]);

            if ($updated > 0) {
                Log::info('DeviceCodeObserver: Updated RepairItem', [
                    'count' => $updated,
                    'old_serial' => $oldSerial,
                    'new_serial' => $newSerial,
                ]);
            }
        }
    }

    /**
     * Cập nhật serial trong Warranty
     */
    private function updateWarranties(DeviceCode $deviceCode, string $oldSerial, string $newSerial)
    {
        // Tìm các Warranty có serial_number khớp với old_serial
        // và thuộc về cùng item_id và item_type
        $updated = \App\Models\Warranty::where('serial_number', $oldSerial)
            ->where('item_id', $deviceCode->product_id)
            ->where('item_type', $deviceCode->item_type ?: 'product')
            ->update(['serial_number' => $newSerial]);

        if ($updated > 0) {
            Log::info('DeviceCodeObserver: Updated Warranty', [
                'count' => $updated,
                'old_serial' => $oldSerial,
                'new_serial' => $newSerial,
            ]);
        }
    }

}
