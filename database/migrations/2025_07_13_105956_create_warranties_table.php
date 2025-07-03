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
        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            $table->string('warranty_code')->unique(); // Mã bảo hành
            $table->foreignId('dispatch_id')->constrained('dispatches')->onDelete('cascade'); // Liên kết với phiếu xuất
            $table->foreignId('dispatch_item_id')->constrained('dispatch_items')->onDelete('cascade'); // Liên kết với item trong phiếu xuất
            $table->string('item_type'); // material, product, good
            $table->unsignedBigInteger('item_id'); // ID của sản phẩm
            $table->text('serial_number', 1000)->nullable(); // Số serial của sản phẩm
            $table->string('customer_name'); // Tên khách hàng
            $table->string('customer_phone')->nullable(); // SĐT khách hàng
            $table->string('customer_email')->nullable(); // Email khách hàng
            $table->text('customer_address')->nullable(); // Địa chỉ khách hàng
            $table->string('project_name'); // Tên dự án
            $table->date('purchase_date'); // Ngày mua (từ dispatch_date)
            $table->date('warranty_start_date'); // Ngày bắt đầu bảo hành
            $table->date('warranty_end_date'); // Ngày kết thúc bảo hành
            $table->integer('warranty_period_months')->default(12); // Thời gian bảo hành (tháng)
            $table->enum('warranty_type', ['standard', 'extended', 'premium'])->default('standard'); // Loại bảo hành
            $table->enum('status', ['active', 'expired', 'claimed', 'void'])->default('active'); // Trạng thái bảo hành
            $table->text('warranty_terms')->nullable(); // Điều khoản bảo hành
            $table->text('notes')->nullable(); // Ghi chú
            $table->string('qr_code')->nullable(); // Mã QR cho tra cứu nhanh
            $table->foreignId('created_by')->nullable()->constrained('users'); // Người tạo
            $table->timestamp('activated_at')->nullable(); // Thời gian kích hoạt
            $table->timestamps();
            
            // Indexes
            $table->index(['item_type', 'item_id']);
            $table->index('serial_number');
            $table->index('warranty_code');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warranties');
    }
};
