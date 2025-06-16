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
            // Add tester_id field
            $table->foreignId('tester_id')->nullable()->constrained('employees')->onDelete('set null')->after('assigned_to');
            
            // Note: We'll handle assigned_to conversion in a separate step
            // For now, just add a new field for the foreign key
            $table->foreignId('assigned_employee_id')->nullable()->constrained('employees')->onDelete('set null')->after('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assemblies', function (Blueprint $table) {
            $table->dropForeign(['tester_id']);
            $table->dropColumn('tester_id');
            
            $table->dropForeign(['assigned_employee_id']);
            $table->dropColumn('assigned_employee_id');
        });
    }
};
