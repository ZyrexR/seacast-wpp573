<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Domain\Fleet\Services\FleetAnalyticsService;
use Illuminate\View\View;

class FleetAnalyticsController extends Controller
{
    public function __construct(
        protected FleetAnalyticsService $fleetService
    ) {}

    public function index(): View
    {
        $fleet = $this->fleetService->getFleetComposition();
        $annual = $this->fleetService->getAnnualLandingsSummary();
        $seasonality = $this->fleetService->getMonthlySeasonality();
        $recent = $this->fleetService->getRecentMonthlyLandings(12);
        $caveats = $this->fleetService->getScientificCaveats();

        return view('dashboard.fleet-analytics', [
            'fleet' => $fleet,
            'annual' => $annual,
            'seasonality' => $seasonality,
            'recent' => $recent,
            'caveats' => $caveats,
        ]);
    }
}

