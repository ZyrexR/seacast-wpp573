# SEACAST PRIGI V3 — CURRENT SYSTEM INVENTORY
**Audit Date:** 4 November 2026  
**Migration Baseline:** Next.js 15.5.28 / React 19 / TypeScript 5.7 / PostGIS 17.11  
**Scope:** Complete Codebase & Route Inventory  

---

## 1. TECH STACK & RUNTIME INVENTORY

| Subsystem | Incumbent Technology | Version | Migration Target | Status |
| :--- | :--- | :--- | :--- | :---: |
| **Web Framework** | Next.js (App Router) | 15.5.28 | Laravel 13 (PHP 8.3+) | **DEPRECATED** |
| **Frontend Library** | React (Client Components) | 19.0.0 | Laravel Blade + Livewire 4 | **DEPRECATED** |
| **Client State / Interactivity** | React Context (`AuthContext`) | N/A | Alpine.js 3.x + Livewire | **DEPRECATED** |
| **CSS & Styling** | Tailwind CSS (Design Tokens) | 3.4.17 / v4 tokens | Tailwind CSS (Blade Preset) | **READY TO MIGRATE** |
| **Database Engine** | PostgreSQL | 17.11 | PostgreSQL 17.11 (Native) | **RETAIN (ZERO TOUCH)** |
| **Spatial Engine** | PostGIS | 3.6.2 | PostGIS 3.6.2 (Native) | **RETAIN (ZERO TOUCH)** |
| **Scientific Processing** | Python (NumPy, Pandas, Scikit-learn) | 3.14 / venv | Python Scientific Engine | **RETAIN & ORCHESTRATE** |
| **Mapping Engine** | MapLibre GL JS | 5.1.0 | MapLibre GL JS (Browser-side) | **READY TO MIGRATE** |
| **Node.js Runtime** | Node.js (Production Server) | 24.19.0 | Removed from production | **DEPRECATED (DEV ONLY)** |

---

## 2. ROUTE INVENTORY (24 PAGES + 2 LAYOUTS)

### 2.1. Layout Components
| File Path | Description | Target Laravel Structure | Status |
| :--- | :--- | :--- | :---: |
| `web/src/app/layout.tsx` | Global HTML shell, font declarations, AuthProvider | `resources/views/layouts/base.blade.php` | **READY TO MIGRATE** |
| `web/src/app/dashboard/layout.tsx` | Dashboard authenticated shell, Sidebar + Topbar | `resources/views/layouts/dashboard.blade.php` | **READY TO MIGRATE** |

### 2.2. Public Portal Routes (13 Pages)
| Route | Incumbent File | Content & Functionality | Target View | Status |
| :--- | :--- | :--- | :--- | :---: |
| `/` | `web/src/app/page.tsx` | Scientific Ocean Intelligence Homepage (7 seksi) | `resources/views/pages/home.blade.php` | **READY TO MIGRATE** |
| `/about` | `web/src/app/about/page.tsx` | Inisiatif PPN Prigi, profil WPPNRI 573 | `resources/views/pages/about.blade.php` | **READY TO MIGRATE** |
| `/design-system` | `web/src/app/design-system/page.tsx` | Katalog token, warna, tipografi, komponen interaktif | `resources/views/pages/design-system.blade.php` | **READY TO MIGRATE** |
| `/documentation` | `web/src/app/documentation/page.tsx` | Glosarium oseanografi, formula ilmiah | `resources/views/pages/documentation.blade.php` | **READY TO MIGRATE** |
| `/login` | `web/src/app/login/page.tsx` | Login persona (Nelayan, Syahbandar, Peneliti, Admin) | `resources/views/auth/login.blade.php` | **REQUIRES REFACTORING** |
| `/ocean-data` | `web/src/app/ocean-data/page.tsx` | Katalog satelit NOAA OISST, Sentinel-3, GEBCO | `resources/views/pages/ocean-data.blade.php` | **READY TO MIGRATE** |
| `/platform` | `web/src/app/platform/page.tsx` | Visi arsitektur platform intelijen kelautan | `resources/views/pages/platform/index.blade.php` | **READY TO MIGRATE** |
| `/platform/data-sources` | `web/src/app/platform/data-sources/page.tsx` | Metrik metadata feed satelit & validasi sensor | `resources/views/pages/platform/data-sources.blade.php` | **READY TO MIGRATE** |
| `/platform/features` | `web/src/app/platform/features/page.tsx` | Solusi berbasis armada (<10 GT, 20-30 GT, Syahbandar) | `resources/views/pages/platform/features.blade.php` | **READY TO MIGRATE** |
| `/science` | `web/src/app/science/page.tsx` | Ringkasan teori ekologi & Hutchinson niche | `resources/views/pages/science/index.blade.php` | **READY TO MIGRATE** |
| `/science/dynamic-ocean-management` | `web/src/app/science/dynamic-ocean-management/page.tsx` | Kerangka 5 zona DOM & mitigasi bycatch | `resources/views/pages/science/dom.blade.php` | **READY TO MIGRATE** |
| `/science/methodology` | `web/src/app/science/methodology/page.tsx` | Metodologi Ensemble SDM (MaxEnt, RF, GLM) | `resources/views/pages/science/methodology.blade.php` | **READY TO MIGRATE** |
| `/science/prediction-models` | `web/src/app/science/prediction-models/page.tsx` | Evaluasi kurva respons & nilai AUC ketiga tuna | `resources/views/pages/science/models.blade.php` | **READY TO MIGRATE** |

### 2.3. Operational Dashboard Routes (11 Pages)
| Route | Incumbent File | Functionality | Target Livewire Component | Status |
| :--- | :--- | :--- | :--- | :---: |
| `/dashboard` | `web/src/app/dashboard/page.tsx` | Executive Overview, 4 Dynamic KPIs, DOM widget | `App\Livewire\Dashboard\Overview` | **READY TO MIGRATE** |
| `/dashboard/bycatch-safety` | `web/src/app/dashboard/bycatch-safety/page.tsx` | IOTC Traffic Light table, turtle/shark mitigation | `App\Livewire\Dashboard\BycatchSafety` | **READY TO MIGRATE** |
| `/dashboard/cpue-analysis` | `web/src/app/dashboard/cpue-analysis/page.tsx` | Nominal CPUE Proxy & 132-month landing archive | `App\Livewire\Dashboard\CpueAnalysis` | **READY TO MIGRATE** |
| `/dashboard/data-monitoring` | `web/src/app/dashboard/data-monitoring/page.tsx` | Sensor health status, satellite latency, ingest logs | `App\Livewire\Dashboard\DataMonitoring` | **READY TO MIGRATE** |
| `/dashboard/dom-management` | `web/src/app/dashboard/dom-management/page.tsx` | 5 DOM zonation priority allocation matrix | `App\Livewire\Dashboard\DomManagement` | **READY TO MIGRATE** |
| `/dashboard/dpi-radar` | `web/src/app/dashboard/dpi-radar/page.tsx` | Interactive WebGL MapLibre map, layer controls | `App\Livewire\Dashboard\DpiRadar` | **REQUIRES REFACTORING** |
| `/dashboard/oceanographic-explorer`| `web/src/app/dashboard/oceanographic-explorer/page.tsx` | Environmental parameter comparison desk | `App\Livewire\Dashboard\OceanExplorer` | **READY TO MIGRATE** |
| `/dashboard/species-models` | `web/src/app/dashboard/species-models/page.tsx` | Species SDM weights, ROC-AUC diagnostics | `App\Livewire\Dashboard\SpeciesModels` | **READY TO MIGRATE** |
| `/dashboard/system-status` | `web/src/app/dashboard/system-status/page.tsx` | PostGIS database health, query performance | `App\Livewire\Dashboard\SystemStatus` | **READY TO MIGRATE** |
| `/dashboard/time-machine` | `web/src/app/dashboard/time-machine/page.tsx` | 12-month 2025 spatial slider & landing timeline | `App\Livewire\Dashboard\TimeMachine` | **READY TO MIGRATE** |
| `/dashboard/what-if-simulator` | `web/src/app/dashboard/what-if-simulator/page.tsx` | SST & Chl-a perturbation climate scenario | `App\Livewire\Dashboard\WhatIfSimulator` | **READY TO MIGRATE** |

---

## 3. API ROUTES INVENTORY (6 ENDPOINTS)

| Endpoint | HTTP Method | Incumbent Route | Primary Function | Status |
| :--- | :---: | :--- | :--- | :---: |
| `/api/predictions` | GET | `web/src/app/api/predictions/route.ts` | GeoJSON FeatureCollection / Dynamic summary | **READY TO MIGRATE** |
| `/api/grid` | GET | `web/src/app/api/grid/route.ts` | Canonical 8,129 cells GeoJSON by bbox/sector | **READY TO MIGRATE** |
| `/api/landings` | GET | `web/src/app/api/landings/route.ts` | 132 months of PPN Prigi empirical landings | **READY TO MIGRATE** |
| `/api/timemachine` | GET | `web/src/app/api/timemachine/route.ts` | 2025 monthly spatial summaries & cell series | **READY TO MIGRATE** |
| `/api/fleet` | GET | `web/src/app/api/fleet/route.ts` | Prigi vessel GT classes & operational profiles | **READY TO MIGRATE** |
| `/api/simulate` | POST | `web/src/app/api/simulate/route.ts` | Climate scenario perturbation calculations | **READY TO MIGRATE** |

---

## 4. STATUS SUMMARY

- **Total Routes:** 26 (2 Layouts, 13 Public Pages, 11 Dashboard Pages).
- **Total API Endpoints:** 6.
- **Ready to Migrate:** 24 routes / 6 APIs.
- **Requires Refactoring:** 2 components (Authentication from localStorage to Laravel Auth; WebGL MapLibre wrapper from React to Alpine.js/Blade).
- **Blocked / Deprecated:** 0 items blocked; entire Next.js runtime marked as **DEPRECATED**.

