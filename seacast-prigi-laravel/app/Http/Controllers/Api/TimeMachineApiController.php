<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\ScientificData\Services\TimeMachineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimeMachineApiController extends Controller
{
    public function __construct(
        protected TimeMachineService $timeMachineService
    ) {}

    /**
     * Get spatio-temporal time machine predictions and summaries.
     */
    public function index(Request $request): JsonResponse
    {
        $summary = filter_var($request->query('summary'), FILTER_VALIDATE_BOOLEAN);
        $cellId = $request->query('cell_id');
        $ym = $request->query('ym');
        $limit = min(8129, max(1, (int)$request->query('limit', 8129)));

        // 1. Monthly summaries across all 12 months for 2025
        if ($summary) {
            $summaries = $this->timeMachineService->getMonthlySummaries();
            return response()->json([
                'status' => 'success',
                'summary' => $summaries,
                'total_months' => count($summaries),
            ])->header('Cache-Control', 'public, max-age=86400');
        }

        // 2. Single canonical cell 12-month trajectory
        if ($cellId) {
            $history = $this->timeMachineService->getCellHistory($cellId);
            return response()->json([
                'status' => 'success',
                'cell_id' => $cellId,
                'total_months' => count($history),
                'history' => $history,
            ])->header('Cache-Control', 'public, max-age=86400');
        }

        // 3. Specific year_month spatial snapshot
        $targetYm = $ym ?? '2025_08';
        $cells = $this->timeMachineService->getYearMonthCells($targetYm, $limit);

        return response()->json([
            'status' => 'success',
            'year_month' => $targetYm,
            'total_cells' => count($cells),
            'cells' => $cells,
        ])->header('Cache-Control', 'public, max-age=86400');
    }
}

