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
            $table->string('original_serial')->nullable()->after('replacement_dispatch_item_id'); // Serial cụ thể của thiết bị gốc được thay thế
            $table->string('replacement_serial')->nullable()->after('original_serial'); // Serial cụ thể của thiết bị thay thế
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispatch_replacements', function (Blueprint $table) {
            $table->dropColumn(['original_serial', 'replacement_serial']);
        });
    }
};
