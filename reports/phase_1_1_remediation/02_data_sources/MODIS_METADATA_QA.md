# MODIS AQUA L3SMI CHLOROPHYLL-A METADATA & QA AUDIT
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** MODIS-META-01  
**Phase:** 1.1 — Independent Scientific Audit  
**Auditor:** Remote Sensing and Satellite Data Specialist  
**Date:** 2026-11-02  

---

## 1. IDENTIFIKASI RESMI PRODUK MODIS AQUA

- **Koleksi Resmi GEE:** `NASA/OCEANDATA/MODIS-Aqua/L3SMI`
- **Penyedia:** NASA Ocean Biology Processing Group (OBPG).
- **Algoritma Pemrosesan:** Standard OC3M / OCI (Hu et al., 2012; O'Reilly et al., 2000).
- **Band Utama:** `chlor_a` (Konsentrasi Klorofil-a Permukaan Laut).
- **Satuan:** $\text{mg/m}^3$.
- **Faktor Skala:** $1,0$ (Nilai float bawaan).
- **Offset:** $0,0$.
- **Resolusi Spasial Bawaan:** $0,04166667^\circ$ ($\sim 4,63\text{ km}$ pada ekuator).
- **Proyeksi Asli:** EPSG:4326 (WGS 84 Plate Carree).
- **Cakupan Temporal 132 Bulan (2015–2025):**
  - Bulan Lengkap dengan Data: **129 bulan** ($97,73\%$).
  - Bulan Kosong (*Missing Months*): Tepat **3 bulan**:
    1. **Juli 2021 (`2021-07`):** Anomali instrumen MODIS safe-mode downlink.
    2. **November 2021 (`2021-11`):** Gap granule processing NASA OBPG.
    3. **Desember 2025 (`2025-12`):** Akhir horizon arsip publikasi NASA saat penarikan data.
