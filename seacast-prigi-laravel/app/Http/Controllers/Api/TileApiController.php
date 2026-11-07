<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Services\GeospatialCacheManager;

/**
 * Class TileApiController
 *
 * High-performance Mapbox Vector Tile (MVT) endpoint powered by PostGIS.
 * Slices 8,129 canonical polygons and daily prediction attributes into
 * binary Protocol Buffer tiles (application/x-protobuf) on-the-fly.
 */
class TileApiController extends Controller
{
    public const DEFAULT_DATE = '2026-11-03';
    public const ALLOWED_LAYERS = ['predictions', 'canonical_grids', 'hotspots'];
    public const MIN_ZOOM = 4;
    public const MAX_ZOOM = 16;

    public function __construct(
        protected GeospatialCacheManager $cacheManager
    ) {}

    public function getTile(Request $request, string $layer, int $z, int $x, int $y): Response
    {
        // 1. Validate layer allowlist
        if (!in_array($layer, self::ALLOWED_LAYERS, true)) {
            return response("Layer '{$layer}' is not supported.", 404)
                ->header('Content-Type', 'text/plain');
        }

        // 2. Validate zoom and tile coordinates
        if ($z < self::MIN_ZOOM || $z > self::MAX_ZOOM) {
            return response("Zoom level {$z} is out of bounds (allowed: " . self::MIN_ZOOM . "-" . self::MAX_ZOOM . ").", 400)
                ->header('Content-Type', 'text/plain');
        }

        $maxCoord = (1 << $z) - 1;
        if ($x < 0 || $x > $maxCoord || $y < 0 || $y > $maxCoord) {
            return response("Tile coordinates ({$x}, {$y}) are invalid for zoom {$z}.", 400)
                ->header('Content-Type', 'text/plain');
        }

        $date = $request->query('date', self::DEFAULT_DATE);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = self::DEFAULT_DATE;
        }

        // 3. Cache lookup
        $cacheKey = sprintf('%s:tile:v1:%s:%s:%d:%d:%d', GeospatialCacheManager::NAMESPACE_PREFIX, $layer, $date, $z, $x, $y);
        $cached = $this->cacheManager->get($cacheKey);

        if ($cached !== null && isset($cached['data']['mvt'])) {
            $mvtData = base64_decode($cached['data']['mvt']);
            $etag = $cached['etag'];

            if ($request->header('If-None-Match') === $etag) {
                return response('', 304, [
                    'ETag' => $etag,
                    'Cache-Control' => 'public, max-age=86400, stale-while-revalidate=604800',
                    'X-Cache' => 'HIT-REVALIDATED',
                ]);
            }

            if (empty($mvtData)) {
                return response('', 204, [
                    'ETag' => $etag,
                    'X-Cache' => 'HIT-EMPTY',
                    'Cache-Control' => 'public, max-age=86400, stale-while-revalidate=604800',
                ]);
            }

            return response($mvtData, 200, [
                'Content-Type' => 'application/x-protobuf',
                'Content-Length' => strlen($mvtData),
                'ETag' => $etag,
                'X-Cache' => 'HIT',
                'Cache-Control' => 'public, max-age=86400, stale-while-revalidate=604800',
            ]);
        }

        // 4. Execute PostGIS ST_AsMVT query
        $mvtBinary = $this->queryMvt($layer, $z, $x, $y, $date);

        // 5. Store in cache
        $ttl = ($layer === 'canonical_grids')
            ? GeospatialCacheManager::DEFAULT_GRID_TTL
            : GeospatialCacheManager::DEFAULT_PREDICTIONS_TTL;

        $etag = '"' . substr(hash('sha256', $mvtBinary), 0, 32) . '"';
        $this->cacheManager->put(
            $cacheKey,
            ['mvt' => base64_encode($mvtBinary)],
            $ttl,
            ['domain:tiles', 'layer:' . $layer, 'date:' . $date]
        );

        if (empty($mvtBinary)) {
            return response('', 204, [
                'ETag' => $etag,
                'X-Cache' => 'MISS-EMPTY',
                'Cache-Control' => 'public, max-age=86400, stale-while-revalidate=604800',
            ]);
        }

        return response($mvtBinary, 200, [
            'Content-Type' => 'application/x-protobuf',
            'Content-Length' => strlen($mvtBinary),
            'ETag' => $etag,
            'X-Cache' => 'MISS',
            'Cache-Control' => 'public, max-age=86400, stale-while-revalidate=604800',
        ]);
    }

    /**
     * Query PostGIS to build MVT binary protobuf using ST_TileEnvelope and ST_AsMVT.
     */
    protected function queryMvt(string $layer, int $z, int $x, int $y, string $date): string
    {
        $connection = 'pgsql';

        if ($layer === 'canonical_grids') {
            $sql = "
                WITH bbox AS (
                    SELECT ST_Transform(ST_TileEnvelope(?, ?, ?), 4326) AS geom_4326,
                           ST_TileEnvelope(?, ?, ?) AS geom_3857
                ),
                mvtgeom AS (
                    SELECT 
                        ST_AsMVTGeom(
                            ST_Transform(cg.geom, 3857),
                            bbox.geom_3857,
                            4096,
                            64,
                            true
                        ) AS geom,
                        cg.cell_id,
                        cg.cell_class,
                        cg.depth_m,
                        cg.dist_port_nm,
                        cg.sector,
                        cg.is_shelf_break,
                        cg.is_prigi_inshore
                    FROM canonical_grids cg
                    CROSS JOIN bbox
                    WHERE cg.geom && bbox.geom_4326
                )
                SELECT ST_AsMVT(mvtgeom.*, 'canonical_grids', 4096, 'geom') AS mvt FROM mvtgeom
            ";
            $params = [$z, $x, $y, $z, $x, $y];
        } elseif ($layer === 'hotspots') {
            $sql = "
                WITH bbox AS (
                    SELECT ST_Transform(ST_TileEnvelope(?, ?, ?), 4326) AS geom_4326,
                           ST_TileEnvelope(?, ?, ?) AS geom_3857
                ),
                mvtgeom AS (
                    SELECT 
                        ST_AsMVTGeom(
                            ST_Transform(cg.geom, 3857),
                            bbox.geom_3857,
                            4096,
                            64,
                            true
                        ) AS geom,
                        cg.cell_id,
                        cg.depth_m,
                        cg.sector,
                        dp.hsi_cakalang,
                        dp.hsi_madidihang,
                        dp.hsi_albakor,
                        dp.dom_zone,
                        dp.bycatch_traffic_light,
                        dp.sst_c,
                        dp.chla_mg_m3
                    FROM canonical_grids cg
                    CROSS JOIN bbox
                    JOIN daily_predictions dp ON cg.cell_id = dp.cell_id
                    WHERE dp.prediction_date = ?
                      AND dp.is_hotspot = true
                      AND cg.geom && bbox.geom_4326
                )
                SELECT ST_AsMVT(mvtgeom.*, 'hotspots', 4096, 'geom') AS mvt FROM mvtgeom
            ";
            $params = [$z, $x, $y, $z, $x, $y, $date];
        } else { // 'predictions' (default layer)
            $sql = "
                WITH bbox AS (
                    SELECT ST_Transform(ST_TileEnvelope(?, ?, ?), 4326) AS geom_4326,
                           ST_TileEnvelope(?, ?, ?) AS geom_3857
                ),
                mvtgeom AS (
                    SELECT 
                        ST_AsMVTGeom(
                            ST_Transform(cg.geom, 3857),
                            bbox.geom_3857,
                            4096,
                            64,
                            true
                        ) AS geom,
                        cg.cell_id,
                        cg.cell_class,
                        cg.depth_m,
                        cg.dist_port_nm,
                        cg.sector,
                        cg.is_shelf_break,
                        cg.is_prigi_inshore,
                        dp.hsi_cakalang,
                        dp.hsi_madidihang,
                        dp.hsi_albakor,
                        dp.is_hotspot,
                        dp.dom_zone,
                        dp.bycatch_traffic_light,
                        dp.sst_c,
                        dp.chla_mg_m3,
                        dp.wave_height_m,
                        dp.safety_advisory
                    FROM canonical_grids cg
                    CROSS JOIN bbox
                    JOIN daily_predictions dp ON cg.cell_id = dp.cell_id
                    WHERE dp.prediction_date = ?
                      AND cg.geom && bbox.geom_4326
                )
                SELECT ST_AsMVT(mvtgeom.*, 'predictions', 4096, 'geom') AS mvt FROM mvtgeom
            ";
            $params = [$z, $x, $y, $z, $x, $y, $date];
        }

        try {
            $row = DB::connection($connection)->selectOne($sql, $params);
            return (string)($row->mvt ?? '');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("MVT Query Error for {$layer}/{$z}/{$x}/{$y}: " . $e->getMessage());
            return '';
        }
    }
}

