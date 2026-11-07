<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
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
}
