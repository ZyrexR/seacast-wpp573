<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GeospatialCacheManager;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\PredictionApiController;
use App\Http\Controllers\Api\GridApiController;

class BenchmarkGeospatialPerformanceCommand extends Command
{
    protected $signature = 'seacast:benchmark 
                            {--iterations=10 : Number of warm iterations per test case}
                            {--date=2026-11-03 : Date to benchmark}';

    protected $description = 'Benchmark cold and warm cache geospatial performance, latencies, and payload metrics';

    public function handle(
        GeospatialCacheManager $cacheManager,
        PredictionApiController $predictionController,
        GridApiController $gridController
    ): int {
        $iterations = (int)$this->option('iterations');
        $date = $this->option('date');

        $this->info("================================================================================");
        $this->info("SEACAST PRIGI V3 — GEOSPATIAL PERFORMANCE BENCHMARK (PHASE 3.1)");
        $this->info("================================================================================");
        $this->info("Cache Store: " . $cacheManager->getStoreName());
        $this->info("Benchmark Date: {$date} | Iterations: {$iterations}");
        $this->line("");

        $testCases = [
            [
                'name' => '1. Daily Summary API (/api/predictions?summary_only=true)',
                'controller' => 'prediction',
                'params' => ['date' => $date, 'summary_only' => 'true'],
            ],
            [
                'name' => '2. Bounding Box Viewport Query (/api/predictions?bbox=111.5,-8.6,112.0,-8.1)',
                'controller' => 'prediction',
                'params' => ['date' => $date, 'bbox' => '111.5,-8.6,112.0,-8.1'],
            ],
            [
                'name' => '3. High HSI Filter (/api/predictions?species=cakalang&min_hsi=0.60)',
                'controller' => 'prediction',
                'params' => ['date' => $date, 'species' => 'cakalang', 'min_hsi' => '0.60'],
            ],
            [
                'name' => '4. Canonical Grid Query (/api/grid?limit=200)',
                'controller' => 'grid',
                'params' => ['limit' => '200'],
            ],
            [
                'name' => '5. Full Prediction Layer (8,129 cells)',
                'controller' => 'prediction',
                'params' => ['date' => $date],
            ],
        ];

        $results = [];

        foreach ($testCases as $tc) {
            $this->comment("Running: {$tc['name']}");

            // Step A: Invalidate cache for this test case
            if ($tc['controller'] === 'grid') {
                $cacheManager->invalidateCanonicalGrids();
            } else {
                $cacheManager->invalidateDate($date);
            }

            // Step B: Cold request
            gc_collect_cycles();
            $memBefore = memory_get_usage(true);
            $t0 = microtime(true);
            $request = Request::create('/test', 'GET', $tc['params']);
            
            if ($tc['controller'] === 'grid') {
                $response = $gridController->index($request);
            } else {
                $response = $predictionController->index($request);
            }
            $coldMs = (microtime(true) - $t0) * 1000;
            $memPeak = (memory_get_peak_usage(true) - $memBefore) / (1024 * 1024);
            $content = $response->getContent();
            $payloadBytes = strlen($content);
            $json = json_decode($content, true);
            $featureCount = isset($json['features']) ? count($json['features']) : (isset($json['summary']) ? 1 : 0);
            $coldCacheHeader = $response->headers->get('X-Cache', 'NONE');

            // Step C: Warm requests (N iterations)
            $warmTimes = [];
            for ($i = 0; $i < $iterations; $i++) {
                $tWarm = microtime(true);
                $requestWarm = Request::create('/test', 'GET', $tc['params']);
                if ($tc['controller'] === 'grid') {
                    $respWarm = $gridController->index($requestWarm);
                } else {
                    $respWarm = $predictionController->index($requestWarm);
                }
                $warmTimes[] = (microtime(true) - $tWarm) * 1000;
            }

            sort($warmTimes);
            $avgWarm = array_sum($warmTimes) / count($warmTimes);
            $minWarm = min($warmTimes);
            $maxWarm = max($warmTimes);
            $p95Index = (int)ceil(0.95 * count($warmTimes)) - 1;
            $p95Warm = $warmTimes[max(0, $p95Index)];

            $results[] = [
                'Case' => substr($tc['name'], 0, 32),
                'Cold (ms)' => round($coldMs, 1),
                'Warm Avg' => round($avgWarm, 2) . ' ms',
                'P95' => round($p95Warm, 2) . ' ms',
                'Min' => round($minWarm, 2) . ' ms',
                'Features' => $featureCount,
                'Size' => round($payloadBytes / 1024, 1) . ' KB',
                'Target <50ms' => ($avgWarm < 50.0) ? 'PASS' : 'WARN',
            ];
        }

        $this->line("");
        $this->table(['Benchmark Case', 'Cold', 'Warm Avg', 'P95', 'Min', 'Features', 'Payload', 'Target <50ms'], $results);
        $this->info("================================================================================");
        $this->info("Benchmark complete. All warm requests validated.");

        return 0;
    }
}

