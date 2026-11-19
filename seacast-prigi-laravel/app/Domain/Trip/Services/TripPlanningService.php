<?php

namespace App\Domain\Trip\Services;

class TripPlanningService
{
    /** WGS84 mean Earth radius used by the Haversine approximation. */
    public const EARTH_RADIUS_KM = 6371.0088;

    /** International nautical mile (IHO/BIPM): exactly 1,852 metres. */
    public const KM_PER_NAUTICAL_MILE = 1.852;

    /** Derived Haversine radius in nautical miles. */
    public const EARTH_RADIUS_NM = 3440.0695;

    public const PORTS = [
        'PPN_PRIGI' => [
            'code' => 'PPN_PRIGI',
            'name' => 'PPN Prigi',
            'province' => 'Jawa Timur',
            'lat' => -8.2833,
            'lon' => 111.7275,
            'prefix' => 'PRG',
        ],
        'PPS_CILACAP' => [
            'code' => 'PPS_CILACAP',
            'name' => 'PPS Cilacap',
            'province' => 'Jawa Tengah',
            'lat' => -7.7472,
            'lon' => 109.0069,
            'prefix' => 'CLP',
        ],
        'PPN_PALABUHANRATU' => [
            'code' => 'PPN_PALABUHANRATU',
            'name' => 'PPN Palabuhanratu',
            'province' => 'Jawa Barat',
            'lat' => -6.9850,
            'lon' => 106.5410,
            'prefix' => 'PLB',
        ],
    ];

    public const GT_SPECS = [
        '< 10 GT' => [
            'speed_knots' => 7.5,
            'fuel_liters_per_nm' => 1.5,
            'daily_provisions_cost_rp' => 150000,
            'max_hold_capacity_kg' => 400,
            'max_endurance_days' => 2,
            'max_legal_range_nm' => 12.0,
            'base_catch_kg' => 180,
            'gear_code' => 'LTL', // Pancing tonda
            'gear_name' => 'Pancing Tonda (Troll Line)',
        ],
        '10 - 20 GT' => [
            'speed_knots' => 9.0,
            'fuel_liters_per_nm' => 2.8,
            'daily_provisions_cost_rp' => 350000,
            'max_hold_capacity_kg' => 1500,
            'max_endurance_days' => 5,
            'max_legal_range_nm' => 30.0,
            'base_catch_kg' => 450,
            'gear_code' => 'LHP-PUT', // Pancing ulur tuna
            'gear_name' => 'Pancing Ulur Tuna (Handline)',
        ],
        '20 - 30 GT' => [
            'speed_knots' => 11.0,
            'fuel_liters_per_nm' => 4.5,
            'daily_provisions_cost_rp' => 650000,
            'max_hold_capacity_kg' => 5000,
            'max_endurance_days' => 10,
            'max_legal_range_nm' => 60.0,
            'base_catch_kg' => 1200,
            'gear_code' => 'PS1-B', // Purse seine pelagis besar
            'gear_name' => 'Pukat Cincin Pelagis Besar (Purse Seine)',
        ],
        '> 30 GT' => [
            'speed_knots' => 12.5,
            'fuel_liters_per_nm' => 7.0,
            'daily_provisions_cost_rp' => 1200000,
            'max_hold_capacity_kg' => 15000,
            'max_endurance_days' => 14,
            'max_legal_range_nm' => 200.0,
            'base_catch_kg' => 3500,
            'gear_code' => 'LLD-RT', // Rawai tuna
            'gear_name' => 'Rawai Tuna (Tuna Longline)',
        ],
    ];

    public const COMMODITY_PRICES = [
        'cakalang' => 22000,
        'madidihang' => 45000,
        'tongkol' => 28000,
        'tuna_sirip_kuning' => 45000,
        'tongkol_krai' => 28000,
    ];

    public const MIN_EFFECTIVE_FISHING_HOURS = 8.0;
    public const STANDARD_EFFECTIVE_FISHING_HOURS_PER_DAY = 16.0;
    public const DAILY_OPERATIONAL_SEARCH_NM = 10.0;
    public const DEFAULT_SOLAR_PRICE_PER_LITER = 6800.0;
    public const NON_SUBSIDIZED_SOLAR_PRICE_PER_LITER = 15000.0;

    public static function fuelTypeForGtClass(string $gtClass): string
    {
        return $gtClass === '> 30 GT' ? 'NON_SUBSIDIZED' : 'SUBSIDIZED';
    }

    private static function commodityDisplayName(string $commodity): string
    {
        return match (str_replace([' ', '-'], '_', strtolower(trim($commodity)))) {
            'tuna_sirip_kuning', 'madidihang' => 'Tuna Sirip Kuning',
            'tongkol', 'tongkol_krai' => 'Tongkol Krai',
            'cakalang' => 'Cakalang',
            default => ucfirst($commodity),
        };
    }

    public const READINESS_FACTORS = [
        'OPTIMAL' => 1.0,
        'CUKUP' => 0.85,
        'TERBATAS' => 0.65,
    ];

    /**
     * Public dual-hub list. PPN_PALABUHANRATU stays in PORTS for legacy calls.
     */
    public static function availablePorts(): array
    {
        return array_intersect_key(self::PORTS, array_flip([
            'PPN_PRIGI',
            'PPS_CILACAP',
        ]));
    }

    public function calculateDistanceAndBearing(string $portCode, float $targetLat, float $targetLon): array
    {
        $port = self::PORTS[$portCode] ?? self::PORTS['PPN_PRIGI'];
        $portLat = $port['lat'];
        $portLon = $port['lon'];

        // Great circle distance in km
        $earthRadiusKm = self::EARTH_RADIUS_KM;
        $dLat = deg2rad($targetLat - $portLat);
        $dLon = deg2rad($targetLon - $portLon);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($portLat)) * cos(deg2rad($targetLat)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distKm = $earthRadiusKm * $c;
        $distNm = $distKm / self::KM_PER_NAUTICAL_MILE;

        // Initial compass bearing
        $y = sin($dLon) * cos(deg2rad($targetLat));
        $x = cos(deg2rad($portLat)) * sin(deg2rad($targetLat)) -
             sin(deg2rad($portLat)) * cos(deg2rad($targetLat)) * cos($dLon);
        $bearingDeg = (fmod(rad2deg(atan2($y, $x)) + 360, 360));

        // Compass 8-point direction
        $compassPoints = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
        $compassIndex = (int)round($bearingDeg / 45) % 8;
        $compassDir = $compassPoints[$compassIndex];

        return [
            'port_code' => $port['code'],
            'port_name' => $port['name'],
            'port_lat' => $portLat,
            'port_lon' => $portLon,
            'target_lat' => $targetLat,
            'target_lon' => $targetLon,
            'distance_km' => round($distKm, 2),
            'distance_nm' => round($distNm, 2),
            'bearing_degrees' => round($bearingDeg, 1),
            'compass_direction' => $compassDir,
        ];
    }

    public function calculateFuelEconomics(float $distanceNm, string $gtClass, float $solarPricePerLiter = 6800.0): array
    {
        $spec = self::GT_SPECS[$gtClass] ?? self::GT_SPECS['< 10 GT'];
        $roundTripNm = round($distanceNm * 2.0, 2);
        $travelTimeHours = round($roundTripNm / $spec['speed_knots'], 1);

        $baseFuelLiters = round($roundTripNm * $spec['fuel_liters_per_nm'], 1);
        $safetyReserveLiters = round($baseFuelLiters * 0.15, 1); // Mandatory +15%
        $totalFuelLiters = round($baseFuelLiters + $safetyReserveLiters, 1);
        $totalFuelCostRp = (int)round($totalFuelLiters * $solarPricePerLiter);

        return [
            'gt_class' => $gtClass,
            'round_trip_nm' => $roundTripNm,
            'speed_knots' => $spec['speed_knots'],
            'travel_time_hours' => $travelTimeHours,
            'fuel_rate_liters_per_nm' => $spec['fuel_liters_per_nm'],
            'base_fuel_liters' => $baseFuelLiters,
            'safety_reserve_liters' => $safetyReserveLiters,
            'total_fuel_liters' => $totalFuelLiters,
            'subsidized_price_per_liter' => self::DEFAULT_SOLAR_PRICE_PER_LITER,
            'solar_price_per_liter' => $solarPricePerLiter,
            'total_fuel_cost_rp' => $totalFuelCostRp,
        ];
    }

    private function normalizeTripDays(mixed $tripDays): int
    {
        $normalized = filter_var($tripDays, FILTER_VALIDATE_INT);

        return $normalized !== false && $normalized >= 1 && $normalized <= 14
            ? $normalized
            : 1;
    }

    public function calculateTripDuration(float $travelTimeHours, int $tripDays): array
    {
        $availableHours = $tripDays * 24.0;
        $effectiveFishingHours = max(0.0, round($availableHours - $travelTimeHours, 1));
        $status = $effectiveFishingHours >= self::MIN_EFFECTIVE_FISHING_HOURS
            ? 'LAYAK'
            : 'PERLU_TAMBAH_HARI';

        return [
            'available_trip_hours' => round($availableHours, 1),
            'effective_fishing_hours' => $effectiveFishingHours,
            'duration_feasibility_status' => $status,
            'duration_warning' => $status === 'LAYAK'
                ? 'Durasi melaut menyediakan waktu tangkap efektif yang memadai.'
                : sprintf(
                    'Tambah hari: waktu tangkap efektif hanya %.1f jam; minimum operasional %.1f jam.',
                    $effectiveFishingHours,
                    self::MIN_EFFECTIVE_FISHING_HOURS
                ),
        ];
    }

    public function calculateOperationalEconomics(
        array $fuel,
        string $gtClass,
        int $tripDays,
        float $solarPricePerLiter = self::DEFAULT_SOLAR_PRICE_PER_LITER
    ): array {
        $spec = self::GT_SPECS[$gtClass] ?? self::GT_SPECS['< 10 GT'];
        $dailyOpsFuelLiters = round(self::DAILY_OPERATIONAL_SEARCH_NM * $spec['fuel_liters_per_nm'], 1);
        $totalOpsFuelLiters = round($dailyOpsFuelLiters * $tripDays, 1);
        $operationalFuelCostRp = (int)round($totalOpsFuelLiters * $solarPricePerLiter);
        $dailyProvisionsCostRp = $spec['daily_provisions_cost_rp'];
        $provisionsCostRp = $tripDays * $dailyProvisionsCostRp;
        $totalOperationalCostRp = (int)$fuel['total_fuel_cost_rp'] + $operationalFuelCostRp + $provisionsCostRp;

        return [
            'daily_ops_fuel_liters' => $dailyOpsFuelLiters,
            'total_ops_fuel_liters' => $totalOpsFuelLiters,
            'operational_fuel_cost_rp' => $operationalFuelCostRp,
            'daily_provisions_cost_rp' => $dailyProvisionsCostRp,
            'provisions_cost_rp' => $provisionsCostRp,
            'total_operational_cost_rp' => $totalOperationalCostRp,
        ];
    }

    private function applyDurationToCatchProjection(
        array $catch,
        array $duration,
        int $tripDays
    ): array {
        $durationFactor = round(
            min(
                (float)$tripDays,
                max(0.0, (float)$duration['effective_fishing_hours']) / self::STANDARD_EFFECTIVE_FISHING_HOURS_PER_DAY
            ),
            2
        );
        $catch['duration_factor'] = $durationFactor;
        $catch['projected_kg'] = (int)round($catch['projected_kg'] * $durationFactor);
        $catch['gross_revenue_rp'] = (int)round(
            $catch['projected_kg'] * $catch['benchmark_price_per_kg']
        );

        return $catch;
    }

    public function validatePermenKpCompliance(string $gtClass, float $distanceNm): array
    {
        $citation = "Permen KP No. 59/PERMEN-KP/2020 tentang Jalur Penangkapan Ikan dan Alat Penangkapan Ikan di WPPNRI dan Laut Lepas";

        if ($gtClass === '< 10 GT' && $distanceNm > 12.0) {
            return [
                'status' => 'WARNING_OUT_OF_LEGAL_RANGE',
                'status_label' => 'PERINGATAN (> 12 NM)',
                'is_compliant' => false,
                'jalur_target' => 'Jalur III (> 12 NM / ZEE)',
                'jalur_label' => 'Jalur III (> 12 NM / ZEE)',
                'jalur_desc' => 'Di luar batas legal < 10 GT',
                'citation' => $citation,
                'message' => "Kapal motor < 10 GT dilarang beroperasi melampaui Jalur II (> 12 NM) berdasar Permen KP No. 59/PERMEN-KP/2020 Pasal 3 & Lampiran. Wilayah operasi terbatas pada Jalur I (0–4 NM) dan Jalur II (4–12 NM).",
            ];
        }

        if ($gtClass === '> 30 GT' && $distanceNm <= 12.0) {
            $inshoreJalur = $distanceNm <= 4.0 ? 'Jalur I (0–4 NM)' : 'Jalur II (4–12 NM)';
            return [
                'status' => 'VIOLATION_RESTRICTED_INSHORE',
                'status_label' => 'PELANGGARAN ZONA PESISIR',
                'is_compliant' => false,
                'jalur_target' => $inshoreJalur,
                'jalur_label' => $inshoreJalur,
                'jalur_desc' => 'Terlarang untuk > 30 GT',
                'citation' => $citation,
                'message' => "Kapal motor > 30 GT dilarang beroperasi di Jalur I dan II (0–12 NM). Wajib beroperasi di Jalur III (> 12 NM / ZEE) atau Laut Lepas berdasar Permen KP No. 59/PERMEN-KP/2020.",
            ];
        }

        $allocatedJalur = $distanceNm <= 4.0 
            ? 'Jalur I (0–4 NM)' 
            : ($distanceNm <= 12.0 ? 'Jalur II (4–12 NM)' : 'Jalur III (> 12 NM / ZEE)');

        return [
            'status' => 'COMPLIANT_WITHIN_LEGAL_BOUNDS',
            'status_label' => 'PATUH',
            'is_compliant' => true,
            'jalur_target' => $allocatedJalur,
            'jalur_label' => $allocatedJalur,
            'jalur_desc' => 'Sesuai kelas tonase armada',
            'citation' => $citation,
            'message' => "Rute pelayaran dan spesifikasi armada memenuhi alokasi {$allocatedJalur} sesuai Permen KP No. 59/PERMEN-KP/2020.",
        ];
    }

    public function evaluateWaveSafety(float $waveHeightM): array
    {
        if ($waveHeightM > 2.5) {
            return [
                'advisory_code' => 'BAHAYA_GELOMBANG_EKSTREM',
                'status' => 'DANGER',
                'status_label' => 'BAHAYA GELOMBANG EKSTREM',
                'wave_height_m' => $waveHeightM,
                'is_safe_to_sail' => false,
                'badge_color' => 'red',
                'advice' => 'Tunda keberangkatan (> 2,5 m).',
                'message' => "Gelombang ekstrem (> 2.5 m). BMKG Maritim / Syahbandar melarang persetujuan berlayar (SPB) bagi seluruh armada pelagis.",
            ];
        }

        if ($waveHeightM > 1.5) {
            return [
                'advisory_code' => 'WASPADA_GELOMBANG_SEDANG',
                'status' => 'CAUTION',
                'status_label' => 'WASPADA',
                'wave_height_m' => $waveHeightM,
                'is_safe_to_sail' => true,
                'badge_color' => 'yellow',
                'advice' => 'Waspada armada < 10 GT (1,5–2,5 m).',
                'message' => "Gelombang laut sedang (1.5 – 2.5 m). Kapal < 10 GT wajib waspada, pastikan radio maritim dan life jacket aktif.",
            ];
        }

        return [
            'advisory_code' => 'AMAN_MELAUT',
            'status' => 'SAFE',
            'status_label' => 'AMAN',
            'wave_height_m' => $waveHeightM,
            'is_safe_to_sail' => true,
            'badge_color' => 'green',
            'advice' => 'Perairan tenang (< 1,5 m).',
            'message' => "Kondisi perairan tenang (< 1.5 m). Rekomendasi operasional aman untuk seluruh kelas armada.",
        ];
    }

    public function calculateMoonPhase(string|\DateTimeInterface $date): array
    {
        $dt = is_string($date) ? new \DateTime($date) : $date;
        $refNewMoon = new \DateTime('2000-01-06 18:14:00', new \DateTimeZone('UTC'));
        
        $diffSeconds = $dt->getTimestamp() - $refNewMoon->getTimestamp();
        $synodicMonth = 29.53058867 * 86400;
        $phaseAgeSeconds = fmod($diffSeconds, $synodicMonth);
        if ($phaseAgeSeconds < 0) {
            $phaseAgeSeconds += $synodicMonth;
        }
        $ageDays = round($phaseAgeSeconds / 86400, 1);

        if ($ageDays < 1.84 || $ageDays >= 27.69) {
            $phaseName = 'Bulan Baru (New Moon / Gelap)';
            $illuminationPct = 2;
            $advisory = 'Malam gelap gulita: Ikan pelagis terkonsentrasi di sekitar rumpon/atraktor cahaya; sangat optimal untuk pancing ulur dan purse seine malam.';
        } elseif ($ageDays < 5.53) {
            $phaseName = 'Bulan Sabit Awal (Waxing Crescent)';
            $illuminationPct = 25;
            $advisory = 'Cahaya bulan minimal: Efisiensi penangkapan tinggi di dekat rumpon laut dalam.';
        } elseif ($ageDays < 9.22) {
            $phaseName = 'Kuartal Pertama (First Quarter)';
            $illuminationPct = 50;
            $advisory = 'Fase transisi: Waktu operasi malam hari sebelum tengah malam cukup efektif.';
        } elseif ($ageDays < 12.92) {
            $phaseName = 'Bulan Cembung Awal (Waxing Gibbous)';
            $illuminationPct = 75;
            $advisory = 'Pencahayaan bulan meningkat: Ikan mulai menyebar ke lapisan permukaan.';
        } elseif ($ageDays < 16.61) {
            $phaseName = 'Bulan Purnama (Full Moon / Terang)';
            $illuminationPct = 98;
            $advisory = 'Malam terang bulan: Kawanan tuna menyebar luas di permukaan laut; efisiensi atraktor lampu berkurang drastis, prioritaskan pancing tonda siang hari.';
        } elseif ($ageDays < 20.30) {
            $phaseName = 'Bulan Cembung Akhir (Waning Gibbous)';
            $illuminationPct = 75;
            $advisory = 'Pencahayaan bulan mulai meredup setelah tengah malam.';
        } elseif ($ageDays < 23.99) {
            $phaseName = 'Kuartal Ketiga (Last Quarter)';
            $illuminationPct = 50;
            $advisory = 'Fase pasang perbani: Arus permukaan melambat, navigasi stabil.';
        } else {
            $phaseName = 'Bulan Sabit Akhir (Waning Crescent)';
            $illuminationPct = 25;
            $advisory = 'Malam gelap menjelang fajar: Agregasi mangsa tinggi di lapisan fotik.';
        }

        return [
            'date' => $dt->format('Y-m-d'),
            'age_days' => $ageDays,
            'phase_name' => $phaseName,
            'illumination_pct' => $illuminationPct,
            'fishing_advisory' => $advisory,
        ];
    }

    public function calculateCatchProjection(string $commodity, string $gtClass, float $hsiScore, ?string $date = null): array
    {
        $spec = self::GT_SPECS[$gtClass] ?? self::GT_SPECS['< 10 GT'];
        $commKey = str_replace([' ', '-'], '_', strtolower(trim($commodity)));
        $benchmarkPrice = self::COMMODITY_PRICES[$commKey] ?? 22000;

        // Seasonal factor based on month (July-October upwelling peak in WPPNRI 573)
        $month = $date ? (int)date('n', strtotime($date)) : 11;
        $seasonalFactor = ($month >= 7 && $month <= 10) ? 1.25 : 1.0;

        $boundedHsi = max(0.15, min(1.0, $hsiScore));
        $projectedKg = (int)round($spec['base_catch_kg'] * $boundedHsi * $seasonalFactor);
        $grossRevenueRp = (int)round($projectedKg * $benchmarkPrice);
        $hsiCategory = $boundedHsi >= 0.70 ? 'Tinggi' : ($boundedHsi >= 0.55 ? 'Sedang' : 'Rendah');

        return [
            'commodity' => self::commodityDisplayName($commodity),
            'benchmark_price_per_kg' => $benchmarkPrice,
            'price_per_kg_rp' => $benchmarkPrice,
            'base_capacity_kg' => $spec['base_catch_kg'],
            'hsi_score' => $hsiScore,
            'hsi_category' => $hsiCategory,
            'seasonal_factor' => $seasonalFactor,
            'projected_kg' => $projectedKg,
            'gross_revenue_rp' => $grossRevenueRp,
        ];
    }

    public static function temporalCalibrationIsReleased(array $gate, string $commodityKey): bool
    {
        if (($gate['status'] ?? null) === 'HOLD_FOR_SCIENTIFIC_REVIEW'
            || ($gate['web_integration_allowed'] ?? false) !== true) {
            return false;
        }

        foreach (($gate['species'] ?? []) as $species) {
            if (!is_array($species) || ($species['species'] ?? null) !== $commodityKey) {
                continue;
            }

            return in_array(($species['web_status'] ?? null), [
                'APPROVED_FOR_RELEASE',
                'CANDIDATE_HIGH_CONFIDENCE',
            ], true);
        }

        return false;
    }

    private function temporalCorrectionFactor(string $commodityKey): ?float
    {
        $gatePath = base_path('../outputs/tct_hybrid_2015_2025/reports/tct_scientific_gate.json');
        if (!is_file($gatePath)) {
            return null;
        }

        $gate = json_decode((string)file_get_contents($gatePath), true);
        if (!is_array($gate) || !self::temporalCalibrationIsReleased($gate, $commodityKey)) {
            return null;
        }

        $manifestPath = base_path('../outputs/tct_hybrid_2015_2025/reports/tct_hybrid_model_validation_2015_2025.json');
        if (!is_file($manifestPath)) {
            return null;
        }

        $manifest = json_decode((string)file_get_contents($manifestPath), true);
        $calibration = $manifest['temporal_calibration'][$commodityKey] ?? null;
        $rollingOriginFolds = (int)($calibration['validation_context']['rolling_origin_folds'] ?? 0);
        $correctionFactor = $calibration['correction_factor'] ?? null;

        if (
            is_array($calibration)
            && ($calibration['calibration_status'] ?? null) === 'ENABLED'
            && ($calibration['correction_enabled'] ?? false) === true
            && $rollingOriginFolds > 0
            && is_numeric($correctionFactor)
            && (float)$correctionFactor > 0
        ) {
            return (float)$correctionFactor;
        }

        return null;
    }

    private function resolveEstimateSource(string $requestedSource, ?float $temporalCorrectionFactor): string
    {
        return $requestedSource === 'TEMPORAL_CPUE_CALIBRATED' && $temporalCorrectionFactor !== null
            ? 'TEMPORAL_CPUE_CALIBRATED'
            : 'FORMULA_SCENARIO';
    }

    public function generateCompleteTripPlan(array $params): array
    {
        $portCode = $params['port_code'] ?? 'PPN_PRIGI';
        $port = self::PORTS[$portCode] ?? self::PORTS['PPN_PRIGI'];
        $gtClass = $params['gt_class'] ?? '< 10 GT';
        $spec = self::GT_SPECS[$gtClass] ?? self::GT_SPECS['< 10 GT'];
        $tripDays = $this->normalizeTripDays($params['trip_days'] ?? 1);
        $fuelType = self::fuelTypeForGtClass($gtClass);
        $subsidyEligibilityStatus = $fuelType === 'SUBSIDIZED'
            ? 'UNVERIFIED'
            : 'NOT_ELIGIBLE_BY_GT';
        $fuelPriceNote = $fuelType === 'SUBSIDIZED'
            ? 'Tarif subsidi hanya asumsi biaya; hak pembelian perlu surat rekomendasi dan dokumen kapal yang berlaku.'
            : 'Tarif non-subsidi Rp15.000/L adalah asumsi skema harga khusus 2026 untuk kapal >30 sampai 200 GT; kelas ini dapat mencakup kapal >200 GT. Konfirmasi syarat dan harga aktual.';
        $solarPrice = $fuelType === 'NON_SUBSIDIZED'
            ? self::NON_SUBSIDIZED_SOLAR_PRICE_PER_LITER
            : self::DEFAULT_SOLAR_PRICE_PER_LITER;
        $requestedReadiness = $params['vessel_readiness'] ?? null;
        $vesselReadiness = is_string($requestedReadiness) && array_key_exists($requestedReadiness, self::READINESS_FACTORS)
            ? $requestedReadiness
            : 'OPTIMAL';
        $readinessFactor = self::READINESS_FACTORS[$vesselReadiness];
        $cellId = isset($params['cell_id']) && $params['cell_id'] !== '' ? (string)$params['cell_id'] : null;
        $selectedCell = is_array($params['selected_cell'] ?? null) ? $params['selected_cell'] : null;
        $selectedCellLat = $selectedCell['lat'] ?? $selectedCell['center_lat'] ?? $selectedCell['lat_dec'] ?? null;
        $selectedCellLon = $selectedCell['lon'] ?? $selectedCell['center_lon'] ?? $selectedCell['lon_dec'] ?? null;
        $coordinatesFallback = $selectedCellLat === null && !array_key_exists('target_lat', $params);
        $targetLat = (float)($selectedCellLat ?? $params['target_lat'] ?? -8.5);
        $targetLon = (float)($selectedCellLon ?? $params['target_lon'] ?? 111.75);
        $departureDate = $params['departure_date'] ?? date('Y-m-d');
        $commodity = $params['target_commodity'] ?? 'Cakalang';
        $commodityKey = str_replace([' ', '-'], '_', strtolower($commodity));
        $hsiKeys = [
            'tuna_sirip_kuning' => ['hsi_tuna_sirip_kuning', 'hsi_madidihang'],
            'madidihang' => ['hsi_madidihang', 'hsi_tuna_sirip_kuning'],
            'cakalang' => ['hsi_cakalang'],
            'tongkol' => ['hsi_tongkol', 'hsi_tongkol_krai'],
            'tongkol_krai' => ['hsi_tongkol_krai', 'hsi_tongkol'],
        ][$commodityKey] ?? ['hsi_score'];
        $selectedHsi = null;
        foreach ($hsiKeys as $hsiKey) {
            if ($selectedCell !== null && array_key_exists($hsiKey, $selectedCell)) {
                $selectedHsi = $selectedCell[$hsiKey];
                break;
            }
        }
        $hsiFallback = $selectedHsi === null && !is_numeric($params['hsi_score'] ?? null);
        $hsiScore = (float)($selectedHsi ?? $params['hsi_score'] ?? 0.75);
        $selectedWave = $selectedCell['wave_height_m'] ?? null;
        $waveFallback = $selectedWave === null && !array_key_exists('wave_height_m', $params);
        $waveHeightM = (float)($selectedWave ?? $params['wave_height_m'] ?? 1.3);
        $requestedEstimateSource = in_array($params['estimate_source'] ?? null, [
            'FORMULA_SCENARIO',
            'TEMPORAL_CPUE_CALIBRATED',
        ], true) ? $params['estimate_source'] : 'FORMULA_SCENARIO';
        $temporalCorrectionFactor = $this->temporalCorrectionFactor($commodityKey);
        $estimateSource = $this->resolveEstimateSource($requestedEstimateSource, $temporalCorrectionFactor);
        $spatialEvidenceLevel = (string)($selectedCell['spatial_evidence_level']
            ?? $params['spatial_evidence_level']
            ?? 'PROXY_HSI');
        $dataSourceMode = $selectedCell !== null
            ? 'SELECTED_SPATIAL_CELL'
            : ($coordinatesFallback || $hsiFallback || $waveFallback ? 'FALLBACK_DEFAULT' : 'PARAMETERS');
        $targetSource = $selectedCell !== null
            ? 'SELECTED_SPATIAL_CELL'
            : ($dataSourceMode === 'FALLBACK_DEFAULT' ? 'FALLBACK_DEFAULT' : 'PARAMETERS');

        $nav = $this->calculateDistanceAndBearing($portCode, $targetLat, $targetLon);
        $fuel = $this->calculateFuelEconomics($nav['distance_nm'], $gtClass, $solarPrice);
        $fuel['fuel_type'] = $fuelType;
        $fuel['fuel_type_source'] = 'GT_CLASS_SCENARIO';
        $fuel['subsidy_eligibility_status'] = $subsidyEligibilityStatus;
        $fuel['price_assumption_note'] = $fuelPriceNote;
        $fuel['price_basis'] = 'FORMULA_SCENARIO';
        $duration = $this->calculateTripDuration($fuel['travel_time_hours'], $tripDays);
        $operations = $this->calculateOperationalEconomics($fuel, $gtClass, $tripDays, $solarPrice);
        $fuel['total_trip_fuel_liters'] = round($fuel['total_fuel_liters'] + $operations['total_ops_fuel_liters'], 1);
        $fuel['total_trip_fuel_cost_rp'] = $fuel['total_fuel_cost_rp'] + $operations['operational_fuel_cost_rp'];
        $compliance = $this->validatePermenKpCompliance($gtClass, $nav['distance_nm']);
        $waveSafety = $this->evaluateWaveSafety($waveHeightM);
        $moon = $this->calculateMoonPhase($departureDate);
        $catch = $this->calculateCatchProjection($commodity, $gtClass, $hsiScore, $departureDate);
        $catch = $this->applyDurationToCatchProjection($catch, $duration, $tripDays);
        if ($estimateSource === 'TEMPORAL_CPUE_CALIBRATED' && $temporalCorrectionFactor !== null) {
            $catch['projected_kg'] = (int)round($catch['projected_kg'] * $temporalCorrectionFactor);
            $catch['gross_revenue_rp'] = (int)round(
                $catch['projected_kg'] * $catch['benchmark_price_per_kg']
            );
        }
        $readinessAdjustedKg = (int)round($catch['projected_kg'] * $readinessFactor);
        $isHoldCapped = $readinessAdjustedKg > $spec['max_hold_capacity_kg'];
        $catch['projected_kg'] = min($readinessAdjustedKg, $spec['max_hold_capacity_kg']);
        $catch['gross_revenue_rp'] = (int)round($catch['projected_kg'] * $catch['benchmark_price_per_kg']);
        $catch['readiness_factor'] = $readinessFactor;
        $catch['max_hold_capacity_kg'] = $spec['max_hold_capacity_kg'];
        $catch['is_hold_capped'] = $isHoldCapped;
        $catch['estimate_source'] = $estimateSource;
        $catch['spatial_evidence_level'] = $spatialEvidenceLevel;

        $enduranceExceeded = $tripDays > $spec['max_endurance_days'];
        $readinessNotes = [];
        if (!$waveSafety['is_safe_to_sail']) {
            $readinessNotes[] = 'Kondisi gelombang tidak aman untuk berlayar.';
        }
        if (!$compliance['is_compliant']) {
            $readinessNotes[] = 'Rencana tidak memenuhi pemeriksaan jalur penangkapan dan kelas kapal.';
        }
        if ($enduranceExceeded) {
            $readinessNotes[] = 'Lama melaut melebihi daya tahan kapal.';
        }
        if ($vesselReadiness === 'TERBATAS' && $tripDays > 1) {
            $readinessNotes[] = 'Kapal dengan kesiapan terbatas tidak disarankan melaut lebih dari satu hari.';
        }
        if ($vesselReadiness !== 'OPTIMAL') {
            $readinessNotes[] = 'Kesiapan kapal belum optimal.';
        }
        if ($duration['duration_feasibility_status'] !== 'LAYAK') {
            $readinessNotes[] = $duration['duration_warning'];
        }
        if ($isHoldCapped) {
            $readinessNotes[] = 'Proyeksi tangkapan dibatasi kapasitas palka.';
        }
        if (($params['wave_source'] ?? null) === 'UNVERIFIED_INPUT') {
            $readinessNotes[] = 'Prakiraan gelombang BMKG untuk tanggal keberangkatan tidak tersedia; verifikasi cuaca sebelum berangkat.';
        }
        $notRecommended = !$waveSafety['is_safe_to_sail']
            || !$compliance['is_compliant']
            || $enduranceExceeded
            || ($vesselReadiness === 'TERBATAS' && $tripDays > 1);
        $readinessStatus = $notRecommended
            ? 'TIDAK_DISARANKAN'
            : ($readinessNotes !== [] ? 'SIAP_DENGAN_CATATAN' : 'SIAP_MELAUT');

        $netProfitRp = $catch['gross_revenue_rp'] - $operations['total_operational_cost_rp'];

        // Generate official Reference Code
        $refSuffix = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
        $referenceNo = sprintf(
            'DOC-%s-%s-%s',
            $port['prefix'],
            date('Ym', strtotime($departureDate)),
            $refSuffix
        );

        // Format DD°MM'SS"
        $latVal = abs($targetLat);
        $latDeg = floor($latVal);
        $latMin = floor(($latVal - $latDeg) * 60);
        $latSec = round((($latVal - $latDeg) * 60 - $latMin) * 60);
        $latDms = sprintf("%02d°%02d'%02d\"S", $latDeg, $latMin, $latSec);

        $lonVal = abs($targetLon);
        $lonDeg = floor($lonVal);
        $lonMin = floor(($lonVal - $lonDeg) * 60);
        $lonSec = round((($lonVal - $lonDeg) * 60 - $lonMin) * 60);
        $lonDms = sprintf("%03d°%02d'%02d\"E", $lonDeg, $lonMin, $lonSec);

        return [
            'reference_no' => $referenceNo,
            'cell_id' => $cellId,
            'estimate_source' => $estimateSource,
            'spatial_evidence_level' => $spatialEvidenceLevel,
            'data_source_mode' => $dataSourceMode,
            'wave_source' => $params['wave_source'] ?? 'UNVERIFIED_INPUT',
            'weather_forecast' => $params['weather_forecast'] ?? null,
            'target_source' => $targetSource,
            'fallbacks' => [
                'coordinates' => $coordinatesFallback,
                'hsi' => $hsiFallback,
                'wave' => $waveFallback,
            ],
            'port' => $port,
            'vessel_name' => $params['vessel_name'] ?? 'KM Nelayan Bahari',
            'skipper_name' => $params['skipper_name'] ?? 'Nakhoda Sukirno',
            'gt_class' => $gtClass,
            'trip_days' => $tripDays,
            'fuel_type' => $fuelType,
            'fuel_type_source' => 'GT_CLASS_SCENARIO',
            'subsidy_eligibility_status' => $subsidyEligibilityStatus,
            'fuel_price_assumption_note' => $fuelPriceNote,
            'solar_price_per_liter' => $solarPrice,
            'vessel_readiness' => $vesselReadiness,
            'readiness_factor' => $readinessFactor,
            'readiness_status' => $readinessStatus,
            'readiness_notes' => $readinessNotes,
            'max_hold_capacity_kg' => $spec['max_hold_capacity_kg'],
            'max_endurance_days' => $spec['max_endurance_days'],
            'endurance_exceeded' => $enduranceExceeded,
            'is_hold_capped' => $isHoldCapped,
            'gear_code' => $spec['gear_code'],
            'gear_name' => $spec['gear_name'],
            'departure_date' => $departureDate,
            'target_commodity' => self::commodityDisplayName($commodity),
            'target_coordinates' => [
                'lat_dec' => $targetLat,
                'lon_dec' => $targetLon,
                'dms' => "{$latDms}, {$lonDms}",
                'lat_dms' => $latDms,
                'lon_dms' => $lonDms,
            ],
            'navigation' => $nav,
            'fuel' => $fuel,
            'effective_fishing_hours' => $duration['effective_fishing_hours'],
            'duration_feasibility_status' => $duration['duration_feasibility_status'],
            'duration_warning' => $duration['duration_warning'],
            'daily_ops_fuel_liters' => $operations['daily_ops_fuel_liters'],
            'daily_provisions_cost_rp' => $operations['daily_provisions_cost_rp'],
            'provisions_cost_rp' => $operations['provisions_cost_rp'],
            'total_operational_cost_rp' => $operations['total_operational_cost_rp'],
            'permen_kp' => $compliance,
            'wave_safety' => $waveSafety,
            'moon_phase' => $moon,
            'catch_projection' => $catch,
            'economics' => [
                'fuel_type' => $fuelType,
                'solar_price_per_liter' => $solarPrice,
                'gross_revenue_rp' => $catch['gross_revenue_rp'],
                'total_fuel_cost_rp' => $fuel['total_fuel_cost_rp'],
                'daily_ops_fuel_liters' => $operations['daily_ops_fuel_liters'],
                'operational_fuel_cost_rp' => $operations['operational_fuel_cost_rp'],
                'daily_provisions_cost_rp' => $operations['daily_provisions_cost_rp'],
                'provisions_cost_rp' => $operations['provisions_cost_rp'],
                'total_operational_cost_rp' => $operations['total_operational_cost_rp'],
                'net_profit_rp' => $netProfitRp,
                'roi_pct' => $operations['total_operational_cost_rp'] > 0
                    ? round(($netProfitRp / $operations['total_operational_cost_rp']) * 100, 1)
                    : 100.0,
            ],
        ];
    }

    /**
     * Rank candidate ocean cells by net profit, compliance, wave safety, and suitability.
     *
     * @param  iterable<int, array<string, mixed>|object>  $candidateCells
     * @return array<int, array<string, mixed>>
     */
    public function recommendTopCells(array $params, iterable $candidateCells, int $limit = 5): array
    {
        $commodity = (string) ($params['target_commodity'] ?? 'Cakalang');
        $commodityKey = str_replace([' ', '-'], '_', strtolower($commodity));
        $hsiKeys = [
            'tuna_sirip_kuning' => ['hsi_tuna_sirip_kuning', 'hsi_madidihang'],
            'madidihang' => ['hsi_madidihang', 'hsi_tuna_sirip_kuning'],
            'cakalang' => ['hsi_cakalang'],
            'tongkol' => ['hsi_tongkol', 'hsi_tongkol_krai'],
            'tongkol_krai' => ['hsi_tongkol_krai', 'hsi_tongkol'],
        ][$commodityKey] ?? ['hsi_cakalang'];

        $ranked = [];
        foreach ($candidateCells as $rawCell) {
            $cell = is_array($rawCell) ? $rawCell : (array) $rawCell;
            $depth = (float) ($cell['depth_m'] ?? 0);
            if ($depth <= 0.0) {
                continue;
            }

            $lat = $cell['center_lat'] ?? $cell['lat'] ?? null;
            $lon = $cell['center_lon'] ?? $cell['lon'] ?? null;
            if ($lat === null || $lon === null) {
                continue;
            }

            $hsiScore = null;
            foreach ($hsiKeys as $key) {
                if (isset($cell[$key]) && is_numeric($cell[$key])) {
                    $hsiScore = (float) $cell[$key];
                    break;
                }
            }
            $hsiScore = $hsiScore ?? (float) ($cell['hsi_score'] ?? 0.0);
            $waveHeight = (float) ($cell['wave_height_m'] ?? 1.3);
            $bycatch = strtoupper((string) ($cell['bycatch_traffic_light'] ?? 'GREEN'));

            $plan = $this->generateCompleteTripPlan(array_merge($params, [
                'cell_id' => $cell['cell_id'] ?? null,
                'selected_cell' => [
                    'cell_id' => $cell['cell_id'] ?? null,
                    'center_lat' => (float) $lat,
                    'center_lon' => (float) $lon,
                    'wave_height_m' => $waveHeight,
                    'hsi_score' => $hsiScore,
                    $hsiKeys[0] => $hsiScore,
                ],
                'hsi_score' => $hsiScore,
                'wave_height_m' => $waveHeight,
            ]));

            if (!($plan['wave_safety']['is_safe_to_sail'] ?? false)) {
                continue;
            }
            if (!($plan['permen_kp']['is_compliant'] ?? false)) {
                continue;
            }
            if ($bycatch === 'RED') {
                continue;
            }

            $ranked[] = [
                'cell_id' => (string) ($cell['cell_id'] ?? ''),
                'lat' => round((float) $lat, 4),
                'lon' => round((float) $lon, 4),
                'dms' => $plan['target_coordinates']['dms'],
                'depth_m' => round($depth, 1),
                'hsi_score' => round($hsiScore, 3),
                'wave_height_m' => round($waveHeight, 2),
                'bycatch_traffic_light' => $bycatch,
                'distance_nm' => $plan['navigation']['distance_nm'],
                'bearing_degrees' => $plan['navigation']['bearing_degrees'],
                'compass_direction' => $plan['navigation']['compass_direction'],
                'projected_kg' => $plan['catch_projection']['projected_kg'],
                'total_operational_cost_rp' => $plan['total_operational_cost_rp'],
                'net_profit_rp' => $plan['economics']['net_profit_rp'],
                'roi_pct' => $plan['economics']['roi_pct'],
                'readiness_status' => $plan['readiness_status'],
                'duration_feasibility_status' => $plan['duration_feasibility_status'],
                'estimate_source' => $plan['estimate_source'],
                'spatial_evidence_level' => $plan['spatial_evidence_level'],
            ];
        }

        usort($ranked, static function (array $a, array $b): int {
            $feasibleA = $a['duration_feasibility_status'] === 'LAYAK' ? 1 : 0;
            $feasibleB = $b['duration_feasibility_status'] === 'LAYAK' ? 1 : 0;
            if ($feasibleA !== $feasibleB) {
                return $feasibleB <=> $feasibleA;
            }
            if ($a['net_profit_rp'] !== $b['net_profit_rp']) {
                return $b['net_profit_rp'] <=> $a['net_profit_rp'];
            }
            if ($a['hsi_score'] !== $b['hsi_score']) {
                return $b['hsi_score'] <=> $a['hsi_score'];
            }

            return $a['distance_nm'] <=> $b['distance_nm'];
        });

        return array_values(array_slice($ranked, 0, max(1, $limit)));
    }

    /**
     * Build standard GPX 1.1 XML waypoints for marine GPS plotters.
     *
     * @param  array<int, array<string, mixed>>  $recommendations
     */
    public function exportWaypointsGpx(array $recommendations, array $meta = []): string
    {
        $title = htmlspecialchars((string) ($meta['title'] ?? 'SEACAST Top-5 Waypoints WPPNRI 573'), ENT_XML1);
        $date = htmlspecialchars((string) ($meta['date'] ?? date('Y-m-d')), ENT_XML1);
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<gpx version="1.1" creator="SEACAST WPPNRI 573" xmlns="http://www.topografix.com/GPX/1/1">',
            '  <metadata>',
            "    <name>{$title}</name>",
            "    <desc>Generated {$date} | Mode: FORMULA_SCENARIO | Evidence: PROXY_HSI</desc>",
            '  </metadata>',
        ];

        foreach ($recommendations as $idx => $rec) {
            $rank = $idx + 1;
            $lat = number_format((float) ($rec['lat'] ?? 0), 5, '.', '');
            $lon = number_format((float) ($rec['lon'] ?? 0), 5, '.', '');
            $name = htmlspecialchars(sprintf('#%d %s', $rank, (string) ($rec['cell_id'] ?? "WPT-{$rank}")), ENT_XML1);
            $desc = htmlspecialchars(sprintf(
                'HSI %.3f | Dist %.1f NM %s | Wave %.1fm | Est %d kg | Margin Rp %s (FORMULA_SCENARIO / PROXY_HSI)',
                (float) ($rec['hsi_score'] ?? 0),
                (float) ($rec['distance_nm'] ?? 0),
                (string) ($rec['compass_direction'] ?? ''),
                (float) ($rec['wave_height_m'] ?? 0),
                (int) ($rec['projected_kg'] ?? 0),
                number_format((int) ($rec['net_profit_rp'] ?? 0), 0, ',', '.')
            ), ENT_XML1);

            $lines[] = "  <wpt lat=\"{$lat}\" lon=\"{$lon}\">";
            $lines[] = "    <name>{$name}</name>";
            $lines[] = "    <desc>{$desc}</desc>";
            $lines[] = '    <sym>Fishing</sym>';
            $lines[] = '  </wpt>';
        }

        $lines[] = '</gpx>';

        return implode("\n", $lines) . "\n";
    }
}

