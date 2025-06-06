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
        Schema::create('goods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category');
            $table->string('unit');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('serial')->nullable();
            $table->text('notes')->nullable();
            $table->string('image_path')->nullable();
            $table->json('inventory_warehouses')->nullable();
            $table->timestamps();
        });

        // Add foreign key constraint only if suppliers table exists
        if (Schema::hasTable('suppliers')) {
            Schema::table('goods', function (Blueprint $table) {
                $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods');
    }
}; 