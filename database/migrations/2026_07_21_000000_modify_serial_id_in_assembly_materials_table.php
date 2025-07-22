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
            // Xóa ràng buộc foreign key nếu tồn tại
            try {
                $table->dropForeign('assembly_materials_serial_id_foreign');
            } catch (\Exception $e) {
                // Không có foreign key thì bỏ qua lỗi
            }

            // Đổi kiểu dữ liệu của serial_id
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
            $table->unsignedBigInteger('serial_id')->nullable()->change();

            // Thêm lại foreign key
            $table->foreign('serial_id')->references('id')->on('serials')->onDelete('set null');
        });

        Schema::enableForeignKeyConstraints();
    }
};
