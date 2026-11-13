<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SystemMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Reconciled API Endpoint: GET /api/monitoring
 * Provides sanitized health data for public consumption,
 * and protected diagnostics for authenticated administrators.
 */
class MonitoringApiController extends Controller
{
    protected SystemMonitoringService $monitoringService;

    public function __construct(SystemMonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    /**
     * Public Health Status Endpoint (Sanitized, zero secret leaks, zero absolute paths)
     */
    public function index(): JsonResponse
    {
        $status = $this->monitoringService->getPublicSystemStatus();

        return response()->json([
            'status' => 'success',
            'data' => $status,
        ], 200, [
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    /**
     * Operational GEE Pipeline Telemetry Endpoint
     */
    public function pipeline(): JsonResponse
    {
        $pipeline = $this->monitoringService->getPipelineStatus();

        return response()->json([
            'status' => 'success',
            'data' => $pipeline,
        ], 200, [
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    /**
     * Protected Administrator Diagnostics Endpoint
     * Requires active session and 'admin' role.
     */
    public function diagnostics(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Autentikasi diperlukan untuk mengakses data diagnostik sistem.',
            ], 401);
        }

        if (!$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses Ditolak: Peran pengguna tidak memiliki kewenangan diagnostik administrator.',
            ], 403);
        }

        $diagnostics = $this->monitoringService->getAdminDiagnostics();

        return response()->json([
            'status' => 'success',
            'data' => $diagnostics,
        ], 200, [
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    /**
     * Live BMKG Maritim Weather & Oceanographic Endpoint
     */
    public function bmkgLive(Request $request, \App\Services\BmkgMaritimService $bmkgService): JsonResponse
    {
        $hub = $request->query('hub') ?? $request->query('port') ?? 'prigi';
        $overview = $bmkgService->getMaritimeOverview($hub);

        return response()->json([
            'status' => 'success',
            'data' => $overview,
        ], 200, [
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
