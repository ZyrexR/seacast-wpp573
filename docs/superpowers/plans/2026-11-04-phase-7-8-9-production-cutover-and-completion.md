# Phase 7, 8 & 9: Production Authentication, Spatial QA, & Final Cutover Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Complete the remaining phases of the SEACAST PRIGI V3 migration: Phase 7 (Production Authentication, Roles & Security Hardening), Phase 8 (Comprehensive QA, Spatial Validation & Scientific Regression), and Phase 9 (Production Cutover & Legacy Framework Retirement), achieving 100% standalone Laravel 13 operation.

**Architecture:** Native Laravel 13.40.0 Modular Monolith + PostgreSQL 17.11 / PostGIS 3.6.2 (read-only for scientific data) + SQLite for auth/session/queue state. Option 1 Architecture (Blade + Alpine.js + Tailwind CSS, 0 React packages).

**Tech Stack:** Laravel 13, PHP 8.3.36, PostgreSQL 17.11 + PostGIS 3.6.2, SQLite 3, MapLibre GL JS, Tailwind CSS v4, Alpine.js, PHPUnit.

**Spec:** `docs/migration/MIGRATION_ROADMAP.md` (Phases 7, 8, 9), `LARAVEL_TARGET_ARCHITECTURE.md`, `SCIENTIFIC_INTEGRITY_AUDIT.md`.

## Global Constraints
- Preserve `seacast_readonly` database connection on PostgreSQL 17 PostGIS database (zero write/destructive operations).
- Maintain Option 1 architecture (pure Blade + Alpine.js + Tailwind CSS; 0 React packages in runtime).
- Keep scientific baselines immutable: HSI formulas, DOM waterfall resolution hierarchy ($Z4 \to Z5 \to Z3 \to Z1 \to Z2 \to GEN$), Bycatch traffic light screening, and BMKG fallback disclaimers.
- All existing 127 tests must continue to pass 100%.

## Review Focus
1. Session and cookie security: Ensure `Secure`, `HttpOnly`, `SameSite=lax` headers and active CSRF validation.
2. RBAC authorization matrix: Verify admin-only routes reject nelayan/syahbandar/peneliti with 403 Forbidden.
3. PostGIS spatial integrity: Verify all 8,129 polygon geometries in `canonical_grids` pass `ST_IsValid` with SRID 4326.
4. Scientific calculation precision: Verify floating-point tolerance and ensemble model weights sum to exactly 1.000.
5. Legacy retirement safety: Verify legacy Next.js server remains completely terminated and `web/` archive manifest is complete.

---

### Task 1: Phase 7 — Production Security Hardening & RBAC Infrastructure

**Files:**
- Create: `seacast-prigi-laravel/app/Enums/UserRole.php`
- Create: `seacast-prigi-laravel/app/Http/Middleware/SecurityHeadersMiddleware.php`
- Modify: `seacast-prigi-laravel/app/Models/User.php`
- Modify: `seacast-prigi-laravel/bootstrap/app.php`
- Create: `seacast-prigi-laravel/tests/Feature/ProductionSecurityHardeningTest.php`

- [x] Step 1: Create typed enum `App\Enums\UserRole` with `NELAYAN = 'nelayan'`, `SYAHBANDAR = 'syahbandar'`, `PENELITI = 'peneliti'`, `ADMIN = 'admin'`, and helper methods `labels()`, `values()`.
- [x] Step 2: Implement `SecurityHeadersMiddleware` setting standard enterprise security headers (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`).
- [x] Step 3: Register `SecurityHeadersMiddleware` as global middleware in `bootstrap/app.php`.
- [x] Step 4: Write `ProductionSecurityHardeningTest.php` testing security headers, complete RBAC matrix across all 4 personas, CSRF token verification, and session regeneration.
- [x] Step 5: Run tests to verify Phase 7 security requirements pass.

---

### Task 2: Phase 8 — Comprehensive Spatial Validation & Geometry Audit

**Files:**
- Create: `seacast-prigi-laravel/tests/Feature/SpatialGeometryValidationTest.php`

- [x] Step 1: Write `SpatialGeometryValidationTest.php` testing:
  - 100% validity of all 8,129 polygons via `ST_IsValid(geom)`.
  - SRID is strictly 4326 via `ST_SRID(geom)`.
  - Bounding box limits within $102.5400^\circ \text{E} \le \text{lon} \le 126.4400^\circ \text{E}$ and $-13.9600^\circ \text{S} \le \text{lat} \le -6.8600^\circ \text{S}$.
  - Sector integrity: `BALI_NUSA_TENGGARA` (4,561), `JABAR_JATENG` (2,121), `JATIM` (1,447) summing to exactly 8,129.
  - Port distance `dist_port_nm` non-negative and finite.
- [x] Step 2: Run spatial validation test suite.

---

### Task 3: Phase 8 — Scientific Tolerance & Numerical Regression Suite

**Files:**
- Create: `seacast-prigi-laravel/tests/Unit/ScientificToleranceRegressionTest.php`

- [x] Step 1: Write `ScientificToleranceRegressionTest.php` testing:
  - Ensemble SDM species weights sum to 1.000 (Cakalang: 0.320 + 0.355 + 0.325 = 1.000; Madidihang: 0.295 + 0.365 + 0.340 = 1.000; Legacy species removed from TCT scope: 0.000 + 0.517 + 0.483 = 1.000).
  - Legacy species removed from TCT scope MaxEnt weight is strictly 0.000 (zero contribution).
  - HSI value normalization is strictly clamped in $[0.000, 1.000]$.
  - DOM waterfall resolution priority order ($Z4 \to Z5 \to Z3 \to Z1 \to Z2 \to GEN$).
  - Bycatch screening criteria and threshold invariance.
  - Zero NaN, zero infinite values in scientific computation outputs.
- [x] Step 2: Run scientific regression test suite.

---

### Task 4: Phase 8 — Full System Integration & Complete Route Verification

**Files:**
- Create: `seacast-prigi-laravel/tests/Feature/ComprehensiveRouteCoverageTest.php`

- [ ] Step 1: Write comprehensive test verifying all 24 web routes (13 public + 11 dashboard) and all 10 API endpoints return valid HTTP statuses, correct Content-Type headers, and contain required scientific elements.
- [ ] Step 2: Execute full test suite across all feature and unit test files. Verify 100% pass rate.

---

### Task 5: Phase 9 — Production Cutover, Legacy Retirement & Final Audit Documentation

**Files:**
- Create: `docs/migration/PHASE_7_SECURITY_HARDENING_REPORT.md`
- Create: `docs/migration/PHASE_8_SPATIAL_AND_SCIENTIFIC_REGRESSION_REPORT.md`
- Create: `docs/migration/PHASE_9_PRODUCTION_CUTOVER_REPORT.md`
- Create: `docs/migration/FINAL_MIGRATION_COMPLETION_REPORT.md`

- [ ] Step 1: Verify legacy Next.js / Node.js processes are fully inactive.
- [ ] Step 2: Inventory legacy `web/` directory and compile archive manifest.
- [ ] Step 3: Run production frontend asset compilation (`npm run build`) and verify build artifacts.
- [ ] Step 4: Write comprehensive reports:
  - `PHASE_7_SECURITY_HARDENING_REPORT.md`
  - `PHASE_8_SPATIAL_AND_SCIENTIFIC_REGRESSION_REPORT.md`
  - `PHASE_9_PRODUCTION_CUTOVER_REPORT.md`
  - `FINAL_MIGRATION_COMPLETION_REPORT.md`
- [ ] Step 5: Copy `FINAL_MIGRATION_COMPLETION_REPORT.md` to artifact directory.