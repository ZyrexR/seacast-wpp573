# GEE DATASET SOURCE PROVENANCE & HARMONIZATION REGISTER
**Project:** SEACAST PRIGI — WPPNRI 573 (2015–2025)  
**Document ID:** GEE-PROV-01  
**Status:** VERIFIED ON GOOGLE EARTH ENGINE CATALOG  

---

## 1. MATRIKS KOLEKSI SATELIT & REANALISIS RESMI

| Variabel | Koleksi GEE / Provider | Resolusi Native | Skala / Faktor Kalibrasi | Band Kunci | Cadence Sumber | Periode Efektif | Status Layer |
|---|---|:---:|:---:|---|:---:|:---:|:---:|
| **SST** | `NOAA/CDR/OISST/V2_1` | 0.25° (~27.8 km) | × 0.01 | `sst` | Harian | 2015–2025 (132 bln) | **PRIMARY** |
| **Thermal Front** | Derived from OISST v2.1 | 0.25° | Metric Sobel (°C/km) | `sst_gradient_deg_km` | Bulanan | 2015–2025 (132 bln) | **DERIVED** |
| **Klorofil-a** | `NASA/OCEANDATA/MODIS-Aqua/L3SMI` | 0.0417° (~4.6 km) | 1.0 (mg/m³) | `chlor_a` | Harian | 2015–2025 (132 bln) | **PRIMARY** |
| **Klorofil-a Fallback** | `JAXA/GCOM-C/L3/OCEAN/CHLA/V3` | 0.0417° (~4.6 km) | × 0.0016 (mg/m³) | `CHLA_AVE` | Harian | 2018–2025 (Standby) | **FALLBACK** |
| **Angin 10m** | `NOAA/CFSV2/FOR6H_HARMONIZED` | 0.205° (~22.8 km) | 1.0 (m/s) | `u-component...`, `v-component...` | 6-Jam | 2015–2025 (132 bln) | **PRIMARY** |
| **Tegangan Angin** | Derived from CFSv2 (Wu 1982) | 0.205° | Metric N/m² | `wind_stress_tau_mag` | 6-Jam / Bln | 2015–2025 (132 bln) | **DERIVED** |
| **Ekman Pumping** | Derived from CFSv2 + Coriolis | 0.205° | m/s & m/day | `ekman_w_e_m_s` | Bulanan | 2015–2025 (132 bln) | **DERIVED** |
| **Presipitasi** | `NOAA/CFSV2/FOR6H_HARMONIZED` | 0.312° (~34.7 km) | × 86400 (mm/day) | `Precipitation_rate...` | 6-Jam | 2015–2025 (132 bln) | **PRIMARY** |
| **Arus Laut (1)** | `HYCOM/sea_water_velocity` | 0.08° (~8.9 km) | × 0.001 (m/s) | `velocity_u_0`, `velocity_v_0` | 3-Jam | 2015-01 s/d 2024-08 | **PRIMARY (z=0m)** |
| **Arus Laut (2)** | `projects/seca-513505/assets/cmems_phy_anfc` | 0.083° (~9.2 km) | 1.0 (m/s) | `uo`, `vo` | Harian | 2024-09 s/d 2025-12 | **FALLBACK (z=0.494m)** |
| **Batimetri** | `NOAA/NGDC/ETOPO1` | 0.0167° (~1.85 km) | 1.0 (meter) | `bedrock` | Statis | Permanen | **STATIC** |

---

## 2. ATURAN PENANGANAN DATA HILANG (ZERO FABRICATION POLICY)

1. **Prinsip Kejujuran Data:** Tidak ada imputasi nilai fiktif atau pengisian nilai nol untuk observasi satelit yang tidak tersedia.
2. **Ketiadaan Arus September 2024–Desember 2025:**
   - HYCOM resmi menghentikan feed operasional per 5 September 2024.
   - Karena CMEMS belum diunggah ke private asset GEE proyek `seca-513505`, maka seluruh variabel arus pada periode tersebut disajikan sebagai `masked` (null).
   - Flag metadata dicatat secara eksplisit sebagai `SENSOR_FALLBACK_MISSING_ASSET`.
3. **Penyaringan Klorofil-a:** Nilai klorofil-a di luar rentang fisik $[0.01, 64.0]	ext{ mg/m}^3$ dimask sebagai awan/invalid pixel.
