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
        // Drop the rental_items table if it exists
        Schema::dropIfExists('rental_items');

        // Update the rentals table structure
        Schema::table('rentals', function (Blueprint $table) {
            // Keep the existing fields for now
            // The relationship with rental_items is removed by dropping the rental_items table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Since we're dropping the rental_items table, recreate it in the down method
        Schema::create('rental_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained()->onDelete('cascade');
            $table->string('item_type');
            $table->unsignedBigInteger('item_id');
            $table->integer('quantity');
            $table->decimal('price', 15, 2);
            $table->text('notes')->nullable();
            $table->json('serial_numbers')->nullable();
            $table->timestamps();
        });
    }
}; 