<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CanonicalGrid;
use App\Models\DailyPrediction;

class PublicPageController extends Controller
{
    public function about()
    {
        $totalCells = CanonicalGrid::count() ?: 8129;
        $hotspotsCount = DailyPrediction::where('is_hotspot', true)->count() ?: 468;

        return view('public.about', [
            'totalCells' => $totalCells,
            'hotspotsCount' => $hotspotsCount,
            'fleetCount' => '2.450+',
            'portsCount' => 3,
        ]);
    }

    public function science()
    {
        $hotspotsCount = DailyPrediction::where('is_hotspot', true)->count() ?: 468;
        $avgTongkolHsi = round(DailyPrediction::avg('hsi_tongkol') ?? 0.62, 2);
        $avgCakalangHsi = round(DailyPrediction::avg('hsi_cakalang') ?? 0.58, 2);
        $avgMadidihangHsi = round(DailyPrediction::avg('hsi_madidihang') ?? 0.54, 2);

        return view('public.science', [
            'hotspotsCount' => $hotspotsCount,
            'avgTongkolHsi' => $avgTongkolHsi,
            'avgCakalangHsi' => $avgCakalangHsi,
            'avgMadidihangHsi' => $avgMadidihangHsi,
        ]);
    }

    public function documentation()
    {
        return view('public.documentation');
    }

    public function designSystem()
    {
        return view('public.design-system');
    }

    public function oceanData()
    {
        $totalCells = CanonicalGrid::count() ?: 8129;
        $avgSst = round(DailyPrediction::avg('sst_c') ?? 28.4, 2);
        $avgChla = round(DailyPrediction::avg('chla_mg_m3') ?? 0.18, 3);
        $avgWave = round(DailyPrediction::avg('wave_height_m') ?? 1.6, 2);
        $avgCurrent = round(DailyPrediction::avg('current_velocity_ms') ?? 0.28, 2);
        $latestDate = DailyPrediction::max('prediction_date') ?? now()->toDateString();

        return view('public.ocean-data', [
            'totalCells' => $totalCells,
            'avgSst' => $avgSst,
            'avgChla' => $avgChla,
            'avgWave' => $avgWave,
            'avgCurrent' => $avgCurrent,
            'latestDate' => $latestDate,
        ]);
    }

    public function platform()
    {
        return view('public.platform.index');
    }

    public function platformDataSources()
    {
        return view('public.platform.data-sources');
    }

    public function platformFeatures()
    {
        return view('public.platform.features');
    }

    public function scienceDom()
    {
        return view('public.science.dom');
    }

    public function scienceMethodology()
    {
        return view('public.science.methodology');
    }

    public function scienceModels()
    {
        return view('public.science.models');
    }
}

