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
        Schema::table('repair_items', function (Blueprint $table) {
            $table->string('device_type')->nullable()->after('device_images')->comment('Type of device (product/good)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repair_items', function (Blueprint $table) {
            $table->dropColumn('device_type');
        });
    }
};
