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
        Schema::create('repair_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('repair_id')->comment('ID phiếu sửa chữa');
            $table->string('device_code')->comment('Mã thiết bị');
            $table->string('device_name')->comment('Tên thiết bị');
            $table->string('device_serial')->nullable()->comment('Serial thiết bị');
            $table->enum('device_status', ['selected', 'rejected'])->default('selected')->comment('Trạng thái thiết bị');
            $table->text('device_notes')->nullable()->comment('Ghi chú thiết bị');
            $table->json('device_images')->nullable()->comment('Hình ảnh thiết bị');
            $table->json('device_parts')->nullable()->comment('Vật tư thiết bị');
            $table->text('reject_reason')->nullable()->comment('Lý do từ chối');
            $table->unsignedBigInteger('reject_warehouse_id')->nullable()->comment('Kho lưu trữ thiết bị từ chối');
            $table->timestamps();

            $table->foreign('repair_id')->references('id')->on('repairs')->onDelete('cascade');
            
            $table->index('repair_id');
            $table->index('device_code');
            $table->index('device_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_items');
    }
};
