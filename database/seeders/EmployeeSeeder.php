<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Employee;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kiểm tra xem đã có nhân viên nào chưa
        if (Employee::where('username', 'admin')->exists()) {
            // Nếu đã có, xóa đi để tạo lại
            Employee::where('username', 'admin')->delete();
        }
        
        if (Employee::where('username', 'nhanvien')->exists()) {
            Employee::where('username', 'nhanvien')->delete();
        }
        
        // Lấy vai trò Super Admin
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        
        // Tạo tài khoản admin - sử dụng DB::table để tránh trigger setPasswordAttribute
        DB::table('employees')->insert([
            'username' => 'admin',
            'password' => Hash::make('password'),
            'name' => 'Quản trị viên',
            'email' => 'admin@example.com',
            'phone' => '0123456789',
            'address' => 'Hà Nội, Việt Nam',
            'role' => 'admin',
            'role_id' => $superAdminRole ? $superAdminRole->id : null,
            'status' => 'active',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Tạo tài khoản nhân viên - sử dụng DB::table để tránh trigger setPasswordAttribute
        DB::table('employees')->insert([
            'username' => 'nhanvien',
            'password' => Hash::make('password'),
            'name' => 'Nhân viên',
            'email' => 'nhanvien@example.com',
            'phone' => '0987654321',
            'address' => 'Hồ Chí Minh, Việt Nam',
            'role' => 'Nhân viên',
            'status' => 'active',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}           