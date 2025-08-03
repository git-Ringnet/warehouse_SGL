<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Thêm cột nếu chưa có
        if (!Schema::hasColumn('maintenance_request_products', 'serial_number')) {
            Schema::table('maintenance_request_products', function (Blueprint $table) {
                $table->string('serial_number')->nullable()->after('product_code');
            });
        }
        
        if (!Schema::hasColumn('maintenance_request_products', 'type')) {
            Schema::table('maintenance_request_products', function (Blueprint $table) {
                $table->string('type')->nullable()->after('serial_number');
            });
        }
        
        Schema::table('maintenance_requests', function (Blueprint $table) {
            // Thay đổi enum trước
            DB::statement("ALTER TABLE maintenance_requests MODIFY COLUMN maintenance_type ENUM('maintenance', 'repair', 'replacement', 'upgrade', 'other', 'regular', 'emergency', 'preventive') DEFAULT 'maintenance'");
            
            // Cập nhật dữ liệu cũ
            DB::statement("UPDATE maintenance_requests SET maintenance_type = 'maintenance' WHERE maintenance_type = 'regular'");
            DB::statement("UPDATE maintenance_requests SET maintenance_type = 'repair' WHERE maintenance_type = 'emergency'");
            DB::statement("UPDATE maintenance_requests SET maintenance_type = 'maintenance' WHERE maintenance_type = 'preventive'");
            
            // Loại bỏ các giá trị cũ khỏi enum
            DB::statement("ALTER TABLE maintenance_requests MODIFY COLUMN maintenance_type ENUM('maintenance', 'repair', 'replacement', 'upgrade', 'other') DEFAULT 'maintenance'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            // Cập nhật enum maintenance_type từ mới về cũ
            DB::statement("ALTER TABLE maintenance_requests MODIFY COLUMN maintenance_type ENUM('regular', 'emergency', 'preventive') DEFAULT 'regular'");
            
            // Cập nhật dữ liệu về cũ
            DB::statement("UPDATE maintenance_requests SET maintenance_type = 'regular' WHERE maintenance_type = 'maintenance'");
            DB::statement("UPDATE maintenance_requests SET maintenance_type = 'emergency' WHERE maintenance_type = 'repair'");
            DB::statement("UPDATE maintenance_requests SET maintenance_type = 'preventive' WHERE maintenance_type = 'upgrade'");
        });
    }
};
