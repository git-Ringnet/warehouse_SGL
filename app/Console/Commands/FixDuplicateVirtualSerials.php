<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\SerialHelper;

class FixDuplicateVirtualSerials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:virtual-serials 
                            {--dry-run : Chỉ hiển thị các serial sẽ được sửa, không thay đổi dữ liệu}
                            {--project-id= : Chỉ sửa cho một dự án cụ thể}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sửa các virtual serial N/A bị trùng lặp (N/A-0, N/A-1...) thành format mới duy nhất (N/A-XXXXXX)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $projectId = $this->option('project-id');

        $this->info('=================================================');
        $this->info(' FIX DUPLICATE VIRTUAL SERIALS');
        $this->info('=================================================');
        $this->info('Mô tả: Chuyển đổi các virtual serial format cũ (N/A-0, N/A-1...)');
        $this->info('       sang format mới duy nhất toàn cục (N/A-XXXXXX)');
        $this->info('');

        if ($dryRun) {
            $this->warn('*** CHẾ ĐỘ DRY-RUN - Không thay đổi dữ liệu ***');
            $this->info('');
        }

        // Thống kê
        $stats = [
            'total_dispatch_items' => 0,
            'items_with_legacy_serials' => 0,
            'serials_replaced' => 0,
            'items_with_null_serials' => 0,
            'serials_added' => 0,
        ];

        // Query dispatch_items
        $query = DB::table('dispatch_items as di')
            ->join('dispatches as d', 'd.id', '=', 'di.dispatch_id')
            ->whereIn('d.status', ['approved', 'completed'])
            ->where('di.item_type', 'product');

        if ($projectId) {
            $query->where('d.project_id', $projectId);
            $this->info("Lọc theo dự án ID: {$projectId}");
        }

        $dispatchItems = $query
            ->select('di.*', 'd.dispatch_code', 'd.project_id')
            ->get();

        $stats['total_dispatch_items'] = $dispatchItems->count();
        $this->info("Tìm thấy {$stats['total_dispatch_items']} dispatch items (thành phẩm) cần kiểm tra.");
        $this->info('');

        $bar = $this->output->createProgressBar($dispatchItems->count());
        $bar->start();

        foreach ($dispatchItems as $item) {
            $bar->advance();

            // Parse serial_numbers
            $serialNumbers = $item->serial_numbers;
            if (is_string($serialNumbers)) {
                $serialNumbers = json_decode($serialNumbers, true);
            }

            // Trường hợp 1: serial_numbers là NULL hoặc mảng rỗng
            if (empty($serialNumbers) || (is_array($serialNumbers) && count($serialNumbers) === 0)) {
                $quantity = (int) $item->quantity;

                if ($quantity > 0) {
                    $stats['items_with_null_serials']++;

                    // Sinh virtual serial mới cho tất cả quantity
                    $newVirtualSerials = SerialHelper::generateUniqueVirtualSerials($quantity);
                    $stats['serials_added'] += $quantity;

                    if (!$dryRun) {
                        DB::table('dispatch_items')
                            ->where('id', $item->id)
                            ->update(['serial_numbers' => json_encode($newVirtualSerials)]);

                        Log::info('Đã thêm virtual serial cho dispatch item không có serial', [
                            'dispatch_item_id' => $item->id,
                            'dispatch_code' => $item->dispatch_code,
                            'quantity' => $quantity,
                            'new_serials' => $newVirtualSerials
                        ]);
                    } else {
                        $this->line("\n[DRY-RUN] Dispatch Item #{$item->id} ({$item->dispatch_code}) - Thêm {$quantity} virtual serial");
                    }
                }
                continue;
            }

            if (!is_array($serialNumbers)) {
                continue;
            }

            // Trường hợp 2: Có serial nhưng cần kiểm tra legacy format và số lượng
            $hasLegacySerials = false;
            $needsAdditionalSerials = false;

            $newSerialNumbers = [];
            $oldSerialNumbers = [];

            foreach ($serialNumbers as $serial) {
                if (SerialHelper::isLegacyVirtualSerial($serial)) {
                    // Đây là format cũ N/A-0, N/A-1... cần thay thế
                    $hasLegacySerials = true;
                    $oldSerialNumbers[] = $serial;
                    $newSerial = SerialHelper::generateUniqueVirtualSerial();
                    $newSerialNumbers[] = $newSerial;
                    $stats['serials_replaced']++;
                } else {
                    // Giữ nguyên serial (thật hoặc format mới)
                    $newSerialNumbers[] = $serial;
                }
            }

            // Kiểm tra nếu quantity > số serial hiện có
            $quantity = (int) $item->quantity;
            $currentSerialCount = count($serialNumbers);

            if ($quantity > $currentSerialCount) {
                $needsAdditionalSerials = true;
                $needCount = $quantity - $currentSerialCount;
                $additionalSerials = SerialHelper::generateUniqueVirtualSerials($needCount);
                $newSerialNumbers = array_merge($newSerialNumbers, $additionalSerials);
                $stats['serials_added'] += $needCount;
            }

            if ($hasLegacySerials || $needsAdditionalSerials) {
                $stats['items_with_legacy_serials']++;

                if (!$dryRun) {
                    DB::table('dispatch_items')
                        ->where('id', $item->id)
                        ->update(['serial_numbers' => json_encode(array_values($newSerialNumbers))]);

                    Log::info('Đã cập nhật virtual serial cho dispatch item', [
                        'dispatch_item_id' => $item->id,
                        'dispatch_code' => $item->dispatch_code,
                        'old_serials' => $oldSerialNumbers,
                        'new_serials' => $newSerialNumbers,
                        'has_legacy' => $hasLegacySerials,
                        'added_count' => $needsAdditionalSerials ? ($quantity - $currentSerialCount) : 0
                    ]);
                } else {
                    $this->line("\n[DRY-RUN] Dispatch Item #{$item->id} ({$item->dispatch_code})");
                    if (!empty($oldSerialNumbers)) {
                        $this->line("  - Legacy serials: " . implode(', ', $oldSerialNumbers));
                    }
                    if ($needsAdditionalSerials) {
                        $this->line("  - Cần thêm " . ($quantity - $currentSerialCount) . " serial (quantity: {$quantity}, current: {$currentSerialCount})");
                    }
                }
            }
        }

        $bar->finish();

        $this->info("\n");
        $this->info('=================================================');
        $this->info(' KẾT QUẢ');
        $this->info('=================================================');
        $this->table(
            ['Thống kê', 'Số lượng'],
            [
                ['Tổng dispatch items (thành phẩm)', $stats['total_dispatch_items']],
                ['Items có serial format cũ/thiếu', $stats['items_with_legacy_serials']],
                ['Items không có serial (NULL/rỗng)', $stats['items_with_null_serials']],
                ['Serial đã thay thế (format cũ → mới)', $stats['serials_replaced']],
                ['Serial đã thêm mới', $stats['serials_added']],
            ]
        );

        if ($dryRun) {
            $this->warn('');
            $this->warn('*** ĐÂY LÀ CHẾ ĐỘ DRY-RUN - Không có thay đổi nào được lưu ***');
            $this->info('Chạy lại lệnh mà không có --dry-run để áp dụng thay đổi.');
        } else {
            $this->info('');
            $this->info('✅ Đã hoàn thành sửa virtual serial!');
        }

        return Command::SUCCESS;
    }
}
