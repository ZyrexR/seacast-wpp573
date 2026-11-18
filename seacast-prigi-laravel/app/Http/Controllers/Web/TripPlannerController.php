<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Domain\Trip\Services\TripPlanningService;
use App\Services\BmkgGridForecastService;
use App\Models\TripPlan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TripPlannerController extends Controller
{
    public function __construct(
        protected TripPlanningService $plannerService,
        protected BmkgGridForecastService $gridForecast
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        // Defaults from query params (e.g. from DPI Radar shortcut)
        $initialTarget = [
            'port_code' => $request->query('port', 'PPN_PRIGI'),
            'cell_id' => $request->query('cell_id'),
            'vessel_name' => $request->query('vessel_name', $user->name ? "KM {$user->name}" : 'KM Nelayan Bahari'),
            'skipper_name' => $request->query('skipper_name', $user->name ?? 'Nakhoda Sukirno'),
            'gt_class' => $request->query('gt_class', '< 10 GT'),
            'target_commodity' => $this->canonicalCommodity((string)$request->query('species', 'Cakalang')),
            'target_lat' => (float)$request->query('lat', -8.4500),
            'target_lon' => (float)$request->query('lon', 111.7500),
            'departure_date' => $request->query('date', date('Y-m-d')),
            'hsi_score' => (float)$request->query('hsi', 0.78),
            'wave_height_m' => (float)$request->query('wave', 1.3),
            'trip_days' => max(1, min(14, (int)$request->query('trip_days', 1))),
            'vessel_readiness' => $request->query('vessel_readiness', 'OPTIMAL'),
            'fuel_type' => TripPlanningService::fuelTypeForGtClass($request->query('gt_class', '< 10 GT')),
        ];

        // Initial calculation
        $initialPlan = $this->plannerService->generateCompleteTripPlan($this->withDepartureForecast($initialTarget));

        // User's own trip history
        $myTrips = TripPlan::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Port Admin / Syahbandar Logbook (Review Focus 5: Port-scoped admin access)
        $adminLogbook = null;
        if ($user->isAdmin() || $user->hasRole('syahbandar') || $user->hasRole('peneliti')) {
            $logQuery = TripPlan::with('user')->orderBy('departure_date', 'desc')->orderBy('id', 'desc');

            // If user has specific port affiliation
            if ($user->hasRole('syahbandar') && str_contains(strtolower($user->name . $user->email), 'cilacap')) {
                $logQuery->where('port_code', 'PPS_CILACAP');
            } elseif ($user->hasRole('syahbandar') && str_contains(strtolower($user->name . $user->email), 'prigi')) {
                $logQuery->where('port_code', 'PPN_PRIGI');
            }

            $adminLogbook = $logQuery->take(100)->get();
        }

        return view('dashboard.trip-planner', [
            'ports' => TripPlanningService::availablePorts(),
            'gtSpecs' => TripPlanningService::GT_SPECS,
            'commodityPrices' => TripPlanningService::COMMODITY_PRICES,
            'initialTarget' => $initialTarget,
            'initialPlan' => $initialPlan,
            'myTrips' => $myTrips,
            'adminLogbook' => $adminLogbook,
        ]);
    }

    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'port_code' => 'required|string',
            'gt_class' => 'required|string',
            'target_commodity' => 'required|string',
            'target_lat' => 'required|numeric',
            'target_lon' => 'required|numeric',
            'cell_id' => 'nullable|string|max:100',
            'departure_date' => 'required|date',
            'hsi_score' => 'nullable|numeric|between:0,1',
            'wave_height_m' => 'nullable|numeric|min:0',
            'trip_days' => 'sometimes|integer|between:1,14',
            'vessel_readiness' => 'sometimes|in:OPTIMAL,CUKUP,TERBATAS',
            'fuel_type' => 'sometimes|in:SUBSIDIZED,NON_SUBSIDIZED',
            'vessel_name' => 'nullable|string',
            'skipper_name' => 'nullable|string',
        ]);

        $plan = $this->plannerService->generateCompleteTripPlan($this->withDepartureForecast($validated));

        return response()->json($plan);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'port_code' => 'required|string',
            'vessel_name' => 'required|string|max:100',
            'skipper_name' => 'required|string|max:100',
            'gt_class' => 'required|string',
            'target_commodity' => 'required|string',
            'target_lat' => 'required|numeric',
            'target_lon' => 'required|numeric',
            'cell_id' => 'nullable|string|max:100',
            'departure_date' => 'required|date',
            'hsi_score' => 'nullable|numeric|between:0,1',
            'wave_height_m' => 'nullable|numeric|min:0',
            'trip_days' => 'sometimes|integer|between:1,14',
            'vessel_readiness' => 'sometimes|in:OPTIMAL,CUKUP,TERBATAS',
            'fuel_type' => 'sometimes|in:SUBSIDIZED,NON_SUBSIDIZED',
            'notes' => 'nullable|string|max:500',
        ]);

        $calculated = $this->plannerService->generateCompleteTripPlan($this->withDepartureForecast($validated));

        $trip = TripPlan::create([
            'user_id' => $request->user()?->id,
            'reference_no' => $calculated['reference_no'],
            'port_code' => $calculated['port']['code'],
            'port_name' => $calculated['port']['name'],
            'vessel_name' => $calculated['vessel_name'],
            'skipper_name' => $calculated['skipper_name'],
            'gt_class' => $calculated['gt_class'],
            'gear_code' => $calculated['gear_code'],
            'gear_name' => $calculated['gear_name'],
            'departure_date' => $calculated['departure_date'],
            'target_commodity' => $calculated['target_commodity'],
            'target_lat' => $calculated['target_coordinates']['lat_dec'],
            'target_lon' => $calculated['target_coordinates']['lon_dec'],
            'target_dms' => $calculated['target_coordinates']['dms'],
            'distance_nm' => $calculated['navigation']['distance_nm'],
            'bearing_degrees' => $calculated['navigation']['bearing_degrees'],
            'compass_direction' => $calculated['navigation']['compass_direction'],
            'base_fuel_liters' => $calculated['fuel']['base_fuel_liters'],
            'safety_reserve_liters' => $calculated['fuel']['safety_reserve_liters'],
            'total_fuel_liters' => $calculated['fuel']['total_fuel_liters'],
            'total_fuel_cost_rp' => $calculated['fuel']['total_fuel_cost_rp'],
            'projected_catch_kg' => $calculated['catch_projection']['projected_kg'],
            'gross_revenue_rp' => $calculated['catch_projection']['gross_revenue_rp'],
            'net_profit_rp' => $calculated['economics']['net_profit_rp'],
            'wave_height_m' => $calculated['wave_safety']['wave_height_m'],
            'wave_status' => $calculated['wave_safety']['status'],
            'permen_kp_status' => $calculated['permen_kp']['status'],
            'moon_phase_name' => $calculated['moon_phase']['phase_name'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('dashboard.trip-planner')
            ->with('success', "Rencana pelayaran berhasil disimpan dengan Nomor Referensi resmi: {$trip->reference_no}.");
    }

    public function downloadPdf(string $id)
    {
        $query = TripPlan::where('reference_no', $id);
        if (ctype_digit($id)) {
            $query->orWhere('id', (int) $id);
        }
        $trip = $query->firstOrFail();

        $fontDir = storage_path('fonts');
        if (!is_dir($fontDir)) {
            @mkdir($fontDir, 0775, true);
        }

        $pdf = Pdf::loadView('pdf.sailing-advisory', [
            'trip' => $trip,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $filename = "Sailing-Advisory-{$trip->reference_no}.pdf";

        return $pdf->stream($filename);
    }

    private function withDepartureForecast(array $params): array
    {
        try {
            $date = \Carbon\Carbon::parse($params['departure_date'], 'Asia/Jakarta')->startOfDay();
            $today = now('Asia/Jakarta')->startOfDay();
            if ($date->lessThan($today) || $date->greaterThan($today->copy()->addDays(7))) {
                $params['wave_source'] = 'UNVERIFIED_INPUT';
                return $params;
            }

            $departure = $date->copy()->setTime(7, 0)->utc();
            if ($date->isSameDay($today) && $departure->lessThan(now('UTC'))) {
                $departure = now('UTC');
            }
            $forecast = $this->gridForecast->forecast(
                (float)$params['target_lat'], (float)$params['target_lon'], $departure
            );
            if ($forecast !== null && isset($forecast['wave_height_m'], $forecast['forecast_at']) &&
                \Carbon\Carbon::parse($forecast['forecast_at'])->setTimezone('Asia/Jakarta')->isSameDay($date)) {
                $params['wave_height_m'] = (float)$forecast['wave_height_m'];
                $params['wave_source'] = 'BMKG_INAWAVES_POINT_FORECAST';
                $params['weather_forecast'] = $forecast;
                return $params;
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        $params['wave_source'] = 'UNVERIFIED_INPUT';
        return $params;
    }

    private function canonicalCommodity(string $species): string
    {
        return match (str_replace([' ', '-'], '_', strtolower(trim($species)))) {
            'tuna_sirip_kuning', 'madidihang' => 'tuna_sirip_kuning',
            'tongkol', 'tongkol_krai', 'tongkol_lisong' => 'tongkol_krai',
            default => 'Cakalang',
        };
    }
}
