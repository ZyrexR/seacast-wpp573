<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class BmkgGridForecastService
{
    private const BASE_URL = 'https://maritim.bmkg.go.id/pusmar/api23';

    public function forecast(float $lat, float $lon, ?\DateTimeInterface $forTime = null): ?array
    {
        $runs = Cache::remember('bmkg_inawaves_modelrun', 900, function () {
            $response = Http::timeout(12)->withoutVerifying()->get(self::BASE_URL . '/modelrun');
            return $response->successful() ? $response->json() : null;
        });
        $run = $runs['w3g_hires'][0] ?? null;
        if (!is_string($run)) {
            return null;
        }
        $base = \Carbon\Carbon::parse($run)->utc();
        $now = now('UTC');
        $target = $forTime === null ? $now : \Carbon\Carbon::parse($forTime->format(DATE_ATOM))->utc();
        if ($base->diffInHours($now, false) > 24 || $base->greaterThan($now)) {
            return null;
        }
        if ($target->lessThan($now->copy()->subHours(3)) || $target->greaterThan($now->copy()->addDays(7))) {
            return null;
        }
        $hours = max(0, (int) floor($base->diffInHours($target, false) / 3) * 3);

        foreach ([$hours, max(0, $hours - 3), max(0, $hours - 6)] as $offset) {
            $valid = $base->copy()->addHours($offset);
            if ($forTime !== null && abs($valid->diffInHours($target, false)) > 3) {
                continue;
            }
            $baseKey = $base->format('YmdHi');
            $validKey = $valid->format('YmdHi');
            $wave = $this->field('hs', $baseKey, $validKey);
            $wind = $this->field('wind', $baseKey, $validKey);
            if ($wave === null || $wind === null || count($wind) < 2) {
                continue;
            }

            $waveSample = $this->sample($wave[0] ?? [], $lat, $lon);
            $east = $this->sample($wind[0], $lat, $lon);
            $north = $this->sample($wind[1], $lat, $lon);
            if ($waveSample === null || $east === null || $north === null ||
                $waveSample['value'] < 0 || $waveSample['value'] > 20) {
                continue;
            }
            $speed = hypot($east['value'], $north['value']);
            return [
                'status' => 'AVAILABLE',
                'source' => 'BMKG InaWaves',
                'evidence_level' => 'MODEL_FORECAST_NEAREST_GRID_POINT',
                'requested_lat' => $lat,
                'requested_lon' => $lon,
                'model_lat' => $waveSample['lat'],
                'model_lon' => $waveSample['lon'],
                'wave_height_m' => round($waveSample['value'], 2),
                'wind_speed_knots' => round($speed, 1),
                'wind_from_degrees' => round(fmod(rad2deg(atan2(-$east['value'], -$north['value'])) + 360, 360)),
                'model_run_at' => $base->toIso8601String(),
                'forecast_at' => $valid->toIso8601String(),
            ];
        }
        return null;
    }

    private function field(string $parameter, string $base, string $valid): ?array
    {
        return Cache::remember("bmkg_inawaves_{$parameter}_{$base}_{$valid}", 3600, function () use ($parameter, $base, $valid) {
            $response = Http::timeout(20)->withoutVerifying()->get(self::BASE_URL . "/arr_req/inawaves/{$parameter}/{$base}/{$valid}");
            if (!$response->successful()) {
                return null;
            }
            $decoded = json_decode($response->body(), true);
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }
            return is_array($decoded) ? $decoded : null;
        });
    }

    private function sample(array $field, float $lat, float $lon): ?array
    {
        $header = $field['header'] ?? [];
        $data = $field['data'] ?? [];
        $dx = (float) ($header['dx'] ?? 0);
        $dy = (float) ($header['dy'] ?? 0);
        if ($dx <= 0 || $dy <= 0 || !is_array($data)) {
            return null;
        }
        $west = (float) ($header['lo1'] ?? 0);
        $east = (float) ($header['lo2'] ?? 0);
        $north = (float) ($header['la1'] ?? 0);
        $south = (float) ($header['la2'] ?? 0);
        $cols = (int) round(($east - $west) / $dx) + 1;
        $rows = (int) round(($north - $south) / $dy) + 1;
        if ($cols < 2 || $rows < 2 || $cols * $rows !== count($data) ||
            $lon < $west || $lon > $east || $lat < $south || $lat > $north) {
            return null;
        }
        $column = (int) round(($lon - $west) / $dx);
        $row = (int) round(($north - $lat) / $dy);
        $value = $data[$row * $cols + $column] ?? null;
        if (!is_numeric($value) || !is_finite((float) $value)) {
            return null;
        }
        return ['value' => (float) $value, 'lat' => round($north - $row * $dy, 4), 'lon' => round($west + $column * $dx, 4)];
    }
}
