<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Warranty;
use App\Models\Rental;
use App\Models\Project;
use App\Models\Dispatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixRentalWarranties extends Command
{
    protected $signature = 'fix:rental-warranties {--force : Thực hiện thay đổi thực tế (mặc định chỉ dry-run)}';
    protected $description = 'Sửa lỗi phiếu bảo hành điện tử: phát hiện warranty dự án bị ghi đè tên cho thuê, phân loại lại, xóa phiếu lỗi và đồng bộ format chuẩn';

    public function handle()
    {
        $isDryRun = !$this->option('force');

        if ($isDryRun) {
            $this->warn('🔍 Chế độ DRY-RUN: Không thay đổi dữ liệu. Dùng --force để áp dụng.');
            $this->newLine();
        } else {
            $this->warn('⚠️  Chế độ FORCE: Sẽ thay đổi dữ liệu thực tế!');
            if (!$this->confirm('Bạn có chắc chắn muốn tiếp tục?')) {
                return;
            }
        }

        $stats = [
            'corrupted_restored' => 0,
            'dispatch_fixed' => 0,
            'type_migrated' => 0,
            'orphan_deleted' => 0,
            'project_standardized' => 0,
            'rental_standardized' => 0,
            'duplicate_deleted' => 0,
        ];

        // ==============================
        // PHẦN 1: Phát hiện warranty DỰ ÁN bị ghi đè tên cho thuê
        // Root cause: syncWarrantiesFromRental cũ dùng item_id trùng project_id
        // ==============================
        $this->info('━━━ PHẦN 1: Phát hiện warranty dự án bị ghi đè tên cho thuê ━━━');
        $this->line('  (Bug cũ: rental_id trùng project_id → ghi đè tên warranty dự án)');

        $projectWarranties = Warranty::where('item_type', 'project')->get();
        
        foreach ($projectWarranties as $warranty) {
            $dispatch = $warranty->dispatch;
            if (!$dispatch) continue;
            
            // Warranty thuộc dispatch DỰ ÁN
            if ($dispatch->dispatch_type !== 'project') continue;
            
            $project = Project::find($dispatch->project_id);
            if (!$project) continue;

            // Kiểm tra: tên warranty KHÔNG chứa mã dự án → bị ghi đè bởi rental
            $expectedPrefix = $project->project_code;
            $hasCorrectProjectName = str_contains($warranty->project_name, $expectedPrefix) 
                || str_contains($warranty->project_name, $project->project_name);
            
            if (!$hasCorrectProjectName) {
                // Warranty dự án bị ghi đè tên → phục hồi
                $standardName = Warranty::formatProjectName(
                    $project->project_code,
                    $project->project_name,
                    optional($project->customer)->name ?? $warranty->customer_name
                );

                $stats['corrupted_restored']++;
                $this->line("  🔧 #{$warranty->warranty_code}: BỊ GHI ĐÈ '{$warranty->project_name}'");
                $this->line("     → Phục hồi: '{$standardName}'");

                if (!$isDryRun) {
                    DB::table('warranties')
                        ->where('id', $warranty->id)
                        ->update(['project_name' => $standardName]);
                }
            }
        }

        if ($stats['corrupted_restored'] > 0) {
            $this->warn("  " . ($isDryRun ? 'Cần phục hồi' : 'Đã phục hồi') . " {$stats['corrupted_restored']} warranty bị ghi đè.");
        } else {
            $this->info('  ✅ Không có warranty nào bị ghi đè tên.');
        }
        $this->newLine();

        // ==============================
        // PHẦN 2: Sửa Dispatch bị sai loại (Project -> Rental)
        // ==============================
        $this->info('━━━ PHẦN 2: Sửa Dispatch bị sai loại (Project -> Rental) ━━━');

        $misclassifiedDispatches = Dispatch::where('dispatch_type', 'project')
            ->where(function ($q) {
                $q->where('project_receiver', 'like', '%RNT-%')
                  ->orWhere('dispatch_note', 'like', '%RNT-%');
            })
            ->get();

        foreach ($misclassifiedDispatches as $dispatch) {
            $stats['dispatch_fixed']++;
            $this->line("  🚚 Dispatch #{$dispatch->dispatch_code}: type 'project' → 'rental'");
            if (!$isDryRun) {
                DB::table('dispatches')
                    ->where('id', $dispatch->id)
                    ->update(['dispatch_type' => 'rental']);
            }
        }

        if ($stats['dispatch_fixed'] > 0) {
            $this->warn("  " . ($isDryRun ? 'Cần sửa' : 'Đã sửa') . " {$stats['dispatch_fixed']} phiếu xuất.");
        } else {
            $this->info('  ✅ Không có phiếu xuất nào bị sai loại.');
        }
        $this->newLine();

        // ==============================
        // PHẦN 3: Phân loại lại Warranty (Project -> Rental)
        // ==============================
        $this->info('━━━ PHẦN 3: Phân loại lại Warranty Rental ━━━');

        // Reload after Phase 2 fixes
        $rentalTypedWarranties = Warranty::where('item_type', 'project')
            ->whereHas('dispatch', function ($q) {
                $q->where('dispatch_type', 'rental');
            })
            ->get();

        foreach ($rentalTypedWarranties as $w) {
            $stats['type_migrated']++;
            $this->line("  📦 #{$w->warranty_code}: item_type 'project' → 'rental'");
            if (!$isDryRun) {
                DB::table('warranties')
                    ->where('id', $w->id)
                    ->update(['item_type' => 'rental']);
            }
        }

        if ($stats['type_migrated'] > 0) {
            $this->warn("  " . ($isDryRun ? 'Cần chuyển đổi' : 'Đã chuyển đổi') . " {$stats['type_migrated']} phiếu.");
        } else {
            $this->info('  ✅ Không có phiếu nào cần chuyển đổi.');
        }
        $this->newLine();

        // ==============================
        // PHẦN 4: Xóa warranty lỗi (rental không có dispatch thực)
        // ==============================
        $this->info('━━━ PHẦN 4: Tìm phiếu bảo hành lỗi (Rental rỗng/mất liên kết) ━━━');

        $rentalWarranties = Warranty::where('item_type', 'rental')->get();
        $orphanIds = [];

        foreach ($rentalWarranties as $warranty) {
            $dispatch = $warranty->dispatch;
            if (!$dispatch) {
                $orphanIds[] = $warranty->id;
                $this->line("  ❌ #{$warranty->warranty_code} - Mất liên kết phiếu xuất");
                continue;
            }

            $nonBackupItems = $dispatch->items()->where('category', '!=', 'backup')->count();
            if ($nonBackupItems === 0) {
                $orphanIds[] = $warranty->id;
                $this->line("  ❌ #{$warranty->warranty_code} - Phiếu xuất {$dispatch->dispatch_code} rỗng");
                continue;
            }
        }

        $stats['orphan_deleted'] = count($orphanIds);
        if ($stats['orphan_deleted'] > 0) {
            $this->warn("  Tìm thấy {$stats['orphan_deleted']} phiếu bảo hành lỗi.");
            if (!$isDryRun) {
                DB::table('warranties')->whereIn('id', $orphanIds)->delete();
                $this->info("  ✅ Đã xóa.");
            }
        } else {
            $this->info('  ✅ Không tìm thấy phiếu bảo hành rỗng.');
        }
        $this->newLine();

        // ==============================
        // PHẦN 5: Chuẩn hóa tên Project Warranty
        // ==============================
        $this->info('━━━ PHẦN 5: Chuẩn hóa tên Project Warranty ━━━');

        // Re-query after all previous fixes
        $projectWarranties = Warranty::where('item_type', 'project')->get();

        foreach ($projectWarranties as $warranty) {
            $dispatch = $warranty->dispatch;
            if (!$dispatch || $dispatch->dispatch_type === 'rental') continue;

            $project = Project::find($dispatch->project_id);
            if (!$project) continue;

            $standardName = Warranty::formatProjectName(
                $project->project_code,
                $project->project_name,
                optional($project->customer)->name ?? $warranty->customer_name
            );

            if ($warranty->project_name !== $standardName) {
                $stats['project_standardized']++;
                $this->line("  🔄 #{$warranty->warranty_code}: '{$warranty->project_name}' → '{$standardName}'");

                if (!$isDryRun) {
                    DB::table('warranties')
                        ->where('id', $warranty->id)
                        ->update(['project_name' => $standardName]);
                }
            }
        }

        if ($stats['project_standardized'] > 0) {
            $this->warn("  " . ($isDryRun ? 'Cần chuẩn hóa' : 'Đã chuẩn hóa') . " {$stats['project_standardized']} phiếu dự án.");
        } else {
            $this->info('  ✅ Tất cả phiếu dự án đã đúng chuẩn.');
        }
        $this->newLine();

        // ==============================
        // PHẦN 6: Chuẩn hóa tên Rental Warranty
        // ==============================
        $this->info('━━━ PHẦN 6: Chuẩn hóa tên Rental Warranty ━━━');

        $rentalWarranties = Warranty::where('item_type', 'rental')->get();

        foreach ($rentalWarranties as $warranty) {
            // Tìm rental qua dispatch (cách chính xác nhất)
            $rental = null;
            $dispatch = $warranty->dispatch;
            if ($dispatch && $dispatch->dispatch_type === 'rental') {
                $rental = Rental::find($dispatch->project_id);
            }
            // Fallback: tìm qua item_id
            if (!$rental) {
                $rental = Rental::find($warranty->item_id);
            }
            if (!$rental) continue;

            $customerDisplay = optional($rental->customer)->name ?? '';
            $standardName = Warranty::formatProjectName(
                $rental->rental_code,
                $rental->rental_name,
                $customerDisplay
            );

            if ($warranty->project_name !== $standardName) {
                $stats['rental_standardized']++;
                $this->line("  🔄 #{$warranty->warranty_code}: '{$warranty->project_name}' → '{$standardName}'");

                if (!$isDryRun) {
                    DB::table('warranties')
                        ->where('id', $warranty->id)
                        ->update(['project_name' => $standardName]);
                }
            }
        }

        if ($stats['rental_standardized'] > 0) {
            $this->warn("  " . ($isDryRun ? 'Cần chuẩn hóa' : 'Đã chuẩn hóa') . " {$stats['rental_standardized']} phiếu rental.");
        } else {
            $this->info('  ✅ Tất cả phiếu rental đã đúng chuẩn.');
        }
        $this->newLine();

        // ==============================
        // PHẦN 7: Xóa phiếu bảo hành trùng lặp
        // ==============================
        $this->info('━━━ PHẦN 7: Xóa phiếu bảo hành trùng lặp ━━━');

        $allWarranties = Warranty::whereIn('item_type', ['project', 'rental'])->get();
        $grouped = [];

        foreach ($allWarranties as $w) {
            $ownerId = null;
            if ($w->dispatch) {
                $ownerId = $w->dispatch->project_id;
            } elseif ($w->item_id > 0) {
                $ownerId = $w->item_id;
            }

            if ($ownerId) {
                $key = "{$w->item_type}_{$ownerId}";
                $grouped[$key][] = $w;
            }
        }

        $duplicateIds = [];
        foreach ($grouped as $key => $warranties) {
            if (count($warranties) <= 1) continue;

            usort($warranties, function ($a, $b) {
                return $a->id <=> $b->id;
            });

            $keep = $warranties[0];
            for ($i = 1; $i < count($warranties); $i++) {
                $w = $warranties[$i];
                $duplicateIds[] = $w->id;
                $this->line("  ❌ Duplicate #{$w->warranty_code} (Trùng với #{$keep->warranty_code} của {$key})");
            }
        }

        $stats['duplicate_deleted'] = count($duplicateIds);
        if ($stats['duplicate_deleted'] > 0) {
            $this->warn("  " . ($isDryRun ? 'Phát hiện' : 'Đã xóa') . " {$stats['duplicate_deleted']} phiếu trùng lặp.");
            if (!$isDryRun) {
                DB::table('warranties')->whereIn('id', $duplicateIds)->delete();
            }
        } else {
            $this->info('  ✅ Không có phiếu trùng lặp.');
        }
        $this->newLine();

        // ==============================
        // TỔNG KẾT
        // ==============================
        $this->info('━━━ TỔNG KẾT ━━━');
        $this->line("  Phục hồi warranty bị ghi đè: {$stats['corrupted_restored']}");
        $this->line("  Sửa phiếu xuất sai loại: {$stats['dispatch_fixed']}");
        $this->line("  Phân loại item_type=rental: {$stats['type_migrated']}");
        $this->line("  Xóa phiếu bảo hành lỗi: {$stats['orphan_deleted']}");
        $this->line("  Chuẩn hóa tên Project: {$stats['project_standardized']}");
        $this->line("  Chuẩn hóa tên Rental: {$stats['rental_standardized']}");
        $this->line("  Xóa phiếu trùng lặp: {$stats['duplicate_deleted']}");

        $totalChanges = array_sum($stats);
        if ($isDryRun && $totalChanges > 0) {
            $this->newLine();
            $this->warn('💡 Chạy lại với --force để áp dụng: php artisan fix:rental-warranties --force');
        } elseif (!$isDryRun && $totalChanges > 0) {
            $this->newLine();
            $this->info('✅ Đã áp dụng tất cả thay đổi thành công!');
        }
    }
}
