<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class DailyInferenceCommandTest extends TestCase
{
    protected ?string $ingestionManifestBackup = null;
    protected bool $ingestionManifestExisted = false;

    protected function setUp(): void
    {
        parent::setUp();

        $manifestPath = storage_path('app/gee_ingestion/ingestion_manifest.json');
        $this->ingestionManifestExisted = File::exists($manifestPath);
        $this->ingestionManifestBackup = $this->ingestionManifestExisted ? File::get($manifestPath) : null;
        File::delete($manifestPath);
    }

    protected function tearDown(): void
    {
        $manifestPath = storage_path('app/gee_ingestion/ingestion_manifest.json');
        File::delete($manifestPath);
        if ($this->ingestionManifestExisted && $this->ingestionManifestBackup !== null) {
            File::put($manifestPath, $this->ingestionManifestBackup);
        }

        parent::tearDown();
    }

    public function test_command_rejects_invalid_date_format(): void
    {
        $exitCode = Artisan::call('seacast:infer-daily', [
            '--date' => 'invalid-date',
        ]);

        $this->assertEquals(1, $exitCode);
        $output = Artisan::output();
        $this->assertStringContainsString('Invalid date format', $output);
    }

    public function test_command_executes_successfully_with_valid_date(): void
    {
        $exitCode = Artisan::call('seacast:infer-daily', [
            '--date' => '2026-11-03',
            '--limit' => 5,
        ]);

        $this->assertEquals(0, $exitCode);
        $output = Artisan::output();
        $this->assertStringContainsString('INFERENCE PIPELINE EXECUTED SUCCESSFULLY', $output);
        $this->assertStringContainsString('Total Cells Processed', $output);
        $this->assertStringContainsString('Geospatial cache flushed', $output);

        $manifest = json_decode(File::get(storage_path('app/gee_ingestion/ingestion_manifest.json')), true);
        $this->assertIsArray($manifest);
        $this->assertSame('2026-11-03', $manifest[0]['target_date']);
        $this->assertSame('VALID_WITH_FALLBACK', $manifest[0]['status']);
        $this->assertSame('LOCAL_SIMULATION_FALLBACK', $manifest[0]['source_mode']);
        $this->assertSame('2026-11-03', $manifest[0]['date_coverage']['start']);
        $this->assertSame('2026-11-03', $manifest[0]['date_coverage']['end']);
    }
}

