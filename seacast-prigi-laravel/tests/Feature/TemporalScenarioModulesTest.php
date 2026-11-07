<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class TemporalScenarioModulesTest extends TestCase
{
    protected function getAuthenticatedUser(): User
    {
        return User::first() ?? User::factory()->create([
            'email' => 'peneliti@seacast.id',
            'role' => 'peneliti',
        ]);
    }

    public function test_authenticated_user_can_access_time_machine(): void
    {
        $user = $this->getAuthenticatedUser();

        $response = $this->actingAs($user)->get('/dashboard/time-machine');

        $response->assertStatus(200);
        $response->assertSee('Time Machine 2025');
        $response->assertSee('Putar Siklus Musiman');
        $response->assertSee('Suhu Muka Laut');
        $response->assertSee('Sinkronisasi Pendaratan Riil TPI Prigi');
        $response->assertSee('CPUE Proxy Nominal');
    }

    public function test_authenticated_user_can_access_what_if_simulator(): void
    {
        $user = $this->getAuthenticatedUser();

        $response = $this->actingAs($user)->get('/dashboard/what-if-simulator');

        $response->assertStatus(200);
        $response->assertSee('What-if Simulator');
        $response->assertSee('Kalkulator Biaya Operasional');
        $response->assertSee('Harga Solar Subsidi');
        $response->assertSee('Simulator Perturbasi Biofisik');
        $response->assertSee('Anomali Suhu Muka Laut');
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/dashboard/time-machine');
        $response->assertRedirect('/login');

        $response = $this->get('/dashboard/what-if-simulator');
        $response->assertRedirect('/login');
    }
}

