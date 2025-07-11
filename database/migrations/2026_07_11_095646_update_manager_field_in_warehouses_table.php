<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Lưu dữ liệu cũ
        $warehouses = DB::table('warehouses')->get();
        $managerData = [];
        
        foreach ($warehouses as $warehouse) {
            // Tìm employee có name = manager
            $employee = Employee::where('name', $warehouse->manager)->first();
            if ($employee) {
                $managerData[$warehouse->id] = $employee->id;
            }
        }
        
        Schema::table('warehouses', function (Blueprint $table) {
            // Xóa cột manager cũ
            $table->dropColumn('manager');
        });

        Schema::table('warehouses', function (Blueprint $table) {
            // Tạo lại cột manager mới với kiểu unsignedBigInteger
            $table->unsignedBigInteger('manager')->nullable();
            $table->foreign('manager')
                  ->references('id')
                  ->on('employees')
                  ->onDelete('set null');
        });
        
        // Khôi phục dữ liệu
        foreach ($managerData as $warehouseId => $employeeId) {
            DB::table('warehouses')
                ->where('id', $warehouseId)
                ->update(['manager' => $employeeId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            // Xóa khóa ngoại
            $table->dropForeign(['manager']);
            // Xóa cột
            $table->dropColumn('manager');
        });

        Schema::table('warehouses', function (Blueprint $table) {
            // Tạo lại cột manager với kiểu string
            $table->string('manager');
        });
    }
};
