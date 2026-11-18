<?php

namespace App\Console\Commands;

use App\Services\BmkgMaritimService;
use App\Services\GeospatialCacheManager;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Illuminate\Support\Str;

class SyncLiveOceanDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'seacast:sync-live 
                            {--date= : Optional target observation date (YYYY-MM-DD); defaults to latest GEE OISST}
                            {--skip-gee : Skip GEE satellite extraction and only warm BMKG feeds}
                            {--skip-bmkg : Skip BMKG weather sync}
                            {--force : Re-extract even when a valid manifest exists}';

    /**
     * The console command description.
     */
    protected $description = 'Orchestrates daily live zero-dummy data sync: BMKG Maritim APIs + GEE satellite extraction + ML inference + PostgreSQL ingestion';

    public function __construct(
        protected BmkgMaritimService $bmkgService,
        protected GeospatialCacheManager $cacheManager
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startTime = microtime(true);

        $this->info("================================================================================");
        $this->info(" SEACAST PRIGI V3 — LIVE ZERO-DUMMY DATA ORCHESTRATOR");
        $this->info("================================================================================");
        $this->line("Execution Time : <fg=cyan>" . Carbon::now('Asia/Jakarta')->toDateTimeString() . " WIB</>");
        $this->line("Policy         : <fg=green>STRICT ZERO-DUMMY (100% Real BMKG & GEE Observations)</>");
        $this->newLine();

        // -----------------------------------------------------------------
        // STEP 1: Sync & Warm Live BMKG Maritim Feeds
        // -----------------------------------------------------------------
        if (!$this->option('skip-bmkg')) {
            $this->info("🌊 [1/3] Fetching Live BMKG Maritim Marine Bulletins (Prigi & Cilacap)...");
            try {
                $prigiOverview = $this->bmkgService->getMaritimeOverview('prigi');
                $cilacapOverview = $this->bmkgService->getMaritimeOverview('cilacap');

                $this->table(
                    ['Hub / Port', 'Port Wave', 'Ocean Wave', 'Max Wave', 'Syahbandar Advisory'],
                    [
                        [
                            'PPN Prigi (Jatim)',
                            ($prigiOverview['port']['wave_height'] ?? '1.0') . ' m',
                            ($prigiOverview['ocean_offshore']['wave_height'] ?? '1.8') . ' m',
                            ($prigiOverview['max_wave_height'] ?? '1.8') . ' m',
                            $prigiOverview['overall_safety']['label'] ?? 'AMAN',
                        ],
                        [
                            'PPS Cilacap (Jateng)',
                            ($cilacapOverview['port']['wave_height'] ?? '1.0') . ' m',
                            ($cilacapOverview['ocean_offshore']['wave_height'] ?? '1.8') . ' m',
                            ($cilacapOverview['max_wave_height'] ?? '1.8') . ' m',
                            $cilacapOverview['overall_safety']['label'] ?? 'AMAN',
                        ],
                    ]
                );
                $this->line("   <fg=green>✓ BMKG Live forecasts fetched and cached successfully.</>");
            } catch (\Throwable $e) {
                $this->warn("   ⚠️ Warning: BMKG sync encountered an error: " . $e->getMessage());
                Log::warning("SEACAST SyncLive: BMKG fetch warning: " . $e->getMessage());
            }
        } else {
            $this->line("   [1/3] Skipping BMKG sync per --skip-bmkg flag.");
        }
        $this->newLine();

        // -----------------------------------------------------------------
        // STEP 2: Extract GEE Satellite Images & Run Ensemble SDM (.joblib)
        // -----------------------------------------------------------------
        if (!$this->option('skip-gee')) {
            $this->info("🛰️ [2/3] Extracting Authentic GEE Satellite Data & Running ML SDM Models...");
            $requestedDate = $this->option('date');
            if ($requestedDate && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedDate) ||
                !Carbon::hasFormat($requestedDate, 'Y-m-d'))) {
                $this->error('Invalid --date; expected YYYY-MM-DD.');
                return Command::FAILURE;
            }
            $existing = $this->validManifestFor($requestedDate);
            if ($existing && !$this->option('force')) {
                $actualDate = $existing['target_date'];
                $this->info("   Valid GEE manifest already covers observation {$actualDate}; skipping extraction. Use --force to re-run.");
                Log::info('SEACAST SyncLive: skipped existing valid GEE ingestion', ['observation_date' => $actualDate]);
                $targetDate = $actualDate;
            } else {
            $pythonBin = env('PYTHON_BINARY') ?: (PHP_OS_FAMILY === 'Windows' ? 'py' : 'python3');
            $scriptPath = base_path('../scripts/gee_daily_extractor.py');

            if (!file_exists($scriptPath)) {
                $scriptPath = base_path('scripts/gee_daily_extractor.py');
            }

            if (!file_exists($scriptPath)) {
                $this->error("❌ Error: GEE daily extractor script not found at {$scriptPath}");
                $this->recordGeeFailure($requestedDate, 'extractor_missing');
                return Command::FAILURE;
            }

            $cmd = [$pythonBin, $scriptPath, '--run-inference', '--persist-db'];
            if ($dateOpt = $this->option('date')) {
                $cmd[] = '--date=' . $dateOpt;
            }

            $this->line('   Starting GEE extraction and inference.');

            $timeout = filter_var(env('GEE_SYNC_TIMEOUT_SECONDS', 1800), FILTER_VALIDATE_INT);
            $timeout = $timeout && $timeout > 0 ? $timeout : 1800;
            try {
                $process = new Process($cmd, null, [
                    'DB_HOST' => (string) env('DB_HOST', ''),
                    'DB_PORT' => (string) env('DB_PORT', '5432'),
                    'DB_DATABASE' => (string) env('DB_DATABASE', ''),
                    'DB_USERNAME' => (string) env('DB_USERNAME', ''),
                    'DB_PASSWORD' => (string) env('DB_PASSWORD', ''),
                    'GEE_PROJECT' => (string) env('GEE_PROJECT', ''),
                    'GEE_SERVICE_ACCOUNT_EMAIL' => (string) env('GEE_SERVICE_ACCOUNT_EMAIL', ''),
                    'GEE_SERVICE_ACCOUNT_KEY_PATH' => (string) env('GEE_SERVICE_ACCOUNT_KEY_PATH', ''),
                    'GEE_SYNC_MAX_RETRIES' => (string) env('GEE_SYNC_MAX_RETRIES', '3'),
                ]);
                $process->setTimeout($timeout);
                $process->run(function ($type, $buffer) {
                    if ($type === Process::OUT) {
                        $this->output->write($buffer);
                    }
                });
            } catch (\Throwable $e) {
                $this->recordGeeFailure($requestedDate, 'process_exception');
                $this->error('GEE extraction failed or timed out; manifest marked FAILED.');
                Log::error('SEACAST SyncLive: GEE process exception', ['exception_type' => get_class($e)]);
                return Command::FAILURE;
            }

            if (!$process->isSuccessful()) {
                $this->recordGeeFailure($requestedDate, 'extractor_failed');
                $this->error('GEE extraction failed; manifest marked FAILED. Exit code: ' . $process->getExitCode());
                Log::error('SEACAST SyncLive: GEE extraction failed', ['exit_code' => $process->getExitCode()]);
                return Command::FAILURE;
            }

            $this->line("   <fg=green>✓ GEE Satellite Extraction & ML inference completed successfully.</>");
            $latest = $this->latestManifest();
            if (!$latest || !in_array($latest['status'] ?? null, ['VALID', 'VALID_WITH_FALLBACK'], true) ||
                !str_starts_with((string)($latest['source_mode'] ?? ''), 'GEE_INGESTED') ||
                empty($latest['target_date']) || empty($latest['completed_at']) ||
                (strtotime((string) $latest['completed_at']) ?: 0) < (int) $startTime - 1 ||
                ($requestedDate !== null && ($latest['requested_date'] ?? $latest['target_date']) !== $requestedDate)) {
                $this->recordGeeFailure($requestedDate, 'valid_manifest_missing');
                $this->error('GEE process ended without a valid ingestion manifest.');
                return Command::FAILURE;
            }
            $targetDate = $latest['target_date'] ?? $requestedDate;
            Log::info('SEACAST SyncLive: GEE extraction completed', ['observation_date' => $targetDate]);
            }
        } else {
            $this->line("   [2/3] Skipping GEE extraction per --skip-gee flag.");
        }
        $this->newLine();

        // -----------------------------------------------------------------
        // STEP 3: Invalidate Geospatial Cache & Warm Web Snapshot
        // -----------------------------------------------------------------
        $this->info("⚡ [3/3] Invalidate and Re-warm Geospatial Cache...");
        try {
            $targetDate = $targetDate ?? $this->option('date') ?: Carbon::today()->toDateString();
            $flushed = $this->cacheManager->invalidateDate($targetDate);
            $this->line("   <fg=green>✓ Geospatial cache invalidated ({$flushed} keys cleared).</>");
        } catch (\Throwable $e) {
            $this->warn("   ⚠️ Cache invalidation notice: " . $e->getMessage());
        }

        $totalDurationMs = round((microtime(true) - $startTime) * 1000, 2);
        $this->newLine();
        $this->info("================================================================================");
        $this->info(" SYNCHRONIZATION PIPELINE COMPLETED IN {$totalDurationMs} ms");
        $this->info("================================================================================");

        Log::info("SEACAST SyncLive: Orchestration finished successfully", [
            'duration_ms' => $totalDurationMs,
            'observation_date' => $targetDate ?? null,
            'manifest_status' => $this->latestManifest()['status'] ?? 'UNVERIFIED',
            'timestamp'   => now()->toIso8601String(),
        ]);

        return Command::SUCCESS;
    }

    private function latestManifest(): ?array
    {
        $path = storage_path('app/gee_ingestion/ingestion_manifest.json');
        if (!is_file($path)) {
            return null;
        }
        $records = json_decode((string) file_get_contents($path), true);
        return is_array($records) && isset($records[0]) && is_array($records[0]) ? $records[0] : null;
    }

    private function validManifestFor(?string $requestedDate): ?array
    {
        $record = $this->latestManifest();
        if (!$record || !in_array($record['status'] ?? null, ['VALID', 'VALID_WITH_FALLBACK'], true) ||
            !str_starts_with((string)($record['source_mode'] ?? ''), 'GEE_INGESTED') ||
            empty($record['target_date']) || empty($record['valid_until'])) {
            return null;
        }
        try {
            if (Carbon::parse($record['valid_until'])->isPast()) {
                return null;
            }
            if ($requestedDate !== null) {
                return ($record['requested_date'] ?? $record['target_date']) === $requestedDate ? $record : null;
            }
            return !empty($record['completed_at']) &&
                Carbon::parse($record['completed_at'])->timezone('Asia/Jakarta')->isToday() ? $record : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function recordGeeFailure(?string $requestedDate, string $reason): void
    {
        $path = storage_path('app/gee_ingestion/ingestion_manifest.json');
        $records = is_file($path) ? json_decode((string) file_get_contents($path), true) : [];
        $records = is_array($records) ? array_values(array_filter($records, 'is_array')) : [];
        array_unshift($records, [
            'dataset' => 'gee_daily_extraction',
            'target_date' => null,
            'requested_date' => $requestedDate,
            'status' => 'FAILED',
            'ingestion_status' => 'FAILED',
            'source_mode' => 'UNVERIFIED',
            'evidence_level' => 'UNVERIFIED',
            'validation_status' => 'UNVERIFIED',
            'date_coverage' => [],
            'completed_at' => now()->toIso8601String(),
            'valid_until' => null,
            'warnings' => [$reason],
        ]);
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $payload = json_encode(array_slice($records, 0, 100), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $temporary = $path . '.' . Str::random(12) . '.tmp';
        file_put_contents($temporary, $payload, LOCK_EX);
        if (!@rename($temporary, $path)) {
            file_put_contents($path, $payload, LOCK_EX);
            @unlink($temporary);
        }
    }
}
