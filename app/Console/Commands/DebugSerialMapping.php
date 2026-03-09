<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugSerialMapping extends Command
{
    protected $signature = 'debug:serial-mapping {item_code}';
    protected $description = 'Debug serial mapping for an item';

    public function handle()
    {
        $itemCode = $this->argument('item_code');
        
        $this->info("=== DEBUG SERIAL MAPPING FOR {$itemCode} ===");
        $this->newLine();

        // 1. Tìm item
        $good = DB::table('goods')->where('code', $itemCode)->first();
        if (!$good) {
            $this->error("Không tìm thấy hàng hóa với mã: {$itemCode}");
            return 1;
        }

        $this->info("✅ Tìm thấy hàng hóa:");
        $this->line("   ID: {$good->id}");
        $this->line("   Tên: {$good->name}");
        $this->newLine();

        // 2. Kiểm tra warehouse_materials
        $this->info("📦 Kiểm tra warehouse_materials:");
        $wms = DB::table('warehouse_materials')
            ->where('item_type', 'good')
            ->where('material_id', $good->id)
            ->get();

        if ($wms->isEmpty()) {
            $this->warn("   Không có bản ghi nào trong warehouse_materials");
        } else {
            foreach ($wms as $wm) {
                $warehouse = DB::table('warehouses')->find($wm->warehouse_id);
                $this->line("   Kho: " . ($warehouse ? $warehouse->name : "ID {$wm->warehouse_id}"));
                $this->line("   Số lượng: {$wm->quantity}");
                $this->line("   Serial: {$wm->serial_number}");
                
                // Parse serial
                if (!empty($wm->serial_number)) {
                    $serials = json_decode($wm->serial_number, true);
                    if (is_array($serials)) {
                        $this->line("   Serial parsed: " . implode(', ', $serials));
                    }
                }
                $this->newLine();
            }
        }

        // 3. Kiểm tra device_codes
        $this->info("🔧 Kiểm tra device_codes:");
        $deviceCodes = DB::table('device_codes')
            ->where('item_type', 'good')
            ->where('item_id', $good->id)
            ->get();

        if ($deviceCodes->isEmpty()) {
            $this->warn("   Không có bản ghi nào trong device_codes");
        } else {
            $this->line("   Tìm thấy {$deviceCodes->count()} device_codes:");
            foreach ($deviceCodes as $dc) {
                $dispatch = DB::table('dispatches')->find($dc->dispatch_id);
                $this->line("   ---");
                $this->line("   ID: {$dc->id}");
                $this->line("   Dispatch: " . ($dispatch ? $dispatch->dispatch_code : "ID {$dc->dispatch_id}"));
                $this->line("   Serial Main: {$dc->serial_main}");
                $this->line("   Old Serial: {$dc->old_serial}");
                $this->line("   Type: {$dc->type}");
            }
        }
        $this->newLine();

        // 4. Kiểm tra dispatch_items
        $this->info("📋 Kiểm tra dispatch_items:");
        $dispatchItems = DB::table('dispatch_items')
            ->where('item_type', 'good')
            ->where('item_id', $good->id)
            ->get();

        if ($dispatchItems->isEmpty()) {
            $this->warn("   Không có bản ghi nào trong dispatch_items");
        } else {
            $this->line("   Tìm thấy {$dispatchItems->count()} dispatch_items:");
            foreach ($dispatchItems as $di) {
                $dispatch = DB::table('dispatches')->find($di->dispatch_id);
                $warehouse = DB::table('warehouses')->find($di->warehouse_id);
                $this->line("   ---");
                $this->line("   ID: {$di->id}");
                $this->line("   Dispatch: " . ($dispatch ? "{$dispatch->dispatch_code} ({$dispatch->status})" : "ID {$di->dispatch_id}"));
                $this->line("   Kho: " . ($warehouse ? $warehouse->name : "ID {$di->warehouse_id}"));
                $this->line("   Số lượng: {$di->quantity}");
                $this->line("   Category: {$di->category}");
                $this->line("   Serial numbers: {$di->serial_numbers}");
                
                // Parse serial
                if (!empty($di->serial_numbers)) {
                    $serials = json_decode($di->serial_numbers, true);
                    if (is_array($serials)) {
                        $this->line("   Serial parsed: " . implode(', ', $serials));
                    }
                }
            }
        }

        return 0;
    }
}
