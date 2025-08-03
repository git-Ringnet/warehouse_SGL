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
        Schema::table('dispatches', function (Blueprint $table) {
            // Xóa foreign key constraint cũ
            $table->dropForeign(['created_by']);
            
            // Thêm foreign key constraint mới tham chiếu đến bảng employees
            $table->foreign('created_by')->references('id')->on('employees');

            // Xóa foreign key constraint cũ
            $table->dropForeign(['approved_by']);
            
            // Thêm foreign key constraint mới tham chiếu đến bảng employees
            $table->foreign('approved_by')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispatches', function (Blueprint $table) {
            // Xóa foreign key constraint mới
            $table->dropForeign(['created_by']);
            
            // Khôi phục foreign key constraint cũ tham chiếu đến bảng users
            $table->foreign('created_by')->references('id')->on('users');
        });
    }
};
