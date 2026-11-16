<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domain\ScientificData\Repositories\ScientificDataRepositoryInterface;
use App\Domain\ScientificData\Repositories\PostGisScientificDataRepository;
use App\Domain\ScientificData\Services\ScientificDataService;
use Illuminate\Support\Facades\File;

class ScientificDataAccessTest extends TestCase
{
    protected ScientificDataRepositoryInterface $repository;
    protected ScientificDataService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PostGisScientificDataRepository();
        $this->service = new ScientificDataService($this->repository);
    }

    public function test_can_retrieve_canonical_grids_count(): void
    {
        $count = $this->repository->getCanonicalGridCount();
        $this->assertEquals(8129, $count);
    }

    public function test_can_retrieve_grids_by_bounding_box(): void
    {
        // Bounding box around Prigi bay: Lon 111.0 to 112.5, Lat -9.0 to -8.0
        $grids = $this->repository->getGridsInBoundingBox(111.0, -9.0, 112.5, -8.0);
        $this->assertNotEmpty($grids);
        $this->assertLessThan(8129, count($grids));

        foreach ($grids as $grid) {
            $this->assertGreaterThanOrEqual(111.0 - 0.1, $grid->center_lon);
            $this->assertLessThanOrEqual(112.5 + 0.1, $grid->center_lon);
            $this->assertGreaterThanOrEqual(-9.0 - 0.1, $grid->center_lat);
            $this->assertLessThanOrEqual(-8.0 + 0.1, $grid->center_lat);
        }
    }

    public function test_can_retrieve_daily_prediction_summary(): void
    {
        $summary = $this->repository->getDailySummary('2026-11-03');
        $this->assertNotNull($summary);
        $this->assertEquals('2026-11-03', $summary['date']);
        $this->assertEquals(8129, $summary['summary']['total_cells']);
        $this->assertArrayHasKey('hotspots_count', $summary['summary']);
        $this->assertArrayHasKey('mean_sst', $summary['summary']);
        $this->assertArrayHasKey('mean_chla', $summary['summary']);
        $this->assertArrayHasKey('species', $summary['summary']);
        $this->assertArrayHasKey('bycatch', $summary['summary']);
    }

    public function test_can_retrieve_daily_predictions_with_geojson_features(): void
    {
        $features = $this->repository->getDailyGeoJsonFeatures('2026-11-03', ['limit' => 10]);
        $this->assertCount(10, $features);
        $this->assertEquals('Feature', $features[0]['type']);
        $this->assertArrayHasKey('properties', $features[0]);
        $this->assertArrayHasKey('geometry', $features[0]);
        $this->assertEquals('Polygon', $features[0]['geometry']['type']);
    }

    public function test_service_exposes_spatial_provenance_without_changing_legacy_summary_fields(): void
    {
        $summary = $this->service->getDailySummary('2026-11-03');

        $this->assertNotNull($summary);
        $this->assertArrayHasKey('sources', $summary['summary']);
        $this->assertArrayHasKey('provenance', $summary);
        $this->assertSame(
            [
                'evidence_level',
                'source_mode',
                'model_version',
                'prediction_timestamp',
                'validation_status',
                'source',
                'date_coverage',
                'ingestion_status',
                'fallback_used',
            ],
            array_keys($summary['provenance'])
        );
        $this->assertNotEmpty($summary['provenance']['model_version']);
        $this->assertNotEmpty($summary['provenance']['prediction_timestamp']);
    }

    public function test_service_does_not_claim_live_ingest_when_manifest_is_missing(): void
    {
        $manifestPath = storage_path('app/gee_ingestion/ingestion_manifest.json');
        $existed = File::exists($manifestPath);
        $backup = $existed ? File::get($manifestPath) : null;
        File::delete($manifestPath);

        try {
            $provenance = $this->service->getSpatialProvenance('2026-11-03');

            $this->assertSame('DATABASE_SNAPSHOT_SOURCE_UNVERIFIED', $provenance['source_mode']);
            $this->assertSame('UNVERIFIED', $provenance['validation_status']);
            $this->assertSame('UNVERIFIED', $provenance['ingestion_status']);
            $this->assertFalse($provenance['fallback_used']);
        } finally {
            File::delete($manifestPath);
            if ($existed && $backup !== null) {
                File::put($manifestPath, $backup);
            }
        }
    }

    public function test_geojson_places_provenance_in_top_level_metadata(): void
    {
        $geoJson = $this->service->getDailyGeoJson('2026-11-03', ['limit' => 1]);

        $this->assertArrayHasKey('metadata', $geoJson);
        $this->assertSame('2026-11-03', $geoJson['metadata']['prediction_date']);
        $this->assertArrayHasKey('provenance', $geoJson['metadata']);
        $this->assertArrayHasKey('validation_status', $geoJson['metadata']['provenance']);
        $this->assertCount(1, $geoJson['features']);
    }

    public function test_geodesic_distance_to_prigi_port(): void
    {
        // Cell near Prigi entrance (e.g. CELL_0001 or closest cell)
        $distanceNm = $this->repository->calculateGeodesicDistanceNm(111.7275, -8.2833);
        $this->assertLessThan(0.01, $distanceNm); // 0 nautical miles at exact homeport
    }
}

