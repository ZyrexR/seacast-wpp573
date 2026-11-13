# 09 — DATA INTEGRATION SCHEMA & RELATIONAL SPECIFICATION
## SEACAST PRIGI — Wilayah Pengelolaan Perikanan Negara Republik Indonesia (WPPNRI) 573

- **Document ID:** `INTEG-SCH-09`
- **Machine-Readable Schema:** [`reports/spatial_reconstruction/SPATIOTEMPORAL_SCHEMA.json`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/reports/spatial_reconstruction/SPATIOTEMPORAL_SCHEMA.json)

---

## 1. Definisi Kolom Dataset Spasial-Temporal Terintegrasi

| Kolom | Tipe Data | Satuan | Sumber Data | Keterangan |
| :--- | :---: | :---: | :--- | :--- |
| `grid_id` | Integer | - | Master Grid WPP 573 | Primary Key Komposit 1 |
| `year` | Integer | Tahun | Fixed Calendar | Primary Key Komposit 2 (`2015–2025`) |
| `month` | Integer | Bulan | Fixed Calendar | Primary Key Komposit 3 (`1–12`) |
| `date` | String | YYYY-MM | ISO 8601 | Indeks waktu bulanan |
| `longitude` | Float | °BT | EPSG:4326 | Koordinat bujur titik tengah sel |
| `latitude` | Float | °LS | EPSG:4326 | Koordinat lintang titik tengah sel |
| `area_km2` | Float | km² | EPSG:6933 | Luas sel di dalam batas WPPNRI 573 |
| `sst` | Float | °C | MODIS + OISST | Suhu permukaan laut komposit |
| `sst_anomaly` | Float | °C | OISST v2.1 | Anomali terhadap baseline 1981–2010 |
| `chlor_a` | Float | mg/m³ | MODIS-Aqua | Konsentrasi klorofil-a permukaan |
| `log10_chla` | Float | $\log_{10}$ | Turunan Chl-a | Transformasi normalisasi statistik |
| `wind_speed` | Float | m/s | NOAA CFSv2 | Kecepatan angin permukaan 10m |
| `current_speed`| Float | m/s | HYCOM | Kecepatan arus laut permukaan (0m) |
| `thermal_front`| Float | °C/km | Sobel Metrik Bola | Intensitas gradien spasial termal |
| `ekman_transport_v` | Float | m²/s | NOAA CFSv2 | Transpor Ekman meridional ($M_{ey}$) |
| `bathymetry` | Float | meter | NOAA ETOPO1 | Kedalaman dasar laut (nilai negatif) |
| `bathymetric_slope` | Float | derajat | NOAA ETOPO1 | Kemiringan lereng batimetri |
| `data_coverage`| Float | 0.0–1.0 | Masker Kualitas | Proporsi piksel valid bebas awan |
| `quality_flag` | String | Enum | QC Protocol | `OBSERVED_RAW`, `SENSOR_FUSED`, dll. |
