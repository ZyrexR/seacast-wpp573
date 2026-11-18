<?php

namespace Tests\Feature;

use App\Domain\ScientificData\Services\ScientificDataService;
use App\Services\LatestPredictionDateService;
use Mockery\MockInterface;
use Tests\TestCase;

class CoreRadarApiTest extends TestCase
{
    public function test_predictions_without_date_use_latest_observation(): void
    {
        $this->mock(LatestPredictionDateService::class, function (MockInterface $mock) {
            $mock->shouldReceive('latest')->once()->andReturn('2026-11-14');
        });
        $this->mock(ScientificDataService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getDailySummary')
                ->once()
                ->with('2026-11-14')
                ->andReturn(['date' => '2026-11-14', 'summary' => ['total_cells' => 8129]]);
        });

        $this->getJson('/api/predictions?summary_only=1')
            ->assertOk()
            ->assertJsonPath('date', '2026-11-14');
    }

    public function test_predictions_without_available_observation_return_honest_404(): void
    {
        $this->mock(LatestPredictionDateService::class, function (MockInterface $mock) {
            $mock->shouldReceive('latest')->once()->andReturn(null);
        });

        $this->getJson('/api/predictions?summary_only=1')
            ->assertNotFound()
            ->assertJsonPath('status', 'ERROR');
    }

    public function test_predictions_summary_endpoint_returns_correct_schema(): void
    {
        $response = $this->getJson('/api/predictions?summary_only=true&date=2026-11-03');

        $response->assertStatus(200);
        $response->assertHeader('Cache-Control');
        $response->assertJsonStructure([
            'date',
            'summary' => [
                'total_cells',
                'hotspots_count',
                'safe_pct',
                'mean_sst',
                'mean_chla',
                'mean_wave_m',
                'species' => [
                    'cakalang_mean_hsi',
                    'madidihang_mean_hsi',
                    'tongkol_mean_hsi',
                    'tuna_sirip_kuning_mean_hsi',
                    'tongkol_krai_mean_hsi',
                    'highest_species',
                    'highest_score',
                ],
                'bycatch' => [
                    'green_count',
                    'green_pct',
                    'yellow_count',
                    'yellow_pct',
                    'red_count',
                    'red_pct',
                ],
                'sources' => [
                    'sdm',
                    'ocean',
                    'wave',
                    'bycatch',
                ],
            ],
            'provenance' => [
                'evidence_level',
                'source_mode',
                'model_version',
                'prediction_timestamp',
                'validation_status',
            ],
        ]);

        $data = $response->json();
        $this->assertEquals('2026-11-03', $data['date']);
        $this->assertEquals(8129, $data['summary']['total_cells']);
        $this->assertEquals('Simulasi Oseanografi Musiman BMKG Terkalibrasi (Fallback Lokal)', $data['summary']['sources']['wave']);
        $this->assertSame($data['summary']['species']['madidihang_mean_hsi'], $data['summary']['species']['tuna_sirip_kuning_mean_hsi']);
        $this->assertSame($data['summary']['species']['tongkol_mean_hsi'], $data['summary']['species']['tongkol_krai_mean_hsi']);
    }

    public function test_predictions_geojson_endpoint_returns_valid_features(): void
    {
        $response = $this->getJson('/api/predictions?format=geojson&limit=5');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'type',
            'features' => [
                '*' => [
                    'type',
                    'properties' => [
                        'cell_id',
                        'cell_class',
                        'center_lon',
                        'center_lat',
                        'depth_m',
                        'dist_port_km',
                        'dist_port_nm',
                        'is_shelf_break',
                        'is_prigi_inshore',
                        'is_prigi_offshore',
                        'sector',
                        'hsi_cakalang',
                        'hsi_madidihang',
                        'hsi_tongkol',
                        'hsi_tuna_sirip_kuning',
                        'hsi_tongkol_krai',
                        'is_hotspot',
                        'dom_zone',
                        'bycatch_traffic_light',
                        'sst_c',
                        'chla_mg_m3',
                        'wave_height_m',
                        'safety_advisory',
                    ],
                    'geometry' => [
                        'type',
                        'coordinates',
                    ],
                ],
            ],
            'metadata' => [
                'prediction_date',
                'provenance' => [
                    'evidence_level',
                    'source_mode',
                    'model_version',
                    'prediction_timestamp',
                    'validation_status',
                ],
            ],
        ]);

        $features = $response->json('features');
        $this->assertCount(5, $features);
        $this->assertEquals('Polygon', $features[0]['geometry']['type']);
    }

    public function test_predictions_filtered_by_dom_zone_and_sector(): void
    {
        $response = $this->getJson('/api/predictions?format=geojson&sector=JATIM&dom_zone=Z4_NURSERY_GROUND&limit=10');

        $response->assertStatus(200);
        $features = $response->json('features');

        foreach ($features as $f) {
            $this->assertEquals('JATIM', $f['properties']['sector']);
            $this->assertEquals('Z4_NURSERY_GROUND', $f['properties']['dom_zone']);
        }
    }

    public function test_predictions_filter_accepts_canonical_z1_dom_zone_code(): void
    {
        $response = $this->getJson('/api/predictions?format=geojson&date=2026-11-03&dom_zone=Z1_PFG_INSHORE&limit=10');

        $response->assertStatus(200);
        $features = $response->json('features');

        $this->assertNotEmpty($features);
        foreach ($features as $feature) {
            $this->assertSame('Z1_PFG_INSHORE', $feature['properties']['dom_zone']);
        }
    }

    public function test_canonical_grid_api_returns_features(): void
    {
        $response = $this->getJson('/api/grid?sector=JATIM&limit=10');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'type',
            'features' => [
                '*' => [
                    'type',
                    'properties' => [
                        'cell_id',
                        'sector',
                    ],
                    'geometry',
                ],
            ],
        ]);

        $features = $response->json('features');
        $this->assertCount(10, $features);
        foreach ($features as $f) {
            $this->assertEquals('JATIM', $f['properties']['sector']);
        }
    }

    public function test_predictions_api_rejects_invalid_date(): void
    {
        $response = $this->getJson('/api/predictions?date=invalid-date');
        $response->assertStatus(422);
    }
}

