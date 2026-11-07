<?php

namespace App\Domain\ScientificData\Services;

use Illuminate\Support\Facades\DB;

class SimulationService
{
    /**
     * Compute Gaussian Habitat Suitability Index (HSI).
     */
    public function calcGaussianHsi(float $sst, float $chla, array $optSst, array $optChla): float
    {
        [$sstMin, $sstMax] = $optSst;
        $sstMid = ($sstMin + $sstMax) / 2.0;
        $sstWidth = ($sstMax - $sstMin) / 1.5;
        $sstScore = exp(-pow(($sst - $sstMid) / $sstWidth, 2));

        [$chlaMin, $chlaMax] = $optChla;
        $chlaMid = ($chlaMin + $chlaMax) / 2.0;
        $chlaWidth = ($chlaMax - $chlaMin) / 1.5;
        $chlaScore = exp(-pow(($chla - $chlaMid) / $chlaWidth, 2));

        return round(sqrt($sstScore * $chlaScore), 4);
    }

    /**
     * Tongkol (Euthynnus spp. / Auxis thazard) HSI Calibration.
     * Literature: Tangke et al., 2020; Hidayat et al., 2021; Syah et al., 2023.
     * Neritic coastal preference: SST 27.0 - 30.5°C, Chl-a 0.20 - 0.50 mg/m³, depth < 200m.
     */
    public function calcTongkolHsi(float $sst, float $chla, float $depth = 100.0): float
    {
        $sstMid = 28.75;
        $sstWidth = (30.5 - 27.0) / 1.5;
        $siSst = exp(-pow(($sst - $sstMid) / $sstWidth, 2));

        $chlaMid = 0.35;
        $chlaWidth = (0.50 - 0.20) / 1.5;
        $siChla = exp(-pow(($chla - $chlaMid) / $chlaWidth, 2));

        // Neritic / coastal shallow preference (< 200m)
        $siDepth = $depth <= 150.0 
            ? 1.0 
            : exp(-pow(($depth - 150.0) / 150.0, 2));

        $hsi = (0.45 * $siSst) + (0.40 * $siChla) + (0.15 * $siDepth);
        return round(max(0.0, min(1.0, $hsi)), 3);
    }

    /**
     * Madidihang / Yellowfin Tuna (Thunnus albacares) HSI Calibration.
     * Literature: Vaihola et al., 2023; Yati et al., 2024.
     * Deep oceanic & slope preference: SST 23.0 - 31.5°C, Chl-a 0.05 - 0.35 mg/m³, depth > 200m.
     */
    public function calcMadidihangHsi(float $sst, float $chla, float $depth = 1000.0): float
    {
        $sstMid = 27.25;
        $sstWidth = (31.5 - 23.0) / 1.5;
        $siSst = exp(-pow(($sst - $sstMid) / $sstWidth, 2));

        $chlaMid = 0.20;
        $chlaWidth = 0.25;
        $siChla = exp(-pow(($chla - $chlaMid) / $chlaWidth, 2));

        // Oceanic deep water preference (> 200m)
        $siDepth = $depth >= 200.0
            ? 1.0
            : max(0.02, pow($depth / 200.0, 2));

        $hsi = ((0.55 * $siSst) + (0.45 * $siChla)) * $siDepth;
        return round(max(0.0, min(1.0, $hsi)), 3);
    }

    /**
     * Cakalang / Skipjack Tuna (Katsuwonus pelamis) HSI Calibration.
     * Literature: Syah et al., 2023; Zainuddin et al., 2023; Muhyun et al., 2025.
     * High productivity fronts: Chl-a 50%, SST 35% (28.5 - 31.5°C), Wave dynamics 15%.
     */
    public function calcCakalangHsi(float $sst, float $chla, float $waveHeight = 1.5): float
    {
        $sstMid = 30.0;
        $sstWidth = (31.5 - 28.5) / 1.5;
        $siSst = exp(-pow(($sst - $sstMid) / $sstWidth, 2));

        $chlaMid = 0.25;
        $chlaWidth = (0.35 - 0.15) / 1.5;
        $siChla = exp(-pow(($chla - $chlaMid) / $chlaWidth, 2));

        // Wave safety & dynamics
        $siWave = $waveHeight <= 2.0 
            ? 1.0 
            : max(0.1, exp(-pow(($waveHeight - 2.0) / 1.5, 2)));

        $hsi = (0.50 * $siChla) + (0.35 * $siSst) + (0.15 * $siWave);
        return round(max(0.0, min(1.0, $hsi)), 3);
    }

    /**
     * Run climate & oceanographic perturbation simulation across spatial grid.
     */
    public function simulateClimateScenario(float $sstDelta, float $chlaMultiplier, ?string $date = null, int $limit = 8129): array
    {
        $sql = "
            SELECT 
                dp.cell_id,
                dp.sst_c,
                dp.chla_mg_m3,
                dp.bycatch_traffic_light,
                cg.dist_port_nm,
                cg.sector,
                cg.is_prigi_inshore
            FROM daily_predictions dp
            JOIN canonical_grids cg ON dp.cell_id = cg.cell_id
            WHERE dp.prediction_date::date = COALESCE(:date::date, (SELECT MAX(prediction_date)::date FROM daily_predictions))
            ORDER BY cg.dist_port_nm ASC
            LIMIT :limit
        ";

        $rows = DB::connection('pgsql')->select($sql, [
            'date' => $date,
            'limit' => $limit
        ]);

        $baselineHotspots = 0;
        $simulatedHotspots = 0;
        $zoneCounts = ['Z1' => 0, 'Z2' => 0, 'Z3' => 0, 'Z4' => 0, 'Z5' => 0];

        $simulatedCells = [];

        foreach ($rows as $r) {
            $baseSst = (float)($r->sst_c ?? 28.5);
            $baseChla = (float)($r->chla_mg_m3 ?? 0.25);
            $distNm = (float)($r->dist_port_nm ?? 0);

            $simSst = $baseSst + $sstDelta;
            $simChla = max(0.01, $baseChla * $chlaMultiplier);

            // Compute multi-species simulated HSIs
            $hsiCakalang = $this->calcGaussianHsi($simSst, $simChla, [28.0, 29.5], [0.20, 0.45]);
            $hsiMadidihang = $this->calcGaussianHsi($simSst, $simChla, [27.0, 29.0], [0.15, 0.40]);
            $hsiAlbakor = $this->calcGaussianHsi($simSst, $simChla, [18.0, 22.0], [0.10, 0.35]);
            $hsiTongkol = $this->calcTongkolHsi($simSst, $simChla, 250.0);

            $isHotspot = ($hsiCakalang >= 0.70 || $hsiMadidihang >= 0.70);
            if ($isHotspot) {
                $simulatedHotspots++;
            }

            // Baseline check
            $baseHsiCakalang = $this->calcGaussianHsi($baseSst, $baseChla, [28.0, 29.5], [0.20, 0.45]);
            if ($baseHsiCakalang >= 0.70) {
                $baselineHotspots++;
            }

            // DOM Waterfall Priority: Z4 (Nursery) > Z5 (Bycatch Risk) > Z1 (PFG) > Z2 (Secondary) > Z3 (Other)
            if ($r->is_prigi_inshore && $distNm <= 12.0) {
                $domZone = 'Z4'; // Coastal nursery zone
            } elseif ($isHotspot && $r->bycatch_traffic_light === 'RED') {
                $domZone = 'Z5'; // Bycatch restriction advisory
            } elseif ($isHotspot && $r->bycatch_traffic_light !== 'RED') {
                $domZone = 'Z1'; // Prime fishing ground
            } elseif ($hsiCakalang >= 0.50 || $hsiMadidihang >= 0.50) {
                $domZone = 'Z2'; // Secondary fishing ground
            } else {
                $domZone = 'Z3'; // Feeding ground / buffer
            }

            $zoneCounts[$domZone]++;

            $simulatedCells[] = [
                'cell_id' => $r->cell_id,
                'sim_sst' => round($simSst, 2),
                'sim_chla' => round($simChla, 3),
                'hsi_cakalang' => $hsiCakalang,
                'hsi_madidihang' => $hsiMadidihang,
                'hsi_albakor' => $hsiAlbakor,
                'hsi_tongkol' => $hsiTongkol,
                'is_hotspot' => $isHotspot,
                'dom_zone' => $domZone,
                'dist_port_nm' => $distNm,
            ];
        }

        $total = max(1, count($rows));

        return [
            'scenario' => [
                'sst_delta' => $sstDelta,
                'chla_multiplier' => $chlaMultiplier,
                'baseline_date' => $date ?? '2026-11-03',
            ],
            'summary' => [
                'total_cells' => count($rows),
                'baseline_hotspots' => $baselineHotspots,
                'simulated_hotspots' => $simulatedHotspots,
                'hotspot_delta_pct' => round((($simulatedHotspots - $baselineHotspots) / max(1, $baselineHotspots)) * 100, 1),
                'zones' => [
                    'Z1' => ['count' => $zoneCounts['Z1'], 'pct' => round(($zoneCounts['Z1'] / $total) * 100, 1)],
                    'Z2' => ['count' => $zoneCounts['Z2'], 'pct' => round(($zoneCounts['Z2'] / $total) * 100, 1)],
                    'Z3' => ['count' => $zoneCounts['Z3'], 'pct' => round(($zoneCounts['Z3'] / $total) * 100, 1)],
                    'Z4' => ['count' => $zoneCounts['Z4'], 'pct' => round(($zoneCounts['Z4'] / $total) * 100, 1)],
                    'Z5' => ['count' => $zoneCounts['Z5'], 'pct' => round(($zoneCounts['Z5'] / $total) * 100, 1)],
                ],
            ],
            'cells' => $simulatedCells,
        ];
    }

    /**
     * Compute vessel operational cost & revenue model per GT class.
     */
    public function simulateVesselEconomics(string $vesselClass, float $targetDistanceNm, string $targetSpecies = 'cakalang', float $solarPricePerLiter = 6800.0): array
    {
        $specs = [
            '< 10 GT' => ['speed_knots' => 7.5, 'fuel_liters_per_nm' => 1.5, 'max_range_nm' => 12.0, 'base_catch_kg' => 180],
            '10 - 20 GT' => ['speed_knots' => 9.0, 'fuel_liters_per_nm' => 2.8, 'max_range_nm' => 30.0, 'base_catch_kg' => 450],
            '20 - 30 GT' => ['speed_knots' => 11.0, 'fuel_liters_per_nm' => 4.5, 'max_range_nm' => 60.0, 'base_catch_kg' => 1200],
        ];

        $spec = $specs[$vesselClass] ?? $specs['< 10 GT'];

        $roundTripDistance = $targetDistanceNm * 2.0;
        $travelTimeHours = round($roundTripDistance / $spec['speed_knots'], 1);
        $estimatedFuelLiters = round($roundTripDistance * $spec['fuel_liters_per_nm'], 1);
        $estimatedFuelCostRp = round($estimatedFuelLiters * $solarPricePerLiter);
        $isOutOfRange = ($targetDistanceNm > $spec['max_range_nm']);

        $tunaPrices = [
            'cakalang' => 22000,
            'madidihang' => 45000,
            'albakor' => 38000,
            'tongkol' => 28000,
        ];

        $tunaPricePerKg = $tunaPrices[strtolower($targetSpecies)] ?? 22000;
        $grossRevenueRp = $spec['base_catch_kg'] * $tunaPricePerKg;
        $netProfitRp = $grossRevenueRp - $estimatedFuelCostRp;

        return [
            'vessel_class' => $vesselClass,
            'target_distance_nm' => $targetDistanceNm,
            'target_species' => $targetSpecies,
            'solar_price_per_liter' => $solarPricePerLiter,
            'round_trip_nm' => $roundTripDistance,
            'travel_time_hours' => $travelTimeHours,
            'estimated_fuel_liters' => $estimatedFuelLiters,
            'estimated_fuel_cost_rp' => $estimatedFuelCostRp,
            'base_catch_kg' => $spec['base_catch_kg'],
            'tuna_price_per_kg' => $tunaPricePerKg,
            'gross_revenue_rp' => $grossRevenueRp,
            'net_profit_rp' => $netProfitRp,
            'max_range_nm' => $spec['max_range_nm'],
            'is_out_of_range' => $isOutOfRange,
            'safety_status' => $isOutOfRange ? 'DANGER_OUT_OF_RANGE' : 'SAFE_WITHIN_RANGE',
        ];
    }
}

