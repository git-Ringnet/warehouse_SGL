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
        Schema::disableForeignKeyConstraints();

        Schema::table('assembly_materials', function (Blueprint $table) {
            // Đổi kiểu dữ liệu của serial_id thành text
            $table->text('serial_id')->nullable()->change();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('assembly_materials', function (Blueprint $table) {
            // Khôi phục kiểu dữ liệu của serial_id về unsignedBigInteger
            $table->unsignedBigInteger('serial_id')->nullable()->change();
            
            // Thêm lại khóa ngoại
            $table->foreign('serial_id')->references('id')->on('serials')->onDelete('set null');
        });

        Schema::enableForeignKeyConstraints();
    }
}; 