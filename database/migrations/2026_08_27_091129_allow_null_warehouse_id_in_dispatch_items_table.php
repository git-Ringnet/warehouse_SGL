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
        Schema::table('dispatch_items', function (Blueprint $table) {
            // Xóa foreign key constraint cũ
            $table->dropForeign(['warehouse_id']);
            
            // Thay đổi warehouse_id thành nullable
            $table->unsignedBigInteger('warehouse_id')->nullable()->change();
            
            // Thêm lại foreign key constraint với nullable
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispatch_items', function (Blueprint $table) {
            // Xóa foreign key constraint mới
            $table->dropForeign(['warehouse_id']);
            
            // Khôi phục warehouse_id thành not nullable
            $table->unsignedBigInteger('warehouse_id')->nullable(false)->change();
            
            // Thêm lại foreign key constraint cũ
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
        });
    }
};
