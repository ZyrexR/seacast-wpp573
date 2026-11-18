<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SyncLiveOceanDataCommandTest extends TestCase
{
    private string $manifestPath;
    private ?string $previousManifest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manifestPath = storage_path('app/gee_ingestion/ingestion_manifest.json');
        $this->previousManifest = File::exists($this->manifestPath) ? File::get($this->manifestPath) : null;
    }

    protected function tearDown(): void
    {
        if ($this->previousManifest === null) {
            File::delete($this->manifestPath);
        } else {
            File::put($this->manifestPath, $this->previousManifest);
        }
        parent::tearDown();
    }

    public function test_valid_manifest_skips_duplicate_requested_date_even_when_observation_is_older(): void
    {
        File::ensureDirectoryExists(dirname($this->manifestPath));
        File::put($this->manifestPath, json_encode([[
            'status' => 'VALID',
            'source_mode' => 'GEE_INGESTED_WITH_LOCAL_FALLBACKS',
            'target_date' => '2026-11-14',
            'requested_date' => '2026-11-16',
            'completed_at' => now()->toIso8601String(),
            'valid_until' => now()->addDay()->toIso8601String(),
        ]]));

        $this->artisan('seacast:sync-live', [
            '--skip-bmkg' => true,
            '--date' => '2026-11-16',
        ])->expectsOutputToContain('skipping extraction')->assertExitCode(0);

        $manifest = json_decode(File::get($this->manifestPath), true);
        $this->assertSame('VALID', $manifest[0]['status']);
        $this->assertSame('2026-11-14', $manifest[0]['target_date']);
    }

    public function test_force_reruns_and_failed_extractor_marks_manifest_unverified(): void
    {
        File::ensureDirectoryExists(dirname($this->manifestPath));
        File::put($this->manifestPath, json_encode([[
            'status' => 'VALID',
            'source_mode' => 'GEE_INGESTED_WITH_LOCAL_FALLBACKS',
            'target_date' => '2026-11-16',
            'requested_date' => '2026-11-16',
            'completed_at' => now()->toIso8601String(),
            'valid_until' => now()->addDay()->toIso8601String(),
        ]]));
        $previous = $_ENV['PYTHON_BINARY'] ?? null;
        $_ENV['PYTHON_BINARY'] = '__seacast_nonexistent_python__';
        try {
            $this->artisan('seacast:sync-live', [
                '--skip-bmkg' => true,
                '--date' => '2026-11-16',
                '--force' => true,
            ])->assertExitCode(1);
        } finally {
            if ($previous === null) {
                unset($_ENV['PYTHON_BINARY']);
            } else {
                $_ENV['PYTHON_BINARY'] = $previous;
            }
        }

        $manifest = json_decode(File::get($this->manifestPath), true);
        $this->assertSame('FAILED', $manifest[0]['status']);
        $this->assertSame('UNVERIFIED', $manifest[0]['source_mode']);
        $this->assertNull($manifest[0]['target_date']);
        $this->assertNull($manifest[0]['valid_until']);
    }

    public function test_local_simulation_manifest_cannot_skip_gee_extraction(): void
    {
        File::ensureDirectoryExists(dirname($this->manifestPath));
        File::put($this->manifestPath, json_encode([[
            'status' => 'VALID_WITH_FALLBACK',
            'source_mode' => 'LOCAL_SIMULATION_FALLBACK',
            'target_date' => '2026-11-16',
            'requested_date' => '2026-11-16',
            'completed_at' => now()->toIso8601String(),
            'valid_until' => now()->addDay()->toIso8601String(),
        ]]));
        $previous = $_ENV['PYTHON_BINARY'] ?? null;
        $_ENV['PYTHON_BINARY'] = '__seacast_nonexistent_python__';
        try {
            $this->artisan('seacast:sync-live', [
                '--skip-bmkg' => true,
                '--date' => '2026-11-16',
            ])->assertExitCode(1);
        } finally {
            if ($previous === null) {
                unset($_ENV['PYTHON_BINARY']);
            } else {
                $_ENV['PYTHON_BINARY'] = $previous;
            }
        }

        $manifest = json_decode(File::get($this->manifestPath), true);
        $this->assertSame('FAILED', $manifest[0]['status']);
        $this->assertSame('LOCAL_SIMULATION_FALLBACK', $manifest[1]['source_mode']);
    }

    public function test_sync_live_command_executes_successfully_with_skip_gee_flag(): void
    {
        $this->artisan('seacast:sync-live', ['--skip-gee' => true])
            ->expectsOutputToContain('SEACAST PRIGI V3 — LIVE ZERO-DUMMY DATA ORCHESTRATOR')
            ->expectsOutputToContain('Fetching Live BMKG Maritim Marine Bulletins')
            ->expectsOutputToContain('PPN Prigi (Jatim)')
            ->expectsOutputToContain('PPS Cilacap (Jateng)')
            ->assertExitCode(0);
    }
}
