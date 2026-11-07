<?php

namespace App\Domain\ScientificData\Services;

use Illuminate\Support\Facades\DB;
use App\Models\GridMonthlyPrediction;

class TimeMachineService
{
    /**
     * Get monthly aggregates across all 12 months for 2025.
     */
    public function getMonthlySummaries(): array
    {
        $sql = "
            SELECT 
                year_month,
                year,
                month,
                COUNT(*) as total_cells,
                ROUND(AVG(hsi_cakalang)::numeric, 3) as avg_hsi_cakalang,
                ROUND(AVG(hsi_madidihang)::numeric, 3) as avg_hsi_madidihang,
                ROUND(AVG(hsi_albakor)::numeric, 3) as avg_hsi_albakor,
                ROUND(AVG(sst)::numeric, 2) as avg_sst,
                ROUND(AVG(chla)::numeric, 3) as avg_chla,
                ROUND(AVG(current_velocity)::numeric, 3) as avg_current,
                COUNT(*) FILTER (WHERE hsi_cakalang >= 0.70) as hotspot_cells
            FROM grid_monthly_predictions
            GROUP BY year_month, year, month
            ORDER BY year ASC, month ASC
        ";

        return DB::connection('pgsql')->select($sql);
    }

    /**
     * Get 12-month temporal trajectory for a specific canonical grid cell.
     */
    public function getCellHistory(string $cellId): array
    {
        $sql = "
            SELECT 
                year_month,
                year,
                month,
                ROUND(hsi_cakalang::numeric, 3) as hsi_cakalang,
                ROUND(hsi_madidihang::numeric, 3) as hsi_madidihang,
                ROUND(hsi_albakor::numeric, 3) as hsi_albakor,
                dom_zone,
                ROUND(sst::numeric, 2) as sst,
                ROUND(chla::numeric, 3) as chla,
                ROUND(current_velocity::numeric, 3) as current_velocity,
                ROUND(wind_speed::numeric, 2) as wind_speed
            FROM grid_monthly_predictions
            WHERE cell_id = :cell_id
            ORDER BY year ASC, month ASC
        ";

        return DB::connection('pgsql')->select($sql, ['cell_id' => $cellId]);
    }

    /**
     * Get spatial grid predictions for a specific year_month snapshot.
     */
    public function getYearMonthCells(string $yearMonth, int $limit = 8129): array
    {
        $sql = "
            SELECT 
                gmp.year_month,
                gmp.cell_id,
                ROUND(gmp.hsi_cakalang::numeric, 3) as hsi_cakalang,
                ROUND(gmp.hsi_madidihang::numeric, 3) as hsi_madidihang,
                ROUND(gmp.hsi_albakor::numeric, 3) as hsi_albakor,
                gmp.dom_zone,
                ROUND(gmp.sst::numeric, 2) as sst,
                ROUND(gmp.chla::numeric, 3) as chla,
                ROUND(gmp.current_velocity::numeric, 3) as current_velocity,
                ROUND(gmp.wind_speed::numeric, 2) as wind_speed,
                cg.center_lon,
                cg.center_lat,
                cg.dist_port_nm,
                cg.sector
            FROM grid_monthly_predictions gmp
            JOIN canonical_grids cg ON gmp.cell_id = cg.cell_id
            WHERE gmp.year_month = :ym
            ORDER BY cg.dist_port_nm ASC
            LIMIT :limit
        ";

        return DB::connection('pgsql')->select($sql, [
            'ym' => $yearMonth,
            'limit' => $limit
        ]);
    }
}

