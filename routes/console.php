<?php

use App\Jobs\ReportNotificationJob;
use App\Jobs\StockNotificationJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule low stock notifications
Schedule::job(new StockNotificationJob)->everyFifteenMinutes();
// Schedule daily sales report
Schedule::job(new ReportNotificationJob)->dailyAt('22:00');
