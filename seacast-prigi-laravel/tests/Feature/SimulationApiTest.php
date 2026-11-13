<?php

namespace Tests\Feature;

use Tests\TestCase;

class SimulationApiTest extends TestCase
{
    public function test_climate_simulation_returns_valid_structure_and_deltas(): void
    {
        $response = $this->postJson('/api/simulate', [
            'sstDelta' => 1.5,
            'chlaMultiplier' => 1.2,
            'limit' => 20,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'type',
            'data' => [
                'scenario' => ['sst_delta', 'chla_multiplier', 'baseline_date'],
                'summary' => [
                    'total_cells',
                    'baseline_hotspots',
                    'simulated_hotspots',
                    'hotspot_delta_pct',
                    'zones' => [
                        'Z1' => ['count', 'pct'],
                        'Z2' => ['count', 'pct'],
                        'Z3' => ['count', 'pct'],
                        'Z4' => ['count', 'pct'],
                        'Z5' => ['count', 'pct'],
                    ]
                ],
                'cells' => [
                    '*' => [
                        'cell_id',
                        'sim_sst',
                        'sim_chla',
                        'hsi_cakalang',
                        'hsi_madidihang',
                        'hsi_tongkol',
                        'is_hotspot',
                        'dom_zone',
                        'dist_port_nm',
                    ]
                ]
            ]
        ]);

        $this->assertEquals('climate_perturbation', $response->json('type'));
        $this->assertEquals(1.5, $response->json('data.scenario.sst_delta'));
        $this->assertCount(20, $response->json('data.cells'));
    }

    public function test_vessel_economics_simulation_calculates_valid_margins(): void
    {
        $response = $this->postJson('/api/simulate', [
            'vesselClass' => '< 10 GT',
            'targetDistanceNm' => 10.0,
            'targetSpecies' => 'cakalang',
            'solarPrice' => 6800,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'type',
            'data' => [
                'vessel_class',
                'target_distance_nm',
                'round_trip_nm',
                'travel_time_hours',
                'estimated_fuel_liters',
                'estimated_fuel_cost_rp',
                'base_catch_kg',
                'tuna_price_per_kg',
                'gross_revenue_rp',
                'net_profit_rp',
                'max_range_nm',
                'is_out_of_range',
                'safety_status',
            ]
        ]);

        $this->assertEquals('vessel_economics', $response->json('type'));
        $this->assertEquals(20.0, $response->json('data.round_trip_nm'));
        $this->assertFalse($response->json('data.is_out_of_range'));
        $this->assertGreaterThan(0, $response->json('data.net_profit_rp'));
    }

    public function test_vessel_economics_flags_out_of_range_danger(): void
    {
        $response = $this->postJson('/api/simulate', [
            'vesselClass' => '< 10 GT',
            'targetDistanceNm' => 25.0, // Exceeds 12 NM max range for <10 GT
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('data.is_out_of_range'));
        $this->assertEquals('DANGER_OUT_OF_RANGE', $response->json('data.safety_status'));
    }
}

