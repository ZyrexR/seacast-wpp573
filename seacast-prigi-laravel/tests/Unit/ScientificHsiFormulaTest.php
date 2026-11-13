<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domain\ScientificData\Services\SimulationService;

class ScientificHsiFormulaTest extends TestCase
{
    private SimulationService $simulationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->simulationService = new SimulationService();
    }

    public function test_tongkol_hsi_is_optimal_in_coastal_neritic_bounds(): void
    {
        // Optimal Tongkol: SST 27.0 - 30.5°C, Chl-a 0.20 - 0.50 mg/m3, shallow depth < 200m (Tangke et al., 2020; Syah et al., 2023)
        $optimalHsi = $this->simulationService->calcTongkolHsi(
            sst: 28.5,
            chla: 0.35,
            depth: 85.0
        );
        $this->assertGreaterThanOrEqual(0.70, $optimalHsi);

        // Sub-optimal: Deep oceanic abyss (> 1000m) and cold SST (< 23°C)
        $subOptimalHsi = $this->simulationService->calcTongkolHsi(
            sst: 22.0,
            chla: 0.05,
            depth: 2500.0
        );
        $this->assertLessThan(0.40, $subOptimalHsi);
    }

    public function test_madidihang_hsi_is_optimal_in_deep_oceanic_bounds(): void
    {
        // Optimal Tuna Sirip Kuning: SST 23.0 - 31.5°C, oceanic Chl-a, deep bathymetry > 200m (Vaihola et al., 2023; Yati et al., 2024)
        $optimalHsi = $this->simulationService->calcMadidihangHsi(
            sst: 28.0,
            chla: 0.15,
            depth: 1200.0
        );
        $this->assertGreaterThanOrEqual(0.70, $optimalHsi);

        // Sub-optimal: extremely cold SST (< 20°C) or shallow coastal lagoon (< 20m)
        $subOptimalHsi = $this->simulationService->calcMadidihangHsi(
            sst: 19.0,
            chla: 0.15,
            depth: 30.0
        );
        $this->assertLessThan(0.40, $subOptimalHsi);
    }

    public function test_cakalang_hsi_prioritizes_chla_and_optimal_sst(): void
    {
        // Optimal Cakalang: SST 28.5 - 31.5°C, Chl-a 0.15 - 0.35 mg/m3 (Syah et al., 2023; Zainuddin et al., 2023)
        $optimalHsi = $this->simulationService->calcCakalangHsi(
            sst: 29.5,
            chla: 0.22,
            waveHeight: 1.2
        );
        $this->assertGreaterThanOrEqual(0.70, $optimalHsi);

        // Sub-optimal: Cold SST (24°C) with very low Chl-a (0.02)
        $subOptimalHsi = $this->simulationService->calcCakalangHsi(
            sst: 24.0,
            chla: 0.02,
            waveHeight: 3.5
        );
        $this->assertLessThan(0.40, $subOptimalHsi);
    }
}

