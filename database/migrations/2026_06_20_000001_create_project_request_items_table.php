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
        Schema::create('project_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_request_id')->constrained()->onDelete('cascade');
            $table->enum('item_type', ['equipment', 'material', 'good']); // Phân loại: thiết bị, vật tư hoặc hàng hóa
            $table->foreignId('item_id')->nullable(); // ID của thiết bị/vật tư/hàng hóa
            $table->string('name'); // Tên thiết bị/vật tư/hàng hóa
            $table->string('code')->nullable(); // Mã thiết bị/vật tư/hàng hóa
            $table->string('unit')->nullable(); // Đơn vị tính
            $table->text('description')->nullable(); // Mô tả
            $table->integer('quantity'); // Số lượng
            $table->text('notes')->nullable(); // Ghi chú
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_request_items');
    }
}; 