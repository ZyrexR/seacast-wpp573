<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\DatabaseHealthService;
use App\Services\PostGisService;
use App\Models\CanonicalGrid;
use App\Models\DailyPrediction;
use App\Models\GridMonthlyPrediction;
use App\Models\PrigiPortLanding;
use App\Models\PrigiFleetByGt;

class DatabaseHealthTest extends TestCase
{
    public function test_database_health_service_reports_healthy_and_exact_counts(): void
    {
        $service = new DatabaseHealthService();
        $health = $service->checkHealth();

        $this->assertEquals('healthy', $health['status']);
        $this->assertTrue($health['engine']['postgis_available']);
        $this->assertStringContainsString('17.', $health['engine']['postgresql_version']);
        $this->assertStringContainsString('3.6.', $health['engine']['postgis_version']);

        // Exact baseline spatial verification
        $this->assertEquals(8129, $health['spatial_baseline']['canonical_grids_count']);
        $this->assertEquals(8129, $health['spatial_baseline']['valid_geometries']);
        $this->assertEquals(0, $health['spatial_baseline']['invalid_geometries']);
        $this->assertEquals(4326, $health['spatial_baseline']['srid']);
        $this->assertEquals('POLYGON', $health['spatial_baseline']['geometry_type']);
        $this->assertTrue($health['spatial_baseline']['gist_index_active']);

        // Exact baseline records verification
        $this->assertEquals(16258, $health['data_records']['daily_predictions_count']);
        $this->assertEquals('2026-11-07', $health['data_records']['latest_prediction_date']);
        $this->assertEquals(97548, $health['data_records']['grid_monthly_predictions_count']);
        $this->assertEquals(132, $health['data_records']['prigi_port_landings_count']);
        $this->assertEquals(3, $health['data_records']['prigi_fleet_rows_count']);
        $this->assertEquals(1269, $health['data_records']['total_registered_vessels']);
    }

    public function test_postgis_service_extent_and_bounding_box(): void
    {
        $postGis = new PostGisService();
        $extent = $postGis->getGridExtent();

        $this->assertEquals(102.5, $extent['min_lon']);
        $this->assertEquals(-14.0, $extent['min_lat']);
        $this->assertEquals(126.5, $extent['max_lon']);
        $this->assertEquals(-6.8, $extent['max_lat']);

        // Test BBox query around Prigi Homeport (111.7275, -8.2833)
        $cells = $postGis->getCellsInBoundingBox(111.5, -8.5, 112.0, -8.0, 10);
        $this->assertNotEmpty($cells);
        $this->assertLessThanOrEqual(10, count($cells));
        $this->assertNotNull($cells[0]->geojson);
    }

    public function test_eloquent_models_read_only_access(): void
    {
        $this->assertEquals(8129, CanonicalGrid::count());
        $this->assertEquals(16258, DailyPrediction::count());
        $this->assertEquals(97548, GridMonthlyPrediction::count());
        $this->assertEquals(132, PrigiPortLanding::count());
        $this->assertEquals(3, PrigiFleetByGt::count());

        $firstCell = CanonicalGrid::first();
        $this->assertNotNull($firstCell);
        $this->assertEquals(4326, $firstCell->getConnection()->selectOne("SELECT ST_SRID(geom) as srid FROM canonical_grids WHERE cell_id = ?", [$firstCell->cell_id])->srid);
    }
}

