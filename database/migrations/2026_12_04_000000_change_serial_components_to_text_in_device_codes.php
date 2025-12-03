<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Thay đổi kiểu dữ liệu của serial_components từ VARCHAR sang TEXT
     * để có thể lưu JSON array với nhiều phần tử
     */
    public function up(): void
    {
        Schema::table('device_codes', function (Blueprint $table) {
            $table->text('serial_components')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_codes', function (Blueprint $table) {
            $table->string('serial_components')->nullable()->change();
        });
    }
};
