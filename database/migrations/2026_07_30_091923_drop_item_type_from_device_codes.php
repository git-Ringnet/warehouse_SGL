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
        // Drop unique nếu tồn tại
        DB::statement("ALTER TABLE `device_codes` DROP INDEX IF EXISTS `device_codes_item_unique`");
        Schema::table('device_codes', function (Blueprint $table) {
            // drop column
            if (Schema::hasColumn('device_codes', 'item_type')) {
                $table->dropColumn('item_type');
            }
            if (Schema::hasColumn('device_codes', 'item_id')) {
                $table->dropColumn('item_id');
            }
            if (Schema::hasColumn('device_codes', 'old_serial')) {
                $table->dropColumn('old_serial');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_codes', function (Blueprint $table) {
            if (!Schema::hasColumn('device_codes', 'item_type')) {
                $table->string('item_type', 20)->nullable()->after('product_id');
            }
            if (!Schema::hasColumn('device_codes', 'item_id')) {
                $table->unsignedBigInteger('item_id')->nullable()->after('item_type');
            }
            if (!Schema::hasColumn('device_codes', 'old_serial')) {
                $table->string('old_serial')->nullable()->after('serial_main');
            }
    
            // Thêm lại unique
            $table->unique(['item_type', 'item_id', 'serial_main'], 'device_codes_item_unique');
        });
    }
};
