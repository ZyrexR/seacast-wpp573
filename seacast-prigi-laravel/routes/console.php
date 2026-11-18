<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Automated Live Zero-Dummy Data Synchronization & Ensemble SDM Inference Pipeline
 * Orchestrates: Live BMKG Maritim APIs + GEE Satellite Extraction + Model Inference + Cache Warming
 * Executes automatically every morning at 04:00 WIB (Asia/Jakarta)
 */
Schedule::command('seacast:sync-live')
    ->dailyAt('04:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/seacast_sync_live.log'));
