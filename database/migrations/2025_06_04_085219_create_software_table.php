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
        Schema::create('software', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('version');
            $table->string('type'); // mobile_app, firmware, desktop_app, driver, other
            $table->string('file_path'); // Đường dẫn lưu trữ file
            $table->string('file_name'); // Tên file gốc
            $table->string('file_size'); // Kích thước file (đã được format)
            $table->string('file_type'); // Loại file (apk, bin, zip, v.v.)
            $table->date('release_date')->nullable();
            $table->string('platform')->nullable(); // android, ios, windows, mac, linux, v.v.
            $table->string('status')->default('active'); // active, inactive, beta
            $table->text('description')->nullable();
            $table->text('changelog')->nullable();
            $table->integer('download_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('software');
    }
};
