# Phase 5 & 6 — Public Portal & Python Scientific Engine Orchestration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Complete the migration of all 7 remaining public portal pages into native Blade + Tailwind CSS (Phase 5), and establish the automated daily Python inference orchestration pipeline (Artisan command, Queue Job, and Scheduler at 04:00 WIB) with cache invalidation and telemetry (Phase 6).

**Architecture:** Laravel 13 Modular Monolith using Option 1 (Native Blade + Tailwind CSS). Zero React runtime dependencies. Asynchronous queue-based Python process execution via `PythonComputationBridgeInterface`. Deterministic geospatial cache invalidation via `GeospatialCacheManager`. Read-only database preservation.

**Tech Stack:** PHP 8.3+, Laravel 13.40, Python 3.14, Tailwind CSS v4, PostgreSQL 17.11, PostGIS 3.6.2, PHPUnit 11.

**Spec:** `docs/migration/CURRENT_SYSTEM_INVENTORY.md`, `docs/migration/MIGRATION_ROADMAP.md`, `docs/migration/PYTHON_ENGINE_INVENTORY.md`.

---

## Global Constraints

- Option 1 Architecture: Native Blade + Tailwind CSS. Zero React/ReactDOM dependencies in runtime.
- Read-Only Database: All production database access strictly enforces `seacast_readonly` bounds.
- Scientific Immutability: Locked ensemble weights (`cakalang`: 0.320 MaxEnt / 0.355 RF / 0.325 GLM), HSI Gaussian response curves, and DOM waterfall resolution are immutable.
- Existing Test Suite: All 115 current automated tests must continue passing with zero regressions.

---

## Review Focus

1. **Public Portal Breadcrumbs & Navigation:** Ensuring all 7 migrated public pages link smoothly back to `/`, `/science`, `/platform`, and the dashboard login.
2. **Process Runner Security & Argument Escaping:** Ensuring `seacast:infer-daily` rejects malformed dates or injection characters before spawning child Python processes.
3. **Queue Job Timeout & Resilience:** Ensuring `ProcessDailyInferenceJob` has explicit 180s timeout, failure handling, and retry limits.
4. **Cache Invalidation:** Ensuring running daily inference flushes `predictions:*` tags in `GeospatialCacheManager` and auto-warms fresh payloads.
5. **Scheduler Timezone:** Ensuring the daily run is explicitly scheduled at `04:00` in `Asia/Jakarta` (WIB).

---

## Task Decomposition

### Task 1: Subphase 5.1 — Public Portal Ocean Data & Platform Pages
- [x] Add controller actions in `PublicPageController.php`: `oceanData()`, `platform()`, `platformDataSources()`, `platformFeatures()`.
- [x] Build `resources/views/public/ocean-data.blade.php`: Multi-sensor satellite catalog (SST, Chl-a, currents, wind, bathymetry).
- [x] Build `resources/views/public/platform/index.blade.php`: 4-tier architecture pipeline & system overview.
- [x] Build `resources/views/public/platform/data-sources.blade.php`: Satellite provider telemetry, update schedules, and ground validation.
- [x] Build `resources/views/public/platform/features.blade.php`: User persona solutions (<10 GT traditional, 20–30 GT purse seine, Syahbandar).
- [x] Register public routes in `routes/web.php`.
- [x] Write and run automated tests in `tests/Feature/PublicOceanPlatformPagesTest.php`.

### Task 2: Subphase 5.2 — Public Portal Scientific Knowledge Pages
- [x] Add controller actions in `PublicPageController.php`: `scienceDom()`, `scienceMethodology()`, `scienceModels()`.
- [x] Build `resources/views/public/science/dom.blade.php`: 5 DOM zonation framework, waterfall priority, and megafauna conservation.
- [x] Build `resources/views/public/science/methodology.blade.php`: Multi-model ensemble SDM (MaxEnt, RF, GLM), 10-fold spatial CV.
- [x] Build `resources/views/public/science/models.blade.php`: Species evaluation diagnostics, AUC-ROC, and variable contributions for 3 tuna species.
- [x] Register scientific knowledge routes in `routes/web.php`.
- [x] Write and run automated tests in `tests/Feature/PublicScienceKnowledgePagesTest.php`.

### Task 3: Subphase 6.1 — Daily Inference Artisan Console Command & Service
- [x] Create `App\Console\Commands\DailyInferenceCommand.php` (`seacast:infer-daily {--date=} {--force}`):
  - Validates date format (`YYYY-MM-DD`).
  - Calls `PythonComputationBridgeInterface::runInference($date)`.
  - Flushes and warms geospatial cache tags via `GeospatialCacheManager`.
  - Outputs structured CLI telemetry and benchmark runtime.
- [x] Write and run automated tests in `tests/Feature/DailyInferenceCommandTest.php`.

### Task 4: Subphase 6.2 — Queue Job Orchestration & Scheduler Configuration
- [x] Create `App\Jobs\ProcessDailyInferenceJob.php`:
  - Implements `ShouldQueue`, retry logic (3 attempts), 180-second timeout.
  - Spawns inference and manages background failure notifications.
- [x] Register scheduler in `routes/console.php`:
  - Schedule `seacast:infer-daily` daily at `04:00` with `timezone('Asia/Jakarta')` and `withoutOverlapping()`.
- [x] Write and run automated tests in `tests/Feature/InferenceQueueJobTest.php`.

### Task 5: Subphase 6.3 — Master QA, Documentation & Phase 5 & 6 Gate Audit
- [ ] Run full automated test suite (`php artisan test`) verifying all tests pass.
- [ ] Run Vite production build (`npm run build`) ensuring clean compilation.
- [ ] Compile Phase 5 & 6 documentation in `docs/migration/`:
  - `PHASE_5_PUBLIC_PORTAL_REPORT.md`
  - `PHASE_6_PYTHON_ORCHESTRATION_REPORT.md`
  - `PHASE_5_6_FINAL_READINESS.md`
- [ ] Mirror final readiness report to Artifact Directory and present completion sign-off.
