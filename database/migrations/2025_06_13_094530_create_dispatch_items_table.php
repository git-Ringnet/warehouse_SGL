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
        Schema::create('dispatch_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dispatch_id');
            $table->enum('item_type', ['material', 'product', 'good']);
            $table->unsignedBigInteger('item_id');
            $table->integer('quantity');
            $table->enum('category', ['contract', 'backup', 'general'])->default('general');
            $table->json('serial_numbers')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('dispatch_id')->references('id')->on('dispatches')->onDelete('cascade');

            // Indexes
            $table->index(['dispatch_id', 'item_type']);
            $table->index(['item_type', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispatch_items');
    }
};
