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
        // Kiểm tra và drop foreign key constraints cho warehouse_transfers
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'warehouse_transfers' 
            AND COLUMN_NAME = 'material_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE warehouse_transfers DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
        }
        
        // Kiểm tra và drop foreign key constraints cho warehouse_transfer_materials
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'warehouse_transfer_materials' 
            AND COLUMN_NAME = 'material_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE warehouse_transfer_materials DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
        }
        
        // Thêm lại index mà không có foreign key constraint
        Schema::table('warehouse_transfers', function (Blueprint $table) {
            $table->index('material_id');
        });
        
        Schema::table('warehouse_transfer_materials', function (Blueprint $table) {
            $table->index('material_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Khôi phục lại foreign key constraints
        Schema::table('warehouse_transfers', function (Blueprint $table) {
            $table->dropIndex(['material_id']);
            $table->foreign('material_id')->references('id')->on('materials');
        });
        
        Schema::table('warehouse_transfer_materials', function (Blueprint $table) {
            $table->dropIndex(['material_id']);
            $table->foreign('material_id')->references('id')->on('materials');
        });
    }
}; 