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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->string('project_address')->nullable()->change();
        });
    }
};
