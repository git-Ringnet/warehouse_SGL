<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
            EmployeeSeeder::class,
            WarehouseSeeder::class,
            MaterialSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            UserLogSeeder::class,
            SoftwareSeeder::class,
        ]);
    }
}
