<?php

namespace App\Domain\ScientificData\Services;

use App\Domain\ScientificData\Repositories\ScientificDataRepositoryInterface;

class ScientificDataService
{
    public function __construct(
        protected ScientificDataRepositoryInterface $repository
    ) {}

    public function getCanonicalGridCount(): int
    {
        return $this->repository->getCanonicalGridCount();
    }

    public function getBoundingBoxGrids(float $minLon, float $minLat, float $maxLon, float $maxLat): array
    {
        return $this->repository->getGridsInBoundingBox($minLon, $minLat, $maxLon, $maxLat);
    }

    public function getDailySummary(string $date): ?array
    {
        return $this->repository->getDailySummary($date);
    }

    public function getDailyGeoJson(string $date, array $filters = []): array
    {
        $features = $this->repository->getDailyGeoJsonFeatures($date, $filters);

        return [
            'type' => 'FeatureCollection',
            'crs' => [
                'type' => 'name',
                'properties' => [
                    'name' => 'urn:ogc:def:crs:OGC:1.3:CRS84',
                ],
            ],
            'features' => $features,
        ];
    }

    public function getDistanceToPortNm(float $lon, float $lat): float
    {
        return $this->repository->calculateGeodesicDistanceNm($lon, $lat);
    }
}

