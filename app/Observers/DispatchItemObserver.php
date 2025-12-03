<?php

namespace App\Observers;

use App\Models\DispatchItem;
use App\Models\MaintenanceRequestProduct;
use App\Models\RepairItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DispatchItemObserver
{
    /**
     * Flag để tránh vòng lặp đệ quy khi đồng bộ serial
     */
    private static bool $syncing = false;

    /**
     * Handle the DispatchItem "updated" event.
     * Đồng bộ serial khi DispatchItem được cập nhật
     */
    public function updated(DispatchItem $dispatchItem)
    {
        // Tránh vòng lặp đệ quy
        if (self::$syncing) {
            return;
        }

        // Chỉ xử lý khi serial_numbers thay đổi
        if (!$dispatchItem->isDirty('serial_numbers')) {
            return;
        }

        $oldSerials = $dispatchItem->getOriginal('serial_numbers') ?? [];
        $newSerials = $dispatchItem->serial_numbers ?? [];

        // Đảm bảo là array
        if (is_string($oldSerials)) {
            $oldSerials = json_decode($oldSerials, true) ?? [];
        }
        if (is_string($newSerials)) {
            $newSerials = json_decode($newSerials, true) ?? [];
        }

        // Tìm các serial đã thay đổi
        $changedSerials = $this->findChangedSerials($oldSerials, $newSerials);

        if (empty($changedSerials)) {
            return;
        }

        Log::info('DispatchItemObserver: Serial numbers changed', [
            'dispatch_item_id' => $dispatchItem->id,
            'dispatch_id' => $dispatchItem->dispatch_id,
            'old_serials' => $oldSerials,
            'new_serials' => $newSerials,
            'changed_serials' => $changedSerials,
        ]);

        $this->syncSerialChanges($dispatchItem, $changedSerials);
    }

    /**
     * Tìm các serial đã thay đổi (theo vị trí index)
     */
    private function findChangedSerials(array $oldSerials, array $newSerials): array
    {
        $changes = [];

        // So sánh theo vị trí index
        $maxLength = max(count($oldSerials), count($newSerials));
        for ($i = 0; $i < $maxLength; $i++) {
            $oldSerial = $oldSerials[$i] ?? null;
            $newSerial = $newSerials[$i] ?? null;

            if ($oldSerial !== $newSerial && !empty($oldSerial) && !empty($newSerial)) {
                $changes[] = [
                    'old' => $oldSerial,
                    'new' => $newSerial,
                ];
            }
        }

        return $changes;
    }

    /**
     * Đồng bộ các thay đổi serial đến các bản ghi liên quan
     */
    private function syncSerialChanges(DispatchItem $dispatchItem, array $changedSerials)
    {
        // Đặt flag để tránh vòng lặp đệ quy
        self::$syncing = true;

        try {
            DB::beginTransaction();

            foreach ($changedSerials as $change) {
                $oldSerial = $change['old'];
                $newSerial = $change['new'];

                // 1. Cập nhật MaintenanceRequestProduct
                $this->updateMaintenanceRequestProducts($dispatchItem, $oldSerial, $newSerial);

                // 2. Cập nhật RepairItem
                $this->updateRepairItems($dispatchItem, $oldSerial, $newSerial);
            }

            DB::commit();

            Log::info('DispatchItemObserver: Serial sync completed successfully', [
                'dispatch_item_id' => $dispatchItem->id,
                'changes_count' => count($changedSerials),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('DispatchItemObserver: Error syncing serial', [
                'error' => $e->getMessage(),
                'dispatch_item_id' => $dispatchItem->id,
            ]);
        } finally {
            // Reset flag
            self::$syncing = false;
        }
    }

    /**
     * Cập nhật serial trong MaintenanceRequestProduct
     */
    private function updateMaintenanceRequestProducts(DispatchItem $dispatchItem, string $oldSerial, string $newSerial)
    {
        // Tìm các MaintenanceRequestProduct có serial_number khớp với old_serial
        // và thuộc về cùng product_id
        $updated = MaintenanceRequestProduct::where('serial_number', $oldSerial)
            ->where('product_id', $dispatchItem->item_id)
            ->update(['serial_number' => $newSerial]);

        if ($updated > 0) {
            Log::info('DispatchItemObserver: Updated MaintenanceRequestProduct', [
                'count' => $updated,
                'old_serial' => $oldSerial,
                'new_serial' => $newSerial,
            ]);
        }
    }

    /**
     * Cập nhật serial trong RepairItem
     */
    private function updateRepairItems(DispatchItem $dispatchItem, string $oldSerial, string $newSerial)
    {
        // Lấy product/good code
        $productCode = null;
        if ($dispatchItem->item_type === 'product' && $dispatchItem->product) {
            $productCode = $dispatchItem->product->code;
        } elseif ($dispatchItem->item_type === 'good' && $dispatchItem->good) {
            $productCode = $dispatchItem->good->code;
        }

        if ($productCode) {
            $updated = RepairItem::where('device_serial', $oldSerial)
                ->where('device_code', $productCode)
                ->update(['device_serial' => $newSerial]);

            if ($updated > 0) {
                Log::info('DispatchItemObserver: Updated RepairItem', [
                    'count' => $updated,
                    'old_serial' => $oldSerial,
                    'new_serial' => $newSerial,
                ]);
            }
        }
    }
}
