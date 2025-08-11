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
        Schema::table('customer_maintenance_requests', function (Blueprint $table) {
            // Kiểm tra xem các trường đã tồn tại chưa trước khi thêm
            if (!Schema::hasColumn('customer_maintenance_requests', 'project_id')) {
                $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('customer_maintenance_requests', 'rental_id')) {
                $table->foreignId('rental_id')->nullable()->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('customer_maintenance_requests', 'item_source')) {
                $table->enum('item_source', ['project', 'rental'])->nullable();
            }
            if (!Schema::hasColumn('customer_maintenance_requests', 'selected_item')) {
                $table->string('selected_item')->nullable(); // Lưu thông tin item được chọn (type:id)
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_maintenance_requests', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['rental_id']);
            $table->dropColumn(['project_id', 'rental_id', 'item_source', 'selected_item']);
        });
    }
};
