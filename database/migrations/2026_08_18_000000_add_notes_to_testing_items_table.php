<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('testing_items', function (Blueprint $table) {
            if (!Schema::hasColumn('testing_items', 'notes')) {
                $table->text('notes')->nullable()->after('serial_results');
            }
        });
    }

    public function down(): void
    {
        Schema::table('testing_items', function (Blueprint $table) {
            if (Schema::hasColumn('testing_items', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};

