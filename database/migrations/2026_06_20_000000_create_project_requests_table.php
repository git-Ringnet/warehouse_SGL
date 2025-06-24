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
        Schema::create('project_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code')->unique();
            $table->date('request_date');
            $table->foreignId('proposer_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->foreignId('implementer_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->string('project_name');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->string('project_address');
            $table->enum('approval_method', ['production', 'warehouse'])->default('production');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->string('customer_address');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'in_progress', 'completed', 'canceled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_requests');
    }
}; 