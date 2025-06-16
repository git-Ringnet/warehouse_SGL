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
        Schema::create('testing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('testing_id')->constrained()->onDelete('cascade');
            $table->enum('item_type', ['material', 'product', 'finished_product']);
            $table->foreignId('material_id')->nullable()->constrained('materials');
            $table->foreignId('product_id')->nullable()->constrained('products');
            $table->foreignId('good_id')->nullable()->constrained('goods');
            $table->foreignId('assembly_id')->nullable()->constrained('assemblies');
            $table->string('serial_number')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers');
            $table->string('batch_number')->nullable();
            $table->integer('quantity')->default(1);
            $table->enum('result', ['pass', 'fail', 'pending'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testing_items');
    }
}; 