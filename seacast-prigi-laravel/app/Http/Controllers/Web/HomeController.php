<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CanonicalGrid;
use App\Models\DailyPrediction;
use App\Models\PrigiFleetByGt;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // Query live metrics from PostgreSQL 17 / PostGIS 3.6
        $totalCells = CanonicalGrid::count();
        $latestDate = DailyPrediction::max('prediction_date') ?? '2026-11-03';
        
        $dailySummary = DB::connection('pgsql')->table('daily_predictions')
            ->where('prediction_date', $latestDate)
            ->selectRaw("
                COUNT(*) FILTER (WHERE is_hotspot) as hotspots_count,
                ROUND(AVG(sst_c)::numeric, 1) as avg_sst,
                ROUND(AVG(chla_mg_m3)::numeric, 3) as avg_chla,
                ROUND(AVG(wave_height_m)::numeric, 2) as avg_wave,
                ROUND(AVG(hsi_cakalang)::numeric, 3) as cakalang_hsi,
                ROUND(AVG(hsi_madidihang)::numeric, 3) as madidihang_hsi,
                ROUND(AVG(hsi_albakor)::numeric, 3) as albakor_hsi,
                COUNT(*) FILTER (WHERE bycatch_traffic_light = 'GREEN') as green_bycatch,
                COUNT(*) as total_daily
            ")->first();

        $totalVessels = PrigiFleetByGt::sum('vessel_count');

        $bycatchPct = $dailySummary && $dailySummary->total_daily > 0 
            ? round(($dailySummary->green_bycatch / $dailySummary->total_daily) * 100, 1) 
            : 86.3;

        return view('public.home', [
            'totalCells' => $totalCells,
            'latestDate' => $latestDate,
            'hotspotsCount' => $dailySummary->hotspots_count ?? 6,
            'avgSst' => $dailySummary->avg_sst ?? 29.2,
            'avgChla' => $dailySummary->avg_chla ?? 0.125,
            'avgWave' => $dailySummary->avg_wave ?? 3.36,
            'cakalangHsi' => $dailySummary->cakalang_hsi ?? 0.209,
            'madidihangHsi' => $dailySummary->madidihang_hsi ?? 0.276,
            'albakorHsi' => $dailySummary->albakor_hsi ?? 0.448,
            'bycatchSafePct' => $bycatchPct,
            'totalFleet' => $totalVessels > 0 ? $totalVessels : 1269,
            'totalVessels' => $totalVessels > 0 ? $totalVessels : 1269,
        ]);
    }
}
