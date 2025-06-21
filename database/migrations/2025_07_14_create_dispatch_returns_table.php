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
        Schema::create('dispatch_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_code')->unique(); // Mã phiếu nhập trả
            $table->foreignId('dispatch_item_id')->constrained('dispatch_items')->onDelete('cascade'); // Liên kết với item trong phiếu xuất
            $table->foreignId('warehouse_id')->constrained('warehouses'); // Kho nhận
            $table->foreignId('user_id')->constrained('users'); // Người thực hiện
            $table->dateTime('return_date'); // Ngày trả
            $table->enum('reason_type', ['warranty', 'return', 'replacement']); // Lý do: bảo hành, trả về, thay thế
            $table->text('reason')->nullable(); // Lý do chi tiết
            $table->enum('condition', ['good', 'damaged', 'broken'])->default('good'); // Tình trạng: tốt, hư hỏng nhẹ, hư hỏng nặng
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed'); // Trạng thái phiếu
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispatch_returns');
    }
}; 