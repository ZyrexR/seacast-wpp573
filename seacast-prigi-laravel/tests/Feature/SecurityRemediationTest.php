<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SecurityRemediationTest extends TestCase
{
    public function test_role_based_authorization_protects_admin_system_status(): void
    {
        // 1. Create a fisherman (nelayan) user
        $nelayan = User::updateOrCreate(
            ['email' => 'nelayan_test@seacast.id'],
            [
                'name' => 'Nelayan Test',
                'role' => 'nelayan',
                'password' => Hash::make('secret123'),
            ]
        );

        // 2. Create an admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin_test@seacast.id'],
            [
                'name' => 'Admin Test',
                'role' => 'admin',
                'password' => Hash::make('secret123'),
            ]
        );

        // Nelayan accesses dashboard overview (allowed)
        $overviewResponse = $this->actingAs($nelayan)->get('/dashboard');
        $overviewResponse->assertStatus(200);

        // Nelayan tries to access system-status (forbidden -> 403)
        $forbiddenResponse = $this->actingAs($nelayan)->get('/dashboard/system-status');
        $forbiddenResponse->assertStatus(403);
        $forbiddenResponse->assertSee('Akses Ditolak');

        // Admin accesses system-status (allowed -> 200)
        $adminResponse = $this->actingAs($admin)->get('/dashboard/system-status');
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('Spesifikasi Arsitektur Runtime Target');
    }

    public function test_demo_credentials_blocked_when_environment_is_production(): void
    {
        $controller = new \App\Http\Controllers\Web\AuthController();

        // Temporarily simulate production environment
        $originalEnv = config('app.env');
        config(['app.env' => 'production']);
        $this->app['env'] = 'production';

        try {
            $request = \Illuminate\Http\Request::create('/login', 'POST', [
                'email' => 'admin@seacast.id',
                'password' => 'seacast2026',
            ]);

            $this->expectException(\Illuminate\Validation\ValidationException::class);
            $this->expectExceptionMessage('lingkungan produksi');

            $controller->login($request);
        } finally {
            // Restore original environment
            config(['app.env' => $originalEnv]);
            $this->app['env'] = $originalEnv;
        }
    }

    public function test_public_monitoring_api_sanitizes_paths_and_masks_secrets(): void
    {
        $response = $this->getJson('/api/monitoring');

        $response->assertStatus(200);
        $content = $response->getContent();

        // Must NOT leak absolute server drive letters or paths
        $this->assertStringNotContainsString('C:\\Users\\', $content);
        $this->assertStringNotContainsString('C:/Users/', $content);
        $this->assertStringNotContainsString('C:\\Python', $content);
        $this->assertStringNotContainsString('C:/Python', $content);

        // Must NOT leak credentials
        $this->assertStringNotContainsString('DB_PASSWORD', $content);
        $this->assertStringNotContainsString('APP_KEY', $content);
        $this->assertStringNotContainsString('seacast_readonly', $content);

        // Relative paths sanitized
        $json = $response->json();
        $this->assertEquals('storage/', $json['data']['storage']['path']);
    }

    public function test_diagnostic_monitoring_endpoint_requires_admin_authorization(): void
    {
        // Unauthenticated -> 401
        $guestResponse = $this->getJson('/api/monitoring/diagnostics');
        $guestResponse->assertStatus(401);

        // Nelayan -> 403
        $nelayan = User::firstOrCreate(
            ['email' => 'nelayan@seacast.id'],
            ['name' => 'Nelayan', 'role' => 'nelayan', 'password' => Hash::make('seacast2026')]
        );
        $nelayanResponse = $this->actingAs($nelayan)->getJson('/api/monitoring/diagnostics');
        $nelayanResponse->assertStatus(403);

        // Admin -> 200
        $admin = User::firstOrCreate(
            ['email' => 'admin@seacast.id'],
            ['name' => 'Admin', 'role' => 'admin', 'password' => Hash::make('seacast2026')]
        );
        $adminResponse = $this->actingAs($admin)->getJson('/api/monitoring/diagnostics');
        $adminResponse->assertStatus(200);
        $adminResponse->assertJsonStructure([
            'status',
            'data' => [
                'admin_diagnostics' => [
                    'php_sapi',
                    'memory_usage_mb',
                    'memory_peak_mb',
                    'opcache_enabled',
                ],
            ],
        ]);
    }
}
