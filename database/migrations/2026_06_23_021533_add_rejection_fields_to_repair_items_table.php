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
            $table->integer('device_quantity')->default(1)->after('device_serial');
            $table->text('rejected_reason')->nullable()->after('device_notes');
            $table->unsignedBigInteger('rejected_warehouse_id')->nullable()->after('rejected_reason');
            $table->timestamp('rejected_at')->nullable()->after('rejected_warehouse_id');
            
            $table->foreign('rejected_warehouse_id')->references('id')->on('warehouses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repair_items', function (Blueprint $table) {
            $table->dropForeign(['rejected_warehouse_id']);
            $table->dropColumn(['device_quantity', 'rejected_reason', 'rejected_warehouse_id', 'rejected_at']);
        });
    }
};
