<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Automated Scientific Python Ensemble SDM Daily Ingestion & Inference
 * Executes every morning at 04:00 WIB (Asia/Jakarta)
 */
Schedule::command('seacast:infer-daily')
    ->dailyAt('04:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/daily_inference.log'));
