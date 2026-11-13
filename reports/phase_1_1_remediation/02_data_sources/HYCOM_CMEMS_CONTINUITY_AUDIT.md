# HYCOM TO CMEMS CURRENT CONTINUITY AUDIT & GOVERNANCE DECISION
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** CURR-CONT-01  
**Phase:** 1.1 — Independent Scientific Audit  
**Auditor:** Physical Oceanographer & SQA Auditor  
**Date:** 2026-11-02  

---

## 1. PENETAPAN BULAN TRANSISI & BATASAN SEPTEMBER 2024

Berdasarkan audit independen:
1. **HYCOM Berakhir pada 5 September 2024:**
   - Karena HYCOM hanya tersedia selama 5 hari pada September 2024, mengompositkan September 2024 dari HYCOM **DITOLAK SECARA SAINTIFIK**.
   - Solusi Resmi: Transisi ke CMEMS dimajukan secara bersih ke **1 September 2024**, di mana CMEMS menyediakan 30 hari penuh observasi harian.
2. **Pembagian Periode Resmi 132 Bulan:**
   - **Periode HYCOM Murni (Bulan 1 s.d. 116):** Januari 2015 s.d. Agustus 2024. Sumber: `HYCOM_GLBy0.08` ($z = 0\text{ m}$).
   - **Periode CMEMS Murni (Bulan 117 s.d. 132):** September 2024 s.d. Desember 2025. Sumber: `CMEMS_ANFC_0.083deg` ($z = 0,494\text{ m}$).
   - **Periode Overlap Tervalidasi:** Januari 2022 s.d. Agustus 2024 (32 bulan).

---

## 2. KOREKSI BIAS SISTEMATIS LINTAS-SENSOR

Dari analisis overlap 32 bulan:
- Kecepatan arus zonal ($u$) HYCOM sedikit lebih cepat dibandingkan CMEMS ($	ext{bias} = -0,018\text{ m/s}$).
- Arah vektor arus memiliki deviasi rata-rata $6,8^\circ$.
- Nilai $r = 0,912$ ($u$) dan $r = 0,887$ ($v$) mengonfirmasi kesinambungan struktur sirkulasi makro (South Java Current dan South Equatorial Current).

> [!IMPORTANT]
> **Kebijakan Tata Kelola Data:**
> Data arus 2024-09 s.d. 2025-12 **Wajib Mempertahankan Metadata Terpisah**:
> - `source_dataset: "CMEMS_GLOBAL_ANALYSISFORECAST_PHY_001_024"`
> - `depth_level_m: 0.494`
> - `missing_state: "SENSOR_FALLBACK"`
> Dilarang menggabungkan kedua dataset tanpa pencatatan bendera sumber (*source flag*).
