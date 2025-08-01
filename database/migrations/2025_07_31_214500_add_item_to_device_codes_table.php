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
            $table->string('item_type', 20)->nullable()->after('product_id');
            $table->unsignedBigInteger('item_id')->nullable()->after('item_type');
            $table->string('old_serial')->nullable()->after('serial_main');
            $table->unique(['item_type', 'item_id', 'serial_main'], 'device_codes_item_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_codes', function (Blueprint $table) {
            $table->dropUnique('device_codes_item_unique');
            $table->dropColumn(['item_type', 'item_id', 'old_serial']);
        });
    }
};
