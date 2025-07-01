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
        Schema::table('repairs', function (Blueprint $table) {
            $table->foreignId('maintenance_request_id')->nullable()->after('created_by')->constrained('maintenance_requests')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            $table->dropForeign(['maintenance_request_id']);
            $table->dropColumn('maintenance_request_id');
        });
    }
};
