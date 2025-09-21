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
        Schema::table('dispatch_items', function (Blueprint $table) {
            $table->unsignedBigInteger('assembly_id')->nullable()->after('serial_numbers');
            $table->integer('product_unit')->nullable()->after('assembly_id');
            $table->index(['assembly_id', 'product_unit']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispatch_items', function (Blueprint $table) {
            $table->dropIndex(['assembly_id', 'product_unit']);
            $table->dropColumn(['assembly_id', 'product_unit']);
        });
    }
};
