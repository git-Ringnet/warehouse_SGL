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
        Schema::table('customer_maintenance_requests', function (Blueprint $table) {
            // Xóa khóa ngoại cũ
            $table->dropForeign(['approved_by']);
            
            // Thêm khóa ngoại mới trỏ đến bảng employees
            $table->foreign('approved_by')
                  ->references('id')
                  ->on('employees')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_maintenance_requests', function (Blueprint $table) {
            // Xóa khóa ngoại mới
            $table->dropForeign(['approved_by']);
            
            // Thêm lại khóa ngoại cũ trỏ đến bảng users
            $table->foreign('approved_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }
};
