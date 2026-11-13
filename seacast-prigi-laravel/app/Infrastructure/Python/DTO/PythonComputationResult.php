<?php

namespace App\Infrastructure\Python\DTO;

class PythonComputationResult
{
    public function __construct(
        public readonly string $status,
        public readonly string $engine,
        public readonly string $modelVersion,
        public readonly array $species,
        public readonly string $generatedAt,
        public readonly array $parameters,
        public readonly int $totalCells,
        public readonly int $hotspotsCount,
        public readonly array $data,
        public readonly array $warnings = [],
        public readonly array $provenance = [],
        public readonly float $executionDurationMs = 0.0
    ) {}

    public static function fromArray(array $payload, float $durationMs = 0.0): self
    {
        return new self(
            status: $payload['status'] ?? 'SUCCESS',
            engine: $payload['engine'] ?? 'SEACAST_SDM_ENGINE',
            modelVersion: $payload['model_version'] ?? '2.3.2',
            species: $payload['species'] ?? ['tuna_sirip_kuning', 'cakalang', 'tongkol_krai'],
            generatedAt: $payload['generated_at'] ?? now()->toIso8601String(),
            parameters: $payload['parameters'] ?? [],
            totalCells: (int)($payload['total_cells'] ?? count($payload['data'] ?? [])),
            hotspotsCount: (int)($payload['hotspots_count'] ?? 0),
            data: $payload['data'] ?? [],
            warnings: $payload['warnings'] ?? [],
            provenance: $payload['provenance'] ?? [],
            executionDurationMs: $durationMs
        );
    }
}

