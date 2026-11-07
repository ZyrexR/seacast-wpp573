<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class SpatialGeometryValidationTest extends TestCase
{
    private string $conn = 'pgsql';

    protected function setUp(): void
    {
        parent::setUp();
        // Skip if PostGIS is not reachable (e.g. CI without PG)
        try {
            DB::connection($this->conn)->select('SELECT 1');
        } catch (\Throwable $e) {
            $this->markTestSkipped('PostgreSQL PostGIS connection unavailable: ' . $e->getMessage());
        }
    }

    public function test_canonical_grid_count_matches_exact_total_8129(): void
    {
        $count = DB::connection($this->conn)
            ->table('canonical_grids')
            ->count();

        $this->assertEquals(8129, $count, 'Canonical grids table must have exactly 8,129 cells.');
    }

    public function test_all_canonical_grids_have_valid_geometries(): void
    {
        $invalidGeomCount = DB::connection($this->conn)
            ->select("SELECT COUNT(*) AS invalid_count FROM canonical_grids WHERE ST_IsValid(geom) = false")[0]->invalid_count;

        $this->assertEquals(0, (int) $invalidGeomCount, 'All 8,129 cells must have ST_IsValid(geom) = true.');
    }

    public function test_all_canonical_grids_have_srid_4326(): void
    {
        $nonSrid4326Count = DB::connection($this->conn)
            ->select("SELECT COUNT(*) AS non_srid_count FROM canonical_grids WHERE ST_SRID(geom) != 4326")[0]->non_srid_count;

        $this->assertEquals(0, (int) $nonSrid4326Count, 'All 8,129 cells must have SRID 4326 (WGS 84).');
    }

    public function test_all_canonical_grids_are_polygons(): void
    {
        $nonPolygonCount = DB::connection($this->conn)
            ->select("SELECT COUNT(*) AS non_polygon_count FROM canonical_grids WHERE ST_GeometryType(geom) != 'ST_Polygon'")[0]->non_polygon_count;

        $this->assertEquals(0, (int) $nonPolygonCount, 'All 8,129 cells must be ST_Polygon.');
    }

    public function test_bounding_box_within_southern_java_and_bali_nusa_tenggara_limits(): void
    {
        $bbox = DB::connection($this->conn)
            ->select("
                SELECT 
                    MIN(ST_XMin(geom)) AS min_lon,
                    MAX(ST_XMax(geom)) AS max_lon,
                    MIN(ST_YMin(geom)) AS min_lat,
                    MAX(ST_YMax(geom)) AS max_lat
                FROM canonical_grids
            ")[0];

        // Bounds: 102.54°E to 126.44°E, -13.96°S to -6.86°S
        $this->assertGreaterThanOrEqual(102.0, (float) $bbox->min_lon, 'Min Lon within geographic bounds');
        $this->assertLessThanOrEqual(127.0, (float) $bbox->max_lon, 'Max Lon within geographic bounds');
        $this->assertGreaterThanOrEqual(-14.5, (float) $bbox->min_lat, 'Min Lat within geographic bounds');
        $this->assertLessThanOrEqual(-6.5, (float) $bbox->max_lat, 'Max Lat within geographic bounds');
    }

    public function test_sector_distribution_sums_to_exact_total_8129(): void
    {
        $sectors = DB::connection($this->conn)
            ->select("
                SELECT sector, COUNT(*) AS cell_count 
                FROM canonical_grids 
                GROUP BY sector 
                ORDER BY sector
            ");

        $sectorMap = [];
        $total = 0;
        foreach ($sectors as $row) {
            $sectorMap[$row->sector] = (int) $row->cell_count;
            $total += (int) $row->cell_count;
        }

        $this->assertEquals(8129, $total, 'Sum of all sectors must equal 8,129.');
        $this->assertEquals(4561, $sectorMap['BALI_NUSA_TENGGARA'] ?? 0, 'BALI_NUSA_TENGGARA count');
        $this->assertEquals(2121, $sectorMap['JABAR_JATENG'] ?? 0, 'JABAR_JATENG count');
        $this->assertEquals(1447, $sectorMap['JATIM'] ?? 0, 'JATIM count');
    }

    public function test_port_distance_is_non_negative_and_finite(): void
    {
        $invalidDistCount = DB::connection($this->conn)
            ->select("
                SELECT COUNT(*) AS invalid_count 
                FROM canonical_grids 
                WHERE dist_port_nm < 0 OR dist_port_nm IS NULL
            ")[0]->invalid_count;

        $this->assertEquals(0, (int) $invalidDistCount, 'Port distance (NM) must be non-negative and non-null.');
    }

    public function test_bathymetric_depth_is_physically_consistent(): void
    {
        // In oceanographic modeling, depth_m is represented as positive water depth (0m at surface, down to ~7500m in Java Trench)
        $invalidDepthCount = DB::connection($this->conn)
            ->select("
                SELECT COUNT(*) AS invalid_count 
                FROM canonical_grids 
                WHERE depth_m < 0 OR depth_m > 8500 OR depth_m IS NULL
            ")[0]->invalid_count;

        $this->assertEquals(0, (int) $invalidDepthCount, 'Bathymetric depth must be between 0m (sea level) and 8500m (Java Trench).');
    }

    public function test_spatial_gist_index_is_present_on_canonical_grids(): void
    {
        $indexes = DB::connection($this->conn)
            ->select("
                SELECT indexname, indexdef 
                FROM pg_indexes 
                WHERE tablename = 'canonical_grids' AND indexdef LIKE '%gist%'
            ");

        $this->assertNotEmpty($indexes, 'canonical_grids table must have a GiST spatial index on geom.');
    }
}
