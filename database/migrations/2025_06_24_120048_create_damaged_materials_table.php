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
        Schema::create('damaged_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('repair_id')->index();
            $table->string('device_code', 100);
            $table->string('material_code', 100);
            $table->string('material_name');
            $table->string('serial')->nullable();
            $table->text('damage_description')->nullable();
            $table->unsignedBigInteger('reported_by'); // Người báo cáo hư hỏng
            $table->timestamp('reported_at');
            $table->timestamps();

            $table->foreign('repair_id')->references('id')->on('repairs')->onDelete('cascade');
            $table->foreign('reported_by')->references('id')->on('users');
            
            // Unique constraint để tránh duplicate
            $table->unique(['repair_id', 'device_code', 'material_code', 'serial'], 'unique_damaged_material');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('damaged_materials');
    }
};
