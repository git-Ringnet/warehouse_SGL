<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testing_item_unit_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('testing_id');
            $table->unsignedBigInteger('testing_item_id')->nullable();
            $table->unsignedBigInteger('material_id')->nullable();
            $table->unsignedBigInteger('product_item_id')->nullable(); // testing item id của thành phẩm
            $table->integer('unit_index')->default(0); // chỉ số đơn vị thành phẩm
            $table->integer('no_serial_pass_quantity')->default(0);
            $table->integer('no_serial_fail_quantity')->default(0);
            $table->timestamps();

            // Short index names to satisfy MySQL 64-char limit
            $table->index(['testing_id', 'product_item_id', 'unit_index'], 'tiur_tid_pid_ui_idx');
            $table->index(['testing_item_id'], 'tiur_titem_idx');
            $table->index(['material_id'], 'tiur_mid_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testing_item_unit_results');
    }
};

