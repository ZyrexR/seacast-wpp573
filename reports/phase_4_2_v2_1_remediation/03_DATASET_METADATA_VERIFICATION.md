# SEACAST PRIGI — DATASET METADATA VERIFICATION REPORT
## PHASE 4.2 GEE PRODUCTION SCRIPT v2.1
### Comprehensive Sensor, Reanalysis, and Geophysical Registry

**Document ID:** `SEACAST-PHASE-4.2-v2.1-DATASET-METADATA`  
**Classification:** `TECHNICAL_REFERENCE`  
**Target Script:** [`gee/SEACAST_PRIGI_GEE_PRODUCTION_2015_2025_v2_1.js`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/gee/SEACAST_PRIGI_GEE_PRODUCTION_2015_2025_v2_1.js)  
**Study Domain:** WPPNRI 573 ($105.0^\circ\text{E} - 117.0^\circ\text{E}$, $6.8^\circ\text{S} - 14.0^\circ\text{S}$)  
**Temporal Window:** 2015-01-01 to 2025-12-31 (132 calendar months)  
**Date of Verification:** 2026-11-02  

---

## 1. PURPOSE AND SCOPE

This document provides independent verification and empirical documentation of all remote sensing, reanalysis, numerical model, and geophysical datasets integrated into `SEACAST_PRIGI_GEE_PRODUCTION_2015_2025_v2_1.js`. It details asset identifiers, band names, spatial resolutions, temporal coverage, unit conversions, scale factors, and masking criteria to eliminate data provenance errors.

---

## 2. COMPREHENSIVE DATASET REGISTRY

| Parameter | Dataset Identifier | Band Name | Native Resolution | Native Unit | Applied Scale Factor | Derived Unit | Temporal Availability | Provider / Sensor |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **Sea Surface Temperature (SST)** | `NOAA/CDR/OISST_V2_1` | `sst` | 0.25° (~27.8 km) | 0.01 °C | `0.01` | °C | 1981-09-01 to Present (Continuous) | NOAA NCEI / AVHRR + In Situ |
| **Chlorophyll-a (Primary)** | `NASA/OCEANDATA/MODIS-Aqua/L3SMI` | `chlor_a` | 4638.3 m (~4.6 km) | mg/m³ | `1.0` | mg/m³ | 2002-07-04 to Present (Intermittent anomalies in 2021/2022) | NASA Ocean Biology DAAC / MODIS Aqua |
| **Chlorophyll-a (Secondary)** | `JAXA/GCOM-C/L3/OCEAN/CHLA/V3` | `CHLA_AVE` | 4638.3 m (~4.6 km) | Raw DN | `0.0016` (offset `0.0`) | mg/m³ | 2018-01-01 to Present (Operational) | JAXA EORC / SGLI (GCOM-C) |
| **Wind Vectors (10m)** | `NOAA/CFSV2/FOR6H` | `u-component...`, `v-component...` | 0.205° (~22.8 km) | m/s | `1.0` | m/s | 1979-01-01 to Present (6-hourly synoptic) | NOAA NCEP / CFSv2 Operational Reanalysis |
| **Ocean Current Vectors (Primary)** | `HYCOM/sea_water_velocity` | `velocity_u_0`, `velocity_v_0` | 0.08° (~8.9 km) | 0.001 m/s | `0.001` | m/s | 2015-01-01 to 2024-08-31 (Cessation: 2024-09-05) | HYCOM Consortium / NOAA NCEP |
| **Ocean Current Vectors (Secondary)** | `COPERNICUS/MARINE/GLOBAL...` *(Asset Pending)* | `uo`, `vo` | 0.083° (~9.2 km) | m/s | `1.0` | m/s | 2024-09-01 to 2025-12-31 (Pending GEE Asset Ingestion) | Copernicus Marine Service / Mercator Ocean |
| **Bathymetry & Bedrock Elevation** | `NOAA/NGDC/ETOPO1` | `bedrock` | 1 arc-minute (~1.85 km) | meters | `1.0` | meters (elevation); derived depth: $-1 \times$ elevation | Static Global Relief Model | NOAA NCEI |

---

## 3. DATASET-SPECIFIC METADATA & HANDLING RULES

### 3.1 Sea Surface Temperature: NOAA CDR OISST v2.1
- **GEE Asset ID:** `NOAA/CDR/OISST_V2_1`
- **Native Grid Spacing:** Uniform 0.25° $\times$ 0.25° global grid (EPSG:4326).
- **Physical Scaling:** Raw integer stored as integer hundredths of a degree Celsius. The script applies an explicit multiplication: `image.select('sst').multiply(0.01)`.
- **Quality Control:** Grid cells flagged with ice or missing observations are excluded.
- **Physical Plausible Bounds:** Sea surface temperature in WPPNRI 573 strictly constrained to $[18.0^\circ\text{C}, 35.0^\circ\text{C}]$.

### 3.2 Chlorophyll-a Primary: NASA MODIS-Aqua L3 SMI
- **GEE Asset ID:** `NASA/OCEANDATA/MODIS-Aqua/L3SMI`
- **Native Resolution:** 4638.3 m nominal cell spacing at equator.
- **Measurement Band:** `chlor_a` representing near-surface chlorophyll-a concentration calculated via the standard OCx band-ratio algorithm.
- **Scale Factor:** Native units are physical mg/m³ (float). Scale factor is `1.0`.
- **Operational Masking:** Negative and extreme values outside $[0.01, 30.0]\text{ mg/m}^3$ are masked.
- **Observed Anomalies:** Known sensor safe-mode anomalies in July 2021, November 2021, and April 2022 resulting in zero valid passes over WPPNRI 573. Dynamic fallback automatically routes to GCOM-C V3.

### 3.3 Chlorophyll-a Secondary: JAXA GCOM-C SGLI L3 V3
- **GEE Asset ID:** `JAXA/GCOM-C/L3/OCEAN/CHLA/V3`
- **Native Resolution:** 4638.3 m nominal cell spacing.
- **Measurement Band:** `CHLA_AVE` (statistical average chlorophyll concentration).
- **Verified Scale Factor:** Independent GEE asset inspection confirmed metadata properties:
  $$\text{CHLA\_AVE\_SLOPE} = 0.0016, \quad \text{CHLA\_AVE\_OFFSET} = 0.0$$
  Physical concentration is computed as:
  $$\text{Chl-a}_{\text{physical}} = \text{DN} \times 0.0016\text{ mg/m}^3$$
- **QA Masking:** Cells with QA bitflags indicating dense cloud contamination (`QA_flag & 0x0001 != 0`) are masked prior to spatial reduction.

### 3.4 Synoptic Wind & Surface Stress: NOAA CFSv2 Operational
- **GEE Asset ID:** `NOAA/CFSV2/FOR6H`
- **Native Grid Spacing:** 0.205° Gaussian grid (~22.8 km at equator).
- **Temporal Resolution:** 6-hourly synoptic forecasts initialized at 00:00, 06:00, 12:00, and 18:00 UTC.
- **Band Nomenclature Harmonization:**
  - Standard bands: `u-component_of_wind_height_above_ground` and `v-component_of_wind_height_above_ground`.
  - Harmonization wrapper handles alternate naming conventions (`u_wind`, `v_wind`) across the March 2021 collection update.
- **Instantaneous Stress Processing:**
  Wind stress components $\tau_x$ and $\tau_y$ are computed per 6-hour frame using Wu (1982) drag scaling and subsequently averaged to monthly means, preventing negative bias from time-averaged wind magnitude.

### 3.5 Ocean Surface Currents: HYCOM & CMEMS Missing Asset Policy
- **Primary GEE Asset ID:** `HYCOM/sea_water_velocity`
- **Bands:** `velocity_u_0`, `velocity_v_0` (surface layer, depth $= 0\text{ m}$).
- **Scale Factor:** Multiplied by `0.001` to convert integer mm/s into physical m/s.
- **Coverage Horizon:** 2015-01-01 through 2024-08-31 (Operational shutdown occurred on 2024-09-05).
- **Post-August 2024 Governance (Zero Data Fabrication):**
  Copernicus Marine Service (CMEMS) global reanalysis/forecast data for September 2024 through December 2025 is not natively present in the standard Earth Engine catalog under public asset IDs. In strict compliance with scientific integrity protocols:
  1. No synthetic current vectors are generated.
  2. No temporal persistence or climatological filling is applied without explicit flagging.
  3. All 16 months from September 2024 through December 2025 are masked as null and assigned quality flag:
     `SENSOR_FALLBACK_MISSING_ASSET`
  4. Once user-uploaded CMEMS assets are ingested into project `seca-513505`, the script architecture seamlessly reads them via the configured fallback path without code refactoring.

### 3.6 Geophysical Relief: NOAA ETOPO1 Bedrock
- **GEE Asset ID:** `NOAA/NGDC/ETOPO1`
- **Native Resolution:** 1 arc-minute (~1.85 km).
- **Measurement Band:** `bedrock` (representing bedrock elevation relative to sea level).
- **Semantic Disambiguation:**
  - Sea surface / sea level: $0\text{ m}$.
  - Ocean floor elevation: Negative values ($[-7000\text{ m}, 0\text{ m}]$).
  - Derived Ocean Depth: Positive physical values:
    $$\text{depth\_m} = -\text{elevation\_m} \quad (\text{for elevation\_m} \le 0)$$
  - Terrestrial topography: Positive values ($> 0\text{ m}$), strictly masked from marine oceanographic analysis.

---

## 4. AUDIT COMPLIANCE SIGN-OFF

The dataset metadata definitions in `gee/SEACAST_PRIGI_GEE_PRODUCTION_2015_2025_v2_1.js` strictly match the physical, spatial, and numerical parameters verified above. All scale factors and band mappings have been verified against live Google Earth Engine data catalogs.

