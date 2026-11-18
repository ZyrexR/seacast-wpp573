<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Fleet\Services\FleetAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FleetApiController extends Controller
{
    public function __construct(
        protected FleetAnalyticsService $fleetService
    ) {}

    public function composition(Request $request): JsonResponse
    {
        $data = $this->fleetService->getFleetComposition($this->selectedPort($request));
        return response()->json([
            'status' => 'SUCCESS',
            'data' => $data,
            'caveats' => $this->fleetService->getScientificCaveats(),
        ])->header('Cache-Control', 'public, max-age=86400');
    }

    public function annualLandings(Request $request): JsonResponse
    {
        $data = $this->fleetService->getAnnualLandingsSummary($this->selectedPort($request));
        return response()->json([
            'status' => 'SUCCESS',
            'data' => $data,
            'caveats' => $this->fleetService->getScientificCaveats(),
        ])->header('Cache-Control', 'public, max-age=86400');
    }

    public function seasonality(Request $request): JsonResponse
    {
        $data = $this->fleetService->getMonthlySeasonality($this->selectedPort($request));
        return response()->json([
            'status' => 'SUCCESS',
            'data' => $data,
        ])->header('Cache-Control', 'public, max-age=86400');
    }

    private function selectedPort(Request $request): string
    {
        $validated = $request->validate([
            'port' => 'sometimes|in:PPN_PRIGI,PPS_CILACAP',
        ]);

        return $validated['port'] ?? 'PPN_PRIGI';
    }
}

