<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Domain\Fleet\Services\FleetAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FleetAnalyticsController extends Controller
{
    public function __construct(
        protected FleetAnalyticsService $fleetService
    ) {}

    public function index(Request $request): View
    {
        $selectedPort = strtoupper($request->query('port', 'PPN_PRIGI'));
        $availablePorts = $this->fleetService->getAvailablePorts();

        if (!isset($availablePorts[$selectedPort])) {
            $selectedPort = 'PPN_PRIGI';
        }

        $currentPortMeta = $availablePorts[$selectedPort];
        $fleet = $this->fleetService->getFleetComposition($selectedPort);
        $annual = $this->fleetService->getAnnualLandingsSummary($selectedPort);
        $seasonality = $this->fleetService->getMonthlySeasonality($selectedPort);
        $recent = $this->fleetService->getRecentMonthlyLandings(12, $selectedPort);
        $caveats = $this->fleetService->getScientificCaveats();

        return view('dashboard.fleet-analytics', [
            'selectedPort' => $selectedPort,
            'currentPortMeta' => $currentPortMeta,
            'availablePorts' => $availablePorts,
            'fleet' => $fleet,
            'annual' => $annual,
            'seasonality' => $seasonality,
            'recent' => $recent,
            'caveats' => $caveats,
        ]);
    }
}
