<?php

namespace App\Domain\ScientificData\Repositories;

interface ScientificDataRepositoryInterface
{
    public function getCanonicalGridCount(): int;

    public function getGridsInBoundingBox(float $minLon, float $minLat, float $maxLon, float $maxLat): array;

    public function getDailySummary(string $date): ?array;

    public function getDailyGeoJsonFeatures(string $date, array $filters = []): array;

    public function calculateGeodesicDistanceNm(float $lon, float $lat): float;

    public function getMonthlyPredictions(string $yearMonth, array $filters = []): array;

    public function getLandings(?int $year = null): array;

    public function getFleetAggregation(): array;
}

