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
        Schema::table('project_requests', function (Blueprint $table) {
            // Thêm cột project_id nếu chưa tồn tại
            if (!Schema::hasColumn('project_requests', 'project_id')) {
                $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null');
            }
            
            // Thêm cột rental_id nếu chưa tồn tại
            if (!Schema::hasColumn('project_requests', 'rental_id')) {
                $table->foreignId('rental_id')->nullable()->constrained('rentals')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_requests', function (Blueprint $table) {
            // Xóa foreign key constraints trước khi xóa cột
            if (Schema::hasColumn('project_requests', 'project_id')) {
                $table->dropForeign(['project_id']);
                $table->dropColumn('project_id');
            }
            
            if (Schema::hasColumn('project_requests', 'rental_id')) {
                $table->dropForeign(['rental_id']);
                $table->dropColumn('rental_id');
            }
        });
    }
};
