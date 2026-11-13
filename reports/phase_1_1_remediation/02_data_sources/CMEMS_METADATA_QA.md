# CMEMS GLOBAL OCEAN PHYSICS ANALYSIS METADATA & SPECIFICATION
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** CMEMS-META-01  
**Phase:** 1.1 — Independent Scientific Audit  
**Auditor:** Physical Oceanographer & Satellite Data Specialist  
**Date:** 2026-11-02  

---

## 1. IDENTIFIKASI RESMI PRODUK COPERNICUS MARINE SERVICE (CMEMS)

- **Official Product ID:** `GLOBAL_ANALYSISFORECAST_PHY_001_024`
- **Dataset ID:** `cmems_mod_glo_phy_anfc_0.083deg_P1D-m`
- **Tipe Produk:** Global Ocean Physical Analysis and Forecast (NEMO 3.6 numerical model dengan asimilasi data satelit altimetri dan in-situ Argo profiling).
- **Penyedia Resmi:** Copernicus Marine Environment Monitoring Service (Mercator Ocean International, EU).
- **Variabel Arus:**
  - `uo`: Eastward sea water velocity (komponen zonal $u$).
  - `vo`: Northward sea water velocity (komponen meridional $v$).
- **Kedalaman Lapisan Permukaan:** Bin kedalaman pertama $z = 0,494\text{ m}$ (lapisan sub-permukaan langsung di bawah tegangan angin permukaan).
- **Satuan:** $\text{m/s}$.
- **Resolusi Spasial:** $0,083^\circ \times 0,083^\circ$ reguler ($\sim 9,25\text{ km}$).
- **Cadence Temporal:** Rata-rata harian (*Daily-mean*).
- **Metode Akses:** Direct API melalui `copernicusmarine` Python CLI / OPeNDAP Data Store.
- **Status GEE Catalog:** Tidak tersedia secara native di public catalog GEE; memerlukan pipeline ekstraksi netCDF eksternal dan diunggah ke Google Earth Engine Private Asset sebelum pemrosesan skala penuh.
