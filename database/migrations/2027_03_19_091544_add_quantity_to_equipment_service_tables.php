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
        Schema::table('dispatch_returns', function (Blueprint $table) {
            $table->decimal('quantity', 15, 3)->default(1.0)->after('serial_number');
        });

        Schema::table('dispatch_replacements', function (Blueprint $table) {
            $table->decimal('quantity', 15, 3)->default(1.0)->after('replacement_serial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispatch_returns', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });

        Schema::table('dispatch_replacements', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};
