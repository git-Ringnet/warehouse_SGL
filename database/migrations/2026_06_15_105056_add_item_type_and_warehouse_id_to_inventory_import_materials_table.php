<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventory_import_materials', function (Blueprint $table) {
            // Thêm trường warehouse_id nếu chưa tồn tại
            if (!Schema::hasColumn('inventory_import_materials', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->constrained()->after('material_id');
            }
            
            // Thêm trường item_type nếu chưa tồn tại
            if (!Schema::hasColumn('inventory_import_materials', 'item_type')) {
                $table->string('item_type')->default('material')->after('material_id');
            }
            
            // Thêm trường serial_numbers nếu chưa tồn tại
            if (!Schema::hasColumn('inventory_import_materials', 'serial_numbers')) {
                $table->json('serial_numbers')->nullable()->after('serial');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_import_materials', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_import_materials', 'warehouse_id')) {
                $table->dropForeign(['warehouse_id']);
                $table->dropColumn('warehouse_id');
            }
            
            if (Schema::hasColumn('inventory_import_materials', 'item_type')) {
                $table->dropColumn('item_type');
            }
            
            if (Schema::hasColumn('inventory_import_materials', 'serial_numbers')) {
                $table->dropColumn('serial_numbers');
            }
        });
    }
};
