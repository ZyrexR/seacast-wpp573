# SEACAST — WPPNRI 573 Pelagic Habitat Suitability & Decision Support (Prigi & Cilacap)

> **Judul Inovasi:** *“Pemodelan Spasial Daerah Penangkapan Ikan Presisi Berbasis Dinamika Oseanografi Satelit dan Ensemble Machine Learning di WPPNRI 573”*  
> **Demo Produksi:** [https://www.seacast.my.id](https://www.seacast.my.id)

Platform intelijen spasial-oseanografi untuk komoditas **TCT** (**Tuna Sirip Kuning**, **Cakalang**, **Tongkol Krai**) di **WPPNRI 573** dengan dual hub operasional **PPN Prigi** (Jawa Timur) dan **PPS Cilacap** (Jawa Tengah).

> **Status Rilis Saat Ini:** `SCIENTIFIC REVIEW / CONTROLLED WEB INTEGRATION` (`HOLD_FOR_SCIENTIFIC_REVIEW`)
> - **Spatial HSI / DOM:** berstatus **`PROXY_HSI`** (kesesuaian habitat berbasis skoring heuristik multi-kriteria & oseanografi satelit pada `8.129` sel grid kanonikal `0,10°`, bukan lokasi koordinat tangkapan lapangan terverifikasi).
> - **Trip Planner:** berstatus **`FORMULA_SCENARIO`** (simulasi skenario biaya BBM, jarak mil laut `1 NM = 1,852 km`, durasi melaut `trip_days`, kapasitas palka & kesiapan kapal `vessel_readiness`, cuaca BMKG, serta kepatuhan jalur Permen KP No. 59/2020; kalibrasi temporal CPUE belum diaktifkan karena model holdout 2025 belum lolos target gate).

---

## Notebook Model (`.ipynb`), Skrip Pelatihan, & Evaluasi (AUC / TSS)

### 1. Tautan Notebook & Artefak Model
- **Notebook Utama Ensemble SDM Spasial (`MaxEnt + Random Forest + GLM`, 8.129 Sel, DOM 5-Zona):** [`notebooks/SEACAST_PRIGI_COLAB_SDM_PIPELINE.ipynb`](notebooks/SEACAST_PRIGI_COLAB_SDM_PIPELINE.ipynb)
- **Notebook Eksperimen Kalibrasi Temporal TCT (2015–2025):** [`notebooks/SEACAST_TCT_HYBRID_MODELING_2015_2025.ipynb`](notebooks/SEACAST_TCT_HYBRID_MODELING_2015_2025.ipynb)
- **Skrip Pelatihan 5-Fold CV Ensemble SDM Tongkol Krai:** [`scripts/train_tongkol_krai_sdm.py`](scripts/train_tongkol_krai_sdm.py)
- **Artefak Model Terlatih (`.joblib`):** [`models/cakalang_ensemble_model.joblib`](models/cakalang_ensemble_model.joblib), [`models/madidihang_ensemble_model.joblib`](models/madidihang_ensemble_model.joblib), [`models/tongkol_krai_ensemble_model.joblib`](models/tongkol_krai_ensemble_model.joblib), [`models/scaler_oceanographic.joblib`](models/scaler_oceanographic.joblib)
- **Manifest Evaluasi Resmi (SSOT):** [`models/sdm/ensemble/model_evaluations_summary.json`](models/sdm/ensemble/model_evaluations_summary.json)

### 2. Tabel Evaluasi Model Spasial SDM (Metrik Proxy Lingkungan)

| Algoritma | Komoditas | Proxy ROC-AUC | Proxy TSS | Bobot Ensemble |
|---|---|---|---|---|
| MaxEnt | Tongkol Krai (*Auxis thazard*) | `0.888` | `0.725` | `0.332` |
| Random Forest | Tongkol Krai (*Auxis thazard*) | `0.891` | `0.756` | `0.335` |
| GLM | Tongkol Krai (*Auxis thazard*) | `0.888` | `0.725` | `0.333` |
| **Ensemble** | **Tongkol Krai (*Auxis thazard*)** | **`0.892`** | **`0.752`** | **`1.000`** |
| MaxEnt | Cakalang (*Katsuwonus pelamis*) | `0.774` | `0.084` | `0.320` |
| Random Forest | Cakalang (*Katsuwonus pelamis*) | `0.805` | `0.610` | `0.355` |
| GLM | Cakalang (*Katsuwonus pelamis*) | `0.750` | `0.473` | `0.325` |
| **Ensemble** | **Cakalang (*Katsuwonus pelamis*)** | **`0.795`** | **`0.630`** | **`1.000`** |
| MaxEnt | Madidihang / Tuna Sirip Kuning (*Thunnus albacares*) | `0.722` | `0.000` | `0.295` |
| Random Forest | Madidihang / Tuna Sirip Kuning (*Thunnus albacares*) | `0.804` | `0.586` | `0.365` |
| GLM | Madidihang / Tuna Sirip Kuning (*Thunnus albacares*) | `0.755` | `0.422` | `0.340` |
| **Ensemble** | **Madidihang / Tuna Sirip Kuning (*Thunnus albacares*)** | **`0.784`** | **`0.606`** | **`1.000`** |

---

## Dataset yang Digunakan di Repositori

### A. Dataset Spasial & Oseanografi Satelit WPPNRI 573
- **Grid Kanonikal 8.129 Sel (0,10° × 0,10°, EPSG:4326):** [`data/qa/phase_1_1/grid/GRID_010_REGIONAL_CELLS_QA.csv`](data/qa/phase_1_1/grid/GRID_010_REGIONAL_CELLS_QA.csv) & [`data/boundaries/wppnri_573_web.geojson`](data/boundaries/wppnri_573_web.geojson)
- **Panel Oseanografi Satelit Bulanan (SST, Chl-a, Batimetri, Front Termal, Arus, Gelombang):** [`data/pilot_2024/PILOT_2024_WPP573_R010_12MONTHS.csv`](data/pilot_2024/PILOT_2024_WPP573_R010_12MONTHS.csv) & [`data/predictions/SEACAST_PRIGI_PREDICTIONS_2025_SAMPLE.csv`](data/predictions/SEACAST_PRIGI_PREDICTIONS_2025_SAMPLE.csv)
- **Sampel Presensi Proxy & Background SDM:** [`data/sdm/presence/presence_proxy_cakalang.geojson`](data/sdm/presence/presence_proxy_cakalang.geojson), [`data/sdm/presence/presence_proxy_madidihang.geojson`](data/sdm/presence/presence_proxy_madidihang.geojson), [`data/sdm/background/target_group_background.geojson`](data/sdm/background/target_group_background.geojson)

### B. Dataset Statistik Pendaratan, Armada, & Konservasi (PPN Prigi & PPS Cilacap)
- **Statistik Pendaratan, Armada (814 unit), & Trip Bulanan PPS Cilacap (2016–2025, 120 bulan, 286.680 trip):** [`seacast-prigi-laravel/database/data/raw/pps_cilacap_statistics_2016_2025.csv`](seacast-prigi-laravel/database/data/raw/pps_cilacap_statistics_2016_2025.csv)
- **Statistik Pendaratan & Oseanografi Bulanan PPN Prigi (2015–2025, 132 bulan):** [`data/master_prigi_all_inclusive.csv`](data/master_prigi_all_inclusive.csv)
- **Profil Armada & Trip Melaut PPN Prigi:** [`data/prigi_fleet_by_gt_2015_2025.csv`](data/prigi_fleet_by_gt_2015_2025.csv) & [`data/prigi_fleet_trips_2012_2025.csv`](data/prigi_fleet_trips_2012_2025.csv)
- **Rekam Indikator Bycatch Elasmobranchia & Parameter Ekologi Spesies:** [`data/prigi_elasmobranch_bycatch_records.csv`](data/prigi_elasmobranch_bycatch_records.csv) & [`data/species_traits.csv`](data/species_traits.csv)

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
