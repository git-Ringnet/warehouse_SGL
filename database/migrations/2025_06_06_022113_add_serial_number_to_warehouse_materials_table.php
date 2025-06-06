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
        Schema::table('warehouse_materials', function (Blueprint $table) {
            $table->string('serial_number')->nullable()->after('quantity');
            $table->string('location')->nullable()->after('serial_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_materials', function (Blueprint $table) {
            $table->dropColumn('serial_number');
            $table->dropColumn('location');
        });
    }
};
