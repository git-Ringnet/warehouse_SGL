<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProjectObserver
{
    /**
     * Handle the Project "created" event.
     */
    public function created(Project $project): void
    {
        $this->checkWarrantyStatus($project);
    }

    /**
     * Handle the Project "updated" event.
     */
    public function updated(Project $project): void
    {
        // Kiểm tra xem có thay đổi về ngày bắt đầu hoặc thời gian bảo hành không
        if ($project->wasChanged('start_date') || $project->wasChanged('warranty_period')) {
            $this->checkWarrantyStatus($project);
        }
    }

    /**
     * Handle the Project "deleted" event.
     */
    public function deleted(Project $project): void
    {
        //
    }

    /**
     * Handle the Project "restored" event.
     */
    public function restored(Project $project): void
    {
        //
    }

    /**
     * Handle the Project "force deleted" event.
     */
    public function forceDeleted(Project $project): void
    {
        //
    }
    
    /**
     * Kiểm tra trạng thái bảo hành của dự án và gửi thông báo nếu cần
     */
    protected function checkWarrantyStatus(Project $project): void
    {
        // Chỉ thực hiện nếu dự án có người phụ trách
        if (!$project->employee_id) {
            return;
        }
        
        // Lấy ngày hiện tại
        $today = Carbon::today();
        
        // Lấy ngày bắt đầu dự án
        $startDate = Carbon::parse($project->start_date);
        
        // Đảm bảo warranty_period là số nguyên
        $warrantyPeriod = (int)$project->warranty_period;
        
        // Tính ngày kết thúc bảo hành
        $warrantyEndDate = $startDate->copy()->addMonths($warrantyPeriod);
        
        // Kiểm tra trạng thái bảo hành
        $daysUntilExpiration = $today->diffInDays($warrantyEndDate, false);
        
        Log::info("Checking warranty status for project {$project->project_code}", [
            'project_id' => $project->id,
            'days_until_expiration' => $daysUntilExpiration,
            'warranty_end_date' => $warrantyEndDate->format('Y-m-d')
        ]);
        
        // Gửi thông báo dựa trên số ngày còn lại
        if ($daysUntilExpiration < 0) {
            // Đã hết hạn bảo hành
            if ($daysUntilExpiration >= -1) {
                // Dự án vừa hết hạn bảo hành (0 đến 1 ngày)
                $this->sendWarrantyExpiredNotification($project);
            }
        } else {
            // Còn hạn bảo hành, kiểm tra các mốc cần thông báo
            if ($daysUntilExpiration == 0) {
                // Dự án hết hạn bảo hành ngày hôm nay
                $this->sendWarrantyNotification($project, 0);
            } elseif ($daysUntilExpiration == 1) {
                // Còn 1 ngày nữa sẽ hết hạn
                $this->sendWarrantyNotification($project, 1);
            } elseif ($daysUntilExpiration == 7) {
                // Còn 7 ngày nữa sẽ hết hạn
                $this->sendWarrantyNotification($project, 7);
            } elseif ($daysUntilExpiration == 30) {
                // Còn 30 ngày nữa sẽ hết hạn
                $this->sendWarrantyNotification($project, 30);
            } elseif ($daysUntilExpiration == 90) {
                // Còn 90 ngày nữa sẽ hết hạn
                $this->sendWarrantyNotification($project, 90);
            }
        }
    }
    
    /**
     * Gửi thông báo về việc sắp hết hạn bảo hành
     */
    protected function sendWarrantyNotification(Project $project, int $days): void
    {
        // Xác định mức độ ưu tiên của thông báo dựa trên số ngày còn lại
        $type = 'info';
        if ($days <= 7) {
            $type = 'warning';
        }
        if ($days <= 1) {
            $type = 'error';
        }
        
        // Tạo tiêu đề thông báo
        $title = "Dự án sắp hết hạn bảo hành";
        
        // Tạo nội dung thông báo
        $message = "Dự án #{$project->project_code} {$project->project_name} sẽ hết hạn bảo hành trong {$days} ngày nữa.";
        
        // Kiểm tra xem đã có thông báo tương tự trong 24 giờ qua chưa
        $existingNotification = Notification::where('user_id', $project->employee_id)
            ->where('title', $title)
            ->where('message', $message)
            ->where('created_at', '>=', now()->subDay())
            ->first();
            
        if ($existingNotification) {
            Log::info("Skipping duplicate notification for project {$project->project_code} - {$days} days");
            return;
        }
        
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
            
            Log::info("Sent warranty notification for project {$project->project_code} - {$days} days");
        } catch (\Exception $e) {
            Log::error("Error sending warranty notification for project {$project->project_code}: " . $e->getMessage());
        }
    }
    
    /**
     * Gửi thông báo về việc đã hết hạn bảo hành
     */
    protected function sendWarrantyExpiredNotification(Project $project): void
    {
        // Tạo tiêu đề thông báo
        $title = "Dự án đã hết hạn bảo hành";
        
        // Tạo nội dung thông báo
        $message = "Dự án #{$project->project_code} {$project->project_name} đã hết hạn bảo hành.";
        
        // Kiểm tra xem đã có thông báo tương tự trong 24 giờ qua chưa
        $existingNotification = Notification::where('user_id', $project->employee_id)
            ->where('title', $title)
            ->where('message', $message)
            ->where('created_at', '>=', now()->subDay())
            ->first();
            
        if ($existingNotification) {
            Log::info("Skipping duplicate expired notification for project {$project->project_code}");
            return;
        }
        
        try {
            // Tạo thông báo
            Notification::createNotification(
                $title,
                $message,
                'error',
                $project->employee_id,
                'project',
                $project->id,
                route('projects.show', $project->id)
            );
            
            Log::info("Sent warranty expired notification for project {$project->project_code}");
        } catch (\Exception $e) {
            Log::error("Error sending warranty expired notification for project {$project->project_code}: " . $e->getMessage());
        }
    }
}
