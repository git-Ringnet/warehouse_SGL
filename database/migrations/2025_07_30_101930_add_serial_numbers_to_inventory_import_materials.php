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
            $table->json('serial_numbers')->nullable()->after('serial')->comment('Danh sách các số serial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_import_materials', function (Blueprint $table) {
            $table->dropColumn('serial_numbers');
        });
    }
};
