<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Fleet\Services\FleetAnalyticsService;
use Illuminate\Http\JsonResponse;

class FleetApiController extends Controller
{
    public function __construct(
        protected FleetAnalyticsService $fleetService
    ) {}

    public function composition(): JsonResponse
    {
        $data = $this->fleetService->getFleetComposition();
        return response()->json([
            'status' => 'SUCCESS',
            'data' => $data,
            'caveats' => $this->fleetService->getScientificCaveats(),
        ])->header('Cache-Control', 'public, max-age=86400');
    }

    public function annualLandings(): JsonResponse
    {
        $data = $this->fleetService->getAnnualLandingsSummary();
        return response()->json([
            'status' => 'SUCCESS',
            'data' => $data,
            'caveats' => $this->fleetService->getScientificCaveats(),
        ])->header('Cache-Control', 'public, max-age=86400');
    }

    public function seasonality(): JsonResponse
    {
        $data = $this->fleetService->getMonthlySeasonality();
        return response()->json([
            'status' => 'SUCCESS',
            'data' => $data,
        ])->header('Cache-Control', 'public, max-age=86400');
    }
}

