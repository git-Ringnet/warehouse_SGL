<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Lệnh kiểm tra và gửi thông báo cho các dự án sắp hết hạn bảo hành
        $schedule->command('project:notify-warranty-expiration 90')->daily(); // Thông báo dự án sắp hết hạn trong 90 ngày
        $schedule->command('project:notify-warranty-expiration 30')->daily(); // Thông báo dự án sắp hết hạn trong 30 ngày
        $schedule->command('project:notify-warranty-expiration 7')->daily(); // Thông báo dự án sắp hết hạn trong 7 ngày
        $schedule->command('project:notify-warranty-expiration 1')->daily(); // Thông báo dự án sắp hết hạn trong 1 ngày
        
        // Kiểm tra trạng thái bảo hành hàng ngày
        $schedule->command('warranty:check-status')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
} 