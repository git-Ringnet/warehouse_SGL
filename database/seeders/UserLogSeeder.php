<?php

namespace Database\Seeders;

use App\Models\UserLog;
use App\Models\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Faker\Factory as Faker;

class UserLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('vi_VN');
        
        // Get all employees
        $employees = Employee::all();
        
        if ($employees->isEmpty()) {
            $this->command->warn('Không có nhân viên nào trong hệ thống. Hãy chạy EmployeeSeeder trước.');
            return;
        }
        
        // Sample modules
        $modules = [
            'roles', 'permissions', 'employees', 'warehouses', 'products',
            'materials', 'inventory', 'suppliers', 'customers', 'requests',
            'projects', 'assemble', 'warranty', 'settings', 'reports'
        ];
        
        // Sample actions
        $actions = ['login', 'logout', 'create', 'update', 'delete', 'view', 'export', 'import'];
        
        // Sample IP addresses
        $ipAddresses = [
            '192.168.1.' . $faker->numberBetween(2, 254),
            '10.0.0.' . $faker->numberBetween(2, 254),
            '172.16.0.' . $faker->numberBetween(2, 254),
            $faker->ipv4(),
        ];
        
        // Sample user agents
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Safari/605.1.15',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.212 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1'
        ];
        
        // Set default log count
        $logCount = 200;
        
        // Create sample logs
        $userLogs = [];
        
        for ($i = 0; $i < $logCount; $i++) {
            $employee = $employees->random();
            $action = $faker->randomElement($actions);
            $module = $faker->randomElement($modules);
            $createdAt = $faker->dateTimeBetween('-3 months', 'now');
            
            // Generate appropriate log data based on action and module
            $description = $this->generateDescription($action, $module, $employee->name);
            $oldData = null;
            $newData = null;
            
            // For update and delete actions, create some sample data
            if ($action === 'update' || $action === 'delete') {
                $oldData = $this->generateSampleData($module);
                
                if ($action === 'update') {
                    $newData = $this->updateSampleData($oldData);
                }
            } 
            // For create action, only have new data
            elseif ($action === 'create') {
                $newData = $this->generateSampleData($module);
            }
            
            // Create the log entry
            $userLogs[] = [
                'user_id' => $employee->id,
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'old_data' => $oldData ? json_encode($oldData) : null,
                'new_data' => $newData ? json_encode($newData) : null,
                'ip_address' => $faker->randomElement($ipAddresses),
                'user_agent' => $faker->randomElement($userAgents),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
            
            // Insert in chunks of 50 to avoid memory issues
            if (count($userLogs) >= 50) {
                UserLog::insert($userLogs);
                $userLogs = [];
                if (isset($this->command)) {
                    $this->command->info("Đã tạo " . ($i + 1) . " bản ghi...");
                }
            }
        }
        
        // Insert any remaining logs
        if (!empty($userLogs)) {
            UserLog::insert($userLogs);
        }
        
        if (isset($this->command)) {
            $this->command->info('Đã tạo ' . $logCount . ' bản ghi nhật ký người dùng thành công!');
        }
    }
    
    /**
     * Generate appropriate description based on action and module
     */
    private function generateDescription($action, $module, $employeeName)
    {
        $singularModule = rtrim($module, 's'); // Remove trailing 's' for singular form
        
        $descriptions = [
            'login' => [
                "$employeeName đã đăng nhập vào hệ thống",
                "Đăng nhập thành công vào hệ thống",
                "Người dùng $employeeName đăng nhập"
            ],
            'logout' => [
                "$employeeName đã đăng xuất khỏi hệ thống",
                "Đăng xuất khỏi phiên làm việc",
                "Người dùng $employeeName đăng xuất"
            ],
            'create' => [
                "Tạo mới $singularModule: {ID}",
                "Thêm $singularModule mới vào hệ thống",
                "Đã tạo bản ghi $singularModule {NAME}"
            ],
            'update' => [
                "Cập nhật thông tin $singularModule: {ID}",
                "Chỉnh sửa $singularModule {NAME}",
                "Thay đổi thông tin $singularModule {ID}"
            ],
            'delete' => [
                "Xóa $singularModule: {ID}",
                "Đã xóa bản ghi $singularModule {NAME}",
                "Xóa $singularModule khỏi hệ thống"
            ],
            'view' => [
                "Xem chi tiết $singularModule: {ID}",
                "Truy cập thông tin $singularModule {NAME}",
                "Xem danh sách $module"
            ],
            'export' => [
                "Xuất dữ liệu $module ra file",
                "Xuất báo cáo $module",
                "Tải xuống danh sách $module"
            ],
            'import' => [
                "Nhập dữ liệu $module từ file",
                "Import danh sách $module",
                "Cập nhật hàng loạt $module từ file"
            ]
        ];
        
        $description = $descriptions[$action][array_rand($descriptions[$action])];
        
        // Replace placeholders with some values if needed
        $description = str_replace(['{ID}', '{NAME}'], ['#' . rand(1, 999), 'ITEM-' . rand(100, 999)], $description);
        
        return $description;
    }
    
    /**
     * Generate sample data based on module
     */
    private function generateSampleData($module)
    {
        $faker = Faker::create('vi_VN');
        
        $sampleData = [];
        
        switch($module) {
            case 'employees':
                $sampleData = [
                    'id' => $faker->randomNumber(3),
                    'username' => $faker->userName(),
                    'name' => $faker->name(),
                    'email' => $faker->email(),
                    'phone' => $faker->phoneNumber(),
                    'address' => $faker->address(),
                    'role' => $faker->randomElement(['admin', 'manager', 'staff', 'tech']),
                    'status' => $faker->randomElement(['active', 'inactive', 'leave']),
                ];
                break;
                
            case 'products':
                $sampleData = [
                    'id' => $faker->randomNumber(3),
                    'code' => 'PRD' . $faker->randomNumber(5),
                    'name' => $faker->words(3, true),
                    'category' => $faker->randomElement(['Điện tử', 'Cơ khí', 'Tự động hóa', 'Điện']),
                    'price' => $faker->numberBetween(100000, 9999999),
                    'quantity' => $faker->numberBetween(1, 100),
                ];
                break;
                
            case 'customers':
                $sampleData = [
                    'id' => $faker->randomNumber(3),
                    'code' => 'CUS' . $faker->randomNumber(5),
                    'name' => $faker->company(),
                    'contact_person' => $faker->name(),
                    'phone' => $faker->phoneNumber(),
                    'email' => $faker->companyEmail(),
                    'address' => $faker->address(),
                ];
                break;
                
            case 'suppliers':
                $sampleData = [
                    'id' => $faker->randomNumber(3),
                    'code' => 'SUP' . $faker->randomNumber(5),
                    'name' => $faker->company(),
                    'contact_person' => $faker->name(),
                    'phone' => $faker->phoneNumber(),
                    'email' => $faker->companyEmail(),
                    'address' => $faker->address(),
                ];
                break;
                
            case 'warehouses':
                $sampleData = [
                    'id' => $faker->randomNumber(2),
                    'code' => 'WH' . $faker->randomNumber(3),
                    'name' => 'Kho ' . $faker->city(),
                    'address' => $faker->address(),
                    'manager' => $faker->name(),
                    'phone' => $faker->phoneNumber(),
                    'email' => $faker->email(),
                ];
                break;
                
            case 'roles':
                $sampleData = [
                    'id' => $faker->randomNumber(2),
                    'name' => 'Role ' . $faker->word(),
                    'description' => $faker->sentence(),
                    'permissions' => [1, 2, 3, 5, 8],
                    'is_active' => true,
                ];
                break;
                
            default:
                // Generic data for other modules
                $sampleData = [
                    'id' => $faker->randomNumber(3),
                    'name' => $faker->words(2, true),
                    'description' => $faker->sentence(),
                    'created_by' => $faker->randomNumber(2),
                    'status' => $faker->randomElement(['active', 'inactive', 'draft', 'completed']),
                    'created_at' => $faker->dateTimeThisMonth()->format('Y-m-d H:i:s'),
                ];
        }
        
        return $sampleData;
    }
    
    /**
     * Update sample data with some changes
     */
    private function updateSampleData($oldData)
    {
        $faker = Faker::create('vi_VN');
        $newData = $oldData;
        
        // Modify 2-3 random fields
        $fields = array_keys($oldData);
        shuffle($fields);
        $fieldsToUpdate = array_slice($fields, 0, rand(2, 3));
        
        foreach ($fieldsToUpdate as $field) {
            switch ($field) {
                case 'name':
                case 'description':
                    $newData[$field] = $faker->words(3, true);
                    break;
                    
                case 'email':
                    $newData[$field] = $faker->email();
                    break;
                    
                case 'phone':
                    $newData[$field] = $faker->phoneNumber();
                    break;
                    
                case 'address':
                    $newData[$field] = $faker->address();
                    break;
                    
                case 'price':
                case 'quantity':
                    $newData[$field] = $oldData[$field] + rand(-10, 20);
                    break;
                    
                case 'status':
                    $statuses = ['active', 'inactive', 'draft', 'completed', 'pending'];
                    $newStatus = $faker->randomElement($statuses);
                    while ($newStatus === $oldData[$field]) {
                        $newStatus = $faker->randomElement($statuses);
                    }
                    $newData[$field] = $newStatus;
                    break;
                    
                default:
                    // For other fields, just randomize
                    if (is_numeric($oldData[$field])) {
                        $newData[$field] = $oldData[$field] + rand(-5, 10);
                    } elseif (is_string($oldData[$field])) {
                        $newData[$field] = $faker->word() . ' ' . $oldData[$field];
                    } elseif (is_array($oldData[$field])) {
                        $newData[$field] = array_merge($oldData[$field], [rand(10, 20)]);
                    }
            }
        }
        
        return $newData;
    }

    /**
     * Set the console command instance.
     */
    public function setCommand($command)
    {
        $this->command = $command;
        return $this;
    }
} 