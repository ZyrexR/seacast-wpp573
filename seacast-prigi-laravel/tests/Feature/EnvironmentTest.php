<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;

class EnvironmentTest extends TestCase
{
    public function test_laravel_boots_and_configuration_loads(): void
    {
        $this->assertEquals('SEACAST', config('app.name'));
        $this->assertEquals('id', config('app.locale'));
        $this->assertNotEmpty(config('app.key'));
        $this->assertEquals('sqlite', config('database.default'));
        $this->assertEquals('seacast_prigi', config('database.connections.pgsql.database'));
    }

    public function test_all_core_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('home'));
        $this->assertTrue(Route::has('about'));
        $this->assertTrue(Route::has('science'));
        $this->assertTrue(Route::has('documentation'));
        $this->assertTrue(Route::has('design-system'));
        $this->assertTrue(Route::has('login'));
        $this->assertTrue(Route::has('login.post'));
        $this->assertTrue(Route::has('logout'));
        $this->assertTrue(Route::has('dashboard'));
        $this->assertTrue(Route::has('dashboard.system-status'));
        $this->assertTrue(Route::has('dashboard.dpi-radar'));
        $this->assertTrue(Route::has('api.monitoring'));
    }

    public function test_public_pages_render_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('SEACAST');
        $response->assertSee('8,129');

        $response = $this->get('/about');
        $response->assertStatus(200);
        $response->assertSee('Tentang Platform SEACAST');

        $response = $this->get('/science');
        $response->assertStatus(200);
        $response->assertSee('MaxEnt');
        $response->assertSee('Tongkol Krai');

        $response = $this->get('/documentation');
        $response->assertStatus(200);

        $response = $this->get('/design-system');
        $response->assertStatus(200);
        $response->assertSee('Design System');
    }
}

