<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote');

Schedule::command('subscriptions:process')
    ->hourly()
    ->withoutOverlapping(10)
    ->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::command('rates:fetch')
    ->twiceDaily(7, 22)
    ->withoutOverlapping(10)
    ->appendOutputTo(storage_path('logs/scheduler.log'));
