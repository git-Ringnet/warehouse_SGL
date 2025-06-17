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
        Schema::create('testings', function (Blueprint $table) {
            $table->id();
            $table->string('test_code')->unique();
            $table->enum('test_type', ['material', 'finished_product']);
            $table->foreignId('tester_id')->constrained('employees');
            $table->date('test_date');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->text('conclusion')->nullable();
            $table->integer('pass_quantity')->default(0);
            $table->integer('fail_quantity')->default(0);
            $table->text('fail_reasons')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('employees');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('employees');
            $table->timestamp('received_at')->nullable();
            $table->boolean('is_inventory_updated')->default(false);
            $table->foreignId('success_warehouse_id')->nullable()->constrained('warehouses');
            $table->foreignId('fail_warehouse_id')->nullable()->constrained('warehouses');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testings');
    }
}; 