<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\TripPlan;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;

class SailingAdvisoryPdfExportTest extends TestCase
{
    private User $nelayan;
    private TripPlan $tripPlan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nelayan = User::updateOrCreate(
            ['email' => 'nelayan_pdf@seacast.id'],
            ['name' => 'Nakhoda Warsito', 'role' => UserRole::NELAYAN->value, 'password' => Hash::make('secret123')]
        );

        $ref = 'DOC-PRG-TEST-' . bin2hex(random_bytes(4));
        $this->tripPlan = TripPlan::create([
            'user_id' => $this->nelayan->id,
            'reference_no' => $ref,
            'port_code' => 'PPN_PRIGI',
            'port_name' => 'PPN Prigi',
            'vessel_name' => 'KM Sumber Rejeki',
            'skipper_name' => 'Nakhoda Warsito',
            'gt_class' => '10 - 20 GT',
            'gear_code' => 'LHP-PUT',
            'gear_name' => 'Pancing Ulur Tuna (Handline)',
            'departure_date' => '2026-11-20',
            'target_commodity' => 'Cakalang',
            'target_lat' => -8.7000,
            'target_lon' => 111.7500,
            'target_dms' => "08°42'00\"S, 111°45'00\"E",
            'distance_nm' => 25.4,
            'bearing_degrees' => 174.5,
            'compass_direction' => 'S',
            'base_fuel_liters' => 142.24,
            'safety_reserve_liters' => 21.34,
            'total_fuel_liters' => 163.58,
            'total_fuel_cost_rp' => 1112344,
            'projected_catch_kg' => 450,
            'gross_revenue_rp' => 9900000,
            'net_profit_rp' => 8787656,
            'wave_height_m' => 1.3,
            'wave_status' => 'AMAN',
            'permen_kp_status' => 'COMPLIANT',
            'moon_phase_name' => 'Bulan Sabit Muda (Waxing Crescent)',
            'notes' => 'Perbekalan es 15 balok dan umpan tongkol segar.',
        ]);
    }

    public function test_guest_cannot_download_sailing_advisory_pdf(): void
    {
        $response = $this->get("/dashboard/trip-planner/{$this->tripPlan->reference_no}/pdf");
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_stream_sailing_advisory_pdf(): void
    {
        $response = $this->actingAs($this->nelayan)
            ->get("/dashboard/trip-planner/{$this->tripPlan->reference_no}/pdf");

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_can_download_pdf_using_numeric_id_or_reference_no(): void
    {
        $response = $this->actingAs($this->nelayan)
            ->get("/dashboard/trip-planner/{$this->tripPlan->id}/pdf");

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_non_existent_trip_returns_404(): void
    {
        $response = $this->actingAs($this->nelayan)
            ->get('/dashboard/trip-planner/DOC-NON-EXISTENT/pdf');

        $response->assertStatus(404);
    }
}
