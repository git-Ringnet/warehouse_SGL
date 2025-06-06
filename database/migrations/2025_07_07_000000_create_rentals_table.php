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
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->string('rental_code')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->date('rental_date');
            $table->date('due_date');
            $table->decimal('total_price', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->enum('payment_status', ['paid', 'unpaid', 'partial'])->default('unpaid');
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->string('warehouse_id')->nullable();
            $table->timestamps();
        });

        // Bảng rental_items để lưu các thiết bị cho thuê
        Schema::create('rental_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained()->onDelete('cascade');
            $table->string('item_type'); // Loại thiết bị (sản phẩm, linh kiện)
            $table->unsignedBigInteger('item_id'); // ID của thiết bị
            $table->integer('quantity');
            $table->decimal('price', 12, 2);
            $table->text('notes')->nullable();
            $table->json('serial_numbers')->nullable(); // Danh sách serial
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_items');
        Schema::dropIfExists('rentals');
    }
}; 