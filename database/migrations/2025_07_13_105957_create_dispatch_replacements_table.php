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
        Schema::create('dispatch_replacements', function (Blueprint $table) {
            $table->id();
            $table->string('replacement_code')->unique(); // Mã phiếu thay thế
            $table->foreignId('original_dispatch_item_id')->constrained('dispatch_items')->onDelete('cascade'); // Thiết bị cần thay thế
            $table->foreignId('replacement_dispatch_item_id')->constrained('dispatch_items')->onDelete('cascade'); // Thiết bị thay thế
            $table->foreignId('user_id')->constrained('users'); // Người thực hiện
            $table->dateTime('replacement_date'); // Ngày thay thế
            $table->text('reason')->nullable(); // Lý do thay thế
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed'); // Trạng thái
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispatch_replacements');
    }
}; 