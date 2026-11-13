<?php

namespace Tests\Feature;

use Tests\TestCase;

class BmkgLiveApiTest extends TestCase
{
    public function test_bmkg_live_endpoint_returns_successful_payload(): void
    {
        $response = $this->getJson('/api/bmkg/live?hub=prigi');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'hub',
                'port' => [
                    'port_code',
                    'port_name',
                    'wave_height',
                    'wave_cat',
                    'safety'
                ],
                'ocean_offshore' => [
                    'area_code',
                    'area_name',
                    'wave_height',
                    'safety'
                ],
                'max_wave_height',
                'overall_safety'
            ]
        ]);
    }

    public function test_bmkg_live_endpoint_supports_cilacap_hub(): void
    {
        $response = $this->getJson('/api/bmkg/live?hub=cilacap');

        $response->assertStatus(200);
        $this->assertEquals('CILACAP', $response->json('data.hub'));
        $this->assertEquals('PPS Cilacap', $response->json('data.port.port_name'));
    }
}
