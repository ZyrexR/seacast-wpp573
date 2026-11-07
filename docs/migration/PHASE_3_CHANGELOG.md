# SEACAST PRIGI V3 — PHASE 3 CHANGELOG
## Comprehensive Record of Modifications, Additions, and Verifications

**Target Version:** SEACAST PRIGI v3.0-rc1  
**Release Date:** 2026-11-04  
**Architecture:** Laravel 13 Modular Monolith (Option 1: Native Blade + Alpine.js + Tailwind CSS)  

---

### [Phase 3.0] — Repository Cleanup & Technical Debt Reduction
- **Added:** Archive bundle `archive/phase_1_scratch_scripts.zip` containing retired debugging scripts.
- **Removed:** 6 temporary scratch scripts (`test_db.php`, `test_env.php`, `test_gis.php`, `test_postgis.php`, `verify_postgis.php`, `test_view.php`).
- **Removed:** Stale Python bytecode directories (`scripts/__pycache__`) and default Laravel welcome view (`resources/views/welcome.blade.php`).
- **Verified:** Protection of Golden Assets (`data/`, `notebooks/`, `legacy/`, `tests/fixtures/`, `database/sql/`) and legacy Next.js application (`web/`).

---

### [Phase 3.1] — Geospatial Performance & Multi-Tier Caching
- **Added:** `App\Services\GeospatialCacheManager` with custom Tag-Index Registry for file and database drivers lacking native cache tagging.
- **Added:** Deterministic query parameter sorting and MD5 hashing for spatial cache keys.
- **Added:** HTTP 304 Not Modified ETag revalidation header support across `/api/predictions/daily` and `/api/grids/canonical`.
- **Added:** Artisan commands:
  - `php artisan seacast:cache:warm` (pre-populates common spatial queries).
  - `php artisan seacast:cache:clear` (invalidates geospatial cache tag indices).
  - `php artisan seacast:benchmark` (measures empirical latencies across multiple query profiles).
- **Added:** `Tests\Feature\GeospatialCacheTest` (8 tests, 48 assertions).

---

### [Phase 3.2] — PostGIS MVT Vector Tiles & Native mapcn UI Integration
- **Added:** `App\Http\Controllers\Api\TileApiController` implementing PostGIS MVT vector tile generation (`/api/tiles/{layer}/{z}/{x}/{y}.pbf`) utilizing `ST_TileEnvelope`, `ST_AsMVTGeom`, and `ST_AsMVT`.
- **Added:** Native mapcn Blade components:
  - `resources/views/components/map/map.blade.php` (MapLibre GL JS wrapper with full WebGL lifecycle management).
  - `resources/views/components/map/map-controls.blade.php` (Glassmorphic zoom, pitch, bearing, and reset buttons).
  - `resources/views/components/map/map-layers.blade.php` (Interactive layer toggles for HSI, hotspots, DOM zones, bathymetry).
  - `resources/views/components/map/map-legend.blade.php` (Scientific gradient bar and DOM zoning legend).
  - `resources/views/components/map/map-inspector.blade.php` (Floating HUD with cell metrics and multi-species HSI bars).
- **Updated:** `resources/views/dashboard/dpi-radar.blade.php` to integrate native mapcn components.
- **Added:** `Tests\Feature\VectorTileApiTest` (7 tests, 13 assertions).

---

### [Phase 3.3] — Fleet & Port Landing Analytics
- **Added:** `App\Domain\Fleet\Services\FleetAnalyticsService` providing multi-year aggregation, GT vessel breakdown, and nominal CPUE proxies.
- **Added:** `App\Http\Controllers\Api\FleetApiController` (`/api/fleet/summary`, `/api/fleet/landings`, `/api/fleet/vessels`).
- **Added:** `App\Http\Controllers\Web\FleetAnalyticsController` (`GET /dashboard/fleet-analytics`).
- **Added:** `resources/views/dashboard/fleet-analytics.blade.php` (KPI summary cards, GT fleet breakdown, 11-year empirical table, monthly seasonality matrix, and scientific CPUE caveats panel).
- **Added:** `Tests\Feature\FleetAnalyticsTest` (5 tests, 23 assertions).

---

### [Phase 3.4] — Scientific Export Engine
- **Added:** `App\Services\ScientificExportService` supporting RFC 7946 GeoJSON, RFC 4180 UTF-8 BOM CSV, and ESRI/QGIS ZIP packages.
- **Added:** `App\Http\Controllers\Api\ExportApiController` (`/api/export/geojson`, `/api/export/csv`, `/api/export/gis-package`).
- **Added:** Automatic inclusion of ESRI `.prj` (EPSG:4326 WKT), `.cpg` (UTF-8), and `METADATA_README.txt` in GIS export packages.
- **Added:** `Tests\Feature\ScientificExportTest` (4 tests, 26 assertions).

---

### Master Test Suite Status
- **Total Tests:** 99
- **Total Assertions:** 1,959
- **Pass Rate:** 100%
- **Regressions:** 0

