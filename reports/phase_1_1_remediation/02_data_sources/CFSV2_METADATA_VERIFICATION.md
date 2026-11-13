# CFSV2 DATASET METADATA & INVENTORY VERIFICATION
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** CFSV2-META-01  
**Phase:** 1.1 — Independent Scientific Audit  
**Auditor:** Remote Sensing and Satellite Data Specialist & Physical Oceanographer  
**Date:** 2026-11-02  

---

## 1. IDENTIFIKASI RESMI KOLEKSI GOOGLE EARTH ENGINE

Dataset reanalisis dan prakiraan atmosfer operasional yang diaudit secara independen:
- **Exact Collection ID:** `NOAA/CFSV2/FOR6H_HARMONIZED`
- **Penyedia Resmi:** National Oceanic and Atmospheric Administration (NOAA) / NCEP.
- **Platform:** Google Earth Engine Public Data Catalog.
- **Karakteristik Temporal:**
  - Cadence Temporal: 6-jam (00:00, 06:00, 12:00, 18:00 UTC).
  - Rentang Periode Penelitian: 1 Januari 2015 s.d. 31 Desember 2025 (4.018 hari kalender).
  - Total Frame Teoretis: $4.018 \times 4 = 16.072\text{ frame}$.
  - Total Frame Tersedia: $16.061\text{ frame}$.
  - Frame Hilang (*Missing Frames*): Tepat **11 frame** (kelengkapan $99,931\%$).

---

## 2. SPESIFIKASI BAND & UNIT FISIK

| Nama Band Resmi GEE | Deskripsi Fisik | Satuan Asli | Faktor Skala | Offset | Rentang Fisik Valid di Laut Bebas |
|---|---|---|---|---|---|
| `u-component_of_wind_height_above_ground` | Kecepatan angin zonal pada 10m di atas permukaan | $\text{m/s}$ | $1,0$ | $0,0$ | $[-30,0, +30,0]\text{ m/s}$ |
| `v-component_of_wind_height_above_ground` | Kecepatan angin meridional pada 10m di atas permukaan | $\text{m/s}$ | $1,0$ | $0,0$ | $[-30,0, +30,0]\text{ m/s}$ |
| `Precipitation_rate_surface_6_Hour_Average`| Laju presipitasi permukaan | $\text{kg/m}^2/\text{s}$ | $1,0$ | $0,0$ | $[0,0, 0,05]\text{ kg/m}^2/\text{s}$ |

- **Sistem Koordinat Asli:** WGS 84 (EPSG:4326), kisi reguler $0,205^\circ \times 0,204^\circ$ Gaussian grid (resolusi spasial ekuivalen $\sim 22,5\text{ km}$).
- **Konvensi Tanda Vektor:**
  - $u > 0$: Angin bertiup ke arah TIMUR (angina baratan).
  - $u < 0$: Angin bertiup ke arah BARAT (angin timuran / pasat tenggara).
  - $v > 0$: Angin bertiup ke arah UTARA.
  - $v < 0$: Angin bertiup ke arah SELATAN.
