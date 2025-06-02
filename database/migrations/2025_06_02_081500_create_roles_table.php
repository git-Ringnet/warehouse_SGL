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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Tên nhóm quyền
            $table->string('description')->nullable(); // Mô tả nhóm quyền
            $table->string('scope')->nullable(); // Phạm vi (theo kho, theo dự án...)
            $table->boolean('is_active')->default(true); // Trạng thái kích hoạt
            $table->boolean('is_system')->default(false); // Quyền hệ thống không thể xóa
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
