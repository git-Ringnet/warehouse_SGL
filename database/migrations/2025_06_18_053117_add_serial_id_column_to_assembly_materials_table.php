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
            // Check if serial_id column doesn't exist before adding
            if (!Schema::hasColumn('assembly_materials', 'serial_id')) {
                $table->foreignId('serial_id')->nullable()->constrained('serials')->onDelete('set null')->after('serial');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assembly_materials', function (Blueprint $table) {
            if (Schema::hasColumn('assembly_materials', 'serial_id')) {
                $table->dropForeign(['serial_id']);
                $table->dropColumn('serial_id');
            }
        });
    }
};
