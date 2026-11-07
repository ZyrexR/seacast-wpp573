<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\GeospatialCacheManager;

class GeospatialPerformanceFeatureTest extends TestCase
{
    protected GeospatialCacheManager $cacheManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheManager = app(GeospatialCacheManager::class);
        $this->cacheManager->flushAll();
    }

    public function test_cold_summary_returns_miss_and_warm_returns_hit(): void
    {
        // 1. Cold request
        $response1 = $this->getJson('/api/predictions?summary_only=true');
        $response1->assertStatus(200);
        $response1->assertHeader('X-Cache', 'MISS');
        $response1->assertHeader('ETag');

        $etag = $response1->headers->get('ETag');

        // 2. Warm request
        $response2 = $this->getJson('/api/predictions?summary_only=true');
        $response2->assertStatus(200);
        $response2->assertHeader('X-Cache', 'HIT');
        $response2->assertHeader('ETag', $etag);

        // 3. Conditional revalidation (If-None-Match)
        $response3 = $this->withHeaders(['If-None-Match' => $etag])
            ->getJson('/api/predictions?summary_only=true');
        $response3->assertStatus(304);
        $response3->assertHeader('X-Cache', 'HIT-REVALIDATED');
    }

    public function test_prediction_filters_cache_and_etag_revalidation(): void
    {
        $uri = '/api/predictions?bbox=111.5,-8.6,112.0,-8.1';

        // Cold request
        $resCold = $this->getJson($uri);
        $resCold->assertStatus(200);
        $resCold->assertHeader('X-Cache', 'MISS');
        $this->assertNotEmpty($resCold->headers->get('ETag'));

        $etag = $resCold->headers->get('ETag');

        // Warm request
        $resWarm = $this->getJson($uri);
        $resWarm->assertStatus(200);
        $resWarm->assertHeader('X-Cache', 'HIT');
        $resWarm->assertHeader('ETag', $etag);

        // Revalidation
        $resReval = $this->withHeaders(['If-None-Match' => $etag])->getJson($uri);
        $resReval->assertStatus(304);
        $resReval->assertHeader('X-Cache', 'HIT-REVALIDATED');
    }

    public function test_canonical_grid_cache_hit_and_invalidation(): void
    {
        $uri = '/api/grid?limit=50';

        // 1. Cold request
        $res1 = $this->getJson($uri);
        $res1->assertStatus(200);
        $res1->assertHeader('X-Cache', 'MISS');

        // 2. Warm request
        $res2 = $this->getJson($uri);
        $res2->assertStatus(200);
        $res2->assertHeader('X-Cache', 'HIT');

        // 3. Invalidate canonical grids
        $this->cacheManager->invalidateCanonicalGrids();

        // 4. Next request should be MISS
        $res3 = $this->getJson($uri);
        $res3->assertStatus(200);
        $res3->assertHeader('X-Cache', 'MISS');
    }
}

