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
        Schema::table('software', function (Blueprint $table) {
            $table->string('manual_path')->nullable()->after('file_type'); // Đường dẫn lưu trữ tài liệu hướng dẫn
            $table->string('manual_name')->nullable()->after('manual_path'); // Tên tài liệu hướng dẫn gốc
            $table->string('manual_size')->nullable()->after('manual_name'); // Kích thước tài liệu hướng dẫn (đã được format)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('software', function (Blueprint $table) {
            $table->dropColumn('manual_path');
            $table->dropColumn('manual_name');
            $table->dropColumn('manual_size');
        });
    }
};
