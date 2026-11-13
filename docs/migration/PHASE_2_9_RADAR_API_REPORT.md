# PHASE 2.9 — CORE RADAR API REPORT
## SEACAST PRIGI V3 — Core Radar Engine & Dynamic Filter

**Tanggal:** 2026-11-04  
**Arsitektur Target:** Laravel 13.40.0 Modular Monolith  
**Routing:** `routes/api.php` (`throttle:60,1`)  
**Controllers:** `App\Http\Controllers\Api\PredictionApiController`, `App\Http\Controllers\Api\GridApiController`  
**Status Evaluasi:** **VERIFIED & IMPLEMENTED (100% PASS)**  

---

### 1. Eksekutif Ringkasan Core Radar API

Phase 2.9 telah berhasil mengimplementasikan endpoint API utama yang dibutuhkan oleh mesin radar spasial dan sistem filter dinamis. Seluruh endpoint mematuhi kontrak schema JSON yang telah diaudit pada Phase 0 ([`API_CONTRACT_INVENTORY.md`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/docs/migration/API_CONTRACT_INVENTORY.md)), mendukung format `summary` dan `geojson`, menerapkan perlindungan rate limiting (`throttle:60,1`), serta menyertakan header HTTP caching untuk efisiensi transfer data.

Struktur file implementasi:
- **Prediction Controller:** [`PredictionApiController.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Http/Controllers/Api/PredictionApiController.php)
- **Grid Controller:** [`GridApiController.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/app/Http/Controllers/Api/GridApiController.php)
- **Registrasi Rute:** [`routes/api.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/routes/api.php)

---

### 2. Inventaris & Spesifikasi Endpoint

#### 2.1. Endpoint 1: `GET /api/predictions`
- **Tujuan:** Mengembalikan data prediksi harian (ringkasan statistik atau layer spasial GeoJSON 8.129 sel).
- **Parameter Query yang Didukung:**
  - `date`: Tanggal prediksi format `YYYY-MM-DD` (default: `2026-11-03`).
  - `format`: `geojson` | `json` | `summary` (default: `geojson`).
  - `summary_only`: `true` | `false` (default: `false`).
  - `species`: `cakalang` | `madidihang` | `legacy_species_removed` | `all`.
  - `min_hsi`: Ambang batas minimum kesesuaian habitat (float $0.00 - 1.00$).
  - `dom_zone`: Filter zona DOM (`Z1_PFG_INSHORE`, `Z2_PFG_OFFSHORE`, `Z3_FEEDING_CORE`, `Z4_NURSERY_GROUND`, `Z5_BYCATCH_SCREENING`, `GENERAL_OCEANIC`).
  - `bycatch`: Filter risiko tangkapan sampingan (`GREEN`, `YELLOW`, `RED`).
  - `sector`: Filter sektor spasial (`ALL`, `JATIM`, `JABAR_JATENG`, `BALI_NUSA_TENGGARA`).
  - `bbox`: Filter bounding box spasial (`minLon,minLat,maxLon,maxLat`).
  - `hotspot_only`: `true` | `false` (hanya sel dengan HSI $\ge 0.70$).
  - `limit`: Batas maksimal sel yang dikembalikan (integer).
- **Header Caching:** `Cache-Control: public, max-age=300, stale-while-revalidate=1800`.

#### 2.2. Endpoint 2: `GET /api/grid`
- **Tujuan:** Mengembalikan layer poligon kanonik PostGIS (8.129 sel EPSG:4326) beserta atribut fisik batimetri dan jarak ke PPN Prigi.
- **Parameter Query:** `sector`, `bbox`, `limit`.
- **Header Caching:** `Cache-Control: public, max-age=3600, stale-while-revalidate=86400`.

---

### 3. Validasi & Penanganan Error

- Seluruh request divalidasi ketat menggunakan Laravel Form Request Validation.
- Format tanggal yang tidak valid (misal `invalid-date`) mengembalikan status HTTP 422 Unprocessable Content.
- Tanggal yang tidak memiliki data di database mengembalikan status HTTP 404 Not Found dengan pesan deskriptif.

---

### 4. Bukti Pengujian Feature API

Pengujian dijalankan pada [`tests/Feature/CoreRadarApiTest.php`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/seacast-prigi-laravel/tests/Feature/CoreRadarApiTest.php):
- `test_predictions_summary_endpoint_returns_correct_schema` -> PASS
- `test_predictions_geojson_endpoint_returns_valid_features` -> PASS
- `test_predictions_filtered_by_dom_zone_and_sector` -> PASS
- `test_canonical_grid_api_returns_features` -> PASS
- `test_predictions_api_rejects_invalid_date` -> PASS
- **Hasil:** 5 tests, 240 assertions, 0 failures (1,75s).

