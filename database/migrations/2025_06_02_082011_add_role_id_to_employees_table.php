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
        Schema::table('employees', function (Blueprint $table) {
            // Thêm trường role_id và foreign key
            $table->unsignedBigInteger('role_id')->nullable()->after('role');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
            
            // Thêm trường scope_value để lưu giá trị phạm vi quyền (ID kho hoặc ID dự án)
            $table->string('scope_value')->nullable()->after('role_id');
            
            // Thêm trường scope_type để lưu loại phạm vi (kho, dự án, địa bàn...)
            $table->string('scope_type')->nullable()->after('scope_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id', 'scope_value', 'scope_type']);
        });
    }
};
