<?php

namespace Tests\Feature;

use Tests\TestCase;

class MonitoringApiTest extends TestCase
{
    public function test_monitoring_api_endpoint_returns_live_health_data(): void
    {
        $response = $this->getJson('/api/monitoring');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'app_health' => [
                    'status',
                    'framework',
                    'php_version',
                    'environment',
                ],
                'database' => [
                    'status',
                    'latency_ms',
                    'engine' => [
                        'postgresql_version',
                        'postgis_version',
                        'postgis_available',
                    ],
                    'spatial_baseline' => [
                        'canonical_grids_count',
                        'valid_geometries',
                        'invalid_geometries',
                        'srid',
                    ],
                    'data_records' => [
                        'daily_predictions_count',
                        'latest_prediction_date',
                        'grid_monthly_predictions_count',
                        'prigi_port_landings_count',
                        'prigi_fleet_rows_count',
                        'total_registered_vessels',
                    ],
                ],
                'python_engine',
                'storage',
                'queue',
                'scheduler',
                'pipeline_satelit' => [
                    'status',
                    'description',
                    'satellites' => [
                        'NOAA_CoralTemp_OISST',
                        'Sentinel_3_OLCI_Chla',
                        'BMKG_Marine_Forecast',
                    ],
                ],
                'timestamp',
            ],
        ]);

        $json = $response->json();

        $this->assertEquals('success', $json['status']);
        $this->assertEquals('healthy', $json['data']['database']['status']);
        $this->assertTrue($json['data']['database']['engine']['postgis_available']);
        $this->assertEquals(8129, $json['data']['database']['spatial_baseline']['canonical_grids_count']);
        $this->assertEquals(8129, $json['data']['database']['spatial_baseline']['valid_geometries']);
        $this->assertEquals(0, $json['data']['database']['spatial_baseline']['invalid_geometries']);
        $this->assertEquals(4326, $json['data']['database']['spatial_baseline']['srid']);

        // Check BMKG calibrated fallback designation
        $bmkgStatus = $json['data']['pipeline_satelit']['satellites']['BMKG_Marine_Forecast']['status'];
        $this->assertStringContainsString('Simulasi Oseanografi Musiman BMKG Terkalibrasi (Fallback Lokal)', $bmkgStatus);
    }
}

