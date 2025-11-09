<?php

namespace App\Console\Commands;

use App\Models\AssemblyProduct;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncAssemblyAndDispatchUnits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:assembly-dispatch-units {--dry-run : Only log actions without writing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill and correct product_unit for assembly_products and assembly_id/product_unit for dispatch_items';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Starting sync of assembly_products.product_unit ...');
        $this->syncAssemblyProductsUnits($dryRun);

        $this->info('Starting sync of dispatch_items assembly_id/product_unit ...');
        $this->syncDispatchItems($dryRun);

        $this->info('Sync completed.');
        return Command::SUCCESS;
    }

    private function syncAssemblyProductsUnits(bool $dryRun): void
    {
        // Derive product_unit arrays from assembly_materials per (assembly_id, target_product_id)
        $rows = DB::table('assembly_materials')
            ->select('assembly_id', 'target_product_id', DB::raw('GROUP_CONCAT(DISTINCT product_unit ORDER BY product_unit) as units'))
            ->groupBy('assembly_id', 'target_product_id')
            ->get();

        foreach ($rows as $row) {
            $unitValues = array_values(array_filter(array_map(function ($u) {
                $u = trim((string) $u);
                return $u === '' ? null : (int) $u;
            }, explode(',', (string) $row->units)), function ($v) { return $v !== null; }));

            $jsonUnits = json_encode($unitValues, JSON_UNESCAPED_UNICODE);

            $this->line("assembly_id={$row->assembly_id} product_id={$row->target_product_id} -> units={$jsonUnits}");

            if ($dryRun) {
                continue;
            }

            DB::table('assembly_products')
                ->where('assembly_id', $row->assembly_id)
                ->where('product_id', $row->target_product_id)
                ->update(['product_unit' => $jsonUnits, 'updated_at' => now()]);
        }
    }

    private function syncDispatchItems(bool $dryRun): void
    {
        // Group dispatch_items by product_id to calculate product_unit globally
        $items = DB::table('dispatch_items')
            ->where('item_type', 'product')
            ->select('id', 'item_id', 'quantity', 'assembly_id', 'product_unit', 'serial_numbers', 'dispatch_id', 'category', 'warehouse_id')
            ->orderBy('item_id')
            ->orderBy('id')
            ->get();

        // Group by dispatch_id and product_id to calculate product_unit per dispatch
        // Bỏ qua assembly_id cũ vì có thể bị sai, tìm lại từ assembly_products
        $groupedItems = $items->groupBy(function($item) {
            return $item->dispatch_id . '_' . $item->item_id . '_' . ($item->warehouse_id ?? '');
        });

        foreach ($groupedItems as $groupKey => $groupItems) {
            // Extract product_id and warehouse_id from group key "dispatch_id_product_id_warehouseId"
            $parts = explode('_', $groupKey);
            $productId = (int) ($parts[1] ?? 0);
            $warehouseId = isset($parts[2]) && $parts[2] !== '' ? (int)$parts[2] : null;
            
            // Collect all serials from all dispatch_items in this group
            $allSerials = [];
            foreach ($groupItems as $item) {
                $serialNumbers = [];
                if (is_string($item->serial_numbers) && $item->serial_numbers !== '') {
                    $decoded = json_decode($item->serial_numbers, true);
                    if (is_array($decoded)) {
                        $serialNumbers = $decoded;
                    }
                }
                $allSerials = array_merge($allSerials, $serialNumbers);
            }

            // Calculate total quantity for this group
            $totalQuantity = $groupItems->sum('quantity');

            // Compute global mapping for all items in this group
            [$assemblyIdStr, $productUnitStr] = $this->computeAssemblyMapping(
                $productId,
                $allSerials,
                $totalQuantity,
                $warehouseId
            );
            
            // Convert strings back to arrays
            $globalAssemblyIds = explode(',', $assemblyIdStr);
            $globalProductUnits = explode(',', $productUnitStr);

            // Now assign assembly_id and product_unit to each item based on its position in the global sequence
            $currentIndex = 0;

            foreach ($groupItems as $item) {
                $quantity = (int) ($item->quantity ?? 1);
                
                // Get assembly_id and product_unit slice for this item
                $itemAssemblyIds = array_slice($globalAssemblyIds, $currentIndex, $quantity);
                $itemProductUnits = array_slice($globalProductUnits, $currentIndex, $quantity);
                $currentIndex += $quantity;

                // Convert to strings
                $assemblyIdStr = implode(',', array_map(fn($v) => $v !== null ? $v : '', $itemAssemblyIds));
                $productUnitStr = implode(',', array_map(fn($v) => $v !== null ? $v : 0, $itemProductUnits));

                $this->line("dispatch_item={$item->id} product={$item->item_id} -> assembly_id='{$assemblyIdStr}' product_unit='{$productUnitStr}'");

                if ($dryRun) {
                    continue;
                }

                // assembly_id: lưu dạng chuỗi "41,42" 
                // product_unit: lưu dạng JSON string "[0,1,2,3]" (có CHECK json_valid constraint)
                $productUnitArray = array_map('intval', $itemProductUnits);
                
                DB::table('dispatch_items')
                    ->where('id', $item->id)
                    ->update([
                        'assembly_id' => $assemblyIdStr,
                        'product_unit' => json_encode($productUnitArray),
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    /**
     * Compute assembly_id and product_unit strings for a product based on serials and availability.
     * Mirrors controller logic for N/A distribution.
     */
    private function computeAssemblyMapping(int $productId, array $serialNumbers, int $quantity, ?int $warehouseId): array
    {
        $assemblyIds = [];
        $productUnits = [];

        // 1) Map serials to assemblies (where assembly_products.serials contains the serial)
        foreach ($serialNumbers as $serial) {
            if ($serial === null || $serial === '' || strtoupper($serial) === 'N/A' || strtoupper($serial) === 'NA') {
                continue;
            }

            $trimmedSerial = trim($serial);

            // Tìm assembly_id cho serial cụ thể này (giống logic trong DispatchController)
            // Sử dụng model để có thể parse JSON product_unit đúng cách
            $assemblyProduct = AssemblyProduct::where('product_id', $productId)
                ->where(function($q) use ($trimmedSerial) {
                    $q->where('serials', $trimmedSerial)
                      ->orWhereRaw('FIND_IN_SET(?, serials) > 0', [$trimmedSerial]);
                })
                ->first();

            $assemblyId = $assemblyProduct->assembly_id ?? null;
            
            // Lấy product_unit từ assembly_products dựa trên vị trí serial trong serials
            // Logic giống hệt DispatchController::update (lines 1210-1224)
            $unitValue = 0; // Default
            if ($assemblyProduct && $assemblyProduct->product_unit !== null) {
                $productUnitValue = $assemblyProduct->product_unit;
                
                if (is_array($productUnitValue)) {
                    // Tìm vị trí của serial trong assembly_products.serials
                    $serialsStr = $assemblyProduct->serials ?? '';
                    $serialsArray = array_map('trim', explode(',', $serialsStr));
                    
                    // Tìm index của serial trong mảng serials của assembly
                    $serialPositionInAssembly = array_search($trimmedSerial, $serialsArray);
                    
                    if ($serialPositionInAssembly !== false && isset($productUnitValue[$serialPositionInAssembly])) {
                        // Lấy product_unit tại vị trí tương ứng với serial trong assembly
                        $unitValue = (int) $productUnitValue[$serialPositionInAssembly];
                    } else {
                        // Fallback: nếu không tìm thấy, dùng index đầu tiên hoặc 0
                        $unitValue = isset($productUnitValue[0]) ? (int) $productUnitValue[0] : 0;
                    }
                } else if (is_string($productUnitValue)) {
                    // Parse JSON string nếu cần
                    $decoded = json_decode($productUnitValue, true);
                    if (is_array($decoded)) {
                        $serialsStr = $assemblyProduct->serials ?? '';
                        $serialsArray = array_map('trim', explode(',', $serialsStr));
                        $serialPositionInAssembly = array_search($trimmedSerial, $serialsArray);
                        
                        if ($serialPositionInAssembly !== false && isset($decoded[$serialPositionInAssembly])) {
                            $unitValue = (int) $decoded[$serialPositionInAssembly];
                        } else {
                            $unitValue = isset($decoded[0]) ? (int) $decoded[0] : 0;
                        }
                    } else {
                        $unitValue = (int) $productUnitValue;
                    }
                } else {
                    $unitValue = (int) $productUnitValue;
                }
            }

            $assemblyIds[] = $assemblyId;
            $productUnits[] = $unitValue;
        }

        // 2) Fill remaining N/A based on available assemblies without serials
        $naQuantity = $quantity - count($assemblyIds);
        if ($naQuantity > 0) {
            // Lấy danh sách assembly cho sản phẩm này có slot N/A còn lại và thuộc kho nhận theo Testing
            $availableAssemblies = AssemblyProduct::where('product_id', $productId)
                ->orderBy('assembly_id')
                ->get()
                ->filter(function($ap) use ($productId, $warehouseId) {
                    // Chỉ nhận assembly có NA capacity > 0 tại đúng kho
                    return $this->getNaCapacityForAssemblyProductCmd((int)$ap->assembly_id, (int)$productId, $warehouseId, true) > 0;
                })
                ->sortBy(function($ap) {
                    // Ưu tiên assembly không có serial
                    $hasSerials = $ap->serials && $ap->serials !== '' && $ap->serials !== 'N/A' && $ap->serials !== 'NA';
                    return $hasSerials ? 1 : 0;
                })
                ->values();

            $maxUnit = !empty($productUnits) ? max($productUnits) : -1;
            $index = 0;
            for ($i = 0; $i < $naQuantity; $i++) {
                if ($index >= $availableAssemblies->count()) {
                    $assemblyIds[] = null;
                    $productUnits[] = ++$maxUnit;
                    continue;
                }
                $ap = $availableAssemblies[$index];
                $aid = (int)$ap->assembly_id;
                // Lấy unit đầu tiên hoặc 0
                $unit = 0;
                $pu = $ap->product_unit;
                if (is_string($pu)) {
                    $decoded = json_decode($pu, true);
                    if (is_array($decoded) && isset($decoded[0])) { $unit = (int)$decoded[0]; }
                    elseif ($pu !== '') { $unit = (int)$pu; }
                } elseif (is_array($pu) && isset($pu[0])) {
                    $unit = (int)$pu[0];
                } elseif ($pu !== null) {
                    $unit = (int)$pu;
                }

                $assemblyIds[] = $aid;
                $productUnits[] = $unit;
                $index++;
            }
        }

        // Implode to strings keeping positions; nulls -> '' for assembly, 0 for unit
        $assemblyIdStr = implode(',', array_map(fn($v) => $v !== null ? $v : '', $assemblyIds));
        $productUnitStr = implode(',', array_map(fn($v) => $v !== null ? $v : 0, $productUnits));

        return [$assemblyIdStr, $productUnitStr];
    }

    private function countUsedNaUnitsCmd(int $assemblyId, int $productId): int
    {
        $approvedItems = DB::table('dispatch_items as di')
            ->join('dispatches as d', 'd.id', '=', 'di.dispatch_id')
            ->where('di.assembly_id', $assemblyId)
            ->where('di.item_type', 'product')
            ->where('di.item_id', $productId)
            ->where('d.status', 'approved')
            ->select('di.quantity', 'di.serial_numbers')
            ->get();
        $used = 0;
        foreach ($approvedItems as $di) {
            $qty = (int)($di->quantity ?? 0);
            $serials = [];
            if (is_string($di->serial_numbers) && $di->serial_numbers !== '') {
                $decoded = json_decode($di->serial_numbers, true);
                if (is_array($decoded)) { $serials = $decoded; }
            }
            $realSerialCount = 0;
            foreach ($serials as $s) {
                $s = is_string($s) ? trim($s) : '';
                if ($s === '' || strtoupper($s) === 'N/A' || strtoupper($s) === 'NA' || strpos($s, 'N/A-') === 0) { continue; }
                $realSerialCount++;
            }
            $used += max(0, $qty - $realSerialCount);
        }
        return $used;
    }

    private function getNaCapacityForAssemblyProductCmd(int $assemblyId, int $productId, ?int $warehouseId, bool $requireProductSpecific = true): int
    {
        if ($warehouseId) {
            $existsProductSpecific = DB::table('testings as t')
                ->join('testing_items as ti', 'ti.testing_id', '=', 't.id')
                ->where('t.assembly_id', $assemblyId)
                ->whereIn('t.status', ['completed', 'approved', 'received'])
                ->where('t.success_warehouse_id', $warehouseId)
                ->whereIn('ti.item_type', ['finished_product', 'product'])
                ->where('ti.product_id', $productId)
                ->exists();

            if (!$existsProductSpecific) {
                if ($requireProductSpecific) { return 0; }
                $existsAny = DB::table('testings as t')
                    ->where('t.assembly_id', $assemblyId)
                    ->whereIn('t.status', ['completed', 'approved', 'received'])
                    ->where('t.success_warehouse_id', $warehouseId)
                    ->exists();
                if (!$existsAny) { return 0; }
            }
        }

        $ap = AssemblyProduct::where('assembly_id', $assemblyId)
            ->where('product_id', $productId)
            ->first();
        if (!$ap) { return 0; }

        $units = [];
        if (is_string($ap->product_unit)) {
            $decoded = json_decode($ap->product_unit, true);
            if (is_array($decoded)) { $units = $decoded; }
            elseif ($ap->product_unit !== '') { $units = [$ap->product_unit]; }
        } elseif (is_array($ap->product_unit)) {
            $units = $ap->product_unit;
        } elseif ($ap->product_unit !== null) {
            $units = [$ap->product_unit];
        }
        $unitsCount = count($units);

        $serialCount = 0;
        if ($ap->serials && $ap->serials !== 'N/A' && $ap->serials !== 'NA') {
            $parts = preg_split('/[\s,;|\/]+/', (string)$ap->serials, -1, PREG_SPLIT_NO_EMPTY);
            $serialCount = is_array($parts) ? count($parts) : 1;
        }

        $baseCapacity = $unitsCount > 0 ? max(0, $unitsCount - $serialCount) : ($serialCount > 0 ? 0 : 1);
        $used = $this->countUsedNaUnitsCmd($assemblyId, $productId);
        return max(0, $baseCapacity - $used);
    }

    private function countSerialsString(?string $serials): int
    {
        if (!$serials) return 0;
        $parts = preg_split('/[\s,;|\/]+/', $serials, -1, PREG_SPLIT_NO_EMPTY);
        return is_array($parts) ? count($parts) : 0;
    }

}


