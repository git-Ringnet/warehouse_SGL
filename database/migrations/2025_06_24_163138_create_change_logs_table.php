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
        Schema::create('change_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('time_changed'); // Thời gian
            $table->string('item_code'); // Mã Vật tư/Thành Phẩm/Hàng Hóa
            $table->string('item_name'); // Tên Vật tư/Thành Phẩm/Hàng Hóa
            $table->enum('change_type', ['lap_rap', 'xuat_kho', 'sua_chua', 'thu_hoi', 'nhap_kho', 'chuyen_kho']); // Loại hình
            $table->string('document_code')->nullable(); // Mã phiếu
            $table->integer('quantity')->default(0); // Số lượng
            $table->text('description')->nullable(); // Mô tả
            $table->string('performed_by'); // Người thực hiện
            $table->text('notes')->nullable(); // Chú thích
            $table->json('detailed_info')->nullable(); // Thông tin chi tiết (JSON format)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_logs');
    }
};
