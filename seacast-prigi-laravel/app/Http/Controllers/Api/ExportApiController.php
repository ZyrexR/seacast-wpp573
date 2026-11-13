<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ScientificExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExportApiController extends Controller
{
    public const DEFAULT_DATE = '2026-11-03';

    public function __construct(
        protected ScientificExportService $exportService
    ) {}

    public function geojson(Request $request): Response
    {
        $validated = $this->validateExportRequest($request);
        $date = $validated['date'] ?? self::DEFAULT_DATE;
        $filters = $this->extractFilters($validated);

        $content = $this->exportService->exportGeoJson($date, $filters);
        $filename = sprintf('seacast_prigi_predictions_%s.geojson', $date);

        return response($content, 200, [
            'Content-Type' => 'application/geo+json; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function csv(Request $request): Response
    {
        $validated = $this->validateExportRequest($request);
        $date = $validated['date'] ?? self::DEFAULT_DATE;
        $filters = $this->extractFilters($validated);

        $content = $this->exportService->exportCsv($date, $filters);
        $filename = sprintf('seacast_prigi_predictions_%s.csv', $date);

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function gisPackage(Request $request): Response
    {
        $validated = $this->validateExportRequest($request);
        $date = $validated['date'] ?? self::DEFAULT_DATE;
        $filters = $this->extractFilters($validated);

        $content = $this->exportService->exportGisPackage($date, $filters);
        $filename = sprintf('seacast_prigi_gis_package_%s.zip', $date);

        return response($content, 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    protected function validateExportRequest(Request $request): array
    {
        return $request->validate([
            'date' => 'nullable|date_format:Y-m-d',
            'species' => 'nullable|in:cakalang,tuna_sirip_kuning,madidihang,tongkol_krai,tongkol,all',
            'min_hsi' => 'nullable|numeric|between:0,1',
            'dom_zone' => 'nullable|string',
            'bycatch' => 'nullable|in:GREEN,YELLOW,RED',
            'sector' => 'nullable|string',
            'hotspot_only' => 'nullable|in:true,false,1,0',
            'limit' => 'nullable|integer|min:1|max:10000',
        ]);
    }

    protected function extractFilters(array $validated): array
    {
        $filters = [];
        if (!empty($validated['sector'])) $filters['sector'] = $validated['sector'];
        if (!empty($validated['dom_zone'])) $filters['dom_zone'] = $validated['dom_zone'];
        if (!empty($validated['bycatch'])) $filters['bycatch'] = $validated['bycatch'];
        if (!empty($validated['hotspot_only'])) $filters['hotspot_only'] = true;
        if (!empty($validated['species']) && isset($validated['min_hsi'])) {
            $filters['species'] = $validated['species'];
            $filters['min_hsi'] = (float)$validated['min_hsi'];
        }
        if (!empty($validated['limit'])) $filters['limit'] = (int)$validated['limit'];

        return $filters;
    }
}

