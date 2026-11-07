<?php

namespace App\Jobs;

use App\Domain\ScientificData\Services\GeeDataIngestionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessGeeIngestionJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 300;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public array $backoff = [15, 45, 90];

    public function __construct(
        public string $filePath,
        public ?string $date = null,
        public array $options = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(GeeDataIngestionService $ingestionService): void
    {
        Log::info("ProcessGeeIngestionJob started", [
            'file' => $this->filePath,
            'date' => $this->date,
            'attempt' => $this->attempts(),
        ]);

        $startTime = microtime(true);

        $report = $ingestionService->ingest($this->filePath, $this->date, $this->options);

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        Log::info("ProcessGeeIngestionJob completed successfully", [
            'file' => $this->filePath,
            'date' => $report['target_date'],
            'total_cells' => $report['total_cells'],
            'hotspots' => $report['hotspots_count'],
            'duration_ms' => $durationMs,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessGeeIngestionJob failed permanently", [
            'file' => $this->filePath,
            'date' => $this->date,
            'error' => $exception->getMessage(),
        ]);
    }
}

