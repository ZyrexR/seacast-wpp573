# PHASE 2 SCIENTIFIC VALIDATION REPORT
## SEACAST PRIGI V3 — Core Radar Engine & Dynamic Filter

**Tanggal:** 2026-11-04  
**Auditor:** Scientific Software Auditor & Geospatial Database Engineer  
**Status Validasi:** **VERIFIED & SCIENTIFICALLY COMPLIANT (100% PASS)**  
**Koneksi Database:** PostgreSQL 17.11 / PostGIS 3.6.2 (`seacast_prigi` via `seacast_readonly`)  
**Mesin Komputasi:** Python 3.14.7 + scikit-learn 1.9.1  

---

### 1. Eksekutif Ringkasan Validasi Ilmiah

Validasi ilmiah independen pada Phase 2 dilakukan untuk membuktikan bahwa migrasi ke Laravel 13 dan pembangunan Core Radar Engine tidak merusak metodologi, mengubah parameter ambang batas, maupun memanipulasi keluaran inferensi spasial. Setiap komponen komputasi telah diuji secara numerik dan dibandingkan langsung dengan acuan baseline PRD SEACAST PRIGI V2.3.2 dan file metadata [`model_evaluations_summary.json`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/models/sdm/ensemble/model_evaluations_summary.json).

---

### 2. Matriks Verifikasi Domain Ilmiah

| Domain Ilmiah | Parameter Terkunci | Nilai Baseline Audit | Hasil Pengujian Aktual | Status | Bukti Pengujian |
|---|---|---|---|:---:|---|
| **A. Grid Spasial** | Jumlah Sel Poligon | Tepat 8.129 sel | 8.129 sel | **PASS** | `ScientificDataAccessTest` |
| | Sistem Koordinat (CRS) | EPSG:4326 (WGS 84) | EPSG:4326 | **PASS** | PostGIS `ST_SRID(geom)` = 4326 |
| | Resolusi Grid | 0.10° (~11.1 km) | 0.10° | **PASS** | Verifikasi geometri PostGIS |
| | Spatial Index | GiST on `geom` | `idx_canonical_grids_geom` aktif | **PASS** | Bounding box query scan |
| **B. Oseanografi** | Rentang Suhu (SST) | 20.0 – 33.0 °C | 21.0 – 31.0 °C (Rata-rata: 29.2 °C) | **PASS** | `OceanographicEngineTest` |
| | Front Termal Gradient | $\ge 0.035^\circ\text{C/km}$ | $\ge 0.035^\circ\text{C/km}$ | **PASS** | `OceanographicEngineTest` |
| | Klorofil-a Bloom | $\ge 0.40\text{ mg/m}^3$ | $\ge 0.40\text{ mg/m}^3$ | **PASS** | `OceanographicEngineTest` |
| | Batimetri Lereng Benua | 150 – 1200 m | 150 – 1200 m | **PASS** | PostGIS `depth_m` filter |
| | Disclaimer BMKG | Simulasi Terkalibrasi | Label eksplisit pada UI/API/Provenance | **PASS** | `CoreRadarApiTest` |
| **C. Model SDM** | Bobot Cakalang | MaxEnt 0.320, RF 0.355, GLM 0.325 | MaxEnt 0.320, RF 0.355, GLM 0.325 | **PASS** | `SpeciesAndHsiEngineTest` |
| | Bobot Madidihang | MaxEnt 0.295, RF 0.365, GLM 0.340 | MaxEnt 0.295, RF 0.365, GLM 0.340 | **PASS** | `SpeciesAndHsiEngineTest` |
| | Bobot Albakor | **MaxEnt 0.000**, RF 0.517, GLM 0.483 | **MaxEnt 0.000**, RF 0.517, GLM 0.483 | **PASS** | `PythonBridgeSecurityTest` |
| | Fitur Input Scaler | 10 variabel terurut | 10 variabel terurut | **PASS** | `bridge_inference_runner.py` |
| **D. HSI Formula** | Rentang HSI | $0.000 - 1.000$ | $0.000 - 1.000$ (Clamped) | **PASS** | `SpeciesAndHsiEngineTest` |
| | Ambang Hotspot Tuna | HSI Cakalang $\ge 0.70$ atau Madidihang $\ge 0.70$ | HSI $\ge 0.70$ | **PASS** | `SpeciesAndHsiEngineTest` |
| | Ambang Tuna PFG | HSI $\ge 0.55$ | HSI $\ge 0.55$ | **PASS** | `SpeciesAndHsiEngineTest` |
| | Pemisahan Metrik | AUC/TSS tidak disubstitusikan ke HSI | Terpisah mutlak (AUC: Metadata) | **PASS** | `SpeciesAndHsiEngineTest` |
| **E. Delineasi DOM** | Prioritas Delineasi | $\text{Z4} \to \text{Z5} \to \text{Z3} \to \text{Z1} \to \text{Z2} \to \text{GEN}$ | Terbukti mutlak pada kasus konflik | **PASS** | `DomAndBycatchEngineTest` |
| | Z4 Nursery Ground | Kedalaman $\le 150$ m, Jarak $\le 15$ NM | $\le 150\text{ m} \land \le 15\text{ NM}$ | **PASS** | `DomAndBycatchEngineTest` |
| | Z5 Bycatch Screening | Kedalaman $150-1200$ m, SST $\le 26.5$, $\nabla \text{SST} \ge 0.035$ | $150-1200\text{ m} \land \le 26.5 \land \ge 0.035$ | **PASS** | `DomAndBycatchEngineTest` |
| | Z3 Feeding Core | Chl-a $\ge 0.40$, $\nabla \text{SST} \ge 0.035$ | $\ge 0.40 \land \ge 0.035$ | **PASS** | `DomAndBycatchEngineTest` |
| | Z1 Inshore PFG | Jarak $\le 12$ NM, HSI Tuna $\ge 0.55$ | $\le 12\text{ NM} \land \text{HSI} \ge 0.55$ | **PASS** | `DomAndBycatchEngineTest` |
| | Z2 Offshore PFG | Jarak $12-60$ NM, HSI Tuna $\ge 0.55$ | $12-60\text{ NM} \land \text{HSI} \ge 0.55$ | **PASS** | `DomAndBycatchEngineTest` |
| | GENERAL OCEANIC | Fallback sel di luar Z1-Z5 | Fallback sel di luar Z1-Z5 | **PASS** | `DomAndBycatchEngineTest` |
| **F. Bycatch IOTC** | Green | Luar lereng benua ($<150$ m atau $>1200$ m) | Luar lereng benua | **PASS** | `DomAndBycatchEngineTest` |
| | Yellow | Lereng benua $150-1200$ m tanpa eskalasi Red | Lereng benua | **PASS** | `DomAndBycatchEngineTest` |
| | Red | Lereng benua + Front Dingin + HSI Cakalang $\ge 0.70$ | Lereng benua + Front + $\text{HSI} \ge 0.70$ | **PASS** | `DomAndBycatchEngineTest` |
| **G. Spasial Geodetik** | Homeport PPN Prigi | $111.7275^\circ\text{E}, -8.2833^\circ\text{S}$ | $111.7275^\circ\text{E}, -8.2833^\circ\text{S}$ | **PASS** | `ScientificDataAccessTest` |
| | Jarak Nautical Miles | $1\text{ NM} = 1.852\text{ meter}$ via PostGIS `geography` | Geodesic `ST_Distance / 1852.0` | **PASS** | Jarak di titik homeport = 0.000 NM |

---

### 3. Keterbatasan Metodologis & Independent Validation Caveat

Sesuai mandat ilmiah:
1. **Status Validasi Model:**
   Evaluasi AUC yang tercantum (Cakalang 0.795, Madidihang 0.784, Albakor 0.860) dievaluasi terhadap titik proxy kehadiran lingkungan deterministik (*analytical heuristic response equations*), bukan terhadap data observasi penangkapan kapal empiris independen (*empirical catch coordinates*). Status resmi model adalah `EXPLORATORY_HEURISTIC_SCREENING`.
2. **Ketiadaan Spatial Cross-Validation:**
   Tidak ada spatial k-fold cross-validation yang dieksekusi dalam kode pelatihan model legacy. Sistem baru mempertahankan pembobotan ini sebagai baseline operasional terkunci hingga validasi lapangan empiris dilakukan pada fase selanjutnya.
3. **Data Oseanografi:**
   Data deret spasial bulanan mencakup 12 bulan tahun 2025 (`grid_monthly_predictions`). Data pendaratan historis mencakup 132 bulan (2015–2025). Tidak ada klaim bahwa seluruh 132 bulan memiliki layer prediksi spasial resolusi 0.10°.

