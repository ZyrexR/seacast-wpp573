# PHASE 2.0 — FORENSIC SOURCE AUDIT & ENGINE DISCOVERY
## SEACAST PRIGI V3 — Core Radar Engine & Dynamic Filter

**Tanggal:** 2026-11-04  
**Auditor:** Senior Scientific Software Engineer & Geospatial Database Architect  
**Lingkungan:** PHP 8.3.36 (CLI) / Laravel 13.40.0 / Python 3.14.7 / PostgreSQL 17.11 / PostGIS 3.6.2  
**Database Name:** `seacast_prigi` (Least-privilege role: `seacast_readonly`)  
**Status Audit:** **VERIFIED & COMPLETED**  

---

### 1. Eksekutif Ringkasan Audit Forensik

Audit forensik Phase 2.0 telah memetakan secara komprehensif seluruh komponen komputasi ilmiah, skrip Python, model prediktif `.joblib`, metadata evaluasi ensemble, skema geospasial PostGIS, dan relasi endpoint lama. Seluruh aset siap diintegrasikan ke dalam arsitektur Laravel 13 Modular Monolith tanpa mengubah formula ilmiah, data sumber, maupun batasan metodologis.

---

### 2. Inventaris Python Engine & Dependensi Runtime

#### 2.1. Spesifikasi Runtime Host
- **Binary Python:** `C:\Users\ThinkPad\AppData\Local\Python\pythoncore-3.14-64\python.exe`
- **Versi Python:** 3.14.7 (64-bit) on Windows x64.
- **Pustaka Utama:**
  - `numpy`: 2.5.3
  - `pandas`: 3.0.5
  - `scikit-learn`: 1.9.1 (Kompatibel dengan evaluasi model)
  - `scipy`: 1.18.1
  - `joblib`: 1.6.0
  - `shapely`: 2.2.0
  - `psycopg2-binary`: 2.9.13

#### 2.2. Catatan Kompatibilitas Versi Scikit-Learn
Model ensemble `.joblib` awalnya di-pickle menggunakan `scikit-learn 1.6.1`. Saat di-unpickle pada `scikit-learn 1.9.1`, Python mengeluarkan `InconsistentVersionWarning`. Namun, eksekusi inferensi probabilitas via `predict_proba()` pada model sub-komponen (MaxEnt/LogisticRegression, Random Forest, GLM) telah diuji dan menghasilkan output probabilitas numerik yang valid dan konsisten:
```
Sample test vector: [28.5, 0.04, 1.0, 0.45, 0.3, 5.0, 0.04, 1e-5, 500.0, 30.0]
Output probabilities:
- MaxEnt (Logistic): 0.4098
- Random Forest:    0.5694
- GLM:              0.4102
```

---

### 3. Model Habitat & Rekonsiliasi Bobot Ilmiah Terkunci

#### 3.1. Penemuan Krusial (Discrepancy Discovery)
Dalam file bundle `.joblib` (`cakalang_ensemble_model.joblib`, `madidihang_ensemble_model.joblib`, `legacy_species_removed_ensemble_model.joblib`), dictionary default internal tersimpan sebagai stub awal: `{'maxent': 0.4, 'rf': 0.35, 'glm': 0.25}`.
**Namun**, berdasarkan dokumen baseline ilmiah terkunci (`models/sdm/ensemble/model_evaluations_summary.json`), PRD SEACAST V2.3.2, dan Mandat Phase 0/1, bobot ensemble yang disetujui secara ilmiah adalah:

| Spesies | MaxEnt | Random Forest | GLM | Evaluasi AUC | Evaluasi TSS | Catatan Metodologis |
|---|---:|---:|---:|---:|---:|---|
| **Cakalang** (*Katsuwonus pelamis*) | **0.320** | **0.355** | **0.325** | 0.795 | 0.630 | Heuristic Proxy Niche Screening |
| **Madidihang** (*Thunnus albacares*) | **0.295** | **0.365** | **0.340** | 0.784 | 0.606 | Thermal front dominant sensitivity |
| **Legacy species removed from TCT scope** (*Current TCT scope exclusion*) | **0.000** | **0.517** | **0.483** | 0.860 | 0.743 | **MaxEnt dinolkan karena AUC proxy acak (0.507)** |

> [!IMPORTANT]
> Arsitektur Laravel dan Python Bridge wajib memaksakan (*enforce*) bobot ilmiah baseline di atas (`0.320/0.355/0.325`, `0.295/0.365/0.340`, `0.000/0.517/0.483`) dan menolak penggunaan bobot stub yang belum terkalibrasi.

#### 3.2. Fitur Input Scaler (`scaler_oceanographic.joblib`)
Scaler menggunakan 10 variabel terurut:
1. `sst` (°C)
2. `grad_sst` (°C/km)
3. `front_indicator` (0.0 atau 1.0)
4. `chla` (mg/m³)
5. `current_velocity` (m/s)
6. `wind_speed` (m/s)
7. `wind_stress` (N/m²)
8. `ekman_upwelling_velocity` (m/s)
9. `depth` (m)
10. `dist_port_km` (km)

---

### 4. Skema Database Spasial PostGIS Aktual (`seacast_prigi`)

Audit langsung melalui PostgreSQL information schema memvalidasi tabel-tabel berikut:

#### 4.1. Tabel `canonical_grids` (8.129 Baris)
- `cell_id` (varchar, Primary Identifier)
- `cell_class` (varchar, misal 'PURE_OCEAN')
- `center_lon`, `center_lat` (double precision)
- `depth_m` (double precision, batimetri GEBCO)
- `dist_port_km`, `dist_port_nm` (double precision, jarak geodetik ke PPN Prigi `111.7275°E, -8.2833°S`)
- `is_shelf_break` (boolean, depth 150–1200 m)
- `is_prigi_inshore` (boolean, dist <= 12 NM)
- `is_prigi_offshore` (boolean, dist 12–60 NM)
- `sector` (varchar: 'JABAR_JATENG', 'JATIM', 'BALI_NUSA_TENGGARA')
- `geom` (geometry(Polygon, 4326), spatial GiST index `idx_canonical_grids_geom`)

#### 4.2. Tabel `daily_predictions` (8.129 Baris Snapshot: 2026-11-03)
- `prediction_date` (date)
- `cell_id` (varchar)
- `hsi_cakalang`, `hsi_madidihang`, `hsi_legacy_removed` (numeric, skala 0.000–1.000)
- `is_hotspot` (boolean, True jika HSI Cakalang >= 0.70 atau Madidihang >= 0.70)
- `dom_zone` (varchar)
- `bycatch_traffic_light` (varchar: 'GREEN', 'YELLOW', 'RED')
- `sst_c`, `chla_mg_m3`, `current_velocity_ms`, `wave_height_m`, `wind_speed_ms` (numeric)
- `safety_advisory` (varchar)
- `created_at` (timestamp with time zone)

#### 4.3. Tabel `grid_monthly_predictions` (97.548 Baris: 12 Bulan Tahun 2025)
- Mencakup `year_month` dari `2025_01` hingga `2025_12` (masing-masing tepat 8.129 sel).

---

### 5. Aturan Bisnis Ilmiah (Delineasi DOM & Bycatch)

#### 5.1. Delineasi Zona DOM & Urutan Prioritas Mutlak
Berdasarkan PRD SEACAST V2.3.2 dan `scripts/daily_inference.py`:
1. **Z4 (NURSERY_GROUND):** `depth <= 150 m` DAN `dist_port <= 15 NM`.
2. **Z5 (BYCATCH_SCREENING / THERMAL FRONT):** `depth 150–1200 m` (shelf break) DAN `sst <= 26.5 °C` DAN `grad_sst >= 0.035 °C/km`.
3. **Z3 (FEEDING_CORE):** `chla >= 0.40 mg/m³` DAN `grad_sst >= 0.035 °C/km`.
4. **Z1 (PFG_INSHORE):** `dist_port <= 12 NM` DAN (`hsi_cakalang >= 0.55` ATAU `hsi_madidihang >= 0.55`).
5. **Z2 (PFG_OFFSHORE):** `dist_port > 12 NM` DAN `dist_port <= 60 NM` DAN (`hsi_cakalang >= 0.55` ATAU `hsi_madidihang >= 0.55`).
6. **GEN (GENERAL_OCEANIC):** Seluruh grid yang tidak memenuhi kriteria Z1 s.d. Z5.

**Urutan Prioritas Mutlak:** `Z4 -> Z5 -> Z3 -> Z1 -> Z2 -> GEN`.

#### 5.2. Bycatch Risk Screening (Standar IOTC)
- **GREEN (Risiko Rendah):** Sel di luar zona lereng benua (`~is_shelf_break`, yaitu depth < 150m atau > 1200m).
- **RED (Risiko Tinggi):** Terletak pada lereng benua (`is_shelf_break`), memenuhi kondisi cold front (`is_bycatch_risk`), DAN potensi Cakalang sangat tinggi (`hsi_cakalang >= 0.70`).
- **YELLOW (Kewaspadaan / Precautionary):** Terletak pada lereng benua (`is_shelf_break`) namun tidak memenuhi kriteria eskalasi RED.

#### 5.3. Standar Keselamatan Pelayaran BMKG
- `wave_height_m >= 3.5` -> `BAHAYA_GELOMBANG_EKSTREM`
- `wave_height_m >= 2.0` -> `WASPADA_KAPAL_KECIL_GELOMBANG_TINGGI`
- Lainnya -> `AMAN_MELAUT`

---

### 6. Peta Arsitektur Hubungan Sistem (Relationship Map)

```
[Pengguna / Dashboard / Filter UI]
       │
       ▼
[Laravel Request / DTO Validator] ── (Species, Date, BBox, DOM, Bycatch, Depth)
       │
       ├────────────────────────────────────────┐
       ▼ (Data Eksisting / Query Cepat)          ▼ (Komputasi Ad-hoc Baru)
[ScientificDataRepository (PostGIS)]    [PythonComputationBridge]
  - Parameterized PostGIS SQL             - Safe Process Execution
  - ST_Intersects, ST_DWithin             - Strict Argv (No Shell Interpolation)
  - Read-only seacast_readonly            - Timeout & JSON Schema Validator
  - GiST Index: idx_canonical_grids_geom  - scripts/daily_inference.py
       │                                        │
       └───────────────────┬────────────────────┘
                           ▼
              [GeoJSON & Summary Transformer]
                           │
                           ▼
          [Core Radar API: /api/predictions]
                           │
                           ▼
         [MapLibre GL JS (Client Layer Engine)]
          - Canonical Grid Polygon Layer
          - HSI Gradient Color Fill
          - DOM Zone Overlay
          - Bycatch Risk Markers & Tooltip
```

---

### 7. Kesimpulan & Kesiapan Masuk Phase 2.1
- Seluruh dependensi runtime Python 3.14.7 terverifikasi aktif.
- Skema tabel PostGIS terverifikasi 100% cocok dengan audit Phase 0 dan 1.
- Logika delineasi DOM, bobot ensemble ilmiah, dan aturan bycatch telah terdokumentasi secara definitif.
- **Phase 2.0 dinyatakan LULUS (PASS). Siap melanjutkan ke Phase 2.1 (Scientific Data Access Layer).**

