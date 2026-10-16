<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Optional automatic sending (only if a cPanel cron runs `schedule:run`). Without cron, the admin's
// Email campaigns page sends while it is open. The heartbeat lets that page show whether cron is active.
Schedule::call(fn () => \App\Support\NewsletterSender::heartbeat())->everyMinute()->name('scheduler-heartbeat');
Schedule::command('newsletter:send')->everyMinute()->withoutOverlapping(10);
