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
            $table->integer('pass_quantity')->default(0)->after('result');
            $table->integer('fail_quantity')->default(0)->after('pass_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('testing_items', function (Blueprint $table) {
            $table->dropColumn(['pass_quantity', 'fail_quantity']);
        });
    }
};
