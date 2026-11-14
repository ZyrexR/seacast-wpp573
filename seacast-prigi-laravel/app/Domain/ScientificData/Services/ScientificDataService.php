<?php

namespace App\Domain\ScientificData\Services;

use App\Domain\ScientificData\Repositories\ScientificDataRepositoryInterface;
use Carbon\Carbon;

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
        $summary = $this->repository->getDailySummary($date);

        if ($summary === null) {
            return null;
        }

        $summary['provenance'] = $this->getSpatialProvenance($date);

        return $summary;
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
            'metadata' => [
                'prediction_date' => $date,
                'provenance' => $this->getSpatialProvenance($date),
            ],
            'features' => $features,
        ];
    }

    /**
     * Resolve spatial prediction provenance at the API boundary.
     *
     * The current daily_predictions table intentionally remains unchanged.
     * Evidence and validation fields are assembled from the ingestion and
     * model manifests, with explicit conservative defaults when a manifest is
     * missing or does not cover the requested date.
     */
    public function getSpatialProvenance(string $date): array
    {
        $rootPath = dirname(base_path());
        $ingestionManifest = $this->readJson(storage_path('app/gee_ingestion/ingestion_manifest.json'));
        $modelSummary = $this->readJson($rootPath . '/models/sdm/ensemble/model_evaluations_summary.json');
        $runManifest = $this->readJson($rootPath . '/outputs/tct_hybrid_2015_2025/reports/run_manifest.json');

        $ingestionRecord = collect($ingestionManifest)
            ->first(fn (mixed $record): bool => is_array($record)
                && str_starts_with((string)($record['target_date'] ?? ''), $date));

        $occurrenceValidation = is_array($runManifest['occurrence_validation'] ?? null)
            ? $runManifest['occurrence_validation']
            : [];

        $evidenceLevel = (string)($runManifest['spatial_evidence_level']
            ?? $modelSummary['spatial_evidence_level']
            ?? 'PROXY_HSI');

        $validationStatus = (string)($occurrenceValidation['status']
            ?? 'UNVERIFIED_NO_OCCURRENCE_AUDIT');

        $modelVersion = (string)($runManifest['model_version']
            ?? $modelSummary['model_version']
            ?? 'TCT_HYBRID_2015_2025');

        $predictionTimestamp = (string)($ingestionRecord['completed_at']
            ?? $runManifest['generated_at']
            ?? Carbon::createFromFormat('Y-m-d', $date, 'Asia/Jakarta')
                ->startOfDay()
                ->toIso8601String());

        $sourceMode = $ingestionRecord !== null
            ? 'GEE_INGESTED_WITH_LOCAL_FALLBACKS'
            : 'DATABASE_SNAPSHOT_SOURCE_UNVERIFIED';

        return [
            'evidence_level' => $evidenceLevel,
            'source_mode' => $sourceMode,
            'model_version' => $modelVersion,
            'prediction_timestamp' => $predictionTimestamp,
            'validation_status' => $validationStatus,
        ];
    }

    private function readJson(string $path): array
    {
        if (!is_file($path)) {
            return [];
        }

        $decoded = json_decode((string)file_get_contents($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    public function getDistanceToPortNm(float $lon, float $lat): float
    {
        return $this->repository->calculateGeodesicDistanceNm($lon, $lat);
    }
}

