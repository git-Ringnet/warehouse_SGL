<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\UserLogSeeder;

class SeedUserLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-user-logs {count=200 : Number of log records to generate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tạo dữ liệu mẫu cho nhật ký người dùng';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Bắt đầu tạo dữ liệu mẫu cho nhật ký người dùng...');
        
        // Chạy seeder
        $seeder = new UserLogSeeder();
        $seeder->setCommand($this);
        $seeder->run();
        
        $this->info('Hoàn thành tạo dữ liệu mẫu cho nhật ký người dùng.');
        
        return Command::SUCCESS;
    }
} 