<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('monetization:verify-links --limit=200')
    ->dailyAt('03:30')
    ->withoutOverlapping()
    ->onOneServer();
