<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration to add indexes for faster device materials lookup in repairs
 * Performance optimization for repair page loading materials
 */
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add index on testing_items for fast lookup
        // Use raw SQL with prefix for long string columns
        if (!$this->hasIndex('testing_items', 'testing_items_serial_number_index')) {
            DB::statement('CREATE INDEX testing_items_serial_number_index ON testing_items (serial_number(191))');
        }
        if (!$this->hasIndex('testing_items', 'testing_items_type_serial_index')) {
            DB::statement('CREATE INDEX testing_items_type_serial_index ON testing_items (item_type, serial_number(100))');
        }

        // Index cho quan hệ testing_id + item_type (không cần prefix vì không phải string)
        Schema::table('testing_items', function (Blueprint $table) {
            if (!$this->hasIndex('testing_items', 'testing_items_testing_type_index')) {
                $table->index(['testing_id', 'item_type'], 'testing_items_testing_type_index');
            }
        });

        // Add index on assembly_materials for faster lookup
        Schema::table('assembly_materials', function (Blueprint $table) {
            if (!$this->hasIndex('assembly_materials', 'assembly_materials_lookup_index')) {
                $table->index(['assembly_id', 'target_product_id'], 'assembly_materials_lookup_index');
            }
            if (!$this->hasIndex('assembly_materials', 'assembly_materials_product_index')) {
                $table->index('target_product_id', 'assembly_materials_product_index');
            }
        });

        // Add index on assembly_products for faster serial search
        Schema::table('assembly_products', function (Blueprint $table) {
            if (!$this->hasIndex('assembly_products', 'assembly_products_product_index')) {
                $table->index(['product_id'], 'assembly_products_product_index');
            }
        });

        // Add index on material_replacement_histories for faster history lookup
        if (Schema::hasTable('material_replacement_histories')) {
            // Use raw SQL with prefix for device_code and material_code
            if (!$this->hasIndex('material_replacement_histories', 'material_replacement_device_material_index')) {
                DB::statement('CREATE INDEX material_replacement_device_material_index ON material_replacement_histories (device_code(100), material_code(100))');
            }

            Schema::table('material_replacement_histories', function (Blueprint $table) {
                if (!$this->hasIndex('material_replacement_histories', 'material_replacement_date_index')) {
                    $table->index('replaced_at', 'material_replacement_date_index');
                }
            });
        }
    }

    /**
     * Check if index exists
     */
    private function hasIndex($table, $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Use raw SQL to drop indexes safely
        if ($this->hasIndex('testing_items', 'testing_items_serial_number_index')) {
            DB::statement('DROP INDEX testing_items_serial_number_index ON testing_items');
        }
        if ($this->hasIndex('testing_items', 'testing_items_type_serial_index')) {
            DB::statement('DROP INDEX testing_items_type_serial_index ON testing_items');
        }

        Schema::table('testing_items', function (Blueprint $table) {
            if ($this->hasIndex('testing_items', 'testing_items_testing_type_index')) {
                $table->dropIndex('testing_items_testing_type_index');
            }
        });

        Schema::table('assembly_materials', function (Blueprint $table) {
            if ($this->hasIndex('assembly_materials', 'assembly_materials_lookup_index')) {
                $table->dropIndex('assembly_materials_lookup_index');
            }
            if ($this->hasIndex('assembly_materials', 'assembly_materials_product_index')) {
                $table->dropIndex('assembly_materials_product_index');
            }
        });

        Schema::table('assembly_products', function (Blueprint $table) {
            if ($this->hasIndex('assembly_products', 'assembly_products_product_index')) {
                $table->dropIndex('assembly_products_product_index');
            }
        });

        if (Schema::hasTable('material_replacement_histories')) {
            if ($this->hasIndex('material_replacement_histories', 'material_replacement_device_material_index')) {
                DB::statement('DROP INDEX material_replacement_device_material_index ON material_replacement_histories');
            }

            Schema::table('material_replacement_histories', function (Blueprint $table) {
                if ($this->hasIndex('material_replacement_histories', 'material_replacement_date_index')) {
                    $table->dropIndex('material_replacement_date_index');
                }
            });
        }
    }
};

