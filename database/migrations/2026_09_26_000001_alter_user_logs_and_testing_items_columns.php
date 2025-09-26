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
        // Nâng cấp kiểu dữ liệu cột log lên LONGTEXT
        Schema::table('user_logs', function (Blueprint $table) {
            $table->longText('old_data')->nullable()->change();
            $table->longText('new_data')->nullable()->change();
        });

        // Tăng độ dài cột serial_number của bảng testing_items lên LONGTEXT
        Schema::table('testing_items', function (Blueprint $table) {
            $table->longText('serial_number')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Trả về TEXT (giả định kiểu cũ là TEXT và VARCHAR(255) cho testing_items.serial_number)
        Schema::table('user_logs', function (Blueprint $table) {
            $table->text('old_data')->nullable()->change();
            $table->text('new_data')->nullable()->change();
        });

        Schema::table('testing_items', function (Blueprint $table) {
            $table->string('serial_number', 255)->nullable()->change();
        });
    }
};


