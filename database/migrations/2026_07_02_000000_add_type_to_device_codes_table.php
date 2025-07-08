<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('device_codes', function (Blueprint $table) {
            $table->enum('type', ['contract', 'backup'])->default('contract')->after('note');
        });
    }

    public function down()
    {
        Schema::table('device_codes', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}; 