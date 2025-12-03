<?php

namespace App\Observers;

use App\Models\AssemblyMaterial;
use App\Models\MaintenanceRequestProduct;
use App\Models\RepairItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AssemblyMaterialObserver
{
    /**
     * Flag để tránh vòng lặp đệ quy khi đồng bộ serial
     */
    private static bool $syncing = false;

    /**
     * Handle the AssemblyMaterial "updated" event.
     * Đồng bộ serial vật tư lắp ráp khi được cập nhật
     */
    public function updated(AssemblyMaterial $assemblyMaterial)
    {
        // Tránh vòng lặp đệ quy
        if (self::$syncing) {
            return;
        }

        // Chỉ xử lý khi serial thay đổi
        if (!$assemblyMaterial->isDirty('serial')) {
            return;
        }

        $oldSerial = $assemblyMaterial->getOriginal('serial');
        $newSerial = $assemblyMaterial->serial;

        // Nếu không có old_serial hoặc serial không thay đổi, bỏ qua
        if (empty($oldSerial) || $oldSerial === $newSerial) {
            return;
        }

        Log::info('AssemblyMaterialObserver: Serial changed', [
            'assembly_material_id' => $assemblyMaterial->id,
            'assembly_id' => $assemblyMaterial->assembly_id,
            'material_id' => $assemblyMaterial->material_id,
            'old_serial' => $oldSerial,
            'new_serial' => $newSerial,
        ]);

        $this->syncSerialToRelatedRecords($assemblyMaterial, $oldSerial, $newSerial);
    }

    /**
     * Đồng bộ serial đến các bản ghi liên quan
     */
    private function syncSerialToRelatedRecords(AssemblyMaterial $assemblyMaterial, string $oldSerial, string $newSerial)
    {
        // Đặt flag để tránh vòng lặp đệ quy
        self::$syncing = true;

        try {
            DB::beginTransaction();

            // Lấy material để có code
            $material = $assemblyMaterial->material;
            if (!$material) {
                Log::warning('AssemblyMaterialObserver: Material not found', [
                    'material_id' => $assemblyMaterial->material_id,
                ]);
                DB::rollBack();
                return;
            }

            // 1. Cập nhật MaintenanceRequestProduct (cho vật tư)
            $updatedMRP = MaintenanceRequestProduct::where('serial_number', $oldSerial)
                ->where('product_id', $assemblyMaterial->material_id)
                ->update(['serial_number' => $newSerial]);

            if ($updatedMRP > 0) {
                Log::info('AssemblyMaterialObserver: Updated MaintenanceRequestProduct', [
                    'count' => $updatedMRP,
                    'old_serial' => $oldSerial,
                    'new_serial' => $newSerial,
                ]);
            }

            // 2. Cập nhật RepairItem device_parts (serial vật tư lắp ráp được lưu trong device_parts)
            $this->updateRepairItemParts($material->code, $oldSerial, $newSerial);

            // 3. Cập nhật DeviceCode serial_components
            $this->updateDeviceCodeComponents($assemblyMaterial, $oldSerial, $newSerial);

            DB::commit();

            Log::info('AssemblyMaterialObserver: Serial sync completed successfully', [
                'old_serial' => $oldSerial,
                'new_serial' => $newSerial,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AssemblyMaterialObserver: Error syncing serial', [
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
     * Cập nhật serial trong RepairItem device_parts
     */
    private function updateRepairItemParts(string $materialCode, string $oldSerial, string $newSerial)
    {
        // Tìm các RepairItem có device_parts chứa old_serial
        $repairItems = RepairItem::whereNotNull('device_parts')
            ->whereRaw("JSON_SEARCH(device_parts, 'one', ?) IS NOT NULL", [$oldSerial])
            ->get();

        foreach ($repairItems as $repairItem) {
            $deviceParts = $repairItem->device_parts ?? [];
            $updated = false;

            foreach ($deviceParts as $index => $part) {
                if (isset($part['serial']) && $part['serial'] === $oldSerial) {
                    $deviceParts[$index]['serial'] = $newSerial;
                    $updated = true;
                }
                // Cũng kiểm tra nếu serial được lưu dưới dạng array
                if (isset($part['serials']) && is_array($part['serials'])) {
                    $serialIndex = array_search($oldSerial, $part['serials']);
                    if ($serialIndex !== false) {
                        $deviceParts[$index]['serials'][$serialIndex] = $newSerial;
                        $updated = true;
                    }
                }
            }

            if ($updated) {
                $repairItem->update(['device_parts' => $deviceParts]);
                Log::info('AssemblyMaterialObserver: Updated RepairItem device_parts', [
                    'repair_item_id' => $repairItem->id,
                    'old_serial' => $oldSerial,
                    'new_serial' => $newSerial,
                ]);
            }
        }
    }

    /**
     * Cập nhật serial trong DeviceCode serial_components
     */
    private function updateDeviceCodeComponents(AssemblyMaterial $assemblyMaterial, string $oldSerial, string $newSerial)
    {
        // Tìm các DeviceCode có serial_components chứa old_serial
        $deviceCodes = \App\Models\DeviceCode::whereNotNull('serial_components')
            ->whereRaw("JSON_SEARCH(serial_components, 'one', ?) IS NOT NULL", [$oldSerial])
            ->get();

        foreach ($deviceCodes as $deviceCode) {
            $serialComponents = $deviceCode->serial_components ?? [];
            $serialIndex = array_search($oldSerial, $serialComponents);

            if ($serialIndex !== false) {
                $serialComponents[$serialIndex] = $newSerial;
                $deviceCode->update(['serial_components' => array_values($serialComponents)]);

                Log::info('AssemblyMaterialObserver: Updated DeviceCode serial_components', [
                    'device_code_id' => $deviceCode->id,
                    'old_serial' => $oldSerial,
                    'new_serial' => $newSerial,
                ]);
            }
        }
    }
}
