<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Domain\ScientificData\Repositories\PostGisScientificDataRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class ScientificDataRepositoryTest extends TestCase
{
    protected PostGisScientificDataRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new PostGisScientificDataRepository();
    }

    public function test_read_only_privilege_strictly_blocks_mutations(): void
    {
        $this->expectException(QueryException::class);
        DB::connection('pgsql')->statement("UPDATE canonical_grids SET cell_class = 'MUTATION_TEST' WHERE cell_id = 'CELL_0001'");
    }

    public function test_spatial_bounding_box_utilizes_envelope_filter(): void
    {
        $results = $this->repo->getGridsInBoundingBox(111.5, -8.5, 112.0, -8.1);
        $this->assertNotEmpty($results);

        foreach ($results as $row) {
            $this->assertNotNull($row->geojson_geom);
            $geo = json_decode($row->geojson_geom, true);
            $this->assertEquals('Polygon', $geo['type']);
        }
    }

    public function test_daily_geojson_features_respect_filters(): void
    {
        // Filter by sector JATIM
        $features = $this->repo->getDailyGeoJsonFeatures('2026-11-03', [
            'sector' => 'JATIM',
            'limit' => 20
        ]);
        $this->assertNotEmpty($features);
        $this->assertLessThanOrEqual(20, count($features));

        foreach ($features as $f) {
            $this->assertEquals('JATIM', $f['properties']['sector']);
        }

        // Filter by DOM Zone Z4_NURSERY_GROUND
        $z4Features = $this->repo->getDailyGeoJsonFeatures('2026-11-03', [
            'dom_zone' => 'Z4_NURSERY_GROUND',
            'limit' => 10
        ]);
        foreach ($z4Features as $f) {
            $this->assertEquals('Z4_NURSERY_GROUND', $f['properties']['dom_zone']);
            $this->assertLessThanOrEqual(150.0, $f['properties']['depth_m']);
            $this->assertLessThanOrEqual(15.0, $f['properties']['dist_port_nm']);
        }
    }

    public function test_monthly_predictions_can_be_retrieved(): void
    {
        $monthly = $this->repo->getMonthlyPredictions('2025_08', ['limit' => 15]);
        $this->assertCount(15, $monthly);
        $this->assertEquals('2025_08', $monthly[0]->year_month);
    }

    public function test_landings_and_fleet_aggregation(): void
    {
        $landings = $this->repo->getLandings(2025);
        $this->assertCount(12, $landings); // 12 months for 2025

        $fleet = $this->repo->getFleetAggregation();
        $this->assertCount(3, $fleet);
        $totalVessels = array_sum(array_column($fleet, 'vessel_count'));
        $this->assertEquals(1269, $totalVessels);
    }
}

