<?php

namespace App\Domain\Oceanography\DTO;

class OceanographicConditions
{
    public function __construct(
        public readonly string $cellId,
        public readonly string $date,
        public readonly float $sst,
        public readonly ?float $gradSst,
        public readonly bool $isThermalFront,
        public readonly float $chla,
        public readonly ?float $currentVelocity,
        public readonly ?float $waveHeight,
        public readonly ?float $windSpeed,
        public readonly ?float $depth,
        public readonly ?float $distPortNm,
        public readonly string $safetyAdvisory,
        public readonly array $qualityFlags = []
    ) {}
}

