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
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code')->unique();
            $table->date('request_date');
            $table->foreignId('proposer_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('project_name');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('project_address');
            $table->date('maintenance_date');
            $table->enum('maintenance_type', ['regular', 'emergency', 'preventive'])->default('regular');
            $table->text('maintenance_reason');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->string('customer_address');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'in_progress', 'completed', 'canceled'])->default('pending');
            $table->text('reject_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
}; 