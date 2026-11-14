<?php

namespace App\Domain\Trip\Services;

class TripPlanningService
{
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
            'max_legal_range_nm' => 12.0,
            'base_catch_kg' => 180,
            'gear_code' => 'LTL', // Pancing tonda
            'gear_name' => 'Pancing Tonda (Troll Line)',
        ],
        '10 - 20 GT' => [
            'speed_knots' => 9.0,
            'fuel_liters_per_nm' => 2.8,
            'max_legal_range_nm' => 30.0,
            'base_catch_kg' => 450,
            'gear_code' => 'LHP-PUT', // Pancing ulur tuna
            'gear_name' => 'Pancing Ulur Tuna (Handline)',
        ],
        '20 - 30 GT' => [
            'speed_knots' => 11.0,
            'fuel_liters_per_nm' => 4.5,
            'max_legal_range_nm' => 60.0,
            'base_catch_kg' => 1200,
            'gear_code' => 'PS1-B', // Purse seine pelagis besar
            'gear_name' => 'Pukat Cincin Pelagis Besar (Purse Seine)',
        ],
        '> 30 GT' => [
            'speed_knots' => 12.5,
            'fuel_liters_per_nm' => 7.0,
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
        $earthRadiusKm = 6371.0;
        $dLat = deg2rad($targetLat - $portLat);
        $dLon = deg2rad($targetLon - $portLon);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($portLat)) * cos(deg2rad($targetLat)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distKm = $earthRadiusKm * $c;
        $distNm = $distKm / 1.852;

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
            'subsidized_price_per_liter' => $solarPricePerLiter,
            'total_fuel_cost_rp' => $totalFuelCostRp,
        ];
    }

    public function validatePermenKpCompliance(string $gtClass, float $distanceNm): array
    {
        $citation = "Permen KP No. 59/PERMEN-KP/2020 tentang Jalur Penangkapan Ikan dan Alat Penangkapan Ikan di WPPNRI dan Laut Lepas";

        if ($gtClass === '< 10 GT' && $distanceNm > 12.0) {
            return [
                'status' => 'WARNING_OUT_OF_LEGAL_RANGE',
                'is_compliant' => false,
                'jalur_target' => 'Jalur III (> 12 NM / ZEE)',
                'citation' => $citation,
                'message' => "Kapal motor < 10 GT dilarang beroperasi melampaui Jalur II (> 12 NM) berdasar Permen KP No. 59/PERMEN-KP/2020 Pasal 3 & Lampiran. Wilayah operasi terbatas pada Jalur I (0–4 NM) dan Jalur II (4–12 NM).",
            ];
        }

        if ($gtClass === '> 30 GT' && $distanceNm <= 12.0) {
            return [
                'status' => 'VIOLATION_RESTRICTED_INSHORE',
                'is_compliant' => false,
                'jalur_target' => $distanceNm <= 4.0 ? 'Jalur I (0–4 NM)' : 'Jalur II (4–12 NM)',
                'citation' => $citation,
                'message' => "Kapal motor > 30 GT dilarang beroperasi di Jalur I dan II (0–12 NM). Wajib beroperasi di Jalur III (> 12 NM / ZEE) atau Laut Lepas berdasar Permen KP No. 59/PERMEN-KP/2020.",
            ];
        }

        $allocatedJalur = $distanceNm <= 4.0 
            ? 'Jalur I (0–4 NM)' 
            : ($distanceNm <= 12.0 ? 'Jalur II (4–12 NM)' : 'Jalur III (> 12 NM / ZEE)');

        return [
            'status' => 'COMPLIANT_WITHIN_LEGAL_BOUNDS',
            'is_compliant' => true,
            'jalur_target' => $allocatedJalur,
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
                'wave_height_m' => $waveHeightM,
                'is_safe_to_sail' => false,
                'badge_color' => 'red',
                'message' => "Gelombang ekstrem (> 2.5 m). BMKG Maritim / Syahbandar melarang persetujuan berlayar (SPB) bagi seluruh armada pelagis.",
            ];
        }

        if ($waveHeightM > 1.5) {
            return [
                'advisory_code' => 'WASPADA_GELOMBANG_SEDANG',
                'status' => 'CAUTION',
                'wave_height_m' => $waveHeightM,
                'is_safe_to_sail' => true,
                'badge_color' => 'yellow',
                'message' => "Gelombang laut sedang (1.5 – 2.5 m). Kapal < 10 GT wajib waspada, pastikan radio maritim dan life jacket aktif.",
            ];
        }

        return [
            'advisory_code' => 'AMAN_MELAUT',
            'status' => 'SAFE',
            'wave_height_m' => $waveHeightM,
            'is_safe_to_sail' => true,
            'badge_color' => 'green',
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
        $commKey = strtolower($commodity);
        $benchmarkPrice = self::COMMODITY_PRICES[$commKey] ?? 22000;

        // Seasonal factor based on month (July-October upwelling peak in WPPNRI 573)
        $month = $date ? (int)date('n', strtotime($date)) : 11;
        $seasonalFactor = ($month >= 7 && $month <= 10) ? 1.25 : 1.0;

        $boundedHsi = max(0.15, min(1.0, $hsiScore));
        $projectedKg = (int)round($spec['base_catch_kg'] * $boundedHsi * $seasonalFactor);
        $grossRevenueRp = (int)round($projectedKg * $benchmarkPrice);

        return [
            'commodity' => ucfirst($commodity),
            'benchmark_price_per_kg' => $benchmarkPrice,
            'base_capacity_kg' => $spec['base_catch_kg'],
            'hsi_score' => $hsiScore,
            'seasonal_factor' => $seasonalFactor,
            'projected_kg' => $projectedKg,
            'gross_revenue_rp' => $grossRevenueRp,
        ];
    }

    private function temporalCorrectionFactor(string $commodityKey): ?float
    {
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
        $hsiFallback = $selectedHsi === null && !array_key_exists('hsi_score', $params);
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
        $fuel = $this->calculateFuelEconomics($nav['distance_nm'], $gtClass);
        $compliance = $this->validatePermenKpCompliance($gtClass, $nav['distance_nm']);
        $waveSafety = $this->evaluateWaveSafety($waveHeightM);
        $moon = $this->calculateMoonPhase($departureDate);
        $catch = $this->calculateCatchProjection($commodity, $gtClass, $hsiScore, $departureDate);
        if ($estimateSource === 'TEMPORAL_CPUE_CALIBRATED' && $temporalCorrectionFactor !== null) {
            $catch['projected_kg'] = (int)round($catch['projected_kg'] * $temporalCorrectionFactor);
            $catch['gross_revenue_rp'] = (int)round(
                $catch['projected_kg'] * $catch['benchmark_price_per_kg']
            );
        }
        $catch['estimate_source'] = $estimateSource;
        $catch['spatial_evidence_level'] = $spatialEvidenceLevel;

        $netProfitRp = $catch['gross_revenue_rp'] - $fuel['total_fuel_cost_rp'];

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
            'gear_code' => $spec['gear_code'],
            'gear_name' => $spec['gear_name'],
            'departure_date' => $departureDate,
            'target_commodity' => ucfirst($commodity),
            'target_coordinates' => [
                'lat_dec' => $targetLat,
                'lon_dec' => $targetLon,
                'dms' => "{$latDms}, {$lonDms}",
                'lat_dms' => $latDms,
                'lon_dms' => $lonDms,
            ],
            'navigation' => $nav,
            'fuel' => $fuel,
            'permen_kp' => $compliance,
            'wave_safety' => $waveSafety,
            'moon_phase' => $moon,
            'catch_projection' => $catch,
            'economics' => [
                'gross_revenue_rp' => $catch['gross_revenue_rp'],
                'total_fuel_cost_rp' => $fuel['total_fuel_cost_rp'],
                'net_profit_rp' => $netProfitRp,
                'roi_pct' => $fuel['total_fuel_cost_rp'] > 0 
                    ? round(($netProfitRp / $fuel['total_fuel_cost_rp']) * 100, 1) 
                    : 100.0,
            ],
        ];
    }
}

