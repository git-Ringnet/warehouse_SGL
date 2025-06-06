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
        Schema::table('rentals', function (Blueprint $table) {
            // Thêm cột rental_name
            $table->string('rental_name')->after('rental_code');
            
            // Xóa các cột không cần thiết
            $table->dropColumn([
                'total_price',
                'payment_status',
                'deposit_amount',
                'warehouse_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            // Thêm lại các cột đã xóa
            $table->decimal('total_price', 15, 2)->default(0);
            $table->string('payment_status')->default('unpaid');
            $table->decimal('deposit_amount', 15, 2)->default(0);
            $table->foreignId('warehouse_id')->nullable();
            
            // Xóa cột đã thêm
            $table->dropColumn('rental_name');
        });
    }
}; 