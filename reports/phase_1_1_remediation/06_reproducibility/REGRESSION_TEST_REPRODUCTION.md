# PHASE 4.1.1 BASELINE REGRESSION TEST REPRODUCTION REPORT
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** RT-REPRO-01  
**Phase:** 1.1 — Independent Scientific Audit  
**Auditor:** Scientific Python and Reproducibility Auditor  
**Date:** 2026-11-02T08:20:23.736924+00:00  
**Runner:** `C:\Users\ThinkPad\AppData\Local\Python\pythoncore-3.14-64\python.exe`  
**Test Suite:** `scratch/run_phase_4_1_1_regression_tests.py`  

---

## 1. REKAPITULASI HASIL EKSEKUSI REGRESI (15/15 PASS)

- **Total Pengujian Regresi Baseline:** 15
- **Lulus (PASS):** **15** (100.0%)
- **Gagal (FAIL):** 0
- **Dilewati (SKIPPED):** 0
- **Status Baseline:** **100% INTACT & UNALTERED (FROZEN BASELINE PRESERVED)**

| Test ID | Komponen Baseline | Status Eksekusi Aktual | Verifikasi Integritas |
|---|---|---|---|
| `PASS RT-01` | Ensemble Weight Consistency | **PASS** | Terverifikasi sesuai baseline historis |
| `PASS RT-02` | Weighted Disagreement Formula | **PASS** | Terverifikasi sesuai baseline historis |
| `PASS RT-03` | Advisory Score Separation | **PASS** | Terverifikasi sesuai baseline historis |
| `PASS RT-04` | Fleet Breakdown Consistency | **PASS** | Terverifikasi sesuai baseline historis |
| `PASS RT-05` | Fisheries Taxonomic Consistency | **PASS** | Terverifikasi sesuai baseline historis |
| `PASS RT-06` | April 2022 & 132-Month Series | **PASS** | Terverifikasi sesuai baseline historis |
| `PASS RT-07` | AOI Bounds & Grid | **PASS** | Terverifikasi sesuai baseline historis |
| `PASS RT-08` | Polygon Geometry Within AOI | **PASS** | Terverifikasi sesuai baseline historis |
| `PASS RT-09` | Topology & Overlap | **PASS** | Terverifikasi sesuai baseline historis |
| `PASS RT-10` | Background Points Consistency | **PASS** | Terverifikasi sesuai baseline historis |
| `PASS RT-11` | WPPNRI 573 Area Consistency | **PASS** | Terverifikasi sesuai baseline historis |
| `PASS RT-12` | Backup Manifest Integrity | **PASS** | Terverifikasi sesuai baseline historis |
| `PASS RT-13` | Sensitivity Table Arithmetic | **PASS** | Terverifikasi sesuai baseline historis |
| `PASS RT-14` | CITES & Bycatch Compliance | **PASS** | Terverifikasi sesuai baseline historis |
| `PASS RT-15` | Dashboard & Bundle Integrity | **PASS** | Terverifikasi sesuai baseline historis |

---

## 2. VERIFIKASI TIDAK ADANYA MUTASI BASELINE

Audit independen memverifikasi bahwa:
1. `data/spatial_config_aoi.json` tidak mengalami modifikasi.
2. `outputs/sdm/zones/*.geojson` tidak berubah hash kriptografisnya.
3. Seluruh bobot ensemble, parameter perikanan, dan 5.000 titik latar belakang (background points) tetap terlindungi 100%.
