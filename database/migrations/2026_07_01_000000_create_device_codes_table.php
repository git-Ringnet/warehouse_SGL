<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('device_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dispatch_id')->nullable(); // ID phiếu xuất
            $table->string('product_id'); // ID sản phẩm
            $table->string('serial_main'); // Serial chính
            $table->string('serial_components')->nullable(); // Serial vật tư (JSON array)
            $table->string('serial_sim')->nullable(); // Serial SIM
            $table->string('access_code')->nullable(); // Mã truy cập
            $table->string('iot_id')->nullable(); // ID IoT
            $table->string('mac_4g')->nullable(); // MAC 4G
            $table->text('note')->nullable(); // Ghi chú
            $table->timestamps();

            $table->foreign('dispatch_id')
                  ->references('id')
                  ->on('dispatches')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('device_codes');
    }
}; 