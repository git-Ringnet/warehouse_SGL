<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\Warranty;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckWarrantyStatus extends Command
{
    protected $signature = 'warranty:check-status';
    protected $description = 'Check and update warranty status for all projects and warranties';

    public function handle()
    {
        $this->info('Starting warranty status check...');
        
        // Kiểm tra các dự án hết hạn bảo hành
        $expiredProjects = Project::where('start_date', '<=', now())
            ->get();
            
        foreach ($expiredProjects as $project) {
            if (!$project->has_valid_warranty) {
                // Cập nhật tất cả các warranty liên quan thành expired
                $projectDispatches = $project->dispatches()->pluck('id')->toArray();
                if (!empty($projectDispatches)) {
                    Warranty::whereIn('dispatch_id', $projectDispatches)
                        ->where('status', 'active')
                        ->update(['status' => 'expired']);
                    
                    $this->info("Updated warranties to expired for project: {$project->project_code}");
                    Log::info("Updated warranties to expired for project: {$project->project_code}");
                }
            }
        }

        // Kiểm tra các warranty riêng lẻ đã hết hạn
        $expiredWarranties = Warranty::where('status', 'active')
            ->where('warranty_end_date', '<', now())
            ->get();

        foreach ($expiredWarranties as $warranty) {
            $warranty->update(['status' => 'expired']);
            $this->info("Updated warranty status to expired: {$warranty->warranty_code}");
            Log::info("Updated warranty status to expired: {$warranty->warranty_code}");
        }

        $this->info('Warranty status check completed.');
    }
} 