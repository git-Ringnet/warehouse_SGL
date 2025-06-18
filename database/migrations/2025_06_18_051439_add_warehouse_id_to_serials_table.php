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
        Schema::table('serials', function (Blueprint $table) {
            // Check if warehouse_id column doesn't exist before adding
            if (!Schema::hasColumn('serials', 'warehouse_id')) {
                $table->integer('warehouse_id')->default(0)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('serials', function (Blueprint $table) {
            if (Schema::hasColumn('serials', 'warehouse_id')) {
                $table->dropColumn('warehouse_id');
            }
        });
    }
};
