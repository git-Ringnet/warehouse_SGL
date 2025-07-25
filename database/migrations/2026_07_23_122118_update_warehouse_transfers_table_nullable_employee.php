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
        // Đổi employee_id thành nullable
        DB::statement('ALTER TABLE warehouse_transfers MODIFY employee_id BIGINT UNSIGNED NULL');
        
        // Set default status = 'pending' cho các record hiện tại
        DB::statement("UPDATE warehouse_transfers SET status = 'pending' WHERE status IS NULL");
        
        // Đổi status thành default 'pending'
        DB::statement("ALTER TABLE warehouse_transfers MODIFY status VARCHAR(255) NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert employee_id về NOT NULL
        DB::statement('ALTER TABLE warehouse_transfers MODIFY employee_id BIGINT UNSIGNED NOT NULL');
        
        // Revert status về không có default
        DB::statement('ALTER TABLE warehouse_transfers MODIFY status VARCHAR(255) NOT NULL');
    }
};
