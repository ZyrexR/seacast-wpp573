# SEACAST PRIGI V3 — GEE TO WEB PIPELINE IMPLEMENTATION REPORT
## Comprehensive Implementation Documentation of Operational Satellite Ingestion, Scientific Transformation, and Web Integration

**Document Version:** 1.0.0  
**Date:** 2026-11-04  
**Implementation Phase:** GEE to Web Integration (Phase 3 Core Data Pipeline)  
**Status:** Completed & Production Ready  
**Target Domain:** WPPNRI 573 (8,129 Canonical Grid Cells, Resolution 0.10°)  
**Target Species:** Cakalang (*Katsuwonus pelamis*), Madidihang (*Thunnus albacares*), Albakor (*Thunnus alalunga*)  
**Reference Harbor:** PPN Prigi, Jawa Timur (111.7275° E, -8.2833° S)  

---

## 1. IMPLEMENTATION SUMMARY & EXECUTIVE OVERVIEW

Sesuai dengan mandat eksekutif proyek **SEACAST PRIGI V3**, implementasi integrasi menyeluruh (*end-to-end integration*) dari **Google Earth Engine (GEE)** ke platform web telah selesai dibangun, divalidasi, dan diuji secara komprehensif.

Pipeline ini menghubungkan:
$$\text{Google Earth Engine} \longrightarrow \text{Data Export (CSV)} \longrightarrow \text{Ingestion Gateway} \longrightarrow \text{QC Preflight Validation} \longrightarrow \text{Feature Transformation} \longrightarrow \text{Ensemble SDM Inference} \longrightarrow \text{PostgreSQL / PostGIS} \longrightarrow \text{Laravel API} \longrightarrow \text{MapLibre Web Canvas}$$

Semua batasan arsitektur dipatuhi secara ketat:
1. **Laravel 13.40.0 Modular Monolith** tetap sebagai satu-satunya framework aplikasi web.
2. **Nol dependensi runtime Node.js/React**; antarmuka peta geospasial menggunakan Blade native, Alpine.js, dan Tailwind CSS dengan bahasa desain *mapcn*.
3. **Bobot model ilmiah terkunci** ($\Sigma w_i = 1.000$, MaxEnt Albakor $0.000$) dan scikit-learn StandardScaler dipertahankan tanpa modifikasi atau *retraining*.
4. **Prinsip Least Privilege**: Pembacaan data web publik menggunakan koneksi aman `seacast_readonly`, sementara ingest basis data dijalankan melalui handler terkontrol di backend.

---

## 2. DETAILED BREAKDOWN OF IMPLEMENTED COMPONENTS

### 2.1 Python GEE Pipeline Transformer (`scripts/gee_pipeline_transformer.py`)
- **Fungsi:** Mengubah berkas ekspor GEE mentah menjadi matriks fitur model 10 kolom, mengeksekusi inferensi scikit-learn `.joblib`, menerapkan klasifikasi DOM 5 zona, menghitung indeks risiko bycatch dan peringatan keselamatan BMKG.
- **Dukungan Skema Masukan Ganda:**
  1. *Skema Produksi Bulanan GEE (104 Kolom):* Berkas `SEACAST_PRIGI_GRID_8129_{YYYY}_{MM}.csv` yang memuat ringkasan piksel satelit, deviasi, dan statistik area.
  2. *Skema Pilot 2024 (51 Kolom):* Berkas `PILOT_2024_WPP573_R010_M{MM}.csv` yang memuat perhitungan fisika oseanografi lengkap.
- **Protokol Imputasi Ilmiah (*Scientific Fallback*):**
  - Mengeliminasi risiko penggantian nol (`NaN -> 0.0`) yang dapat menyebabkan SST terbaca sebagai air beku kutub ($0^\circ\text{C}$).
  - Melakukan join deterministik terhadap atribut spasial statis (`depth_m`, `dist_nm`, koordinat sentroid) dari `canonical_grid_8129_cells.geojson`.
  - Menerapkan imputasi median domain perairan terbuka untuk piksel satelit yang terhalang awan tebal.
- **Pengecekan Rentang Batas Fisik (*Physical Bounds Check*):**
  - SST: $18.0^\circ\text{C} \le T \le 34.0^\circ\text{C}$
  - Gradien Termal: $0.000 \le \nabla T \le 1.000^\circ\text{C/km}$
  - Klorofil-a: $0.01 \le C \le 20.0\text{ mg/m}^3$
  - Kecepatan Angin: $0.0 \le U_{10} \le 35.0\text{ m/s}$
  - Kedalaman: $1.0 \le D \le 8.500\text{ m}$

### 2.2 Laravel Ingestion & QC Service (`App\Domain\ScientificData\Services\GeeDataIngestionService.php`)
- **Tanggung Jawab:**
  - `validateGeeCsv(string $filePath): array` — Memeriksa keberadaan berkas, ukuran minimal (>1 KB), kelengkapan kolom kunci (`cell_id`, SST, Chl-a), jumlah sel tepat 8.129 sel, serta menghitung hash SHA-256 untuk audit.
  - `ingest(string $filePath, ?string $date = null, array $options = []): array` — Mengeksekusi pipeline ingest, memanggil jembatan komputasi Python, memicu invalidasi cache geospasial pada tanggal yang bersangkutan, dan menyimpan rekam jejak audit (*ingestion manifest*).
  - `getIngestionHistory(int $limit = 10): array` — Menyajikan riwayat berkas satelit yang telah diproses beserta metrik ringkasannya.

### 2.3 Artisan Console Command (`App\Console\Commands\IngestGeeDataCommand.php`)
- **Perintah CLI:**
  ```bash
  php artisan seacast:ingest-gee {file} {--date=} {--dry-run} {--no-db} {--limit=0} {--force}
  ```
- **Fitur Antarmuka CLI:**
  - Validasi *preflight* bertingkat dengan tabel status interaktif.
  - Peringatan dini terhadap piksel hilang (*QC Notices*).
  - Mode `--dry-run` untuk verifikasi tanpa penulisan ke basis data atau disk.
  - Pelaporan metrik performa: latensi bridge, latensi total, jumlah sel, jumlah hotspot, dan kunci cache yang diinvalisir.

### 2.4 Asynchronous Background Ingestion Job (`App\Jobs\ProcessGeeIngestionJob.php`)
- **Antrean:** Mendukung pemrosesan latar belakang (*queued worker*) untuk dataset GEE berukuran besar tanpa memblokir permintaan HTTP.
- **Ketahanan:** `$tries = 3`, `$timeout = 300` detik, dengan strategi *exponential backoff* ([15, 45, 90] detik).

### 2.5 Jembatan Komputasi Ilmiah Terdaftar (`App\Infrastructure\Python\PythonComputationBridge.php`)
- Pendaftaran skrip baru pada whitelist aman:
  ```php
  'gee_transformer' => 'scripts/gee_pipeline_transformer.py'
  ```
- Penambahan metode resmi pada kontrak `PythonComputationBridgeInterface`:
  ```php
  public function runGeeTransformer(string $inputCsv, ?string $date = null, array $options = []): PythonComputationResult;
  ```

### 2.6 Endpoint Telemetri Pipeline Satelit (`/api/monitoring/pipeline`)
- **Rute:** `GET /api/monitoring/pipeline`
- **Tujuan:** Menyajikan telemetri operasional satelit langsung (koleksi sensor, status operasional, resolusi temporal, dataset ingest terbaru, dan timestamp terkini).
- **Integrasi Monitoring Publik:** Endpoint utama `/api/monitoring` diperkaya dengan informasi telemetri operasional pipeline satelit yang konsisten.

---

## 3. FEATURE CONTRACT & DATA FLOW SPECIFICATION

| Fitur Model | Satuan | Sumber GEE (104 Kolom) | Sumber Pilot (51 Kolom) | Fallback Ilmiah |
|---|---|---|---|---|
| `sst` | °C | `sst_mean_c_mean` | `sst_c` | Median domain (~28.5 - 29.1°C) |
| `grad_sst` | °C/km | `sst_gradient_deg_km_mean` | `sst_gradient_deg_km` | 0.015 °C/km |
| `front_indicator` | binary [0, 1] | `thermal_front_flag_mean` | `upwelling_favorable_flag` | 0.0 |
| `chla` | mg/m³ | `chla_mg_m3_mean` | `chla_mg_m3` | Median domain (~0.18 mg/m³) |
| `current_velocity` | m/s | `current_speed_m_s_mean` | `current_speed_m_s` | Median domain (~0.25 m/s) |
| `wind_speed` | m/s | `wind_speed_mean_m_s_mean` | `wind_speed_m_s` | Median domain (~4.5 m/s) |
| `wind_stress` | N/m² | `wind_stress_tau_mag_mean` | `wind_stress_tau_mag` | Wu 1982 formulation |
| `ekman_upwelling_velocity` | m/s | `ekman_w_e_m_s_mean` | `ekman_w_e_m_s` | 0.0 m/s |
| `depth` | meter | `depth_m_mean` | `depth_m` | Canonical grid depth |
| `dist_port_km` | km | Geodesic Distance | `dist_prigi_harbor_km` | Canonical grid distance |

---

## 4. INGESTION EXECUTION EVIDENCE & PERFORMANCE BENCHMARK

### 4.1 Eksekusi Uji pada Berkas Produksi Bulanan (104 Kolom)
```
Target File    : ../data/oceanographic/monthly_csv/SEACAST_PRIGI_GRID_8129_2015_01.csv
File Size      : 7,173,869 bytes (7.17 MB)
Grid Cells     : 8,129 cells (100% WPPNRI 573)
Columns        : 104 columns
Schema Variant : 104_COL_GEE_MONTHLY_PRODUCTION
SHA-256 Digest : e8e7bf8e941c5a26...
Preflight QC   : PASSED (11 nulls detected and imputed via spatial domain median)
Model Latency  : 11,886 ms (Bridge Inference)
Total Latency  : 13,001 ms (End-to-End Artisan Command)
Hotspots Count : 1,202 cells (HSI >= 0.70)
Pipeline Status: SUCCESS (Zero Errors)
```

### 4.2 Eksekusi Uji pada Berkas Pilot 2024 (51 Kolom)
```
Target File    : ../data/pilot_2024/PILOT_2024_WPP573_R010_M01.csv
File Size      : 5,795,738 bytes (5.79 MB)
Grid Cells     : 8,129 cells (100% WPPNRI 573)
Columns        : 51 columns
Schema Variant : 51_COL_PILOT_2024
SHA-256 Digest : 0909aad561558c93...
Preflight QC   : PASSED
Model Latency  : 12,314 ms (Bridge Inference)
Total Latency  : 13,141 ms (End-to-End Artisan Command)
Hotspots Count : 848 cells (HSI >= 0.70)
Pipeline Status: SUCCESS (Zero Errors)
```

---

## 5. CONCLUSION & OPERATIONAL READINESS

Pipeline integrasi Google Earth Engine ke platform web SEACAST PRIGI V3 telah diimplementasikan secara utuh dan memenuhi seluruh standar ilmiah serta ketahanan teknis:
- Validasi ketat terhadap 8.129 sel kanonis.
- Fusi sensor dan penanganan nilai kosong berbasis median oseanografi.
- Penegakan bobot model terkunci scikit-learn.
- Persistensi terstruktur ke PostgreSQL 17 + PostGIS 3.6.
- Penyajian real-time melalui REST API dan visualisasi MapLibre GL JS.

Sistem dinyatakan **SIAP UNTUK DIGUNAKAN SECARA OPERASIONAL (PRODUCTION READY)**.

