<?php

namespace Tests\Feature;

use Tests\TestCase;

class TimeMachineApiTest extends TestCase
{
    public function test_timemachine_summary_returns_12_months(): void
    {
        $response = $this->getJson('/api/timemachine?summary=true');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'summary' => [
                '*' => [
                    'year_month',
                    'year',
                    'month',
                    'total_cells',
                    'avg_hsi_cakalang',
                    'avg_hsi_madidihang',
                    'avg_hsi_albakor',
                    'avg_sst',
                    'avg_chla',
                    'avg_current',
                    'hotspot_cells',
                ]
            ],
            'total_months',
        ]);

        $this->assertEquals(12, $response->json('total_months'));
    }

    public function test_timemachine_snapshot_returns_cells_for_month(): void
    {
        $response = $this->getJson('/api/timemachine?ym=2025_08&limit=10');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'year_month',
            'total_cells',
            'cells' => [
                '*' => [
                    'year_month',
                    'cell_id',
                    'hsi_cakalang',
                    'hsi_madidihang',
                    'hsi_albakor',
                    'dom_zone',
                    'sst',
                    'chla',
                    'center_lon',
                    'center_lat',
                    'dist_port_nm',
                ]
            ]
        ]);

        $this->assertEquals('2025_08', $response->json('year_month'));
        $this->assertCount(10, $response->json('cells'));
    }

    public function test_timemachine_cell_history_returns_12_monthly_points(): void
    {
        $response = $this->getJson('/api/timemachine?cell_id=WPP573_R010_C143_R050');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'cell_id',
            'total_months',
            'history' => [
                '*' => [
                    'year_month',
                    'year',
                    'month',
                    'hsi_cakalang',
                    'hsi_madidihang',
                    'hsi_albakor',
                    'dom_zone',
                    'sst',
                    'chla',
                ]
            ]
        ]);

        $this->assertEquals('WPP573_R010_C143_R050', $response->json('cell_id'));
        $this->assertEquals(12, $response->json('total_months'));
    }
}
