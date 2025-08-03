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
        Schema::table('customer_maintenance_requests', function (Blueprint $table) {
            $table->dropColumn('expected_completion_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_maintenance_requests', function (Blueprint $table) {
            $table->date('expected_completion_date')->nullable();
        });
    }
};
