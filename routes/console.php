<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Email rent reminders every morning (run "php artisan schedule:work" to keep the scheduler going locally)
Schedule::command('rent:send-reminders')->dailyAt('08:00');
