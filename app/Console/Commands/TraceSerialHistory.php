<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TraceSerialHistory extends Command
{
    protected $signature = 'trace:serial {serial}';
    protected $description = 'Trace the history of a serial number';

    public function handle()
    {
        $serial = $this->argument('serial');
        
        $this->info("=== LỊCH SỬ SERIAL: {$serial} ===");
        $this->newLine();

        // 1. Tìm trong device_codes
        $this->info("🔧 Device Codes:");
        $deviceCodes = DB::table('device_codes')
            ->where(function($q) use ($serial) {
                $q->where('serial_main', $serial)
                  ->orWhere('old_serial', $serial);
            })
            ->get();

        if ($deviceCodes->isEmpty()) {
            $this->warn("   Không tìm thấy trong device_codes");
        } else {
            foreach ($deviceCodes as $dc) {
                $dispatch = DB::table('dispatches')->find($dc->dispatch_id);
                $item = null;
                if ($dc->item_type === 'good') {
                    $item = DB::table('goods')->find($dc->item_id);
                } elseif ($dc->item_type === 'product') {
                    $item = DB::table('products')->find($dc->item_id);
                } elseif ($dc->item_type === 'material') {
                    $item = DB::table('materials')->find($dc->item_id);
                }
                
                $this->line("   ---");
                $this->line("   Dispatch: " . ($dispatch ? "{$dispatch->dispatch_code} ({$dispatch->status})" : "N/A"));
                $this->line("   Item: " . ($item ? "{$item->code} - {$item->name}" : "N/A"));
                $this->line("   Serial Main: {$dc->serial_main}");
                $this->line("   Old Serial: {$dc->old_serial}");
                $this->line("   Type: {$dc->type}");
                $this->line("   Created: {$dc->created_at}");
            }
        }
        $this->newLine();

        // 2. Tìm trong dispatch_items
        $this->info("📋 Dispatch Items:");
        $dispatchItems = DB::table('dispatch_items')->get();
        $found = false;
        
        foreach ($dispatchItems as $di) {
            $serials = json_decode($di->serial_numbers, true);
            if (is_array($serials) && in_array($serial, $serials)) {
                $found = true;
                $dispatch = DB::table('dispatches')->find($di->dispatch_id);
                $warehouse = DB::table('warehouses')->find($di->warehouse_id);
                $item = null;
                if ($di->item_type === 'good') {
                    $item = DB::table('goods')->find($di->item_id);
                } elseif ($di->item_type === 'product') {
                    $item = DB::table('products')->find($di->item_id);
                } elseif ($di->item_type === 'material') {
                    $item = DB::table('materials')->find($di->item_id);
                }
                
                $this->line("   ---");
                $this->line("   Dispatch: " . ($dispatch ? "{$dispatch->dispatch_code} ({$dispatch->status})" : "N/A"));
                if ($dispatch && $dispatch->dispatch_type === 'project') {
                    $project = DB::table('projects')->find($dispatch->project_id);
                    $this->line("   Project: " . ($project ? "{$project->project_code} - {$project->project_name}" : "N/A"));
                }
                $this->line("   Item: " . ($item ? "{$item->code} - {$item->name}" : "N/A"));
                $this->line("   Kho xuất: " . ($warehouse ? $warehouse->name : "N/A"));
                $this->line("   Category: {$di->category}");
                $this->line("   Quantity: {$di->quantity}");
                $this->line("   Created: {$di->created_at}");
            }
        }
        
        if (!$found) {
            $this->warn("   Không tìm thấy trong dispatch_items");
        }
        $this->newLine();

        // 3. Tìm trong dispatch_returns
        $this->info("🔙 Dispatch Returns (Thu hồi):");
        $returns = DB::table('dispatch_returns')
            ->where('serial_number', $serial)
            ->get();

        if ($returns->isEmpty()) {
            $this->warn("   Không tìm thấy lịch sử thu hồi");
        } else {
            foreach ($returns as $return) {
                $dispatchItem = DB::table('dispatch_items')->find($return->dispatch_item_id);
                $warehouse = DB::table('warehouses')->find($return->warehouse_id);
                $dispatch = $dispatchItem ? DB::table('dispatches')->find($dispatchItem->dispatch_id) : null;
                
                $this->line("   ---");
                $this->line("   Return Code: {$return->return_code}");
                $this->line("   Dispatch: " . ($dispatch ? $dispatch->dispatch_code : "N/A"));
                $this->line("   Kho thu hồi: " . ($warehouse ? $warehouse->name : "N/A"));
                $this->line("   Reason: {$return->reason}");
                $this->line("   Condition: {$return->condition}");
                $this->line("   Return Date: {$return->return_date}");
                $this->line("   Status: {$return->status}");
            }
        }
        $this->newLine();

        // 4. Tìm trong warehouse_materials
        $this->info("📦 Warehouse Materials (Tồn kho hiện tại):");
        $wms = DB::table('warehouse_materials')->get();
        $found = false;
        
        foreach ($wms as $wm) {
            if (!empty($wm->serial_number)) {
                $serials = json_decode($wm->serial_number, true);
                if (is_array($serials) && in_array($serial, $serials)) {
                    $found = true;
                    $warehouse = DB::table('warehouses')->find($wm->warehouse_id);
                    $item = null;
                    if ($wm->item_type === 'good') {
                        $item = DB::table('goods')->find($wm->material_id);
                    } elseif ($wm->item_type === 'product') {
                        $item = DB::table('products')->find($wm->material_id);
                    } elseif ($wm->item_type === 'material') {
                        $item = DB::table('materials')->find($wm->material_id);
                    }
                    
                    $this->line("   ---");
                    $this->line("   Kho: " . ($warehouse ? $warehouse->name : "N/A"));
                    $this->line("   Item: " . ($item ? "{$item->code} - {$item->name}" : "N/A"));
                    $this->line("   Quantity: {$wm->quantity}");
                    $this->line("   Updated: {$wm->updated_at}");
                }
            }
        }
        
        if (!$found) {
            $this->warn("   Serial không có trong kho nào");
        }
        $this->newLine();

        // 5. Tìm trong change_logs
        $this->info("📝 Change Logs:");
        $logs = DB::table('change_logs')
            ->where('detailed_info', 'like', "%{$serial}%")
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if ($logs->isEmpty()) {
            $this->warn("   Không tìm thấy trong change_logs");
        } else {
            foreach ($logs as $log) {
                $this->line("   ---");
                $this->line("   Action: {$log->action_type}");
                $this->line("   Description: {$log->description}");
                $this->line("   Date: {$log->created_at}");
                
                $detailedInfo = json_decode($log->detailed_info, true);
                if (is_array($detailedInfo)) {
                    if (isset($detailedInfo['warehouse_name'])) {
                        $this->line("   Warehouse: {$detailedInfo['warehouse_name']}");
                    }
                    if (isset($detailedInfo['dispatch_code'])) {
                        $this->line("   Dispatch: {$detailedInfo['dispatch_code']}");
                    }
                }
            }
        }

        return 0;
    }
}
