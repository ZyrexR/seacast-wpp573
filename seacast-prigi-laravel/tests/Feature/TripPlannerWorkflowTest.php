<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\TripPlan;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;

class TripPlannerWorkflowTest extends TestCase
{
    private User $nelayan;
    private User $syahbandarPrigi;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

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
        ]);
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
        // Create 1 plan for Prigi, 1 for Cilacap
        TripPlan::updateOrCreate(
            ['reference_no' => 'DOC-PRG-TEST-001'],
            [
                'user_id' => $this->nelayan->id,
                'port_code' => 'PPN_PRIGI',
                'port_name' => 'PPN Prigi',
                'vessel_name' => 'KM Bahari Prigi',
                'skipper_name' => 'Pak Joko',
                'gt_class' => '10 - 20 GT',
                'gear_code' => 'LHP-PUT',
                'gear_name' => 'Pancing Ulur',
                'departure_date' => '2026-11-20',
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

        TripPlan::updateOrCreate(
            ['reference_no' => 'DOC-CLP-TEST-002'],
            [
                'user_id' => $this->nelayan->id,
                'port_code' => 'PPS_CILACAP',
                'port_name' => 'PPS Cilacap',
                'vessel_name' => 'KM Samudera Cilacap',
                'skipper_name' => 'Pak Taryono',
                'gt_class' => '> 30 GT',
                'gear_code' => 'LLD-RT',
                'gear_name' => 'Rawai Tuna',
                'departure_date' => '2026-11-21',
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
    }
}
