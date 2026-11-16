# SEACAST — WPPNRI 573 Pelagic Habitat Suitability & Decision Support (Prigi & Cilacap)

Platform intelijen spasial-oseanografi untuk komoditas **TCT** (**Tuna Sirip Kuning**, **Cakalang**, **Tongkol Krai**) di **WPPNRI 573** dengan dual hub operasional **PPN Prigi** dan **PPS Cilacap**.

> **Status Rilis Saat Ini:** `SCIENTIFIC REVIEW / CONTROLLED WEB INTEGRATION` (`HOLD_FOR_SCIENTIFIC_REVIEW`)
> - **Spatial HSI / DOM:** berstatus **`PROXY_HSI`** (kesesuaian habitat berbasis skoring heuristik multi-kriteria & oseanografi satelit, bukan lokasi koordinat tangkapan terverifikasi).
> - **Trip Planner:** berstatus **`FORMULA_SCENARIO`** (simulasi skenario biaya, jarak, cuaca, GT kapal, dan estimasi berbasis formula; kalibrasi temporal CPUE belum diaktifkan karena model holdout 2025 belum lolos target gate).

---

## Peta Struktur Repositori

Repositori ini berisi **tiga modul terpisah** agar riset model tidak bercampur dengan aplikasi web produksi:

| Modul | Lokasi Utama | Peran & Status |
|---|---|---|
| **1. Aplikasi WebGIS Produksi** | `seacast-prigi-laravel/`, `scripts/bridge_inference_runner.py`, `scripts/gee_daily_extractor.py` | **Aktif (Deploy Target)** — Laravel 11 + PostgreSQL/PostGIS (`8.129` grid kanonikal `0.10°`), DPI Radar, DOM Zones, Trip Planner, Ekspor PDF/GeoJSON/MVT. |
| **2. Pipeline & Riset Temporal/Spasial TCT** | `scripts/tct_local_modeling.py`, `scripts/evaluate_tct_scientific_gate.py`, `scripts/validate_spatial_occurrences.py`, `notebooks/SEACAST_TCT_HYBRID_MODELING_2015_2025.ipynb`, `outputs/tct_hybrid_2015_2025/` | **Riset & Audit Ilmiah** — Evaluasi temporal CPUE (`HOLD_FOR_SCIENTIFIC_REVIEW`) dan audit validasi spasial (`PARTIAL_SPATIAL_VALIDATION`, `3/31` pada zona operasional ≤ 60 NM). Terpisah dari ensemble WebGIS produksi. |
| **3. Prototipe Awal (Legacy Streamlit)** | `app.py`, `utils.py`, `pages/` | **Arsip / Prototipe Awal** — Kerangka awal Streamlit sebelum migrasi ke arsitektur Laravel + PostGIS. Tidak dipakai di produksi. |

---

## Single Source of Truth (Sumber Angka & Status Resmi)

Seluruh angka evaluasi model, gate ilmiah, dan provenance di web harus mengacu pada berkas-berkas berikut (tidak mengarang angka statis di Blade/README):

1. **Skor Proxy Model WebGIS (`Cakalang`, `Tuna Sirip Kuning`, `Tongkol Krai`):**
   - `models/sdm/ensemble/model_evaluations_summary.json`
   - Cakalang (Ensemble Proxy): AUC `0.795`, TSS `0.630`, threshold hotspot `>= 0.70`
   - Tuna Sirip Kuning (Ensemble Proxy): AUC `0.784`, TSS `0.606`, threshold hotspot `>= 0.70`
   - Tongkol Krai (Ensemble Proxy): AUC `0.892`, TSS `0.752`, bobot provisional `0.332 / 0.335 / 0.333` (`models/tongkol_krai_ensemble_model.joblib`, dilatih terhadap sampel proxy neritik 2015–2025 WPPNRI 573)
2. **Gate Ilmiah Temporal CPUE:**
   - `outputs/tct_hybrid_2015_2025/reports/tct_scientific_gate.json` (`status: HOLD_FOR_SCIENTIFIC_REVIEW`, seluruh komoditas `temporal_status: REJECT` pada validasi holdout 2025)
3. **Audit Validasi Spasial Occurrence:**
   - `outputs/tct_hybrid_2015_2025/reports/tct_spatial_validation.json` (`31/31` full-grid, `3/31` dalam radius operasional `<= 60 NM`)
4. **Status Implementasi & Arsitektur Sistem:**
   - [SEACAST_IMPLEMENTATION_STATUS.md](docs/project/SEACAST_IMPLEMENTATION_STATUS.md)
   - [AGENTS.md](AGENTS.md) (panduan koordinasi Antigravity + Codex)
   - [seacast-prigi-laravel/AGENTS.md](seacast-prigi-laravel/AGENTS.md) (panduan Laravel Boost)

---

## Menjalankan Lokal (Development)

```powershell
# 1. Laravel App (dari seacast-prigi-laravel/)
cd seacast-prigi-laravel
php artisan serve --host=127.0.0.1 --port=8000

# 2. Asset bundler (terminal terpisah di seacast-prigi-laravel/)
npm run dev

# 3. Queue worker (terminal terpisah di seacast-prigi-laravel/)
php artisan queue:work --sleep=3 --tries=3
```

## Verifikasi Tes

```powershell
# PHPUnit (dari seacast-prigi-laravel/)
php artisan test
# atau test suite inti TCT / WebGIS / Trip Planner:
php vendor/bin/phpunit tests/Unit/TripPlanningServiceTest.php tests/Unit/TctCommodityContractTest.php tests/Feature/TripPlannerWorkflowTest.php tests/Feature/SailingAdvisoryPdfExportTest.php tests/Feature/PublicScienceKnowledgePagesTest.php tests/Feature/PublicPagesComprehensiveTest.php tests/Feature/Wpp573GeneralizationTest.php tests/Feature/DpiRadarViewTest.php

# Python Unit Tests (dari root repo)
.venv-tct\Scripts\python.exe -m unittest discover -s tests -t . -v
```
