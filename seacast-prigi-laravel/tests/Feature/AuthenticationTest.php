<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthenticationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clear rate limiter keys before tests
        RateLimiter::clear('login-attempt:127.0.0.1|admin@seacast.id');
        RateLimiter::clear('login-attempt:127.0.0.1|test@seacast.id');
    }

    public function test_guest_can_view_login_page_with_personas(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Ruang Kendali Operasional');
        $response->assertSee('nelayan@seacast.id');
        $response->assertSee('syahbandar@seacast.id');
        $response->assertSee('peneliti@seacast.id');
        $response->assertSee('admin@seacast.id');
    }

    public function test_unauthenticated_user_redirected_to_login_from_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        $responseStatus = $this->get('/dashboard/system-status');
        $responseStatus->assertRedirect('/login');

        $responseModule = $this->get('/dashboard/dpi-radar');
        $responseModule->assertRedirect('/login');
    }

    public function test_user_can_authenticate_with_valid_credentials(): void
    {
        // Ensure user exists in SQLite
        $user = User::firstOrCreate(
            ['email' => 'admin@seacast.id'],
            [
                'name' => 'Administrator Sistem',
                'password' => Hash::make('seacast2026'),
                'role' => 'admin',
            ]
        );

        $response = $this->post('/login', [
            'email' => 'admin@seacast.id',
            'password' => 'seacast2026',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        // Access dashboard as authenticated user
        $dashboardResponse = $this->actingAs($user)->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Ruang Kendali Eksekutif');
    }

    public function test_user_cannot_authenticate_with_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@seacast.id',
            'password' => 'wrongpassword123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_rate_limiting_protects_against_brute_force(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'test@seacast.id',
                'password' => 'wrongpassword',
            ]);
        }

        // 6th attempt should be blocked by rate limiter
        $response = $this->post('/login', [
            'email' => 'test@seacast.id',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertTrue(
            session('errors')->first('email') !== null &&
            str_contains(session('errors')->first('email'), 'Terlalu banyak percobaan masuk')
        );
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@seacast.id'],
            [
                'name' => 'Administrator Sistem',
                'password' => Hash::make('seacast2026'),
                'role' => 'admin',
            ]
        );

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
