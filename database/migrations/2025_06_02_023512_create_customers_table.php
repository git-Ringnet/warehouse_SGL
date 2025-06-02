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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên khách hàng
            $table->string('phone'); // Số điện thoại
            $table->string('email')->nullable(); // Email (không bắt buộc)
            $table->text('address')->nullable(); // Địa chỉ (không bắt buộc)
            $table->text('notes')->nullable(); // Ghi chú (không bắt buộc)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
