<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Service to execute live, read-only health checks on PostgreSQL 17.11 & PostGIS 3.6.2
 */
class DatabaseHealthService
{
    /**
     * Run full diagnostics and return structured health status
     */
    public function checkHealth(): array
    {
        $startTime = microtime(true);
        $status = 'healthy';
        $issues = [];

        try {
            // 1. Connection & Version Check
            $pgVersion = DB::connection('pgsql')->selectOne("SELECT version() as ver")->ver;
            
            // 2. PostGIS Extension Check
            $postgisRow = DB::connection('pgsql')->selectOne("SELECT PostGIS_Full_Version() as ver");
            $postgisVersion = $postgisRow ? $postgisRow->ver : null;
            if (!$postgisVersion) {
                $status = 'degraded';
                $issues[] = 'PostGIS extension not detected';
            }

            // 3. Canonical Grids Count, SRID, Validity
            $gridStats = DB::connection('pgsql')->selectOne("
                SELECT 
                    COUNT(*) as total_cells,
                    COUNT(*) FILTER (WHERE ST_IsValid(geom)) as valid_cells,
                    COUNT(*) FILTER (WHERE NOT ST_IsValid(geom)) as invalid_cells,
                    ST_SRID(geom) as srid,
                    GeometryType(geom) as geom_type
                FROM canonical_grids
                GROUP BY ST_SRID(geom), GeometryType(geom)
            ");

            $totalGrids = (int) ($gridStats->total_cells ?? 0);
            $validGrids = (int) ($gridStats->valid_cells ?? 0);
            $invalidGrids = (int) ($gridStats->invalid_cells ?? 0);
            $srid = (int) ($gridStats->srid ?? 0);

            if ($totalGrids !== 8129) {
                $status = 'degraded';
                $issues[] = "Canonical grids count mismatch: expected 8129, found {$totalGrids}";
            }

            if ($invalidGrids > 0) {
                $status = 'degraded';
                $issues[] = "Found {$invalidGrids} invalid geometries in canonical_grids";
            }

            if ($srid !== 4326) {
                $status = 'degraded';
                $issues[] = "SRID mismatch: expected 4326, found {$srid}";
            }

            // 4. Daily Predictions
            $dailyCount = DB::connection('pgsql')->table('daily_predictions')->count();
            $latestDailyDate = DB::connection('pgsql')->table('daily_predictions')->max('prediction_date');

            // 5. Monthly Predictions
            $monthlyCount = DB::connection('pgsql')->table('grid_monthly_predictions')->count();

            // 6. Port Landings
            $landingsCount = DB::connection('pgsql')->table('prigi_port_landings')->count();

            // 7. Fleet Aggregation
            $fleetCount = DB::connection('pgsql')->table('prigi_fleet_by_gt')->count();
            $totalVessels = DB::connection('pgsql')->table('prigi_fleet_by_gt')->sum('vessel_count');

            // 8. GiST Index Verification
            $gistIndex = DB::connection('pgsql')->selectOne("
                SELECT indexname FROM pg_indexes 
                WHERE tablename = 'canonical_grids' AND indexname = 'idx_canonical_grids_geom'
            ");

            $latencyMs = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'status' => empty($issues) ? 'healthy' : $status,
                'latency_ms' => $latencyMs,
                'timestamp' => now()->toIso8601String(),
                'engine' => [
                    'postgresql_version' => $pgVersion,
                    'postgis_version' => $postgisVersion,
                    'postgis_available' => !empty($postgisVersion),
                ],
                'spatial_baseline' => [
                    'canonical_grids_count' => $totalGrids,
                    'valid_geometries' => $validGrids,
                    'invalid_geometries' => $invalidGrids,
                    'srid' => $srid,
                    'geometry_type' => $gridStats->geom_type ?? 'POLYGON',
                    'gist_index_active' => !empty($gistIndex),
                ],
                'data_records' => [
                    'daily_predictions_count' => $dailyCount,
                    'latest_prediction_date' => $latestDailyDate,
                    'grid_monthly_predictions_count' => $monthlyCount,
                    'prigi_port_landings_count' => $landingsCount,
                    'prigi_fleet_rows_count' => $fleetCount,
                    'total_registered_vessels' => (int) $totalVessels,
                ],
                'issues' => $issues,
            ];

        } catch (Exception $e) {
            return [
                'status' => 'unavailable',
                'latency_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'timestamp' => now()->toIso8601String(),
                'error' => $e->getMessage(),
                'issues' => array_merge($issues, [$e->getMessage()]),
            ];
        }
    }
}

