# 07 — GOOGLE EARTH ENGINE (GEE) TECHNICAL ARCHITECTURE
## SEACAST PRIGI — Wilayah Pengelolaan Perikanan Negara Republik Indonesia (WPPNRI) 573

- **Document ID:** `GEE-ARCH-07`
- **Execution Project:** `seca-513505` (`SEACAST`)
- **API Version:** Earth Engine Python API `1.7.50` & JavaScript API

---

## 1. Struktur Modul Modular GEE (`gee/spatial_reconstruction/`)

```text
gee/spatial_reconstruction/
├── 00_CONFIG.js                  # Konfigurasi terpusat (AOI, waktu, ID koleksi, proyeksi)
├── 01_AOI_VALIDATION.js           # Visualisasi dan validasi batas poligon WPPNRI 573
├── 02_DATA_AUDIT.js               # Pemeriksaan ketersediaan koleksi satelit
├── 03_SST_PROCESSING.js           # Komposit bulanan SST dan perhitungan anomali
├── 04_CHLOROPHYLL_PROCESSING.js   # Ekstraksi Chl-a MODIS-Aqua dan log10 transform
├── 05_WIND_PROCESSING.js          # Ekstraksi u10, v10, dan kecepatan angin
├── 06_CURRENT_PROCESSING.js       # Pemrosesan kecepatan arus permukaan (HYCOM)
├── 07_SLA_EDDY_PROCESSING.js      # Integrasi Sea Level Anomaly & EKE
├── 08_THERMAL_FRONT.js            # Intensitas front termal gradien Sobel metrik bola (°C/km)
├── 09_EKMAN_TRANSPORT.js          # Transpor Ekman zonal & meridional dengan redaman Coriolis
├── 10_BATHYMETRY.js               # Stratifikasi kedalaman (0-200m, 200-1000m, >1000m)
├── 11_GRID_GENERATION.js          # Pembangkitan grid bersarang (0.25°, 0.10°, 0.05°)
├── 12_SPATIOTEMPORAL_INTEG.js     # Penggabungan multi-prediktor per sel grid
├── 13_EXPLORATORY_ANALYSIS.js     # Analisis tren, musiman, dan korelasi spasial
├── 14_EXPORT_MANAGER.js           # Manajemen ekspor tabel & raster ke Drive
├── 15_QA_VALIDATION.js            # Skrip verifikasi QC server-side
└── GEE_INTERACTIVE_DASHBOARD_WPP573.js # Visualizer mandiri untuk Code Editor
```
