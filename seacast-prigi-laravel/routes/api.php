<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MonitoringApiController;
use App\Http\Controllers\Api\PredictionApiController;
use App\Http\Controllers\Api\GridApiController;
use App\Http\Controllers\Api\TileApiController;
use App\Http\Controllers\Api\FleetApiController;
use App\Http\Controllers\Api\ExportApiController;
use App\Http\Controllers\Api\TimeMachineApiController;
use App\Http\Controllers\Api\SimulationApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| All routes are rate-limited to protect against abuse and credential probing.
|
*/

// Vector Tiles Endpoint (Mapbox Vector Tile format for MapLibre WebGL client)
Route::middleware('throttle:600,1')->group(function () {
    Route::get('/tiles/{layer}/{z}/{x}/{y}.pbf', [TileApiController::class, 'getTile'])
        ->where(['z' => '[0-9]+', 'x' => '[0-9]+', 'y' => '[0-9]+'])
        ->name('api.tiles');
});

Route::middleware('throttle:60,1')->group(function () {
    // Core Radar & Prediction Endpoints
    Route::get('/predictions', [PredictionApiController::class, 'index'])->name('api.predictions');
    Route::get('/grid', [GridApiController::class, 'index'])->name('api.grid');

    // Fleet & Landing Analytics Endpoints (Phase 3.3)
    Route::get('/fleet/composition', [FleetApiController::class, 'composition'])->name('api.fleet.composition');
    Route::get('/fleet/annual-landings', [FleetApiController::class, 'annualLandings'])->name('api.fleet.annual');
    Route::get('/fleet/seasonality', [FleetApiController::class, 'seasonality'])->name('api.fleet.seasonality');

    // Scientific Export Engine Endpoints (Phase 3.4)
    Route::get('/export/geojson', [ExportApiController::class, 'geojson'])->name('api.export.geojson');
    Route::get('/export/csv', [ExportApiController::class, 'csv'])->name('api.export.csv');
    Route::get('/export/gis-package', [ExportApiController::class, 'gisPackage'])->name('api.export.gis_package');

    // Temporal & Simulation Endpoints (Phase 4.1)
    Route::get('/timemachine', [TimeMachineApiController::class, 'index'])->name('api.timemachine');
    Route::post('/simulate', [SimulationApiController::class, 'simulate'])->name('api.simulate');

    // Public Health & Pipeline Telemetry Endpoints
    Route::get('/monitoring', [MonitoringApiController::class, 'index'])->name('api.monitoring');
    Route::get('/monitoring/pipeline', [MonitoringApiController::class, 'pipeline'])->name('api.monitoring.pipeline');
    Route::get('/bmkg/live', [MonitoringApiController::class, 'bmkgLive'])->name('api.bmkg.live');

    // Protected Diagnostic Endpoint (Requires auth session & admin role)
    Route::get('/monitoring/diagnostics', [MonitoringApiController::class, 'diagnostics'])->name('api.monitoring.diagnostics');
});
