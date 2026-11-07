<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\PublicPageController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\FleetAnalyticsController;
use App\Http\Controllers\Web\TripPlannerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Public Portal and Operational Dashboard Routes
|
*/

// Public Portal Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [PublicPageController::class, 'about'])->name('about');
Route::get('/science', [PublicPageController::class, 'science'])->name('science');
Route::get('/science/dynamic-ocean-management', [PublicPageController::class, 'scienceDom'])->name('science.dom');
Route::get('/science/methodology', [PublicPageController::class, 'scienceMethodology'])->name('science.methodology');
Route::get('/science/prediction-models', [PublicPageController::class, 'scienceModels'])->name('science.models');
Route::get('/documentation', [PublicPageController::class, 'documentation'])->name('documentation');
Route::get('/design-system', [PublicPageController::class, 'designSystem'])->name('design-system');
Route::get('/ocean-data', [PublicPageController::class, 'oceanData'])->name('ocean-data');
Route::get('/platform', [PublicPageController::class, 'platform'])->name('platform');
Route::get('/platform/data-sources', [PublicPageController::class, 'platformDataSources'])->name('platform.data-sources');
Route::get('/platform/features', [PublicPageController::class, 'platformFeatures'])->name('platform.features');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Operational Dashboard Routes
Route::middleware('auth')->prefix('dashboard')->name('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/system-status', [DashboardController::class, 'systemStatus'])->name('.system-status')->middleware('role:admin');

    // 1. Radar Geospasial Terpadu WPPNRI 573
    Route::get('/dpi-radar', [DashboardController::class, 'dpiRadar'])->name('.dpi-radar');

    // 2. Rencana Melaut & Trip Planner
    Route::get('/trip-planner', [TripPlannerController::class, 'index'])->name('.trip-planner');
    Route::post('/trip-planner/calculate', [TripPlannerController::class, 'calculate'])->name('.trip-planner.calculate');
    Route::post('/trip-planner', [TripPlannerController::class, 'store'])->name('.trip-planner.store');
    Route::get('/trip-planner/{id}/pdf', [TripPlannerController::class, 'downloadPdf'])->name('.trip-planner.pdf');

    // 3. Analitik Armada & Pendaratan Multi-Pelabuhan
    Route::get('/fleet-analytics', [FleetAnalyticsController::class, 'index'])->name('.fleet-analytics');
    Route::get('/cpue-analysis', [FleetAnalyticsController::class, 'index'])->name('.cpue-analysis');
});
