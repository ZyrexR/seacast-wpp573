# DATA SOURCE CATALOG — SEACAST PRIGI (WPPNRI 573)

- **Audited Period:** 2015-01-01 to 2025-12-31 (132 Months)
- **Study Domain:** Wilayah Pengelolaan Perikanan Republik Indonesia (WPPNRI) 573
- **Execution Project:** `seca-513505` (Google Earth Engine)

## 1. Data Source Matrix

| Dataset ID | Parameter | Provider | GEE Collection ID | Resolusi Spasial | Cakupan Temporal | Status & Keputusan |
| :--- | :--- | :--- | :--- | :---: | :---: | :--- |
| **OISST_V21** | Sea Surface Temperature & Anomaly | NOAA / NCEI | `NOAA/CDR/OISST/V2_1` | 0.25° (~27.8 km) | 1981-09-01 s/d 2026-10-29 | `AVAILABLE` |
| **MODIS_AQUA_L3SMI** | Chlorophyll-a & SST (4km) | NASA Ocean Biology Processing Group | `NASA/OCEANDATA/MODIS-Aqua/L3SMI` | 4.63 km | 2002-07-03 s/d 2025-11-30 | `AVAILABLE_WITH_LIMITATIONS` |
| **CFSV2_WIND_6H** | 10m Surface Wind Vectors (u10, v10) | NOAA / NCEP | `NOAA/CFSV2/FOR6H_HARMONIZED` | 0.205° (~22.8 km) | 1979-01-01 s/d 2026-11-01 | `AVAILABLE` |
| **ERA5_LAND_MONTHLY** | Surface Meteorology / Wind Components | ECMWF / Copernicus | `ECMWF/ERA5_LAND/MONTHLY_AGGR` | 0.1° (~11.1 km) | 1950-02-01 s/d 2026-09-01 | `NEEDS_SCIENTIFIC_REVIEW` |
| **HYCOM_VELOCITY** | Ocean Current Velocity (u, v) | HYCOM Consortium / NOPP | `HYCOM/sea_water_velocity` | 0.08° (~8.9 km) | 1992-10-02 s/d 2024-09-05 | `AVAILABLE` |
| **ETOPO1_BATHYMETRY** | Bedrock Bathymetry & Relief | NOAA / NCEI | `NOAA/NGDC/ETOPO1` | 1 arc-minute (~1.85 km) | Static s/d Static | `AVAILABLE` |
| **COPERNICUS_GLOBAL_SLA_API** | Sea Level Anomaly & Geostrophic Currents | Copernicus Marine Service (CMEMS) | `SEALEVEL_GLO_PHY_L4_MY_008_047` | 0.25° (~27.8 km) | 1993-01-01 s/d 2025-12-31 | `NEEDS_MANUAL_ACCESS` |

## 2. Detail Evaluasi per Parameter

### OISST_V21 — Sea Surface Temperature & Anomaly
- **Collection ID:** `NOAA/CDR/OISST/V2_1`
- **Source URL:** https://developers.google.com/earth-engine/datasets/catalog/NOAA_CDR_OISST_V2_1
- **Resolusi Spasial & Temporal:** 0.25° (~27.8 km) | Daily
- **Satuan, Skala & Offset:** °C (raw: deg C * 100) | Scale: `0.01`, Offset: `0.0`
- **Quality Band:** `err`
- **Status Cakupan:** `AVAILABLE` (Citra di Periode Studi: `4017`)
- **Keterbatasan Fisik/Sensor:** Resolusi spasial relatif kasar (0.25° ~ 27.8 km); mewakili suhu lapisan atas (top meter), bebas tutupan awan.
- **Keputusan Metodologis:** `AVAILABLE` — Standar emas SST bebas awan regional dan perhitungan SSTA (1981-sekarang lengkap 100%).

### MODIS_AQUA_L3SMI — Chlorophyll-a & SST (4km)
- **Collection ID:** `NASA/OCEANDATA/MODIS-Aqua/L3SMI`
- **Source URL:** https://developers.google.com/earth-engine/datasets/catalog/NASA_OCEANDATA_MODIS-Aqua_L3SMI
- **Resolusi Spasial & Temporal:** 4.63 km | Daily
- **Satuan, Skala & Offset:** chlor_a: mg/m³, sst: °C | Scale: `1.0`, Offset: `0.0`
- **Quality Band:** `quality_level (via QA)`
- **Status Cakupan:** `AVAILABLE_WITH_LIMITATIONS` (Citra di Periode Studi: `3879`)
- **Keterbatasan Fisik/Sensor:** Koleksi MODIS-Aqua L3SMI di GEE saat ini tersedia hingga 2025-11-30 (131 dari 132 bulan). Bulan 2025-12 memerlukan gap-filling atau sensor VIIRS fallback. Terdapat tutupan awan tinggi pada Musim Barat (DJF).
- **Keputusan Metodologis:** `AVAILABLE_WITH_LIMITATIONS` — Dataset utama klorofil-a resolusi 4km dan SST optik. Digunakan dengan QC cloud-masking dan sensor fusion.

### CFSV2_WIND_6H — 10m Surface Wind Vectors (u10, v10)
- **Collection ID:** `NOAA/CFSV2/FOR6H_HARMONIZED`
- **Source URL:** https://developers.google.com/earth-engine/datasets/catalog/NOAA_CFSV2_FOR6H_HARMONIZED
- **Resolusi Spasial & Temporal:** 0.205° (~22.8 km) | 6-Hourly
- **Satuan, Skala & Offset:** m/s | Scale: `1.0`, Offset: `0.0`
- **Quality Band:** `None`
- **Status Cakupan:** `AVAILABLE` (Citra di Periode Studi: `16061`)
- **Keterbatasan Fisik/Sensor:** Resolusi 0.205°; memerlukan filter QC fisik (|u10|, |v10| < 30 m/s) untuk menolak anomali data grib mentah.
- **Keputusan Metodologis:** `AVAILABLE` — Menyediakan komponen angin 10m kontinu 1979-2026 untuk perhitungan tegangan angin dan transpor Ekman.

### ERA5_LAND_MONTHLY — Surface Meteorology / Wind Components
- **Collection ID:** `ECMWF/ERA5_LAND/MONTHLY_AGGR`
- **Source URL:** https://developers.google.com/earth-engine/datasets/catalog/ECMWF_ERA5_LAND_MONTHLY_AGGR
- **Resolusi Spasial & Temporal:** 0.1° (~11.1 km) | Monthly
- **Satuan, Skala & Offset:** u_component_of_wind_10m: m/s | Scale: `1.0`, Offset: `0.0`
- **Quality Band:** `None`
- **Status Cakupan:** `AVAILABLE_WITH_LIMITATIONS` (Citra di Periode Studi: `132`)
- **Keterbatasan Fisik/Sensor:** ERA5-Land di GEE terutama berfokus pada daratan; sel laut lepas memiliki cakupan terbatas / masked.
- **Keputusan Metodologis:** `NEEDS_SCIENTIFIC_REVIEW` — Hanya cocok untuk analisis meteorologi pesisir; tidak representatif untuk Samudera Hindia lepas pantai WPPNRI 573.

### HYCOM_VELOCITY — Ocean Current Velocity (u, v)
- **Collection ID:** `HYCOM/sea_water_velocity`
- **Source URL:** https://developers.google.com/earth-engine/datasets/catalog/HYCOM_sea_water_velocity
- **Resolusi Spasial & Temporal:** 0.08° (~8.9 km) | Daily / 3-Hourly
- **Satuan, Skala & Offset:** water_u, water_v: m/s (raw * 0.001) | Scale: `0.001`, Offset: `0.0`
- **Quality Band:** `None`
- **Status Cakupan:** `AVAILABLE` (Citra di Periode Studi: `18938`)
- **Keterbatasan Fisik/Sensor:** Data berasal dari model asimilasi numerik (bukan observasi in-situ langsung); skala faktor 0.001.
- **Keputusan Metodologis:** `AVAILABLE` — Pilihan utama estimasi arus laut (water_u, water_v) regional di GEE.

### ETOPO1_BATHYMETRY — Bedrock Bathymetry & Relief
- **Collection ID:** `NOAA/NGDC/ETOPO1`
- **Source URL:** https://developers.google.com/earth-engine/datasets/catalog/NOAA_NGDC_ETOPO1
- **Resolusi Spasial & Temporal:** 1 arc-minute (~1.85 km) | Static
- **Satuan, Skala & Offset:** meters | Scale: `1.0`, Offset: `0.0`
- **Quality Band:** `None`
- **Status Cakupan:** `AVAILABLE` (Citra di Periode Studi: `1`)
- **Keterbatasan Fisik/Sensor:** Resolusi 1 arc-minute (~1.85 km); datum vertikal bedrock global.
- **Keputusan Metodologis:** `AVAILABLE` — Standar global untuk batimetri, kemiringan lereng (slope), dan jarak kontur -200m.

### COPERNICUS_GLOBAL_SLA_API — Sea Level Anomaly & Geostrophic Currents
- **Collection ID:** `SEALEVEL_GLO_PHY_L4_MY_008_047`
- **Source URL:** https://marine.copernicus.eu/
- **Resolusi Spasial & Temporal:** 0.25° (~27.8 km) | Daily / Monthly
- **Satuan, Skala & Offset:** sla: m, ugos/vgos: m/s | Scale: `1.0`, Offset: `0.0`
- **Quality Band:** `flag`
- **Status Cakupan:** `NEEDS_MANUAL_ACCESS` (Citra di Periode Studi: `132`)
- **Keterbatasan Fisik/Sensor:** Tidak terdaftar sebagai koleksi publik langsung di Earth Engine Catalog; memerlukan akuisisi via Copernicus Marine Toolbox di Google Colab.
- **Keputusan Metodologis:** `NEEDS_MANUAL_ACCESS` — Data satelit altimetri altimeter L4 resmi untuk SLA, Eddy Kinetic Energy (EKE), dan arus geostrofik.

