<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BmkgGridForecastService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BmkgGridForecastController extends Controller
{
    public function __invoke(Request $request, BmkgGridForecastService $service): JsonResponse
    {
        $coordinates = $request->validate([
            'lat' => 'required|numeric|between:-15,15',
            'lon' => 'required|numeric|between:90,145',
        ]);
        try {
            $forecast = $service->forecast((float) $coordinates['lat'], (float) $coordinates['lon']);
        } catch (\Throwable) {
            $forecast = null;
        }
        return response()->json($forecast ?: [
            'status' => 'UNAVAILABLE',
            'source' => 'BMKG InaWaves',
            'message' => 'Prakiraan model BMKG untuk titik ini belum tersedia.',
        ]);
    }
}
