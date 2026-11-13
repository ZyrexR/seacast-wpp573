<?php

namespace App\Services;

use App\Domain\ScientificData\Repositories\ScientificDataRepositoryInterface;
use App\Domain\ScientificData\Services\ScientificDataService;
use ZipArchive;

class ScientificExportService
{
    public const DEFAULT_DATE = '2026-11-03';

    public function __construct(
        protected ScientificDataRepositoryInterface $repository,
        protected ScientificDataService $scientificDataService
    ) {}

    /**
     * Generate standard RFC 7946 OGC CRS84 GeoJSON string.
     */
    public function exportGeoJson(string $date, array $filters = []): string
    {
        $geoJson = $this->scientificDataService->getDailyGeoJson($date, $filters);
        return json_encode($geoJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Generate UTF-8 CSV with standard header row.
     */
    public function exportCsv(string $date, array $filters = []): string
    {
        $features = $this->repository->getDailyGeoJsonFeatures($date, $filters);

        $output = fopen('php://temp', 'r+');
        // Add UTF-8 Byte Order Mark (BOM) for seamless Excel & QGIS ingestion
        fwrite($output, "\xEF\xBB\xBF");

        // Header columns
        fputcsv($output, [
            'cell_id',
            'cell_class',
            'center_longitude',
            'center_latitude',
            'depth_m',
            'dist_port_km',
            'dist_port_nm',
            'is_shelf_break',
            'is_prigi_inshore',
            'is_prigi_offshore',
            'sector',
            'prediction_date',
            'hsi_cakalang',
            'hsi_madidihang',
            'hsi_tongkol',
            'is_hotspot',
            'dom_zone',
            'bycatch_traffic_light',
            'sst_c',
            'chla_mg_m3',
            'wave_height_m',
            'wind_speed_ms',
            'safety_advisory'
        ]);

        foreach ($features as $f) {
            $p = $f['properties'];
            fputcsv($output, [
                $p['cell_id'] ?? '',
                $p['cell_class'] ?? '',
                $p['center_lon'] ?? '',
                $p['center_lat'] ?? '',
                $p['depth_m'] ?? '',
                $p['dist_port_km'] ?? '',
                $p['dist_port_nm'] ?? '',
                ($p['is_shelf_break'] ?? false) ? 'true' : 'false',
                ($p['is_prigi_inshore'] ?? false) ? 'true' : 'false',
                ($p['is_prigi_offshore'] ?? false) ? 'true' : 'false',
                $p['sector'] ?? '',
                $p['prediction_date'] ?? $date,
                $p['hsi_cakalang'] ?? '',
                $p['hsi_madidihang'] ?? '',
                $p['hsi_tongkol'] ?? '',
                ($p['is_hotspot'] ?? false) ? 'true' : 'false',
                $p['dom_zone'] ?? '',
                $p['bycatch_traffic_light'] ?? '',
                $p['sst_c'] ?? '',
                $p['chla_mg_m3'] ?? '',
                $p['wave_height_m'] ?? '',
                $p['wind_speed_ms'] ?? '',
                $p['safety_advisory'] ?? ''
            ]);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }

    /**
     * Generate GIS-ready ZIP archive package containing GeoJSON, CSV, PRJ projection, CPG, and Metadata README.
     */
    public function exportGisPackage(string $date, array $filters = []): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'seacast_gis_');
        $zip = new ZipArchive();
        $zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // 1. GeoJSON layer
        $geoJson = $this->exportGeoJson($date, $filters);
        $zip->addFromString("seacast_prigi_{$date}.geojson", $geoJson);

        // 2. CSV tabular data
        $csv = $this->exportCsv($date, $filters);
        $zip->addFromString("seacast_prigi_{$date}.csv", $csv);

        // 3. ESRI WKT Projection file (.prj)
        $prjWkt = 'GEOGCS["WGS 84",DATUM["WGS_1984",SPHEROID["WGS 84",6378137,298.257223563]],PRIMEM["Greenwich",0],UNIT["degree",0.0174532925199433]]';
        $zip->addFromString("seacast_prigi_{$date}.prj", $prjWkt);

        // 4. Codepage specification (.cpg)
        $zip->addFromString("seacast_prigi_{$date}.cpg", "UTF-8\n");

        // 5. Scientific Metadata & Usage README
        $metadata = $this->generateMetadataReadme($date, $filters);
        $zip->addFromString("METADATA_README.txt", $metadata);

        $zip->close();

        $content = file_get_contents($tempFile);
        @unlink($tempFile);

        return $content;
    }

    /**
     * Build scientific metadata text.
     */
    protected function generateMetadataReadme(string $date, array $filters): string
    {
        return <<<TEXT
================================================================================
SEACAST — SCIENTIFIC DATASET EXPORT METADATA
================================================================================
Project: SEACAST (Scientific Ocean Intelligence & Fisheries Decision Support)
Location: Pelabuhan Perikanan Nusantara (PPN) Prigi, Trenggalek, Jawa Timur
Spatial Domain: WPPNRI 573 (Southern Java, Indian Ocean)
Date of Prediction Snapshot: {$date}
Coordinate Reference System (CRS): EPSG:4326 (WGS 84 Geodetic Coordinates)
Canonical Resolution: 0.10 degrees (~11.1 km spatial grid)
Total Canonical Grid Domain: 8,129 polygons
Export Generated At: 2026-11-04 19:40:00 WIB

--------------------------------------------------------------------------------
DATA PROVENANCE & METHODOLOGY:
--------------------------------------------------------------------------------
1. Species Habitat Suitability Index (HSI):
   Ensemble Species Distribution Model (Random Forest, Extra Trees, Gradient Boosting, MaxEnt).
   Species Covered:
   - Cakalang / Skipjack Tuna (Katsuwonus pelamis)
   - Tuna Sirip Kuning / Yellowfin Tuna (Thunnus albacares)
   - Tongkol Krai / Frigate Tuna (Auxis thazard)

2. Dynamic Ocean Management (DOM) Zones:
   - Z1_PFG_INSHORE: Distance <= 12 NM, HSI >= 0.55
   - Z2_PFG_OFFSHORE: Distance 12 - 60 NM, HSI >= 0.55
   - Z3_FEEDING_CORE: Thermal Front Gradient >= 0.035 °C/km, Chl-a >= 0.40 mg/m³
   - Z4_NURSERY_GROUND: Depth <= 150 m, Distance <= 15 NM (Vessel safety advisory)
   - Z5_CONSERVATION_HOTSPOT: Depth 150-1200m, SST <= 26.5°C, Gradient >= 0.035 °C/km

3. Bycatch Traffic Light System:
   Bathymetric slope screening (150 - 1,200m) conforming to IOTC conservation resolutions.

4. Wave Height & Wind Speed:
   Calibrated seasonal oceanographic simulation (BMKG Local Fallback Benchmark).

--------------------------------------------------------------------------------
GIS SOFTWARE COMPATIBILITY:
--------------------------------------------------------------------------------
- QGIS: Open .geojson directly via Layer -> Add Vector Layer.
- ArcGIS Pro / ArcMap: Use "JSON to Features" tool or load .csv via "XY Table To Point".
- MapLibre / Leaflet: Directly consume .geojson via L.geoJSON or map.addSource().

================================================================================
TEXT;
    }
}

