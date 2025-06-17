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
        Schema::create('repairs', function (Blueprint $table) {
            $table->id();
            $table->string('repair_code')->unique()->comment('Mã phiếu sửa chữa');
            $table->string('warranty_code')->nullable()->comment('Mã bảo hành');
            $table->unsignedBigInteger('warranty_id')->nullable()->comment('ID bảo hành');
            $table->enum('repair_type', ['maintenance', 'repair', 'replacement', 'upgrade', 'other'])->comment('Loại sửa chữa');
            $table->date('repair_date')->comment('Ngày sửa chữa');
            $table->unsignedBigInteger('technician_id')->comment('Kỹ thuật viên');
            $table->unsignedBigInteger('warehouse_id')->comment('Kho linh kiện');
            $table->text('repair_description')->comment('Mô tả sửa chữa');
            $table->text('repair_notes')->nullable()->comment('Ghi chú');
            $table->json('repair_photos')->nullable()->comment('Hình ảnh sửa chữa');
            $table->enum('status', ['in_progress', 'completed'])->default('in_progress')->comment('Trạng thái');
            $table->unsignedBigInteger('created_by')->comment('Người tạo');
            $table->timestamps();

            $table->foreign('warranty_id')->references('id')->on('warranties')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('technician_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->index('repair_code');
            $table->index('warranty_code');
            $table->index('repair_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repairs');
    }
};
