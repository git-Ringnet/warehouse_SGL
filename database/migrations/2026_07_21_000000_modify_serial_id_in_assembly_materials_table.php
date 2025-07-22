<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // Tìm tên constraint chính xác
        $constraint = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = 'assembly_materials'
              AND COLUMN_NAME = 'serial_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
              AND CONSTRAINT_SCHEMA = DATABASE()
        ");

        if (!empty($constraint)) {
            $name = $constraint[0]->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE assembly_materials DROP FOREIGN KEY `$name`");
        }

        Schema::table('assembly_materials', function (Blueprint $table) {
            $table->text('serial_id')->nullable()->change();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('assembly_materials', function (Blueprint $table) {
            $table->unsignedBigInteger('serial_id')->nullable()->change();
            $table->foreign('serial_id')->references('id')->on('serials')->onDelete('set null');
        });

        Schema::enableForeignKeyConstraints();
    }
};
