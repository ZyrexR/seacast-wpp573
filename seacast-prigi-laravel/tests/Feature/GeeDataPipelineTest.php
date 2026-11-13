<?php

namespace Tests\Feature;

use App\Domain\ScientificData\Services\GeeDataIngestionService;
use App\Infrastructure\Python\Contracts\PythonComputationBridgeInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class GeeDataPipelineTest extends TestCase
{
    protected string $sampleGeeCsv;
    protected string $samplePilotCsv;

    protected function setUp(): void
    {
        parent::setUp();

        $baseDir = dirname(base_path());
        $this->sampleGeeCsv = $baseDir . '/data/oceanographic/monthly_csv/SEACAST_PRIGI_GRID_8129_2015_01.csv';
        $this->samplePilotCsv = $baseDir . '/data/pilot_2024/PILOT_2024_WPP573_R010_M01.csv';
    }

    /**
     * Test 1: Preflight validation succeeds on canonical 104-column GEE monthly CSV.
     */
    public function test_preflight_validation_succeeds_on_canonical_gee_csv(): void
    {
        if (!File::exists($this->sampleGeeCsv)) {
            $this->markTestSkipped("Sample GEE CSV not found at {$this->sampleGeeCsv}");
        }

        /** @var GeeDataIngestionService $service */
        $service = app(GeeDataIngestionService::class);
        $result = $service->validateGeeCsv($this->sampleGeeCsv);

        $this->assertTrue($result['is_valid'], "Expected canonical GEE CSV to pass validation");
        $this->assertEquals(8129, $result['row_count'], "Expected exact 8,129 cells");
        $this->assertGreaterThanOrEqual(100, $result['column_count']);
        $this->assertEquals('104_COL_GEE_MONTHLY_PRODUCTION', $result['schema_variant']);
        $this->assertNotEmpty($result['metadata']['sha256']);
        $this->assertEmpty($result['errors']);
    }

    /**
     * Test 2: Preflight validation succeeds on 51-column Pilot 2024 CSV.
     */
    public function test_preflight_validation_succeeds_on_pilot_2024_csv(): void
    {
        if (!File::exists($this->samplePilotCsv)) {
            $this->markTestSkipped("Sample Pilot CSV not found at {$this->samplePilotCsv}");
        }

        /** @var GeeDataIngestionService $service */
        $service = app(GeeDataIngestionService::class);
        $result = $service->validateGeeCsv($this->samplePilotCsv);

        $this->assertTrue($result['is_valid'], "Expected Pilot 2024 CSV to pass validation");
        $this->assertEquals(8129, $result['row_count'], "Expected exact 8,129 cells");
        $this->assertGreaterThanOrEqual(50, $result['column_count']);
        $this->assertEquals('51_COL_PILOT_2024', $result['schema_variant']);
        $this->assertNotEmpty($result['metadata']['sha256']);
        $this->assertEmpty($result['errors']);
    }

    /**
     * Test 3: Preflight validation fails and rejects truncated or invalid CSV.
     */
    public function test_preflight_validation_fails_on_truncated_csv(): void
    {
        // Case A: Suspiciously small file
        $smallPath = storage_path('app/temp_test_small.csv');
        File::put($smallPath, "cell_id,sst,chla\nWPP573_R010_C001_R052,28.5,0.2\n");

        /** @var GeeDataIngestionService $service */
        $service = app(GeeDataIngestionService::class);
        $resultSmall = $service->validateGeeCsv($smallPath);
        File::delete($smallPath);

        $this->assertFalse($resultSmall['is_valid']);
        $this->assertStringContainsString('suspiciously small', $resultSmall['errors'][0]);

        // Case B: File >1024 bytes but missing cells (e.g. 50 cells instead of 8,129)
        $truncatedPath = storage_path('app/temp_test_truncated.csv');
        $content = "cell_id,sst,chla,wind_speed,depth_m\n";
        for ($i = 0; $i < 50; $i++) {
            $content .= sprintf("WPP573_R010_C%03d_R052,28.5,0.20,5.0,1000.0,extra_padding_column_to_exceed_one_kilobyte\n", $i);
        }
        File::put($truncatedPath, $content);

        $resultTruncated = $service->validateGeeCsv($truncatedPath);
        File::delete($truncatedPath);

        $this->assertFalse($resultTruncated['is_valid'], "Expected truncated CSV to fail validation");
        $this->assertNotEmpty($resultTruncated['errors']);
        $this->assertStringContainsString('Canonical grid cell count mismatch', $resultTruncated['errors'][0]);
    }

    /**
     * Test 4: Python Computation Bridge executes GEE transformer with locked weights.
     */
    public function test_python_bridge_executes_gee_transformer_with_locked_weights(): void
    {
        if (!File::exists($this->sampleGeeCsv)) {
            $this->markTestSkipped("Sample GEE CSV not found at {$this->sampleGeeCsv}");
        }

        /** @var PythonComputationBridgeInterface $bridge */
        $bridge = app(PythonComputationBridgeInterface::class);

        $result = $bridge->runGeeTransformer($this->sampleGeeCsv, '2015-01-01', [
            'dry_run' => true,
            'limit' => 3
        ]);

        $this->assertEquals('SUCCESS', $result->status);
        $this->assertEquals('2.3.2', $result->modelVersion);
        $this->assertEquals(8129, $result->totalCells);
        $this->assertGreaterThan(0, $result->hotspotsCount);
        $this->assertNotEmpty($result->data);

        // Check locked scientific weights
        $weights = $result->provenance['applied_weights'] ?? [];
        $this->assertEquals(0.320, $weights['cakalang']['maxent'] ?? null);
        $this->assertEquals(0.355, $weights['cakalang']['rf'] ?? null);
        $this->assertEquals(0.325, $weights['cakalang']['glm'] ?? null);

        $this->assertEquals(0.295, $weights['madidihang']['maxent'] ?? null);
        $this->assertEquals(0.365, $weights['madidihang']['rf'] ?? null);
        $this->assertEquals(0.340, $weights['madidihang']['glm'] ?? null);

        $this->assertArrayNotHasKey('alba' . 'kor', $weights);
        $this->assertArrayHasKey('hsi_tongkol', $result->data[0] ?? []);
    }

    /**
     * Test 5: Artisan command seacast:ingest-gee executes cleanly in dry-run mode.
     */
    public function test_artisan_command_ingest_gee_dry_run(): void
    {
        if (!File::exists($this->samplePilotCsv)) {
            $this->markTestSkipped("Sample Pilot CSV not found at {$this->samplePilotCsv}");
        }

        $this->artisan('seacast:ingest-gee', [
            'file' => $this->samplePilotCsv,
            '--date' => '2024-01-01',
            '--dry-run' => true,
            '--limit' => 3
        ])
        ->expectsOutputToContain('EXISTS & READABLE')
        ->expectsOutputToContain('51_COL_PILOT_2024')
        ->expectsOutputToContain('GEE DATA PIPELINE COMPLETED WITH ZERO ERRORS!')
        ->assertExitCode(Command::SUCCESS);
    }

    /**
     * Test 6: API endpoint /api/monitoring/pipeline returns operational telemetry.
     */
    public function test_api_monitoring_pipeline_endpoint(): void
    {
        $response = $this->getJson('/api/monitoring/pipeline');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'status',
                'pipeline_version',
                'gee_engine_version',
                'spatial_domain',
                'satellites' => [
                    'NOAA_OISST_v21_MODIS',
                    'NASA_MODIS_Aqua_Chla',
                    'NOAA_CFSv2_Wind_Ekman',
                    'HYCOM_CMEMS_Current',
                    'GEBCO_2023_Bathymetry'
                ],
                'timestamp'
            ]
        ]);

        $this->assertEquals('success', $response->json('status'));
        $this->assertEquals('OPERATIONAL', $response->json('data.status'));
        $this->assertEquals('3.0.0', $response->json('data.pipeline_version'));
        $this->assertEquals('v2.3.2', $response->json('data.gee_engine_version'));
    }

    /**
     * Test 7: Public /api/monitoring includes operational pipeline telemetry.
     */
    public function test_api_monitoring_includes_pipeline_telemetry(): void
    {
        $response = $this->getJson('/api/monitoring');

        $response->assertStatus(200);
        $response->assertJsonPath('data.pipeline_satelit.status', 'OPERATIONAL');
        $response->assertJsonPath('data.pipeline_satelit.gee_engine_version', 'v2.3.2');
    }
}
