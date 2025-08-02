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
        Schema::table('maintenance_requests', function (Blueprint $table) {
            // Đặt giá trị mặc định cho project_address từ customer_address
            DB::statement('UPDATE maintenance_requests SET project_address = customer_address WHERE project_address IS NULL OR project_address = ""');
            
            // Thay đổi cấu trúc cột để không cho phép null
            $table->string('project_address')->nullable(false)->default('')->change();

            // Cập nhật enum maintenance_type từ cũ sang mới
            DB::statement("ALTER TABLE maintenance_requests MODIFY COLUMN maintenance_type ENUM('maintenance', 'repair', 'replacement', 'upgrade', 'other') DEFAULT 'maintenance'");
            
            // Cập nhật dữ liệu cũ
            DB::statement("UPDATE maintenance_requests SET maintenance_type = 'maintenance' WHERE maintenance_type = 'regular'");
            DB::statement("UPDATE maintenance_requests SET maintenance_type = 'repair' WHERE maintenance_type = 'emergency'");
            DB::statement("UPDATE maintenance_requests SET maintenance_type = 'maintenance' WHERE maintenance_type = 'preventive'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->string('project_address')->nullable()->change();
            // Cập nhật enum maintenance_type từ mới về cũ
            DB::statement("ALTER TABLE maintenance_requests MODIFY COLUMN maintenance_type ENUM('regular', 'emergency', 'preventive') DEFAULT 'regular'");
            
            // Cập nhật dữ liệu về cũ
            DB::statement("UPDATE maintenance_requests SET maintenance_type = 'regular' WHERE maintenance_type = 'maintenance'");
            DB::statement("UPDATE maintenance_requests SET maintenance_type = 'emergency' WHERE maintenance_type = 'repair'");
            DB::statement("UPDATE maintenance_requests SET maintenance_type = 'preventive' WHERE maintenance_type = 'upgrade'");
        });
    }
};
