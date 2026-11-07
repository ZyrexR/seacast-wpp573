# SEACAST PRIGI V3 — GEE TO WEB DATA PIPELINE AUDIT
## Comprehensive Scientific & Technical Audit of Satellite Telemetry, Feature Extraction, Model Inference, and Persistence Pipelines

**Document Version:** 1.0.0  
**Date:** 2026-11-04  
**Spatial Domain:** WPPNRI 573 (Canonical Grid 8,129 Cells, Resolution 0.10°)  
**Target Species:** Cakalang (*Katsuwonus pelamis*), Madidihang (*Thunnus albacares*), Albakor (*Thunnus alalunga*)  
**Reference Port:** PPN Prigi, Jawa Timur (111.7275° E, -8.2833° S)  
**Execution Environment:** Windows PowerShell / PHP 8.3 / Python 3.14 / PostgreSQL 17.11 + PostGIS 3.6.2  

---

## 1. EXECUTIVE SUMMARY & AUDIT OBJECTIVE

Tujuan dari audit ini adalah memetakan dan mengevaluasi seluruh mata rantai pipeline data geospasial kelautan dalam proyek **SEACAST PRIGI V3**, mulai dari akuisisi citra satelit pada **Google Earth Engine (GEE)**, ekspor data tabular berstruktur, validasi kualitas data (QC), transformasi fitur, inferensi model pembelajaran mesin (Ensemble SDM), persistensi ke **PostgreSQL/PostGIS**, hingga penyajian pada **Laravel API** dan visualisasi **MapLibre GL JS**.

Audit ini didasarkan pada pemeriksaan langsung terhadap file kode sumber aktual, artefak model pembelajaran mesin, skrip ekstraksi GEE, berkas CSV hasil ekspor, struktur tabel basis data, dan konfigurasi aplikasi.

---

## 2. INVENTORY OF SATELLITE ASSETS & GEE PIPELINE SCRIPTS

Berdasarkan audit repositori pada folder `gee/`, ditemukan komponen-komponen implementasi ekstraksi satelit berikut:

### 2.1 Skrip Produksi Utama GEE
- **File:** `gee/SEACAST_PRIGI_GEE_PRODUCTION_2015_2025_v2_3_2.js` (1.693 baris kode)
  - **Status:** *Production Candidate v2.3.2* (ES5-compliant untuk GEE Code Editor).
  - **Cakupan Spasial:** Seluruh WPPNRI 573, grid kanonis 8.129 sel (resolusi 0.10° x 0.10°).
  - **Periode:** 2015-01 s/d 2025-12 (132 bulan observasi oseanografi).
  - **Dataset Sumber Resmi:**
    1. *Sea Surface Temperature (SST):* NOAA CDR OISST v2.1 (`NOAA/CDR/OISST/V2_1`, 0.25°) dan NASA MODIS-Aqua L3SMI (`NASA/OCEANDATA/MODIS-Aqua/L3SMI`, 4 km).
    2. *Klorofil-a (Chl-a):* NASA MODIS-Aqua L3SMI (`chlor_a`, 4 km) dengan cadangan JAXA GCOM-C SGLI (`JAXA/GCOM-C/R3/OCEAN/SST/4KM_MONTHLY` / `CHLA`).
    3. *Angin Permukaan 10m:* NOAA CFSv2 6-Hourly Harmonized (`NOAA/CFSV2/FOR6H_HARMONIZED`).
    4. *Arus Laut Permukaan:* HYCOM GLBu0.08 (`HYCOM/sea_water_velocity`) dengan transisi ke CMEMS untuk periode terkini.
    5. *Batimetri & Topografi:* GEBCO 2023 Grid / NOAA ETOPO1 (`NOAA/NGDC/ETOPO1`).
    6. *Curah Hujan:* GPM IMERG Monthly (`NASA/GPM_L3/IMERG_MONTHLY_V06`).

### 2.2 Modul JavaScript Terstruktur (`gee/js/`)
- `00_aoi_and_grid_builder.js`: Pembangunan Sub-AOI Prigi dan grid kanonis.
- `01_bathymetry_and_terrain.js`: Penurunan batimetri, kelerengan (*slope*), kontur -200m s/d -1.000m.
- `02_sst_and_thermal_fronts.js`: Fusi sensor SST, estimasi gradien termal Sobel (°C/km), dan deteksi *thermal front*.
- `03_chla_and_productivity.js`: Komposit bulanan Klorofil-a, transformasi logaritmik $\log_{10}$, dan *gap-filling*.
- `04_wind_and_ekman_dynamics.js`: Vektor tegangan angin ($\tau_x, \tau_y$, Wu 1982), kecepatan pemompaan Ekman (*Ekman upwelling pumping velocity* $w_e$) dengan suku gradien Coriolis ($\beta$).
- `05_scenario_predictor_stacks.js`: Penyusunan tumpukan prediktor 6 skenario klimatologi dan oseanografi.
- `06_export_and_qc_manifest.js`: Orkestrasi ekspor batch ke Google Drive dan manifest hash SHA-256.

### 2.3 Skrip Otomasi Python GEE API (`gee/py/` & `scripts/`)
- `gee/py/config.py`: Konfigurasi resmi sub-AOI, dataset catalog ID, dan 6 skenario spasial.
- `gee/py/run_gee_pipeline.py`: Otomasi ekstraksi server-side via Earth Engine Python API (GCP Project: `seca-513505`).
- `scripts/phase_4_2_pilot/run_pilot_2024_pipeline.py`: Pipeline eksekusi pilot 12 bulan penuh tahun 2024 (8.129 sel) yang menghasilkan dataset terverifikasi pada `data/pilot_2024/`.

---

## 3. INVENTORY OF EXPORTED SCIENTIFIC DATASETS

Pemeriksaan direktori `data/` menemukan arsip dataset geospasial kelautan berikut:

| Lokasi Arsip | Format | Jumlah Berkas | Jumlah Sel per Berkas | Kolom | Keterangan |
|---|---|---|---|---|---|
| `data/oceanographic/monthly_csv/` | CSV | 120 berkas | 8.129 sel | 104 kolom | Seri waktu lengkap 10 tahun (2015_01 s/d 2024_12) |
| `data/pilot_2024/` | CSV | 12 berkas | 8.129 sel | 51 kolom | Pilot dataset tahun 2024 (M01 s/d M12) dengan metadata lengkap |
| `data/predictions/` | CSV | 1 berkas | 8.129 sel | 26 kolom | `SEACAST_PRIGI_PREDICTIONS_2025_SAMPLE.csv` |
| `data/geojson/` | GeoJSON | 1 berkas | 8.129 fitur | - | `canonical_grid_8129_cells.geojson` (geometri batas sel EPSG:4326) |

---

## 4. FEATURE MAPPING & SCALER ALIGNMENT AUDIT

### 4.1 Persyaratan Matriks Fitur Model Ilmiah
Artefak normalisasi `models/scaler_oceanographic.joblib` (StandardScaler) dan model ensemble (`cakalang_ensemble_model.joblib`, `madidihang_ensemble_model.joblib`, `albakor_ensemble_model.joblib`) membutuhkan **tepat 10 variabel input** dalam urutan berikut:

$$X = [x_1, x_2, x_3, x_4, x_5, x_6, x_7, x_8, x_9, x_{10}]$$

| No | Nama Fitur Model | Satuan | Sumber Kolom GEE CSV (104 Kolom) | Sumber Kolom Pilot 2024 (51 Kolom) | Batas Fisik Valid |
|---|---|---|---|---|---|
| 1 | `sst` | °C | `sst_mean_c_mean` (atau `sst_mean_c`) | `sst_c` | $18.0 \le \text{SST} \le 34.0$ |
| 2 | `grad_sst` | °C/km | `sst_gradient_deg_km_mean` | `sst_gradient_deg_km` | $0.000 \le \nabla T \le 1.000$ |
| 3 | `front_indicator` | biner [0, 1] | `thermal_front_flag_mean` | `upwelling_favorable_flag` (atau $\nabla T \ge 0.035$) | $0 \text{ atau } 1$ |
| 4 | `chla` | mg/m³ | `chla_mg_m3_mean` | `chla_mg_m3` | $0.01 \le \text{Chl-a} \le 20.0$ |
| 5 | `current_velocity` | m/s | `current_speed_m_s_mean` | `current_speed_m_s` | $0.00 \le u_{\text{curr}} \le 3.00$ |
| 6 | `wind_speed` | m/s | `wind_speed_mean_m_s_mean` | `wind_speed_m_s` | $0.00 \le U_{10} \le 35.00$ |
| 7 | `wind_stress` | N/m² | `wind_stress_tau_mag_mean` | `wind_stress_tau_mag` | $0.000 \le \tau \le 1.500$ |
| 8 | `ekman_upwelling_velocity` | m/s | `ekman_w_e_m_s_mean` (atau `ekman_w_e_m_s`) | `ekman_w_e_m_s` | $-10^{-3} \le w_e \le 10^{-3}$ |
| 9 | `depth` | meter | `depth_m_mean` | `depth_m` (dikonversi absolut/positif) | $1.0 \le D \le 8500.0$ |
| 10 | `dist_port_km` | kilometer | Geodesic Distance ke PPN Prigi | `dist_prigi_harbor_km` | $0.0 \le \text{Dist} \le 1500.0$ |

### 4.2 Verifikasi Bobot Ensemble yang Terkunci (*Locked Baseline*)
Dalam proses inferensi, ketiga model spesies harus mematuhi bobot ensemble terkunci yang telah divalidasi pada Phase 1.1:

- **Cakalang (*Katsuwonus pelamis*):**
  $$HSI_{\text{cakalang}} = 0.320 \cdot P_{\text{MaxEnt}} + 0.355 \cdot P_{\text{RF}} + 0.325 \cdot P_{\text{GLM}}$$
- **Madidihang (*Thunnus albacares*):**
  $$HSI_{\text{madidihang}} = 0.295 \cdot P_{\text{MaxEnt}} + 0.365 \cdot P_{\text{RF}} + 0.340 \cdot P_{\text{GLM}}$$
- **Albakor (*Thunnus alalunga*):**
  $$HSI_{\text{albakor}} = 0.000 \cdot P_{\text{MaxEnt}} + 0.517 \cdot P_{\text{RF}} + 0.483 \cdot P_{\text{GLM}}$$
  *(MaxEnt berbobot 0.000 karena karakteristik persebaran data pelagis dalam sub-tropis/termoklin).*

---

## 5. MISSING VALUES & SATELLITE PIXEL QUALITY CONTROL AUDIT

Hasil pengujian langsung terhadap file `SEACAST_PRIGI_GRID_8129_2015_01.csv` (8.129 baris) menunjukkan temuan penting:

1. **Integritas Grid:** Tepat 8.129 sel kanonis tanpa ada sel yang hilang atau terduplikasi (`cell_id` konsisten dari `WPP573_R010_C001_R052` hingga `WPP573_R010_C240_R028`).
2. **Keberadaan Nilai Kosong (*Null/NaN*):**
   - `sst_mean_c_mean`: 142 nilai kosong (1,75% dari total sel).
   - `chla_mg_m3_mean`: 401 nilai kosong (4,93% dari total sel).
   - `current_speed_m_s_mean`: 162 nilai kosong (1,99% dari total sel).
   - `wind_speed_mean_m_s_mean` & `wind_stress_tau_mag_mean`: 15 nilai kosong (0,18%).
   - `depth_m_mean`: 41 nilai kosong (0,50%).
3. **Penyebab Ilmiah:**
   - Nilai kosong pada SST dan Chl-a disebabkan oleh tutupan awan tebal (*cloud mask*) pada sensor optik inframerah MODIS-Aqua.
   - Nilai kosong pada arus dan batimetri terjadi pada sel tepi yang berbatasan dengan garis pantai (*coastal boundary cells*) atau kepulauan kecil di mana resolusi model hidrodinamika (HYCOM/CMEMS 1/12°) tidak menjangkau perairan dangkal sangat dekat daratan.
4. **Bahaya Substitusi Nol (`NaN -> 0.0`):**
   - Jika nilai kosong SST diganti menjadi $0.0^\circ\text{C}$, model akan menginterpretasikan perairan Samudra Hindia tropis sebagai air beku kutub, yang menyebabkan kesalahan fatal pada HSI dan memicu anomali klasifikasi DOM.
5. **Strategi Imputasi Ilmiah (*Scientific Fallback Protocol*):**
   - **Fitur Spasial Statis:** Seluruh atribut statis (`depth`, `dist_port_km`, `dist_port_nm`, koordinat sentroid) diambil secara deterministik dari `canonical_grids` / `canonical_grid_8129_cells.geojson`.
   - **Fitur Oseanografi Dinamis:** Nilai kosong diimputasi menggunakan nilai median spasial domain perairan terbuka pada bulan bersangkutan:
     - $\text{SST}_{\text{fallback}} = \text{median}(\text{SST}_{\text{valid}}) \approx 28.8^\circ\text{C}$
     - $\text{Chl-a}_{\text{fallback}} = \text{median}(\text{Chl-a}_{\text{valid}}) \approx 0.18\text{ mg/m}^3$
     - $\text{Current}_{\text{fallback}} = \text{median}(u_{\text{valid}}) \approx 0.25\text{ m/s}$
     - $\text{Grad SST}_{\text{fallback}} = 0.015^\circ\text{C/km}$
     - $\text{Front Indicator}_{\text{fallback}} = 0$
     - $\text{Ekman}_{\text{fallback}} = 0.0\text{ m/s}$

---

## 6. DATABASE STORAGE & SECURITY BOUNDARY AUDIT

1. **Warehouse PostgreSQL 17 + PostGIS 3.6:**
   - Basis data `seacast_prigi` berisi:
     - `canonical_grids`: 8.129 baris poligon geometris (SRID 4326).
     - `daily_predictions`: 8.129 baris (kunci primer komposit: `prediction_date, cell_id`).
     - `grid_monthly_predictions`: 97.548 baris (kunci primer komposit: `year_month, cell_id`).
2. **Pemisahan Hak Akses (*Principle of Least Privilege*):**
   - `seacast_readonly`: Pengguna aplikasi web (read-only). Semua permintaan HTTP publik hanya menggunakan pengguna ini untuk mencegah manipulasi data gudang ilmiah.
   - `postgres` (atau *dedicated writer*): Digunakan secara eksklusif oleh skrip backend dan *Artisan Command* ingest untuk mencatat hasil inferensi baru ke dalam tabel `daily_predictions`.
3. **Cache & File Export Web:**
   - Selain basis data PostGIS, inferensi harian menghasilkan berkas JSON terindeks cepat pada `web/public/data/daily_latest.json` untuk rendering instan pada peta web MapLibre GL JS tanpa membebani basis data relasional.

---

## 7. TARGET PIPELINE GAPS & REMEDIATION PLAN

| Area Pipeline | Kondisi Sebelum Audit | Solusi Implementasi Phase 3 GEE Integration |
|---|---|---|
| **Ekstraksi GEE** | Skrip GEE berjalan mandiri di Code Editor tanpa keterhubungan otomatis dengan Laravel | Skrip otomatisasi Python (`scripts/gee_pipeline_transformer.py`) yang membaca ekspor GEE secara konsisten |
| **Validasi Skema** | Belum ada validator otomatis Laravel untuk memeriksa integritas berkas CSV GEE sebelum inferensi | Pembangunan `GeeDataIngestionService` dengan validasi preflight (8.129 sel, rentang fisik, format kolom) |
| **Penanganan NaN** | Penggantian nol yang berisiko merusak akurasi model | Penerapan imputasi ilmiah berbasis grid kanonis dan median spasial bulanan |
| **Orkestrasi CLI** | Hanya ada `seacast:infer-daily` berbasis data simulasi | Pembuatan Artisan command `seacast:ingest-gee` yang mendukung opsi `--dry-run`, `--date`, dan `--force` |
| **Audit Provenance** | Informasi satelit belum tercatat secara transparan pada antarmuka web | Penyediaan telemetri metadata satelit pada endpoint API `/api/monitoring/pipeline` |

---
*Laporan ini menjadi acuan spesifikasi teknis untuk implementasi arsitektur GEE to Web Integration pada SEACAST PRIGI V3.*

