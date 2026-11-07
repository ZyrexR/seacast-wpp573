<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;

class ComprehensiveRouteCoverageTest extends TestCase
{
    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::updateOrCreate(
            ['email' => 'admin_coverage@seacast.id'],
            [
                'name' => 'Admin Coverage',
                'role' => UserRole::ADMIN->value,
                'password' => Hash::make('secret123'),
            ]
        );
    }

    public function test_all_thirteen_public_portal_routes_render_200_ok(): void
    {
        $publicRoutes = [
            '/' => 'SEACAST',
            '/about' => 'PPN Prigi',
            '/science' => 'Ensemble SDM',
            '/science/dynamic-ocean-management' => 'Dynamic Ocean Management',
            '/science/methodology' => 'Metodologi Ensemble',
            '/science/prediction-models' => 'Model Prediksi Spesies',
            '/documentation' => 'Dokumentasi',
            '/design-system' => 'Design System',
            '/ocean-data' => 'Katalog Data Oseanografi',
            '/platform' => 'Arsitektur Platform',
            '/platform/data-sources' => 'Sumber Data Satelit',
            '/platform/features' => 'Fitur Berbasis Armada',
            '/login' => 'Ruang Kendali Operasional',
        ];

        foreach ($publicRoutes as $uri => $expectedText) {
            $response = $this->get($uri);
            $response->assertStatus(200);
            $response->assertSee($expectedText);
        }
    }

    public function test_streamlined_dashboard_operational_routes_render_200_ok_for_admin(): void
    {
        $dashboardRoutes = [
            '/dashboard' => 'Ruang Kendali Eksekutif',
            '/dashboard/dpi-radar' => 'DPI Radar Spasial',
            '/dashboard/trip-planner' => 'Rencana Operasional Melaut',
            '/dashboard/fleet-analytics' => 'Analitik Armada',
            '/dashboard/system-status' => 'Spesifikasi Arsitektur Runtime Target',
        ];

        foreach ($dashboardRoutes as $uri => $expectedText) {
            $response = $this->actingAs($this->adminUser)->get($uri);
            $response->assertStatus(200);
            $response->assertSee($expectedText);
        }
    }

    public function test_pruned_obsolete_dashboard_routes_return_404(): void
    {
        $obsoleteRoutes = [
            '/dashboard/species-models',
            '/dashboard/oceanographic-explorer',
            '/dashboard/time-machine',
            '/dashboard/dom-management',
            '/dashboard/bycatch-safety',
            '/dashboard/data-monitoring',
        ];

        foreach ($obsoleteRoutes as $uri) {
            $response = $this->actingAs($this->adminUser)->get($uri);
            $response->assertStatus(404);
        }
    }

    public function test_all_rest_api_endpoints_return_successful_responses(): void
    {
        // 1. Predictions API (GeoJSON FeatureCollection)
        $respPredictions = $this->getJson('/api/predictions?limit=5');
        $respPredictions->assertStatus(200);
        $this->assertEquals('FeatureCollection', $respPredictions->json('type'));
        $this->assertNotEmpty($respPredictions->json('features'));

        // 2. Grid API (GeoJSON FeatureCollection)
        $respGrid = $this->getJson('/api/grid?limit=5');
        $respGrid->assertStatus(200);
        $this->assertEquals('FeatureCollection', $respGrid->json('type'));
        $this->assertNotEmpty($respGrid->json('features'));

        // 3. Fleet Composition
        $respFleet = $this->getJson('/api/fleet/composition');
        $respFleet->assertStatus(200);
        $respFleet->assertJsonStructure(['status', 'data']);

        // 4. Annual Landings
        $respAnnual = $this->getJson('/api/fleet/annual-landings');
        $respAnnual->assertStatus(200);
        $respAnnual->assertJsonStructure(['status', 'data']);

        // 5. Fleet Seasonality
        $respSeason = $this->getJson('/api/fleet/seasonality');
        $respSeason->assertStatus(200);
        $respSeason->assertJsonStructure(['status', 'data']);

        // 6. Export GeoJSON
        $respGeojson = $this->get('/api/export/geojson?limit=5');
        $respGeojson->assertStatus(200);
        $this->assertStringContainsString('application/geo+json', (string)$respGeojson->headers->get('Content-Type'));

        // 7. Export CSV
        $respCsv = $this->get('/api/export/csv?limit=5');
        $respCsv->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string)$respCsv->headers->get('Content-Type'));

        // 8. Time Machine API
        $respTimeMachine = $this->getJson('/api/timemachine?summary=true');
        $respTimeMachine->assertStatus(200);
        $respTimeMachine->assertJsonStructure(['status', 'summary']);

        // 9. Simulation API (POST)
        $respSimulate = $this->postJson('/api/simulate', [
            'sstDelta' => 1.0,
            'chlaMultiplier' => 1.2,
        ]);
        $respSimulate->assertStatus(200);
        $respSimulate->assertJsonStructure(['status', 'type', 'data']);

        // 10. Public Monitoring API
        $respMon = $this->getJson('/api/monitoring');
        $respMon->assertStatus(200);

        // 11. Protected Diagnostic Monitoring API
        $respDiag = $this->actingAs($this->adminUser)->getJson('/api/monitoring/diagnostics');
        $respDiag->assertStatus(200);
    }
}
