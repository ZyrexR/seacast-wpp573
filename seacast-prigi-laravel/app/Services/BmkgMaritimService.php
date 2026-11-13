<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Service for Live BMKG Maritim Meteorology & Oceanographic Ingestion
 * Data Source: https://maritim.bmkg.go.id/marine2026-data/
 */
class BmkgMaritimService
{
    public const BASE_URL = 'https://maritim.bmkg.go.id/marine2026-data/';
    public const CACHE_TTL_SECONDS = 3600; // 1 hour cache to respect BMKG bandwidth

    public const PORT_MAP = [
        'prigi'       => 'XO026',
        'xo026'       => 'XO026',
        'ppn_prigi'   => 'XO026',
        'cilacap'     => 'XM001',
        'xm001'       => 'XM001',
        'pps_cilacap' => 'XM001',
    ];

    public const OCEAN_MAP = [
        'jatim'  => 'M.12',
        'm.12'   => 'M.12',
        'prigi'  => 'M.12',
        'jateng' => 'M.10',
        'm.10'   => 'M.10',
        'cilacap'=> 'M.10',
    ];

    /**
     * Fetch Live Port Weather Forecast from BMKG (3-day hourly)
     */
    public function getLivePortWeather(string $portKey = 'prigi'): array
    {
        $normalizedKey = strtolower(trim($portKey));
        $portCode = self::PORT_MAP[$normalizedKey] ?? 'XO026';
        $cacheKey = "seacast_bmkg_port_{$portCode}";

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($portCode, $normalizedKey) {
            $url = self::BASE_URL . "pelabuhan/{$portCode}.json";

            try {
                $response = Http::timeout(8)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Accept'     => 'application/json, text/plain, */*',
                    ])
                    ->withoutVerifying()
                    ->get($url);

                if ($response->successful()) {
                    $json = $response->json();
                    return $this->parsePortPayload($json);
                }

                Log::warning("BMKG Maritim Port API returned status: {$response->status()} for {$url}");
            } catch (\Throwable $e) {
                Log::warning("BMKG Maritim Port fetch failed: {$e->getMessage()}");
            }

            return $this->getFallbackPortData($normalizedKey);
        });
    }

    /**
     * Fetch Live Ocean Area Forecast from BMKG (WPP 573 Indian Ocean Sectors)
     */
    public function getLiveOceanArea(string $areaKey = 'jatim'): array
    {
        $normalizedKey = strtolower(trim($areaKey));
        $areaCode = self::OCEAN_MAP[$normalizedKey] ?? 'M.12';
        $cacheKey = "seacast_bmkg_ocean_{$areaCode}";

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($areaCode, $normalizedKey) {
            $url = self::BASE_URL . "perairan/{$areaCode}.json";

            try {
                $response = Http::timeout(8)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Accept'     => 'application/json, text/plain, */*',
                    ])
                    ->withoutVerifying()
                    ->get($url);

                if ($response->successful()) {
                    $json = $response->json();
                    return $this->parseOceanPayload($json);
                }

                Log::warning("BMKG Maritim Ocean Area API returned status: {$response->status()} for {$url}");
            } catch (\Throwable $e) {
                Log::warning("BMKG Maritim Ocean Area fetch failed: {$e->getMessage()}");
            }

            return $this->getFallbackOceanData($normalizedKey);
        });
    }

    /**
     * Get Consolidated Maritime Overview for a Port Hub
     */
    public function getMaritimeOverview(string $hub = 'prigi'): array
    {
        $portData = $this->getLivePortWeather($hub);
        $oceanData = $this->getLiveOceanArea($hub);

        // Overall safety driven by the more severe wave condition (usually ocean offshore)
        $maxWave = max((float)$portData['wave_height'], (float)$oceanData['wave_height']);
        $safety = $this->evaluateSafety($maxWave);

        return [
            'hub'              => strtoupper($hub),
            'port'             => $portData,
            'ocean_offshore'   => $oceanData,
            'max_wave_height'  => $maxWave,
            'overall_safety'   => $safety,
            'synced_at'        => now('Asia/Jakarta')->toIso8601String(),
        ];
    }

    /**
     * Evaluates maritime wave height according to BMKG & Syahbandar Regulations
     */
    public function evaluateSafety(float $waveHeight): array
    {
        if ($waveHeight >= 3.5) {
            return [
                'status'       => 'DANGER',
                'label'        => 'BAHAYA GELOMBANG EKSTREM (SPB Ditolak Syahbandar)',
                'badge_color'  => 'red',
                'is_safe'      => false,
                'advice'       => 'Gelombang ekstrem (>= 3.5 m). Dilarang keras menerbitkan Surat Persetujuan Berlayar.',
            ];
        }

        if ($waveHeight >= 2.0) {
            return [
                'status'       => 'WARNING',
                'label'        => 'WASPADA GELOMBANG TINGGI (Kapal <10 GT Dilarang)',
                'badge_color'  => 'yellow',
                'is_safe'      => true, // Safe only for medium/large vessels
                'advice'       => 'Gelombang tinggi (2.0 - 3.5 m). Perahu kecil dan kapal < 10 GT dilarang ke laut lepas.',
            ];
        }

        return [
            'status'       => 'SAFE',
            'label'        => 'AMAN MELAUT (Kondusif untuk Pelayaran)',
            'badge_color'  => 'green',
            'is_safe'      => true,
            'advice'       => 'Tinggi gelombang aman (< 2.0 m). Sangat kondusif untuk operasi penangkapan ikan.',
        ];
    }

    /**
     * Parse raw port JSON from BMKG
     */
    protected function parsePortPayload(array $json): array
    {
        $f1 = $json['forecast_day1'][0] ?? [];
        $waveHeight = (float)($f1['wave_height'] ?? 1.0);

        return [
            'port_code'      => $json['code'] ?? 'XO026',
            'port_name'      => $json['name'] ?? 'Pelabuhan Perikanan',
            'issued'         => $json['issued'] ?? now()->toDateTimeString(),
            'valid_from'     => $json['valid_from'] ?? now()->toDateString(),
            'valid_to'       => $json['valid_to'] ?? now()->addDays(3)->toDateString(),
            'weather'        => $f1['weather'] ?? 'Cerah Berawan',
            'temp_avg'       => (float)($f1['temp_avg'] ?? 27.0),
            'rh_avg'         => (int)($f1['rh_avg'] ?? 80),
            'wind_from'      => $f1['wind_from'] ?? 'Selatan',
            'wind_speed'     => (float)($f1['wind_speed'] ?? 4.0),
            'wind_gust'      => (float)($f1['wind_gust'] ?? 8.0),
            'wave_cat'       => $f1['wave_cat'] ?? 'Rendah',
            'wave_height'    => $waveHeight,
            'current_to'     => $f1['current_to'] ?? 'Timur',
            'current_speed'  => (float)($f1['current_speed'] ?? 0.65),
            'tides'          => (float)($f1['tides'] ?? 0.0),
            'safety'         => $this->evaluateSafety($waveHeight),
            'is_fallback'    => false,
            'source'         => 'BMKG Maritim Pusat (Live API)',
        ];
    }

    /**
     * Parse raw ocean area JSON from BMKG
     */
    protected function parseOceanPayload(array $json): array
    {
        $f1 = $json['forecast_day1'][0] ?? [];
        $waveHeight = (float)($f1['wave_height'] ?? 1.8);

        return [
            'area_code'        => $json['code'] ?? 'M.12',
            'area_name'        => $json['name'] ?? 'Samudra Hindia Selatan Jawa',
            'no_surat'         => $json['no_surat'] ?? 'B/ME.01.02/METOS/09/DMM/XI/2026',
            'nama_prakirawan'  => $json['nama_prakirawan'] ?? 'BMKG Maritim',
            'issued'           => $json['issued'] ?? now()->toDateTimeString(),
            'valid_from'       => $json['valid_from'] ?? now()->toDateString(),
            'valid_to'         => $json['valid_to'] ?? now()->addDays(3)->toDateString(),
            'weather'          => $f1['weather'] ?? 'Berawan',
            'wind_from'        => $f1['wind_from'] ?? 'Tenggara',
            'wind_speed'       => (float)($f1['wind_speed'] ?? 6.0),
            'wind_gust'        => (float)($f1['wind_gust'] ?? 12.0),
            'wave_cat'         => $f1['wave_cat'] ?? 'Sedang',
            'wave_height'      => $waveHeight,
            'current_to'       => $f1['current_to'] ?? 'Barat Daya',
            'current_speed'    => (float)($f1['current_speed'] ?? 1.5),
            'safety'           => $this->evaluateSafety($waveHeight),
            'is_fallback'      => false,
            'source'           => 'BMKG Maritim Pusat (Live API)',
        ];
    }

    /**
     * Graceful seasonal fallback for port data
     */
    protected function getFallbackPortData(string $portKey): array
    {
        $isCilacap = str_contains($portKey, 'cilacap') || str_contains($portKey, 'xm001');
        $waveHeight = $isCilacap ? 0.3 : 1.1;

        return [
            'port_code'      => $isCilacap ? 'XM001' : 'XO026',
            'port_name'      => $isCilacap ? 'PPS Cilacap' : 'PPN Prigi',
            'issued'         => now()->format('Y-m-d 00:00 UTC'),
            'valid_from'     => now()->format('Y-m-d 00:00 UTC'),
            'valid_to'       => now()->addDays(3)->format('Y-m-d 00:00 UTC'),
            'weather'        => 'Berawan',
            'temp_avg'       => 27.5,
            'rh_avg'         => 82,
            'wind_from'      => 'Selatan',
            'wind_speed'     => 4.0,
            'wind_gust'      => 8.0,
            'wave_cat'       => 'Rendah',
            'wave_height'    => $waveHeight,
            'current_to'     => 'Timur',
            'current_speed'  => 0.65,
            'tides'          => 0.1,
            'safety'         => $this->evaluateSafety($waveHeight),
            'is_fallback'    => true,
            'source'         => 'Klimatologi Oseanografi Musiman (Cadangan Aman)',
        ];
    }

    /**
     * Graceful seasonal fallback for ocean area data
     */
    protected function getFallbackOceanData(string $areaKey): array
    {
        $isJateng = str_contains($areaKey, 'jateng') || str_contains($areaKey, 'm.10');
        $waveHeight = $isJateng ? 1.6 : 1.8;

        return [
            'area_code'        => $isJateng ? 'M.10' : 'M.12',
            'area_name'        => $isJateng ? 'Samudra Hindia selatan Jawa Tengah' : 'Samudra Hindia selatan Jawa Timur',
            'no_surat'         => 'B/ME.01.02/METOS/OFFLINE-FALLBACK',
            'nama_prakirawan'  => 'Prakirawan BMKG Pusat',
            'issued'           => now()->format('Y-m-d 00:00 UTC'),
            'valid_from'       => now()->format('Y-m-d 00:00 UTC'),
            'valid_to'         => now()->addDays(3)->format('Y-m-d 00:00 UTC'),
            'weather'          => 'Berawan',
            'wind_from'        => 'Tenggara',
            'wind_speed'       => 6.5,
            'wind_gust'        => 12.0,
            'wave_cat'         => 'Sedang',
            'wave_height'      => $waveHeight,
            'current_to'       => 'Barat Daya',
            'current_speed'    => 1.45,
            'safety'           => $this->evaluateSafety($waveHeight),
            'is_fallback'      => true,
            'source'           => 'Klimatologi Oseanografi Musiman (Cadangan Aman)',
        ];
    }
}
