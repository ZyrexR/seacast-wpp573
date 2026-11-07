<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;

class Wpp573GeneralizationTest extends TestCase
{
    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::updateOrCreate(
            ['email' => 'admin_wpp573@seacast.id'],
            [
                'name' => 'Admin WPP573',
                'role' => UserRole::ADMIN->value,
                'password' => Hash::make('secret123'),
            ]
        );
    }

    public function test_public_homepage_renders_wpp573_multi_port_and_three_species(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Multi-port coverage verification
        $response->assertSee('WPP 573');
        $response->assertSee('PPN Prigi');
        $response->assertSee('PPS Cilacap');
        $response->assertSee('PPN Palabuhanratu');

        // Species dossiers: Tongkol, Cakalang, Madidihang
        $response->assertSee('Tongkol');
        $response->assertSee('Cakalang');
        $response->assertSee('Madidihang');

        // Aggregated multi-port fleet
        $response->assertSee('2.450');

        // Preview map in hero should be removed
        $response->assertDontSee('Radar WPPNRI 573 (PPN Prigi)');

        // Navbar new streamlined links
        $response->assertSee('Sains & Model', false);
        $response->assertSee('Data Oseanografi');
        $response->assertSee('Dokumentasi');
        $response->assertSee('Tentang SEACAST');

        // Cinematic background image
        $response->assertSee('hero-marine.jpg');
    }

    public function test_dashboard_overview_and_topbar_are_decluttered(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/dashboard');

        $response->assertStatus(200);

        // Overview headers
        $response->assertSee('Ruang Kendali Eksekutif');
        $response->assertSee('WPPNRI 573');

        // KPI Tongkol presence
        $response->assertSee('Tongkol');

        // Disclaimers and rigid breadcrumbs must NOT be present
        $response->assertDontSee('Pelabelan Ilmiah Mandatori');
        $response->assertDontSee('Simulasi Oseanografi Musiman BMKG Terkalibrasi (Fallback Lokal)');
        $response->assertDontSee('DB Active: 8.129 Sel');
        $response->assertDontSee('WPPNRI 573 / Executive Overview');
    }

    public function test_fleet_analytics_and_trip_planner_are_decluttered_from_breadcrumbs(): void
    {
        // Fleet Analytics
        $fleetResponse = $this->actingAs($this->adminUser)->get('/dashboard/fleet-analytics');
        $fleetResponse->assertStatus(200);
        $fleetResponse->assertDontSee('Scientific Caveats Alert Banner');
        $fleetResponse->assertDontSee('SEACAST / PPN_PRIGI');

        // Trip Planner
        $plannerResponse = $this->actingAs($this->adminUser)->get('/dashboard/trip-planner');
        $plannerResponse->assertStatus(200);
        $plannerResponse->assertDontSee('SEACAST / WPPNRI 573 / Rencana Operasional Melaut');
    }
}

