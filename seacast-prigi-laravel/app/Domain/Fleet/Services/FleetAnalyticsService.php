<?php

namespace App\Domain\Fleet\Services;

use App\Models\PrigiFleetByGt;
use App\Models\PrigiPortLanding;
use Illuminate\Support\Facades\DB;

class FleetAnalyticsService
{
    /**
     * Get registered fleet composition by GT class.
     */
    public function getFleetComposition(): array
    {
        $fleet = PrigiFleetByGt::orderBy('max_range_nm', 'asc')->get();
        $totalVessels = $fleet->sum('vessel_count');

        return [
            'total_vessels' => (int)$totalVessels,
            'classes_count' => $fleet->count(),
            'registration_year' => 2026,
            'classes' => $fleet->map(function ($row) {
                return [
                    'gt_class' => $row->gt_class,
                    'vessel_count' => (int)$row->vessel_count,
                    'pct_fleet' => (float)$row->pct_fleet,
                    'max_range_nm' => (float)$row->max_range_nm,
                    'recommended_dom_zones' => $row->recommended_dom_zones,
                ];
            })->toArray(),
        ];
    }

    /**
     * Get multi-year annual landing summaries (2015 - 2025).
     */
    public function getAnnualLandingsSummary(): array
    {
        $raw = DB::connection('pgsql')
            ->table('prigi_port_landings')
            ->selectRaw("
                year,
                COUNT(*) as months_recorded,
                ROUND(SUM(cakalang_tons)::numeric, 2) as cakalang_tons,
                ROUND(SUM(madidihang_tons)::numeric, 2) as madidihang_tons,
                ROUND(SUM(tongkol_tons)::numeric, 2) as tongkol_tons,
                ROUND(SUM(total_pelagic_tons)::numeric, 2) as total_pelagic_tons,
                SUM(trips_count) as total_trips,
                ROUND(AVG(cpue_proxy)::numeric, 2) as avg_cpue_kg_trip,
                ROUND(AVG(mean_sst)::numeric, 2) as avg_sst,
                ROUND(AVG(mean_chla)::numeric, 3) as avg_chla
            ")
            ->groupBy('year')
            ->orderBy('year', 'asc')
            ->get();

        $overallTotalTons = (float)PrigiPortLanding::sum('total_pelagic_tons');
        $overallTotalTrips = (int)PrigiPortLanding::sum('trips_count');
        $totalMonths = (int)PrigiPortLanding::count();

        return [
            'overall_total_tons' => round($overallTotalTons, 2),
            'overall_total_trips' => $overallTotalTrips,
            'total_months_recorded' => $totalMonths,
            'years' => $raw->toArray(),
        ];
    }

    /**
     * Calculate 11-year monthly seasonality (January to December).
     */
    public function getMonthlySeasonality(): array
    {
        $raw = DB::connection('pgsql')
            ->table('prigi_port_landings')
            ->selectRaw("
                month,
                ROUND(AVG(total_pelagic_tons)::numeric, 2) as avg_tons,
                ROUND(AVG(cakalang_tons)::numeric, 2) as avg_cakalang,
                ROUND(AVG(madidihang_tons)::numeric, 2) as avg_madidihang,
                ROUND(AVG(tongkol_tons)::numeric, 2) as avg_tongkol,
                ROUND(AVG(cpue_proxy)::numeric, 2) as avg_cpue,
                ROUND(AVG(mean_sst)::numeric, 2) as avg_sst,
                ROUND(AVG(mean_chla)::numeric, 3) as avg_chla
            ")
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return $raw->map(function ($row) use ($monthNames) {
            $m = (int)$row->month;
            return [
                'month_num' => $m,
                'month_name' => $monthNames[$m] ?? "Bulan {$m}",
                'avg_tons' => (float)$row->avg_tons,
                'avg_cakalang' => (float)$row->avg_cakalang,
                'avg_madidihang' => (float)$row->avg_madidihang,
                'avg_tongkol' => (float)$row->avg_tongkol,
                'avg_cpue' => (float)$row->avg_cpue,
                'avg_sst' => (float)$row->avg_sst,
                'avg_chla' => (float)$row->avg_chla,
            ];
        })->toArray();
    }

    /**
     * Get recent monthly landings records.
     */
    public function getRecentMonthlyLandings(int $limit = 24): array
    {
        return PrigiPortLanding::orderBy('year_month', 'desc')
            ->take($limit)
            ->get()
            ->toArray();
    }

    /**
     * Mandatory Scientific CPUE and Data Limitations Disclosure.
     */
    public function getScientificCaveats(): array
    {
        return [
            'standardized_cpue' => false,
            'disclaimer_title' => 'Keterbatasan Ilmiah: CPUE Nominal vs. Standardized CPUE',
            'statements' => [
                'Metrik CPUE yang disajikan adalah CPUE Proxy Nominal (total pendaratan per trip tercatat di TPI PPN Prigi), bukan Standardized CPUE.',
                'Data logbook penangkapan ikan individual per fishing ground dan spesifikasi alat tangkap (purse seine vs pancing ulur) tidak tersedia secara spasial mikro.',
                'Profil armada 1.269 kapal terbagi dalam 3 kelas agregasi GT (<10 GT, 10-20 GT, 20-30 GT) dan bukan merupakan tracking individu kapal real-time AIS/VMS.',
                'Indikator produktivitas nominal tidak boleh diekstrapolasikan ke estimasi biomassa stok tanpa pemodelan surplus produksi formal standar WCPFC/IOTC.'
            ],
        ];
    }
}

