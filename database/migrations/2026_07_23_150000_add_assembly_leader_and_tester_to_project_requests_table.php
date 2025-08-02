<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_requests', function (Blueprint $table) {
            $table->foreignId('assembly_leader_id')->nullable()->constrained('employees')->onDelete('set null')->after('implementer_id');
            $table->foreignId('tester_id')->nullable()->constrained('employees')->onDelete('set null')->after('assembly_leader_id');
        });
    }

    public function down(): void
    {
        Schema::table('project_requests', function (Blueprint $table) {
            $table->dropForeign(['assembly_leader_id']);
            $table->dropColumn('assembly_leader_id');
            $table->dropForeign(['tester_id']);
            $table->dropColumn('tester_id');
        });
    }
};