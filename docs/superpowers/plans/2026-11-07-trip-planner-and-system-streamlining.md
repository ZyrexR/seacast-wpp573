# SEACAST — Trip Planner & System Streamlining Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Transform SEACAST into a unified operational decision support platform ("Menangkap Ikan Terencana") by streamlining the dashboard from 11 modules into 3 core pillars, integrating peer-reviewed HSI models (Madidihang, Cakalang, Tongkol), ingesting PPS Cilacap 10-year data, implementing Permen KP No. 59/2020 legal checks, and launching a dedicated Trip Planner with official PDF sailing advisories.

**Architecture:** Pure Laravel 13 Modular Monolith with Blade, Tailwind CSS v4, Alpine.js, PostgreSQL 17 / PostGIS, and SQLite. A dedicated `TripPlanningService` calculates fuel, wave/moon safety, GT boundaries, and HSI-driven catch projections, while a PDF generator creates printable sailing advisories with static route snapshots and precision GPS coordinates.

**Tech Stack:** PHP 8.3+, Laravel 13, PostgreSQL 17 + PostGIS, SQLite, Blade, Tailwind CSS v4, Alpine.js, Leaflet, DomPDF, Python 3.14.

**Spec:** [docs/superpowers/specs/2026-11-06-trip-planner-and-system-streamlining-design.md](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/docs/superpowers/specs/2026-11-06-trip-planner-and-system-streamlining-design.md)

## Global Constraints

- Pure Laravel 13 Modular Monolith: No React, Next.js, Vue, or external Node.js runtime.
- Maintain documentation integrity: Preserve existing comments and docstrings.
- Brand standards: Strictly use "SEACAST" and "SEACAST — Scientific Ocean Intelligence & Fisheries Decision Support".
- Subsidized fuel benchmark: Rp 6.800 / Liter with mandatory +15% safety reserve.
- Official legal basis: Permen KP No. 59/PERMEN-KP/2020 for Jalur I (0–4 NM), Jalur II (4–12 NM), and Jalur III (>12 NM / ZEE).
- All automated tests must pass with 0 errors and 0 failures at every task.

## Review Focus

1. **Vessel < 10 GT attempting to sail beyond 12 NM:** Must trigger `WARNING_OUT_OF_LEGAL_RANGE` citing Permen KP 59/2020 Pasal 3 & Lampiran.
2. **Vessel > 30 GT attempting to fish within 12 NM (Jalur I/II):** Must trigger legal violation warning restricting >30 GT to Jalur III / ZEE.
3. **Future date selected outside database prediction bounds:** System must smoothly fallback to latest known oceanographic baseline with dynamic lunar phase calculation without null exceptions.
4. **Extreme wave height (> 2.5 m):** Must trigger `BAHAYA_GELOMBANG_EKSTREM` and disallow "SAFE" sailing clearance on the PDF slip.
5. **Port-scoped Admin Access:** Admin of PPN Prigi must only see Prigi departure drafts, PPS Cilacap admin only Cilacap, and Superadmin all.

---

### Task 1: System Streamlining & Dashboard Navigation Consolidation

**Files:**
- Modify: `seacast-prigi-laravel/routes/web.php`
- Modify: `seacast-prigi-laravel/resources/views/components/dashboard/sidebar.blade.php`
- Modify: `seacast-prigi-laravel/app/Http/Controllers/Web/DashboardController.php`
- Modify: `seacast-prigi-laravel/tests/Feature/ComprehensiveRouteCoverageTest.php`
- Modify: `seacast-prigi-laravel/tests/Feature/FrontendComponentsTest.php`
- Delete/Archive: `seacast-prigi-laravel/resources/views/dashboard/oceanographic-explorer.blade.php`, `species-models.blade.php`, `time-machine.blade.php`, `dom-management.blade.php`, `bycatch-safety.blade.php`, `data-monitoring.blade.php`

**Interfaces:**
- Consumes: Existing Laravel 13 routing and role-based middleware.
- Produces: Clean 5-item sidebar navigation (Ringkasan Eksekutif, Radar Geospasial WPPNRI 573, Rencana Melaut & Kalkulator, Analitik Armada & Pendaratan, Status Sistem).

- [ ] **Step 1: Write the failing route test for the streamlined dashboard**
  Update `ComprehensiveRouteCoverageTest.php` to assert obsolete routes return 404 or redirect, and verify the new `/dashboard/trip-planner` route exists.
- [ ] **Step 2: Run test to verify it fails**
  Run: `cd seacast-prigi-laravel; php artisan test --filter=ComprehensiveRouteCoverageTest`
  Expected: FAIL (route `/dashboard/trip-planner` not yet defined).
- [ ] **Step 3: Update `routes/web.php` and `DashboardController.php`**
  Remove routes: `.oceanographic-explorer`, `.species-models`, `.time-machine`, `.dom-management`, `.bycatch-safety`, `.data-monitoring`. Register placeholder for `route('dashboard.trip-planner')`.
- [ ] **Step 4: Update `sidebar.blade.php`**
  Prune navigation links to the 5 consolidated items with official icons and badge styling.
- [ ] **Step 5: Safely remove obsolete Blade views and run tests**
  Run: `cd seacast-prigi-laravel; php artisan test`
  Expected: PASS (all route and frontend tests green).
- [ ] **Step 6: Commit**
  ```bash
  git add seacast-prigi-laravel/routes/web.php seacast-prigi-laravel/resources/views/components/dashboard/sidebar.blade.php seacast-prigi-laravel/app/Http/Controllers/Web/DashboardController.php seacast-prigi-laravel/tests/
  git commit -m "refactor: streamline dashboard navigation to 3 core operational modules"
  ```

---

### Task 2: Multi-Species HSI Recalibration & Database Migration (Tongkol Integration)

**Files:**
- Create: `seacast-prigi-laravel/database/migrations/2026_11_07_000001_add_tongkol_hsi_to_predictions_tables.php`
- Modify: `seacast-prigi-laravel/app/Models/DailyPrediction.php`
- Modify: `seacast-prigi-laravel/app/Models/GridMonthlyPrediction.php`
- Modify: `seacast-prigi-laravel/app/Domain/ScientificData/Services/SimulationService.php`
- Modify: `scripts/daily_inference.py`
- Test: `seacast-prigi-laravel/tests/Unit/ScientificHsiFormulaTest.php`

**Interfaces:**
- Consumes: PostGIS 8,129 cells, environmental variables (SST, Chl-a, Bathymetry, Waves).
- Produces: `hsi_tongkol`, `hsi_madidihang`, `hsi_cakalang` columns calculated from peer-reviewed AMM/Gaussian formulas (*Yati et al., 2024; Zainuddin et al., 2023; Syah et al., 2023*).

- [ ] **Step 1: Write failing unit test for scientific HSI formulas**
  Create `ScientificHsiFormulaTest.php` asserting that:
  - Tongkol HSI is optimal in SST 27.0–30.5°C, Chl-a 0.20–0.50 mg/m³, and depth < 200m.
  - Madidihang HSI is optimal in SST 23.0–31.5°C, oceanic Chl-a, and depth > 200m.
  - Cakalang HSI prioritizes Chl-a (50%) and SST 28.5–31.5°C.
- [ ] **Step 2: Run test to verify it fails**
  Run: `cd seacast-prigi-laravel; php artisan test --filter=ScientificHsiFormulaTest`
  Expected: FAIL (`hsi_tongkol` or method missing).
- [ ] **Step 3: Create and run database migration for `hsi_tongkol`**
  Add `hsi_tongkol` (float, nullable) to `daily_predictions` and `grid_monthly_predictions`.
  Run: `cd seacast-prigi-laravel; php artisan migrate`
- [ ] **Step 4: Implement literature formulas in `SimulationService.php` and `scripts/daily_inference.py`**
  Add `calcTongkolHsi()`, calibrate `calcCakalangHsi()`, `calcMadidihangHsi()`.
- [ ] **Step 5: Run tests to verify they pass**
  Run: `cd seacast-prigi-laravel; php artisan test --filter=ScientificHsiFormulaTest`
  Expected: PASS.
- [ ] **Step 6: Commit**
  ```bash
  git add seacast-prigi-laravel/database/migrations/ seacast-prigi-laravel/app/Models/ seacast-prigi-laravel/app/Domain/ScientificData/ seacast-prigi-laravel/tests/Unit/ScientificHsiFormulaTest.php scripts/daily_inference.py
  git commit -m "feat: add scientific HSI formulas for Tongkol, Madidihang, and Cakalang"
  ```

---

### Task 3: Multi-Port Fleet Analytics Ingestion (PPS Cilacap & PPN Prigi)

**Files:**
- Create: `seacast-prigi-laravel/database/migrations/2026_11_07_000002_create_port_landings_table.php`
- Create: `seacast-prigi-laravel/app/Models/PortLanding.php`
- Create: `seacast-prigi-laravel/database/seeders/PpsCilacapLandingsSeeder.php`
- Modify: `seacast-prigi-laravel/app/Domain/Fleet/Services/FleetAnalyticsService.php`
- Modify: `seacast-prigi-laravel/app/Http/Controllers/Web/FleetAnalyticsController.php`
- Modify: `seacast-prigi-laravel/resources/views/dashboard/fleet-analytics.blade.php`
- Test: `seacast-prigi-laravel/tests/Feature/MultiPortFleetAnalyticsTest.php`

**Interfaces:**
- Consumes: `database/data/raw/pps_cilacap_statistics_2016_2025.csv` and `prigi_port_landings`.
- Produces: Unified port analytics endpoint supporting `port_code` (`PPN_PRIGI` vs `PPS_CILACAP`).

- [ ] **Step 1: Write failing feature test for multi-port fleet analytics**
  Assert that `/dashboard/fleet-analytics?port=PPS_CILACAP` returns 200, displays Cilacap landings (Cakalang, Madidihang, Tongkol Krai), and includes fleet GT composition.
- [ ] **Step 2: Run test to verify it fails**
  Run: `cd seacast-prigi-laravel; php artisan test --filter=MultiPortFleetAnalyticsTest`
  Expected: FAIL (port query parameter not supported).
- [ ] **Step 3: Create migration and seeder for PPS Cilacap data**
  Parse `database/data/raw/pps_cilacap_statistics_2016_2025.csv` and seed 120 months (2016–2025) of monthly landings, trips, and fleet records.
  Run: `cd seacast-prigi-laravel; php artisan db:seed --class=PpsCilacapLandingsSeeder`
- [ ] **Step 4: Update `FleetAnalyticsService.php` and controller**
  Add port scoping (`getAnnualLandingsSummary($portCode)`, `getFleetComposition($portCode)`).
- [ ] **Step 5: Add Port Selector to `fleet-analytics.blade.php`**
  Add clean dropdown / pill buttons allowing instant switching between **PPN Prigi** and **PPS Cilacap**.
- [ ] **Step 6: Run tests to verify they pass**
  Run: `cd seacast-prigi-laravel; php artisan test --filter=MultiPortFleetAnalyticsTest`
  Expected: PASS.
- [ ] **Step 7: Commit**
  ```bash
  git add seacast-prigi-laravel/database/ seacast-prigi-laravel/app/Models/PortLanding.php seacast-prigi-laravel/app/Domain/Fleet/ seacast-prigi-laravel/resources/views/dashboard/fleet-analytics.blade.php seacast-prigi-laravel/tests/
  git commit -m "feat: ingest 10-year PPS Cilacap dataset and add multi-port fleet analytics"
  ```

---

### Task 4: DPI Radar Seamless Contour & Focal Peak Waypoint Extraction

**Files:**
- Modify: `seacast-prigi-laravel/resources/views/dashboard/dpi-radar.blade.php`
- Modify: `seacast-prigi-laravel/app/Http/Controllers/Web/DashboardController.php`
- Modify: `seacast-prigi-laravel/app/Domain/ScientificData/Services/ScientificDataService.php`
- Test: `seacast-prigi-laravel/tests/Feature/DpiRadarSeamlessRenderingTest.php`

**Interfaces:**
- Consumes: PostGIS GeoJSON features with multi-species HSI and DOM zones.
- Produces: Seamless vector rendering (`stroke: false`, multi-stop gradient) + focal peak GPS waypoint coordinates with "Rencanakan Melaut" action button.

- [ ] **Step 1: Write failing test for DPI radar data enhancements**
  Assert that radar data payload provides Tongkol HSI, focal waypoint coordinates in DD°MM'SS", and Permen KP 12 NM legal line flag.
- [ ] **Step 2: Run test to verify it fails**
  Run: `cd seacast-prigi-laravel; php artisan test --filter=DpiRadarSeamlessRenderingTest`
  Expected: FAIL.
- [ ] **Step 3: Update `dpi-radar.blade.php` rendering logic**
  - Disable grid outline borders (`stroke: false`, `weight: 0`) and configure smooth continuous colormap.
  - Add layer toggles for: [x] HSI (Tuna / Cakalang / Tongkol), [x] DOM 5-Zones, [x] Permen KP 59/2020 12 NM line, [x] Bycatch.
  - In popup, display formatted GPS coordinates (`08° 24' 15" S, 111° 42' 30" E`) and button linking to `/dashboard/trip-planner?lat=...&lon=...&port=...`.
- [ ] **Step 4: Run tests to verify they pass**
  Run: `cd seacast-prigi-laravel; php artisan test --filter=DpiRadarSeamlessRenderingTest`
  Expected: PASS.
- [ ] **Step 5: Commit**
  ```bash
  git add seacast-prigi-laravel/resources/views/dashboard/dpi-radar.blade.php seacast-prigi-laravel/app/Http/Controllers/Web/DashboardController.php seacast-prigi-laravel/tests/
  git commit -m "feat: implement seamless contour rendering and precision GPS waypoints in DPI Radar"
  ```

---

### Task 5: Trip Planning Calculation Engine & Legal Compliance (Permen KP 59/2020)

**Files:**
- Create: `seacast-prigi-laravel/app/Domain/Trip/Services/TripPlanningService.php`
- Create: `seacast-prigi-laravel/tests/Unit/TripPlanningServiceTest.php`

**Interfaces:**
- Consumes: Departure port (Prigi, Cilacap, Palabuhanratu), GT class (<10 GT, 10–20 GT, 20–30 GT), target waypoint (Lat/Lon), date, commodity.
- Produces: Trip calculation array: distance NM/km, bearing, fuel L/Rp (+15%), Permen KP 59/2020 compliance status, wave/moon safety advisory, catch CPUE kg, gross revenue, net margin.

- [ ] **Step 1: Write failing unit tests for `TripPlanningService`**
  Cover:
  - Fuel calculation for <10 GT (1.5 L/NM * round trip * 1.15).
  - Permen KP 59/2020 violation: Vessel <10 GT targeting 15 NM -> `WARNING_OUT_OF_LEGAL_RANGE`.
  - Permen KP 59/2020 violation: Vessel >30 GT targeting 5 NM -> `WARNING_OUT_OF_LEGAL_RANGE` (must be in Jalur III).
  - Wave height safety: wave > 2.5m -> `BAHAYA_GELOMBANG_EKSTREM`.
  - Moon phase calculation: returns phase name and pelagic behavior impact.
- [ ] **Step 2: Run test to verify it fails**
  Run: `cd seacast-prigi-laravel; php artisan test --filter=TripPlanningServiceTest`
  Expected: FAIL (`TripPlanningService` does not exist).
- [ ] **Step 3: Implement `TripPlanningService.php`**
  Implement Great Circle / Rhumb-line formulas, fuel economics, Permen KP 59/2020 regulatory ladder, astronomical lunar phase calculation, and HSI-driven catch projection.
- [ ] **Step 4: Run tests to verify they pass**
  Run: `cd seacast-prigi-laravel; php artisan test --filter=TripPlanningServiceTest`
  Expected: PASS.
- [ ] **Step 5: Commit**
  ```bash
  git add seacast-prigi-laravel/app/Domain/Trip/ seacast-prigi-laravel/tests/Unit/TripPlanningServiceTest.php
  git commit -m "feat: implement TripPlanningService with fuel economics and Permen KP 59/2020 compliance"
  ```

---

### Task 6: Trip Planner UI & Port Admin Sailing Logbook

**Files:**
- Create: `seacast-prigi-laravel/database/migrations/2026_11_07_000003_create_trip_plans_table.php`
- Create: `seacast-prigi-laravel/app/Models/TripPlan.php`
- Create: `seacast-prigi-laravel/app/Http/Controllers/Web/TripPlannerController.php`
- Create: `seacast-prigi-laravel/resources/views/dashboard/trip-planner.blade.php`
- Test: `seacast-prigi-laravel/tests/Feature/TripPlannerWorkflowTest.php`

**Interfaces:**
- Consumes: `TripPlanningService`, user authentication, port profiles.
- Produces: Interactive trip planner cockpit, database archival of plans (`trip_plans`), and Port Admin Sailing Logbook table.

- [ ] **Step 1: Write failing feature test for trip planner workflow**
  Assert that an authenticated user can:
  - Submit trip plan parameters and receive instant calculation results.
  - Save the trip plan and receive a unique reference code (`SEACAST-PRG-YYYYMM-XXXX`).
  - View their saved trips in "Riwayat Melaut Saya".
  - Port admin can view all departures from their assigned port in "Buku Log Pelabuhan".
- [ ] **Step 2: Run test to verify it fails**
  Run: `cd seacast-prigi-laravel; php artisan test --filter=TripPlannerWorkflowTest`
  Expected: FAIL.
- [ ] **Step 3: Create `trip_plans` migration and model**
  Schema includes: `id`, `reference_no`, `user_id`, `port_code`, `vessel_name`, `vessel_gt_class`, `departure_date`, `target_species`, `target_lat`, `target_lon`, `distance_nm`, `fuel_liters`, `fuel_cost_rp`, `projected_catch_kg`, `wave_height_m`, `safety_status`.
  Run: `cd seacast-prigi-laravel; php artisan migrate`
- [ ] **Step 4: Implement `TripPlannerController.php` and `trip-planner.blade.php`**
  Build clean 2-column interface:
  - Left: Departure Port selector, Date picker, Vessel GT, Commodity, Waypoint picker.
  - Right: Real-time decision cards (Solar L/Rp, Permen KP status badge, Wave & Moon Phase safety badge, Net Margin).
  - Bottom: Action buttons ("Simpan Rencana", "Unduh PDF Panduan Melaut").
  - Tabs: "Kalkulator Baru", "Riwayat Melaut Saya", "Buku Log Pelabuhan (Admin)".
- [ ] **Step 5: Run tests to verify they pass**
  Run: `cd seacast-prigi-laravel; php artisan test --filter=TripPlannerWorkflowTest`
  Expected: PASS.
- [ ] **Step 6: Commit**
  ```bash
  git add seacast-prigi-laravel/database/ seacast-prigi-laravel/app/Models/TripPlan.php seacast-prigi-laravel/app/Http/Controllers/Web/TripPlannerController.php seacast-prigi-laravel/resources/views/dashboard/trip-planner.blade.php seacast-prigi-laravel/tests/
  git commit -m "feat: create Trip Planner cockpit and Port Admin Sailing Logbook"
  ```

---

### Task 7: Official PDF Sailing Advisory Slip Generator

**Files:**
- Create: `seacast-prigi-laravel/resources/views/pdf/sailing-advisory.blade.php`
- Modify: `seacast-prigi-laravel/app/Http/Controllers/Web/TripPlannerController.php`
- Modify: `seacast-prigi-laravel/composer.json` (add `barryvdh/laravel-dompdf` if not installed)
- Test: `seacast-prigi-laravel/tests/Feature/SailingAdvisoryPdfExportTest.php`

**Interfaces:**
- Consumes: Saved `TripPlan` model instance.
- Produces: Official, printable A4 PDF stream (`application/pdf`) with SEACAST branding, QR verification token, waypoint GPS, route snapshot graphic, and Syahbandar/Nahkoda signature blocks.

- [ ] **Step 1: Write failing feature test for PDF generation**
  Assert that `GET /dashboard/trip-planner/{reference_no}/pdf` returns status 200, header `Content-Type: application/pdf`, and valid binary content containing the reference number and vessel name.
- [ ] **Step 2: Run test to verify it fails**
  Run: `cd seacast-prigi-laravel; php artisan test --filter=SailingAdvisoryPdfExportTest`
  Expected: FAIL.
- [ ] **Step 3: Install PDF library if needed**
  Run: `cd seacast-prigi-laravel; composer require barryvdh/laravel-dompdf` (or utilize native Dompdf wrapper).
- [ ] **Step 4: Design `sailing-advisory.blade.php`**
  Format official document:
  - Header: SEACAST emblem, Document Title, Reference Code, Date.
  - Section 1: Vessel & Skipper Profile, Port of Origin, GT Class.
  - Section 2: Route & Precision GPS Coordinates (Lat/Lon in `DD°MM'SS"` & decimal, bearing, distance NM).
  - Section 3: Visual Route Snippet (SVG vector route line between departure port and target coordinate).
  - Section 4: Maritime Safety & Permen KP 59/2020 Compliance (Wave height, moon phase, gear code `LHP-PUT`/`LTL`/`LLD-RT`).
  - Section 5: Fuel & Economics Projection (Solar liters, subsidized cost, catch forecast kg, net margin).
  - Section 6: Official Sign-off Box (Nahkoda Kapal & Petugas Kesyahbandaran / TPI).
- [ ] **Step 5: Run tests to verify they pass**
  Run: `cd seacast-prigi-laravel; php artisan test --filter=SailingAdvisoryPdfExportTest`
  Expected: PASS.
- [ ] **Step 6: Commit**
  ```bash
  git add seacast-prigi-laravel/resources/views/pdf/sailing-advisory.blade.php seacast-prigi-laravel/app/Http/Controllers/Web/TripPlannerController.php seacast-prigi-laravel/tests/
  git commit -m "feat: implement official PDF Sailing Advisory Slip generator"
  ```

---

### Task 8: End-to-End Verification & Full Regression Testing

**Files:**
- Test: All tests in `seacast-prigi-laravel/tests/`

- [ ] **Step 1: Recompile frontend production assets**
  Run: `cd seacast-prigi-laravel; npm run build`
  Expected: PASS with 0 build errors.
- [ ] **Step 2: Run complete PHPUnit test suite**
  Run: `cd seacast-prigi-laravel; php artisan test`
  Expected: All tests pass with 0 errors and 0 failures.
- [ ] **Step 3: Verification of Review Focus criteria**
  Verify the 5 review focus classes:
  1. `<10 GT` beyond 12 NM triggers warning.
  2. `>30 GT` within 12 NM triggers warning.
  3. Future dates fallback gracefully without errors.
  4. Wave > 2.5m marks trip DANGER.
  5. Port admin scoping isolates records correctly.
- [ ] **Step 4: Final verification commit**
  ```bash
  git add .
  git commit -m "chore: complete end-to-end verification for Trip Planner and system streamlining"
  ```

