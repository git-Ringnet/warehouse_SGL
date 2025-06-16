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
        Schema::table('testings', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_to')->nullable()->after('tester_id');
            $table->unsignedBigInteger('receiver_id')->nullable()->after('assigned_to');
            
            $table->foreign('assigned_to')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('receiver_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('testings', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropForeign(['receiver_id']);
            
            $table->dropColumn(['assigned_to', 'receiver_id']);
        });
    }
};
