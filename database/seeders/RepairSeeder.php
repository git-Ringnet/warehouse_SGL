<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Repair;
use App\Models\RepairItem;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;

class RepairSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have at least one employee for technician
        $employee = Employee::where('status', 'active')->first();
        if (!$employee) {
            $employee = Employee::create([
                'username' => 'technician01',
                'password' => bcrypt('password'),
                'name' => 'Kỹ thuật viên A',
                'phone' => '0123456789',
                'role' => 'tech',
                'status' => 'active',
            ]);
        }

        // Ensure we have at least one user for created_by
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        // Create sample repairs
        $repairs = [
            [
                'repair_code' => 'SC202406001',
                'warranty_code' => 'BH202406001',
                'repair_type' => 'maintenance',
                'repair_date' => Carbon::now()->subDays(10),
                'technician_id' => $employee->id,
                'warehouse_id' => 1,
                'repair_description' => 'Bảo trì định kỳ thiết bị, kiểm tra và vệ sinh hệ thống',
                'repair_notes' => 'Thiết bị hoạt động bình thường sau bảo trì',
                'status' => 'completed',
                'created_by' => $user->id,
                'devices' => [
                    [
                        'device_code' => 'DEV001',
                        'device_name' => 'Bộ điều khiển chính',
                        'device_serial' => 'SN001122',
                        'device_status' => 'selected',
                        'device_notes' => 'Thiết bị hoạt động ổn định'
                    ]
                ]
            ],
            [
                'repair_code' => 'SC202406002',
                'warranty_code' => null,
                'repair_type' => 'repair',
                'repair_date' => Carbon::now()->subDays(5),
                'technician_id' => $employee->id,
                'warehouse_id' => 2,
                'repair_description' => 'Sửa chữa lỗi cảm biến nhiệt độ không hoạt động',
                'repair_notes' => 'Đã thay thế cảm biến mới, thiết bị hoạt động bình thường',
                'status' => 'completed',
                'created_by' => $user->id,
                'devices' => [
                    [
                        'device_code' => 'DEV002',
                        'device_name' => 'Cảm biến nhiệt độ',
                        'device_serial' => 'SN002233',
                        'device_status' => 'selected',
                        'device_notes' => 'Đã thay thế cảm biến hỏng'
                    ]
                ]
            ],
            [
                'repair_code' => 'SC202406003',
                'warranty_code' => 'BH202406003',
                'repair_type' => 'replacement',
                'repair_date' => Carbon::now()->subDays(2),
                'technician_id' => $employee->id,
                'warehouse_id' => 3,
                'repair_description' => 'Thay thế màn hình LCD bị hỏng',
                'repair_notes' => 'Đang chờ linh kiện thay thế từ nhà cung cấp',
                'status' => 'in_progress',
                'created_by' => $user->id,
                'devices' => [
                    [
                        'device_code' => 'DEV003',
                        'device_name' => 'Màn hình giám sát',
                        'device_serial' => 'SN003344',
                        'device_status' => 'selected',
                        'device_notes' => 'Màn hình bị nứt, cần thay thế'
                    ]
                ]
            ],
            [
                'repair_code' => 'SC202406004',
                'warranty_code' => null,
                'repair_type' => 'upgrade',
                'repair_date' => Carbon::now()->addDays(1),
                'technician_id' => $employee->id,
                'warehouse_id' => 1,
                'repair_description' => 'Nâng cấp firmware và phần mềm điều khiển',
                'repair_notes' => 'Lên lịch nâng cấp vào tuần tới',
                'status' => 'in_progress',
                'created_by' => $user->id,
                'devices' => [
                    [
                        'device_code' => 'DEV004',
                        'device_name' => 'Bộ xử lý trung tâm',
                        'device_serial' => 'SN004455',
                        'device_status' => 'selected',
                        'device_notes' => 'Cần nâng cấp firmware phiên bản mới nhất'
                    ]
                ]
            ]
        ];

        foreach ($repairs as $repairData) {
            $devices = $repairData['devices'];
            unset($repairData['devices']);

            $repair = Repair::create($repairData);

            // Create repair items
            foreach ($devices as $deviceData) {
                RepairItem::create(array_merge($deviceData, ['repair_id' => $repair->id]));
            }
        }
    }
}
