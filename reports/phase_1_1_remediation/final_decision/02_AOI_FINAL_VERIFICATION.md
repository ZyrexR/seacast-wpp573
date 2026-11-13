# AOI FINAL VERIFICATION REPORT
**Project:** SEACAST PRIGI — WPPNRI 573  
**Document ID:** AOI-FINAL-01  
**Phase:** 1.1 — Independent Scientific Audit  
**Date:** 2026-11-02  

---

## 1. REKONSILIASI PENUH KONFLIK LUAS MASTER

Audit independen Phase 1.1 secara definitif memecahkan misteri perbedaan tiga angka luas pada laporan sebelumnya:
1. **Luas Master Asli Bersih (`AOI_WPP573_MASTER.geojson`):**
   - **Luas Equal-Area EPSG:6933:** **`933.749,49 km²`**
   - **Luas Geodesik WGS84:** **`933.750,90 km²`**
   - **Perbedaan Proyeksi vs Geodesik:** **`1,41 km² (0,00015%)`** (Identik dan tervalidasi).
   - **Lubang Interior Pulau:** **`1.400 lubang pulau daratan`** seluas **`13.464,71 km²`** dipotong secara sempurna.
2. **Asal Usul Angka $947.214,20	ext{ km}^2$ pada R-05:**
   - Terbukti merupakan kesalahan pemanggilan file simplifikasi `AOI_WPP573_SIMPLIFIED_0p01deg_FOR_GEE.geojson`.
   - File tersebut **kehilangan seluruh 1.400 lubang pulau (0 lubang)** sehingga daratan pulau Jawa dan Nusa Tenggara terhitung palsu sebagai laut.
   - **Keputusan:** Angka $947.214,20	ext{ km}^2$ resmi **DIBATALKAN**.

---

## 2. STATUS SUMBER & REGULASI

- **Hash Sumber Mentah BIG (`data/aoi/raw/WPP573_BIG_ORIGINAL.geojson`):**
  `4e2b16197b0d48f350a488ad0a9c076691cf757e78ae855ae117d63d0e53c669` (**100% MATCH**).
- **Status Otoritas:** Geometri kartografis BIG Layer 21 disahkan sebagai representasi spasial terbaik yang tersedia (*Best Available Official Geospatial Boundary*). Angka $1,42	ext{ juta km}^2$ KKP Permen-KP 18/2014 adalah amplop maritim nominal non-subtraktif.
