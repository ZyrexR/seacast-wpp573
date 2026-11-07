# SEACAST PRIGI — PHASE 4.2.2 DATA PROVENANCE SCHEMA
## End-to-End Source Attribution, Sensor Transitions, and Transformation Lineage

**Document ID:** `PHASE_4_2_2_DATA_PROVENANCE_SCHEMA`  
**Classification:** `PROVENANCE_SPECIFICATION`  
**Project:** SEACAST PRIGI — Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi  
**Domain:** WPPNRI 573 ($105.0^\circ\text{E} - 117.0^\circ\text{E}$, $6.8^\circ\text{S} - 14.0^\circ\text{S}$)  
**Target Script:** [`gee/SEACAST_PRIGI_GEE_PRODUCTION_2015_2025_v2_2.js`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/gee/SEACAST_PRIGI_GEE_PRODUCTION_2015_2025_v2_2.js)  
**Date:** 2026-11-02  

---

## 1. PURPOSE AND TRACEABILITY ARCHITECTURE

To ensure scientific reproducibility, every variable produced by the SEACAST PRIGI pipeline carries traceable metadata linking it back to:
1. Originating space agency / data center.
2. Earth Engine asset collection identifier.
3. Band nomenclature and native units.
4. Scale factors and calibration offsets applied.
5. Native spatial resolution and temporal sampling frequency.
6. Processing transformations and mathematical models applied.

---

## 2. COMPREHENSIVE PROVENANCE REGISTRY

| Variable | Source Dataset ID | Sensor / Model | Native Band | Native Unit | Scale Factor | Offset | Derived Unit | Native Resolution | Temporal Sampling |
| :--- | :--- | :--- | :--- | :--- | :---: | :---: | :--- | :---: | :---: |
| **SST** | `NOAA/CDR/OISST/V2_1` | AVHRR + In Situ | `sst` | 0.01 °C | `0.01` | `0.0` | °C | 0.25° (~27.8 km) | Daily |
| **Chl-a (Primary)** | `NASA/OCEANDATA/MODIS-Aqua/L3SMI` | MODIS Aqua | `chlor_a` | mg/m³ | `1.0` | `0.0` | mg/m³ | 4638.3 m (~4.6 km) | Daily passes |
| **Chl-a (Secondary)** | `JAXA/GCOM-C/L3/OCEAN/CHLA/V3` | SGLI (GCOM-C) | `CHLA_AVE` | Raw DN | `0.0016` | `0.0` | mg/m³ | 4638.3 m (~4.6 km) | 8-day / Daily |
| **Wind Vectors** | `NOAA/CFSV2/FOR6H_HARMONIZED` | CFSv2 Operational | `u-comp...`, `v-comp...` | m/s | `1.0` | `0.0` | m/s | 0.205° (~22.8 km) | 6-hourly synoptic |
| **Precipitation** | `NOAA/CFSV2/FOR6H_HARMONIZED` | CFSv2 Operational | `Precipitation_rate...` | kg/m²/s | `86400.0` | `0.0` | mm/day | 0.205° (~22.8 km) | 6-hourly synoptic |
| **Currents (Primary)** | `HYCOM/sea_water_velocity` | HYCOM GLBy0.08 | `velocity_u_0`, `velocity_v_0`| mm/s | `0.001` | `0.0` | m/s (z=0m) | 0.08° (~8.9 km) | 3-hourly |
| **Currents (Fallback)**| `projects/seca-513505/assets/cmems_phy_anfc` | CMEMS NEMO | `uo`, `vo` | m/s | `1.0` | `0.0` | m/s (z=0.494m)| 0.083° (~9.2 km) | Daily |
| **Bathymetry** | `NOAA/NGDC/ETOPO1` | Global Relief | `bedrock` | meters | `-1.0` | `0.0` | meters (depth)| 1 arc-minute (~1.85 km)| Static |

---

## 3. SENSOR TRANSITION GOVERNANCE & FALLBACK MECHANICS

### 3.1 Chlorophyll-a Dynamic Fallback
- **Historical Baseline:**
  - 2015-01 to 2017-12: MODIS Aqua operational as single source (GCOM-C was pre-launch).
  - 2018-01 to 2025-12: Dual-sensor redundancy active.
- **Pixel-Level Attribution:**
  Every pixel in the output contains `CHLA_SOURCE_CODE`:
  - `1`: `MODIS_PRIMARY`
  - `2`: `GCOM_C_FALLBACK`
  - `0`: `ZERO_OBSERVATIONS`

### 3.2 Ocean Current Hiatus Governance
- **Operational Timeline:**
  - 2015-01 to 2024-08: HYCOM operational surface currents ($z = 0\text{ m}$).
  - 2024-09: HYCOM partial cessation on September 5, 2024 (5 days only).
  - 2024-10 to 2025-12: Un-ingested CMEMS assets strictly masked as null. Zero data fabrication.

---

## 4. PROVENANCE ATTRIBUTES IN EXPORT SCHEMA

Every monthly record aggregated to the canonical 8,129-cell grid carries:
- `sst_source`: `"NOAA/CDR/OISST/V2_1"`
- `chla_primary_source`: `"NASA/OCEANDATA/MODIS-Aqua/L3SMI"`
- `chla_fallback_source`: `"JAXA/GCOM-C/L3/OCEAN/CHLA/V3"`
- `wind_source`: `"NOAA/CFSV2/FOR6H_HARMONIZED"`
- `current_source`: `"HYCOM/sea_water_velocity"` (2015–Aug 2024) or `"CMEMS_GLOBAL_ANALYSISFORECAST_PHY_001_024"` (post-Aug 2024)
- `current_depth_m`: `0.0` (HYCOM) or `0.494` (CMEMS)
- `bathymetry_source`: `"NOAA/NGDC/ETOPO1"`
- `processing_pipeline`: `"SEACAST_PRIGI_v2.2_CONSOLIDATED"`
- `processing_timestamp`: UTC timestamp of aggregation execution.

