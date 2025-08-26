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
        Schema::table('device_codes', function (Blueprint $table) {
            // drop unique trước
            $table->dropUnique('device_codes_item_unique');

            // drop column
            $table->dropColumn('item_type');
            $table->dropColumn('item_id');
            $table->dropColumn('old_serial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_codes', function (Blueprint $table) {
            $table->string('item_type', 20)->nullable()->after('product_id');
            $table->unique(['item_type', 'item_id', 'serial_main'], 'device_codes_item_unique');
        });
    }
};
