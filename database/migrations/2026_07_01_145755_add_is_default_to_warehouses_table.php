<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('is_hidden');
        });

        // Đặt warehouse đầu tiên là mặc định
        $firstWarehouse = DB::table('warehouses')->where('status', 'active')->first();
        if ($firstWarehouse) {
            DB::table('warehouses')->where('id', $firstWarehouse->id)->update(['is_default' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
};
