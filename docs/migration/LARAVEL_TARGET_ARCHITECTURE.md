# SEACAST PRIGI V3 — LARAVEL TARGET ARCHITECTURE
**Target Framework:** Laravel 13 (PHP 8.3+)  
**Architectural Pattern:** Modular Monolith  
**Database:** PostgreSQL 17.11 + PostGIS 3.6.2  
**Frontend Reactivity:** Laravel Blade + Livewire 4 + Alpine.js 3.x  

---

## 1. STRUKTUR DIREKTORI DOMAIN (MODULAR MONOLITH)

```text
seacast-laravel/
├── app/
│   ├── Domain/
│   │   ├── Authentication/
│   │   │   ├── Models/User.php
│   │   │   ├── Policies/OceanDataPolicy.php
│   │   │   └── Middleware/EnsureRole.php
│   │   ├── Spatial/
│   │   │   ├── Models/CanonicalGrid.php
│   │   │   ├── Repositories/CanonicalGridRepository.php
│   │   │   └── Services/GeoJsonSerializerService.php
│   │   ├── Prediction/
│   │   │   ├── Models/DailyPrediction.php
│   │   │   ├── Models/GridMonthlyPrediction.php
│   │   │   ├── Repositories/PredictionRepository.php
│   │   │   └── Services/PredictionSummaryService.php
│   │   ├── Oceanography/
│   │   │   ├── Services/SstFrontDetector.php
│   │   │   └── Services/ChlorophyllIndexer.php
│   │   ├── Species/
│   │   │   ├── Enums/TargetSpecies.php (Cakalang, Madidihang, Albakor)
│   │   │   ├── DTOs/SpeciesEvaluationDto.php
│   │   │   └── Services/HsiModelRegistry.php
│   │   ├── DOM/
│   │   │   ├── Enums/DomZoneCode.php (Z1, Z2, Z3, Z4, Z5, GEN)
│   │   │   ├── Services/ZonationEngine.php
│   │   │   └── Rules/DomPriorityResolver.php
│   │   ├── Bycatch/
│   │   │   ├── Enums/TrafficLightStatus.php (GREEN, YELLOW, RED)
│   │   │   └── Services/IotcSlopeScreener.php
│   │   ├── Fleet/
│   │   │   ├── Models/PrigiFleetByGt.php
│   │   │   ├── Services/FuelEfficiencyCalculator.php (Haversine Port Distance)
│   │   │   └── Repositories/FleetRepository.php
│   │   ├── CPUE/
│   │   │   ├── Models/PrigiPortLanding.php
│   │   │   ├── Services/NominalCpueCalculator.php
│   │   │   └── Repositories/LandingRepository.php
│   │   ├── TimeMachine/
│   │   │   └── Services/MonthlyPlaybackService.php
│   │   └── Simulation/
│   │       ├── DTOs/ClimateScenarioDto.php
│   │       └── Services/OceanPerturbationSimulator.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── PredictionApiController.php
│   │   │   │   ├── GridApiController.php
│   │   │   │   ├── LandingApiController.php
│   │   │   │   ├── TimeMachineApiController.php
│   │   │   │   ├── FleetApiController.php
│   │   │   │   └── SimulationApiController.php
│   │   │   ├── AuthController.php
│   │   │   └── PublicPageController.php
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   └── Resources/
│   │
│   ├── Livewire/
│   │   ├── Dashboard/
│   │   │   ├── Overview.php
│   │   │   ├── DpiRadar.php
│   │   │   ├── DomManagement.php
│   │   │   ├── BycatchSafety.php
│   │   │   ├── CpueAnalysis.php
│   │   │   ├── TimeMachine.php
│   │   │   ├── WhatIfSimulator.php
│   │   │   ├── OceanographicExplorer.php
│   │   │   ├── SpeciesModels.php
│   │   │   ├── DataMonitoring.php
│   │   │   └── SystemStatus.php
│   │   └── Public/
│   │       ├── SnapshotStrip.php
│   │       └── ParameterExplorer.php
│   │
│   ├── Jobs/
│   │   ├── ExecuteDailyInferenceJob.php
│   │   └── IngestSatelliteTelemetryJob.php
│   │
│   └── Console/
│       └── Commands/
│           ├── RunDailyInferenceCommand.php
│           └── VerifySpatialIntegrityCommand.php
│
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── base.blade.php
│   │   │   ├── public.blade.php
│   │   │   ├── dashboard.blade.php
│   │   │   └── partials/
│   │   ├── components/
│   │   │   ├── ui/ (16 base design tokens)
│   │   │   └── public/ (7 scientific homepage sections)
│   │   ├── pages/ (13 public Blade templates)
│   │   └── livewire/ (11 reactive dashboard components)
│   ├── css/
│   │   └── app.css (Tailwind CSS configuration)
│   └── js/
│       ├── app.js (Alpine.js entry point)
│       └── map/
│           ├── maplibre-canvas.js (WebGL map engine)
│           └── vector-field-currents.js (Current particles animation)
│
├── routes/
│   ├── web.php (Public portal & dashboard state routes)
│   ├── api.php (Strict contract parity for 6 endpoints)
│   └── console.php (Scheduler definitions)
│
├── tests/
│   ├── Feature/
│   │   ├── ApiParityTest.php (Compares Next.js baseline with Laravel output)
│   │   ├── PublicPagesTest.php (200 OK & content assertion across all routes)
│   │   └── AuthenticationTest.php (Role enforcement test)
│   ├── Unit/
│   │   ├── DomPriorityResolverTest.php
│   │   ├── BycatchTrafficLightTest.php
│   │   └── FuelCalculationTest.php
│   └── Scientific/
│       ├── HsiCalculationRegressionTest.php
│       └── SpatialGeomValidationTest.php
│
└── python-engine/ (Retained isolated calculation environment)
    ├── scripts/daily_inference.py
    └── models/*.joblib
```

---

## 2. POLA ALIR DATA & INTERAKSI (DATA FLOW)

```
[Web Browser: Nelayan / Stakeholder]
               │
               ▼
[Nginx Web Server / PHP-FPM 8.3]
               │
               ▼
   [Laravel 13 HTTP Router]
       ├── /api/* ──────────► [API Controllers & Resources] ──► [Eloquent / PostGIS 17]
       ├── /dashboard/* ────► [Livewire 4 Components] ──────► [Domain Repositories]
       │                            ▲ (Reactive Events)                 │
       │                            ▼                                   ▼
       │                      [Alpine.js]                       [PostgreSQL 17.11]
       │                      [MapLibre GL]                      (8.129 Sel GiST)
       └── /* ──────────────► [Blade Public Pages]
                                    │
                                    ▼
                          [Laravel Process Job] ──► [Python Engine: daily_inference.py]
```

---

## 3. PRINSIP PEMISAHAN PERAN (SEPARATION OF CONCERNS)

1. **Laravel:** Bertindak sebagai aplikasi utama (Web server, session, routing, sanitasi input, caching, otorisasi, diseminasi data, queue, dan scheduler).
2. **PostgreSQL/PostGIS:** Menjaga kebenaran spasial geometris kanonikal 8.129 sel, relasi, dan arsip historis.
3. **Python Scientific Engine:** Khusus bertugas memuat model `.joblib` machine learning dan menghitung regresi/inferensi numerik berat.
4. **Alpine.js + MapLibre GL:** Khusus menangani rendering interaktif di browser (WebGL canvas, pan/zoom, hover tooltip).

