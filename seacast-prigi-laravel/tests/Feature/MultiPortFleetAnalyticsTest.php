<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use App\Domain\Fleet\Services\FleetAnalyticsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MultiPortFleetAnalyticsTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::updateOrCreate(
            ['email' => 'syahbandar_multiport@seacast.id'],
            [
                'name' => 'Syahbandar Multiport',
                'role' => UserRole::SYAHBANDAR->value,
                'password' => Hash::make('secret123'),
            ]
        );
    }

    public function test_fleet_analytics_defaults_to_ppn_prigi(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard/fleet-analytics');

        $response->assertStatus(200);
        $response->assertSee('PPN Prigi');
        $response->assertSee('Analitik Armada & Pendaratan Multi-Pelabuhan');
    }

    public function test_fleet_analytics_renders_pps_cilacap_data_when_selected(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard/fleet-analytics?port=PPS_CILACAP');

        $response->assertStatus(200);
        $response->assertSee('PPS Cilacap');
        $response->assertSee('Yellowfin Tuna');
        $response->assertSee('Cakalang');
        $response->assertSee('Tongkol Krai');
        $response->assertSee('Jawa Tengah');
    }

    public function test_cilacap_without_landings_never_inherits_prigi_archive(): void
    {
        $connection = DB::connection();
        $connection->beginTransaction();

        try {
            $connection->table('port_landings')->where('port_code', 'PPS_CILACAP')->delete();

            $service = app(FleetAnalyticsService::class);
            $annual = $service->getAnnualLandingsSummary('PPS_CILACAP');

            $this->assertSame('PPS_CILACAP', $annual['port_code']);
            $this->assertSame(0.0, $annual['overall_total_tons']);
            $this->assertSame(0, $annual['overall_total_trips']);
            $this->assertSame(0, $annual['total_months_recorded']);
            $this->assertSame([], $annual['years']);
            $this->assertSame([], $service->getMonthlySeasonality('PPS_CILACAP'));
            $this->assertSame([], $service->getRecentMonthlyLandings(12, 'PPS_CILACAP'));
        } finally {
            $connection->rollBack();
        }
    }

    public function test_fleet_api_uses_requested_port_without_changing_default(): void
    {
        $prigi = $this->getJson('/api/fleet/composition');
        $prigi->assertOk()->assertJsonPath('data.port_code', 'PPN_PRIGI');

        $cilacap = $this->getJson('/api/fleet/composition?port=PPS_CILACAP');
        $cilacap->assertOk()
            ->assertJsonPath('data.port_code', 'PPS_CILACAP')
            ->assertJsonPath('data.total_vessels', 814);

        $annual = $this->getJson('/api/fleet/annual-landings?port=PPS_CILACAP');
        $annual->assertOk()->assertJsonPath('data.port_code', 'PPS_CILACAP');

        $this->getJson('/api/fleet/seasonality?port=UNKNOWN')->assertUnprocessable();
    }
}

