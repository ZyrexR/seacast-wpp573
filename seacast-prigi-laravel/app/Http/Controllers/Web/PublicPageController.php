<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CanonicalGrid;
use App\Models\PrigiPortLanding;
use App\Models\PrigiFleetByGt;

class PublicPageController extends Controller
{
    public function about()
    {
        $fleet = PrigiFleetByGt::all();
        $totalTons = PrigiPortLanding::sum('total_pelagic_tons');
        $totalTrips = PrigiPortLanding::sum('trips_count');
        $avgCpue = $totalTrips > 0 ? round(($totalTons * 1000) / $totalTrips, 2) : 110.06;

        return view('public.about', [
            'fleet' => $fleet,
            'totalTons' => round($totalTons, 1),
            'totalTrips' => $totalTrips,
            'avgCpue' => $avgCpue,
        ]);
    }

    public function science()
    {
        return view('public.science');
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
        return view('public.ocean-data');
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

