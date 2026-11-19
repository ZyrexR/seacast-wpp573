<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class DpiRadarViewTest extends TestCase
{
    public function test_guest_is_redirected_from_radar_to_login(): void
    {
        $response = $this->get('/dashboard/dpi-radar');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_interactive_radar_dashboard(): void
    {
        $user = User::factory()->make([
            'id' => 1,
            'name' => 'Operator Radar',
            'email' => 'operator@seacast.id',
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get('/dashboard/dpi-radar');

        $response->assertStatus(200);
        $response->assertSee('DPI Radar Spasial WPPNRI 573');
        
        // Assert Dual-Hub Corridor buttons are present (Task 2.3)
        $response->assertSee('PPN Prigi');
        $response->assertSee('PPS Cilacap');
        $response->assertSee('Tuna Sirip Kuning');
        $response->assertSee('Tongkol Krai');
        $response->assertSee('cell_id=');

        // Assert Live BMKG Widget is present (Task 2.4)
        $response->assertSee('BMKG Live:');
        $response->assertSee('Buletin BMKG Maritim Live');
        $response->assertSee('bmkg-grid-forecast');
        $response->assertSee('/api/bmkg/grid-forecast?lat=', false);

        // Assert Ocean Current 60 FPS Toggle is present (Task 2.1)
        $response->assertSee('Arus Laut 60 FPS');

        // Assert Seasonal Playback bar is removed
        $response->assertDontSee('Playback Musiman');
        $response->assertDontSee('toggleSeasonalPlayback');

        // Assert DOM and Bycatch filters remain intact
        $response->assertSee('Zonasi DOM (Dynamic Ocean Management)');
        $response->assertSee('Tingkat Risiko Bycatch');
        $response->assertSee('Ambang Batas HSI Minimum');
    }
}
