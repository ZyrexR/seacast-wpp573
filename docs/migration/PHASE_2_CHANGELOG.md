# PHASE 2 CHANGELOG
## SEACAST PRIGI V3 — Core Radar Engine & Dynamic Filter

**Tanggal:** 2026-11-04  
**Framework:** Laravel 13.40.0 + PHP 8.3.36  
**Status:** **COMMITTED & VERIFIED**  

---

### 1. File Baru yang Ditambahkan (Added)

#### 1.1. Lapisan Akses Data Ilmiah (`App\Domain\ScientificData`)
- [`app/Domain/ScientificData/Repositories/ScientificDataRepositoryInterface.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/ScientificData/Repositories/ScientificDataRepositoryInterface.php): Kontrak repositori akses data spasial dan oseanografi.
- [`app/Domain/ScientificData/Repositories/PostGisScientificDataRepository.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/ScientificData/Repositories/PostGisScientificDataRepository.php): Implementasi query PostGIS (GiST index, bounding box, ST_Distance geodetik ke PPN Prigi, agregasi ringkasan).
- [`app/Domain/ScientificData/Services/ScientificDataService.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/ScientificData/Services/ScientificDataService.php): Layanan domain untuk transformer GeoJSON dan query koordinat.

#### 1.2. Jembatan Komputasi Ilmiah Python (`App\Infrastructure\Python`)
- [`app/Infrastructure/Python/Contracts/PythonComputationBridgeInterface.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Infrastructure/Python/Contracts/PythonComputationBridgeInterface.php): Kontrak interface jembatan Python.
- [`app/Infrastructure/Python/PythonComputationBridge.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Infrastructure/Python/PythonComputationBridge.php): Implementasi eksekusi proses aman via Symfony Process, whitelisting skrip, dan sanitasi injeksi.
- [`app/Infrastructure/Python/DTO/PythonComputationResult.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Infrastructure/Python/DTO/PythonComputationResult.php): Objek transfer data terstruktur untuk hasil komputasi.
- `app/Infrastructure/Python/Exceptions/PythonBridgeException.php`: Base exception penanganan kegagalan komputasi.
- `app/Infrastructure/Python/Exceptions/PythonSecurityException.php`: Exception pelanggaran keamanan path atau injeksi argumen.
- `app/Infrastructure/Python/Exceptions/PythonTimeoutException.php`: Exception timeout eksekusi.
- [`scripts/bridge_inference_runner.py`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scripts/bridge_inference_runner.py): Skrip CLI Python terstandarisasi penegak bobot baseline terkunci dan keluaran JSON murni.

#### 1.3. Engine Oseanografi (`App\Domain\Oceanography`)
- [`app/Domain/Oceanography/DTO/VariableMetadata.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/Oceanography/DTO/VariableMetadata.php): Metadata 5 variabel oseanografi fisik.
- [`app/Domain/Oceanography/DTO/OceanographicConditions.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/Oceanography/DTO/OceanographicConditions.php): DTO kondisi biofisik sel grid.
- [`app/Domain/Oceanography/Services/OceanographicDataService.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/Oceanography/Services/OceanographicDataService.php): Layanan validasi rentang, registri sumber data, dan deteksi front termal ($\ge 0.035^\circ\text{C/km}$).

#### 1.4. Engine Spesies & Habitat Suitability Index (`App\Domain\Species`)
- [`app/Domain/Species/DTO/EnsembleWeights.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/Species/DTO/EnsembleWeights.php): DTO bobot ensemble tiga spesies.
- [`app/Domain/Species/DTO/SpeciesPrediction.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/Species/DTO/SpeciesPrediction.php): DTO prediksi habitat.
- [`app/Domain/Species/Services/SpeciesPredictionService.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/Species/Services/SpeciesPredictionService.php): Penegak bobot baseline ilmiah terkunci (Legacy species removed from TCT scope MaxEnt = 0.000).
- [`app/Domain/Species/Services/HabitatSuitabilityIndexService.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/Species/Services/HabitatSuitabilityIndexService.php): Komputasi formula ensemble HSI, normalisasi skala $0.000-1.000$, dan evaluasi hotspot ($\ge 0.70$).

#### 1.5. Engine DOM & Bycatch (`App\Domain\DynamicOceanManagement` & `App\Domain\Bycatch`)
- [`app/Domain/DynamicOceanManagement/DTO/DomZoneClassification.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/DynamicOceanManagement/DTO/DomZoneClassification.php): DTO hasil klasifikasi zona DOM.
- [`app/Domain/DynamicOceanManagement/Services/DomClassificationService.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/DynamicOceanManagement/Services/DomClassificationService.php): Penegak urutan prioritas mutlak DOM ($\text{Z4} \to \text{Z5} \to \text{Z3} \to \text{Z1} \to \text{Z2} \to \text{GEN}$).
- [`app/Domain/Bycatch/DTO/BycatchClassification.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/Bycatch/DTO/BycatchClassification.php): DTO hasil skrining bycatch.
- [`app/Domain/Bycatch/Services/BycatchRiskService.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Domain/Bycatch/Services/BycatchRiskService.php): Layanan penentuan status lampu lalu lintas bycatch (GREEN, YELLOW, RED) standar IOTC.

#### 1.6. Controller API & Antarmuka Radar (`App\Http\Controllers\Api` & `resources/views`)
- [`app/Http/Controllers/Api/PredictionApiController.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Http/Controllers/Api/PredictionApiController.php): Endpoint `/api/predictions` (GeoJSON & Summary).
- [`app/Http/Controllers/Api/GridApiController.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Http/Controllers/Api/GridApiController.php): Endpoint `/api/grid` (Poligon Kanonik).
- [`resources/views/dashboard/dpi-radar.blade.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/resources/views/dashboard/dpi-radar.blade.php): Halaman operasional DPI Radar Spasial interaktif berbasis MapLibre GL JS & Alpine.js.

#### 1.7. Berkas Pengujian (PHPUnit Test Suites)
- [`tests/Unit/ScientificDataAccessTest.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/tests/Unit/ScientificDataAccessTest.php) (5 tests)
- [`tests/Feature/ScientificDataRepositoryTest.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/tests/Feature/ScientificDataRepositoryTest.php) (5 tests)
- [`tests/Unit/PythonBridgeTest.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/tests/Unit/PythonBridgeTest.php) (4 tests)
- [`tests/Feature/PythonBridgeSecurityTest.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/tests/Feature/PythonBridgeSecurityTest.php) (5 tests)
- [`tests/Unit/OceanographicEngineTest.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/tests/Unit/OceanographicEngineTest.php) (4 tests)
- [`tests/Unit/SpeciesAndHsiEngineTest.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/tests/Unit/SpeciesAndHsiEngineTest.php) (5 tests)
- [`tests/Unit/DomAndBycatchEngineTest.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/tests/Unit/DomAndBycatchEngineTest.php) (7 tests)
- [`tests/Feature/CoreRadarApiTest.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/tests/Feature/CoreRadarApiTest.php) (5 tests)
- [`tests/Feature/DpiRadarViewTest.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/tests/Feature/DpiRadarViewTest.php) (2 tests)

#### 1.8. Komponen Antarmuka mapcn (`resources/views/components/ui/`)
- [`resources/views/components/ui/map-controls.blade.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/resources/views/components/ui/map-controls.blade.php): Floating widget navigasi mapcn (Zoom `+`/`-`, rotating compass, recenter Homeport PPN Prigi, fullscreen toggle).
- [`resources/views/components/ui/map-marker.blade.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/resources/views/components/ui/map-marker.blade.php): Reusable marker dengan animasi radar ping ring (`animate-ping`) dan beacon bercahaya.
- [`resources/views/components/ui/map-popup.blade.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/resources/views/components/ui/map-popup.blade.php): Dark card popover bertema mapcn dengan border halus, badge, dan grid metadata.
- [`resources/views/components/ui/map-layer-switcher.blade.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/resources/views/components/ui/map-layer-switcher.blade.php): Segmented control pill untuk switching instan layer HSI Tuna, Zonasi DOM, Bycatch IOTC, dan Batimetri.
- [`tests/Feature/MapcnComponentsTest.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/tests/Feature/MapcnComponentsTest.php): Suite pengujian Blade component mapcn (5 tests, 21 assertions).

---

### 2. Berkas yang Dimodifikasi (Modified)

- [`app/Providers/AppServiceProvider.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Providers/AppServiceProvider.php): Mendaftarkan binding DI container untuk `ScientificDataRepositoryInterface` dan `PythonComputationBridgeInterface`.
- [`routes/api.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/routes/api.php): Mendaftarkan rute publik `/api/predictions` dan `/api/grid` di dalam middleware group `throttle:60,1`.
- [`routes/web.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/routes/web.php): Memetakan rute operasional aktif `/dashboard/dpi-radar` ke `DashboardController::dpiRadar`.
- [`app/Http/Controllers/Web/DashboardController.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Http/Controllers/Web/DashboardController.php): Menambahkan method `dpiRadar()` untuk menginjeksi metadata harian dan opsi filter ke view.
- [`resources/views/dashboard/dpi-radar.blade.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/resources/views/dashboard/dpi-radar.blade.php): Refactor total mengadopsi kontrol floating mapcn, pulse beacon Homeport PPN Prigi, dan drawer filter mobile responsif.

---

### 3. Ringkasan Pengujian Akhir
- **Total Test PHPUnit:** 75 tests
- **Total Assertions:** 1.849 assertions
- **Status:** **100% PASS — ZERO FAILURES, ZERO REGRESSIONS**

