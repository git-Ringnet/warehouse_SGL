<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kiểm tra nếu đã có admin thì không tạo nữa
        if (Employee::where('role', 'admin')->exists()) {
            $this->command->info('Tài khoản admin đã tồn tại, bỏ qua việc tạo mới.');
            return;
        }

        // Tạo tài khoản admin mặc định
        $admin = Employee::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'), // Mật khẩu mặc định
            'name' => 'Administrator',
            'email' => 'admin@sgl.com',
            'phone' => '0123456789',
            'address' => 'Trụ sở chính SGL',
            'role' => 'admin',
            'status' => 'active',
            'department' => 'IT',
            'is_active' => true,
            'notes' => 'Tài khoản quản trị viên hệ thống được tạo tự động'
        ]);

        // Gán role Super Admin nếu có
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        if ($superAdminRole) {
            $admin->update(['role_id' => $superAdminRole->id]);
        }

        $this->command->info('Đã tạo tài khoản admin:');
        $this->command->info('Username: admin');
        $this->command->info('Password: admin123');
        $this->command->info('Vui lòng đổi mật khẩu sau khi đăng nhập lần đầu!');
    }
} 