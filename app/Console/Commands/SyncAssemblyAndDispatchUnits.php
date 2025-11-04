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
            ->select('id', 'item_id', 'quantity', 'assembly_id', 'product_unit', 'serial_numbers', 'dispatch_id', 'category')
            ->orderBy('item_id')
            ->orderBy('id')
            ->get();

        // Group by dispatch_id and product_id to calculate product_unit per dispatch
        // Bỏ qua assembly_id cũ vì có thể bị sai, tìm lại từ assembly_products
        $groupedItems = $items->groupBy(function($item) {
            return $item->dispatch_id . '_' . $item->item_id;
        });

        foreach ($groupedItems as $groupKey => $groupItems) {
            // Extract product_id from group key "dispatch_id_product_id"
            $productId = (int) explode('_', $groupKey)[1];
            
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
            // Lấy danh sách assembly cho sản phẩm này KHÔNG có serial
            $availableAssemblies = AssemblyProduct::where('product_id', $productId)
                ->where(function ($q) {
                    $q->whereNull('serials')
                      ->orWhere('serials', '=','')
                      ->orWhere('serials','=','N/A')
                      ->orWhere('serials','=','NA');
                })
                ->orderBy('assembly_id')
                ->get();

            // Tính sức chứa (số slot N/A) cho từng assembly dựa trên product_unit
            $assemblyCapacity = [];
            foreach ($availableAssemblies as $a) {
                $cap = 1;
                $pu = $a->product_unit;
                if (is_string($pu)) {
                    $decoded = json_decode($pu, true);
                    if (is_array($decoded)) $cap = max(1, count($decoded));
                    else $cap = 1;
                } elseif (is_array($pu)) {
                    $cap = max(1, count($pu));
                } elseif (is_numeric($pu)) {
                    $cap = 1; // single slot
                }
                $assemblyCapacity[(int)$a->assembly_id] = $cap;
            }

            // Trừ đi các slot đã sử dụng ở những phiếu đã duyệt (approved)
            if (!empty($assemblyCapacity)) {
                $approvedItems = DB::table('dispatch_items')
                    ->join('dispatches', 'dispatches.id', '=', 'dispatch_items.dispatch_id')
                    ->where('dispatch_items.item_type', 'product')
                    ->where('dispatch_items.item_id', $productId)
                    ->where('dispatches.status', 'approved')
                    ->whereNotNull('dispatch_items.assembly_id')
                    ->select('dispatch_items.assembly_id')
                    ->get();

                foreach ($approvedItems as $it) {
                    $ids = array_filter(array_map('trim', explode(',', (string)$it->assembly_id)), function($v){return $v !== '';});
                    foreach ($ids as $idStr) {
                        $aid = (int)$idStr;
                        if (isset($assemblyCapacity[$aid]) && $assemblyCapacity[$aid] > 0) {
                            $assemblyCapacity[$aid] -= 1; // tiêu thụ 1 slot cho mỗi lần xuất
                        }
                    }
                }
            }

            // Slot còn lại cho từng assembly (không âm)
            $assemblyRemaining = [];
            foreach ($assemblyCapacity as $aid => $cap) {
                $assemblyRemaining[$aid] = max(0, $cap);
            }

            // Tính max product_unit hiện tại để bắt đầu từ đó cho N/A
            $maxUnit = !empty($productUnits) ? max($productUnits) : -1;
            
            // Lần lượt phân bổ N/A vào các assembly còn slot
            $assemblyOrder = array_keys($assemblyRemaining);
            $orderIndex = 0;
            for ($i = 0; $i < $naQuantity; $i++) {
                // Tìm assembly còn slot
                $chosenAid = null;
                for ($t = 0; $t < count($assemblyOrder); $t++) {
                    $idx = ($orderIndex + $t) % (count($assemblyOrder) ?: 1);
                    $aid = $assemblyOrder[$idx] ?? null;
                    if ($aid !== null && ($assemblyRemaining[$aid] ?? 0) > 0) {
                        $chosenAid = $aid; $orderIndex = $idx; break;
                    }
                }

                if ($chosenAid === null) {
                    // Không còn slot ở bất kỳ assembly nào
                    $assemblyIds[] = null;
                    $productUnits[] = ++$maxUnit;
                    continue;
                }

                // Lấy product_unit mặc định theo assembly (nếu có mảng, lấy phần tử đầu)
                $naUnit = ++$maxUnit;
                $assembly = $availableAssemblies->firstWhere('assembly_id', $chosenAid);
                if ($assembly && $assembly->product_unit !== null) {
                    $pu = $assembly->product_unit;
                    if (is_string($pu)) {
                        $decoded = json_decode($pu, true);
                        if (is_array($decoded) && !empty($decoded)) $naUnit = (int)$decoded[0];
                        else $naUnit = (int)$pu;
                    } elseif (is_array($pu) && !empty($pu)) {
                        $naUnit = (int)$pu[0];
                    } else {
                        $naUnit = (int)$pu;
                    }
                }

                $assemblyIds[] = $chosenAid;
                $productUnits[] = $naUnit;
                $assemblyRemaining[$chosenAid] = max(0, ($assemblyRemaining[$chosenAid] ?? 0) - 1);
                $orderIndex++;
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


