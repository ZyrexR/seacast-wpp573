<?php

namespace App\Domain\Species\DTO;

class SpeciesPrediction
{
    public function __construct(
        public readonly string $speciesKey,
        public readonly string $speciesName,
        public readonly float $hsi,
        public readonly ?float $pMaxent,
        public readonly ?float $pRf,
        public readonly ?float $pGlm,
        public readonly EnsembleWeights $weights,
        public readonly bool $isHotspot,
        public readonly bool $hasTunaPotential,
        public readonly array $evaluationMetrics = []
    ) {}
}

