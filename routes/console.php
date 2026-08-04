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

Schedule::command('billing:purge-expired-writer-data')
    ->dailyAt('04:00')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('sitemap:generate')
    ->dailyAt('03:10')
    ->withoutOverlapping();

Schedule::command('seo:audit --limit=100')
    ->dailyAt('03:30')
    ->withoutOverlapping();
