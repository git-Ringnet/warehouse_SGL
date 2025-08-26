<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testing_items', function (Blueprint $table) {
            if (!Schema::hasColumn('testing_items', 'no_serial_pass_quantity')) {
                $table->integer('no_serial_pass_quantity')->default(0)->after('pass_quantity');
            }
            if (!Schema::hasColumn('testing_items', 'no_serial_fail_quantity')) {
                $table->integer('no_serial_fail_quantity')->default(0)->after('no_serial_pass_quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('testing_items', function (Blueprint $table) {
            if (Schema::hasColumn('testing_items', 'no_serial_pass_quantity')) {
                $table->dropColumn('no_serial_pass_quantity');
            }
            if (Schema::hasColumn('testing_items', 'no_serial_fail_quantity')) {
                $table->dropColumn('no_serial_fail_quantity');
            }
        });
    }
};

