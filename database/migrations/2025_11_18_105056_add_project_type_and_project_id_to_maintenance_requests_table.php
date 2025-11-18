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
        Schema::table('maintenance_requests', function (Blueprint $table) {
            // Thêm cột project_type nếu chưa tồn tại
            if (!Schema::hasColumn('maintenance_requests', 'project_type')) {
                $table->enum('project_type', ['project', 'rental'])->nullable()->after('proposer_id');
            }
            
            // Thêm cột project_id nếu chưa tồn tại
            if (!Schema::hasColumn('maintenance_requests', 'project_id')) {
                $table->unsignedBigInteger('project_id')->nullable()->after('project_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            if (Schema::hasColumn('maintenance_requests', 'project_id')) {
                $table->dropColumn('project_id');
            }
            if (Schema::hasColumn('maintenance_requests', 'project_type')) {
                $table->dropColumn('project_type');
            }
        });
    }
};
