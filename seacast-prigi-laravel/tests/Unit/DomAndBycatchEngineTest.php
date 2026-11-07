<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domain\DynamicOceanManagement\Services\DomClassificationService;
use App\Domain\Bycatch\Services\BycatchRiskService;

class DomAndBycatchEngineTest extends TestCase
{
    protected DomClassificationService $domService;
    protected BycatchRiskService $bycatchService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->domService = app(DomClassificationService::class);
        $this->bycatchService = app(BycatchRiskService::class);
    }

    public function test_classifies_z4_nursery_ground(): void
    {
        // Depth <= 150m AND dist <= 15 NM
        $zone = $this->domService->classify([
            'depth' => 120.0,
            'dist_port_nm' => 10.0,
            'sst' => 28.0,
            'grad_sst' => 0.02,
            'chla' => 0.20,
            'hsi_cakalang' => 0.40,
            'hsi_madidihang' => 0.30,
        ]);
        $this->assertEquals('Z4_NURSERY_GROUND', $zone->zoneKey);
    }

    public function test_classifies_z5_bycatch_thermal_front(): void
    {
        // Depth 150-1200m AND sst <= 26.5 AND grad_sst >= 0.035
        $zone = $this->domService->classify([
            'depth' => 600.0,
            'dist_port_nm' => 30.0,
            'sst' => 25.5,
            'grad_sst' => 0.040,
            'chla' => 0.25,
            'hsi_cakalang' => 0.40,
            'hsi_madidihang' => 0.30,
        ]);
        $this->assertEquals('Z5_BYCATCH_SCREENING', $zone->zoneKey);
    }

    public function test_classifies_z3_feeding_core(): void
    {
        // Chla >= 0.40 AND grad_sst >= 0.035 (and not shelf break cold front)
        $zone = $this->domService->classify([
            'depth' => 2000.0,
            'dist_port_nm' => 70.0,
            'sst' => 28.5,
            'grad_sst' => 0.042,
            'chla' => 0.55,
            'hsi_cakalang' => 0.40,
            'hsi_madidihang' => 0.30,
        ]);
        $this->assertEquals('Z3_FEEDING_CORE', $zone->zoneKey);
    }

    public function test_classifies_z1_and_z2_pfg_by_distance(): void
    {
        // Z1: Inshore <= 12 NM with Tuna >= 0.55 (depth > 150 to avoid Z4)
        $zoneZ1 = $this->domService->classify([
            'depth' => 300.0,
            'dist_port_nm' => 8.0,
            'sst' => 28.0,
            'grad_sst' => 0.02,
            'chla' => 0.20,
            'hsi_cakalang' => 0.60,
            'hsi_madidihang' => 0.40,
        ]);
        $this->assertEquals('Z1_PFG_INSHORE', $zoneZ1->zoneKey);

        // Z2: Offshore 12 - 60 NM with Tuna >= 0.55
        $zoneZ2 = $this->domService->classify([
            'depth' => 1500.0,
            'dist_port_nm' => 25.0,
            'sst' => 28.0,
            'grad_sst' => 0.02,
            'chla' => 0.20,
            'hsi_cakalang' => 0.30,
            'hsi_madidihang' => 0.65,
        ]);
        $this->assertEquals('Z2_PFG_OFFSHORE', $zoneZ2->zoneKey);
    }

    public function test_classifies_general_oceanic_fallback(): void
    {
        // Low HSI, deep water, no front
        $zone = $this->domService->classify([
            'depth' => 3500.0,
            'dist_port_nm' => 80.0,
            'sst' => 29.0,
            'grad_sst' => 0.010,
            'chla' => 0.15,
            'hsi_cakalang' => 0.25,
            'hsi_madidihang' => 0.30,
        ]);
        $this->assertEquals('GENERAL_OCEANIC', $zone->zoneKey);
    }

    public function test_strict_dom_priority_order_enforcement(): void
    {
        // Conflict between Z4 and Z1:
        // Depth 80m (Z4 eligible), dist 6 NM (Z4 and Z1 eligible), HSI 0.70 (Z1 eligible)
        // Must be Z4 because Z4 has higher priority than Z1!
        $conflictZ4Z1 = $this->domService->classify([
            'depth' => 80.0,
            'dist_port_nm' => 6.0,
            'sst' => 28.0,
            'grad_sst' => 0.02,
            'chla' => 0.20,
            'hsi_cakalang' => 0.70,
            'hsi_madidihang' => 0.70,
        ]);
        $this->assertEquals('Z4_NURSERY_GROUND', $conflictZ4Z1->zoneKey);

        // Conflict between Z5 and Z3:
        // Shelf break 500m + SST 25.0 + grad 0.040 (Z5) AND Chla 0.60 (Z3)
        // Must be Z5 because Z5 > Z3!
        $conflictZ5Z3 = $this->domService->classify([
            'depth' => 500.0,
            'dist_port_nm' => 40.0,
            'sst' => 25.0,
            'grad_sst' => 0.040,
            'chla' => 0.60,
            'hsi_cakalang' => 0.40,
            'hsi_madidihang' => 0.30,
        ]);
        $this->assertEquals('Z5_BYCATCH_SCREENING', $conflictZ5Z3->zoneKey);
    }

    public function test_bycatch_traffic_light_classification(): void
    {
        // 1. Outside shelf break (depth 100m) -> GREEN
        $b1 = $this->bycatchService->classify(100.0, 25.0, 0.05, 0.80);
        $this->assertEquals('GREEN', $b1->trafficLight);

        // 2. Outside shelf break deep (depth 2500m) -> GREEN
        $b2 = $this->bycatchService->classify(2500.0, 25.0, 0.05, 0.80);
        $this->assertEquals('GREEN', $b2->trafficLight);

        // 3. On shelf break (depth 500m), warm SST / no front -> YELLOW
        $b3 = $this->bycatchService->classify(500.0, 28.5, 0.02, 0.50);
        $this->assertEquals('YELLOW', $b3->trafficLight);

        // 4. On shelf break (depth 500m), cold front, but Cakalang HSI < 0.70 -> YELLOW
        $b4 = $this->bycatchService->classify(500.0, 25.0, 0.04, 0.65);
        $this->assertEquals('YELLOW', $b4->trafficLight);

        // 5. On shelf break (depth 500m), cold front, Cakalang HSI >= 0.70 -> RED
        $b5 = $this->bycatchService->classify(500.0, 25.0, 0.04, 0.72);
        $this->assertEquals('RED', $b5->trafficLight);
    }
}

