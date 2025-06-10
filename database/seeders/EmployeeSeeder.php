<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            [
                'username' => 'anvannguyen',
                'password' => 'password123',
                'name' => 'Nguyễn Văn An',
                'email' => 'an.nguyen@example.com',
                'phone' => '0987654321',
                'address' => 'Số 123 Đường Lê Lợi, Quận 1, TP.HCM',
            
                'notes' => 'Giám đốc điều hành',
                'role' => 'admin',
                'status' => 'active',
            ],
            [
                'username' => 'binhtran',
                'password' => 'password123',
                'name' => 'Trần Thị Bình',
                'email' => 'binh.tran@example.com',
                'phone' => '0912345678',
                'address' => 'Số 456 Đường Nguyễn Huệ, Quận 1, TP.HCM',
              
                'notes' => 'Phụ trách tuyển dụng và đào tạo',
                'role' => 'manager',
                'status' => 'active',
            ],
            [
                'username' => 'cuongle',
                'password' => 'password123',
                'name' => 'Lê Văn Cường',
                'email' => 'cuong.le@example.com',
                'phone' => '0901234567',
                'address' => 'Số 789 Đường Hai Bà Trưng, Quận 3, TP.HCM',
             
                'notes' => 'Chuyên viên kỹ thuật',
                'role' => 'staff',
                'status' => 'active',
            ],
            [
                'username' => 'dungpham',
                'password' => 'password123',
                'name' => 'Phạm Thị Dung',
                'email' => 'dung.pham@example.com',
                'phone' => '0922222222',
                'address' => 'Số 101 Đường Cách Mạng Tháng 8, Quận 10, TP.HCM',
               
                'notes' => 'Kế toán viên',
                'role' => 'staff',
                'status' => 'leave',
            ],
            [
                'username' => 'emhoang',
                'password' => 'password123',
                'name' => 'Hoàng Văn Em',
                'email' => 'em.hoang@example.com',
                'phone' => '0933333333',
                'address' => 'Số 202 Đường Nguyễn Thị Minh Khai, Quận 3, TP.HCM',
               
                'notes' => 'Kỹ thuật viên bảo trì',
                'role' => 'tech',
                'status' => 'inactive',
            ],
        ];

        foreach ($employees as $employee) {
            Employee::create($employee);
        }
    }
} 