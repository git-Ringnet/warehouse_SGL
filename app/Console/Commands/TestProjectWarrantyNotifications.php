<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Observers\ProjectObserver;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestProjectWarrantyNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:test-warranty-notifications {project_code? : Mã dự án cần test, để trống để test tất cả}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test thông báo khi dự án hết hạn bảo hành';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $projectCode = $this->argument('project_code');
        
        // Observer để kiểm tra và gửi thông báo
        $observer = new ProjectObserver();
        
        if ($projectCode) {
            // Test một dự án cụ thể
            $project = Project::where('project_code', $projectCode)->first();
            
            if (!$project) {
                $this->error("Không tìm thấy dự án với mã: {$projectCode}");
                return 1;
            }
            
            $this->testProjectWarranty($project, $observer);
        } else {
            // Test tất cả các dự án
            $projects = Project::all();
            
            if ($projects->isEmpty()) {
                $this->info("Không có dự án nào để test");
                return 0;
            }
            
            $this->info("Đang test {$projects->count()} dự án...");
            
            foreach ($projects as $project) {
                $this->testProjectWarranty($project, $observer);
            }
        }
        
        $this->info("Hoàn thành test thông báo dự án");
        return 0;
    }
    
    /**
     * Test thông báo cho một dự án cụ thể
     */
    private function testProjectWarranty(Project $project, ProjectObserver $observer)
    {
        $this->info("Testing dự án: {$project->project_code} - {$project->project_name}");
        
        if (!$project->employee_id) {
            $this->warn("  Dự án không có nhân viên phụ trách, bỏ qua");
            return;
        }
        
        // Lấy thông tin bảo hành
        $startDate = Carbon::parse($project->start_date);
        $warrantyEndDate = $startDate->copy()->addMonths($project->warranty_period);
        $today = Carbon::today();
        $daysUntilExpiration = $today->diffInDays($warrantyEndDate, false);
        
        $this->line("  Ngày bắt đầu: {$startDate->format('Y-m-d')}");
        $this->line("  Thời gian bảo hành: {$project->warranty_period} tháng");
        $this->line("  Ngày hết hạn: {$warrantyEndDate->format('Y-m-d')}");
        
        if ($daysUntilExpiration < 0) {
            $this->warn("  Dự án đã hết hạn bảo hành " . abs($daysUntilExpiration) . " ngày trước");
        } elseif ($daysUntilExpiration == 0) {
            $this->warn("  Dự án hết hạn bảo hành hôm nay");
        } else {
            $this->info("  Dự án còn {$daysUntilExpiration} ngày nữa hết hạn bảo hành");
        }
        
        // Gọi phương thức protected của observer thông qua Reflection API
        $this->callProtectedMethod($observer, 'checkWarrantyStatus', [$project]);
        
        // Thêm khoảng cách giữa các dự án
        $this->newLine();
    }
    
    /**
     * Gọi phương thức protected thông qua Reflection API
     */
    private function callProtectedMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        
        return $method->invokeArgs($object, $parameters);
    }
}
