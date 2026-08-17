<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote');

Schedule::command('subscriptions:process')
    ->hourly()
    ->withoutOverlapping(10);

Schedule::command('rates:fetch')
    ->twiceDaily(7, 22)          // 22:00 WIB ≈ abis ECB publish 16:00 CET
    ->withoutOverlapping(10);
