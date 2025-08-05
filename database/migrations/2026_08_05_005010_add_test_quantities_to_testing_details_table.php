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
        Schema::table('testing_details', function (Blueprint $table) {
            $table->integer('test_pass_quantity')->default(0)->after('notes');
            $table->integer('test_fail_quantity')->default(0)->after('test_pass_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('testing_details', function (Blueprint $table) {
            $table->dropColumn(['test_pass_quantity', 'test_fail_quantity']);
        });
    }
};
