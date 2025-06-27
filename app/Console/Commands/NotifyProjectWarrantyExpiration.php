<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NotifyProjectWarrantyExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:notify-warranty-expiration {days=30 : Số ngày trước khi hết hạn bảo hành để thông báo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm tra và gửi thông báo cho các dự án sắp hết hạn bảo hành';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $daysBeforeExpiration = (int) $this->argument('days');
        
        // Kiểm tra nếu người dùng không cung cấp số ngày hợp lệ
        if ($daysBeforeExpiration <= 0) {
            $this->error('Số ngày phải lớn hơn 0');
            return 1;
        }
        
        $this->info("Đang kiểm tra các dự án sắp hết hạn bảo hành trong {$daysBeforeExpiration} ngày tới...");
        
        // Lấy danh sách các dự án sắp hết hạn bảo hành
        $projects = $this->getProjectsWithExpiringWarranty($daysBeforeExpiration);
        
        if ($projects->isEmpty()) {
            $this->info("Không có dự án nào sắp hết hạn bảo hành trong {$daysBeforeExpiration} ngày tới.");
            return 0;
        }
        
        $this->info("Tìm thấy {$projects->count()} dự án sắp hết hạn bảo hành.");
        
        // Gửi thông báo cho từng dự án
        $notificationCount = 0;
        foreach ($projects as $project) {
            if ($this->sendNotification($project, $daysBeforeExpiration)) {
                $notificationCount++;
            }
        }
        
        $this->info("Đã gửi {$notificationCount} thông báo.");
        return 0;
    }
    
    /**
     * Lấy danh sách các dự án sắp hết hạn bảo hành trong số ngày chỉ định
     */
    private function getProjectsWithExpiringWarranty(int $days): \Illuminate\Support\Collection
    {
        // Tính toán ngày hiện tại và ngày hết hạn
        $today = Carbon::today();
        $targetDate = $today->copy()->addDays($days);
        
        $projects = Project::whereNotNull('employee_id')
            ->get()
            ->filter(function ($project) use ($days, $today) {
                // Lấy ngày bắt đầu từ start_date
                $startDate = Carbon::parse($project->start_date);
                
                // Đảm bảo warranty_period là số nguyên
                $warrantyPeriod = (int)$project->warranty_period;
                
                // Tính ngày kết thúc bảo hành bằng cách thêm số tháng bảo hành vào ngày bắt đầu
                $warrantyEndDate = $startDate->copy()->addMonths($warrantyPeriod);
                
                // Chỉ lấy các dự án có bảo hành hết hạn trong chính xác số ngày chỉ định
                $daysUntilExpiration = $today->diffInDays($warrantyEndDate, false);
                
                return $daysUntilExpiration == $days;
            });
            
        return $projects;
    }
    
    /**
     * Gửi thông báo cho nhân viên phụ trách dự án
     */
    private function sendNotification(Project $project, int $days): bool
    {
        if (!$project->employee_id) {
            $this->warn("Dự án {$project->project_code} không có nhân viên phụ trách.");
            return false;
        }
        
        // Xác định mức độ ưu tiên của thông báo dựa trên số ngày còn lại
        $type = 'info';
        if ($days <= 7) {
            $type = 'warning';
        }
        if ($days <= 3) {
            $type = 'error';
        }
        
        // Tạo tiêu đề thông báo
        $title = "Dự án sắp hết hạn bảo hành";
        
        // Tạo nội dung thông báo
        $message = "Dự án #{$project->project_code} {$project->project_name} sẽ hết hạn bảo hành trong {$days} ngày nữa.";
        
        try {
            // Tạo thông báo
            Notification::createNotification(
                $title,
                $message,
                $type,
                $project->employee_id,
                'project',
                $project->id,
                route('projects.show', $project->id)
            );
            
            $this->info("Đã gửi thông báo cho nhân viên phụ trách dự án {$project->project_code}.");
            
            // Ghi log
            Log::info("Đã gửi thông báo hết hạn bảo hành cho dự án {$project->project_code}", [
                'project_id' => $project->id,
                'days_remaining' => $days,
                'employee_id' => $project->employee_id
            ]);
            
            return true;
        } catch (\Exception $e) {
            $this->error("Lỗi khi gửi thông báo cho dự án {$project->project_code}: {$e->getMessage()}");
            
            // Ghi log lỗi
            Log::error("Lỗi khi gửi thông báo hết hạn bảo hành cho dự án {$project->project_code}", [
                'project_id' => $project->id,
                'days_remaining' => $days,
                'employee_id' => $project->employee_id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
}
