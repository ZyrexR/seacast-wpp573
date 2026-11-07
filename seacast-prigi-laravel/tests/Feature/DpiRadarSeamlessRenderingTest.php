<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;

class DpiRadarSeamlessRenderingTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::updateOrCreate(
            ['email' => 'radar_tester@seacast.id'],
            [
                'name' => 'Radar Tester',
                'role' => UserRole::NELAYAN->value,
                'password' => Hash::make('secret123'),
            ]
        );
    }

    public function test_predictions_api_includes_tongkol_and_precision_waypoint_properties(): void
    {
        $response = $this->getJson('/api/predictions?limit=3');

        $response->assertStatus(200);
        $features = $response->json('features');
        $this->assertNotEmpty($features);

        $props = $features[0]['properties'];
        $this->assertArrayHasKey('hsi_tongkol', $props);
        $this->assertArrayHasKey('waypoint_dms', $props);
        $this->assertArrayHasKey('is_within_12nm', $props);
        $this->assertArrayHasKey('jalur_penangkapan', $props);
    }

    public function test_dpi_radar_page_includes_tongkol_and_seamless_contour_config(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard/dpi-radar');

        $response->assertStatus(200);
        $response->assertSee('Tongkol (Euthynnus spp.)');
        $response->assertSee('stroke: false');
        $response->assertSee('Rencanakan Melaut');
    }
}
