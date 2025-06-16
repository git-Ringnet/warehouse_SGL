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
        Schema::table('assemblies', function (Blueprint $table) {
            // Add purpose field
            $table->enum('purpose', ['storage', 'project'])->default('storage')->after('status');
            
            // Add project_id field
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null')->after('purpose');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assemblies', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
            $table->dropColumn('purpose');
        });
    }
};
