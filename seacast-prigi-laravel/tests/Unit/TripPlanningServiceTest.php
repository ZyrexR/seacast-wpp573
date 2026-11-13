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
}

