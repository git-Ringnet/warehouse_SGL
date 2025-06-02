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
        Schema::create('user_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // ID của nhân viên/người dùng
            $table->string('action'); // Hành động thực hiện (đăng nhập, thêm, sửa, xóa...)
            $table->string('module'); // Module/phần thực hiện hành động (kho, nhân viên, dự án...)
            $table->text('description')->nullable(); // Mô tả chi tiết hành động
            $table->text('old_data')->nullable(); // Dữ liệu cũ (nếu có)
            $table->text('new_data')->nullable(); // Dữ liệu mới (nếu có)
            $table->string('ip_address')->nullable(); // Địa chỉ IP
            $table->string('user_agent')->nullable(); // Trình duyệt/thiết bị
            $table->timestamps();

            // Foreign key
            $table->foreign('user_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_logs');
    }
};
