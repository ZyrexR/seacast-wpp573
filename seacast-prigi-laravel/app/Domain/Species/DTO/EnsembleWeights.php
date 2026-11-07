<?php

namespace App\Domain\Species\DTO;

class EnsembleWeights
{
    public function __construct(
        public readonly float $maxent,
        public readonly float $rf,
        public readonly float $glm
    ) {}

    public function total(): float
    {
        return round($this->maxent + $this->rf + $this->glm, 3);
    }

    public function toArray(): array
    {
        return [
            'maxent' => $this->maxent,
            'rf' => $this->rf,
            'glm' => $this->glm,
        ];
    }
}

