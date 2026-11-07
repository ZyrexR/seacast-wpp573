<?php

namespace App\Domain\Oceanography\Services;

use App\Domain\Oceanography\DTO\VariableMetadata;
use App\Domain\Oceanography\DTO\OceanographicConditions;
use Illuminate\Support\Facades\DB;

class OceanographicDataService
{
    public const THERMAL_FRONT_THRESHOLD = 0.035; // °C/km
    public const CHLA_BLOOM_THRESHOLD = 0.40;     // mg/m³
    public const SHELF_BREAK_MIN_DEPTH = 150.0;   // m
    public const SHELF_BREAK_MAX_DEPTH = 1200.0;  // m

    protected string $connection = 'pgsql';

    public function getVariableMetadataRegistry(): array
    {
        return [
            'sst' => new VariableMetadata(
                key: 'sst',
                name: 'Suhu Permukaan Laut (SST)',
                unit: '°C',
                source: 'NOAA CoralTemp OISST Daily Blended High-Resolution',
                sourceType: 'OBSERVED',
                minValid: 20.0,
                maxValid: 33.0,
                resolution: '0.05° (~5 km)',
                temporalFrequency: 'Daily',
                description: 'Suhu permukaan laut resolusi tinggi untuk memetakan kantong air dingin dan front termal.'
            ),
            'chla' => new VariableMetadata(
                key: 'chla',
                name: 'Klorofil-a (Chl-a)',
                unit: 'mg/m³',
                source: 'Copernicus Sentinel-3 OLCI Ocean Colour',
                sourceType: 'OBSERVED',
                minValid: 0.01,
                maxValid: 5.0,
                resolution: '0.04° (~4 km)',
                temporalFrequency: 'Daily Composite',
                description: 'Konsentrasi biomassa fitoplankton penanda zona produktivitas dan feeding core tuna.'
            ),
            'currents' => new VariableMetadata(
                key: 'currents',
                name: 'Arus Permukaan Laut',
                unit: 'm/s',
                source: 'HYCOM + NCODA Global 1/12° Analysis',
                sourceType: 'MODELED',
                minValid: 0.0,
                maxValid: 2.5,
                resolution: '0.08° (~9 km)',
                temporalFrequency: 'Daily',
                description: 'Vektor kecepatan dan arah arus permukaan laut perairan WPPNRI 573.'
            ),
            'wind' => new VariableMetadata(
                key: 'wind',
                name: 'Kecepatan Angin & Gelombang',
                unit: 'm/s',
                source: 'Simulasi Oseanografi Musiman BMKG Terkalibrasi (Fallback Lokal)',
                sourceType: 'SIMULATED',
                minValid: 0.5,
                maxValid: 25.0,
                resolution: '0.10° (~11 km)',
                temporalFrequency: 'Daily Forecast',
                description: 'Kecepatan angin permukaan dan tinggi gelombang signifikan untuk keselamatan armada nelayan.'
            ),
            'bathymetry' => new VariableMetadata(
                key: 'bathymetry',
                name: 'Batimetri Dasar Laut',
                unit: 'm',
                source: 'GEBCO Global Ocean Depth Gridded Dataset 2024',
                sourceType: 'OBSERVED',
                minValid: 5.0,
                maxValid: 7000.0,
                resolution: '15 arc-second (~450 m)',
                temporalFrequency: 'Static Terrain',
                description: 'Topografi kedalaman dasar laut untuk mendelineasi paparan pesisir, lereng benua (150-1200m), dan palung oseanik.'
            ),
        ];
    }

    public function isThermalFront(float $gradient): bool
    {
        return $gradient >= self::THERMAL_FRONT_THRESHOLD;
    }

    public function validateSstRange(float $sst): bool
    {
        $meta = $this->getVariableMetadataRegistry()['sst'];
        return $sst >= $meta->minValid && $sst <= $meta->maxValid;
    }

    public function validateChlaRange(float $chla): bool
    {
        $meta = $this->getVariableMetadataRegistry()['chla'];
        return $chla >= $meta->minValid && $chla <= $meta->maxValid;
    }

    public function getConditionsForCell(string $cellId, string $date): ?OceanographicConditions
    {
        $row = DB::connection($this->connection)
            ->selectOne("
                SELECT 
                    dp.cell_id, dp.prediction_date, dp.sst_c, dp.chla_mg_m3, 
                    dp.current_velocity_ms, dp.wave_height_m, dp.wind_speed_ms,
                    dp.safety_advisory,
                    cg.depth_m, cg.dist_port_nm
                FROM daily_predictions dp
                JOIN canonical_grids cg ON dp.cell_id = cg.cell_id
                WHERE dp.cell_id = ? AND dp.prediction_date = ?
            ", [$cellId, $date]);

        if (!$row) {
            return null;
        }

        // Check if monthly gradient is available or approximate from surrounding
        $monthly = DB::connection($this->connection)
            ->selectOne("SELECT grad_sst, front_indicator FROM grid_monthly_predictions WHERE cell_id = ? LIMIT 1", [$cellId]);

        $gradSst = $monthly ? (float)$monthly->grad_sst : null;
        $isThermalFront = $gradSst ? $this->isThermalFront($gradSst) : false;

        return new OceanographicConditions(
            cellId: $row->cell_id,
            date: (string)$row->prediction_date,
            sst: (float)$row->sst_c,
            gradSst: $gradSst,
            isThermalFront: $isThermalFront,
            chla: (float)$row->chla_mg_m3,
            currentVelocity: $row->current_velocity_ms !== null ? (float)$row->current_velocity_ms : null,
            waveHeight: $row->wave_height_m !== null ? (float)$row->wave_height_m : null,
            windSpeed: $row->wind_speed_ms !== null ? (float)$row->wind_speed_ms : null,
            depth: $row->depth_m !== null ? (float)$row->depth_m : null,
            distPortNm: $row->dist_port_nm !== null ? (float)$row->dist_port_nm : null,
            safetyAdvisory: (string)$row->safety_advisory,
            qualityFlags: [
                'sst_valid' => $this->validateSstRange((float)$row->sst_c),
                'chla_valid' => $this->validateChlaRange((float)$row->chla_mg_m3),
                'wave_source' => 'Simulasi Oseanografi Musiman BMKG Terkalibrasi (Fallback Lokal)',
            ]
        );
    }
}

