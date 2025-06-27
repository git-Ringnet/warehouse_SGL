<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Customer;
use App\Models\Employee;
use Carbon\Carbon;

class ProjectWarrantyDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy employee đầu tiên
        $employee = Employee::first();
        if (!$employee) return;
        $customer = Customer::first();
        if (!$customer) return;

        $today = Carbon::today();
        $demoProjects = [
            // Hết hạn (ngày kết thúc bảo hành là hôm qua)
            [
                'project_code' => 'PRJ-DEMO-EXPIRED',
                'project_name' => 'Dự án hết hạn',
                'start_date' => $today->copy()->subMonths(12)->subDay(),
                'warranty_period' => 12,
            ],
            // Còn 1 ngày
            [
                'project_code' => 'PRJ-DEMO-1DAY',
                'project_name' => 'Dự án còn 1 ngày',
                'start_date' => $today->copy()->subMonths(12)->addDay(),
                'warranty_period' => 12,
            ],
            // Còn 7 ngày
            [
                'project_code' => 'PRJ-DEMO-7DAY',
                'project_name' => 'Dự án còn 7 ngày',
                'start_date' => $today->copy()->subMonths(12)->addDays(7),
                'warranty_period' => 12,
            ],
            // Còn 30 ngày
            [
                'project_code' => 'PRJ-DEMO-30DAY',
                'project_name' => 'Dự án còn 30 ngày',
                'start_date' => $today->copy()->subMonths(12)->addDays(30),
                'warranty_period' => 12,
            ],
            // Còn 90 ngày
            [
                'project_code' => 'PRJ-DEMO-90DAY',
                'project_name' => 'Dự án còn 90 ngày',
                'start_date' => $today->copy()->subMonths(12)->addDays(90),
                'warranty_period' => 12,
            ],
        ];

        foreach ($demoProjects as $data) {
            Project::updateOrCreate(
                ['project_code' => $data['project_code']],
                [
                    'project_name' => $data['project_name'],
                    'customer_id' => $customer->id,
                    'employee_id' => $employee->id,
                    'start_date' => $data['start_date'],
                    'end_date' => Carbon::parse($data['start_date'])->addMonths($data['warranty_period']),
                    'warranty_period' => $data['warranty_period'],
                    'description' => 'Demo dự án bảo hành',
                ]
            );
        }
    }
}
