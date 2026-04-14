<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ChangeLog;
use App\Models\DispatchReturn;
use App\Models\DispatchItem;
use App\Models\Project;
use App\Models\Rental;
use Illuminate\Support\Facades\DB;

class FixRecallChangeLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fix:recall-changelogs {--dry-run : Chỉ kiểm tra, không sửa dữ liệu}';

    /**
     * The console command description.
     */
    protected $description = 'Sửa lại Nhật ký thay đổi (change_logs) loại Thu hồi: bổ sung Ghi chú, Mô tả nguồn, sửa Mã/Tên item bị sai';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('=== CHẾ ĐỘ KIỂM TRA (dry-run) - KHÔNG SỬA DỮ LIỆU ===');
        } else {
            if (!$this->confirm('Bạn có chắc muốn sửa dữ liệu Nhật ký thay đổi Thu hồi?')) {
                $this->info('Đã hủy.');
                return 0;
            }
        }

        $logs = ChangeLog::where('change_type', 'thu_hoi')->get();
        $this->info("Tổng phiếu thu hồi: {$logs->count()}");

        $fixedNotes = 0;
        $fixedDesc = 0;
        $fixedItemCode = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($logs as $log) {
                $updates = [];

                // Tìm DispatchReturn qua document_code (return_code)
                $dispatchReturn = DispatchReturn::where('return_code', $log->document_code)->first();
                if (!$dispatchReturn) {
                    $errors[] = "LogID={$log->id}: Không tìm thấy DispatchReturn với code={$log->document_code}";
                    continue;
                }

                $dispatchItem = DispatchItem::with(['dispatch', 'material', 'product', 'good'])
                    ->find($dispatchReturn->dispatch_item_id);
                if (!$dispatchItem) {
                    $errors[] = "LogID={$log->id}: Không tìm thấy DispatchItem ID={$dispatchReturn->dispatch_item_id}";
                    continue;
                }

                // === FIX 1: Ghi chú (Notes) ===
                if (empty($log->notes) && !empty($dispatchReturn->reason)) {
                    $updates['notes'] = $dispatchReturn->reason;
                    $fixedNotes++;

                    if ($isDryRun) {
                        $this->line("  [FIX NOTES] ID={$log->id} | Notes sẽ = \"{$dispatchReturn->reason}\"");
                    }
                }

                // === FIX 2: Mô tả (Description) - thêm thông tin nguồn ===
                $hasSource = (
                    stripos($log->description ?? '', 'dự án') !== false ||
                    stripos($log->description ?? '', 'cho thuê') !== false ||
                    stripos($log->description ?? '', 'project') !== false
                );

                if (!$hasSource) {
                    $dispatch = $dispatchItem->dispatch;
                    $newDesc = '';

                    if ($dispatch && $dispatch->project_id) {
                        $project = Project::find($dispatch->project_id);
                        $newDesc = 'Thu hồi từ Dự án: ' . ($project ? $project->project_name : 'Không xác định');
                    } elseif ($dispatch && $dispatch->dispatch_type === 'rental') {
                        $rental = null;
                        if (preg_match('/([A-Z]{2,}\d+)/', $dispatch->project_receiver ?? '', $matches)) {
                            $rental = Rental::where('rental_code', $matches[1])->first();
                        }
                        if ($rental) {
                            $newDesc = 'Thu hồi từ Phiếu cho thuê: ' . ($rental->rental_name ?: $rental->rental_code);
                        } else {
                            $newDesc = 'Thu hồi từ Phiếu cho thuê: ' . ($dispatch->project_receiver ?? 'Không xác định');
                        }
                    }

                    if (!empty($newDesc)) {
                        $suffix = '';
                        if (stripos($log->description ?? '', 'Serial') !== false) {
                            $suffix = ' (Serial)';
                        } elseif (stripos($log->description ?? '', 'đo lường') !== false) {
                            $suffix = ' (Hàng đo lường)';
                        }
                        $updates['description'] = $newDesc . $suffix;
                        $fixedDesc++;

                        if ($isDryRun) {
                            $this->line("  [FIX DESC] ID={$log->id} | \"{$log->description}\" → \"{$updates['description']}\"");
                        }
                    }
                }

                // === FIX 3: Sửa item_code/item_name sai ===
                $correctCode = 'N/A';
                $correctName = 'N/A';
                switch ($dispatchItem->item_type) {
                    case 'material':
                        if ($dispatchItem->material) {
                            $correctCode = $dispatchItem->material->code;
                            $correctName = $dispatchItem->material->name;
                        }
                        break;
                    case 'product':
                        if ($dispatchItem->product) {
                            $correctCode = $dispatchItem->product->code;
                            $correctName = $dispatchItem->product->name;
                        }
                        break;
                    case 'good':
                        if ($dispatchItem->good) {
                            $correctCode = $dispatchItem->good->code;
                            $correctName = $dispatchItem->good->name;
                        }
                        break;
                }

                if ($correctCode !== 'N/A' && ($log->item_code !== $correctCode || $log->item_name !== $correctName)) {
                    $updates['item_code'] = $correctCode;
                    $updates['item_name'] = $correctName;
                    $fixedItemCode++;

                    if ($isDryRun) {
                        $this->line("  [FIX CODE] ID={$log->id} | [{$log->item_code} / {$log->item_name}] → [{$correctCode} / {$correctName}]");
                    }
                }

                // Cập nhật bằng DB::table để tránh Eloquent tự thay đổi timestamps
                // Giữ nguyên time_changed gốc (tránh MySQL auto ON UPDATE CURRENT_TIMESTAMP)
                if (!empty($updates) && !$isDryRun) {
                    $updates['time_changed'] = $log->time_changed;
                    DB::table('change_logs')
                        ->where('id', $log->id)
                        ->update($updates);
                }
            }

            if ($isDryRun) {
                DB::rollBack();
                $this->info("\n=== KẾT QUẢ KIỂM TRA (chưa sửa) ===");
            } else {
                DB::commit();
                $this->info("\n=== KẾT QUẢ ĐÃ SỬA ===");
            }

            $this->table(
                ['Loại sửa', 'Số phiếu'],
                [
                    ['Bổ sung Ghi chú (Notes)', $fixedNotes],
                    ['Bổ sung Mô tả nguồn (Description)', $fixedDesc],
                    ['Sửa Mã/Tên item', $fixedItemCode],
                ]
            );

            if (!empty($errors)) {
                $this->warn("\nCảnh báo ({$this->count($errors)} lỗi):");
                foreach ($errors as $err) {
                    $this->line("  ⚠ {$err}");
                }
            }

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("LỖI NGHIÊM TRỌNG - ĐÃ ROLLBACK: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }

    private function count($arr)
    {
        return count($arr);
    }
}
