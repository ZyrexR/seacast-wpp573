# HYCOM GLBy0.08 OCEAN CURRENT METADATA & QA AUDIT
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** HYCOM-META-01  
**Phase:** 1.1 — Independent Scientific Audit  
**Auditor:** Physical Oceanographer  
**Date:** 2026-11-02  

---

## 1. IDENTIFIKASI RESMI PRODUK HYCOM

- **Exact Dataset ID:** `HYCOM/GLBy0_08/latest` (dan seri reanalisis `GLBv0.08`).
- **Penyedia:** Hybrid Coordinate Ocean Model (HYCOM) Consortium / Naval Research Laboratory (NRL).
- **Platform:** Google Earth Engine Public Data Catalog.
- **Variabel Arus Permukaan Terpilih:**
  - `velocity_u_0`: Komponen kecepatan arus zonal pada kedalaman $z = 0\text{ m}$ (permukaan laut murni).
  - `velocity_v_0`: Komponen kecepatan arus meridional pada kedalaman $z = 0\text{ m}$.
- **Satuan:** $\text{m/s}$ (faktor skala $0,001$, offset $0,0$).
- **Resolusi Spasial Bawaan:** $0,08^\circ \times 0,04^\circ$ pada garis lintang ekuator/tropis ($\sim 1/12^\circ$).
- **Cadence Temporal Asli:** 3-jam / Harian.
- **Valid Temporal Range:** 1 Januari 2015 s.d. **5 September 2024**.

> [!WARNING]
> **Temuan Kritis Tanggal Penghentian (September 2024):**
> HYCOM GLBy0.08 berhenti beroperasi secara permanen pada **5 September 2024**.
> Bulan September 2024 **TIDAK DAPAT DIANGGAP SEBAGAI BULAN LENGKAP PADA HYCOM** (hanya tersedia 5 hari observasi). Memaksakan komposit bulanan dari 5 hari menghasilkan bias representasi musiman transisi Monsun Timur ke Peralihan II.
