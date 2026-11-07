<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GeospatialCacheManager;

class ClearGeospatialCacheCommand extends Command
{
    protected $signature = 'seacast:cache:clear 
                            {--date= : Invalidate cache for a specific date (YYYY-MM-DD)}
                            {--domain= : Invalidate specific domain (daily_predictions, canonical_grids)}';

    protected $description = 'Clear or selectively invalidate SEACAST geospatial and prediction cache';

    public function handle(GeospatialCacheManager $cacheManager): int
    {
        $date = $this->option('date');
        $domain = $this->option('domain');

        if ($date) {
            $this->info("Invalidating geospatial cache for date: {$date}...");
            $cleared = $cacheManager->invalidateDate($date);
            $this->info("Cleared {$cleared} cached entries for date {$date}.");
            return 0;
        }

        if ($domain === 'canonical_grids') {
            $this->info("Invalidating canonical grids cache...");
            $cleared = $cacheManager->invalidateCanonicalGrids();
            $this->info("Cleared {$cleared} cached entries for canonical grids.");
            return 0;
        }

        $this->info("Flushing all SEACAST geospatial cache entries...");
        $cacheManager->flushAll();
        $this->info("All geospatial cache entries successfully cleared.");

        return 0;
    }
}

