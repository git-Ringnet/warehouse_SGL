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
        Schema::table('assembly_materials', function (Blueprint $table) {
            // Xóa khóa ngoại nếu tồn tại
            if (Schema::hasColumn('assembly_materials', 'serial_id')) {
                $table->dropForeign(['serial_id']);
            }
            
            // Đổi kiểu dữ liệu của serial_id thành varchar với độ dài đủ lớn
            $table->string('serial_id', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assembly_materials', function (Blueprint $table) {
            // Khôi phục kiểu dữ liệu của serial_id về unsignedBigInteger
            $table->unsignedBigInteger('serial_id')->nullable()->change();
            
            // Thêm lại khóa ngoại
            $table->foreign('serial_id')->references('id')->on('serials')->onDelete('set null');
        });
    }
}; 