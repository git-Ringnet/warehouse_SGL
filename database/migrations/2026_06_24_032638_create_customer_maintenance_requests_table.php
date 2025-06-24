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
        Schema::create('customer_maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code')->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name')->nullable(); // Tên khách hàng nếu không liên kết với bảng customers
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_address')->nullable();
            $table->string('project_name');
            $table->text('project_description')->nullable();
            $table->date('request_date');
            $table->text('maintenance_reason');
            $table->text('maintenance_details')->nullable();
            $table->date('expected_completion_date')->nullable();
            $table->decimal('estimated_cost', 15, 2)->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'approved', 'rejected', 'in_progress', 'completed', 'canceled'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_maintenance_requests');
    }
};
