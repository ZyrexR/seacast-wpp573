<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\GeospatialCacheManager;
use Illuminate\Support\Facades\Cache;

class GeospatialCacheManagerTest extends TestCase
{
    protected GeospatialCacheManager $cacheManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheManager = new GeospatialCacheManager();
        $this->cacheManager->flushAll();
    }

    public function test_it_generates_deterministic_cache_keys(): void
    {
        $filters1 = ['species' => 'cakalang', 'min_hsi' => 0.6, 'sector' => 'PRIGI'];
        $filters2 = ['sector' => 'PRIGI', 'min_hsi' => 0.60, 'species' => 'CAKALANG'];

        $key1 = $this->cacheManager->buildPredictionsKey('2026-11-03', $filters1);
        $key2 = $this->cacheManager->buildPredictionsKey('2026-11-03', $filters2);

        $this->assertEquals($key1, $key2);
    }

    public function test_it_normalizes_bounding_box_coordinates(): void
    {
        $filters1 = ['bbox' => [111.50001, -8.60002, 112.00003, -8.10004]];
        $filters2 = ['bbox' => [111.50004, -8.60001, 112.00001, -8.10002]];

        $key1 = $this->cacheManager->buildPredictionsKey('2026-11-03', $filters1);
        $key2 = $this->cacheManager->buildPredictionsKey('2026-11-03', $filters2);

        $this->assertEquals($key1, $key2);
    }

    public function test_it_stores_and_retrieves_payload(): void
    {
        $key = 'seacast:geo:test:key1';
        $data = ['type' => 'FeatureCollection', 'features' => []];

        $this->cacheManager->put($key, $data, 300, ['tag:test']);
        $cached = $this->cacheManager->get($key);

        $this->assertNotNull($cached);
        $this->assertEquals($data, $cached['data']);
        $this->assertNotEmpty($cached['etag']);
    }

    public function test_it_stores_and_retrieves_raw_json(): void
    {
        $key = 'seacast:geo:test:raw';
        $json = '{"type":"FeatureCollection","features":[]}';
        $array = ['type' => 'FeatureCollection', 'features' => []];

        $this->cacheManager->putRaw($key, $json, $array, 300, ['tag:raw_test']);
        $cached = $this->cacheManager->get($key);

        $this->assertNotNull($cached);
        $this->assertEquals($json, $cached['json']);
        $this->assertEquals($array, $cached['data']);
    }

    public function test_it_selectively_invalidates_by_tag(): void
    {
        $key1 = 'seacast:geo:date:2026-11-03:item1';
        $key2 = 'seacast:geo:date:2026-11-03:item2';
        $key3 = 'seacast:geo:date:2026-11-04:item3';

        $this->cacheManager->put($key1, ['item' => 1], 300, ['date:2026-11-03']);
        $this->cacheManager->put($key2, ['item' => 2], 300, ['date:2026-11-03']);
        $this->cacheManager->put($key3, ['item' => 3], 300, ['date:2026-11-04']);

        $this->assertNotNull($this->cacheManager->get($key1));
        $this->assertNotNull($this->cacheManager->get($key2));
        $this->assertNotNull($this->cacheManager->get($key3));

        $cleared = $this->cacheManager->invalidateDate('2026-11-03');

        $this->assertEquals(2, $cleared);
        $this->assertNull($this->cacheManager->get($key1));
        $this->assertNull($this->cacheManager->get($key2));
        $this->assertNotNull($this->cacheManager->get($key3));
    }
}

