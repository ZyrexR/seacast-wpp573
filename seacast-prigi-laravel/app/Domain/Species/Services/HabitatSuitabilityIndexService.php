<?php

namespace App\Domain\Species\Services;

class HabitatSuitabilityIndexService
{
    public const HOTSPOT_THRESHOLD = 0.70;
    public const TUNA_POTENTIAL_THRESHOLD = 0.55;

    public function __construct(
        protected SpeciesPredictionService $speciesService
    ) {}

    public function calculateRawEnsemble(string $species, float $pMaxent, float $pRf, float $pGlm): float
    {
        $weights = $this->speciesService->getEnsembleWeights($species);

        return ($weights->maxent * $pMaxent) + ($weights->rf * $pRf) + ($weights->glm * $pGlm);
    }

    public function normalizeScores(array $rawScores): array
    {
        if (empty($rawScores)) {
            return [];
        }

        $min = min($rawScores);
        $max = max($rawScores);

        if ($max <= $min) {
            return array_fill(0, count($rawScores), 0.0);
        }

        $range = $max - $min;
        return array_map(function ($val) use ($min, $range) {
            $norm = ($val - $min) / $range;
            return round(max(0.0, min(1.0, $norm)), 3);
        }, $rawScores);
    }

    public function isHotspot(float $hsiCakalang, float $hsiMadidihang): bool
    {
        return $hsiCakalang >= self::HOTSPOT_THRESHOLD || $hsiMadidihang >= self::HOTSPOT_THRESHOLD;
    }

    public function hasTunaPotential(float $hsiCakalang, float $hsiMadidihang): bool
    {
        return $hsiCakalang >= self::TUNA_POTENTIAL_THRESHOLD || $hsiMadidihang >= self::TUNA_POTENTIAL_THRESHOLD;
    }
}

