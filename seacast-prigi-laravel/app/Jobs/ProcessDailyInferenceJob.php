<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Infrastructure\Python\Contracts\PythonComputationBridgeInterface;
use App\Services\GeospatialCacheManager;
use Illuminate\Support\Facades\Log;

class ProcessDailyInferenceJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 180;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public array $backoff = [10, 30, 60];

    public function __construct(
        public string $date,
        public array $options = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        PythonComputationBridgeInterface $bridge,
        GeospatialCacheManager $cacheManager
    ): void {
        Log::info("ProcessDailyInferenceJob started", [
            'date' => $this->date,
            'attempt' => $this->attempts(),
        ]);

        $startTime = microtime(true);

        $result = $bridge->runInference($this->date, $this->options);

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        // Invalidate cache tags
        $flushed = $cacheManager->invalidateDate($this->date);

        Log::info("ProcessDailyInferenceJob completed successfully", [
            'date' => $this->date,
            'model_version' => $result->modelVersion,
            'total_cells' => $result->totalCells,
            'duration_ms' => $durationMs,
            'flushed_cache_keys' => $flushed,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical("ProcessDailyInferenceJob exhausted all retries", [
            'date' => $this->date,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
