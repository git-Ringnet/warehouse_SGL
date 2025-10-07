<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AssemblyProduct;
use Illuminate\Support\Facades\DB;

class UpdateAssemblyProductsProductUnit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assembly:update-product-units';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật product_unit cho các assembly_products hiện có';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Bắt đầu cập nhật product_unit cho assembly_products...');

        // Lấy tất cả assembly_products chưa có product_unit
        $assemblyProducts = AssemblyProduct::whereNull('product_unit')
            ->orderBy('assembly_id')
            ->orderBy('id')
            ->get();

        $this->info("Tìm thấy {$assemblyProducts->count()} assembly_products cần cập nhật");

        $updatedCount = 0;
        $currentAssemblyId = null;
        $unitCounter = 0;

        foreach ($assemblyProducts as $assemblyProduct) {
            // Reset counter khi chuyển sang assembly mới
            if ($currentAssemblyId !== $assemblyProduct->assembly_id) {
                $currentAssemblyId = $assemblyProduct->assembly_id;
                $unitCounter = 0;
            }

            // Gán product_unit theo thứ tự (0, 1, 2...)
            $assemblyProduct->product_unit = $unitCounter;
            $assemblyProduct->save();

            $this->line("Assembly ID: {$assemblyProduct->assembly_id}, Product ID: {$assemblyProduct->product_id}, Product Unit: {$unitCounter}");
            
            $unitCounter++;
            $updatedCount++;
        }

        $this->info("Hoàn thành! Đã cập nhật {$updatedCount} assembly_products");
        
        return Command::SUCCESS;
    }
}
