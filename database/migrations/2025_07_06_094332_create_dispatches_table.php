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
        Schema::create('dispatches', function (Blueprint $table) {
            $table->id();
            $table->string('dispatch_code')->unique();
            $table->date('dispatch_date');
            $table->enum('dispatch_type', ['project', 'rental', 'other']);
            $table->enum('dispatch_detail', ['all', 'contract', 'backup']);
            $table->string('project_receiver');
            $table->string('warranty_period')->nullable();
            $table->unsignedBigInteger('company_representative_id')->nullable();
            $table->text('dispatch_note')->nullable();
            $table->enum('status', ['pending', 'approved', 'completed', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('company_representative_id')->references('id')->on('employees');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->foreign('project_id')->references('id')->on('projects');

            // Indexes
            $table->index(['dispatch_date', 'status']);
            $table->index('dispatch_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispatches');
    }
};
