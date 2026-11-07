<?php

namespace App\Domain\Bycatch\DTO;

class BycatchClassification
{
    public function __construct(
        public readonly string $trafficLight, // GREEN | YELLOW | RED
        public readonly string $level,        // LOW | PRECAUTIONARY | HIGH
        public readonly string $reasonCode,
        public readonly string $description,
        public readonly array $conditions = []
    ) {}
}

