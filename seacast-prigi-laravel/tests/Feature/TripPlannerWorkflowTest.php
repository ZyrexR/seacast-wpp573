<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\TripPlan;
use App\Enums\UserRole;
use App\Services\BmkgGridForecastService;
use Mockery\MockInterface;
use Illuminate\Support\Facades\Hash;

class TripPlannerWorkflowTest extends TestCase
{
    private User $nelayan;
    private User $syahbandarPrigi;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mock(BmkgGridForecastService::class, function (MockInterface $mock) {
            $mock->shouldReceive('forecast')->andReturn(null);
        });

        $this->nelayan = User::updateOrCreate(
            ['email' => 'nelayan_trip@seacast.id'],
            ['name' => 'Nakhoda Sukirno', 'role' => UserRole::NELAYAN->value, 'password' => Hash::make('secret123')]
        );

        $this->syahbandarPrigi = User::updateOrCreate(
            ['email' => 'syahbandar_prigi@seacast.id'],
            ['name' => 'Syahbandar Prigi', 'role' => UserRole::SYAHBANDAR->value, 'password' => Hash::make('secret123')]
        );

        $this->admin = User::updateOrCreate(
            ['email' => 'admin_trip@seacast.id'],
            ['name' => 'Admin Utama', 'role' => UserRole::ADMIN->value, 'password' => Hash::make('secret123')]
        );
    }

    public function test_trip_planner_page_loads_with_required_form_fields(): void
    {
        $response = $this->actingAs($this->nelayan)->get('/dashboard/trip-planner');

        $response->assertStatus(200);
        $response->assertSee('Rencana Operasional Melaut');
        $response->assertSee('PPN Prigi');
        $response->assertSee('PPS Cilacap');
        $response->assertSee('Permen KP 59/2020');
        $response->assertSee('Cadangan Keselamatan +15%');
        $this->assertSame('SUBSIDIZED', $response->viewData('initialPlan')['fuel_type']);
        $this->assertSame('UNVERIFIED_INPUT', $response->viewData('initialPlan')['wave_source']);

        $largeVessel = $this->actingAs($this->nelayan)->get('/dashboard/trip-planner?gt_class=%3E%2030%20GT&fuel_type=SUBSIDIZED');
        $largeVessel->assertOk();
        $this->assertSame('NON_SUBSIDIZED', $largeVessel->viewData('initialTarget')['fuel_type']);
        $this->assertSame(15000.0, $largeVessel->viewData('initialPlan')['solar_price_per_liter']);

        $legacySpecies = $this->actingAs($this->nelayan)->get('/dashboard/trip-planner?species=madidihang');
        $legacySpecies->assertOk()->assertDontSee('Tongkol Lisong / Krai');
        $this->assertSame('tuna_sirip_kuning', $legacySpecies->viewData('initialTarget')['target_commodity']);
    }

    public function test_initial_forecast_does_not_replace_form_fallback_wave_for_other_dates(): void
    {
        $date = now('Asia/Jakarta')->addDay()->toDateString();
        $forecastAt = \Carbon\Carbon::parse($date . ' 07:00', 'Asia/Jakarta')->utc()->toIso8601String();
        $this->mock(BmkgGridForecastService::class, function (MockInterface $mock) use ($forecastAt) {
            $mock->shouldReceive('forecast')->once()->andReturn([
                'status' => 'AVAILABLE', 'wave_height_m' => 2.2,
                'forecast_at' => $forecastAt,
            ]);
        });
        $response = $this->actingAs($this->nelayan)->get('/dashboard/trip-planner?date=' . $date . '&wave=1.3');
        $response->assertOk();
        $this->assertSame(1.3, $response->viewData('initialTarget')['wave_height_m']);
        $this->assertSame(2.2, $response->viewData('initialPlan')['wave_safety']['wave_height_m']);
        $this->assertSame('BMKG_INAWAVES_POINT_FORECAST', $response->viewData('initialPlan')['wave_source']);
    }

    public function test_trip_planner_and_saved_pdf_do_not_claim_one_solar_tariff_for_every_gt_class(): void
    {
        $page = $this->actingAs($this->nelayan)->get('/dashboard/trip-planner?gt_class=%3E%2030%20GT');
        $page->assertOk()->assertDontSee('Estimasi Biaya Solar (Subsidi)')
            ->assertDontSee('@ Rp 6.800 / Liter');

        $trip = TripPlan::make([
            'reference_no' => 'DOC-CLP-DYNAMIC-001', 'gt_class' => '> 30 GT',
            'departure_date' => '2026-11-20', 'total_fuel_liters' => 100,
            'total_fuel_cost_rp' => 1500000, 'gross_revenue_rp' => 10000000,
            'net_profit_rp' => 6000000, 'wave_height_m' => 1.5,
            'wave_status' => 'AMAN', 'permen_kp_status' => 'COMPLIANT_WITHIN_LEGAL_BOUNDS',
        ]);
        $trip->created_at = now();
        $pdfHtml = view('pdf.sailing-advisory', ['trip' => $trip])->render();
        $this->assertStringContainsString('@ Rp 15.000/L', $pdfHtml);
        $this->assertStringContainsString('Rp 4.000.000', $pdfHtml);
        $this->assertStringNotContainsString('PATUH TERHADAP PERMEN KP', $pdfHtml);
    }

    public function test_trip_planner_ajax_calculate_returns_scientific_metrics(): void
    {
        $payload = [
            'port_code' => 'PPN_PRIGI',
            'gt_class' => '10 - 20 GT',
            'target_commodity' => 'Cakalang',
            'target_lat' => -8.75,
            'target_lon' => 111.75,
            'departure_date' => '2026-11-20',
            'hsi_score' => 0.78,
            'wave_height_m' => 1.3,
        ];

        $response = $this->actingAs($this->nelayan)->postJson('/dashboard/trip-planner/calculate', $payload);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'reference_no',
            'cell_id',
            'trip_days',
            'effective_fishing_hours',
            'duration_feasibility_status',
            'duration_warning',
            'estimate_source',
            'spatial_evidence_level',
            'data_source_mode',
            'target_source',
            'navigation' => ['distance_nm', 'compass_direction'],
            'fuel' => ['base_fuel_liters', 'safety_reserve_liters', 'total_fuel_liters', 'total_fuel_cost_rp'],
            'permen_kp' => ['status', 'is_compliant', 'citation'],
            'wave_safety' => ['status', 'is_safe_to_sail'],
            'moon_phase' => ['phase_name', 'fishing_advisory'],
            'catch_projection' => ['projected_kg', 'gross_revenue_rp'],
            'economics' => ['total_operational_cost_rp', 'provisions_cost_rp', 'net_profit_rp'],
        ]);
        $response->assertJsonPath('trip_days', 1);
        $response->assertJsonPath('vessel_readiness', 'OPTIMAL');
        $this->assertEquals(1.0, $response->json('readiness_factor'));
        $response->assertJsonPath('estimate_source', 'FORMULA_SCENARIO');
    }

    public function test_fuel_type_is_selected_automatically_from_gt_class(): void
    {
        $payload = [
            'port_code' => 'PPN_PRIGI', 'gt_class' => '< 10 GT',
            'target_commodity' => 'Cakalang', 'target_lat' => -8.45,
            'target_lon' => 111.75, 'departure_date' => '2026-11-20',
            'wave_height_m' => 1.2,
        ];
        $small = $this->actingAs($this->nelayan)->postJson('/dashboard/trip-planner/calculate', $payload + ['fuel_type' => 'NON_SUBSIDIZED']);
        $large = $this->actingAs($this->nelayan)->postJson('/dashboard/trip-planner/calculate', array_merge($payload, [
            'gt_class' => '> 30 GT', 'fuel_type' => 'SUBSIDIZED',
        ]));
        $small->assertOk()->assertJsonPath('fuel_type', 'SUBSIDIZED')
            ->assertJsonPath('fuel.solar_price_per_liter', 6800)
            ->assertJsonPath('subsidy_eligibility_status', 'UNVERIFIED');
        $large->assertOk()->assertJsonPath('fuel_type', 'NON_SUBSIDIZED')
            ->assertJsonPath('fuel.solar_price_per_liter', 15000)
            ->assertJsonPath('subsidy_eligibility_status', 'NOT_ELIGIBLE_BY_GT')
            ->assertJsonPath('wave_source', 'UNVERIFIED_INPUT');
        $this->assertSame(
            (int)round($large->json('fuel.total_fuel_liters') * 15000),
            $large->json('fuel.total_fuel_cost_rp')
        );

        $this->actingAs($this->nelayan)->postJson('/dashboard/trip-planner/calculate', $payload + ['fuel_type' => 'UNKNOWN'])
            ->assertUnprocessable()->assertJsonValidationErrors('fuel_type');
    }

    public function test_matching_bmkg_forecast_overrides_input_wave_for_departure(): void
    {
        $departureDate = now('Asia/Jakarta')->addDay()->toDateString();
        $forecastAt = \Carbon\Carbon::parse($departureDate . ' 07:00', 'Asia/Jakarta')->utc()->toIso8601String();
        $this->mock(BmkgGridForecastService::class, function (MockInterface $mock) use ($forecastAt) {
            $mock->shouldReceive('forecast')->once()->andReturn([
                'status' => 'AVAILABLE', 'wave_height_m' => 3.1,
                'forecast_at' => $forecastAt,
                'source' => 'BMKG InaWaves',
            ]);
        });
        $response = $this->actingAs($this->nelayan)->postJson('/dashboard/trip-planner/calculate', [
            'port_code' => 'PPN_PRIGI', 'gt_class' => '< 10 GT',
            'target_commodity' => 'Cakalang', 'target_lat' => -8.45,
            'target_lon' => 111.75, 'departure_date' => $departureDate,
            'wave_height_m' => 0.5,
        ]);
        $response->assertOk()->assertJsonPath('wave_safety.wave_height_m', 3.1)
            ->assertJsonPath('wave_source', 'BMKG_INAWAVES_POINT_FORECAST')
            ->assertJsonPath('readiness_status', 'TIDAK_DISARANKAN');
    }

    public function test_forecast_for_another_day_is_not_used_for_departure(): void
    {
        $departureDate = now('Asia/Jakarta')->addDay()->toDateString();
        $this->mock(BmkgGridForecastService::class, function (MockInterface $mock) {
            $mock->shouldReceive('forecast')->once()->andReturn([
                'status' => 'AVAILABLE', 'wave_height_m' => 3.1,
                'forecast_at' => now('Asia/Jakarta')->addDays(2)->startOfDay()->utc()->toIso8601String(),
            ]);
        });
        $this->actingAs($this->nelayan)->postJson('/dashboard/trip-planner/calculate', [
            'port_code' => 'PPN_PRIGI', 'gt_class' => '< 10 GT',
            'target_commodity' => 'Cakalang', 'target_lat' => -8.45,
            'target_lon' => 111.75, 'departure_date' => $departureDate,
            'wave_height_m' => 0.5,
        ])->assertOk()->assertJsonPath('wave_safety.wave_height_m', 0.5)
            ->assertJsonPath('wave_source', 'UNVERIFIED_INPUT')
            ->assertJsonPath('readiness_status', 'SIAP_DENGAN_CATATAN');
    }

    public function test_trip_planner_three_day_calculation_returns_operational_economics(): void
    {
        $response = $this->actingAs($this->nelayan)->postJson('/dashboard/trip-planner/calculate', [
            'port_code' => 'PPS_CILACAP',
            'gt_class' => '10 - 20 GT',
            'target_commodity' => 'Tuna Sirip Kuning',
            'target_lat' => -8.75,
            'target_lon' => 109.10,
            'departure_date' => '2026-11-20',
            'hsi_score' => 0.82,
            'wave_height_m' => 1.3,
            'trip_days' => 3,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('trip_days', 3);
        $response->assertJsonPath('estimate_source', 'FORMULA_SCENARIO');
        $this->assertGreaterThan(0, $response->json('effective_fishing_hours'));
        $this->assertGreaterThan(0, $response->json('economics.provisions_cost_rp'));
        $this->assertGreaterThan(0, $response->json('economics.total_operational_cost_rp'));
        $this->assertGreaterThan(0, $response->json('catch_projection.projected_kg'));
        $response->assertJsonPath('daily_provisions_cost_rp', 350000);
        $response->assertJsonPath('economics.daily_provisions_cost_rp', 350000);
        $response->assertJsonPath('provisions_cost_rp', 1050000);
    }

    public function test_calculate_endpoint_scales_provisions_for_large_vessel(): void
    {
        $response = $this->actingAs($this->nelayan)->postJson('/dashboard/trip-planner/calculate', [
            'port_code' => 'PPS_CILACAP',
            'gt_class' => '> 30 GT',
            'target_commodity' => 'Cakalang',
            'target_lat' => -8.75,
            'target_lon' => 109.10,
            'departure_date' => '2026-11-20',
            'trip_days' => 2,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('daily_provisions_cost_rp', 1200000);
        $response->assertJsonPath('economics.daily_provisions_cost_rp', 1200000);
        $response->assertJsonPath('provisions_cost_rp', 2400000);
        $this->assertSame(
            $response->json('catch_projection.gross_revenue_rp') - $response->json('total_operational_cost_rp'),
            $response->json('economics.net_profit_rp')
        );
    }

    public function test_calculate_endpoint_reports_vessel_readiness_and_capacity(): void
    {
        $response = $this->actingAs($this->nelayan)->postJson('/dashboard/trip-planner/calculate', [
            'port_code' => 'PPN_PRIGI', 'gt_class' => '10 - 20 GT',
            'target_commodity' => 'Cakalang', 'target_lat' => -8.35,
            'target_lon' => 111.7275, 'departure_date' => '2026-11-20',
            'trip_days' => 2, 'vessel_readiness' => 'TERBATAS',
            'wave_height_m' => 1.0,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('vessel_readiness', 'TERBATAS');
        $response->assertJsonPath('readiness_factor', 0.65);
        $response->assertJsonPath('readiness_status', 'TIDAK_DISARANKAN');
        $response->assertJsonPath('max_hold_capacity_kg', 1500);
        $response->assertJsonPath('max_endurance_days', 5);
        $this->assertIsArray($response->json('readiness_notes'));
        $response->assertJsonPath('estimate_source', 'FORMULA_SCENARIO');
    }

    public function test_calculate_endpoint_rejects_unknown_vessel_readiness(): void
    {
        $response = $this->actingAs($this->nelayan)->postJson('/dashboard/trip-planner/calculate', [
            'port_code' => 'PPN_PRIGI', 'gt_class' => '10 - 20 GT',
            'target_commodity' => 'Cakalang', 'target_lat' => -8.35,
            'target_lon' => 111.7275, 'departure_date' => '2026-11-20',
            'vessel_readiness' => 'RUSAK',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('vessel_readiness');
    }

    public function test_trip_planner_rejects_trip_days_outside_one_to_fourteen(): void
    {
        foreach ([0, 15] as $tripDays) {
            $response = $this->actingAs($this->nelayan)->postJson('/dashboard/trip-planner/calculate', [
                'port_code' => 'PPN_PRIGI',
                'gt_class' => '10 - 20 GT',
                'target_commodity' => 'Cakalang',
                'target_lat' => -8.75,
                'target_lon' => 111.75,
                'departure_date' => '2026-11-20',
                'trip_days' => $tripDays,
            ]);

            $response->assertStatus(422);
        }
    }

    public function test_selected_cell_id_is_propagated_through_trip_planner(): void
    {
        $response = $this->actingAs($this->nelayan)->postJson('/dashboard/trip-planner/calculate', [
            'port_code' => 'PPN_PRIGI',
            'gt_class' => '10 - 20 GT',
            'target_commodity' => 'Cakalang',
            'target_lat' => -8.75,
            'target_lon' => 111.75,
            'departure_date' => '2026-11-20',
            'hsi_score' => 0.78,
            'wave_height_m' => 1.3,
            'cell_id' => 'WPP573_TEST_CELL_001',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('cell_id', 'WPP573_TEST_CELL_001');
        $response->assertJsonPath('target_source', 'PARAMETERS');
    }

    public function test_user_can_save_trip_plan_and_view_it_in_history(): void
    {
        $payload = [
            'port_code' => 'PPN_PRIGI',
            'vessel_name' => 'KM Sumber Rejeki',
            'skipper_name' => 'Nakhoda Warsito',
            'gt_class' => '10 - 20 GT',
            'target_commodity' => 'Cakalang',
            'target_lat' => -8.70,
            'target_lon' => 111.72,
            'departure_date' => '2026-11-22',
            'hsi_score' => 0.85,
            'wave_height_m' => 1.2,
        ];

        $response = $this->actingAs($this->nelayan)->post('/dashboard/trip-planner', $payload);

        $response->assertRedirect('/dashboard/trip-planner');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('trip_plans', [
            'user_id' => $this->nelayan->id,
            'vessel_name' => 'KM Sumber Rejeki',
            'port_code' => 'PPN_PRIGI',
        ]);
    }

    public function test_syahbandar_can_view_port_scoped_logbook(): void
    {
        // Recreate 1 plan for Prigi and 1 for Cilacap so they stay within the latest 100 logbook rows.
        TripPlan::whereIn('reference_no', ['DOC-PRG-TEST-001', 'DOC-CLP-TEST-002'])->delete();

        TripPlan::create(
            [
                'reference_no' => 'DOC-PRG-TEST-001',
                'user_id' => $this->nelayan->id,
                'port_code' => 'PPN_PRIGI',
                'port_name' => 'PPN Prigi',
                'vessel_name' => 'KM Bahari Prigi',
                'skipper_name' => 'Pak Joko',
                'gt_class' => '10 - 20 GT',
                'gear_code' => 'LHP-PUT',
                'gear_name' => 'Pancing Ulur',
                'departure_date' => '2099-11-20',
                'target_commodity' => 'Cakalang',
                'target_lat' => -8.5,
                'target_lon' => 111.7,
                'target_dms' => "08°30'00\"S, 111°42'00\"E",
                'distance_nm' => 15.0,
                'bearing_degrees' => 180.0,
                'compass_direction' => 'S',
                'base_fuel_liters' => 84.0,
                'safety_reserve_liters' => 12.6,
                'total_fuel_liters' => 96.6,
                'total_fuel_cost_rp' => 656880,
                'projected_catch_kg' => 450,
                'gross_revenue_rp' => 9900000,
                'net_profit_rp' => 9243120,
                'wave_height_m' => 1.2,
                'wave_status' => 'AMAN',
                'permen_kp_status' => 'COMPLIANT',
                'moon_phase_name' => 'Bulan Baru',
            ]
        );

        TripPlan::create(
            [
                'reference_no' => 'DOC-CLP-TEST-002',
                'user_id' => $this->nelayan->id,
                'port_code' => 'PPS_CILACAP',
                'port_name' => 'PPS Cilacap',
                'vessel_name' => 'KM Samudera Cilacap',
                'skipper_name' => 'Pak Taryono',
                'gt_class' => '> 30 GT',
                'gear_code' => 'LLD-RT',
                'gear_name' => 'Rawai Tuna',
                'departure_date' => '2099-11-21',
                'target_commodity' => 'Tuna Sirip Kuning',
                'target_lat' => -9.5,
                'target_lon' => 109.0,
                'target_dms' => "09°30'00\"S, 109°00'00\"E",
                'distance_nm' => 105.0,
                'bearing_degrees' => 180.0,
                'compass_direction' => 'S',
                'base_fuel_liters' => 1470.0,
                'safety_reserve_liters' => 220.5,
                'total_fuel_liters' => 1690.5,
                'total_fuel_cost_rp' => 11495400,
                'projected_catch_kg' => 3500,
                'gross_revenue_rp' => 157500000,
                'net_profit_rp' => 146004600,
                'wave_height_m' => 1.5,
                'wave_status' => 'AMAN',
                'permen_kp_status' => 'COMPLIANT',
                'moon_phase_name' => 'Bulan Sabit',
            ]
        );

        // Syahbandar Prigi should see KM Bahari Prigi
        $responsePrigi = $this->actingAs($this->syahbandarPrigi)->get('/dashboard/trip-planner');
        $responsePrigi->assertStatus(200);
        $responsePrigi->assertSee('KM Bahari Prigi');
        $responsePrigi->assertDontSee('KM Samudera Cilacap');

        // Admin should see both
        $responseAdmin = $this->actingAs($this->admin)->get('/dashboard/trip-planner');
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('KM Bahari Prigi');
        $responseAdmin->assertSee('KM Samudera Cilacap');
        $responseAdmin->assertSee('Skenario Formula HSI');
    }

    public function test_saved_compliant_safe_trip_renders_patuh_and_aman_badges(): void
    {
        $payload = [
            'port_code' => 'PPN_PRIGI',
            'vessel_name' => 'KM Patuh Samudera',
            'skipper_name' => 'Nakhoda Budi',
            'gt_class' => '10 - 20 GT',
            'target_commodity' => 'tuna_sirip_kuning',
            'target_lat' => -8.65,
            'target_lon' => 111.75,
            'departure_date' => '2026-11-22',
            'hsi_score' => 0.80,
            'wave_height_m' => 1.1,
        ];

        $this->actingAs($this->nelayan)->post('/dashboard/trip-planner', $payload)
            ->assertRedirect('/dashboard/trip-planner');

        $response = $this->actingAs($this->admin)->get('/dashboard/trip-planner');
        $response->assertStatus(200);
        $response->assertSee('KM Patuh Samudera');
        $response->assertSee('TANPA KONFLIK AWAL');
        $response->assertSee('AMAN');
    }
}
