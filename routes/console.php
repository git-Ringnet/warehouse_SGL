<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// NOTE: Để lên lịch cho lệnh thông báo hết hạn bảo hành, thêm đoạn code sau vào App\Console\Kernel.php
// trong phương thức schedule:
/*
$schedule->command('project:notify-warranty-expiration 30')->daily(); // Thông báo dự án sắp hết hạn trong 30 ngày
$schedule->command('project:notify-warranty-expiration 7')->daily(); // Thông báo dự án sắp hết hạn trong 7 ngày
$schedule->command('project:notify-warranty-expiration 1')->daily(); // Thông báo dự án sắp hết hạn trong 1 ngày
*/
