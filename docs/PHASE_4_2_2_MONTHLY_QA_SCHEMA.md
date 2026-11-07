# SEACAST PRIGI — PHASE 4.2.2 MONTHLY QUALITY ASSURANCE (QA) SCHEMA
## Standardized Multi-Variable Quality Evaluation Protocol

**Document ID:** `PHASE_4_2_2_MONTHLY_QA_SCHEMA`  
**Classification:** `DATA_QUALITY_SPECIFICATION`  
**Project:** SEACAST PRIGI — Sistem Intelijen Geospasial Perikanan Pelagis PPN Prigi  
**Domain:** WPPNRI 573 ($105.0^\circ\text{E} - 117.0^\circ\text{E}$, $6.8^\circ\text{S} - 14.0^\circ\text{S}$)  
**Target Script:** [`gee/SEACAST_PRIGI_GEE_PRODUCTION_2015_2025_v2_2.js`](file:///C:/Users/ThinkPad/.gemini/antigravity/scratch/prigi-pelagis/gee/SEACAST_PRIGI_GEE_PRODUCTION_2015_2025_v2_2.js)  
**Date:** 2026-11-02  

---

## 1. PURPOSE AND PRINCIPLES

This document establishes the official Quality Assurance (QA) Schema for monthly spatial-temporal aggregates generated across the 132-month study period (2015–2025). 

### Non-Negotiable QA Principles:
1. **Multi-Variable Independence:** A month can have complete SST and wind observations but missing surface current vectors. Quality states must be computed and reported independently for every variable.
2. **Decoupled Coverage Semantics:** Successful loading of the Master AOI geometry does NOT equate to full marine valid-data coverage. Geometry status and valid-data coverage fractions are tracked as separate attributes.
3. **Zero Data Fabrication:** When an asset is unavailable or decommissioned, values must be strictly masked as null and flagged with explicit missing-state reasons.

---

## 2. STANDARDIZED QA TAXONOMY

| QA State Code | Category | Definition / Trigger Condition |
| :--- | :--- | :--- |
| `AVAILABLE_FULL` | Operational | Catalog observations equal $100\%$ of theoretical expected frames (e.g., $120/120$ for 30-day CFSv2 month). |
| `AVAILABLE_PARTIAL` | Operational | Valid observations present but less than $100\%$ of expected frames due to unbroadcast frames or catalog outages (e.g., August 2024 CFSv2 with $123/124$ frames). |
| `ZERO_OBSERVATIONS` | Sensor Anomaly | Sensor was in orbit but recorded zero valid passes over WPPNRI 573 during the month (e.g., MODIS safe-mode anomalies in July 2021 and November 2021). |
| `MISSING_ASSET` | Infrastructure | Intended data collection is not ingested or available in the Earth Engine asset environment (e.g., CMEMS surface current for October 2024 through December 2025). |
| `INVALID_SOURCE_FLAG`| Numerical QC | Catalog frame is present but contains unphysical numerical spikes ($>40\text{ m/s}$) that violate physical limits and are masked to `NaN`. |
| `INVALID_DATA` | Numerical QC | Pixel value falls outside physically plausible domain (e.g., SST $< 18.0^\circ\text{C}$ or $> 35.0^\circ\text{C}$ in tropical pelagic waters). |
| `NOT_YET_LAUNCHED` | Temporal Limit | Sensor was not yet in orbit or operational (e.g., JAXA GCOM-C prior to January 2018). |
| `PROCESSING_FAILED` | Pipeline Error | Computational failure during raster transformation, spatial gradient calculation, or zonal reduction. |

---

## 3. VARIABLE-SPECIFIC QA EVALUATION RULES

### 3.1 Sea Surface Temperature (SST) — NOAA OISST v2.1
- **Expected Observations:** 1 daily composite per day ($28 - 31$ images/month).
- **Physical Plausible Bounds:** $[18.0^\circ\text{C}, 35.0^\circ\text{C}]$.
- **QA Logic:** If daily images $\ge \text{daysInMonth}$ and mean values within bounds $\implies$ `VALID`. If images $< \text{daysInMonth}$ $\implies$ `AVAILABLE_PARTIAL`. Out-of-bounds pixels masked.

### 3.2 Chlorophyll-a (Chl-a) — MODIS Aqua Primary & GCOM-C Secondary
- **Primary Source:** NASA MODIS-Aqua L3 SMI (`chlor_a`).
- **Secondary Source:** JAXA GCOM-C SGLI L3 V3 (`CHLA_AVE` $\times 0.0016$).
- **Physical Plausible Bounds:** $[0.01\text{ mg/m}^3, 30.0\text{ mg/m}^3]$.
- **Dynamic Fallback Arbitration:**
  - If MODIS valid pixels $> 0 \implies$ Use MODIS (`CHLA_SOURCE_CODE = 1`).
  - If MODIS pixels $= 0$ and GCOM-C pixels $> 0 \implies$ Use GCOM-C fallback (`CHLA_SOURCE_CODE = 2`).
  - If both $= 0 \implies$ Masked null (`CHLA_SOURCE_CODE = 0`, `ZERO_OBSERVATIONS`).

### 3.3 Surface Wind & Stress — NOAA CFSv2 Operational
- **Expected Observations:** $\text{daysInMonth} \times 4$ frames ($112, 116, 120, \text{ or } 124$ frames/month).
- **Drag Formulation:** Instantaneous 6-hour Wu (1982) stress $\boldsymbol{\tau} = \rho_a C_d |\mathbf{W}| \mathbf{W}$ with canonical $\rho_a = 1.225\text{ kg/m}^3$ and $C_d \in [0.0010, 0.0025]$.
- **QA Logic:**
  - Actual frames $=$ Expected frames $\implies$ `AVAILABLE_FULL`.
  - Actual frames $<$ Expected frames $\implies$ `AVAILABLE_PARTIAL` (e.g., August 2024: 123/124, December 2024: 122/124, April 2025: 114/120).
  - Outlier spikes $> 40\text{ m/s}$ $\implies$ Masked to NaN and flagged `INVALID_SOURCE_FLAG`.

### 3.4 Surface Ocean Currents — HYCOM & CMEMS Governance
- **Primary Operational Period (2015-01 to 2024-08):**
  - Expected: $\text{daysInMonth} \times 8$ frames ($224 - 248$ frames/month).
  - QA Status: `VALID`, `AVAILABLE_PRIMARY`.
- **Partial Cessation Month (September 2024):**
  - HYCOM ceased operations on September 5, 2024 (yielding exactly 36 frames).
  - QA Status: `PARTIAL_HYCOM_CESSATION_5_DAYS`.
- **Post-HYCOM Hiatus (October 2024 to December 2025):**
  - Un-ingested CMEMS assets strictly masked as null. Zero synthetic data.
  - QA Status: `MISSING_ASSET`, `SENSOR_FALLBACK_MISSING_ASSET`.

### 3.5 Derived Physical Variables
- **Thermal Front (|∇T|):** Evaluated from smoothed SST using dynamic longitudinal distance scaling in $^\circ\text{C/km}$. Valid range $[0.0, 0.5]^\circ\text{C/km}$. QA status inherits from SST.
- **Ekman Pumping ($w_E$):** Evaluated from instantaneous wind stress curl and beta term: $w_E = \frac{\text{curl}(\boldsymbol{\tau})}{\rho_w f} + \frac{\beta \tau_x}{\rho_w f^2}$ [m/day]. Valid range $[-50.0, +50.0]\text{ m/day}$. QA status inherits from CFSv2.
- **Bathymetry Depth ($D$):** Evaluated from NOAA ETOPO1 bedrock elevation. Positive ocean depth $D = -elevation$ for $elevation < 0$. Land pixels ($elevation \ge 0$) strictly masked. QA status: `VALID`.

---

## 4. OUTPUT SCHEMA METRICS

Every monthly record in the canonical grid export includes:
- `aoi_geometry_status`: `AOI_LOADED_VALID`
- `aoi_intersection_status`: `FULL_WPP573_INTERSECT` / `BOUNDARY_CELL_INTERSECT`
- `valid_data_coverage_fraction`: Floating point $[0.0, 1.0]$ representing unmasked marine pixels.
- `valid_data_coverage_status`: `NOMINAL_MARINE_COVERAGE` / `PARTIAL_CLOUD_MASKED`
- Individual variable QA statuses: `sst_qa_status`, `chla_qa_status`, `wind_qa_status`, `stress_qa_status`, `current_qa_status`, `front_qa_status`, `ekman_qa_status`, `bathy_qa_status`.

