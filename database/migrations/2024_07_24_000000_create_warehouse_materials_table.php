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
        Schema::create('warehouse_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('material_id');
            $table->enum('item_type', ['material', 'product'])->default('material');
            $table->integer('quantity')->default(0);
            $table->timestamps();

            // Unique constraint kết hợp để tránh trùng lặp (warehouse_id + material_id + item_type)
            $table->unique(['warehouse_id', 'material_id', 'item_type'], 'warehouse_material_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_materials');
    }
}; 