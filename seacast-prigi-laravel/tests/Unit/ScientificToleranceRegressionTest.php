<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domain\Species\Services\SpeciesPredictionService;
use App\Domain\Species\Services\HabitatSuitabilityIndexService;
use App\Domain\DynamicOceanManagement\Services\DomClassificationService;
use App\Domain\Bycatch\Services\BycatchRiskService;

class ScientificToleranceRegressionTest extends TestCase
{
    private SpeciesPredictionService $speciesService;
    private HabitatSuitabilityIndexService $hsiService;
    private DomClassificationService $domService;
    private BycatchRiskService $bycatchService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->speciesService = app(SpeciesPredictionService::class);
        $this->hsiService = app(HabitatSuitabilityIndexService::class);
        $this->domService = app(DomClassificationService::class);
        $this->bycatchService = app(BycatchRiskService::class);
    }

    public function test_ensemble_weights_sum_to_exact_unity_for_modelled_tct_species(): void
    {
        $speciesList = ['cakalang', 'tuna_sirip_kuning'];

        foreach ($speciesList as $sp) {
            $weights = $this->speciesService->getEnsembleWeights($sp);
            $sum = $weights->maxent + $weights->rf + $weights->glm;

            $this->assertEqualsWithDelta(
                1.000,
                $sum,
                0.0001,
                "Ensemble weights for species '{$sp}' must sum to exactly 1.000 within floating point delta 0.0001."
            );

            $this->assertEquals(1.000, $weights->total(), "Total method for '{$sp}' must return 1.000.");
        }
    }

    public function test_hsi_values_are_strictly_clamped_in_zero_to_one_range(): void
    {
        // Test raw ensemble calculation and min-max normalization
        $rawScores = [-0.25, 0.0, 0.45, 0.82, 1.25, 0.99];
        $normalized = $this->hsiService->normalizeScores($rawScores);

        $this->assertCount(count($rawScores), $normalized);

        foreach ($normalized as $norm) {
            $this->assertFalse(is_nan($norm), "Normalized HSI score must not be NaN.");
            $this->assertFalse(is_infinite($norm), "Normalized HSI score must not be Infinite.");
            $this->assertGreaterThanOrEqual(0.000, $norm, "Normalized HSI must be >= 0.0.");
            $this->assertLessThanOrEqual(1.000, $norm, "Normalized HSI must be <= 1.0.");
        }

        // Test raw ensemble weighted combination bounds
        $rawCakalang = $this->hsiService->calculateRawEnsemble('cakalang', 0.8, 0.9, 0.7);
        $this->assertEqualsWithDelta(0.803, $rawCakalang, 0.001);

        $rawTunaSiripKuning = $this->hsiService->calculateRawEnsemble('tuna_sirip_kuning', 0.9, 0.8, 0.6);
        $this->assertEqualsWithDelta(0.7615, $rawTunaSiripKuning, 0.001);

        // Verification of hotspot and tuna potential thresholds
        $this->assertTrue($this->hsiService->isHotspot(0.75, 0.50));
        $this->assertFalse($this->hsiService->isHotspot(0.68, 0.69));
        $this->assertTrue($this->hsiService->hasTunaPotential(0.55, 0.40));
        $this->assertFalse($this->hsiService->hasTunaPotential(0.54, 0.54));
    }

    public function test_dom_waterfall_priority_resolution_hierarchy(): void
    {
        // 1. Z4 Conflict: Meets both Z4 (nursery) and Z1 (inshore fishing ground)
        // Depth <= 150m AND dist <= 12 NM AND HSI >= 0.55
        // Z4 must override Z1 because nursery conservation takes precedence.
        $z4Conflict = $this->domService->classify([
            'depth' => 80.0,          // <= 150m (Z4)
            'dist_port_nm' => 10.0,   // <= 15 NM (Z4) AND <= 12 NM (Z1)
            'sst' => 28.5,
            'grad_sst' => 0.02,
            'chla' => 0.20,
            'hsi_cakalang' => 0.85,   // >= 0.55 (Z1)
            'hsi_madidihang' => 0.70,
        ]);
        $this->assertEquals('Z4_NURSERY_GROUND', $z4Conflict->zoneKey, 'Z4 must take priority over Z1.');

        // 2. Z5 Conflict: Meets both Z5 (bycatch screening) and Z3 (thermal front / feeding core)
        // Depth 150-1200m, SST <= 26.5°C, Grad >= 0.035 AND Chl-a >= 0.40
        $z5Conflict = $this->domService->classify([
            'depth' => 500.0,         // 150-1200m (Z5)
            'dist_port_nm' => 30.0,
            'sst' => 25.8,           // <= 26.5°C (Z5)
            'grad_sst' => 0.045,      // >= 0.035 (Z5 & Z3)
            'chla' => 0.55,           // >= 0.40 (Z3)
            'hsi_cakalang' => 0.60,
            'hsi_madidihang' => 0.60,
        ]);
        $this->assertEquals('Z5_BYCATCH_SCREENING', $z5Conflict->zoneKey, 'Z5 must take priority over Z3.');

        // 3. Z3 vs Z1 Conflict: Meets Z3 (feeding core / thermal front) and Z1 (inshore)
        // Depth > 1200m (outside Z4/Z5), Grad >= 0.035, Chl >= 0.40 AND dist <= 12 NM
        $z3Conflict = $this->domService->classify([
            'depth' => 2000.0,
            'dist_port_nm' => 10.0,   // <= 12 NM (Z1)
            'sst' => 28.0,
            'grad_sst' => 0.040,      // >= 0.035 (Z3)
            'chla' => 0.50,           // >= 0.40 (Z3)
            'hsi_cakalang' => 0.75,   // >= 0.55 (Z1)
            'hsi_madidihang' => 0.60,
        ]);
        $this->assertEquals('Z3_FEEDING_CORE', $z3Conflict->zoneKey, 'Z3 must take priority over Z1.');

        // 4. Fallback General Zone (GEN) when no thresholds are met
        $genZone = $this->domService->classify([
            'depth' => 2500.0,
            'dist_port_nm' => 80.0,   // > 60 NM
            'sst' => 29.0,
            'grad_sst' => 0.010,
            'chla' => 0.10,
            'hsi_cakalang' => 0.20,   // Low HSI
            'hsi_madidihang' => 0.20,
        ]);
        $this->assertEquals('GENERAL_OCEANIC', $genZone->zoneKey, 'Unmatched conditions must resolve to GENERAL_OCEANIC.');
    }

    public function test_bycatch_traffic_light_classification_threshold_invariance(): void
    {
        // 1. Red (High Risk): Continental slope (150-1200m), thermal front / cold SST (<= 26.5°C, grad >= 0.035), Cakalang HSI >= 0.70
        $redCase = $this->bycatchService->classify(
            depthM: 600.0,
            sstC: 25.5,
            gradSst: 0.040,
            hsiCakalang: 0.75
        );
        $this->assertEquals('RED', $redCase->trafficLight);
        $this->assertEquals('HIGH', $redCase->level);

        // 2. Yellow (Moderate / Caution): Continental slope with warm SST
        $yellowCase = $this->bycatchService->classify(
            depthM: 600.0,
            sstC: 28.0,
            gradSst: 0.040,
            hsiCakalang: 0.65
        );
        $this->assertEquals('YELLOW', $yellowCase->trafficLight);
        $this->assertEquals('PRECAUTIONARY', $yellowCase->level);

        // 3. Green (Low Risk / Safe): Shallow shelf (< 150m) outside shelf break
        $greenCase = $this->bycatchService->classify(
            depthM: 80.0,
            sstC: 29.0,
            gradSst: 0.010,
            hsiCakalang: 0.30
        );
        $this->assertEquals('GREEN', $greenCase->trafficLight);
        $this->assertEquals('LOW', $greenCase->level);
    }
}
