<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Thêm indexes để tối ưu performance cho getDeviceMaterials
     */
    public function up(): void
    {
        // Helper function để kiểm tra index tồn tại
        $indexExists = function ($table, $indexName) {
            $result = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
            return count($result) > 0;
        };

        // Index cho testing_items
        if (!$indexExists('testing_items', 'idx_testing_items_type_testing')) {
            Schema::table('testing_items', function (Blueprint $table) {
                $table->index(['item_type', 'testing_id'], 'idx_testing_items_type_testing');
            });
        }
        
        if (!$indexExists('testing_items', 'idx_testing_items_serial')) {
            DB::statement('ALTER TABLE testing_items ADD INDEX idx_testing_items_serial (serial_number(191))');
        }

        // Index cho assembly_products
        if (!$indexExists('assembly_products', 'idx_assembly_products_product_assembly')) {
            Schema::table('assembly_products', function (Blueprint $table) {
                $table->index(['product_id', 'assembly_id'], 'idx_assembly_products_product_assembly');
            });
        }

        // Index cho assembly_materials
        if (!$indexExists('assembly_materials', 'idx_assembly_materials_assembly_target')) {
            Schema::table('assembly_materials', function (Blueprint $table) {
                $table->index(['assembly_id', 'target_product_id'], 'idx_assembly_materials_assembly_target');
            });
        }
        
        if (!$indexExists('assembly_materials', 'idx_assembly_materials_assembly_unit')) {
            Schema::table('assembly_materials', function (Blueprint $table) {
                $table->index(['assembly_id', 'product_unit'], 'idx_assembly_materials_assembly_unit');
            });
        }

        // Index cho dispatch_items - chỉ tạo index đơn giản để tránh lỗi key too long
        if (!$indexExists('dispatch_items', 'idx_dispatch_items_dispatch_item')) {
            Schema::table('dispatch_items', function (Blueprint $table) {
                $table->index(['dispatch_id', 'item_id'], 'idx_dispatch_items_dispatch_item');
            });
        }
        
        if (!$indexExists('dispatch_items', 'idx_dispatch_items_assembly')) {
            DB::statement('ALTER TABLE dispatch_items ADD INDEX idx_dispatch_items_assembly (assembly_id(191))');
        }

        // Index cho material_replacement_history
        if (!$indexExists('material_replacement_history', 'idx_material_replacement_device_material_date')) {
            Schema::table('material_replacement_history', function (Blueprint $table) {
                $table->index(['device_code', 'material_code', 'replaced_at'], 'idx_material_replacement_device_material_date');
            });
        }

        // Index cho serials
        if (!$indexExists('serials', 'idx_serials_number_type_product')) {
            Schema::table('serials', function (Blueprint $table) {
                $table->index(['serial_number', 'type', 'product_id'], 'idx_serials_number_type_product');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexExists = function ($table, $indexName) {
            $result = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
            return count($result) > 0;
        };

        if ($indexExists('testing_items', 'idx_testing_items_type_testing')) {
            Schema::table('testing_items', function (Blueprint $table) {
                $table->dropIndex('idx_testing_items_type_testing');
            });
        }
        
        if ($indexExists('testing_items', 'idx_testing_items_serial')) {
            DB::statement('ALTER TABLE testing_items DROP INDEX idx_testing_items_serial');
        }

        if ($indexExists('assembly_products', 'idx_assembly_products_product_assembly')) {
            Schema::table('assembly_products', function (Blueprint $table) {
                $table->dropIndex('idx_assembly_products_product_assembly');
            });
        }

        if ($indexExists('assembly_materials', 'idx_assembly_materials_assembly_target')) {
            Schema::table('assembly_materials', function (Blueprint $table) {
                $table->dropIndex('idx_assembly_materials_assembly_target');
            });
        }
        
        if ($indexExists('assembly_materials', 'idx_assembly_materials_assembly_unit')) {
            Schema::table('assembly_materials', function (Blueprint $table) {
                $table->dropIndex('idx_assembly_materials_assembly_unit');
            });
        }

        if ($indexExists('dispatch_items', 'idx_dispatch_items_dispatch_item')) {
            Schema::table('dispatch_items', function (Blueprint $table) {
                $table->dropIndex('idx_dispatch_items_dispatch_item');
            });
        }
        
        if ($indexExists('dispatch_items', 'idx_dispatch_items_assembly')) {
            DB::statement('ALTER TABLE dispatch_items DROP INDEX idx_dispatch_items_assembly');
        }

        if ($indexExists('material_replacement_history', 'idx_material_replacement_device_material_date')) {
            Schema::table('material_replacement_history', function (Blueprint $table) {
                $table->dropIndex('idx_material_replacement_device_material_date');
            });
        }

        if ($indexExists('serials', 'idx_serials_number_type_product')) {
            Schema::table('serials', function (Blueprint $table) {
                $table->dropIndex('idx_serials_number_type_product');
            });
        }
    }
};
