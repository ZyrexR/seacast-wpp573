<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Class GeospatialCacheManager
 *
 * High-performance multi-tier caching manager for PostGIS geospatial queries,
 * daily predictions, GeoJSON feature collections, and analytical summaries.
 *
 * Implements:
 * - Deterministic hash-based cache keys with filter normalization
 * - Tag-index registry for cache drivers lacking native tag support (file, database)
 * - Transparent upgrade path to Redis when redis driver is provisioned
 * - ETag generation and HTTP 304 Not Modified support
 * - Cache warming and targeted invalidation
 */
class GeospatialCacheManager
{
    public const NAMESPACE_PREFIX = 'seacast:geo';
    public const DEFAULT_PREDICTIONS_TTL = 86400; // 24 hours (daily predictions are static per date)
    public const DEFAULT_SUMMARY_TTL = 86400;     // 24 hours
    public const DEFAULT_GRID_TTL = 604800;       // 7 days (canonical grids are immutable)
    public const VIEWPORT_QUERY_TTL = 3600;       // 1 hour for dynamic spatial bounding boxes
    public const PREDICTION_CACHE_VERSION = 'v2';
    public const SUMMARY_CACHE_VERSION = 'v2';
    public const GRID_CACHE_VERSION = 'v2';

    protected string $store;

    public function __construct()
    {
        // Use configured geospatial store, or default to application cache store
        $this->store = Config::get('cache.geospatial_store', Config::get('cache.default', 'file'));
    }

    /**
     * Get the active cache store name.
     */
    public function getStoreName(): string
    {
        return $this->store;
    }

    /**
     * Determine if active store supports native Laravel tags.
     */
    public function supportsNativeTags(): bool
    {
        $driver = Config::get("cache.stores.{$this->store}.driver", $this->store);
        return in_array($driver, ['redis', 'memcached', 'array'], true);
    }

    /**
     * Build deterministic cache key for daily prediction queries.
     */
    public function buildPredictionsKey(string $date, array $filters = []): string
    {
        $normalized = $this->normalizeFilters($filters);
        $filterHash = md5(json_encode($normalized));

        return sprintf('%s:pred:%s:%s:%s', self::NAMESPACE_PREFIX, self::PREDICTION_CACHE_VERSION, $date, $filterHash);
    }

    /**
     * Build deterministic cache key for daily summary.
     */
    public function buildSummaryKey(string $date): string
    {
        return sprintf('%s:summary:%s:%s', self::NAMESPACE_PREFIX, self::SUMMARY_CACHE_VERSION, $date);
    }

    /**
     * Build deterministic cache key for canonical grid queries.
     */
    public function buildGridKey(array $filters = []): string
    {
        $normalized = $this->normalizeFilters($filters);
        $filterHash = md5(json_encode($normalized));

        return sprintf('%s:grid:%s:%s', self::NAMESPACE_PREFIX, self::GRID_CACHE_VERSION, $filterHash);
    }

    /**
     * Retrieve item from cache or execute fallback and store.
     *
     * @param string $key
     * @param int $ttl
     * @param callable $callback
     * @param array $tags
     * @return array
     */
    public function remember(string $key, int $ttl, callable $callback, array $tags = []): array
    {
        $cached = $this->get($key);
        if ($cached !== null) {
            return $cached;
        }

        $data = $callback();
        $this->put($key, $data, $ttl, $tags);

        return $data;
    }

    /**
     * Retrieve cached payload.
     */
    public function get(string $key): ?array
    {
        try {
            return Cache::store($this->store)->get($key);
        } catch (\Throwable $e) {
            Log::warning("GeospatialCacheManager get failed for key {$key}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Store payload into cache with tag indexing.
     */
    public function put(string $key, array $data, int $ttl, array $tags = []): bool
    {
        try {
            // Include ETag in metadata for cache validation
            $etag = $this->generateEtag($data);
            $payload = [
                'etag' => $etag,
                'cached_at' => microtime(true),
                'data' => $data,
            ];

            $stored = Cache::store($this->store)->put($key, $payload, $ttl);

            if ($stored && !empty($tags)) {
                $this->registerTags($key, $tags, $ttl);
            }

            return $stored;
        } catch (\Throwable $e) {
            Log::warning("GeospatialCacheManager put failed for key {$key}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Store pre-serialized JSON payload alongside optional array for ultra-fast raw streaming.
     */
    public function putRaw(string $key, string $rawJson, ?array $arrayData, int $ttl, array $tags = []): bool
    {
        try {
            $etag = '"' . substr(hash('sha256', $rawJson), 0, 32) . '"';
            $payload = [
                'etag' => $etag,
                'cached_at' => microtime(true),
                'json' => $rawJson,
                'data' => $arrayData,
            ];

            $stored = Cache::store($this->store)->put($key, $payload, $ttl);

            if ($stored && !empty($tags)) {
                $this->registerTags($key, $tags, $ttl);
            }

            return $stored;
        } catch (\Throwable $e) {
            Log::warning("GeospatialCacheManager putRaw failed for key {$key}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Invalidate all cached entries for a given prediction date.
     */
    public function invalidateDate(string $date): int
    {
        return $this->invalidateTag('date:' . $date);
    }

    /**
     * Invalidate all cached entries for canonical grids.
     */
    public function invalidateCanonicalGrids(): int
    {
        return $this->invalidateTag('domain:canonical_grids');
    }

    /**
     * Invalidate all keys associated with a specific tag.
     */
    public function invalidateTag(string $tag): int
    {
        $registryKey = sprintf('%s:tag_idx:%s', self::NAMESPACE_PREFIX, $tag);
        $keys = Cache::store($this->store)->get($registryKey, []);

        if (!is_array($keys) || empty($keys)) {
            return 0;
        }

        $count = 0;
        foreach ($keys as $key) {
            if (Cache::store($this->store)->forget($key)) {
                $count++;
            }
        }

        Cache::store($this->store)->forget($registryKey);

        return $count;
    }

    /**
     * Flush all geospatial cache entries.
     */
    public function flushAll(): bool
    {
        try {
            $masterIndexKey = sprintf('%s:master_keys', self::NAMESPACE_PREFIX);
            $allKeys = Cache::store($this->store)->get($masterIndexKey, []);

            if (is_array($allKeys)) {
                foreach ($allKeys as $key) {
                    Cache::store($this->store)->forget($key);
                }
            }

            Cache::store($this->store)->forget($masterIndexKey);

            return true;
        } catch (\Throwable $e) {
            Log::error("GeospatialCacheManager flushAll error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate an HTTP ETag for response caching.
     */
    public function generateEtag(mixed $data): string
    {
        return '"' . substr(hash('sha256', serialize($data)), 0, 32) . '"';
    }

    /**
     * Register cache key under tag index registry.
     */
    protected function registerTags(string $key, array $tags, int $ttl): void
    {
        $masterIndexKey = sprintf('%s:master_keys', self::NAMESPACE_PREFIX);
        $masterKeys = Cache::store($this->store)->get($masterIndexKey, []);
        if (!in_array($key, $masterKeys, true)) {
            $masterKeys[] = $key;
            Cache::store($this->store)->put($masterIndexKey, $masterKeys, max($ttl, 86400 * 30));
        }

        foreach ($tags as $tag) {
            $registryKey = sprintf('%s:tag_idx:%s', self::NAMESPACE_PREFIX, $tag);
            $keys = Cache::store($this->store)->get($registryKey, []);
            if (!in_array($key, $keys, true)) {
                $keys[] = $key;
                Cache::store($this->store)->put($registryKey, $keys, max($ttl, 86400 * 30));
            }
        }
    }

    /**
     * Normalize query filters for deterministic hashing.
     */
    protected function normalizeFilters(array $filters): array
    {
        $normalized = [];

        foreach ($filters as $k => $v) {
            if ($v === null || $v === '' || $v === 'ALL') {
                continue;
            }

            if ($k === 'bbox' && is_array($v) && count($v) === 4) {
                // Round bbox coordinates to 4 decimal places (~11 meters precision)
                $normalized['bbox'] = array_map(fn($coord) => round((float)$coord, 4), $v);
                continue;
            }

            if ($k === 'min_hsi') {
                $normalized['min_hsi'] = round((float)$v, 2);
                continue;
            }

            if ($k === 'species') {
                $normalized['species'] = strtolower((string)$v);
                continue;
            }

            if ($k === 'limit') {
                $normalized['limit'] = (int)$v;
                continue;
            }

            if (is_bool($v)) {
                $normalized[$k] = $v;
                continue;
            }

            $normalized[$k] = (string)$v;
        }

        ksort($normalized);

        return $normalized;
    }
}
