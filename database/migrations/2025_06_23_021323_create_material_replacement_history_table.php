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
        Schema::create('material_replacement_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('repair_id')->index();
            $table->string('device_code', 100);
            $table->string('material_code', 100);
            $table->string('material_name');
            $table->json('old_serials'); // Serial cũ được thay thế
            $table->json('new_serials'); // Serial mới thay thế
            $table->integer('quantity')->default(1);
            $table->unsignedBigInteger('source_warehouse_id'); // Kho chuyển vật tư cũ đến
            $table->unsignedBigInteger('target_warehouse_id'); // Kho lấy vật tư mới
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('replaced_by'); // Người thực hiện thay thế
            $table->timestamp('replaced_at');
            $table->timestamps();

            $table->foreign('repair_id')->references('id')->on('repairs')->onDelete('cascade');
            $table->foreign('source_warehouse_id')->references('id')->on('warehouses');
            $table->foreign('target_warehouse_id')->references('id')->on('warehouses');
            $table->foreign('replaced_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_replacement_history');
    }
};
