<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Testing;
use App\Models\Warehouse;
use App\Models\InventoryImport;
use App\Models\WarehouseTransfer;
use App\Models\Dispatch;
use App\Http\Controllers\TestingController;

class GenerateTestingFixture extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'testing:fixture {--code=} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run updateInventory for a given testing code (or latest completed) and print created documents';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $code = $this->option('code');
        $dryRun = (bool)$this->option('dry-run');

        // 1) Pick target testing
        $testingQuery = Testing::query();
        if ($code) {
            $testing = $testingQuery->where('test_code', $code)->first();
            if (!$testing) {
                $this->error("Không tìm thấy phiếu kiểm thử với mã: {$code}");
                return self::FAILURE;
            }
        } else {
            $testing = $testingQuery->where('status', 'completed')->orderByDesc('created_at')->first();
            if (!$testing) {
                $this->error('Không có phiếu kiểm thử nào ở trạng thái completed.');
                return self::FAILURE;
            }
        }

        // Load relations used by controller branches
        $testing->load(['items', 'assembly.project']);

        $this->line("Testing: {$testing->test_code} | type={$testing->test_type} | status={$testing->status} | assemblyPurpose=" . ($testing->assembly->purpose ?? 'N/A'));
        $this->line("is_inventory_updated=" . (int)($testing->is_inventory_updated));

        // 2) Determine warehouses and payload
        $successWarehouse = Warehouse::where('status', 'active')->first();
        $failWarehouse = Warehouse::where('status', 'active')->where('id', '!=', optional($successWarehouse)->id)->first();
        if (!$failWarehouse) { $failWarehouse = $successWarehouse; }

        // For finished_product with assembly purpose=project, use project_export flag
        $successWarehouseId = $successWarehouse?->id;
        if ($testing->test_type === 'finished_product' && ($testing->assembly->purpose ?? null) === 'project') {
            $successWarehouseId = 'project_export';
        }

        $payload = [
            'success_warehouse_id' => $successWarehouseId,
            'fail_warehouse_id' => $failWarehouse?->id,
            'redirect_to' => 'index',
        ];

        // 3) Dry-run output
        if ($dryRun) {
            $this->info('[DRY RUN] Payload: ' . json_encode($payload));
            return self::SUCCESS;
        }

        // 4) Call controller method
        try {
            // Snapshot before
            $beforeImports = InventoryImport::count();
            $beforeTransfers = WarehouseTransfer::count();
            $beforeDispatches = Dispatch::count();

            $controller = app(TestingController::class);
            $request = Request::create('/testing/' . $testing->id . '/update-inventory', 'POST', $payload);

            $response = $controller->updateInventory($request, $testing);

            // Refresh testing and query created docs referencing this test code
            $testing->refresh();

            $imports = InventoryImport::where('order_code', 'like', '%' . $testing->test_code . '%')
                ->orderByDesc('id')->take(5)->get(['id','import_code','warehouse_id','notes','created_at']);
            $transfers = WarehouseTransfer::where('notes', 'like', '%' . $testing->test_code . '%')
                ->orderByDesc('id')->take(5)->get(['id','transfer_code','source_warehouse_id','destination_warehouse_id','status','created_at']);
            $dispatches = Dispatch::where('dispatch_note', 'like', '%' . $testing->test_code . '%')
                ->orderByDesc('id')->take(5)->get(['id','dispatch_code','dispatch_type','project_id','status','created_at']);

            $afterImports = InventoryImport::count();
            $afterTransfers = WarehouseTransfer::count();
            $afterDispatches = Dispatch::count();

            $this->newLine();
            $this->info('Kết quả:');
            $this->line('- is_inventory_updated = ' . (int)$testing->is_inventory_updated);
            $this->line('- Imports created: ' . ($afterImports - $beforeImports));
            foreach ($imports as $imp) {
                $this->line("  • NK: {$imp->import_code} | wh={$imp->warehouse_id} | {$imp->created_at}");
            }
            $this->line('- Transfers created: ' . ($afterTransfers - $beforeTransfers));
            foreach ($transfers as $tr) {
                $this->line("  • CT: {$tr->transfer_code} | {$tr->status} | {$tr->created_at}");
            }
            $this->line('- Dispatches created: ' . ($afterDispatches - $beforeDispatches));
            foreach ($dispatches as $dp) {
                $this->line("  • XK: {$dp->dispatch_code} | {$dp->dispatch_type} | {$dp->status} | {$dp->created_at}");
            }

            // Surface redirect message if any
            if (method_exists($response, 'getSession')) {
                $session = $response->getSession();
                if ($session && $session->has('success')) {
                    $this->newLine();
                    $this->info('Message: ' . $session->get('success'));
                } elseif ($session && $session->has('error')) {
                    $this->newLine();
                    $this->error('Error: ' . $session->get('error'));
                }
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('testing:fixture error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->error('Lỗi: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
