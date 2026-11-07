<?php

namespace App\Console\Commands;

use App\Domain\ScientificData\Services\GeeDataIngestionService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class IngestGeeDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'seacast:ingest-gee 
                            {file : Path to the GEE export CSV file}
                            {--date= : Target prediction date in YYYY-MM-DD format}
                            {--dry-run : Validate and run inference without modifying DB or cache}
                            {--no-db : Skip PostgreSQL database persistence}
                            {--limit=0 : Optional cell limit for testing (0 for all 8129)}
                            {--force : Force re-ingestion even if date already exists}';

    /**
     * The console command description.
     */
    protected $description = 'Ingest, validate, and execute scientific Ensemble SDM inference on Google Earth Engine export CSV';

    public function __construct(
        protected GeeDataIngestionService $ingestionService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');
        $dateOption = $this->option('date');
        $dryRun = (bool)$this->option('dry-run');
        $noDb = (bool)$this->option('no-db');
        $limit = (int)$this->option('limit');

        $this->info("================================================================================");
        $this->info(" SEACAST PRIGI V3 — GEE SCIENTIFIC DATA INGESTION & INFERENCE PIPELINE");
        $this->info("================================================================================");
        $this->line("Target File       : <fg=cyan>{$filePath}</>");
        $this->line("Execution Mode    : " . ($dryRun ? '<fg=yellow>DRY-RUN (Preflight & Simulation Only)</>' : '<fg=green>PRODUCTION INGESTION</>'));
        $this->line("Database Write    : " . ($noDb || $dryRun ? '<fg=yellow>DISABLED</>' : '<fg=green>ENABLED (PostgreSQL 17)</>'));
        $this->line("Timestamp         : <fg=cyan>" . Carbon::now('Asia/Jakarta')->toDateTimeString() . " WIB</>");
        $this->newLine();

        // 1. Run Preflight QC Validation
        $this->info("Step 1: Executing preflight scientific quality control (QC) validation...");
        $validation = $this->ingestionService->validateGeeCsv($filePath);

        if (!$validation['is_valid']) {
            $this->error("❌ PREFLIGHT VALIDATION FAILED!");
            foreach ($validation['errors'] as $err) {
                $this->line("  • <fg=red>{$err}</>");
            }
            return Command::FAILURE;
        }

        $this->table(
            ['Validation Check', 'Result'],
            [
                ['File Status', 'EXISTS & READABLE'],
                ['File Size', number_format($validation['file_size_bytes']) . ' bytes'],
                ['Total Grid Rows', number_format($validation['row_count']) . ' (Exact 8,129 cells)'],
                ['Total Columns', number_format($validation['column_count'])],
                ['Schema Variant', $validation['schema_variant']],
                ['SHA-256 Digest', substr($validation['metadata']['sha256'], 0, 16) . '...'],
            ]
        );

        if (!empty($validation['warnings'])) {
            $this->warn("⚠️  QC Notices:");
            foreach ($validation['warnings'] as $warn) {
                $this->line("  • <fg=yellow>{$warn}</>");
            }
        }

        // 2. Execute Ingestion & Scientific Transformation
        $this->newLine();
        $this->info("Step 2: Spawning Python Scientific Engine (Scaler + 3 Species Ensemble + DOM)...");

        try {
            $options = [
                'dry_run' => $dryRun,
                'persist_db' => !$noDb && !$dryRun,
                'limit' => $limit
            ];

            $report = $this->ingestionService->ingest($filePath, $dateOption, $options);

            $this->newLine();
            $this->info("✅ GEE DATA PIPELINE COMPLETED WITH ZERO ERRORS!");
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Pipeline Status', $report['status']],
                    ['Execution Action', $report['action']],
                    ['Target Date', $report['target_date']],
                    ['Processed Cells', number_format($report['total_cells'])],
                    ['Hotspots Detected', number_format($report['hotspots_count'])],
                    ['Bridge Inference Latency', "{$report['bridge_duration_ms']} ms"],
                    ['Total End-to-End Latency', "{$report['total_duration_ms']} ms"],
                    ['Invalidated Cache Keys', (string)$report['cache_invalidated_keys']],
                ]
            );

            $this->info("================================================================================");
            $this->info(" GEE INGESTION PIPELINE FINISHED SUCCESSFULLY");
            $this->info("================================================================================");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error("❌ INGESTION PIPELINE EXECUTION ERROR:");
            $this->error($e->getMessage());
            Log::error("IngestGeeDataCommand failed", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return Command::FAILURE;
        }
    }
}

