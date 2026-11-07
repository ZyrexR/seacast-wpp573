<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GeospatialCacheManager;
use App\Domain\ScientificData\Repositories\ScientificDataRepositoryInterface;
use App\Domain\ScientificData\Services\ScientificDataService;

class WarmGeospatialCacheCommand extends Command
{
    protected $signature = 'seacast:cache:warm 
                            {--date=2026-11-03 : Target prediction date to warm}';

    protected $description = 'Pre-warm daily summary and primary prediction layers into geospatial cache';

    public function handle(
        GeospatialCacheManager $cacheManager,
        ScientificDataRepositoryInterface $repository,
        ScientificDataService $service
    ): int {
        $date = $this->option('date');
        $this->info("Pre-warming geospatial cache for date: {$date} (Store: {$cacheManager->getStoreName()})...");

        // 1. Warm Daily Summary
        $this->line("  1. Computing & caching daily summary...");
        $t0 = microtime(true);
        $summaryKey = $cacheManager->buildSummaryKey($date);
        $summary = $repository->getDailySummary($date);
        if ($summary) {
            $cacheManager->put($summaryKey, $summary, GeospatialCacheManager::DEFAULT_SUMMARY_TTL, ['domain:daily_predictions', 'date:' . $date]);
            $ms = round((microtime(true) - $t0) * 1000, 2);
            $this->info("     ✓ Summary cached in {$ms}ms.");
        } else {
            $this->warn("     ✗ No summary found for {$date}.");
        }

        // 2. Warm Full Predictions GeoJSON
        $this->line("  2. Generating & caching full GeoJSON prediction features (8,129 cells)...");
        $t0 = microtime(true);
        $fullKey = $cacheManager->buildPredictionsKey($date, []);
        $geoJson = $service->getDailyGeoJson($date, []);
        $cacheManager->put($fullKey, $geoJson, GeospatialCacheManager::DEFAULT_PREDICTIONS_TTL, ['domain:daily_predictions', 'date:' . $date]);
        $ms = round((microtime(true) - $t0) * 1000, 2);
        $featureCount = count($geoJson['features'] ?? []);
        $this->info("     ✓ Full GeoJSON cached ({$featureCount} features) in {$ms}ms.");

        // 3. Warm Common Filter Subsets
        $subsets = [
            'hotspot_only' => ['hotspot_only' => true],
            'prigi_inshore_bbox' => ['bbox' => [111.5, -8.6, 112.0, -8.1]],
            'cakalang_high_hsi' => ['species' => 'cakalang', 'min_hsi' => 0.60],
        ];

        foreach ($subsets as $name => $filters) {
            $this->line("  3. Pre-warming subset [{$name}]...");
            $t0 = microtime(true);
            $subsetKey = $cacheManager->buildPredictionsKey($date, $filters);
            $subsetGeoJson = $service->getDailyGeoJson($date, $filters);
            $cacheManager->put($subsetKey, $subsetGeoJson, GeospatialCacheManager::DEFAULT_PREDICTIONS_TTL, ['domain:daily_predictions', 'date:' . $date]);
            $ms = round((microtime(true) - $t0) * 1000, 2);
            $count = count($subsetGeoJson['features'] ?? []);
            $this->info("     ✓ Subset [{$name}] cached ({$count} features) in {$ms}ms.");
        }

        $this->info("Cache warming complete! Subsequent API requests will hit warm cache (<50ms).");

        return 0;
    }
}

