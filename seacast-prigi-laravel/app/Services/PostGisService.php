<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\CanonicalGrid;

/**
 * Service to execute high-performance PostGIS operations with spatial GiST indexing
 */
class PostGisService
{
    /**
     * Query canonical grid cells inside bounding box using GiST index
     */
    public function getCellsInBoundingBox(float $minLon, float $minLat, float $maxLon, float $maxLat, ?int $limit = null): array
    {
        $query = DB::connection('pgsql')
            ->table('canonical_grids')
            ->select(
                'cell_id',
                'cell_class',
                'center_lon',
                'center_lat',
                'depth_m',
                'dist_port_km',
                'dist_port_nm',
                'is_shelf_break',
                'is_prigi_inshore',
                'is_prigi_offshore',
                'sector',
                DB::raw('ST_AsGeoJSON(geom) as geojson')
            )
            ->whereRaw("geom && ST_MakeEnvelope(?, ?, ?, ?, 4326)", [$minLon, $minLat, $maxLon, $maxLat]);

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->toArray();
    }

    /**
     * Get bounding box extent of canonical grids
     */
    public function getGridExtent(): array
    {
        $res = DB::connection('pgsql')->selectOne("
            SELECT 
                ST_XMin(ST_Extent(geom)) as min_lon,
                ST_YMin(ST_Extent(geom)) as min_lat,
                ST_XMax(ST_Extent(geom)) as max_lon,
                ST_YMax(ST_Extent(geom)) as max_lat
            FROM canonical_grids
        ");

        return [
            'min_lon' => (float) ($res->min_lon ?? 102.5),
            'min_lat' => (float) ($res->min_lat ?? -14.0),
            'max_lon' => (float) ($res->max_lon ?? 126.5),
            'max_lat' => (float) ($res->max_lat ?? -6.8),
        ];
    }

    /**
     * Build GeoJSON FeatureCollection from grid cells with latest daily prediction
     */
    public function getDailyPredictionGeoJson(string $date, ?string $sector = null, ?int $limit = null): array
    {
        $query = DB::connection('pgsql')
            ->table('canonical_grids as cg')
            ->join('daily_predictions as dp', function ($join) use ($date) {
                $join->on('cg.cell_id', '=', 'dp.cell_id')
                     ->where('dp.prediction_date', '=', $date);
            })
            ->select(
                'cg.cell_id',
                'cg.cell_class',
                'cg.center_lon',
                'cg.center_lat',
                'cg.depth_m',
                'cg.dist_port_km',
                'cg.dist_port_nm',
                'cg.is_shelf_break',
                'cg.is_prigi_inshore',
                'cg.is_prigi_offshore',
                'cg.sector',
                'dp.hsi_cakalang',
                'dp.hsi_madidihang',
                'dp.hsi_tongkol',
                'dp.is_hotspot',
                'dp.dom_zone',
                'dp.bycatch_traffic_light',
                'dp.sst_c',
                'dp.chla_mg_m3',
                'dp.wave_height_m',
                'dp.safety_advisory',
                DB::raw('ST_AsGeoJSON(cg.geom) as geojson')
            );

        if ($sector && $sector !== 'ALL') {
            $query->where('cg.sector', $sector);
        }

        if ($limit) {
            $query->limit($limit);
        }

        $rows = $query->get();

        $features = [];
        foreach ($rows as $row) {
            $features[] = [
                'type' => 'Feature',
                'geometry' => json_decode($row->geojson, true),
                'properties' => [
                    'cell_id' => $row->cell_id,
                    'cell_class' => $row->cell_class,
                    'center_lon' => (float) $row->center_lon,
                    'center_lat' => (float) $row->center_lat,
                    'depth_m' => (float) $row->depth_m,
                    'dist_port_km' => (float) $row->dist_port_km,
                    'dist_port_nm' => (float) $row->dist_port_nm,
                    'is_shelf_break' => (bool) $row->is_shelf_break,
                    'is_prigi_inshore' => (bool) $row->is_prigi_inshore,
                    'is_prigi_offshore' => (bool) $row->is_prigi_offshore,
                    'sector' => $row->sector,
                    'hsi_cakalang' => (float) $row->hsi_cakalang,
                    'hsi_madidihang' => (float) $row->hsi_madidihang,
                    'hsi_tongkol' => (float) $row->hsi_tongkol,
                    'is_hotspot' => (bool) $row->is_hotspot,
                    'dom_zone' => $row->dom_zone,
                    'bycatch_traffic_light' => $row->bycatch_traffic_light,
                    'sst_c' => (float) $row->sst_c,
                    'chla_mg_m3' => (float) $row->chla_mg_m3,
                    'wave_height_m' => (float) $row->wave_height_m,
                    'safety_advisory' => $row->safety_advisory,
                ]
            ];
        }

        return [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
    }
}

