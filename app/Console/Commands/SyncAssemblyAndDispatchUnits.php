<?php

namespace App\Console\Commands;

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
            ->select('id', 'item_id', 'quantity', 'assembly_id', 'product_unit', 'serial_numbers', 'dispatch_id', 'category')
            ->orderBy('item_id')
            ->orderBy('id')
            ->get();

        // Group by product_id only to calculate global product_unit
        $groupedItems = $items->groupBy('item_id');

        foreach ($groupedItems as $productId => $groupItems) {
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
                (int) $productId,
                $allSerials,
                $totalQuantity
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

                // Skip if unchanged
                $currentAssembly = (string) ($item->assembly_id ?? '');
                $currentUnits = (string) ($item->product_unit ?? '');
                
                if ($assemblyIdStr === $currentAssembly && $productUnitStr === $currentUnits) {
                    continue;
                }

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
    private function computeAssemblyMapping(int $productId, array $serialNumbers, int $quantity): array
    {
        $assemblyIds = [];
        $productUnits = [];

        // 1) Map serials to assemblies (where assembly_products.serials contains the serial)
        $unitCounter = 0; // Counter để tạo 0,1,2,3...
        foreach ($serialNumbers as $serialIndex => $serial) {
            if ($serial === null || $serial === '' || strtoupper($serial) === 'N/A' || strtoupper($serial) === 'NA') {
                continue;
            }

            // Tìm assembly_id cho serial cụ thể này (giống logic trong DispatchController)
            $row = DB::table('assembly_products')
                ->where('product_id', $productId)
                ->where(function($q) use ($serial) {
                    $s = trim($serial);
                    $q->where('serials', $s)
                      ->orWhereRaw('FIND_IN_SET(?, serials) > 0', [$s]);
                })
                ->first();

            $assemblyId = $row->assembly_id ?? null;
            $unitValue = $unitCounter++; // product_unit cho serials: 0,1,2,3... theo thứ tự

            $assemblyIds[] = $assemblyId;
            $productUnits[] = $unitValue;
        }

        // 2) Fill remaining N/A based on available assemblies without serials
        $naQuantity = $quantity - count($assemblyIds);
        if ($naQuantity > 0) {
            // Available assemblies for this product (no serials)
            $availableAssemblies = DB::table('assembly_products')
                ->where('product_id', $productId)
                ->where(function ($q) {
                    $q->whereNull('serials')
                      ->orWhere('serials', '=','')
                      ->orWhere('serials','=','N/A')
                      ->orWhere('serials','=','NA');
                })
                ->orderBy('assembly_id')
                ->get();

            $assemblyIndex = 0;
            for ($i = 0; $i < $naQuantity; $i++) {
                if (!isset($availableAssemblies[$assemblyIndex])) {
                    $assemblyIds[] = null;
                    $productUnits[] = $unitCounter++; // Tiếp tục counter
                    continue;
                }

                $assembly = $availableAssemblies[$assemblyIndex];

                $assemblyIds[] = $assembly->assembly_id;
                $productUnits[] = $unitCounter++; // Tiếp tục counter: 0,1,2,3...

                // advance to next assembly when units consumed
                $assemblyIndex++;
            }
        }

        // Implode to strings keeping positions; nulls -> '' for assembly, 0 for unit
        $assemblyIdStr = implode(',', array_map(fn($v) => $v !== null ? $v : '', $assemblyIds));
        $productUnitStr = implode(',', array_map(fn($v) => $v !== null ? $v : 0, $productUnits));

        return [$assemblyIdStr, $productUnitStr];
    }

    private function countSerialsString(?string $serials): int
    {
        if (!$serials) return 0;
        $parts = preg_split('/[\s,;|\/]+/', $serials, -1, PREG_SPLIT_NO_EMPTY);
        return is_array($parts) ? count($parts) : 0;
    }

}


