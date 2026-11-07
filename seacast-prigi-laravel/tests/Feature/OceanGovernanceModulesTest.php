<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class OceanGovernanceModulesTest extends TestCase
{
    protected function getAuthenticatedUser(): User
    {
        return User::first() ?? User::factory()->create([
            'email' => 'nelayan@seacast.id',
            'role' => 'nelayan',
        ]);
    }

    public function test_authenticated_user_can_access_dom_management(): void
    {
        $user = $this->getAuthenticatedUser();

        $response = $this->actingAs($user)->get('/dashboard/dom-management');

        $response->assertStatus(200);
        $response->assertSee('Dynamic Ocean Management');
        $response->assertSee('Z1');
        $response->assertSee('Z2');
        $response->assertSee('Z3');
        $response->assertSee('Z4');
        $response->assertSee('Z5');
        $response->assertSee('Hierarki Resolusi Konflik Waterfall DOM');
    }

    public function test_authenticated_user_can_access_bycatch_safety(): void
    {
        $user = $this->getAuthenticatedUser();

        $response = $this->actingAs($user)->get('/dashboard/bycatch-safety');

        $response->assertStatus(200);
        $response->assertSee('Bycatch Traffic Light');
        $response->assertSee('GREEN');
        $response->assertSee('YELLOW');
        $response->assertSee('RED');
        $response->assertSee('Circle Hooks');
        $response->assertSee('Dehooker');
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/dashboard/dom-management');
        $response->assertRedirect('/login');

        $response = $this->get('/dashboard/bycatch-safety');
        $response->assertRedirect('/login');
    }
}

