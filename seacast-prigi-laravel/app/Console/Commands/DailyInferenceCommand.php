<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Infrastructure\Python\Contracts\PythonComputationBridgeInterface;
use App\Services\GeospatialCacheManager;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DailyInferenceCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'seacast:infer-daily 
                            {--date= : Target prediction date in YYYY-MM-DD format} 
                            {--force : Force execution ignoring existing predictions}
                            {--limit=0 : Optional cell limit for testing (0 for all 8129)}';

    /**
     * The console command description.
     */
    protected $description = 'Execute automated daily scientific Python Ensemble SDM inference and flush geospatial cache';

    public function __construct(
        protected PythonComputationBridgeInterface $bridge,
        protected GeospatialCacheManager $cacheManager
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dateOption = $this->option('date') ?: Carbon::today()->toDateString();

        // 1. Validate date parameter format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateOption)) {
            $this->error("Invalid date format: [{$dateOption}]. Expected YYYY-MM-DD.");
            return Command::FAILURE;
        }

        $limit = (int)$this->option('limit');

        $this->info("================================================================================");
        $this->info(" SEACAST PRIGI V3 — DAILY SCIENTIFIC INFERENCE ORCHESTRATION PIPELINE");
        $this->info("================================================================================");
        $this->line("Target Date       : <fg=cyan>{$dateOption}</>");
        $this->line("Spatial Domain    : <fg=cyan>WPPNRI 573 (8,129 Canonical Grid Polygons)</>");
        $this->line("Ensemble Models   : <fg=cyan>MaxEnt + Random Forest + GLM (scikit-learn)</>");
        $this->line("Execution Time    : <fg=cyan>" . Carbon::now('Asia/Jakarta')->toDateTimeString() . " WIB</>");
        $this->newLine();

        $this->info("Spawning scientific Python engine via secure execution bridge...");

        $startTime = microtime(true);

        try {
            $options = [];
            if ($limit > 0) {
                $options['limit'] = $limit;
            }

            // Run inference via Python Bridge
            $result = $this->bridge->runInference($dateOption, $options);

            $durationMs = round((microtime(true) - $startTime) * 1000, 2);

            $this->info("Scientific computation completed successfully in {$durationMs} ms.");
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Status', $result->status],
                    ['Model Version', $result->modelVersion],
                    ['Prediction Date', $dateOption],
                    ['Total Cells Processed', number_format($result->totalCells)],
                    ['Hotspots Detected', number_format($result->hotspotsCount)],
                    ['Bridge Latency', "{$result->executionDurationMs} ms"],
                    ['Total Pipeline Duration', "{$durationMs} ms"],
                ]
            );

            // 2. Invalidate Geospatial Cache Tags
            $this->info("Flushing and revalidating geospatial cache tags...");
            $flushed = $this->cacheManager->invalidateDate($dateOption);
            $this->line("Geospatial cache flushed: <fg=green>{$flushed} keys invalidated</>.");

            Log::info("Daily scientific inference completed successfully", [
                'date' => $dateOption,
                'total_cells' => $result->totalCells,
                'duration_ms' => $durationMs,
                'flushed_cache_keys' => $flushed,
            ]);

            $this->info("================================================================================");
            $this->info(" INFERENCE PIPELINE EXECUTED SUCCESSFULLY — ZERO ERRORS");
            $this->info("================================================================================");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $durationMs = round((microtime(true) - $startTime) * 1000, 2);

            $this->error("Daily inference pipeline failed: " . $e->getMessage());

            Log::error("Daily scientific inference pipeline failed", [
                'date' => $dateOption,
                'error' => $e->getMessage(),
                'duration_ms' => $durationMs,
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
