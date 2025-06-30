<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\TestingSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Chạy các Seeders
        $this->call([
            CustomerSeeder::class,
            SupplierSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            EmployeeSeeder::class,
            AdminSeeder::class, // Tạo admin sau khi có role
            WarehouseSeeder::class,
            MaterialSeeder::class,
            SoftwareSeeder::class,
            WarehouseMaterialSeeder::class,
            ProductSeeder::class,
            // UserLogSeeder::class,
            // AssemblySeeder::class,
            // DispatchSeeder::class,
            // WarrantySeeder::class,
            TestingSeeder::class,
        ]);
    }
}
