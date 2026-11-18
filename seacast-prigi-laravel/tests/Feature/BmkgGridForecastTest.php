<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BmkgGridForecastTest extends TestCase
{
    public function test_grid_forecast_samples_nearest_model_point(): void
    {
        Cache::flush();
        $header = ['dx' => 1, 'dy' => 1, 'nx' => 3, 'ny' => 3,
            'lo1' => 100, 'lo2' => 102, 'la1' => 1, 'la2' => -1];
        $wave = [['header' => $header, 'data' => [1, 1, 1, 1, 2.5, 1, 1, 1, 1]]];
        $wind = [
            ['header' => $header, 'data' => [0, 0, 0, 0, 3, 0, 0, 0, 0]],
            ['header' => $header, 'data' => [0, 0, 0, 0, 4, 0, 0, 0, 0]],
        ];
        Http::fake([
            '*/modelrun' => Http::response(['w3g_hires' => [now('UTC')->toIso8601String()]]),
            '*/inawaves/hs/*' => Http::response(json_encode($wave)),
            '*/inawaves/wind/*' => Http::response(json_encode($wind)),
        ]);

        $this->getJson('/api/bmkg/grid-forecast?lat=0.1&lon=100.9')
            ->assertOk()
            ->assertJsonPath('status', 'AVAILABLE')
            ->assertJsonPath('evidence_level', 'MODEL_FORECAST_NEAREST_GRID_POINT')
            ->assertJsonPath('model_lat', 0)
            ->assertJsonPath('model_lon', 101)
            ->assertJsonPath('wave_height_m', 2.5)
            ->assertJsonPath('wind_speed_knots', 5);
    }

    public function test_invalid_coordinates_are_rejected(): void
    {
        $this->getJson('/api/bmkg/grid-forecast?lat=999&lon=101')->assertStatus(422);
    }

    public function test_missing_model_data_is_explicitly_unavailable(): void
    {
        Cache::flush();
        Http::fake(['*/modelrun' => Http::response([], 503)]);
        $this->getJson('/api/bmkg/grid-forecast?lat=-8.5&lon=111.5')
            ->assertOk()->assertJsonPath('status', 'UNAVAILABLE');
    }
}
