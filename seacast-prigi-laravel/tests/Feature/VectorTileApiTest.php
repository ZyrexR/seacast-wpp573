<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Controllers\Api\TileApiController;

class VectorTileApiTest extends TestCase
{
    public function test_rejects_unsupported_layer(): void
    {
        $response = $this->get('/api/tiles/unsupported_layer/7/103/66.pbf');
        $response->assertStatus(404);
    }

    public function test_rejects_out_of_bounds_zoom(): void
    {
        $response = $this->get('/api/tiles/predictions/2/1/1.pbf');
        $response->assertStatus(400);
    }

    public function test_rejects_invalid_tile_coordinates(): void
    {
        $response = $this->get('/api/tiles/predictions/7/500/500.pbf');
        $response->assertStatus(400);
    }

    public function test_returns_valid_predictions_vector_tile(): void
    {
        $response = $this->get('/api/tiles/predictions/7/103/66.pbf');
        $this->assertContains($response->getStatusCode(), [200, 204]);
        $this->assertNotEmpty($response->headers->get('ETag'));
        $this->assertNotEmpty($response->headers->get('X-Cache'));
    }

    public function test_returns_valid_canonical_grids_vector_tile(): void
    {
        $response = $this->get('/api/tiles/canonical_grids/7/103/66.pbf');
        $this->assertContains($response->getStatusCode(), [200, 204]);
        $this->assertNotEmpty($response->headers->get('ETag'));
    }

    public function test_returns_valid_hotspots_vector_tile(): void
    {
        $response = $this->get('/api/tiles/hotspots/7/103/66.pbf');
        $this->assertContains($response->getStatusCode(), [200, 204]);
    }

    public function test_tile_warm_cache_hit_and_etag(): void
    {
        $uri = '/api/tiles/canonical_grids/7/103/66.pbf';

        // 1. Cold or initial request
        $res1 = $this->get($uri);
        $this->assertContains($res1->getStatusCode(), [200, 204]);
        $etag = $res1->headers->get('ETag');

        // 2. Warm request
        $res2 = $this->get($uri);
        $this->assertContains($res2->getStatusCode(), [200, 204]);
        $this->assertStringContainsString('HIT', (string)$res2->headers->get('X-Cache'));

        // 3. Conditional revalidation
        $res3 = $this->withHeaders(['If-None-Match' => $etag])->get($uri);
        $res3->assertStatus(304);
    }

    public function test_prediction_tiles_use_v2_cache_namespace_for_public_tct_aliases(): void
    {
        $response = $this->get('/api/tiles/predictions/7/103/66.pbf');

        $this->assertContains($response->getStatusCode(), [200, 204]);
        $this->assertSame('v2', TileApiController::CACHE_VERSION);
    }
}

