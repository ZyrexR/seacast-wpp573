<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domain\Trip\Services\TripPlanningService;

class TripPlanningServiceTest extends TestCase
{
    private TripPlanningService $planner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->planner = new TripPlanningService();
    }

    public function test_distance_and_bearing_calculation_from_prigi_port(): void
    {
        // PPN Prigi Entrance (-8.2833, 111.7275) to a point 20 NM due south (-8.6166, 111.7275)
        $result = $this->planner->calculateDistanceAndBearing(
            portCode: 'PPN_PRIGI',
            targetLat: -8.6166,
            targetLon: 111.7275
        );

        $this->assertEquals(20.0, round($result['distance_nm'], 1));
        $this->assertGreaterThan(30.0, $result['distance_km']);
        $this->assertEquals('S', $result['compass_direction']);
    }

    public function test_distance_contract_uses_wgs84_haversine_and_international_nautical_mile(): void
    {
        $this->assertSame(6371.0088, TripPlanningService::EARTH_RADIUS_KM);
        $this->assertSame(1.852, TripPlanningService::KM_PER_NAUTICAL_MILE);
        $this->assertSame(3440.0695, TripPlanningService::EARTH_RADIUS_NM);
    }

    public function test_distance_and_bearing_calculation_from_cilacap_port(): void
    {
        // PPS Cilacap (-7.7472, 109.0069) to a point approximately 20 NM due south.
        $result = $this->planner->calculateDistanceAndBearing(
            portCode: 'PPS_CILACAP',
            targetLat: -8.08053,
            targetLon: 109.0069
        );

        $this->assertSame('PPS_CILACAP', $result['port_code']);
        $this->assertSame(-7.7472, $result['port_lat']);
        $this->assertSame(109.0069, $result['port_lon']);
        $this->assertEquals(20.0, round($result['distance_nm'], 1));
        $this->assertEquals('S', $result['compass_direction']);
    }

    public function test_permen_kp_jalur_boundaries_are_consistent_for_both_hubs(): void
    {
        foreach (['PPN_PRIGI', 'PPS_CILACAP'] as $portCode) {
            $this->assertSame($portCode, $this->planner->calculateDistanceAndBearing(
                portCode: $portCode,
                targetLat: TripPlanningService::PORTS[$portCode]['lat'],
                targetLon: TripPlanningService::PORTS[$portCode]['lon']
            )['port_code']);
        }

        $this->assertStringContainsString('Jalur I', $this->planner->validatePermenKpCompliance('10 - 20 GT', 4.0)['jalur_label']);
        $this->assertStringContainsString('Jalur II', $this->planner->validatePermenKpCompliance('10 - 20 GT', 4.01)['jalur_label']);
        $this->assertStringContainsString('Jalur II', $this->planner->validatePermenKpCompliance('10 - 20 GT', 12.0)['jalur_label']);
        $this->assertStringContainsString('Jalur III', $this->planner->validatePermenKpCompliance('10 - 20 GT', 12.01)['jalur_label']);
    }

    public function test_fuel_calculation_includes_mandatory_15_percent_safety_reserve(): void
    {
        // 10 NM one-way = 20 NM round trip.
        // Vessel < 10 GT uses 1.5 L/NM -> Base fuel = 30 L.
        // 15% reserve = 4.5 L -> Total fuel = 34.5 L.
        // At Rp 6.800 / L -> Total Rp 234.600.
        $fuel = $this->planner->calculateFuelEconomics(
            distanceNm: 10.0,
            gtClass: '< 10 GT'
        );

        $this->assertEquals(20.0, $fuel['round_trip_nm']);
        $this->assertEquals(30.0, $fuel['base_fuel_liters']);
        $this->assertEquals(4.5, $fuel['safety_reserve_liters']);
        $this->assertEquals(34.5, $fuel['total_fuel_liters']);
        $this->assertEquals(234600, $fuel['total_fuel_cost_rp']);
    }

    public function test_solar_tariff_is_derived_from_gt_class_even_if_request_asks_for_another_tariff(): void
    {
        $params = [
            'port_code' => 'PPN_PRIGI', 'gt_class' => '< 10 GT',
            'target_commodity' => 'Cakalang', 'target_lat' => -8.45,
            'target_lon' => 111.75, 'departure_date' => '2026-11-20',
            'wave_height_m' => 1.0, 'trip_days' => 2,
        ];
        foreach (['< 10 GT', '10 - 20 GT', '20 - 30 GT'] as $gtClass) {
            $plan = $this->planner->generateCompleteTripPlan(array_merge($params, [
                'gt_class' => $gtClass, 'fuel_type' => 'NON_SUBSIDIZED',
            ]));
            $this->assertSame('SUBSIDIZED', $plan['fuel_type']);
            $this->assertSame(6800.0, $plan['solar_price_per_liter']);
            $this->assertSame('GT_CLASS_SCENARIO', $plan['fuel_type_source']);
            $this->assertSame('UNVERIFIED', $plan['subsidy_eligibility_status']);
        }

        $large = $this->planner->generateCompleteTripPlan(array_merge($params, [
            'gt_class' => '> 30 GT', 'fuel_type' => 'SUBSIDIZED',
        ]));
        $this->assertSame('NON_SUBSIDIZED', $large['fuel_type']);
        $this->assertSame(15000.0, $large['solar_price_per_liter']);
        $this->assertSame('GT_CLASS_SCENARIO', $large['fuel_type_source']);
        $this->assertSame('NOT_ELIGIBLE_BY_GT', $large['subsidy_eligibility_status']);
        $this->assertSame((int)round($large['fuel']['total_fuel_liters'] * 15000), $large['fuel']['total_fuel_cost_rp']);
        $this->assertSame((int)round($large['daily_ops_fuel_liters'] * $large['trip_days'] * 15000), $large['economics']['operational_fuel_cost_rp']);
        $this->assertSame(
            round($large['fuel']['total_fuel_liters'] + $large['daily_ops_fuel_liters'] * $large['trip_days'], 1),
            $large['fuel']['total_trip_fuel_liters']
        );
        $this->assertSame(
            $large['fuel']['total_fuel_cost_rp'] + $large['economics']['operational_fuel_cost_rp'] + $large['economics']['provisions_cost_rp'],
            $large['economics']['total_operational_cost_rp']
        );
        $this->assertSame('FORMULA_SCENARIO', $large['estimate_source']);
    }

    public function test_missing_species_score_uses_labeled_fallback_and_canonical_commodity_name(): void
    {
        $plan = $this->planner->generateCompleteTripPlan([
            'port_code' => 'PPN_PRIGI', 'gt_class' => '10 - 20 GT',
            'target_commodity' => 'tuna_sirip_kuning', 'target_lat' => -8.45,
            'target_lon' => 111.75, 'departure_date' => '2026-11-20',
            'hsi_score' => null, 'wave_height_m' => 1.3,
        ]);
        $this->assertTrue($plan['fallbacks']['hsi']);
        $this->assertSame('Tuna Sirip Kuning', $plan['target_commodity']);
        $this->assertSame('Tuna Sirip Kuning', $plan['catch_projection']['commodity']);
    }

    public function test_permen_kp_59_2020_warns_small_vessel_beyond_12_nm(): void
    {
        // Review Focus 1: Vessel < 10 GT attempting to sail beyond 12 NM
        $compliance = $this->planner->validatePermenKpCompliance(
            gtClass: '< 10 GT',
            distanceNm: 18.5
        );

        $this->assertEquals('WARNING_OUT_OF_LEGAL_RANGE', $compliance['status']);
        $this->assertFalse($compliance['is_compliant']);
        $this->assertStringContainsString('Permen KP No. 59/PERMEN-KP/2020', $compliance['citation']);
        $this->assertStringContainsString('12 NM', $compliance['message']);
    }

    public function test_permen_kp_59_2020_restricts_large_vessel_within_12_nm(): void
    {
        // Review Focus 2: Vessel > 30 GT attempting to fish within Jalur I/II (<= 12 NM)
        $compliance = $this->planner->validatePermenKpCompliance(
            gtClass: '> 30 GT',
            distanceNm: 8.0
        );

        $this->assertEquals('VIOLATION_RESTRICTED_INSHORE', $compliance['status']);
        $this->assertFalse($compliance['is_compliant']);
        $this->assertStringContainsString('Jalur III', $compliance['message']);
    }

    public function test_extreme_wave_safety_threshold(): void
    {
        // Review Focus 4: Extreme wave height (> 2.5 m)
        $safety = $this->planner->evaluateWaveSafety(waveHeightM: 3.2);

        $this->assertEquals('BAHAYA_GELOMBANG_EKSTREM', $safety['advisory_code']);
        $this->assertEquals('DANGER', $safety['status']);
        $this->assertFalse($safety['is_safe_to_sail']);
    }

    public function test_moon_phase_and_catch_projection(): void
    {
        // Test future date fallback and lunar calculation (Review Focus 3)
        $plan = $this->planner->generateCompleteTripPlan([
            'port_code' => 'PPN_PRIGI',
            'vessel_name' => 'KM Makmur Bahari',
            'skipper_name' => 'Nakhoda Sukirno',
            'gt_class' => '10 - 20 GT',
            'target_commodity' => 'Cakalang',
            'target_lat' => -8.75,
            'target_lon' => 111.75,
            'departure_date' => '2026-11-20',
            'hsi_score' => 0.82,
            'wave_height_m' => 1.4,
        ]);

        $this->assertArrayHasKey('reference_no', $plan);
        $this->assertArrayHasKey('moon_phase', $plan);
        $this->assertArrayHasKey('fuel', $plan);
        $this->assertArrayHasKey('catch_projection', $plan);
        $this->assertGreaterThan(0, $plan['catch_projection']['projected_kg']);
        $this->assertGreaterThan(0, $plan['catch_projection']['gross_revenue_rp']);
        $this->assertTrue($plan['wave_safety']['is_safe_to_sail']);
    }

    public function test_trip_days_defaults_to_one_without_changing_formula_source(): void
    {
        $plan = $this->planner->generateCompleteTripPlan([
            'port_code' => 'PPN_PRIGI',
            'gt_class' => '10 - 20 GT',
            'target_commodity' => 'Cakalang',
            'target_lat' => -8.75,
            'target_lon' => 111.75,
            'departure_date' => '2026-11-20',
        ]);

        $this->assertSame(1, $plan['trip_days']);
        $this->assertArrayHasKey('effective_fishing_hours', $plan);
        $this->assertArrayHasKey('duration_feasibility_status', $plan);
        $this->assertArrayHasKey('duration_warning', $plan);
        $this->assertSame('FORMULA_SCENARIO', $plan['estimate_source']);
    }

    public function test_three_trip_days_change_duration_operational_cost_and_net_profit(): void
    {
        $params = [
            'port_code' => 'PPN_PRIGI',
            'gt_class' => '10 - 20 GT',
            'target_commodity' => 'Cakalang',
            'target_lat' => -8.75,
            'target_lon' => 111.75,
            'departure_date' => '2026-11-20',
            'hsi_score' => 0.82,
        ];

        $oneDay = $this->planner->generateCompleteTripPlan($params + ['trip_days' => 1]);
        $threeDays = $this->planner->generateCompleteTripPlan($params + ['trip_days' => 3]);

        $this->assertSame(3, $threeDays['trip_days']);
        $this->assertGreaterThan($oneDay['effective_fishing_hours'], $threeDays['effective_fishing_hours']);
        $this->assertGreaterThan($oneDay['provisions_cost_rp'], $threeDays['provisions_cost_rp']);
        $this->assertGreaterThan($oneDay['total_operational_cost_rp'], $threeDays['total_operational_cost_rp']);
        $this->assertNotSame($oneDay['economics']['net_profit_rp'], $threeDays['economics']['net_profit_rp']);
        $this->assertGreaterThan($oneDay['catch_projection']['projected_kg'], $threeDays['catch_projection']['projected_kg']);
    }

    public function test_one_day_long_route_requires_additional_days(): void
    {
        $plan = $this->planner->generateCompleteTripPlan([
            'port_code' => 'PPN_PRIGI',
            'gt_class' => '10 - 20 GT',
            'target_commodity' => 'Cakalang',
            'target_lat' => -10.0,
            'target_lon' => 111.7275,
            'departure_date' => '2026-11-20',
            'trip_days' => 1,
        ]);

        $this->assertSame('PERLU_TAMBAH_HARI', $plan['duration_feasibility_status']);
        $this->assertStringContainsString('Tambah hari', $plan['duration_warning']);
        $this->assertGreaterThanOrEqual(0, $plan['effective_fishing_hours']);
    }

    public function test_long_route_duration_factor_uses_sixteen_hour_daily_reference(): void
    {
        $plan = $this->planner->generateCompleteTripPlan([
            'port_code' => 'PPN_PRIGI',
            'gt_class' => '10 - 20 GT',
            'target_commodity' => 'Cakalang',
            'target_lat' => -10.0,
            'target_lon' => 111.7275,
            'departure_date' => '2026-11-20',
            'trip_days' => 2,
            'estimate_source' => 'TEMPORAL_CPUE_CALIBRATED',
        ]);

        $this->assertGreaterThanOrEqual(20.0, $plan['fuel']['travel_time_hours']);
        $this->assertSame(16.0, TripPlanningService::STANDARD_EFFECTIVE_FISHING_HOURS_PER_DAY);
        $this->assertSame(
            round(min(2.0, max(0.0, (float)$plan['effective_fishing_hours']) / 16.0), 2),
            $plan['catch_projection']['duration_factor']
        );
        $this->assertLessThanOrEqual(2.0, $plan['catch_projection']['duration_factor']);
        $this->assertSame('FORMULA_SCENARIO', $plan['estimate_source']);
    }

    public function test_daily_provisions_follow_vessel_gt_class(): void
    {
        foreach ([
            '< 10 GT' => 150000,
            '10 - 20 GT' => 350000,
            '20 - 30 GT' => 650000,
            '> 30 GT' => 1200000,
        ] as $gtClass => $dailyCost) {
            $plan = $this->planner->generateCompleteTripPlan([
                'port_code' => 'PPN_PRIGI',
                'gt_class' => $gtClass,
                'target_commodity' => 'Cakalang',
                'target_lat' => -8.75,
                'target_lon' => 111.75,
                'trip_days' => 3,
            ]);

            $this->assertSame($dailyCost, $plan['daily_provisions_cost_rp']);
            $this->assertSame($dailyCost, $plan['economics']['daily_provisions_cost_rp']);
            $this->assertSame($dailyCost * 3, $plan['provisions_cost_rp']);
            $this->assertSame($dailyCost * 3, $plan['economics']['provisions_cost_rp']);
            $this->assertSame(
                $plan['fuel']['total_fuel_cost_rp'] + $plan['economics']['operational_fuel_cost_rp'] + $dailyCost * 3,
                $plan['economics']['total_operational_cost_rp']
            );
        }
    }

    public function test_default_readiness_keeps_optimal_factor_and_safe_trip_ready(): void
    {
        $plan = $this->planner->generateCompleteTripPlan([
            'port_code' => 'PPN_PRIGI', 'gt_class' => '10 - 20 GT',
            'target_commodity' => 'Cakalang', 'target_lat' => -8.35,
            'target_lon' => 111.7275, 'trip_days' => 1,
            'wave_height_m' => 1.0,
        ]);

        $this->assertSame('OPTIMAL', $plan['vessel_readiness']);
        $this->assertSame(1.0, $plan['readiness_factor']);
        $this->assertSame('SIAP_MELAUT', $plan['readiness_status']);
        $this->assertSame([], $plan['readiness_notes']);
        $this->assertFalse($plan['endurance_exceeded']);
        $this->assertFalse($plan['is_hold_capped']);
    }

    public function test_invalid_direct_service_readiness_falls_back_to_optimal(): void
    {
        $plan = $this->planner->generateCompleteTripPlan(['vessel_readiness' => ['TERBATAS']]);

        $this->assertSame('OPTIMAL', $plan['vessel_readiness']);
        $this->assertSame(1.0, $plan['readiness_factor']);
    }

    public function test_limited_readiness_reduces_catch_and_warns_or_rejects_by_duration(): void
    {
        $params = [
            'port_code' => 'PPN_PRIGI', 'gt_class' => '10 - 20 GT',
            'target_commodity' => 'Cakalang', 'target_lat' => -8.35,
            'target_lon' => 111.7275, 'wave_height_m' => 1.0,
        ];
        $optimal = $this->planner->generateCompleteTripPlan($params);
        $limited = $this->planner->generateCompleteTripPlan($params + ['vessel_readiness' => 'TERBATAS']);
        $limitedTwoDays = $this->planner->generateCompleteTripPlan($params + ['vessel_readiness' => 'TERBATAS', 'trip_days' => 2]);

        $this->assertSame(0.65, $limited['readiness_factor']);
        $this->assertSame((int)round($optimal['catch_projection']['projected_kg'] * 0.65), $limited['catch_projection']['projected_kg']);
        $this->assertSame('SIAP_DENGAN_CATATAN', $limited['readiness_status']);
        $this->assertSame('TIDAK_DISARANKAN', $limitedTwoDays['readiness_status']);
        $this->assertSame(
            $limited['catch_projection']['gross_revenue_rp'] - $limited['economics']['total_operational_cost_rp'],
            $limited['economics']['net_profit_rp']
        );
    }

    public function test_enough_readiness_has_085_factor_and_extreme_waves_take_priority(): void
    {
        $plan = $this->planner->generateCompleteTripPlan([
            'port_code' => 'PPN_PRIGI', 'gt_class' => '10 - 20 GT',
            'target_commodity' => 'Cakalang', 'target_lat' => -8.35,
            'target_lon' => 111.7275, 'trip_days' => 1,
            'vessel_readiness' => 'CUKUP', 'wave_height_m' => 3.2,
        ]);

        $this->assertSame(0.85, $plan['readiness_factor']);
        $this->assertSame('TIDAK_DISARANKAN', $plan['readiness_status']);
        $this->assertNotEmpty($plan['readiness_notes']);
    }

    public function test_hold_capacity_cuts_catch_and_endurance_limit_rejects_long_trip(): void
    {
        $params = [
            'port_code' => 'PPN_PRIGI', 'gt_class' => '> 30 GT',
            'target_commodity' => 'Tuna Sirip Kuning', 'target_lat' => -8.7,
            'target_lon' => 111.7275, 'trip_days' => 14,
            'wave_height_m' => 1.0,
        ];
        $capped = $this->planner->generateCompleteTripPlan($params);
        $this->assertSame(15000, $capped['max_hold_capacity_kg']);
        $this->assertSame(14, $capped['max_endurance_days']);
        $this->assertTrue($capped['is_hold_capped']);
        $this->assertSame(15000, $capped['catch_projection']['projected_kg']);
        $this->assertSame(15000 * $capped['catch_projection']['price_per_kg_rp'], $capped['catch_projection']['gross_revenue_rp']);
        $this->assertSame('SIAP_DENGAN_CATATAN', $capped['readiness_status']);

        $tooLong = $this->planner->generateCompleteTripPlan([
            'port_code' => 'PPN_PRIGI', 'gt_class' => '< 10 GT',
            'target_commodity' => 'Cakalang', 'target_lat' => -8.35,
            'target_lon' => 111.7275, 'trip_days' => 3,
            'wave_height_m' => 1.0,
        ]);
        $this->assertSame(400, $tooLong['max_hold_capacity_kg']);
        $this->assertSame(2, $tooLong['max_endurance_days']);
        $this->assertTrue($tooLong['endurance_exceeded']);
        $this->assertSame('TIDAK_DISARANKAN', $tooLong['readiness_status']);
    }

    public function test_selected_cell_overrides_defaults_and_is_returned_as_spatial_source(): void
    {
        $plan = $this->planner->generateCompleteTripPlan([
            'port_code' => 'PPS_CILACAP',
            'gt_class' => '> 30 GT',
            'target_commodity' => 'Tuna Sirip Kuning',
            'departure_date' => '2026-11-20',
            'cell_id' => 'WPP573_TEST_CELL_001',
            'selected_cell' => [
                'center_lat' => -8.9123,
                'center_lon' => 109.4567,
                'hsi_tuna_sirip_kuning' => 0.91,
                'wave_height_m' => 1.8,
                'spatial_evidence_level' => 'PARTIAL_SPATIAL_VALIDATION',
            ],
        ]);

        $this->assertSame('WPP573_TEST_CELL_001', $plan['cell_id']);
        $this->assertSame(-8.9123, $plan['target_coordinates']['lat_dec']);
        $this->assertSame(109.4567, $plan['target_coordinates']['lon_dec']);
        $this->assertSame(0.91, $plan['catch_projection']['hsi_score']);
        $this->assertSame(1.8, $plan['wave_safety']['wave_height_m']);
        $this->assertSame('SELECTED_SPATIAL_CELL', $plan['data_source_mode']);
        $this->assertSame('SELECTED_SPATIAL_CELL', $plan['target_source']);
        $this->assertSame('PARTIAL_SPATIAL_VALIDATION', $plan['spatial_evidence_level']);
        $this->assertSame(45000, $plan['catch_projection']['benchmark_price_per_kg']);
        $this->assertSame(45000, $plan['catch_projection']['price_per_kg_rp']);
        $this->assertSame('Tinggi', $plan['catch_projection']['hsi_category']);
        $this->assertSame('PATUH', $plan['permen_kp']['status_label']);
        $this->assertSame('WASPADA', $plan['wave_safety']['status_label']);
    }

    public function test_formula_fallbacks_are_explicitly_labeled(): void
    {
        $plan = $this->planner->generateCompleteTripPlan([
            'port_code' => 'PPN_PRIGI',
            'gt_class' => '< 10 GT',
            'target_commodity' => 'Cakalang',
            'departure_date' => '2026-11-20',
        ]);

        $this->assertSame('FORMULA_SCENARIO', $plan['estimate_source']);
        $this->assertSame('FALLBACK_DEFAULT', $plan['data_source_mode']);
        $this->assertSame('FALLBACK_DEFAULT', $plan['target_source']);
        $this->assertTrue($plan['fallbacks']['coordinates']);
        $this->assertTrue($plan['fallbacks']['hsi']);
        $this->assertTrue($plan['fallbacks']['wave']);
    }

    public function test_temporal_correction_requires_an_enabled_calibration_manifest(): void
    {
        $plan = $this->planner->generateCompleteTripPlan([
            'port_code' => 'PPN_PRIGI',
            'target_commodity' => 'Cakalang',
            'estimate_source' => 'TEMPORAL_CPUE_CALIBRATED',
        ]);

        $this->assertSame('FORMULA_SCENARIO', $plan['estimate_source']);
    }

    public function test_temporal_calibration_is_blocked_when_scientific_gate_is_on_hold(): void
    {
        $this->assertFalse(TripPlanningService::temporalCalibrationIsReleased([
            'status' => 'HOLD_FOR_SCIENTIFIC_REVIEW',
            'web_integration_allowed' => false,
            'species' => [
                [
                    'species' => 'tuna_sirip_kuning',
                    'web_status' => 'HOLD',
                ],
            ],
        ], 'tuna_sirip_kuning'));
    }

    public function test_palabuhanratu_remains_internal_legacy_port_but_not_public_port_list(): void
    {
        $plan = $this->planner->generateCompleteTripPlan([
            'port_code' => 'PPN_PALABUHANRATU',
            'target_lat' => -7.2,
            'target_lon' => 106.8,
        ]);

        $this->assertSame('PPN_PALABUHANRATU', $plan['port']['code']);
        $this->assertArrayHasKey('PPN_PALABUHANRATU', TripPlanningService::PORTS);
        $this->assertArrayNotHasKey('PPN_PALABUHANRATU', TripPlanningService::availablePorts());
    }

    public function test_recommend_top_cells_filters_land_unsafe_and_non_compliant_and_ranks_by_profit(): void
    {
        $candidates = [
            // Land cell (depth <= 0) must be excluded
            ['cell_id' => 'LAND_01', 'center_lat' => -8.30, 'center_lon' => 111.73, 'depth_m' => 0, 'hsi_cakalang' => 0.99, 'wave_height_m' => 0.5, 'bycatch_traffic_light' => 'GREEN'],
            // Extreme wave (> 2.5 m) must be excluded
            ['cell_id' => 'WAVE_BAD', 'center_lat' => -8.35, 'center_lon' => 111.73, 'depth_m' => 120, 'hsi_cakalang' => 0.95, 'wave_height_m' => 3.1, 'bycatch_traffic_light' => 'GREEN'],
            // RED bycatch must be excluded
            ['cell_id' => 'RED_BYCATCH', 'center_lat' => -8.36, 'center_lon' => 111.73, 'depth_m' => 120, 'hsi_cakalang' => 0.94, 'wave_height_m' => 1.0, 'bycatch_traffic_light' => 'RED'],
            // > 12 NM for < 10 GT must be excluded
            ['cell_id' => 'TOO_FAR_LT10', 'center_lat' => -8.90, 'center_lon' => 111.73, 'depth_m' => 450, 'hsi_cakalang' => 0.98, 'wave_height_m' => 1.1, 'bycatch_traffic_light' => 'GREEN'],
            // Valid inshore cell #2 (lower HSI)
            ['cell_id' => 'VALID_MID', 'center_lat' => -8.35, 'center_lon' => 111.73, 'depth_m' => 80, 'hsi_cakalang' => 0.62, 'wave_height_m' => 1.0, 'bycatch_traffic_light' => 'GREEN'],
            // Valid inshore cell #1 (higher HSI -> higher profit)
            ['cell_id' => 'VALID_TOP', 'center_lat' => -8.36, 'center_lon' => 111.73, 'depth_m' => 95, 'hsi_cakalang' => 0.88, 'wave_height_m' => 1.0, 'bycatch_traffic_light' => 'GREEN'],
        ];

        $top = $this->planner->recommendTopCells([
            'port_code' => 'PPN_PRIGI',
            'gt_class' => '< 10 GT',
            'target_commodity' => 'Cakalang',
            'departure_date' => '2026-11-20',
            'trip_days' => 1,
        ], $candidates, 5);

        $this->assertCount(2, $top);
        $this->assertSame('VALID_TOP', $top[0]['cell_id']);
        $this->assertSame('VALID_MID', $top[1]['cell_id']);
        $this->assertSame('FORMULA_SCENARIO', $top[0]['estimate_source']);
        $this->assertSame('PROXY_HSI', $top[0]['spatial_evidence_level']);
        $this->assertGreaterThan($top[1]['net_profit_rp'], $top[0]['net_profit_rp']);

        $gpx = $this->planner->exportWaypointsGpx($top, ['title' => 'Test GPX', 'date' => '2026-11-20']);
        $this->assertStringContainsString('<gpx version="1.1"', $gpx);
        $this->assertStringContainsString('<wpt lat="-8.36000" lon="111.73000">', $gpx);
        $this->assertStringContainsString('#1 VALID_TOP', $gpx);
    }
}

