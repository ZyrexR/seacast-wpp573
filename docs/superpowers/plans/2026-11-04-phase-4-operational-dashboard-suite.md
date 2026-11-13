# Phase 4 — Operational Dashboard & Scientific Analytics Suite Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement and activate all 7 remaining operational dashboard modules (`dom-management`, `bycatch-safety`, `time-machine`, `what-if-simulator`, `species-models`, `oceanographic-explorer`, `data-monitoring`) and 2 API contracts (`/api/timemachine`, `/api/simulate`) in native Laravel 13 Blade + Alpine.js + Tailwind CSS, achieving 100% functional parity with the legacy platform.

**Architecture:** Laravel 13 Modular Monolith using Option 1 (Native Blade + Alpine.js + Tailwind CSS). Zero React/Next.js runtime dependencies. PostgreSQL 17.11 + PostGIS 3.6.2 queries via `seacast_readonly` connection. Multi-tier deterministic caching and strict scientific parameter preservation.

**Tech Stack:** PHP 8.3+, Laravel 13.40, Tailwind CSS v4, Alpine.js v3, PostgreSQL 17.11, PostGIS 3.6.2, PHPUnit 11.

**Spec:** `docs/migration/CURRENT_SYSTEM_INVENTORY.md`, `docs/migration/API_CONTRACT_INVENTORY.md`, `docs/migration/SCIENTIFIC_INTEGRITY_AUDIT.md`.

---

## Global Constraints

- Option 1 Architecture: Native Blade + Alpine.js + Tailwind CSS.
- **React Island: PROHIBITED.** No React packages in application runtime.
- Read-Only Database: All spatial, analytical, and temporal queries run under `seacast_readonly`.
- Scientific Integrity: HSI formulas, AUC/TSS metrics, DOM waterfall priority ($\text{Z4} \to \text{Z5} \to \text{Z3} \to \text{Z1} \to \text{Z2} \to \text{GEN}$), Bycatch traffic light thresholds, and BMKG fallback disclaimers are immutable.
- Existing Test Suite: All 99 current automated tests must continue passing with zero regressions.

---

## Review Focus

1. **DOM Waterfall Execution:** Ensuring Z4 (Nursery/Turtle High) precedes Z5, Z3, Z1, Z2, and GEN in both live UI and simulation endpoints.
2. **Temporal Consistency:** `/api/timemachine` must correctly group and serve 2025 monthly aggregated statistics across 12 months from `grid_monthly_predictions`.
3. **Simulation Boundary Clamping:** Perturbation calculations must clamp SST and Chl-a within physical bounds and calculate accurate Gaussian response curves.
4. **Vessel Fuel & Range Economics:** What-If simulator must calculate round-trip nautical miles, fuel expenditure, and gross/net margins accurately per GT class (<10 GT, 10–20 GT, 20–30 GT).
5. **A11y & Responsiveness:** All 7 dashboard views must meet WCAG AA standards (minimum font size $\ge 12\text{px}$, color contrast $\ge 4.5:1$, glassmorphic ocean theme).

---

## Task Decomposition

### Task 1: Subphase 4.1 — Backend Services & API Contracts (`/api/timemachine`, `/api/simulate`)
- [x] Create `App\Domain\ScientificData\Services\TimeMachineService.php` to handle monthly summary aggregations, cell-level temporal history, and monthly grid slices from `grid_monthly_predictions`.
- [x] Create `App\Domain\ScientificData\Services\SimulationService.php` to calculate Gaussian HSI responses under SST and Chl-a perturbations, and evaluate vessel economic feasibility.
- [x] Create `App\Http\Controllers\Api\TimeMachineApiController.php` handling GET `/api/timemachine`.
- [x] Create `App\Http\Controllers\Api\SimulationApiController.php` handling POST `/api/simulate`.
- [x] Register `/api/timemachine` and `/api/simulate` in `routes/api.php`.
- [x] Write and run automated tests in `tests/Feature/TimeMachineApiTest.php` and `tests/Feature/SimulationApiTest.php`.

### Task 2: Subphase 4.2 — Ocean Governance & Conservation Modules (`dom-management`, `bycatch-safety`)
- [x] Implement `domManagement()` and `bycatchSafety()` methods in `DashboardController.php` with real data aggregation from `DailyPrediction` and `CanonicalGrid`.
- [x] Build `resources/views/dashboard/dom-management.blade.php`:
  - 5-zone allocation matrix (Z1 PFG Inshore, Z2 PFG Offshore, Z3 Feeding Ground, Z4 Nursery, Z5 Bycatch Advisory).
  - Zone criteria, fleet permit rules, allowable gear, and conservation descriptions.
  - Interactive zone switcher tab with real cell counts and percentage shares.
- [x] Build `resources/views/dashboard/bycatch-safety.blade.php`:
  - IOTC Traffic Light matrix (Green, Yellow, Red).
  - ETP species interaction probabilities and circle-hook / mitigation guidelines.
  - Active fleet safety advisory panel based on wave height and offshore conditions.
- [x] Write and run automated tests in `tests/Feature/OceanGovernanceModulesTest.php`.

### Task 3: Subphase 4.3 — Temporal & Scenario Exploration Modules (`time-machine`, `what-if-simulator`)
- [x] Implement `timeMachine()` and `whatIfSimulator()` methods in `DashboardController.php`.
- [x] Build `resources/views/dashboard/time-machine.blade.php`:
  - 12-month 2025 timeline slider with Play/Pause Alpine.js state controller.
  - Synchronized monthly environmental metrics (SST, Chl-a, current velocity) and tuna HSI shifts.
  - Integration with PPN Prigi monthly landing records for empirical validation.
- [x] Build `resources/views/dashboard/what-if-simulator.blade.php`:
  - Interactive vessel parameter controls (vessel GT class, target distance in NM, solar fuel subsidy price).
  - Real-time calculation of fuel consumption, travel hours, round-trip cost, gross tuna value, and net margin.
  - Oceanographic perturbation testing interface (SST $\pm \Delta^\circ\text{C}$, Chl-a factor).
- [x] Write and run automated tests in `tests/Feature/TemporalScenarioModulesTest.php`.

### Task 4: Subphase 4.4 — Scientific Diagnostics & Sensor Infrastructure (`species-models`, `oceanographic-explorer`, `data-monitoring`)
- [ ] Implement `speciesModels()`, `oceanographicExplorer()`, and `dataMonitoring()` in `DashboardController.php`.
- [ ] Build `resources/views/dashboard/species-models.blade.php`:
  - Species selector (Cakalang, Madidihang, Legacy species removed from TCT scope).
  - Ensemble model weight diagnostics (MaxEnt, Random Forest, GLM).
  - AUC (0.784 – 0.892) and TSS (0.606 – 0.712) model evaluation scorecards.
  - Environmental variable contribution bars (SST, Chl-a, thermal front, bathymetry, slope).
- [ ] Build `resources/views/dashboard/oceanographic-explorer.blade.php`:
  - Multi-sensor comparison cards (SST NOAA OISST, Chl-a Sentinel-3/MODIS, thermal front gradient, current velocity, wind speed).
  - Statistical summary table (min, mean, max, standard deviation across 8,129 cells).
- [ ] Build `resources/views/dashboard/data-monitoring.blade.php`:
  - Database schema & table completeness matrix (8,129 canonical cells, 97,548 monthly predictions, 132 landing months, SRID 4326 verification).
  - Satellite latency and sensor ingestion pipeline status.
  - BMKG in-situ sensor fallback status and legal scientific disclaimer.
- [ ] Write and run automated tests in `tests/Feature/ScientificDiagnosticsModulesTest.php`.

### Task 5: Subphase 4.5 — Master QA, Documentation & Phase 4 Gate Audit
- [x] Run full automated test suite (`php artisan test`) verifying all tests pass.
- [x] Run Vite production build (`npm run build`) ensuring clean compilation without React.
- [x] Compile Phase 4 documentation in `docs/migration/`:
  - `PHASE_4_OPERATIONAL_DASHBOARD_REPORT.md`
  - `PHASE_4_SCIENTIFIC_MODULES_AUDIT.md`
  - `PHASE_4_API_CONTRACT_VERIFICATION.md`
  - `PHASE_4_TEST_REPORT.md`
  - `PHASE_4_FINAL_READINESS.md`
- [x] Mirror final readiness report to Artifact Directory and present completion sign-off.