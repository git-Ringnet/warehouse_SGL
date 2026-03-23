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
        Schema::table('dispatch_replacements', function (Blueprint $table) {
            $table->decimal('original_returned_quantity', 15, 3)->default(0)->after('quantity');
            $table->decimal('replacement_returned_quantity', 15, 3)->default(0)->after('original_returned_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispatch_replacements', function (Blueprint $table) {
            $table->dropColumn(['original_returned_quantity', 'replacement_returned_quantity']);
        });
    }
};
