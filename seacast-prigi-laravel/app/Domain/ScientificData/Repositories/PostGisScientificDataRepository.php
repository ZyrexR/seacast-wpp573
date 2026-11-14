<?php

namespace App\Domain\ScientificData\Repositories;

use Illuminate\Support\Facades\DB;
use App\Models\CanonicalGrid;
use App\Models\DailyPrediction;

class PostGisScientificDataRepository implements ScientificDataRepositoryInterface
{
    public const PRIGI_PORT_LON = 111.7275;
    public const PRIGI_PORT_LAT = -8.2833;

    protected string $connection = 'pgsql';

    public function getCanonicalGridCount(): int
    {
        return DB::connection($this->connection)
            ->table('canonical_grids')
            ->count();
    }

    public function getGridsInBoundingBox(float $minLon, float $minLat, float $maxLon, float $maxLat): array
    {
        return DB::connection($this->connection)
            ->select("
                SELECT cell_id, cell_class, center_lon, center_lat, depth_m, dist_port_km, dist_port_nm,
                       is_shelf_break, is_prigi_inshore, is_prigi_offshore, sector,
                       ST_AsGeoJSON(geom) as geojson_geom
                FROM canonical_grids
                WHERE geom && ST_MakeEnvelope(?, ?, ?, ?, 4326)
                ORDER BY cell_id
            ", [$minLon, $minLat, $maxLon, $maxLat]);
    }

    public function getDailySummary(string $date): ?array
    {
        $raw = DB::connection($this->connection)
            ->selectOne("
                SELECT 
                    COUNT(*) as total_cells,
                    COUNT(CASE WHEN is_hotspot = true THEN 1 END) as hotspots_count,
                    ROUND(AVG(CASE WHEN safety_advisory = 'AMAN_MELAUT' THEN 100.0 ELSE 0.0 END)::numeric, 1) as safe_pct,
                    ROUND(AVG(sst_c)::numeric, 2) as mean_sst,
                    ROUND(AVG(chla_mg_m3)::numeric, 3) as mean_chla,
                    ROUND(AVG(wave_height_m)::numeric, 2) as mean_wave_m,
                    ROUND(AVG(hsi_cakalang)::numeric, 3) as cakalang_mean_hsi,
                    ROUND(AVG(hsi_madidihang)::numeric, 3) as madidihang_mean_hsi,
                    ROUND(AVG(hsi_tongkol)::numeric, 3) as tongkol_mean_hsi,
                    COUNT(CASE WHEN bycatch_traffic_light = 'GREEN' THEN 1 END) as green_count,
                    ROUND(COUNT(CASE WHEN bycatch_traffic_light = 'GREEN' THEN 1 END) * 100.0 / NULLIF(COUNT(*), 0), 1) as green_pct,
                    COUNT(CASE WHEN bycatch_traffic_light = 'YELLOW' THEN 1 END) as yellow_count,
                    ROUND(COUNT(CASE WHEN bycatch_traffic_light = 'YELLOW' THEN 1 END) * 100.0 / NULLIF(COUNT(*), 0), 1) as yellow_pct,
                    COUNT(CASE WHEN bycatch_traffic_light = 'RED' THEN 1 END) as red_count,
                    ROUND(COUNT(CASE WHEN bycatch_traffic_light = 'RED' THEN 1 END) * 100.0 / NULLIF(COUNT(*), 0), 1) as red_pct
                FROM daily_predictions
                WHERE prediction_date = ?
            ", [$date]);

        if (!$raw || $raw->total_cells == 0) {
            return null;
        }

        $speciesAverages = [
            'Cakalang' => (float)$raw->cakalang_mean_hsi,
            'Tuna Sirip Kuning' => (float)$raw->madidihang_mean_hsi,
            'Tongkol Krai' => (float)$raw->tongkol_mean_hsi,
        ];
        arsort($speciesAverages);
        $highestSpecies = key($speciesAverages);
        $highestScore = current($speciesAverages);

        return [
            'date' => $date,
            'summary' => [
                'total_cells' => (int)$raw->total_cells,
                'hotspots_count' => (int)$raw->hotspots_count,
                'safe_pct' => (float)$raw->safe_pct,
                'mean_sst' => (float)$raw->mean_sst,
                'mean_chla' => (float)$raw->mean_chla,
                'mean_wave_m' => (float)$raw->mean_wave_m,
                'species' => [
                    'cakalang_mean_hsi' => (float)$raw->cakalang_mean_hsi,
                    'madidihang_mean_hsi' => (float)$raw->madidihang_mean_hsi,
                    'tongkol_mean_hsi' => (float)$raw->tongkol_mean_hsi,
                    'highest_species' => $highestSpecies,
                    'highest_score' => (float)$highestScore,
                ],
                'bycatch' => [
                    'green_count' => (int)$raw->green_count,
                    'green_pct' => (float)$raw->green_pct,
                    'yellow_count' => (int)$raw->yellow_count,
                    'yellow_pct' => (float)$raw->yellow_pct,
                    'red_count' => (int)$raw->red_count,
                    'red_pct' => (float)$raw->red_pct,
                ],
                'sources' => [
                    'sdm' => 'Ensemble SDM (MaxEnt, Random Forest, GLM)',
                    'ocean' => 'NOAA CoralTemp OISST & Sentinel-3 Chl-a',
                    'wave' => 'Simulasi Oseanografi Musiman BMKG Terkalibrasi (Fallback Lokal)',
                    'bycatch' => 'Screening Lereng Batimetri 150-1200m Standar IOTC',
                ],
            ],
        ];
    }

    public function getDailyGeoJsonFeatures(string $date, array $filters = []): array
    {
        $query = "
            SELECT 
                cg.cell_id, cg.cell_class, cg.center_lon, cg.center_lat, 
                cg.depth_m, cg.dist_port_km, cg.dist_port_nm,
                cg.is_shelf_break, cg.is_prigi_inshore, cg.is_prigi_offshore, cg.sector,
                dp.prediction_date, dp.hsi_cakalang, dp.hsi_madidihang, dp.hsi_tongkol,
                dp.is_hotspot, dp.dom_zone, dp.bycatch_traffic_light,
                dp.sst_c, dp.chla_mg_m3, dp.current_velocity_ms, dp.wave_height_m, dp.wind_speed_ms,
                dp.safety_advisory,
                ST_AsGeoJSON(cg.geom) as geojson_geom
            FROM daily_predictions dp
            JOIN canonical_grids cg ON dp.cell_id = cg.cell_id
            WHERE dp.prediction_date = ?
        ";

        $bindings = [$date];

        if (!empty($filters['sector']) && $filters['sector'] !== 'ALL') {
            $query .= " AND cg.sector = ?";
            $bindings[] = $filters['sector'];
        }

        if (!empty($filters['dom_zone'])) {
            $query .= " AND dp.dom_zone = ?";
            $bindings[] = $filters['dom_zone'];
        }

        if (!empty($filters['bycatch'])) {
            $query .= " AND dp.bycatch_traffic_light = ?";
            $bindings[] = $filters['bycatch'];
        }

        if (!empty($filters['hotspot_only'])) {
            $query .= " AND dp.is_hotspot = true";
        }

        if (!empty($filters['species']) && isset($filters['min_hsi'])) {
            $col = match (strtolower(str_replace('-', '_', $filters['species']))) {
                'cakalang' => 'dp.hsi_cakalang',
                'tuna_sirip_kuning' => 'dp.hsi_madidihang',
                'madidihang' => 'dp.hsi_madidihang',
                'tongkol_krai' => 'dp.hsi_tongkol',
                'tongkol' => 'dp.hsi_tongkol',
                default => null,
            };
            if ($col) {
                $query .= " AND {$col} >= ?";
                $bindings[] = (float)$filters['min_hsi'];
            }
        }

        if (!empty($filters['bbox']) && is_array($filters['bbox']) && count($filters['bbox']) === 4) {
            $query .= " AND cg.geom && ST_MakeEnvelope(?, ?, ?, ?, 4326)";
            $bindings[] = (float)$filters['bbox'][0];
            $bindings[] = (float)$filters['bbox'][1];
            $bindings[] = (float)$filters['bbox'][2];
            $bindings[] = (float)$filters['bbox'][3];
        }

        $query .= " ORDER BY cg.cell_id";

        if (!empty($filters['limit'])) {
            $query .= " LIMIT " . (int)$filters['limit'];
        }

        $rows = DB::connection($this->connection)->select($query, $bindings);

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
                    'prediction_date' => $row->prediction_date,
                    'hsi_cakalang' => (float)$row->hsi_cakalang,
                    'hsi_madidihang' => (float)$row->hsi_madidihang,
                    'hsi_tongkol' => (float)($row->hsi_tongkol ?? 0.0),
                    'waypoint_dms' => sprintf(
                        "%02d°%02d'%02d\"S, %03d°%02d'%02d\"E",
                        floor(abs((float)$row->center_lat)),
                        floor((abs((float)$row->center_lat) - floor(abs((float)$row->center_lat))) * 60),
                        round(((abs((float)$row->center_lat) - floor(abs((float)$row->center_lat))) * 60 - floor((abs((float)$row->center_lat) - floor(abs((float)$row->center_lat))) * 60)) * 60),
                        floor((float)$row->center_lon),
                        floor(((float)$row->center_lon - floor((float)$row->center_lon)) * 60),
                        round((((float)$row->center_lon - floor((float)$row->center_lon)) * 60 - floor(((float)$row->center_lon - floor((float)$row->center_lon)) * 60)) * 60)
                    ),
                    'is_within_12nm' => ((float)$row->dist_port_nm <= 12.0),
                    'jalur_penangkapan' => ((float)$row->dist_port_nm <= 4.0 ? 'Jalur I (0-4 NM)' : ((float)$row->dist_port_nm <= 12.0 ? 'Jalur II (4-12 NM)' : 'Jalur III (>12 NM / ZEE)')),
                    'is_hotspot' => (bool)$row->is_hotspot,
                    'dom_zone' => $row->dom_zone,
                    'bycatch_traffic_light' => $row->bycatch_traffic_light,
                    'sst_c' => (float)$row->sst_c,
                    'chla_mg_m3' => (float)$row->chla_mg_m3,
                    'current_velocity_ms' => (float)$row->current_velocity_ms,
                    'wave_height_m' => (float)$row->wave_height_m,
                    'wind_speed_ms' => (float)$row->wind_speed_ms,
                    'safety_advisory' => $row->safety_advisory,
                ],
                'geometry' => json_decode($row->geojson_geom, true),
            ];
        }

        return $features;
    }

    public function calculateGeodesicDistanceNm(float $lon, float $lat): float
    {
        $res = DB::connection($this->connection)->selectOne("
            SELECT ST_Distance(
                ST_SetSRID(ST_Point(?, ?), 4326)::geography,
                ST_SetSRID(ST_Point(?, ?), 4326)::geography
            ) / 1852.0 as dist_nm
        ", [$lon, $lat, self::PRIGI_PORT_LON, self::PRIGI_PORT_LAT]);

        return (float)($res->dist_nm ?? 0.0);
    }

    public function getMonthlyPredictions(string $yearMonth, array $filters = []): array
    {
        $query = "SELECT * FROM grid_monthly_predictions WHERE year_month = ?";
        $bindings = [$yearMonth];

        if (!empty($filters['cell_id'])) {
            $query .= " AND cell_id = ?";
            $bindings[] = $filters['cell_id'];
        }

        if (!empty($filters['limit'])) {
            $query .= " LIMIT " . (int)$filters['limit'];
        }

        return DB::connection($this->connection)->select($query, $bindings);
    }

    public function getLandings(?int $year = null): array
    {
        $query = "SELECT * FROM prigi_port_landings";
        $bindings = [];

        if ($year !== null) {
            $query .= " WHERE year = ?";
            $bindings[] = $year;
        }

        $query .= " ORDER BY year ASC, month ASC";

        return DB::connection($this->connection)->select($query, $bindings);
    }

    public function getFleetAggregation(): array
    {
        return DB::connection($this->connection)
            ->table('prigi_fleet_by_gt')
            ->orderBy('max_range_nm', 'asc')
            ->get()
            ->toArray();
    }
}
