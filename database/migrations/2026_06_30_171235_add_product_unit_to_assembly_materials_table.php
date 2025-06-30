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
            $table->integer('product_unit')->default(0)->after('target_product_id')
                ->comment('Tracks which unit of a product this material belongs to when multiple product units exist');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assembly_materials', function (Blueprint $table) {
            $table->dropColumn('product_unit');
        });
    }
}; 