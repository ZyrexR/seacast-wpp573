<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class FleetAnalyticsTest extends TestCase
{
    public function test_unauthenticated_user_redirected_from_fleet_analytics(): void
    {
        $response = $this->get('/dashboard/fleet-analytics');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_fleet_analytics_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard/fleet-analytics');
        $response->assertStatus(200);
        $response->assertSee('Analitik Armada');
        $response->assertSee('18,405.33');
        $response->assertSee('1,269');
        $response->assertSee('Keterbatasan Ilmiah: CPUE Nominal vs. Standardized CPUE');
    }

    public function test_fleet_composition_api_returns_baseline_numbers(): void
    {
        $response = $this->getJson('/api/fleet/composition');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'SUCCESS',
            'data' => [
                'total_vessels' => 1269,
                'classes_count' => 3,
            ],
        ]);

        $classes = $response->json('data.classes');
        $this->assertCount(3, $classes);
        $this->assertEquals('< 10 GT', $classes[0]['gt_class']);
        $this->assertEquals(850, $classes[0]['vessel_count']);
        $this->assertEquals(165, $classes[1]['vessel_count']);
        $this->assertEquals(254, $classes[2]['vessel_count']);
    }

    public function test_annual_landings_api_returns_historical_baseline(): void
    {
        $response = $this->getJson('/api/fleet/annual-landings');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'SUCCESS',
            'data' => [
                'overall_total_tons' => 18405.33,
                'total_months_recorded' => 132,
            ],
        ]);

        $years = $response->json('data.years');
        $this->assertCount(11, $years);
        $this->assertEquals(2015, $years[0]['year']);
        $this->assertEquals(2025, $years[10]['year']);
    }

    public function test_seasonality_api_returns_12_months(): void
    {
        $response = $this->getJson('/api/fleet/seasonality');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(12, $data);
        $this->assertEquals('Januari', $data[0]['month_name']);
        $this->assertEquals('Desember', $data[11]['month_name']);
    }
}
