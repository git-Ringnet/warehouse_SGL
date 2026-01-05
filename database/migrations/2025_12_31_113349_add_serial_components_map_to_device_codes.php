<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Thêm cột serial_components_map để lưu serial vật tư theo dạng keyed object.
     * Format mới: {"MW-75W_1": "11111", "MW-75W_2": "", "TUSAT_1": "2222", "TUSAT_2": ""}
     * Key format: {material_code}_{slot_index}
     * 
     * Ưu điểm so với serial_components (positional array):
     * - Không phụ thuộc thứ tự query từ assembly_materials
     * - Khi đọc, tìm theo key material_code + slot, không cần quan tâm position
     * - Tự động đúng khi vật tư thay đổi
     */
    public function up(): void
    {
        Schema::table('device_codes', function (Blueprint $table) {
            $table->json('serial_components_map')->nullable()->after('serial_components');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_codes', function (Blueprint $table) {
            $table->dropColumn('serial_components_map');
        });
    }
};
