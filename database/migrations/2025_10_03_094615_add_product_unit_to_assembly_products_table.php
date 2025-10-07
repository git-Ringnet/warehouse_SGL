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
        Schema::table('assembly_products', function (Blueprint $table) {
            $table->text('product_unit')->nullable()->after('serials')->comment('Đơn vị thành phẩm trong assembly (0, 1, 2...)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assembly_products', function (Blueprint $table) {
            $table->dropColumn('product_unit');
        });
    }
};
