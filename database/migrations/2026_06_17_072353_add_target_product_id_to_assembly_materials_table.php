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
            $table->unsignedBigInteger('target_product_id')->nullable()->after('material_id');
            $table->foreign('target_product_id')->references('id')->on('products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assembly_materials', function (Blueprint $table) {
            $table->dropForeign(['target_product_id']);
            $table->dropColumn('target_product_id');
        });
    }
};
