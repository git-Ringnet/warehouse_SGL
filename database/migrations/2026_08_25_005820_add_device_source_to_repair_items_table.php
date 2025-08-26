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
            $table->string('device_source')->default('warranty')->after('device_type')->comment('Nguồn thiết bị: warranty, warehouse, project_backup, rental_backup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repair_items', function (Blueprint $table) {
            $table->dropColumn('device_source');
        });
    }
};
