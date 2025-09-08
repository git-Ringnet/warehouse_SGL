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
        Schema::table('assembly_materials', function (Blueprint $table) {
            // Check if warehouse_id column doesn't exist before adding
            if (!Schema::hasColumn('assembly_materials', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null')->after('material_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assembly_materials', function (Blueprint $table) {
            if (Schema::hasColumn('assembly_materials', 'warehouse_id')) {
                $table->dropForeign(['warehouse_id']);
                $table->dropColumn('warehouse_id');
            }
        });
    }
};
