<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\ScientificData\Services\SimulationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SimulationApiController extends Controller
{
    public function __construct(
        protected SimulationService $simulationService
    ) {}

    /**
     * Compute what-if environmental perturbation or vessel operational scenario.
     */
    public function simulate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sstDelta' => 'nullable|numeric|between:-5.0,5.0',
            'chlaMultiplier' => 'nullable|numeric|between:0.1,5.0',
            'date' => 'nullable|date_format:Y-m-d',
            'limit' => 'nullable|integer|between:1,8129',
            // Vessel economics params
            'vesselClass' => 'nullable|string|in:< 10 GT,10 - 20 GT,20 - 30 GT',
            'targetDistanceNm' => 'nullable|numeric|between:1,100',
            'targetSpecies' => 'nullable|string|in:cakalang,tuna_sirip_kuning,madidihang,tongkol_krai,tongkol',
            'solarPrice' => 'nullable|numeric|min:1000',
        ]);

        // If vessel economics calculation requested
        if ($request->has('vesselClass') || $request->has('targetDistanceNm')) {
            $vesselClass = $validated['vesselClass'] ?? '< 10 GT';
            $targetDistance = (float)($validated['targetDistanceNm'] ?? 15.0);
            $species = $validated['targetSpecies'] ?? 'cakalang';
            $solarPrice = (float)($validated['solarPrice'] ?? 6800.0);

            $result = $this->simulationService->simulateVesselEconomics(
                $vesselClass,
                $targetDistance,
                $species,
                $solarPrice
            );

            return response()->json([
                'status' => 'success',
                'type' => 'vessel_economics',
                'data' => $result,
            ]);
        }

        // Climate / environmental perturbation scenario
        $sstDelta = (float)($validated['sstDelta'] ?? 0.0);
        $chlaMult = (float)($validated['chlaMultiplier'] ?? 1.0);
        $date = $validated['date'] ?? null;
        $limit = (int)($validated['limit'] ?? 200);

        $result = $this->simulationService->simulateClimateScenario($sstDelta, $chlaMult, $date, $limit);

        return response()->json([
            'status' => 'success',
            'type' => 'climate_perturbation',
            'data' => $result,
        ]);
    }
}

