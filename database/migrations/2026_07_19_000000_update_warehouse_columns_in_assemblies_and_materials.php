<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First add warehouse_id to assembly_materials
        Schema::table('assembly_materials', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
        });

        // Then copy warehouse_id from assemblies to assembly_materials
        $assemblies = DB::table('assemblies')->whereNotNull('warehouse_id')->get();
        foreach ($assemblies as $assembly) {
            DB::table('assembly_materials')
                ->where('assembly_id', $assembly->id)
                ->update(['warehouse_id' => $assembly->warehouse_id]);
        }

        // Finally remove columns from assemblies
        Schema::table('assemblies', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['warehouse_id']);
            $table->dropForeign(['target_warehouse_id']);

            // Then drop the columns
            $table->dropColumn('warehouse_id');
            $table->dropColumn('target_warehouse_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First add columns back to assemblies
        Schema::table('assemblies', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->unsignedBigInteger('target_warehouse_id')->nullable();

            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
            $table->foreign('target_warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
        });

        // Then copy warehouse_id from assembly_materials back to assemblies
        $materials = DB::table('assembly_materials')
            ->select('assembly_id', 'warehouse_id')
            ->groupBy('assembly_id')
            ->get();

        foreach ($materials as $material) {
            DB::table('assemblies')
                ->where('id', $material->assembly_id)
                ->update(['warehouse_id' => $material->warehouse_id]);
        }

        // Finally remove warehouse_id from assembly_materials
        Schema::table('assembly_materials', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });
    }
}; 