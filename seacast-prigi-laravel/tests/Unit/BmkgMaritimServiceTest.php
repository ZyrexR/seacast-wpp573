<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\BmkgMaritimService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class BmkgMaritimServiceTest extends TestCase
{
    private BmkgMaritimService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BmkgMaritimService();
        Cache::flush();
    }

    public function test_safety_evaluation_classifies_wave_heights_correctly(): void
    {
        // Safe (< 2.0m)
        $safe = $this->service->evaluateSafety(1.2);
        $this->assertEquals('SAFE', $safe['status']);
        $this->assertEquals('green', $safe['badge_color']);
        $this->assertTrue($safe['is_safe']);
        $this->assertStringContainsString('AMAN MELAUT', $safe['label']);

        // Warning (2.0m - 3.5m)
        $warning = $this->service->evaluateSafety(2.5);
        $this->assertEquals('WARNING', $warning['status']);
        $this->assertEquals('yellow', $warning['badge_color']);
        $this->assertStringContainsString('WASPADA GELOMBANG TINGGI', $warning['label']);

        // Extreme Danger (>= 3.5m)
        $danger = $this->service->evaluateSafety(3.8);
        $this->assertEquals('DANGER', $danger['status']);
        $this->assertEquals('red', $danger['badge_color']);
        $this->assertFalse($danger['is_safe']);
        $this->assertStringContainsString('BAHAYA GELOMBANG EKSTREM', $danger['label']);
    }

    public function test_get_live_port_weather_parses_real_bmkg_schema_structure(): void
    {
        $mockPayload = [
            'code' => 'XO026',
            'name' => 'PPN Prigi',
            'issued' => '2026-11-10 12:00 UTC',
            'valid_from' => '2026-11-10 00:00 UTC',
            'valid_to' => '2026-11-13 00:00 UTC',
            'forecast_day1' => [
                [
                    'time' => '2026-11-10 00:00 UTC',
                    'weather' => 'Cerah Berawan',
                    'temp_avg' => 27,
                    'wind_from' => 'Selatan',
                    'wind_speed' => 5,
                    'wave_cat' => 'Rendah',
                    'wave_height' => 1.2,
                    'current_to' => 'Timur',
                    'current_speed' => 0.65,
                    'tides' => 0.15
                ]
            ]
        ];

        Http::fake([
            'https://maritim.bmkg.go.id/marine2026-data/pelabuhan/XO026.json' => Http::response($mockPayload, 200)
        ]);

        $data = $this->service->getLivePortWeather('prigi');

        $this->assertEquals('PPN Prigi', $data['port_name']);
        $this->assertEquals('XO026', $data['port_code']);
        $this->assertEquals(1.2, $data['wave_height']);
        $this->assertEquals('Rendah', $data['wave_cat']);
        $this->assertEquals(5, $data['wind_speed']);
        $this->assertEquals('Selatan', $data['wind_from']);
        $this->assertEquals(0.65, $data['current_speed']);
        $this->assertEquals('SAFE', $data['safety']['status']);
        $this->assertNotEmpty($data['issued']);
    }

    public function test_get_live_ocean_area_parses_hindia_ocean_schema(): void
    {
        $mockOcean = [
            'code' => 'M.12',
            'name' => 'Samudra Hindia selatan Jawa Timur',
            'no_surat' => 'B/ME.01.02/METOS/09/DMM/XI/2026',
            'nama_prakirawan' => 'Dyah Ayu',
            'issued' => '2026-11-10 12:00 UTC',
            'valid_from' => '2026-11-10 00:00 UTC',
            'forecast_day1' => [
                [
                    'time' => '2026-11-10 00:00 UTC',
                    'weather' => 'Udara Kabur',
                    'wind_from' => 'Barat Daya',
                    'wind_speed' => 4,
                    'wave_cat' => 'Sedang',
                    'wave_height' => 1.9,
                    'current_to' => 'Barat Daya',
                    'current_speed' => 1.64
                ]
            ]
        ];

        Http::fake([
            'https://maritim.bmkg.go.id/marine2026-data/perairan/M.12.json' => Http::response($mockOcean, 200)
        ]);

        $data = $this->service->getLiveOceanArea('jatim');

        $this->assertEquals('Samudra Hindia selatan Jawa Timur', $data['area_name']);
        $this->assertEquals('M.12', $data['area_code']);
        $this->assertEquals('B/ME.01.02/METOS/09/DMM/XI/2026', $data['no_surat']);
        $this->assertEquals('Dyah Ayu', $data['nama_prakirawan']);
        $this->assertEquals(1.9, $data['wave_height']);
        $this->assertEquals('Sedang', $data['wave_cat']);
        $this->assertEquals(1.64, $data['current_speed']);
    }

    public function test_service_provides_graceful_fallback_when_bmkg_api_fails(): void
    {
        Http::fake([
            'https://maritim.bmkg.go.id/*' => Http::response(null, 500)
        ]);

        $fallback = $this->service->getLivePortWeather('prigi');

        $this->assertNotNull($fallback);
        $this->assertEquals('PPN Prigi', $fallback['port_name']);
        $this->assertArrayHasKey('wave_height', $fallback);
        $this->assertArrayHasKey('safety', $fallback);
        $this->assertTrue($fallback['is_fallback']);
    }
}
