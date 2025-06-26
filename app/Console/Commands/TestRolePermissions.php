<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Console\Command;

class TestRolePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:role-permissions {--setup : Setup test data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the role and permission system for admin-only access';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('setup')) {
            $this->setupTestData();
            return 0;
        }

        $this->info('🔍 Testing Role Permission System...');
        $this->newLine();

        // Test 1: Check if admin exists
        $this->testAdminExists();

        // Test 2: Check roles and permissions
        $this->testRolesAndPermissions();

        // Test 3: Test admin permissions
        $this->testAdminPermissions();

        // Test 4: Test regular user permissions
        $this->testRegularUserPermissions();

        $this->newLine();
        $this->info('✅ All tests completed!');

        return 0;
    }

    private function setupTestData()
    {
        $this->info('🚀 Setting up test data...');

        // Seed permissions
        $this->call('db:seed', ['--class' => 'PermissionSeeder']);
        
        // Seed roles
        $this->call('db:seed', ['--class' => 'RoleSeeder']);
        
        // Seed admin
        $this->call('db:seed', ['--class' => 'AdminSeeder']);

        $this->info('✅ Test data setup completed!');
    }

    private function testAdminExists()
    {
        $this->info('1️⃣ Testing admin account...');

        $admin = Employee::where('role', 'admin')->first();
        
        if ($admin) {
            $this->line("   ✅ Admin account found: {$admin->username} ({$admin->name})");
            
            if ($admin->is_active) {
                $this->line("   ✅ Admin account is active");
            } else {
                $this->error("   ❌ Admin account is inactive");
            }
        } else {
            $this->error("   ❌ No admin account found");
            $this->warn("   💡 Run: php artisan test:role-permissions --setup");
        }
    }

    private function testRolesAndPermissions()
    {
        $this->info('2️⃣ Testing roles and permissions...');

        $rolesCount = Role::count();
        $permissionsCount = Permission::count();

        $this->line("   📊 Found {$rolesCount} roles and {$permissionsCount} permissions");

        // Check for specific role management permissions
        $rolePermissions = Permission::where('group', 'Phân quyền')->get();
        
        if ($rolePermissions->count() > 0) {
            $this->line("   ✅ Role management permissions found:");
            foreach ($rolePermissions as $permission) {
                $this->line("      - {$permission->display_name}");
            }
        } else {
            $this->warn("   ⚠️ No role management permissions found");
        }

        // Check Super Admin role
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $adminPermissionCount = $superAdmin->permissions()->count();
            $this->line("   ✅ Super Admin role has {$adminPermissionCount} permissions");
        } else {
            $this->warn("   ⚠️ Super Admin role not found");
        }
    }

    private function testAdminPermissions()
    {
        $this->info('3️⃣ Testing admin permissions...');

        $admin = Employee::where('role', 'admin')->first();
        
        if (!$admin) {
            $this->error("   ❌ No admin account to test");
            return;
        }

        // Test role management permissions
        $rolePermissions = [
            'roles.view' => 'View roles',
            'roles.create' => 'Create roles',
            'roles.edit' => 'Edit roles',
            'roles.delete' => 'Delete roles',
            'permissions.view' => 'View permissions',
            'permissions.manage' => 'Manage permissions'
        ];

        foreach ($rolePermissions as $permission => $description) {
            if ($admin->hasPermission($permission)) {
                $this->line("   ✅ Admin can: {$description}");
            } else {
                $this->warn("   ⚠️ Admin missing: {$description}");
            }
        }
    }

    private function testRegularUserPermissions()
    {
        $this->info('4️⃣ Testing regular user permissions...');

        // Create a test regular user
        $regularUser = Employee::where('role', '!=', 'admin')->first();
        
        if (!$regularUser) {
            $this->warn("   ⚠️ No regular user found to test");
            return;
        }

        $this->line("   👤 Testing user: {$regularUser->username} ({$regularUser->role})");

        // Test role management permissions (should be false)
        $rolePermissions = [
            'roles.view' => 'View roles',
            'roles.create' => 'Create roles',
            'roles.edit' => 'Edit roles',
            'roles.delete' => 'Delete roles'
        ];

        $hasAnyRolePermission = false;
        foreach ($rolePermissions as $permission => $description) {
            if ($regularUser->hasPermission($permission)) {
                $this->error("   ❌ Regular user should NOT have: {$description}");
                $hasAnyRolePermission = true;
            }
        }

        if (!$hasAnyRolePermission) {
            $this->line("   ✅ Regular user correctly has NO role management permissions");
        }
    }
}
