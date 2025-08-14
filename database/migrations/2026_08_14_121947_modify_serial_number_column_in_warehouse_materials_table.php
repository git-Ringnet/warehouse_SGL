<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Xử lý dữ liệu trước khi thay đổi cột
        DB::statement('UPDATE warehouse_materials SET serial_number = NULL WHERE LENGTH(serial_number) > 255');
        
        Schema::table('warehouse_materials', function (Blueprint $table) {
            // Thay đổi cột serial_number từ VARCHAR(255) thành TEXT
            $table->text('serial_number')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_materials', function (Blueprint $table) {
            // Khôi phục cột serial_number về VARCHAR(255)
            $table->string('serial_number', 255)->change();
        });
    }
};
