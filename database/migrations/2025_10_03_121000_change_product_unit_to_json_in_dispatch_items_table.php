<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dispatch_items', function (Blueprint $table) {
            // Drop composite index that includes product_unit before changing to JSON
            if (Schema::hasColumn('dispatch_items', 'product_unit')) {
                $table->dropIndex('dispatch_items_assembly_id_product_unit_index');
            }
        });

        Schema::table('dispatch_items', function (Blueprint $table) {
            // Change product_unit from integer to json to support arrays
            $table->json('product_unit')->nullable()->change();
        });

        Schema::table('assembly_products', function (Blueprint $table) {
            $table->longText('serials')->nullable()->change();
        });

        Schema::table('dispatch_items', function (Blueprint $table) {
            $table->text('assembly_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Bỏ index cũ nếu còn
        try {
            DB::statement('DROP INDEX IF EXISTS dispatch_items_assembly_id_product_unit_index ON dispatch_items');
        } catch (\Exception $e) {
        }

        // Đổi lại kiểu dữ liệu
        Schema::table('dispatch_items', function (Blueprint $table) {
            $table->unsignedBigInteger('assembly_id')->nullable()->change();
            $table->integer('product_unit')->nullable()->change();
        });

        // Tạo lại index thủ công
        DB::statement('CREATE INDEX dispatch_items_assembly_id_product_unit_index ON dispatch_items (assembly_id, product_unit)');

        Schema::table('assembly_products', function (Blueprint $table) {
            $table->longText('serials')->nullable()->change();
        });
    }
};
