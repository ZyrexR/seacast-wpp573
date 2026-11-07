<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Domain\Trip\Services\TripPlanningService;
use App\Models\TripPlan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TripPlannerController extends Controller
{
    public function __construct(
        protected TripPlanningService $plannerService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        // Defaults from query params (e.g. from DPI Radar shortcut)
        $initialTarget = [
            'port_code' => $request->query('port', 'PPN_PRIGI'),
            'vessel_name' => $request->query('vessel_name', $user->name ? "KM {$user->name}" : 'KM Nelayan Bahari'),
            'skipper_name' => $request->query('skipper_name', $user->name ?? 'Nakhoda Sukirno'),
            'gt_class' => $request->query('gt_class', '< 10 GT'),
            'target_commodity' => ucfirst($request->query('species', 'Cakalang')),
            'target_lat' => (float)$request->query('lat', -8.4500),
            'target_lon' => (float)$request->query('lon', 111.7500),
            'departure_date' => $request->query('date', date('Y-m-d')),
            'hsi_score' => (float)$request->query('hsi', 0.78),
            'wave_height_m' => (float)$request->query('wave', 1.3),
        ];

        // Initial calculation
        $initialPlan = $this->plannerService->generateCompleteTripPlan($initialTarget);

        // User's own trip history
        $myTrips = TripPlan::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Port Admin / Syahbandar Logbook (Review Focus 5: Port-scoped admin access)
        $adminLogbook = null;
        if ($user->isAdmin() || $user->hasRole('syahbandar') || $user->hasRole('peneliti')) {
            $logQuery = TripPlan::with('user')->orderBy('departure_date', 'desc')->take(20);

            // If user has specific port affiliation
            if ($user->hasRole('syahbandar') && str_contains(strtolower($user->name . $user->email), 'cilacap')) {
                $logQuery->where('port_code', 'PPS_CILACAP');
            } elseif ($user->hasRole('syahbandar') && str_contains(strtolower($user->name . $user->email), 'prigi')) {
                $logQuery->where('port_code', 'PPN_PRIGI');
            }

            $adminLogbook = $logQuery->get();
        }

        return view('dashboard.trip-planner', [
            'ports' => TripPlanningService::PORTS,
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
            'departure_date' => 'required|date',
            'hsi_score' => 'nullable|numeric|between:0,1',
            'wave_height_m' => 'nullable|numeric|min:0',
            'vessel_name' => 'nullable|string',
            'skipper_name' => 'nullable|string',
        ]);

        $plan = $this->plannerService->generateCompleteTripPlan($validated);

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
            'departure_date' => 'required|date',
            'hsi_score' => 'nullable|numeric|between:0,1',
            'wave_height_m' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $calculated = $this->plannerService->generateCompleteTripPlan($validated);

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
        return response()->json(['status' => 'OK', 'trip_id' => $id]);
    }
}
