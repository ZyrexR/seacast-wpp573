<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\GeospatialCacheManager;

class GridApiController extends Controller
{
    public function __construct(
        protected GeospatialCacheManager $cacheManager
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sector' => 'nullable|string',
            'bbox' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:10000',
        ]);

        $filters = [];
        if (!empty($validated['sector']) && $validated['sector'] !== 'ALL') {
            $filters['sector'] = $validated['sector'];
        }
        if (!empty($validated['limit'])) {
            $filters['limit'] = (int)$validated['limit'];
        }
        if (!empty($validated['bbox'])) {
            $parts = array_map('floatval', explode(',', $validated['bbox']));
            if (count($parts) === 4) {
                $filters['bbox'] = $parts;
            }
        }

        $cacheKey = $this->cacheManager->buildGridKey($filters);
        $cached = $this->cacheManager->get($cacheKey);

        if ($cached !== null) {
            $etag = $cached['etag'];
            if ($request->header('If-None-Match') === $etag) {
                return response()->json(null, 304, [
                    'ETag' => $etag,
                    'Cache-Control' => 'public, max-age=3600, stale-while-revalidate=86400',
                    'X-Cache' => 'HIT-REVALIDATED',
                ]);
            }

            return response()->json($cached['data'])
                ->header('ETag', $etag)
                ->header('X-Cache', 'HIT')
                ->header('Cache-Control', 'public, max-age=3600, stale-while-revalidate=86400');
        }

        $query = "
            SELECT cell_id, cell_class, center_lon, center_lat, depth_m, 
                   dist_port_km, dist_port_nm, is_shelf_break, is_prigi_inshore, 
                   is_prigi_offshore, sector, ST_AsGeoJSON(geom) as geojson_geom
            FROM canonical_grids
            WHERE 1=1
        ";
        $bindings = [];

        if (!empty($filters['sector'])) {
            $query .= " AND sector = ?";
            $bindings[] = $filters['sector'];
        }

        if (!empty($filters['bbox'])) {
            $query .= " AND geom && ST_MakeEnvelope(?, ?, ?, ?, 4326)";
            $bindings[] = $filters['bbox'][0];
            $bindings[] = $filters['bbox'][1];
            $bindings[] = $filters['bbox'][2];
            $bindings[] = $filters['bbox'][3];
        }

        $query .= " ORDER BY cell_id";

        if (!empty($filters['limit'])) {
            $query .= " LIMIT " . (int)$filters['limit'];
        }

        $rows = DB::connection('pgsql')->select($query, $bindings);

        $features = [];
        foreach ($rows as $row) {
            $features[] = [
                'type' => 'Feature',
                'properties' => [
                    'cell_id' => $row->cell_id,
                    'cell_class' => $row->cell_class,
                    'center_lon' => (float)$row->center_lon,
                    'center_lat' => (float)$row->center_lat,
                    'depth_m' => (float)$row->depth_m,
                    'dist_port_km' => (float)$row->dist_port_km,
                    'dist_port_nm' => (float)$row->dist_port_nm,
                    'is_shelf_break' => (bool)$row->is_shelf_break,
                    'is_prigi_inshore' => (bool)$row->is_prigi_inshore,
                    'is_prigi_offshore' => (bool)$row->is_prigi_offshore,
                    'sector' => $row->sector,
                ],
                'geometry' => json_decode($row->geojson_geom, true),
            ];
        }

        $geoJson = [
            'type' => 'FeatureCollection',
            'crs' => [
                'type' => 'name',
                'properties' => [
                    'name' => 'urn:ogc:def:crs:OGC:1.3:CRS84',
                ],
            ],
            'features' => $features,
        ];

        $this->cacheManager->put(
            $cacheKey,
            $geoJson,
            GeospatialCacheManager::DEFAULT_GRID_TTL,
            ['domain:canonical_grids']
        );
        $etag = $this->cacheManager->generateEtag($geoJson);

        return response()->json($geoJson)
            ->header('ETag', $etag)
            ->header('X-Cache', 'MISS')
            ->header('Cache-Control', 'public, max-age=3600, stale-while-revalidate=86400');
    }
}
