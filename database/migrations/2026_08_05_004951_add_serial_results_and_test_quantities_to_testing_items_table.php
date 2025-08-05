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
        Schema::table('testing_items', function (Blueprint $table) {
            $table->json('serial_results')->nullable()->after('fail_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('testing_items', function (Blueprint $table) {
            $table->dropColumn('serial_results');
        });
    }
};
