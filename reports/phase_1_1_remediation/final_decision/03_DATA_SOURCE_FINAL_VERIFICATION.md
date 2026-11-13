# DATA SOURCE FINAL VERIFICATION REPORT
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** DATA-FINAL-01  
**Phase:** 1.1 — Independent Scientific Audit  
**Date:** 2026-11-02  

---

## 1. REKAPITULASI STATUS INDEPENDEN 5 SUMBER DATA UTAMA

### 1.1 Suhu Permukaan Laut (OISST v2.1)
- Koleksi GEE: `NOAA/CDR/OISST_V2_1`
- Status: **PASS (132 bulan lengkap tanpa celah)**.
- Resolusi: $0,25^\circ$, harian, faktor skala $0,01$.

### 1.2 Angin Permukaan (CFSv2 Reanalysis / Forecast)
- Koleksi GEE: `NOAA/CFSV2/FOR6H_HARMONIZED`
- Status: **CONDITIONAL (Lolos dengan aturan QC 5-kategori)**.
- 2015–2024: $14.610	ext{ frame}$ bersih ($0	ext{ outlier} > 30	ext{ m/s}$, $U_{\max} = 24,72	ext{ m/s}$).
- Akhir 2025: $21	ext{ frame}$ artefak numerik $>40	ext{ m/s}$ diisolasi sebagai `INVALID_SOURCE_FLAG` dan dimask menjadi `NaN`.

### 1.3 Klorofil-a (MODIS Aqua L3SMI & GCOM-C SGLI)
- Koleksi GEE: `NASA/OCEANDATA/MODIS-Aqua/L3SMI` & `JAXA/GCOM-C/L3/OCEAN/CHLA/V1`
- Status: **PASS (Option B: SENSOR_FALLBACK)**.
- MODIS mencakup 129 bulan. 3 bulan kosong (`2021-07`, `2021-11`, `2025-12`) diisi oleh GCOM-C (skala $0,0016$, korelasi overlap $r = 0,924$) dengan metadata transparan.

### 1.4 Arus Permukaan Laut (HYCOM GLBy0.08 & CMEMS ANFC)
- Koleksi: `HYCOM/GLBy0_08/latest` & `cmems_mod_glo_phy_anfc_0.083deg_P1D-m`
- Status: **CONDITIONAL (Memerlukan pipeline ingest CMEMS untuk 16 bulan terakhir)**.
- HYCOM berakhir 5 September 2024 (tidak lengkap pada Sep 2024). Transisi dimajukan ke 1 September 2024 menggunakan CMEMS ($z = 0,494	ext{ m}$, korelasi overlap $r = 0,912$).

### 1.5 Batimetri (GEBCO 2024 / 2022)
- Status: **PASS (Statik, grid 15 arc-sec)**.
