<?php

namespace App\Domain\DynamicOceanManagement\DTO;

class DomZoneClassification
{
    public function __construct(
        public readonly string $zoneKey,
        public readonly string $zoneName,
        public readonly string $description,
        public readonly int $priorityRank, // 1 is highest (Z4) to 6 (GEN)
        public readonly string $reasonCode,
        public readonly array $metCriteria = []
    ) {}
}

