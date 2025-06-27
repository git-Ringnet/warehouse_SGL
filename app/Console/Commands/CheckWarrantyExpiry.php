<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\Notification;
use Carbon\Carbon;

class CheckWarrantyExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warranty:check-expiry {--days=30 : Số ngày trước khi hết hạn để thông báo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm tra và thông báo các dự án sắp hết hạn bảo hành';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $daysBeforeExpiry = (int) $this->option('days');
        $expiryDate = Carbon::now()->addDays($daysBeforeExpiry);
        
        $this->info("Đang kiểm tra các dự án hết hạn bảo hành trong {$daysBeforeExpiry} ngày tới...");
        
        // Lấy các dự án có ngày kết thúc bảo hành trong khoảng thời gian này
        $projects = Project::where('employee_id', '!=', null)
            ->get()
            ->filter(function ($project) use ($expiryDate) {
                // Tính ngày kết thúc bảo hành = ngày bắt đầu + thời gian bảo hành
                $warrantyEndDate = Carbon::parse($project->start_date)->addMonths((int) $project->warranty_period);
                return $warrantyEndDate->lte($expiryDate) && $warrantyEndDate->gte(Carbon::now());
            });
        
        $notificationCount = 0;
        
        foreach ($projects as $project) {
            $warrantyEndDate = Carbon::parse($project->start_date)->addMonths((int) $project->warranty_period);
            $daysUntilExpiry = Carbon::now()->diffInDays($warrantyEndDate, false);
            
            // Kiểm tra xem đã thông báo chưa (tránh spam)
            $existingNotification = Notification::where('user_id', $project->employee_id)
                ->where('related_type', 'project')
                ->where('related_id', $project->id)
                ->where('title', 'like', '%hết hạn bảo hành%')
                ->where('created_at', '>=', Carbon::now()->subDays(7)) // Chỉ thông báo 1 lần trong 7 ngày
                ->first();
            
            if (!$existingNotification) {
                $notificationType = $daysUntilExpiry <= 7 ? 'error' : ($daysUntilExpiry <= 30 ? 'warning' : 'info');
                $message = $daysUntilExpiry <= 0 
                    ? "Dự án #{$project->project_code} đã hết hạn bảo hành!"
                    : "Dự án #{$project->project_code} sẽ hết hạn bảo hành trong {$daysUntilExpiry} ngày.";
                
                Notification::createNotification(
                    'Dự án sắp hết hạn bảo hành',
                    $message,
                    $notificationType,
                    $project->employee_id,
                    'project',
                    $project->id,
                    route('projects.show', $project->id)
                );
                
                $notificationCount++;
                $this->info("Đã thông báo cho dự án: {$project->project_code} - {$project->project_name}");
            }
        }
        
        $this->info("Hoàn thành! Đã gửi {$notificationCount} thông báo.");
        
        return 0;
    }
}
