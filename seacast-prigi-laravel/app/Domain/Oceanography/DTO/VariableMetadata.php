<?php

namespace App\Domain\Oceanography\DTO;

class VariableMetadata
{
    public function __construct(
        public readonly string $key,
        public readonly string $name,
        public readonly string $unit,
        public readonly string $source,
        public readonly string $sourceType, // OBSERVED | MODELED | SIMULATED
        public readonly float $minValid,
        public readonly float $maxValid,
        public readonly string $resolution,
        public readonly string $temporalFrequency,
        public readonly string $description
    ) {}
}

