<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Domain\Oceanography\Services\OceanographicDataService;
use App\Domain\Oceanography\DTO\OceanographicConditions;
use App\Domain\Oceanography\DTO\VariableMetadata;

class OceanographicEngineTest extends TestCase
{
    protected OceanographicDataService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(OceanographicDataService::class);
    }

    public function test_can_retrieve_variable_metadata_registry(): void
    {
        $registry = $this->service->getVariableMetadataRegistry();
        $this->assertArrayHasKey('sst', $registry);
        $this->assertArrayHasKey('chla', $registry);
        $this->assertArrayHasKey('currents', $registry);
        $this->assertArrayHasKey('wind', $registry);
        $this->assertArrayHasKey('bathymetry', $registry);

        $this->assertEquals('°C', $registry['sst']->unit);
        $this->assertEquals('mg/m³', $registry['chla']->unit);
        $this->assertEquals('m/s', $registry['currents']->unit);
        $this->assertEquals('m', $registry['bathymetry']->unit);

        // Verify fallback label for wave/wind
        $this->assertStringContainsString('Simulasi Oseanografi Musiman BMKG Terkalibrasi', $registry['wind']->source);
    }

    public function test_thermal_front_indicator_detection(): void
    {
        $this->assertTrue($this->service->isThermalFront(0.035));
        $this->assertTrue($this->service->isThermalFront(0.048));
        $this->assertFalse($this->service->isThermalFront(0.034));
        $this->assertFalse($this->service->isThermalFront(0.015));
    }

    public function test_variable_range_validation(): void
    {
        $this->assertTrue($this->service->validateSstRange(28.5));
        $this->assertFalse($this->service->validateSstRange(15.0)); // Outlier low
        $this->assertFalse($this->service->validateSstRange(36.0)); // Outlier high

        $this->assertTrue($this->service->validateChlaRange(0.45));
        $this->assertFalse($this->service->validateChlaRange(-0.1)); // Negative invalid
    }

    public function test_can_retrieve_grid_oceanographic_conditions(): void
    {
        $conditions = $this->service->getConditionsForCell('WPP573_R010_C143_R050', '2026-11-03');
        $this->assertInstanceOf(OceanographicConditions::class, $conditions);
        $this->assertEquals('WPP573_R010_C143_R050', $conditions->cellId);
        $this->assertGreaterThan(0, $conditions->sst);
        $this->assertGreaterThan(0, $conditions->chla);
        $this->assertNotNull($conditions->waveHeight);
    }
}
