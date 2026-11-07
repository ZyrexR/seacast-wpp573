<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;

class ProductionSecurityHardeningTest extends TestCase
{
    private User $nelayan;
    private User $syahbandar;
    private User $peneliti;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nelayan = User::updateOrCreate(
            ['email' => 'nelayan_p7@seacast.id'],
            ['name' => 'Nakhoda Sukirno', 'role' => UserRole::NELAYAN->value, 'password' => Hash::make('secret123')]
        );

        $this->syahbandar = User::updateOrCreate(
            ['email' => 'syahbandar_p7@seacast.id'],
            ['name' => 'Syahbandar Bambang', 'role' => UserRole::SYAHBANDAR->value, 'password' => Hash::make('secret123')]
        );

        $this->peneliti = User::updateOrCreate(
            ['email' => 'peneliti_p7@seacast.id'],
            ['name' => 'Dr. Suryo Prasojo', 'role' => UserRole::PENELITI->value, 'password' => Hash::make('secret123')]
        );

        $this->admin = User::updateOrCreate(
            ['email' => 'admin_p7@seacast.id'],
            ['name' => 'Administrator Utama', 'role' => UserRole::ADMIN->value, 'password' => Hash::make('secret123')]
        );
    }

    public function test_security_headers_present_on_all_http_responses(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertTrue($response->headers->has('Permissions-Policy'));
    }

    public function test_user_role_enum_methods_and_type_safety(): void
    {
        $this->assertTrue(UserRole::isValid('nelayan'));
        $this->assertTrue(UserRole::isValid('syahbandar'));
        $this->assertTrue(UserRole::isValid('peneliti'));
        $this->assertTrue(UserRole::isValid('admin'));
        $this->assertFalse(UserRole::isValid('superuser_fake'));

        $this->assertEquals('Nelayan & Nakhoda Kapal', UserRole::NELAYAN->label());
        $this->assertEquals('Administrator Sistem', UserRole::ADMIN->label());

        $this->assertTrue($this->nelayan->isNelayan());
        $this->assertFalse($this->nelayan->isAdmin());
        $this->assertTrue($this->admin->isAdmin());
        $this->assertTrue($this->syahbandar->isSyahbandar());
        $this->assertTrue($this->peneliti->isPeneliti());

        $this->assertTrue($this->nelayan->hasRole(UserRole::NELAYAN));
        $this->assertTrue($this->admin->hasRole(UserRole::ADMIN));
        $this->assertTrue($this->nelayan->hasRole([UserRole::NELAYAN, UserRole::ADMIN]));
    }

    public function test_guest_is_redirected_to_login_for_all_dashboard_routes(): void
    {
        $routes = [
            '/dashboard',
            '/dashboard/dpi-radar',
            '/dashboard/dom-management',
            '/dashboard/bycatch-safety',
            '/dashboard/fleet-analytics',
            '/dashboard/cpue-analysis',
            '/dashboard/species-models',
            '/dashboard/oceanographic-explorer',
            '/dashboard/time-machine',
            '/dashboard/what-if-simulator',
            '/dashboard/data-monitoring',
            '/dashboard/system-status',
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login');
        }
    }

    public function test_rbac_matrix_nelayan_syahbandar_peneliti_blocked_from_admin_system_status(): void
    {
        // 1. Nelayan
        $responseNelayan = $this->actingAs($this->nelayan)->get('/dashboard/system-status');
        $responseNelayan->assertStatus(403);

        // 2. Syahbandar
        $responseSyahbandar = $this->actingAs($this->syahbandar)->get('/dashboard/system-status');
        $responseSyahbandar->assertStatus(403);

        // 3. Peneliti
        $responsePeneliti = $this->actingAs($this->peneliti)->get('/dashboard/system-status');
        $responsePeneliti->assertStatus(403);

        // 4. Admin (Authorized)
        $responseAdmin = $this->actingAs($this->admin)->get('/dashboard/system-status');
        $responseAdmin->assertStatus(200);
    }

    public function test_all_operational_personas_can_access_operational_dashboard_modules(): void
    {
        $operationalRoutes = [
            '/dashboard',
            '/dashboard/dpi-radar',
            '/dashboard/dom-management',
            '/dashboard/bycatch-safety',
            '/dashboard/fleet-analytics',
            '/dashboard/species-models',
            '/dashboard/oceanographic-explorer',
            '/dashboard/time-machine',
            '/dashboard/what-if-simulator',
            '/dashboard/data-monitoring',
        ];

        // Verify Nelayan can access operational routes
        foreach ($operationalRoutes as $route) {
            $response = $this->actingAs($this->nelayan)->get($route);
            $response->assertStatus(200);
        }

        // Verify Syahbandar can access operational routes
        foreach ($operationalRoutes as $route) {
            $response = $this->actingAs($this->syahbandar)->get($route);
            $response->assertStatus(200);
        }

        // Verify Peneliti can access operational routes
        foreach ($operationalRoutes as $route) {
            $response = $this->actingAs($this->peneliti)->get($route);
            $response->assertStatus(200);
        }
    }
}

