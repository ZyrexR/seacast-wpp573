<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class ScientificDiagnosticsModulesTest extends TestCase
{
    protected function getAuthenticatedUser(): User
    {
        return User::first() ?? User::factory()->create([
            'email' => 'peneliti@seacast.id',
            'role' => 'peneliti',
        ]);
    }

    public function test_authenticated_user_can_access_species_models(): void
    {
        $user = $this->getAuthenticatedUser();

        $response = $this->actingAs($user)->get('/dashboard/species-models');

        $response->assertStatus(200);
        $response->assertSee('Diagnostik Model Spesies');
        $response->assertSee('Cakalang');
        $response->assertSee('Katsuwonus pelamis');
        $response->assertSee('Madidihang');
        $response->assertSee('Thunnus albacares');
        $response->assertSee('Albakor');
        $response->assertSee('Thunnus alalunga');
        $response->assertSee('MaxEnt');
        $response->assertSee('Random Forest');
        $response->assertSee('GLM');
    }

    public function test_authenticated_user_can_access_oceanographic_explorer(): void
    {
        $user = $this->getAuthenticatedUser();

        $response = $this->actingAs($user)->get('/dashboard/oceanographic-explorer');

        $response->assertStatus(200);
        $response->assertSee('Eksplorer Parameter Multi-Oseanografi');
        $response->assertSee('Suhu Laut (SST)');
        $response->assertSee('Klorofil-a');
        $response->assertSee('Kecepatan Arus');
        $response->assertSee('Tinggi Gelombang');
        $response->assertSee('NOAA OISST');
        $response->assertSee('Sentinel-3');
    }

    public function test_authenticated_user_can_access_data_monitoring(): void
    {
        $user = $this->getAuthenticatedUser();

        $response = $this->actingAs($user)->get('/dashboard/data-monitoring');

        $response->assertStatus(200);
        $response->assertSee('Monitoring Pipeline Data');
        $response->assertSee('canonical_grids');
        $response->assertSee('daily_predictions');
        $response->assertSee('grid_monthly_predictions');
        $response->assertSee('prigi_port_landings');
        $response->assertSee('seacast_readonly');
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/dashboard/species-models');
        $response->assertRedirect('/login');

        $response = $this->get('/dashboard/oceanographic-explorer');
        $response->assertRedirect('/login');

        $response = $this->get('/dashboard/data-monitoring');
        $response->assertRedirect('/login');
    }
}

