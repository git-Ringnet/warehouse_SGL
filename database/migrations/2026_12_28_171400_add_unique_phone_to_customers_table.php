<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * Thêm ràng buộc unique cho cột phone để có thể sử dụng số điện thoại đăng nhập
     */
    public function up(): void
    {
        // Kiểm tra và xử lý các số điện thoại trùng lặp trước khi thêm unique constraint
        $duplicatePhones = DB::table('customers')
            ->select('phone')
            ->whereNotNull('phone')
            ->groupBy('phone')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('phone');

        if ($duplicatePhones->count() > 0) {
            // Thông báo về các số điện thoại trùng lặp
            throw new \Exception(
                'Có ' . $duplicatePhones->count() . ' số điện thoại trùng lặp trong bảng customers. ' .
                'Vui lòng sửa các số điện thoại sau trước khi chạy migration: ' .
                $duplicatePhones->implode(', ')
            );
        }

        Schema::table('customers', function (Blueprint $table) {
            // Thêm unique constraint cho cột phone
            $table->unique('phone', 'customers_phone_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_phone_unique');
        });
    }
};
