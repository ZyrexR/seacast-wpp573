<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Domain\ScientificData\Repositories\ScientificDataRepositoryInterface;
use App\Domain\ScientificData\Services\ScientificDataService;
use App\Services\GeospatialCacheManager;

class PredictionApiController extends Controller
{
    public const DEFAULT_PREDICTION_DATE = '2026-11-03';

    public function __construct(
        protected ScientificDataRepositoryInterface $repository,
        protected ScientificDataService $service,
        protected GeospatialCacheManager $cacheManager
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'nullable|date_format:Y-m-d',
            'format' => 'nullable|in:geojson,json,summary',
            'summary_only' => 'nullable|in:true,false,1,0',
            'species' => 'nullable|in:cakalang,madidihang,albakor,all',
            'min_hsi' => 'nullable|numeric|between:0,1',
            'dom_zone' => 'nullable|string',
            'bycatch' => 'nullable|in:GREEN,YELLOW,RED',
            'sector' => 'nullable|string',
            'bbox' => 'nullable|string',
            'hotspot_only' => 'nullable|in:true,false,1,0',
            'limit' => 'nullable|integer|min:1|max:10000',
        ]);

        $date = $validated['date'] ?? self::DEFAULT_PREDICTION_DATE;
        $isSummary = ($request->boolean('summary_only') || ($validated['format'] ?? '') === 'summary');

        if ($isSummary) {
            $cacheKey = $this->cacheManager->buildSummaryKey($date);
            $cached = $this->cacheManager->get($cacheKey);

            if ($cached !== null) {
                $etag = $cached['etag'];
                if ($request->header('If-None-Match') === $etag) {
                    return response()->json(null, 304, [
                        'ETag' => $etag,
                        'Cache-Control' => 'public, max-age=300, stale-while-revalidate=1800',
                        'X-Cache' => 'HIT-REVALIDATED',
                    ]);
                }

                return response()->json($cached['data'])
                    ->header('ETag', $etag)
                    ->header('X-Cache', 'HIT')
                    ->header('Cache-Control', 'public, max-age=300, stale-while-revalidate=1800');
            }

            $summary = $this->repository->getDailySummary($date);
            if (!$summary) {
                return response()->json([
                    'status' => 'ERROR',
                    'message' => "Tidak ada data prediksi untuk tanggal {$date}.",
                ], 404);
            }

            $this->cacheManager->put(
                $cacheKey,
                $summary,
                GeospatialCacheManager::DEFAULT_SUMMARY_TTL,
                ['domain:daily_predictions', 'date:' . $date]
            );
            $etag = $this->cacheManager->generateEtag($summary);

            return response()->json($summary)
                ->header('ETag', $etag)
                ->header('X-Cache', 'MISS')
                ->header('Cache-Control', 'public, max-age=300, stale-while-revalidate=1800');
        }

        // Parse filters
        $filters = [];
        if (!empty($validated['sector'])) {
            $filters['sector'] = $validated['sector'];
        }
        if (!empty($validated['dom_zone'])) {
            $filters['dom_zone'] = $validated['dom_zone'];
        }
        if (!empty($validated['bycatch'])) {
            $filters['bycatch'] = $validated['bycatch'];
        }
        if ($request->boolean('hotspot_only')) {
            $filters['hotspot_only'] = true;
        }
        if (!empty($validated['species']) && isset($validated['min_hsi'])) {
            $filters['species'] = $validated['species'];
            $filters['min_hsi'] = (float)$validated['min_hsi'];
        }
        if (!empty($validated['limit'])) {
            $filters['limit'] = (int)$validated['limit'];
        }
        if (!empty($validated['bbox'])) {
            $parts = array_map('floatval', explode(',', $validated['bbox']));
            if (count($parts) === 4) {
                $filters['bbox'] = $parts;
            }
        }

        $cacheKey = $this->cacheManager->buildPredictionsKey($date, $filters);
        $cached = $this->cacheManager->get($cacheKey);

        if ($cached !== null) {
            $etag = $cached['etag'];
            if ($request->header('If-None-Match') === $etag) {
                return response()->json(null, 304, [
                    'ETag' => $etag,
                    'Cache-Control' => 'public, max-age=300, stale-while-revalidate=1800',
                    'X-Cache' => 'HIT-REVALIDATED',
                ]);
            }

            return response()->json($cached['data'])
                ->header('ETag', $etag)
                ->header('X-Cache', 'HIT')
                ->header('Cache-Control', 'public, max-age=300, stale-while-revalidate=1800');
        }

        $geoJson = $this->service->getDailyGeoJson($date, $filters);

        $ttl = !empty($filters['bbox'])
            ? GeospatialCacheManager::VIEWPORT_QUERY_TTL
            : GeospatialCacheManager::DEFAULT_PREDICTIONS_TTL;

        $this->cacheManager->put(
            $cacheKey,
            $geoJson,
            $ttl,
            ['domain:daily_predictions', 'date:' . $date]
        );
        $etag = $this->cacheManager->generateEtag($geoJson);

        return response()->json($geoJson)
            ->header('ETag', $etag)
            ->header('X-Cache', 'MISS')
            ->header('Cache-Control', 'public, max-age=300, stale-while-revalidate=1800');
    }
}
