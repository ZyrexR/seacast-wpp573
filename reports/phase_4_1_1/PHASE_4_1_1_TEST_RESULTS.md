# PHASE 4.1.1 — REGRESSION TEST RESULTS (`RT-01` .. `RT-15`)
## Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi — WPPNRI 573

- **Test Suite Script:** [`scratch/run_phase_4_1_1_regression_tests.py`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/run_phase_4_1_1_regression_tests.py)
- **Legacy Phase 4.1 Suite Script:** [`scratch/test_phase_4_1_integrity.py`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/test_phase_4_1_integrity.py)
- **Polygon Geometry Validator:** [`scratch/validate_polygon_geometries.py`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/validate_polygon_geometries.py)
- **Execution Summary:** **`15 / 15 PASS`** (`0` FAIL)

---

## 1. Regression Test Matrix (`RT-01` through `RT-15`)

| Test ID | Test Name | Status | Verified Objective | Verification Method |
| :---: | :--- | :---: | :--- | :--- |
| `RT-01` | **Ensemble Weight Consistency** | `PASS` | Semua file RHSI memakai bobot identik dan jumlah bobot = 1.000 per spesies dengan status PROVISIONAL_ANALYST_SELECTED_WEIGHTS. | `Cross-file equality assertion across 7 RHSI metadata, CSV, projection, sensitivity, JS bundle, and gate files.` |
| `RT-02` | **Weighted Disagreement Formula** | `PASS` | Albakor w_maxent = 0 tidak memberi kontribusi ke sigma_w pada seluruh 36.900 sel-skenario-spesies. | `Cell-by-cell verification of sigma_w = sqrt(sum_i w_i (RHSI_i - RHSI_bar_w)^2) across 36,900 cells.` |
| `RT-03` | **Advisory Score Separation** | `PASS` | weighted_mean_rhsi, weighted_ensemble_disagreement, raw_advisory_score, dan visualization_score tersimpan terpisah. | `Schema key presence and arithmetic verification across all 36,900 projection cells.` |
| `RT-04` | **Fleet Breakdown Consistency** | `PASS` | Jumlah kapal per GT (296 / 53 / 91 / 3) dan total kapal (443 pada 2025; puncak 794 pada 2016) konsisten di semua file dari sumber primer Sheet 2. | `Cross-file equality verification against Sheet 2 of Laporan Statistik Perikanan PPN Prigi 2025.xlsx.` |
| `RT-05` | **Fisheries Taxonomic Consistency** | `PASS` | Total 12 taksa (18.405.261 kg), total pelagis 10 taksa (18.070.653 kg), Gulamah (207.156 kg), Cucut (127.452 kg), dan Julung (68.906 kg) konsisten di semua file. | `Cell-by-cell summation verification against Sheet 5 of Laporan Statistik Perikanan PPN Prigi 2025.xlsx.` |
| `RT-06` | **April 2022 & 132-Month Series** | `PASS` | April 2022 = 198.246 kg, Cakalang = 122.265 kg, dan total 132 bulan = 18.405.261 kg konsisten. | `Direct row and sum verification across 132-month series.` |
| `RT-07` | **AOI Bounds & Grid** | `PASS` | Semua file spasial memakai [111.00, 113.00] x [-10.45, -8.00], 50 x 41 = 2.050 sel. | `Bounding box and grid dimension assertion across spatial configs and seasonal grids.` |
| `RT-08` | **Polygon Geometry Within AOI** | `PASS` | Semua polygon valid, tertutup, tidak self-intersecting, dan difference(AOI) = 0.0 km2. | `2D polygon ring validation, segment self-intersection check, and exact box difference(AOI) area calculation.` |
| `RT-09` | **Topology & Overlap** | `PASS` | Overlap diuji secara terpisah sebagai within-layer interior overlap (0 di semua layer) dan cross-layer overlap matrix. | `Pairwise interior bounding-box intersection check within each layer and cross-layer cell ID intersection matrix.` |
| `RT-10` | **Background Points Consistency** | `PASS` | Jumlah titik background konsisten (5.000 titik) di semua file, 0 di luar AOI, dan 0 bertumpuk di garis batas setelah remediasi. | `Feature count, bounding box, and boundary line coordinate inspection on target_group_background.geojson.` |
| `RT-11` | **WPPNRI 573 Area Consistency** | `PASS` | Luas Sub-AOI (59.550,43 km2), rujukan nominal WPPNRI 573 (1.423.238 km2 -> 4,1842%), dan luas poligon BIG (933.749,49 km2 -> 6,3776%) konsisten di seluruh file. | `WGS84 authalic equal-area integration on Sub-AOI and data/boundaries/wppnri_573_big.geojson.` |
| `RT-12` | **Backup Manifest Integrity** | `PASS` | total_files_backed_up == len(files) dan semua SHA-256 valid pada phase_4_1_1_baseline_manifest.json (48 file) dan pre_patch_manifest.json (65 file). | `Full disk file existence and SHA-256 recomputation check across all 48 + 65 backup artifacts.` |
| `RT-13` | **Sensitivity Table Arithmetic** | `PASS` | Semua angka sensitivitas konsisten dengan script dan JSON sumber, termasuk dekomposisi skor RHSI_w vs skor berpenalti untuk Madidihang dan Albakor. | `Direct recomputation of lambda x tau grid and 5 weight schemes across 2,050 cells.` |
| `RT-14` | **CITES & Bycatch Compliance** | `PASS` | 123 polygon kandidat terklip ketat, memiliki 8 field CITES, berstatus NEEDS_VERIFICATION, dan bernama Candidate Bathymetric Screening Polygons for Voluntary Shark Bycatch Review. | `Feature and property schema verification across all 123 polygons.` |
| `RT-15` | **Dashboard & Bundle Integrity** | `PASS` | html/index.html, html/oceanographic_data.js, dan html/sdm_data.js bebas syntax error dan 100% sinkron dengan JSON sumber. | `Node.js V8 syntax compilation of all inline scripts and runtime evaluation of window.ocean132Months, window.oceanCrossCorrelation, and window.sdmModelEvaluations.` |

---

## 2. Detailed Output Evidence

Full machine-readable test outputs and assertions are stored in:
- [`scratch/phase_4_1_1_regression_test_results.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/scratch/phase_4_1_1_regression_test_results.json)
- [`reports/phase_4_1_1/PHASE_4_1_1_READINESS_GATE.json`](file:///c:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/reports/phase_4_1_1/PHASE_4_1_1_READINESS_GATE.json)
