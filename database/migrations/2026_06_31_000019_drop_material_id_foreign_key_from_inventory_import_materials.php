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
            // Drop existing foreign key constraint for material_id
            $table->dropForeign(['material_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_import_materials', function (Blueprint $table) {
            // Re-add the foreign key constraint (rollback)
            $table->foreignId('material_id')->constrained('materials')->onDelete('cascade')->change();
        });
    }
};
