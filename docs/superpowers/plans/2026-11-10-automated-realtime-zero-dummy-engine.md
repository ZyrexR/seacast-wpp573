# Automated Real-Time Zero-Dummy Marine GIS Engine Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Membangun orkestrator pipeline perikanan pelagis SEACAST WPPNRI 573 yang sepenuhnya otomatis, berkemampuan real-time (BMKG per jam & frame satelit GEE terkini), mencakup seluruh 8.129 sel kanonikal tanpa data dummy, dan mentransformasi WebGIS menjadi antarmuka interaktif kelas dunia.

**Architecture:** Python GEE extraction package terhubung langsung dengan kredensial `seca-513505` untuk deteksi tanggal dinamis satelit, dipadukan dengan Laravel `BmkgMaritimService` untuk ingest cuaca live perairan WPP 573. Output disalurkan ke model ensemble ML (MaxEnt, RF, GLM), di-upsert ke PostgreSQL 17 / PostGIS, dan disajikan ke WebGIS Leaflet dengan layer partikel arus bergerak 60 FPS serta time-slider musiman.

**Tech Stack:** PHP 8.3 / Laravel 11, Python 3.14 / Earth Engine API (`ee`), scikit-learn / joblib, PostgreSQL 17 / PostGIS 3.6, Alpine.js, Tailwind CSS, Leaflet JS & HTML5 Canvas.

**Spec:** `docs/superpowers/specs/2026-11-10-dual-hub-dynamic-webgis-design.md`

## Global Constraints

- 100% Real/Authentic Data: Dilarang keras menggunakan data acak/sintetis (`simulate_daily_oceanography`).
- Dynamic Date Detection: Tanggal satelit GEE tidak boleh di-hardcode; harus mendeteksi frame NOAA OISST terakhir di server Google.
- Cloud-Free Chlorophyll: Komposit bergerak 8 hari dengan median reducer untuk eliminasi awan monsun tropis.
- Zero-Crash Hybrid Resilience: Jika PostgreSQL offline, sistem otomatis menyajikan fallback data cache tanpa melempar fatal error.
- Performance Floor: Animasi partikel arus harus berjalan lancar pada 60 FPS di browser tanpa membebani CPU.

## Review Focus

- Input NOAA OISST lag: Mengakui jeda latensi orbit (2-3 hari) dan mengikat tanggal prediksi secara jujur dan ilmiah.
- Tutupan awan ekstrim pada klorofil-a: Menguji bahwa tidak ada sel bernilai `NaN` pada 8.129 sel pasca-komposit 8 hari.
- Timeout koneksi BMKG: Menguji mekanisme graceful cache fallback (60 menit) jika server BMKG Pusat melambat.
- Alih pangkalan Cilacap: Menguji bahwa jarak tolak kapal dan lingkaran radius mil laut bergeser akurat dari pangkalan Cilacap.
- Performa rendering 8.129 poligon: Memastikan transisi layer dan animasi berjalan ringan di laptop standar.

---

### Task 1: BmkgMaritimService & Live Weather Integration

**Files:**
- Create: `seacast-prigi-laravel/app/Services/BmkgMaritimService.php`
- Modify: `seacast-prigi-laravel/app/Http/Controllers/Web/DashboardController.php`
- Modify: `seacast-prigi-laravel/routes/api.php`
- Test: `seacast-prigi-laravel/tests/Feature/BmkgMaritimServiceTest.php`

- [ ] Write failing test for `BmkgMaritimServiceTest` verifying live JSON fetching and caching for Prigi (`XO026`), Cilacap (`XM001`), and South Indian Ocean (`M.12`, `M.10`).
- [ ] Run test to verify failure.
- [ ] Implement `BmkgMaritimService.php` with 60-minute TTL cache, timeout handling, and safety evaluation.
- [ ] Expose `/api/bmkg/live` endpoint in `routes/api.php`.
- [ ] Inject BMKG live metrics into `DashboardController::dpiRadar()`.
- [ ] Run tests and verify all green.

---

### Task 2: GEE Dynamic Daily Extractor (Zero-Dummy)

**Files:**
- Create: `scripts/gee_daily_extractor.py`
- Modify: `scripts/gee_pipeline_transformer.py`
- Test: `scripts/test_gee_extractor.py`

- [ ] Write test verifying programmatic retrieval of the latest available NOAA OISST date and 8-day MODIS/Sentinel chlorophyll window.
- [ ] Implement `scripts/gee_daily_extractor.py` using `ee.Initialize(project='seca-513505')` to extract SST, gradient, Chl-a, depth, and wind for canonical 8,129 cells.
- [ ] Connect extractor output to `gee_pipeline_transformer.py` to run Ensemble SDM inference and upsert results directly into PostgreSQL `daily_predictions`.
- [ ] Execute test run and verify zero `NaN` values and valid PostgreSQL persistence.

---

### Task 3: Unified Console Sync Command (`seacast:sync-live`)

**Files:**
- Create: `seacast-prigi-laravel/app/Console/Commands/SyncLiveOceanDataCommand.php`
- Modify: `seacast-prigi-laravel/routes/console.php`
- Test: `seacast-prigi-laravel/tests/Feature/SyncLiveOceanDataCommandTest.php`

- [ ] Write failing test for `seacast:sync-live` command.
- [ ] Implement `SyncLiveOceanDataCommand.php` orchestrating:
  1. BMKG live fetch & cache warming.
  2. Latest GEE satellite extraction & ML inference.
  3. PostgreSQL upsert & geospatial cache invalidation.
- [ ] Register command in `routes/console.php` with daily 04:00 WIB schedule.
- [ ] Run tests and verify zero exit codes.

---

### Task 4: Interactive WebGIS Transformation (Current Streamlines & Time-Slider)

**Files:**
- Modify: `seacast-prigi-laravel/resources/views/dashboard/dpi-radar.blade.php`
- Modify: `seacast-prigi-laravel/resources/views/components/layouts/dashboard.blade.php`
- Test: Manual browser check & Laravel view assertions.

- [ ] Add live BMKG Weather Status Pill at top bar of `dpi-radar.blade.php`.
- [ ] Implement HTML5 Canvas Ocean Current Particle Streamline layer (rendering flowing current vectors over WPP 573).
- [ ] Add Seasonal Time-Slider Playback (Play/Pause 12-month upwelling transition using pre-aggregated authentic seasonal data).
- [ ] Implement Dual-Hub Quick Toggle: `[ PPN Prigi ]` vs `[ PPS Cilacap ]` with automatic camera flyTo, nautical range rings (12, 30, 60 NM), and client-side Haversine distance recalculation.
- [ ] Verify 60 FPS performance and zero lag on desktop and responsive screens.

---

### Task 5: End-to-End Verification & Local Demonstration

**Files:**
- Test: Run full suite and launch `php artisan serve`.

- [ ] Execute `php artisan seacast:sync-live` to populate latest data.
- [ ] Verify PostgreSQL `daily_predictions` contains fresh records.
- [ ] Open WebGIS in browser (`http://127.0.0.1:8000/dashboard/dpi-radar`) and confirm:
  - Fresh live BMKG weather card is visible and active.
  - Ocean current streamlines are flowing smoothly across South Java.
  - Switching between Prigi and Cilacap shifts camera and recalculates distances.
  - Time-slider plays seasonal upwelling smoothly.
