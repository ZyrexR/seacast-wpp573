<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domain\Species\DTO\EnsembleWeights;
use App\Domain\Species\DTO\SpeciesPrediction;
use App\Domain\Species\Services\SpeciesPredictionService;
use App\Domain\Species\Services\HabitatSuitabilityIndexService;

class SpeciesAndHsiEngineTest extends TestCase
{
    protected SpeciesPredictionService $speciesService;
    protected HabitatSuitabilityIndexService $hsiService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->speciesService = app(SpeciesPredictionService::class);
        $this->hsiService = app(HabitatSuitabilityIndexService::class);
    }

    public function test_locked_ensemble_weights_match_scientific_baseline(): void
    {
        $cakalangWeights = $this->speciesService->getEnsembleWeights('cakalang');
        $this->assertEquals(0.320, $cakalangWeights->maxent);
        $this->assertEquals(0.355, $cakalangWeights->rf);
        $this->assertEquals(0.325, $cakalangWeights->glm);
        $this->assertEquals(1.000, $cakalangWeights->total());

        $madidihangWeights = $this->speciesService->getEnsembleWeights('madidihang');
        $this->assertEquals(0.295, $madidihangWeights->maxent);
        $this->assertEquals(0.365, $madidihangWeights->rf);
        $this->assertEquals(0.340, $madidihangWeights->glm);
        $this->assertEquals(1.000, $madidihangWeights->total());

        $albakorWeights = $this->speciesService->getEnsembleWeights('albakor');
        // Albakor MaxEnt weight MUST be strictly 0.000
        $this->assertEquals(0.000, $albakorWeights->maxent);
        $this->assertEquals(0.517, $albakorWeights->rf);
        $this->assertEquals(0.483, $albakorWeights->glm);
        $this->assertEquals(1.000, $albakorWeights->total());
    }

    public function test_raw_ensemble_calculation_formula(): void
    {
        // For Cakalang: 0.320 * 0.5 + 0.355 * 0.6 + 0.325 * 0.4
        // = 0.160 + 0.213 + 0.130 = 0.503
        $score = $this->hsiService->calculateRawEnsemble('cakalang', 0.50, 0.60, 0.40);
        $this->assertEquals(0.503, round($score, 3));

        // For Albakor: 0.000 * 0.999 + 0.517 * 0.60 + 0.483 * 0.50
        // = 0.000 + 0.3102 + 0.2415 = 0.5517 -> 0.552
        $scoreAlbakor = $this->hsiService->calculateRawEnsemble('albakor', 0.999, 0.60, 0.50);
        $this->assertEquals(0.552, round($scoreAlbakor, 3));
    }

    public function test_hsi_normalization_and_clamping(): void
    {
        $rawScores = [0.2, 0.4, 0.6, 0.8];
        $normalized = $this->hsiService->normalizeScores($rawScores);

        $this->assertEquals(0.0, $normalized[0]);
        $this->assertEquals(1.0, $normalized[3]);
        $this->assertEquals(0.333, round($normalized[1], 3));
    }

    public function test_hotspot_and_potential_thresholds(): void
    {
        // Hotspot: HSI Cakalang >= 0.70 or Madidihang >= 0.70
        $this->assertTrue($this->hsiService->isHotspot(0.72, 0.40));
        $this->assertTrue($this->hsiService->isHotspot(0.50, 0.75));
        $this->assertFalse($this->hsiService->isHotspot(0.68, 0.69));

        // Tuna presence for DOM Z1/Z2: >= 0.55
        $this->assertTrue($this->hsiService->hasTunaPotential(0.56, 0.20));
        $this->assertTrue($this->hsiService->hasTunaPotential(0.30, 0.55));
        $this->assertFalse($this->hsiService->hasTunaPotential(0.54, 0.54));
    }

    public function test_evaluation_metrics_are_separated_from_hsi(): void
    {
        $metrics = $this->speciesService->getModelEvaluationSummary('cakalang');
        $this->assertEquals(0.795, $metrics['auc']);
        $this->assertEquals(0.630, $metrics['tss']);
        $this->assertNotEquals($metrics['auc'], $this->hsiService->calculateRawEnsemble('cakalang', 0.5, 0.5, 0.5));
    }
}

