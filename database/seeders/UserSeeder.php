<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo một khách hàng mẫu nếu chưa có
        if (Customer::count() == 0) {
            $customer = Customer::create([
                'name' => 'Nguyễn Văn B',
                'company_name' => 'Công ty TNHH Nguyễn B',
                'phone' => '0987654321',
                'company_phone' => '0123456789',
                'email' => 'nguyenb@example.com',
                'address' => 'Hà Nội, Việt Nam',
                'notes' => 'Khách hàng mẫu',
                'has_account' => true,
                'account_username' => 'nguyenb629',
                'account_password' => 'RureHZe6Q3',
            ]);
            
            // Tạo tài khoản người dùng cho khách hàng
            User::create([
                'name' => $customer->name,
                'email' => $customer->email,
                'username' => 'nguyenb629',
                'password' => Hash::make('RureHZe6Q3'),
                'role' => 'customer',
                'customer_id' => $customer->id,
                'active' => true,
            ]);
        }
        
        // Tạo một tài khoản khách hàng khác
        if (Customer::count() == 1) {
            $customer = Customer::create([
                'name' => 'Trần Thị C',
                'company_name' => 'Công ty CP Trần C',
                'phone' => '0912345678',
                'company_phone' => '0234567890',
                'email' => 'tranc@example.com',
                'address' => 'Hồ Chí Minh, Việt Nam',
                'notes' => 'Khách hàng mẫu thứ hai',
                'has_account' => true,
                'account_username' => 'tranc123',
                'account_password' => 'password123',
            ]);
            
            // Tạo tài khoản người dùng cho khách hàng
            User::create([
                'name' => $customer->name,
                'email' => $customer->email,
                'username' => 'tranc123',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'customer_id' => $customer->id,
                'active' => true,
            ]);
        }
    }
} 