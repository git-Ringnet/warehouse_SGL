<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tạo thư mục software trong storage/app/public
        if (!Storage::disk('public')->exists('software')) {
            Storage::disk('public')->makeDirectory('software');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không xóa thư mục để tránh mất dữ liệu
    }
};
